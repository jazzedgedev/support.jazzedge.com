<?php
/**
 * Repository: apb_sides_scripts.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CRUD for scripts.
 */
class APB_Sides_Script_Repo {

	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'apb_sides_scripts';
	}

	/**
	 * @return array<int, string>
	 */
	private function allowed_insert_keys(): array {
		return array(
			'upload_id',
			'title',
			'writer',
			'medium',
			'genre',
			'tone',
			'setting_location',
			'setting_era',
			'year_written',
			'source_file_url',
			'copyright_status',
			'notes',
			'status',
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
		return array_intersect_key( $data, array_flip( $this->allowed_insert_keys() ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function filter_update_data( array $data ): array {
		return array_intersect_key( $data, array_flip( $this->allowed_update_keys() ) );
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
			$where[]   = 'status = %s';
			$prepare[] = $args['status'];
		}
		if ( ! empty( $args['upload_id'] ) ) {
			$where[]   = 'upload_id = %d';
			$prepare[] = (int) $args['upload_id'];
		}

		$orderby = isset( $args['orderby'] ) ? sanitize_key( (string) $args['orderby'] ) : 'id';
		if ( ! in_array( $orderby, array( 'id', 'created_at', 'title', 'status' ), true ) ) {
			$orderby = 'id';
		}
		$order  = isset( $args['order'] ) && strtoupper( (string) $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		$limit  = isset( $args['limit'] ) ? max( 1, (int) $args['limit'] ) : 100;
		$offset = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : 0;

		$base = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . " ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$prepare[] = $limit;
		$prepare[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( $base, $prepare );
		$rows  = $wpdb->get_results( $query, ARRAY_A );
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

	public function count( array $args = array() ): int {
		global $wpdb;
		$table   = $this->table();
		$where   = array( '1=1' );
		$prepare = array();
		if ( ! empty( $args['status'] ) ) {
			$where[]   = 'status = %s';
			$prepare[] = $args['status'];
		}
		if ( ! empty( $args['upload_id'] ) ) {
			$where[]   = 'upload_id = %d';
			$prepare[] = (int) $args['upload_id'];
		}
		$sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode( ' AND ', $where );
		if ( count( $prepare ) > 0 ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $prepare );
		}
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_published(): array {
		global $wpdb;
		$table = $this->table();
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE status = %s ORDER BY title ASC",
				APB_Sides_Statuses::PUBLISHED
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Search scripts by title or writer (all statuses).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function search( string $query ): array {
		global $wpdb;
		$table = $this->table();
		$like  = '%' . $wpdb->esc_like( $query ) . '%';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE title LIKE %s OR writer LIKE %s ORDER BY title ASC LIMIT 200",
				$like,
				$like
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}
}
