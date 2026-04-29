<?php
/**
 * File-based debug logger for APB Sides Database.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logs to WP_CONTENT_DIR or plugin dir fallback. Errors and warnings always log; info/debug require debug_mode.
 */
class APB_Sides_Logger {

	/**
	 * Preferred path for new writes (wp-content when writable, else plugin directory).
	 */
	private static function resolve_write_path(): string {
		$primary = WP_CONTENT_DIR . '/apb-sides-debug.log';
		$dir     = dirname( $primary );
		if ( is_writable( $dir ) ) {
			return $primary;
		}
		return APB_SIDES_PLUGIN_DIR . 'apb-sides-debug.log';
	}

	/**
	 * Path shown in UI and used when a single canonical path is needed.
	 */
	public static function get_log_path(): string {
		$primary  = WP_CONTENT_DIR . '/apb-sides-debug.log';
		$fallback = APB_SIDES_PLUGIN_DIR . 'apb-sides-debug.log';
		if ( file_exists( $primary ) ) {
			return $primary;
		}
		if ( file_exists( $fallback ) ) {
			return $fallback;
		}
		if ( is_writable( dirname( $primary ) ) ) {
			return $primary;
		}
		return $fallback;
	}

	/**
	 * Read log file from whichever location has content (primary first).
	 *
	 * @return array{path: string, content: string}
	 */
	public static function read_log_contents(): array {
		$paths = array(
			WP_CONTENT_DIR . '/apb-sides-debug.log',
			APB_SIDES_PLUGIN_DIR . 'apb-sides-debug.log',
		);
		foreach ( $paths as $p ) {
			if ( file_exists( $p ) && is_readable( $p ) ) {
				$raw = file_get_contents( $p );
				return array(
					'path'    => $p,
					'content' => false !== $raw ? $raw : '',
				);
			}
		}
		return array(
			'path'    => self::get_log_path(),
			'content' => '',
		);
	}

	/**
	 * Truncate all known log files that are writable.
	 *
	 * @return bool False if an existing log file could not be cleared.
	 */
	public static function clear_log_files(): bool {
		$paths = array(
			WP_CONTENT_DIR . '/apb-sides-debug.log',
			APB_SIDES_PLUGIN_DIR . 'apb-sides-debug.log',
		);
		foreach ( array_unique( $paths ) as $log_path ) {
			if ( ! file_exists( $log_path ) ) {
				continue;
			}
			if ( ! is_writable( $log_path ) ) {
				return false;
			}
			if ( false === file_put_contents( $log_path, '', LOCK_EX ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Write a test line and report whether any log file received data.
	 */
	public static function test(): bool {
		self::log(
			'error',
			'Logger test - if you see this, logging is working',
			array( 'time' => gmdate( 'Y-m-d H:i:s' ) )
		);
		$paths = array(
			WP_CONTENT_DIR . '/apb-sides-debug.log',
			APB_SIDES_PLUGIN_DIR . 'apb-sides-debug.log',
		);
		foreach ( array_unique( $paths ) as $log_path ) {
			if ( file_exists( $log_path ) && is_readable( $log_path ) && filesize( $log_path ) > 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string               $level   error|warning|info|debug.
	 * @param string               $message Message.
	 * @param array<string, mixed> $context Context.
	 */
	public static function log( string $level, string $message, array $context = array() ): void {
		$always_log = in_array( $level, array( 'error', 'warning' ), true );

		if ( ! $always_log ) {
			$settings = APB_Sides_Helpers::get_settings();
			if ( empty( $settings['debug_mode'] ) ) {
				return;
			}
		}

		$log_path = self::resolve_write_path();
		$ctx_str  = '';
		if ( ! empty( $context ) ) {
			$encoded = wp_json_encode( $context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			$ctx_str = false !== $encoded ? ' | ' . $encoded : ' | {}';
		}
		$line = '[' . gmdate( 'Y-m-d H:i:s' ) . ' UTC] [' . strtoupper( $level ) . '] ' . $message . $ctx_str . PHP_EOL;

		$dir = dirname( $log_path );
		if ( ! is_writable( $dir ) && ! ( file_exists( $log_path ) && is_writable( $log_path ) ) ) {
			$log_path = APB_SIDES_PLUGIN_DIR . 'apb-sides-debug.log';
		}

		@file_put_contents( $log_path, $line, FILE_APPEND | LOCK_EX );
	}

	/**
	 * @param array<string, mixed> $context Context.
	 */
	public static function error( string $message, array $context = array() ): void {
		self::log( 'error', $message, $context );
	}

	/**
	 * Log the current $wpdb error if set (always logged at error level).
	 *
	 * @param array<string, mixed> $context Optional extra context (merged with last_query).
	 */
	public static function db_error( string $label, array $context = array() ): void {
		global $wpdb;
		if ( empty( $wpdb->last_error ) ) {
			return;
		}
		$ctx = array_merge(
			array( 'last_query' => $wpdb->last_query ),
			$context
		);
		self::error( $label . ' | DB error: ' . $wpdb->last_error, $ctx );
	}

	/**
	 * @param array<string, mixed> $context Context.
	 */
	public static function warning( string $message, array $context = array() ): void {
		self::log( 'warning', $message, $context );
	}

	/**
	 * @param array<string, mixed> $context Context.
	 */
	public static function info( string $message, array $context = array() ): void {
		self::log( 'info', $message, $context );
	}

	/**
	 * @param array<string, mixed> $context Context.
	 */
	public static function debug( string $message, array $context = array() ): void {
		self::log( 'debug', $message, $context );
	}
}
