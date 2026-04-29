<?php
/**
 * Analytics storage and reporting.
 *
 * @package Academy_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles page view and CTA click analytics.
 */
class AcademyStarterAnalytics {
	/**
	 * Create analytics tables.
	 *
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$views_table     = self::views_table();
		$clicks_table    = self::clicks_table();

		$views_sql = "CREATE TABLE {$views_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			style varchar(50) NOT NULL,
			view_date date NOT NULL,
			session_hash varchar(64) NOT NULL,
			ip_address varchar(45) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY style_date_session (style, view_date, session_hash),
			KEY view_date (view_date)
		) {$charset_collate};";

		$clicks_sql = "CREATE TABLE {$clicks_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			style varchar(50) NOT NULL,
			click_type enum('paid','free') NOT NULL,
			click_date date NOT NULL,
			session_hash varchar(64) NOT NULL,
			ip_address varchar(45) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY click_date (click_date),
			KEY click_type (click_type)
		) {$charset_collate};";

		dbDelta( $views_sql );
		dbDelta( $clicks_sql );
	}

	/**
	 * Log one unique daily view per style/session.
	 *
	 * @param string $style Style key.
	 * @return void
	 */
	public static function log_view( $style ) {
		global $wpdb;

		$style        = sanitize_key( $style );
		$view_date    = current_time( 'Y-m-d' );
		$session_hash = self::get_session_hash();

		$existing_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM " . self::views_table() . ' WHERE style = %s AND view_date = %s AND session_hash = %s LIMIT 1',
				$style,
				$view_date,
				$session_hash
			)
		);

		if ( $existing_id ) {
			return;
		}

		$wpdb->insert(
			self::views_table(),
			array(
				'style'        => $style,
				'view_date'    => $view_date,
				'session_hash' => $session_hash,
				'ip_address'   => self::get_ip_address(),
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Log a CTA click.
	 *
	 * @param string $style Style key.
	 * @param string $type  Click type: paid or free.
	 * @return bool
	 */
	public static function log_click( $style, $type ) {
		global $wpdb;

		$style = sanitize_key( $style );
		$type  = sanitize_key( $type );

		if ( ! in_array( $type, array( 'paid', 'free' ), true ) ) {
			return false;
		}

		return (bool) $wpdb->insert(
			self::clicks_table(),
			array(
				'style'        => $style,
				'click_type'   => $type,
				'click_date'   => current_time( 'Y-m-d' ),
				'session_hash' => self::get_session_hash(),
				'ip_address'   => self::get_ip_address(),
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Get aggregate analytics totals.
	 *
	 * @return array<string, float|int>
	 */
	public static function get_summary() {
		global $wpdb;

		$total_views = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::views_table() );

		$click_rows = $wpdb->get_results(
			'SELECT click_type, COUNT(*) AS total FROM ' . self::clicks_table() . ' GROUP BY click_type',
			ARRAY_A
		);

		$paid_clicks = 0;
		$free_clicks = 0;

		foreach ( $click_rows as $row ) {
			if ( 'paid' === $row['click_type'] ) {
				$paid_clicks = (int) $row['total'];
			} elseif ( 'free' === $row['click_type'] ) {
				$free_clicks = (int) $row['total'];
			}
		}

		$total_clicks = $paid_clicks + $free_clicks;

		return array(
			'total_views'     => $total_views,
			'paid_clicks'     => $paid_clicks,
			'free_clicks'     => $free_clicks,
			'conversion_rate' => $total_views > 0 ? round( ( $total_clicks / $total_views ) * 100, 2 ) : 0,
		);
	}

	/**
	 * Get daily stats for the last N days.
	 *
	 * @param int $days Number of days.
	 * @return array<int, array<string, int|string>>
	 */
	public static function get_daily_stats( $days = 30 ) {
		global $wpdb;

		$days       = max( 1, absint( $days ) );
		$start_date = gmdate( 'Y-m-d', strtotime( '-' . ( $days - 1 ) . ' days', current_time( 'timestamp' ) ) );
		$rows       = array();

		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$date          = gmdate( 'Y-m-d', strtotime( '-' . $i . ' days', current_time( 'timestamp' ) ) );
			$rows[ $date ] = array(
				'date'        => $date,
				'views'       => 0,
				'paid_clicks' => 0,
				'free_clicks' => 0,
			);
		}

		$view_counts = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT view_date AS stat_date, COUNT(*) AS total FROM ' . self::views_table() . ' WHERE view_date >= %s GROUP BY view_date',
				$start_date
			),
			ARRAY_A
		);

		foreach ( $view_counts as $view_count ) {
			$date = $view_count['stat_date'];
			if ( isset( $rows[ $date ] ) ) {
				$rows[ $date ]['views'] = (int) $view_count['total'];
			}
		}

		$click_counts = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT click_date AS stat_date, click_type, COUNT(*) AS total FROM ' . self::clicks_table() . ' WHERE click_date >= %s GROUP BY click_date, click_type',
				$start_date
			),
			ARRAY_A
		);

		foreach ( $click_counts as $click_count ) {
			$date = $click_count['stat_date'];
			$key  = 'paid' === $click_count['click_type'] ? 'paid_clicks' : 'free_clicks';

			if ( isset( $rows[ $date ] ) ) {
				$rows[ $date ][ $key ] = (int) $click_count['total'];
			}
		}

		return array_values( $rows );
	}

	/**
	 * Delete all rows from both analytics tables.
	 *
	 * @return void
	 */
	public static function reset_stats() {
		global $wpdb;

		$wpdb->query( 'TRUNCATE TABLE ' . self::views_table() );
		$wpdb->query( 'TRUNCATE TABLE ' . self::clicks_table() );
	}

	/**
	 * Get individual view records for a specific date.
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return array<int, array<string, string>>
	 */
	public static function get_views_for_date( $date ) {
		global $wpdb;

		$date = sanitize_text_field( $date );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT created_at, ip_address, session_hash FROM ' . self::views_table() . ' WHERE view_date = %s ORDER BY created_at ASC',
				$date
			),
			ARRAY_A
		);

		return $rows ? $rows : array();
	}

	/**
	 * Add missing columns to existing analytics tables.
	 *
	 * @return void
	 */
	public static function maybe_upgrade_tables() {
		global $wpdb;

		$tables = array(
			self::views_table(),
			self::clicks_table(),
		);

		foreach ( $tables as $table ) {
			$column_exists = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s',
					DB_NAME,
					$table,
					'ip_address'
				)
			);

			if ( ! $column_exists ) {
				$wpdb->query( "ALTER TABLE {$table} ADD COLUMN ip_address varchar(45) NOT NULL DEFAULT '' AFTER session_hash" );
			}
		}
	}

	/**
	 * Get the view table name.
	 *
	 * @return string
	 */
	private static function views_table() {
		global $wpdb;

		return $wpdb->prefix . 'academy_starter_views';
	}

	/**
	 * Get the click table name.
	 *
	 * @return string
	 */
	private static function clicks_table() {
		global $wpdb;

		return $wpdb->prefix . 'academy_starter_clicks';
	}

	/**
	 * Hash request IP and user agent.
	 *
	 * @return string
	 */
	private static function get_session_hash() {
		$ip         = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		return hash( 'sha256', $ip . '|' . $user_agent . '|' . wp_salt( 'auth' ) );
	}

	/**
	 * Get the request IP address.
	 *
	 * @return string
	 */
	private static function get_ip_address() {
		$ip = '';

		$headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) )[0] );
				break;
			}
		}

		return $ip;
	}
}
