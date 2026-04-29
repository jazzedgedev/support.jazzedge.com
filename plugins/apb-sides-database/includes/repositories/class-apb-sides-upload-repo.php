<?php
/**
 * Repository: apb_sides_uploads.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CRUD for uploads.
 */
class APB_Sides_Upload_Repo {

	/**
	 * Table name without prefix.
	 */
	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'apb_sides_uploads';
	}

	/**
	 * @return array<int, string>
	 */
	private function allowed_insert_keys(): array {
		return array(
			'original_filename',
			'stored_filename',
			'file_type',
			'mime_type',
			'file_path',
			'file_url',
			'pending_script_title',
			'uploaded_by',
			'upload_status',
			'created_at',
			'updated_at',
		);
	}

	/**
	 * @return array<int, string>
	 */
	private function allowed_update_keys(): array {
		return $this->allowed_insert_keys();
	}

	/**
	 * @return array<string, mixed>
	 */
	private function filter_insert_data( array $data ): array {
		$allowed = array_flip( $this->allowed_insert_keys() );
		return array_intersect_key( $data, $allowed );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function filter_update_data( array $data ): array {
		$allowed = array_flip( $this->allowed_update_keys() );
		return array_intersect_key( $data, $allowed );
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function get_by_id( int $id ): ?array {
		global $wpdb;
		$table = $this->table();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A );
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param array<string, mixed> $args Query args.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_all( array $args = array() ): array {
		global $wpdb;
		$table   = $this->table();
		$where   = array( '1=1' );
		$prepare = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'upload_status = %s';
			$prepare[] = $args['status'];
		}

		$orderby = isset( $args['orderby'] ) ? sanitize_key( (string) $args['orderby'] ) : 'id';
		if ( ! in_array( $orderby, array( 'id', 'created_at', 'updated_at', 'upload_status' ), true ) ) {
			$orderby = 'id';
		}
		$order = isset( $args['order'] ) && strtoupper( (string) $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		$limit = isset( $args['limit'] ) ? max( 1, (int) $args['limit'] ) : 100;
		$offset = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : 0;

		$sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . " ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$prepare[] = $limit;
		$prepare[] = $offset;

		if ( count( $prepare ) > 0 ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- dynamic placeholders built safely.
			$query = $wpdb->prepare( $sql, $prepare );
		} else {
			$query = $sql;
		}

		$rows = $wpdb->get_results( $query, ARRAY_A );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param array<string, mixed> $data Row data.
	 * @return int|false
	 */
	public function insert( array $data ) {
		global $wpdb;
		$data    = $this->filter_insert_data( $data );
		$now     = current_time( 'mysql' );
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$result = $wpdb->insert( $this->table(), $data );
		if ( false === $result || ! $wpdb->insert_id ) {
			APB_Sides_Logger::error(
				'DB insert failed',
				array(
					'table'      => $this->table(),
					'last_error' => $wpdb->last_error,
					'last_query' => $wpdb->last_query,
					'data_keys'  => array_keys( $data ),
				)
			);
			return false;
		}
		return (int) $wpdb->insert_id;
	}

	/**
	 * @param array<string, mixed> $data Row data.
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;
		$data                = $this->filter_update_data( $data );
		$data['updated_at'] = current_time( 'mysql' );
		return false !== $wpdb->update( $this->table(), $data, array( 'id' => $id ) );
	}

	public function delete( int $id ): bool {
		global $wpdb;
		return false !== $wpdb->delete( $this->table(), array( 'id' => $id ) );
	}

	/**
	 * @param array<string, mixed> $args Optional status filter.
	 */
	public function count( array $args = array() ): int {
		global $wpdb;
		$table = $this->table();
		if ( ! empty( $args['status'] ) ) {
			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE upload_status = %s", $args['status'] ) );
		}
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_by_status( string $status ): array {
		global $wpdb;
		$table = $this->table();
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE upload_status = %s ORDER BY id DESC", $status ), ARRAY_A );
		return is_array( $rows ) ? $rows : array();
	}
}
