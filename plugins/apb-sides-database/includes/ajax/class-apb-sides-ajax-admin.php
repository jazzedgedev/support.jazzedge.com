<?php
/**
 * Admin AJAX handlers.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers wp_ajax_* actions.
 */
class APB_Sides_Ajax_Admin {

	public function __construct() {
		$actions = array(
			'apb_sides_upload_file',
			'apb_sides_save_review',
			'apb_sides_approve_publish_all',
			'apb_sides_publish_upload',
			'apb_sides_rename_upload',
			'apb_sides_upload_script_pdf',
			'apb_sides_delete_upload',
			'apb_sides_save_entity',
			'apb_sides_test_api_key',
			'apb_sides_reinstall_tables',
			'apb_sides_migrate_schema',
			'apb_sides_clear_log',
			'apb_sides_get_log',
			'apb_sides_import_json',
		);
		foreach ( $actions as $action ) {
			add_action( 'wp_ajax_' . $action, array( $this, str_replace( 'apb_sides_', 'handle_', $action ) ) );
		}
	}

	private function auth(): void {
		check_ajax_referer( 'apb_sides_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'apb-sides-database' ) ) );
		}
	}

	/**
	 * Create a minimal upload record (parsing/Claude pipeline removed).
	 */
	public function handle_upload_file(): void {
		$this->auth();

		$script_title = isset( $_POST['script_title'] ) ? sanitize_text_field( wp_unslash( $_POST['script_title'] ) ) : '';
		$pasted_text  = isset( $_POST['pasted_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pasted_text'] ) ) : '';

		if ( '' === trim( $script_title ) ) {
			wp_send_json_error( array( 'message' => __( 'Script title is required.', 'apb-sides-database' ) ) );
		}

		$pasted_ok = ( '' !== trim( $pasted_text ) && str_word_count( $pasted_text ) > 20 );
		$file_ok  = ! empty( $_FILES['file']['tmp_name'] ) && is_uploaded_file( (string) $_FILES['file']['tmp_name'] );

		if ( ! $file_ok && ! $pasted_ok ) {
			wp_send_json_error(
				array(
					'message' => __( 'Upload a PDF or image and/or paste at least 20 words of script text.', 'apb-sides-database' ),
				)
			);
		}

		$upload_repo = new APB_Sides_Upload_Repo();
		$file_type   = 'text';
		$orig_name   = $script_title;

		if ( $file_ok ) {
			$filename = isset( $_FILES['file']['name'] ) ? sanitize_file_name( wp_unslash( (string) $_FILES['file']['name'] ) ) : 'upload.pdf';
			$orig_name = $filename;
			$ext       = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
			if ( $ext === 'jpeg' ) {
				$ext = 'jpg';
			}
			$file_type = '' !== $ext ? $ext : 'file';
		} elseif ( $pasted_ok ) {
			$file_type = 'text';
			$orig_name = $script_title;
		}

		$upload_id = (int) $upload_repo->insert(
			array(
				'original_filename'    => $orig_name,
				'file_type'            => $file_type,
				'pending_script_title' => $script_title,
				'uploaded_by'          => get_current_user_id(),
				'upload_status'        => APB_Sides_Statuses::UPLOADED,
			)
		);
		if ( ! $upload_id ) {
			wp_send_json_error( array( 'message' => __( 'Could not create upload record.', 'apb-sides-database' ) ) );
		}

		$message = $pasted_ok
			? __( 'Text noted — use Import JSON to add scenes to the database.', 'apb-sides-database' )
			: __( 'Upload record saved. Use Import JSON to add script data.', 'apb-sides-database' );

