<?php
/**
 * Repository: apb_sides_side_characters pivot.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pivot between sides and characters.
 */
class APB_Sides_Side_Character_Repo {

	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'apb_sides_side_characters';
	}

	/**
	 * @return array<int, string>
	 */
	private function allowed_insert_keys(): array {
		return array(
			'side_id',
			'character_id',
			'created_at',
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
	 * @param array<string, mixed> $args Unused minimal pattern.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_all( array $args = array() ): array {
		global $wpdb;
		$table  = $this->table();
		$limit  = isset( $args['limit'] ) ? max( 1, (int) $args['limit'] ) : 500;
		$offset = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : 0;
		$rows   = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} ORDER BY id ASC LIMIT %d OFFSET %d", $limit, $offset ),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param array<string, mixed> $data Row data.
	 * @return int|false
	 */
	public function insert( array $data ) {
		global $wpdb;
		$data    = $this->filter_insert_data( $data );
		$data['created_at'] = current_time( 'mysql' );
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
		$data = $this->filter_update_data( $data );
		return false !== $wpdb->update( $this->table(), $data, array( 'id' => $id ) );
	}

	public function delete( int $id ): bool {
		global $wpdb;
		return false !== $wpdb->delete( $this->table(), array( 'id' => $id ) );
	}

	public function count( array $args = array() ): int {
		global $wpdb;
		$table = $this->table();
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_characters_for_side( int $side_id ): array {
		global $wpdb;
		$pivot = $this->table();
		$chars = $wpdb->prefix . 'apb_sides_characters';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c.* FROM {$chars} c INNER JOIN {$pivot} sc ON sc.character_id = c.id WHERE sc.side_id = %d ORDER BY c.name ASC",
				$side_id
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_sides_for_character( int $character_id ): array {
		global $wpdb;
		$pivot = $this->table();
		$sides = $wpdb->prefix . 'apb_sides_sides';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.* FROM {$sides} s INNER JOIN {$pivot} sc ON sc.side_id = s.id WHERE sc.character_id = %d ORDER BY s.title ASC",
				$character_id
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Replace pivot rows for a side with given character IDs.
	 *
	 * @param array<int, int> $character_ids Character IDs.
	 */
	public function sync_for_side( int $side_id, array $character_ids ): void {
		global $wpdb;
		$table = $this->table();
		$wpdb->delete( $table, array( 'side_id' => $side_id ) );
		$seen = array();
		foreach ( $character_ids as $cid ) {
			$cid = (int) $cid;
			if ( $cid <= 0 || isset( $seen[ $cid ] ) ) {
				continue;
			}
			$seen[ $cid ] = true;
			$this->insert(
				array(
					'side_id'      => $side_id,
					'character_id' => $cid,
				)
			);
		}
	}

	/**
	 * Delete all pivot rows for a side.
	 */
	public function delete_by_side_id( int $side_id ): void {
		global $wpdb;
		$wpdb->delete( $this->table(), array( 'side_id' => $side_id ) );
	}

	/**
	 * Delete pivot rows referencing character.
	 */
	public function delete_by_character_id( int $character_id ): void {
		global $wpdb;
		$wpdb->delete( $this->table(), array( 'character_id' => $character_id ) );
	}
}
