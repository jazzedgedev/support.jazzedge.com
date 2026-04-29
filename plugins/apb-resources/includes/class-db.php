<?php
/**
 * Database tables.
 *
 * @package APB_Resources
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class APB_DB
 */
class APB_DB {

	/**
	 * Categories table name (with prefix).
	 *
	 * @return string
	 */
	public static function categories_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'apb_resource_categories';
	}

	/**
	 * Resources table name (with prefix).
	 *
	 * @return string
	 */
	public static function resources_table(): string {
		global $wpdb;
		return $wpdb->prefix . 'apb_resources';
	}

	/**
	 * Create or upgrade tables (activation + version bump).
	 *
	 * @return void
	 */
	public static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$cat             = self::categories_table();
		$res             = self::resources_table();

		$sql_categories = "CREATE TABLE {$cat} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			slug VARCHAR(255) NOT NULL,
			description TEXT,
			page_id BIGINT UNSIGNED DEFAULT NULL,
			sort_order INT DEFAULT 0,
			hero_image_id BIGINT UNSIGNED DEFAULT NULL,
			card_image_id BIGINT UNSIGNED DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug)
		) {$charset_collate};";

		$sql_resources = "CREATE TABLE {$res} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL,
			description TEXT,
			url VARCHAR(500) NOT NULL,
			image_url VARCHAR(500) DEFAULT NULL,
			button_text VARCHAR(100) NOT NULL DEFAULT 'Learn more...',
			category_id BIGINT UNSIGNED NOT NULL,
			sort_order INT DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY category_id (category_id)
		) {$charset_collate};";

		dbDelta( $sql_categories );
		dbDelta( $sql_resources );

		update_option( 'apb_res_db_version', APB_RES_VERSION );
	}

	/**
	 * Ensure tables exist when plugin version changes.
	 *
	 * @return void
	 */
	public static function db_version_check(): void {
		if ( get_option( 'apb_res_db_version' ) !== APB_RES_VERSION ) {
			self::create_tables();
		}
	}

	/**
	 * Add legacy image_id column when missing (later renamed to hero_image_id by maybe_split_image_columns).
	 *
	 * @return void
	 */
	public static function maybe_add_image_column(): void {
		global $wpdb;

		$table = self::categories_table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
		$has_hero = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'hero_image_id'" );
		if ( ! empty( $has_hero ) ) {
			return;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
		$col = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'image_id'" );
		if ( empty( $col ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN image_id BIGINT UNSIGNED DEFAULT NULL" );
		}
	}

	/**
	 * Rename legacy image_id to hero_image_id and add card_image_id for dashboard cards.
	 *
	 * @return void
	 */
	public static function maybe_split_image_columns(): void {
		global $wpdb;

		$table = self::categories_table();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
		$has_image_id = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'image_id'" );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
		$has_hero = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'hero_image_id'" );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
		$has_card = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'card_image_id'" );

		if ( ! empty( $has_image_id ) && empty( $has_hero ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
			$wpdb->query( "ALTER TABLE `{$table}` CHANGE COLUMN image_id hero_image_id BIGINT UNSIGNED DEFAULT NULL" );
		}

		if ( empty( $has_card ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN card_image_id BIGINT UNSIGNED DEFAULT NULL" );
		}
	}

	/**
	 * Add image_url column on apb_resources for existing installs.
	 *
	 * @return void
	 */
	public static function maybe_add_resource_image_column(): void {
		global $wpdb;

		$table = self::resources_table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
		$col = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'image_url'" );
		if ( empty( $col ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted helper.
			$wpdb->query( "ALTER TABLE `{$table}` ADD COLUMN image_url VARCHAR(500) DEFAULT NULL" );
		}
	}
}
