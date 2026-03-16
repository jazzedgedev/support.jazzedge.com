<?php
/**
 * Jazzedge Marketing - Database
 *
 * @package Jazzedge_Marketing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JEM_Database
 */
class JEM_Database {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * Table names.
	 *
	 * @var string[]
	 */
	public $funnels_table;
	public $leads_table;
	public $events_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->prefix = $wpdb->prefix;

		$this->funnels_table = $this->prefix . 'jem_funnels';
		$this->leads_table   = $this->prefix . 'jem_leads';
		$this->events_table  = $this->prefix . 'jem_events';
	}

	/**
	 * Create tables on plugin activation.
	 */
	public function create_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $this->wpdb->get_charset_collate();

		$sql1 = "CREATE TABLE {$this->funnels_table} (
			id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(200) NOT NULL,
			webhook_url text NOT NULL,
			media_id bigint UNSIGNED DEFAULT NULL,
			product_url text NOT NULL,
			product_id varchar(50) NOT NULL DEFAULT '',
			coupon_prefix varchar(20) NOT NULL DEFAULT 'JEM',
			discount_pct int NOT NULL DEFAULT 20,
			coupon_days int NOT NULL DEFAULT 3,
			active tinyint NOT NULL DEFAULT 1,
			inactive_msg text NOT NULL DEFAULT '',
			invite_code varchar(32) NOT NULL DEFAULT '',
			created_at datetime DEFAULT NULL,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY invite_code (invite_code)
		) $charset_collate;";

		$sql2 = "CREATE TABLE {$this->leads_table} (
			id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
			funnel_id bigint UNSIGNED NOT NULL,
			first_name varchar(100) NOT NULL,
			last_name varchar(100) NOT NULL,
			email varchar(200) NOT NULL,
			coupon_code varchar(50) NOT NULL,
			coupon_expires datetime DEFAULT NULL,
			download_token varchar(64) NOT NULL,
			webhook_sent tinyint NOT NULL DEFAULT 0,
			webhook_response text NOT NULL DEFAULT '',
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY email_funnel (email, funnel_id)
		) $charset_collate;";

		$sql3 = "CREATE TABLE {$this->events_table} (
			id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
			lead_id bigint UNSIGNED NOT NULL,
			funnel_id bigint UNSIGNED NOT NULL,
			event_type varchar(50) NOT NULL,
			created_at datetime DEFAULT NULL,
			PRIMARY KEY (id),
			KEY funnel_id (funnel_id),
			KEY lead_id (lead_id)
		) $charset_collate;";

		dbDelta( $sql1 );
		dbDelta( $sql2 );
		dbDelta( $sql3 );

		$this->maybe_add_invite_code_column();
		$this->backfill_invite_codes();
		$this->maybe_add_invite_code_unique_index();
	}

	/**
	 * Add UNIQUE index on invite_code after backfill (avoids duplicate '').
	 */
	private function maybe_add_invite_code_unique_index() {
		$indexes = $this->wpdb->get_results( "SHOW INDEX FROM {$this->funnels_table} WHERE Key_name = 'invite_code'" );
		if ( empty( $indexes ) ) {
			$this->wpdb->query( "ALTER TABLE {$this->funnels_table} ADD UNIQUE KEY invite_code (invite_code)" );
		}
	}

	/**
	 * Add invite_code column if missing (for existing installs).
	 */
	private function maybe_add_invite_code_column() {
		$column = $this->wpdb->get_results( $this->wpdb->prepare(
			"SHOW COLUMNS FROM {$this->funnels_table} LIKE %s",
			'invite_code'
		) );
		if ( empty( $column ) ) {
			$this->wpdb->query( "ALTER TABLE {$this->funnels_table} ADD COLUMN invite_code varchar(32) NOT NULL DEFAULT '' AFTER inactive_msg" );
		}
	}

	/**
	 * Generate invite_code for funnels that don't have one.
	 */
	private function backfill_invite_codes() {
		$funnels = $this->wpdb->get_results( "SELECT id FROM {$this->funnels_table} WHERE invite_code = '' OR invite_code IS NULL" );
		foreach ( $funnels as $funnel ) {
			$code = strtolower( wp_generate_password( 24, false ) );
			$this->wpdb->update(
				$this->funnels_table,
				array( 'invite_code' => $code ),
				array( 'id' => $funnel->id )
			);
		}
	}

	/**
	 * Get funnel by invite code.
	 *
	 * @param string $invite_code Invite code.
	 * @return object|null
	 */
	public function get_funnel_by_invite_code( $invite_code ) {
		if ( empty( $invite_code ) || ! is_string( $invite_code ) ) {
			return null;
		}
		return $this->wpdb->get_row( $this->wpdb->prepare(
			"SELECT * FROM {$this->funnels_table} WHERE invite_code = %s AND active = 1",
			$invite_code
		) );
	}

	/**
	 * Regenerate invite code for a funnel.
	 *
	 * @param int $funnel_id Funnel ID.
	 * @return string|false New invite code or false on failure.
	 */
	public function regenerate_invite_code( $funnel_id ) {
		$code = strtolower( wp_generate_password( 24, false ) );
		$result = $this->wpdb->update(
			$this->funnels_table,
			array( 'invite_code' => $code, 'updated_at' => current_time( 'mysql' ) ),
			array( 'id' => (int) $funnel_id )
		);
		return $result !== false ? $code : false;
	}

	/**
	 * Get funnel by ID.
	 *
	 * @param int $id Funnel ID.
	 * @return object|null
	 */
	public function get_funnel( $id ) {
		$id = (int) $id;
		if ( $id <= 0 ) {
			return null;
		}
		return $this->wpdb->get_row( $this->wpdb->prepare(
			"SELECT * FROM {$this->funnels_table} WHERE id = %d",
			$id
		) );
	}

	/**
	 * Get all funnels.
	 *
	 * @return array
	 */
	public function get_funnels() {
		return $this->wpdb->get_results( "SELECT * FROM {$this->funnels_table} ORDER BY id DESC", ARRAY_A );
	}

	/**
	 * Insert funnel.
	 *
	 * @param array $data Funnel data.
	 * @return int|false
	 */
	public function insert_funnel( $data ) {
		$now = current_time( 'mysql' );
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$result = $this->wpdb->insert( $this->funnels_table, $data );
		return $result ? $this->wpdb->insert_id : false;
	}

	/**
	 * Update funnel.
	 *
	 * @param int   $id   Funnel ID.
	 * @param array $data Data to update.
	 * @return bool
	 */
	public function update_funnel( $id, $data ) {
		$data['updated_at'] = current_time( 'mysql' );
		return (bool) $this->wpdb->update( $this->funnels_table, $data, array( 'id' => (int) $id ) );
	}

	/**
	 * Get lead by token.
	 *
	 * @param string $token Download token.
	 * @return object|null
	 */
	public function get_lead_by_token( $token ) {
		if ( empty( $token ) || ! is_string( $token ) ) {
			return null;
		}
		return $this->wpdb->get_row( $this->wpdb->prepare(
			"SELECT * FROM {$this->leads_table} WHERE download_token = %s",
			$token
		) );
	}

	/**
	 * Check for existing lead by email and funnel.
	 *
	 * @param string $email     Email.
	 * @param int    $funnel_id Funnel ID.
	 * @return object|null
	 */
	public function get_lead_by_email_funnel( $email, $funnel_id ) {
		return $this->wpdb->get_row( $this->wpdb->prepare(
			"SELECT * FROM {$this->leads_table} WHERE email = %s AND funnel_id = %d",
			$email,
			(int) $funnel_id
		) );
	}

	/**
	 * Insert lead.
	 *
	 * @param array $data Lead data.
	 * @return int|false
	 */
	public function insert_lead( $data ) {
		$data['created_at'] = current_time( 'mysql' );
		$result = $this->wpdb->insert( $this->leads_table, $data );
		return $result ? $this->wpdb->insert_id : false;
	}

	/**
	 * Update lead.
	 *
	 * @param int   $id   Lead ID.
	 * @param array $data Data to update.
	 * @return bool
	 */
	public function update_lead( $id, $data ) {
		return (bool) $this->wpdb->update( $this->leads_table, $data, array( 'id' => (int) $id ) );
	}

	/**
	 * Delete lead.
	 *
	 * @param int $id Lead ID.
	 * @return bool
	 */
	public function delete_lead( $id ) {
		return (bool) $this->wpdb->delete( $this->leads_table, array( 'id' => (int) $id ) );
	}

	/**
	 * Log event.
	 *
	 * @param int    $lead_id   Lead ID.
	 * @param int    $funnel_id Funnel ID.
	 * @param string $event_type Event type: opt_in, download_click, purchase_click.
	 * @return int|false
	 */
	public function log_event( $lead_id, $funnel_id, $event_type ) {
		$result = $this->wpdb->insert( $this->events_table, array(
			'lead_id'    => (int) $lead_id,
			'funnel_id'  => (int) $funnel_id,
			'event_type' => sanitize_key( $event_type ),
			'created_at' => current_time( 'mysql' ),
		) );
		return $result ? $this->wpdb->insert_id : false;
	}

	/**
	 * Count events by funnel and type.
	 *
	 * @param int|null $funnel_id Optional funnel ID.
	 * @param string   $event_type Event type.
	 * @return int
	 */
	public function count_events( $funnel_id, $event_type ) {
		if ( $funnel_id ) {
			return (int) $this->wpdb->get_var( $this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->events_table} WHERE funnel_id = %d AND event_type = %s",
				(int) $funnel_id,
				$event_type
			) );
		}
		return (int) $this->wpdb->get_var( $this->wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->events_table} WHERE event_type = %s",
			$event_type
		) );
	}

	/**
	 * Get recent leads (last N).
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public function get_recent_leads( $limit = 50 ) {
		$limit = max( 1, (int) $limit );
		return $this->wpdb->get_results( $this->wpdb->prepare(
			"SELECT l.*, f.name as funnel_name FROM {$this->leads_table} l
			 LEFT JOIN {$this->funnels_table} f ON l.funnel_id = f.id
			 ORDER BY l.created_at DESC LIMIT %d",
			$limit
		), ARRAY_A );
	}
}
