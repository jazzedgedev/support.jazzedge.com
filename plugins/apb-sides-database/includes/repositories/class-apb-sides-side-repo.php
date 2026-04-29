<?php
/**
 * Repository: apb_sides_sides.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CRUD for sides + published search.
 */
class APB_Sides_Side_Repo {

	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'apb_sides_sides';
	}

	/**
	 * @return array<int, string>
	 */
	private function allowed_insert_keys(): array {
		return array(
			'script_id',
			'title',
			'scene_context',
			'actor_notes',
			'casting_type',
			'is_featured',
			'popularity_score',
			'times_saved',
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
		if ( ! empty( $args['script_id'] ) ) {
			$where[]   = 'script_id = %d';
			$prepare[] = (int) $args['script_id'];
		}

		$orderby = isset( $args['orderby'] ) ? sanitize_key( (string) $args['orderby'] ) : 'id';
		$allowed = array( 'id', 'created_at', 'title', 'status', 'popularity_score', 'is_featured' );
		if ( ! in_array( $orderby, $allowed, true ) ) {
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
		if ( ! empty( $args['script_id'] ) ) {
			$where[]   = 'script_id = %d';
			$prepare[] = (int) $args['script_id'];
		}
		$sql = "SELECT COUNT(*) FROM {$table} WHERE " . implode( ' AND ', $where );
		if ( count( $prepare ) > 0 ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $prepare );
		}
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Build JOIN + WHERE for published search filters.
	 * Returns [ $join_sql, $where_sql, $prepare_array ]
	 *
	 * @param array<string, mixed> $filters Filters from frontend.
	 * @return array{0: string, 1: string, 2: array<int, mixed>}
	 */
	private function published_where( array $filters ): array {
		global $wpdb;
		$scripts_table = $wpdb->prefix . 'apb_sides_scripts';
		$chars_table   = $wpdb->prefix . 'apb_sides_characters';
		$pivot_table   = $wpdb->prefix . 'apb_sides_side_characters';
		$table         = $this->table();

		$join    = "JOIN {$scripts_table} sc ON {$table}.script_id = sc.id";
		$where   = array( "{$table}.status = %s" );
		$prepare = array( APB_Sides_Statuses::PUBLISHED );

		if ( ! empty( $filters['show'] ) ) {
			$where[]   = 'sc.id = %d';
			$prepare[] = (int) $filters['show'];
		}

		if ( ! empty( $filters['genre'] ) ) {
			$where[]   = 'sc.genre = %s';
			$prepare[] = (string) $filters['genre'];
		}

		if ( ! empty( $filters['medium'] ) ) {
			$where[]   = 'sc.medium = %s';
			$prepare[] = (string) $filters['medium'];
		}

		if ( ! empty( $filters['gender'] ) ) {
			$where[] = "{$table}.id IN (
			SELECT pv.side_id FROM {$pivot_table} pv
			JOIN {$chars_table} ch ON pv.character_id = ch.id
			WHERE ch.gender = %s
		)";
			$prepare[] = (string) $filters['gender'];
		}

		if ( ! empty( $filters['casting_type'] ) ) {
			$ct        = '%' . $wpdb->esc_like( (string) $filters['casting_type'] ) . '%';
			$where[]   = "{$table}.casting_type LIKE %s";
			$prepare[] = $ct;
		}

		if ( ! empty( $filters['keyword'] ) ) {
			$kw        = '%' . $wpdb->esc_like( (string) $filters['keyword'] ) . '%';
			$where[]   = "({$table}.title LIKE %s OR {$table}.scene_context LIKE %s OR {$table}.actor_notes LIKE %s OR {$table}.casting_type LIKE %s OR sc.title LIKE %s)";
			$prepare[] = $kw;
			$prepare[] = $kw;
			$prepare[] = $kw;
			$prepare[] = $kw;
			$prepare[] = $kw;
		}

		return array( $join, implode( ' AND ', $where ), $prepare );
	}

	/**
	 * @param array<string, mixed> $filters Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public function search_published( array $filters, int $page, int $per_page ): array {
		global $wpdb;
		$table = $this->table();
		list( $join_sql, $where_sql, $prepare ) = $this->published_where( $filters );
		$page     = max( 1, $page );
		$per_page = max( 1, min( 50, $per_page ) );
		$offset   = ( $page - 1 ) * $per_page;

		$sql = "SELECT {$table}.* FROM {$table} {$join_sql} WHERE {$where_sql} ORDER BY {$table}.is_featured DESC, {$table}.popularity_score DESC, {$table}.id DESC LIMIT %d OFFSET %d";
		$prepare[] = $per_page;
		$prepare[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( $sql, $prepare );
		$rows  = $wpdb->get_results( $query, ARRAY_A );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param array<string, mixed> $filters Filters.
	 */
	public function count_published( array $filters ): int {
		global $wpdb;
		$table = $this->table();
		list( $join_sql, $where_sql, $prepare ) = $this->published_where( $filters );
		$sql = "SELECT COUNT(*) FROM {$table} {$join_sql} WHERE {$where_sql}";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( $sql, $prepare );
		return (int) $wpdb->get_var( $query );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_published_by_script_id( int $script_id ): array {
		global $wpdb;
		$table = $this->table();
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE script_id = %d AND status = %s ORDER BY title ASC",
				$script_id,
				APB_Sides_Statuses::PUBLISHED
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}
}
