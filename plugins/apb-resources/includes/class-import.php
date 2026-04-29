<?php
/**
 * JSON import handler.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class APB_Import
 */
class APB_Import {

	/**
	 * Bootstrap.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_post_apb_import_json', [ __CLASS__, 'handle_import' ] );
	}

	/**
	 * Process pasted JSON (creates new resources only).
	 *
	 * @return void
	 */
	public static function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'apb-resources' ) );
		}
		check_admin_referer( 'apb_import_nonce', 'apb_import_nonce' );

		$category_id = isset( $_POST['apb_category_id'] ) ? absint( wp_unslash( $_POST['apb_category_id'] ) ) : 0;
		if ( ! $category_id ) {
			wp_safe_redirect(
				esc_url_raw(
					add_query_arg(
						[
							'page'               => 'apb-resource-import',
							'apb_import_error'   => 'no_category',
						],
						admin_url( 'admin.php' )
					)
				)
			);
			exit;
		}

		$raw  = isset( $_POST['apb_json'] ) ? wp_unslash( $_POST['apb_json'] ) : '';
		$data = json_decode( $raw, true );

		if ( null === $data || ! is_array( $data ) ) {
			wp_safe_redirect(
				esc_url_raw(
					admin_url( 'admin.php?page=apb-resource-import&apb_import_error=invalid_json' )
				)
			);
			exit;
		}

		global $wpdb;

		$res_table = APB_DB::resources_table();

		$imported = 0;
		$skipped  = [];

		foreach ( $data as $index => $item ) {
			if ( ! is_array( $item ) ) {
				$skipped[] = (string) $index;
				continue;
			}

			$title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
			$url   = isset( $item['url'] ) ? esc_url_raw( $item['url'] ) : '';

			if ( '' === $title || '' === $url ) {
				$skipped[] = (string) $index;
				continue;
			}

			$description = isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '';
			$image_url   = isset( $item['image_url'] ) ? esc_url_raw( $item['image_url'] ) : '';
			$button_raw  = isset( $item['button_text'] ) ? sanitize_text_field( $item['button_text'] ) : '';
			$button_text = $button_raw ? $button_raw : 'Learn more...';
			$sort_order  = isset( $item['sort_order'] ) ? intval( $item['sort_order'] ) : 0;

			$wpdb->insert(
				$res_table,
				[
					'title'       => $title,
					'description' => $description,
					'url'         => $url,
					'image_url'   => $image_url,
					'button_text' => $button_text,
					'category_id' => $category_id,
					'sort_order'  => $sort_order,
				],
				[ '%s', '%s', '%s', '%s', '%s', '%d', '%d' ]
			);

			if ( $wpdb->insert_id ) {
				++$imported;
			}
		}

		$skipped_count = count( $skipped );

		wp_safe_redirect(
			esc_url_raw(
				add_query_arg(
					[
						'page'         => 'apb-resource-import',
						'apb_imported' => $imported,
						'apb_skipped'  => $skipped_count,
					],
					admin_url( 'admin.php' )
				)
			)
		);
		exit;
	}
}
