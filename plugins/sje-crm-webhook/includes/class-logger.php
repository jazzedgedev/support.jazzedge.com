<?php
/**
 * Logger class for SJE CRM Webhook
 *
 * Handles database logging of webhook requests and responses.
 *
 * @package SJE_CRM_Webhook
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SJE_CRM_Webhook_Logger {

	/**
	 * Whether create_table() has run this request
	 *
	 * @var bool
	 */
	private static $table_checked = false;

	/**
	 * Get the log table name
	 *
	 * @return string
	 */
	private static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'sje_crm_log';
	}

	/**
	 * Create the logs table using dbDelta
	 */
	public static function create_table() {
		if ( self::$table_checked ) {
			return;
		}
		self::$table_checked = true;

		global $wpdb;
		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			source_ip varchar(45) DEFAULT NULL,
			email varchar(255) DEFAULT NULL,
			payload longtext DEFAULT NULL,
			status varchar(20) DEFAULT NULL,
			message text DEFAULT NULL,
			PRIMARY KEY (id),
			KEY created_at (created_at),
			KEY status (status)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Log an entry
	 *
	 * @param array $data Keys: source_ip, email, payload, status, message
	 * @return int|false Insert ID or false on failure
	 */
	public static function log( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$source_ip = isset( $data['source_ip'] ) ? $data['source_ip'] : ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		$email     = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		$payload   = isset( $data['payload'] ) ? $data['payload'] : '';
		$status    = isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : '';
		$message   = isset( $data['message'] ) ? $data['message'] : '';

		if ( is_array( $payload ) || is_object( $payload ) ) {
			$payload = wp_json_encode( $payload );
		}

		$result = $wpdb->insert(
			$table_name,
			array(
				'source_ip' => $source_ip,
				'email'     => $email,
				'payload'   => $payload,
				'status'    => $status,
				'message'   => $message,
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Purge logs older than retention days
	 */
	public static function purge_old_logs() {
		global $wpdb;
		$table_name = self::get_table_name();
		$days       = (int) get_option( 'sje_crm_log_retention_days', 30 );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$days
			)
		);
	}

	/**
	 * Get paginated logs
	 *
	 * @param int $per_page Rows per page
	 * @param int $page     Page number (1-based)
	 * @return array{items: array, total: int}
	 */
	public static function get_logs( $per_page = 20, $page = 1 ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$per_page = max( 1, (int) $per_page );
		$page     = max( 1, (int) $page );
		$offset   = ( $page - 1 ) * $per_page;

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		return array(
			'items' => $items ? $items : array(),
			'total' => $total,
		);
	}

	/**
	 * Delete all logs
	 *
	 * @return int|false Number of rows affected or false
	 */
	public static function purge_all() {
		global $wpdb;
		$table_name = self::get_table_name();
		return $wpdb->query( "DELETE FROM {$table_name}" );
	}
}
