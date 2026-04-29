<?php
/**
 * Plugin activation: tables, defaults, upload dir.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs on activate hook.
 */
class APB_Sides_Activator {

	/**
	 * Drop all plugin tables (destructive).
	 */
	public static function drop_all_tables(): void {
		global $wpdb;
		$prefix = $wpdb->prefix . 'apb_sides_';
		$tables = array(
			'side_characters',
			'sides',
			'characters',
			'scripts',
			'uploads',
			'parsed_documents',
			'ai_extractions',
			'review_queue',
			'jobs',
		);
		foreach ( $tables as $t ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names are fixed list.
			$wpdb->query( "DROP TABLE IF EXISTS `{$prefix}{$t}`" );
		}
	}

	/**
	 * Create or upgrade all tables (no drop).
	 */
	public static function install(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix;

		// updated_at kept for compatibility with APB_Sides_Upload_Repo insert/update.
		$sql_uploads = "CREATE TABLE {$prefix}apb_sides_uploads (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			original_filename varchar(255) NOT NULL DEFAULT '',
			file_type varchar(50) NOT NULL DEFAULT '',
			pending_script_title varchar(255) NOT NULL DEFAULT '',
			uploaded_by bigint(20) unsigned NOT NULL DEFAULT 0,
			upload_status varchar(50) NOT NULL DEFAULT 'uploaded',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id)
		) $charset_collate;";

		$sql_scripts = "CREATE TABLE {$prefix}apb_sides_scripts (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			upload_id bigint(20) unsigned DEFAULT NULL,
			title varchar(255) NOT NULL DEFAULT '',
			medium varchar(50) DEFAULT 'scene',
			genre text DEFAULT NULL,
			source_file_url text DEFAULT NULL,
			status varchar(50) NOT NULL DEFAULT 'draft',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY upload_id (upload_id),
			KEY status (status)
		) $charset_collate;";

		$sql_chars = "CREATE TABLE {$prefix}apb_sides_characters (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			script_id bigint(20) unsigned NOT NULL DEFAULT 0,
			name varchar(255) NOT NULL DEFAULT '',
			gender varchar(100) DEFAULT NULL,
			age_range_label varchar(100) DEFAULT NULL,
			status varchar(50) NOT NULL DEFAULT 'draft',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY script_id (script_id),
			KEY status (status)
		) $charset_collate;";

		$sql_sides = "CREATE TABLE {$prefix}apb_sides_sides (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			script_id bigint(20) unsigned NOT NULL DEFAULT 0,
			title varchar(255) NOT NULL DEFAULT '',
			scene_context longtext DEFAULT NULL,
			actor_notes longtext DEFAULT NULL,
			casting_type varchar(255) DEFAULT NULL,
			is_featured tinyint(1) NOT NULL DEFAULT 0,
			popularity_score int NOT NULL DEFAULT 0,
			times_saved int NOT NULL DEFAULT 0,
			status varchar(50) NOT NULL DEFAULT 'draft',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY script_id (script_id),
			KEY status (status),
			KEY is_featured (is_featured)
		) $charset_collate;";

		// created_at needed by APB_Sides_Side_Character_Repo::insert.
		$sql_pivot = "CREATE TABLE {$prefix}apb_sides_side_characters (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			side_id bigint(20) unsigned NOT NULL DEFAULT 0,
			character_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY side_char (side_id, character_id),
			KEY character_id (character_id)
		) $charset_collate;";

		dbDelta( $sql_uploads );
		dbDelta( $sql_scripts );
		dbDelta( $sql_chars );
		dbDelta( $sql_sides );
		dbDelta( $sql_pivot );

		update_option( 'apb_sides_version', APB_SIDES_VERSION );

		$defaults = array(
			'anthropic_api_key'        => '',
			'anthropic_model'          => 'claude-haiku-4-5',
			'ai_global_instructions'   => '',
			'max_file_size_mb'         => 20,
			'max_input_chars'          => 30000,
			'confidence_threshold'     => 0.7,
			'debug_mode'               => true,
			'schema_version'           => '1.0',
			'publish_warn_pending'     => true,
		);

		if ( false === get_option( 'apb_sides_settings', false ) ) {
			add_option( 'apb_sides_settings', $defaults, '', false );
		}

		$upload_dir = APB_SIDES_UPLOAD_DIR;
		if ( ! is_dir( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}
	}

	/**
	 * One-time cleanup: remove obsolete tables and columns from prior schema versions.
	 */
	public static function migrate_schema(): void {
		global $wpdb;
		$p = $wpdb->prefix . 'apb_sides_';

		// Drop obsolete tables.
		foreach ( array( 'parsed_documents', 'ai_extractions', 'review_queue', 'jobs' ) as $t ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names are fixed list.
			$wpdb->query( "DROP TABLE IF EXISTS `{$p}{$t}`" );
		}

		// Scripts — drop unused columns (ignore errors if already gone).
		foreach ( array( 'writer', 'tone', 'setting_location', 'setting_era', 'year_written', 'copyright_status', 'notes' ) as $col ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- column names are fixed list.
			$wpdb->query( "ALTER TABLE `{$p}scripts` DROP COLUMN IF EXISTS `{$col}`" );
		}

		// Characters — drop unused columns.
		foreach ( array( 'age_min', 'age_max', 'role_size', 'archetype', 'occupation', 'description', 'energy_level', 'traits', 'accent', 'notes' ) as $col ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- column names are fixed list.
			$wpdb->query( "ALTER TABLE `{$p}characters` DROP COLUMN IF EXISTS `{$col}`" );
		}

		// Sides — drop unused columns.
		foreach ( array( 'scene_start_page', 'scene_end_page', 'scene_length_pages', 'estimated_runtime_minutes', 'location', 'time_of_day', 'setting_type', 'primary_character_id', 'gender_focus', 'age_range_focus', 'scene_type', 'tone', 'energy_level', 'emotions', 'relationship_dynamic', 'acting_skills', 'dialogue_type', 'dialogue_density', 'best_for', 'difficulty_level', 'script_excerpt', 'pdf_side_file' ) as $col ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- column names are fixed list.
			$wpdb->query( "ALTER TABLE `{$p}sides` DROP COLUMN IF EXISTS `{$col}`" );
		}
	}

	/**
	 * Plugin activation: create/upgrade tables without dropping data.
	 */
	public static function activate(): void {
		self::install();
	}
}