		wp_send_json_success(
			array(
				'upload_id'     => $upload_id,
				'pasted_saved'  => $pasted_ok,
				'message'       => $message,
			)
		);
	}

	public function handle_save_review(): void {
		$this->auth();
		$raw     = isset( $_POST['payload'] ) ? wp_unslash( $_POST['payload'] ) : '';
		$payload = json_decode( is_string( $raw ) ? $raw : '', true );
		if ( ! is_array( $payload ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid payload.', 'apb-sides-database' ) ) );
		}

		$script_repo = new APB_Sides_Script_Repo();
		$char_repo   = new APB_Sides_Character_Repo();
		$side_repo   = new APB_Sides_Side_Repo();
		$pivot_repo  = new APB_Sides_Side_Character_Repo();

		if ( ! empty( $payload['script'] ) && is_array( $payload['script'] ) ) {
			$sid = isset( $payload['script']['id'] ) ? absint( $payload['script']['id'] ) : 0;
			if ( $sid ) {
				unset( $payload['script']['id'] );
				$script_repo->update( $sid, $this->sanitize_script_row( $payload['script'] ) );
			}
		}

		if ( ! empty( $payload['characters'] ) && is_array( $payload['characters'] ) ) {
			foreach ( $payload['characters'] as $c ) {
				if ( ! is_array( $c ) ) {
					continue;
				}
				$cid = isset( $c['id'] ) ? absint( $c['id'] ) : 0;
				if ( ! $cid ) {
					continue;
				}
				unset( $c['id'] );
				$char_repo->update( $cid, $this->sanitize_character_row( $c ) );
			}
		}

		if ( ! empty( $payload['sides'] ) && is_array( $payload['sides'] ) ) {
			foreach ( $payload['sides'] as $s ) {
				if ( ! is_array( $s ) ) {
					continue;
				}
				$sid = isset( $s['id'] ) ? absint( $s['id'] ) : 0;
				if ( ! $sid ) {
					continue;
				}
				$char_ids = isset( $s['character_ids'] ) && is_array( $s['character_ids'] ) ? array_map( 'absint', $s['character_ids'] ) : array();
				unset( $s['id'], $s['character_ids'] );
				$side_repo->update( $sid, $this->sanitize_side_row( $s ) );
				$pivot_repo->sync_for_side( $sid, $char_ids );
			}
		}

		wp_send_json_success( array( 'message' => __( 'Saved.', 'apb-sides-database' ) ) );
	}

	/**
	 * @param array<string, mixed> $row Raw fields.
	 * @return array<string, mixed>
	 */
	private function sanitize_script_row( array $row ): array {
		$out = array();
		$map = array(
			'title'            => 'sanitize_text_field',
			'medium'          => 'sanitize_text_field',
			'genre'            => 'sanitize_textarea_field',
			'source_file_url' => 'esc_url_raw',
			'status'          => 'sanitize_text_field',
		);
		foreach ( $map as $k => $cb ) {
			if ( array_key_exists( $k, $row ) ) {
				$out[ $k ] = call_user_func( $cb, (string) $row[ $k ] );
			}
		}
		return $out;
	}

	/**
	 * @param array<string, mixed> $row Raw fields.
	 * @return array<string, mixed>
	 */
	private function sanitize_character_row( array $row ): array {
		$out = array();
		$map = array(
			'name'            => 'sanitize_text_field',
			'gender'         => 'sanitize_text_field',
			'age_range_label' => 'sanitize_text_field',
			'status'         => 'sanitize_text_field',
		);
		foreach ( $map as $k => $cb ) {
			if ( array_key_exists( $k, $row ) ) {
				$out[ $k ] = call_user_func( $cb, (string) $row[ $k ] );
			}
		}
		return $out;
	}

	/**
	 * @param array<string, mixed> $row Raw fields.
	 * @return array<string, mixed>
	 */
	private function sanitize_side_row( array $row ): array {
		$out    = array();
		$ints   = array( 'is_featured', 'popularity_score', 'times_saved' );
		$text_l = array( 'scene_context', 'actor_notes' );
		$text   = array( 'title', 'casting_type', 'status' );
		foreach ( array_merge( $text, $text_l ) as $k ) {
			if ( array_key_exists( $k, $row ) ) {
				$out[ $k ] = in_array( $k, $text_l, true ) ? sanitize_textarea_field( (string) $row[ $k ] ) : sanitize_text_field( (string) $row[ $k ] );
			}
		}
		foreach ( $ints as $k ) {
			if ( array_key_exists( $k, $row ) ) {
				$out[ $k ] = ( null === $row[ $k ] || $row[ $k ] === '' ) ? null : absint( $row[ $k ] );
			}
		}
		return $out;
	}

	public function handle_import_json(): void {
		$this->auth();
		$json_raw = isset( $_POST['json_data'] ) ? wp_unslash( $_POST['json_data'] ) : '';
		if ( '' === $json_raw ) {
			wp_send_json_error( array( 'message' => __( 'Missing JSON.', 'apb-sides-database' ) ) );
		}
		$decoded = json_decode( $json_raw, true );
		if ( ! is_array( $decoded ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid JSON.', 'apb-sides-database' ) ) );
		}

		$title = isset( $decoded['show'] )
			? sanitize_text_field( (string) $decoded['show'] )
			: ( isset( $decoded['script']['title'] ) ? sanitize_text_field( (string) $decoded['script']['title'] ) : '' );
		if ( '' === $title ) {
			wp_send_json_error( array( 'message' => __( 'JSON must include a "show" field with the title.', 'apb-sides-database' ) ) );
		}

		$is_simple = isset( $decoded['show'] );
		$normalized = $is_simple
			? APB_Sides_Normalizer::normalize_simple( $decoded )
			: APB_Sides_Normalizer::normalize( $decoded );

		$show_title  = (string) ( $normalized['script']['title'] ?? $title );
		$upload_repo = new APB_Sides_Upload_Repo();
		$script_repo = new APB_Sides_Script_Repo();
		$char_repo   = new APB_Sides_Character_Repo();
		$side_repo   = new APB_Sides_Side_Repo();
		$pivot_repo  = new APB_Sides_Side_Character_Repo();

		$upload_id = (int) $upload_repo->insert(
			array(
				'original_filename'    => $show_title,
				'file_type'            => 'json_import',
				'pending_script_title' => $show_title,
				'uploaded_by'          => get_current_user_id(),
				'upload_status'        => APB_Sides_Statuses::PUBLISHED,
			)
		);
		if ( ! $upload_id ) {
			wp_send_json_error( array( 'message' => __( 'Could not create upload record.', 'apb-sides-database' ) ) );
		}

		global $wpdb;
		$existing_script = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}apb_sides_scripts WHERE title = %s LIMIT 1",
				$show_title
			),
			ARRAY_A
		);

		if ( $existing_script ) {
			$script_id = (int) $existing_script['id'];
		} else {
			$script_id = (int) $script_repo->insert(
				array(
					'upload_id' => $upload_id,
					'title'     => $show_title,
					'medium'    => $normalized['script']['medium'] ?? null,
					'genre'     => $normalized['script']['genre'] ?? null,
					'status'    => APB_Sides_Statuses::PUBLISHED,
				)
			);
			if ( ! $script_id ) {
				wp_send_json_error( array( 'message' => __( 'Could not create script record.', 'apb-sides-database' ) ) );
			}
		}

		$char_id_map = array();
		$existing_chars = $char_repo->get_by_script_id( $script_id );
		foreach ( $existing_chars as $ec ) {
			$char_id_map[ strtolower( (string) $ec['name'] ) ] = (int) $ec['id'];
		}
		foreach ( $normalized['characters'] as $c ) {
			$name_key = strtolower( (string) ( $c['name'] ?? '' ) );
			if ( $name_key === '' ) {
				continue;
			}
			if ( ! isset( $char_id_map[ $name_key ] ) ) {
				$new_cid = $char_repo->insert(
					array(
						'script_id'        => $script_id,
						'name'            => $c['name'] ?? '',
						'gender'          => $c['gender'] ?? null,
						'age_range_label' => $c['age_range_label'] ?? null,
						'status'          => APB_Sides_Statuses::PUBLISHED,
					)
				);
				if ( $new_cid ) {
					$char_id_map[ $name_key ] = (int) $new_cid;
				}
			}
		}

		$scene_count = 0;
		foreach ( $normalized['sides'] as $s ) {
			$side_id = (int) $side_repo->insert(
				array(
					'script_id'     => $script_id,
					'title'         => $s['title'] ?? '',
					'scene_context' => $s['scene_context'] ?? null,
					'actor_notes'   => $s['actor_notes'] ?? null,
					'casting_type'  => $s['casting_type'] ?? null,
					'status'        => APB_Sides_Statuses::PUBLISHED,
				)
			);
			if ( ! $side_id ) {
				continue;
			}
			$scene_count++;
			$pivot_ids = array_values( $char_id_map );
			if ( ! empty( $pivot_ids ) ) {
				$pivot_repo->sync_for_side( $side_id, $pivot_ids );
			}
		}

		$pdf_url = '';
		if ( ! empty( $_FILES['script_pdf']['tmp_name'] ) && is_uploaded_file( (string) wp_unslash( $_FILES['script_pdf']['tmp_name'] ) ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$uploaded = wp_handle_upload( wp_unslash( $_FILES['script_pdf'] ), array( 'test_form' => false ) );
			if ( ! empty( $uploaded['url'] ) ) {
				$pdf_url = $uploaded['url'];
				$script_repo->update( $script_id, array( 'source_file_url' => $pdf_url ) );
			}
		}

		wp_send_json_success(
			array(
				'upload_id'   => $upload_id,
				'script_id'   => $script_id,
				'scene_count' => $scene_count,
				'grouped'     => ! empty( $existing_script ),
				'pdf_url'     => $pdf_url,
			)
		);
	}

	public function handle_upload_script_pdf(): void {
		$this->auth();
		$script_id = isset( $_POST['script_id'] ) ? absint( wp_unslash( $_POST['script_id'] ) ) : 0;
		if ( ! $script_id || empty( $_FILES['script_pdf']['tmp_name'] ) || ! is_uploaded_file( (string) wp_unslash( $_FILES['script_pdf']['tmp_name'] ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'apb-sides-database' ) ) );
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$uploaded = wp_handle_upload( wp_unslash( $_FILES['script_pdf'] ), array( 'test_form' => false ) );
		if ( empty( $uploaded['url'] ) ) {
			wp_send_json_error(
				array(
					'message' => isset( $uploaded['error'] ) ? (string) $uploaded['error'] : __( 'Upload failed.', 'apb-sides-database' ),
				)
			);
		}
		( new APB_Sides_Script_Repo() )->update(
			$script_id,
			array( 'source_file_url' => $uploaded['url'] )
		);
		wp_send_json_success( array( 'pdf_url' => $uploaded['url'] ) );
	}

	public function handle_approve_publish_all(): void {
		$this->auth();
		$upload_id = isset( $_POST['upload_id'] ) ? absint( wp_unslash( $_POST['upload_id'] ) ) : 0;
		if ( ! $upload_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid upload.', 'apb-sides-database' ) ) );
		}
		$script_repo = new APB_Sides_Script_Repo();
		$char_repo   = new APB_Sides_Character_Repo();
		$side_repo   = new APB_Sides_Side_Repo();
		$scripts     = $script_repo->get_all( array( 'upload_id' => $upload_id, 'limit' => 500, 'offset' => 0 ) );
		foreach ( $scripts as $script ) {
			$sid = (int) $script['id'];
			$script_repo->update( $sid, array( 'status' => APB_Sides_Statuses::PUBLISHED ) );
			foreach ( $char_repo->get_by_script_id( $sid ) as $c ) {
				$char_repo->update( (int) $c['id'], array( 'status' => APB_Sides_Statuses::PUBLISHED ) );
			}
			foreach ( $side_repo->get_all( array( 'script_id' => $sid, 'limit' => 500, 'offset' => 0 ) ) as $s ) {
				$side_repo->update( (int) $s['id'], array( 'status' => APB_Sides_Statuses::PUBLISHED ) );
			}
		}
		( new APB_Sides_Upload_Repo() )->update( $upload_id, array( 'upload_status' => APB_Sides_Statuses::PUBLISHED ) );
		wp_send_json_success();
	}

	public function handle_publish_upload(): void {
		$this->auth();
		$upload_id = isset( $_POST['upload_id'] ) ? absint( wp_unslash( $_POST['upload_id'] ) ) : 0;
		if ( ! $upload_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid upload.', 'apb-sides-database' ) ) );
		}
		$script_repo  = new APB_Sides_Script_Repo();
		$char_repo    = new APB_Sides_Character_Repo();
		$side_repo    = new APB_Sides_Side_Repo();
		$upload_repo  = new APB_Sides_Upload_Repo();
		$warn_pending = (bool) APB_Sides_Helpers::get_setting( 'publish_warn_pending', true );
		$warnings     = array();
		$scripts      = $script_repo->get_all(
			array(
				'upload_id' => $upload_id,
				'limit'     => 500,
				'offset'    => 0,
			)
		);
		foreach ( $scripts as $script ) {
			$sid = (int) $script['id'];
			if ( (string) $script['status'] === APB_Sides_Statuses::APPROVED ) {
				$script_repo->update( $sid, array( 'status' => APB_Sides_Statuses::PUBLISHED ) );
			} elseif ( (string) $script['status'] === APB_Sides_Statuses::REVIEW_PENDING && $warn_pending ) {
				$warnings[] = sprintf(
					/* translators: %d script id */
					__( 'Script %d still in review.', 'apb-sides-database' ),
					$sid
				);
			}
			foreach ( $char_repo->get_by_script_id( $sid ) as $c ) {
				$cid = (int) $c['id'];
				if ( (string) $c['status'] === APB_Sides_Statuses::APPROVED ) {
					$char_repo->update( $cid, array( 'status' => APB_Sides_Statuses::PUBLISHED ) );
				} elseif ( (string) $c['status'] === APB_Sides_Statuses::REVIEW_PENDING && $warn_pending ) {
					$warnings[] = sprintf(
						/* translators: %d character id */
						__( 'Character %d still in review.', 'apb-sides-database' ),
						$cid
					);
				}
			}
			foreach ( $side_repo->get_all( array( 'script_id' => $sid, 'limit' => 500, 'offset' => 0 ) ) as $s ) {
				$side_id = (int) $s['id'];
				if ( (string) $s['status'] === APB_Sides_Statuses::APPROVED ) {
					$side_repo->update( $side_id, array( 'status' => APB_Sides_Statuses::PUBLISHED ) );
				} elseif ( (string) $s['status'] === APB_Sides_Statuses::REVIEW_PENDING && $warn_pending ) {
					$warnings[] = sprintf(
						/* translators: %d side id */
						__( 'Side %d still in review.', 'apb-sides-database' ),
						$side_id
					);
				}
			}
		}
		$upload_repo->update( $upload_id, array( 'upload_status' => APB_Sides_Statuses::PUBLISHED ) );
		wp_send_json_success(
			array(
				'success'  => true,
				'warnings' => $warnings,
			)
		);
	}

	public function handle_rename_upload(): void {
		$this->auth();
		$id   = isset( $_POST['upload_id'] ) ? absint( wp_unslash( $_POST['upload_id'] ) ) : 0;
		$name = isset( $_POST['filename'] ) ? sanitize_text_field( wp_unslash( $_POST['filename'] ) ) : '';
		if ( ! $id || '' === $name ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'apb-sides-database' ) ) );
		}
		$repo = new APB_Sides_Upload_Repo();
		$ok   = $repo->update( $id, array( 'original_filename' => $name ) );
		if ( false === $ok ) {
			wp_send_json_error( array( 'message' => __( 'Update failed.', 'apb-sides-database' ) ) );
		}
		wp_send_json_success();
	}

	public function handle_delete_upload(): void {
		$this->auth();
		$id = isset( $_POST['upload_id'] ) ? absint( wp_unslash( $_POST['upload_id'] ) ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid upload.', 'apb-sides-database' ) ) );
		}
		$this->delete_upload_cascade( $id );
		wp_send_json_success();
	}

	private function delete_upload_cascade( int $upload_id ): void {
		$upload_repo  = new APB_Sides_Upload_Repo();
		$script_repo  = new APB_Sides_Script_Repo();
		$char_repo    = new APB_Sides_Character_Repo();
		$side_repo    = new APB_Sides_Side_Repo();
		$pivot_repo   = new APB_Sides_Side_Character_Repo();

		$upload = $upload_repo->get_by_id( $upload_id );
		if ( ! $upload ) {
			return;
		}
		$scripts = $script_repo->get_all( array( 'upload_id' => $upload_id, 'limit' => 500, 'offset' => 0 ) );
		foreach ( $scripts as $sc ) {
			$sid   = (int) $sc['id'];
			$sides = $side_repo->get_all( array( 'script_id' => $sid, 'limit' => 500, 'offset' => 0 ) );
			foreach ( $sides as $s ) {
				$pivot_repo->delete_by_side_id( (int) $s['id'] );
				$side_repo->delete( (int) $s['id'] );
			}
			foreach ( $char_repo->get_by_script_id( $sid ) as $c ) {
				$pivot_repo->delete_by_character_id( (int) $c['id'] );
				$char_repo->delete( (int) $c['id'] );
			}
			$script_repo->delete( $sid );
		}

		$upload_repo->delete( $upload_id );
	}

	public function handle_save_entity(): void {
		$this->auth();
		$type  = isset( $_POST['entity_type'] ) ? sanitize_text_field( wp_unslash( $_POST['entity_type'] ) ) : '';
		$id    = isset( $_POST['entity_id'] ) ? absint( wp_unslash( $_POST['entity_id'] ) ) : 0;
		$raw   = isset( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : '';
		$fields = json_decode( is_string( $raw ) ? $raw : '', true );
		if ( ! $id || ! is_array( $fields ) || ! in_array( $type, array( 'script', 'character', 'side' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid save.', 'apb-sides-database' ) ) );
		}
		if ( 'script' === $type ) {
			( new APB_Sides_Script_Repo() )->update( $id, $this->sanitize_script_row( $fields ) );
		} elseif ( 'character' === $type ) {
			( new APB_Sides_Character_Repo() )->update( $id, $this->sanitize_character_row( $fields ) );
		} else {
			$char_ids = isset( $fields['character_ids'] ) && is_array( $fields['character_ids'] ) ? array_map( 'absint', $fields['character_ids'] ) : array();
			unset( $fields['character_ids'] );
			( new APB_Sides_Side_Repo() )->update( $id, $this->sanitize_side_row( $fields ) );
			( new APB_Sides_Side_Character_Repo() )->sync_for_side( $id, $char_ids );
		}
		wp_send_json_success();
	}

	public function handle_reinstall_tables(): void {
		$this->auth();
		APB_Sides_Activator::drop_all_tables();
		APB_Sides_Activator::install();
		wp_send_json_success( array( 'message' => __( 'All tables dropped and recreated successfully.', 'apb-sides-database' ) ) );
	}

	public function handle_migrate_schema(): void {
		$this->auth();
		APB_Sides_Activator::migrate_schema();
		wp_send_json_success( array( 'message' => __( 'Schema migrated — unused columns and tables removed.', 'apb-sides-database' ) ) );
	}

	public function handle_clear_log(): void {
		$this->auth();
		if ( ! APB_Sides_Logger::clear_log_files() ) {
			wp_send_json_error( array( 'message' => __( 'Log file is not writable.', 'apb-sides-database' ) ) );
			return;
		}
		wp_send_json_success( array( 'message' => __( 'Log cleared.', 'apb-sides-database' ) ) );
	}

	public function handle_get_log(): void {
		$this->auth();
		$read     = APB_Sides_Logger::read_log_contents();
		$log_path = $read['path'];
		$content  = $read['content'];
		wp_send_json_success( array( 'content' => $content, 'log_path' => $log_path ) );
	}

	public function handle_test_api_key(): void {
		check_ajax_referer( 'apb_sides_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'apb-sides-database' ) ) );
		}

		$api_key = APB_Sides_Helpers::get_anthropic_api_key();
		$model   = (string) APB_Sides_Helpers::get_setting( 'anthropic_model', 'claude-haiku-4-5' );
		if ( $api_key === '' ) {
			APB_Sides_Logger::error( 'Anthropic API key test: no key configured', array() );
			wp_send_json_error( array( 'message' => __( 'No API key configured.', 'apb-sides-database' ) ) );
		}

		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'timeout' => 30,
				'headers' => array(
					'x-api-key'         => $api_key,
					'anthropic-version' => '2023-06-01',
					'Content-Type'      => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => $model,
						'max_tokens' => 10,
						'messages'   => array(
							array(
								'role'    => 'user',
								'content' => 'Reply with the single word OK.',
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			APB_Sides_Logger::error( 'Anthropic API key test: request failed', array( 'error' => $response->get_error_message() ) );
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$status  = wp_remote_retrieve_response_code( $response );
		$body    = (string) wp_remote_retrieve_body( $response );
		$decoded = json_decode( $body, true );

		if ( 401 === $status ) {
			APB_Sides_Logger::error( 'Anthropic API key test: 401', array() );
			wp_send_json_error( array( 'message' => __( 'Invalid API key', 'apb-sides-database' ) ) );
		}

		if ( 400 === $status ) {
			$msg = __( 'Bad request', 'apb-sides-database' );
			if ( is_array( $decoded ) && isset( $decoded['error']['message'] ) ) {
				$msg = (string) $decoded['error']['message'];
			}
			APB_Sides_Logger::error( 'Anthropic API key test: 400', array( 'body' => $body ) );
			wp_send_json_error( array( 'message' => $msg ) );
		}

		if ( 200 !== $status ) {
			$msg = __( 'Unknown API error', 'apb-sides-database' );
			if ( is_array( $decoded ) && isset( $decoded['error']['message'] ) ) {
				$msg = (string) $decoded['error']['message'];
			}
			APB_Sides_Logger::error(
				'Anthropic API key test: non-200',
				array(
					'http_status' => $status,
					'body'        => $body,
				)
			);
			wp_send_json_error(
				array(
					'message'     => $msg,
					'http_status' => $status,
				)
			);
		}

		$reply = '';
		if ( is_array( $decoded ) && ! empty( $decoded['content'] ) && is_array( $decoded['content'] ) ) {
			foreach ( $decoded['content'] as $block ) {
				if ( is_array( $block ) && isset( $block['text'] ) ) {
					$reply .= (string) $block['text'];
				}
			}
		}

		APB_Sides_Logger::info(
			'Anthropic API key test: success',
			array(
				'model' => $model,
				'reply' => $reply,
			)
		);

		wp_send_json_success(
			array(
				'message' => __( 'Connection successful. Model responded: ', 'apb-sides-database' ) . sanitize_text_field( $reply ),
			)
		);
	}
}
