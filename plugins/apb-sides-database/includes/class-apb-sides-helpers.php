<?php
/**
 * Static helpers for APB Sides Database.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility helpers: settings, paths, formatting.
 */
class APB_Sides_Helpers {

	/**
	 * Default settings merged with stored option.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		$defaults = array(
			'anthropic_model'          => 'claude-haiku-4-5',
			'anthropic_api_key'        => '',
			'ai_global_instructions'   => '',
			'max_file_size_mb'         => 20,
			'max_input_chars'          => 30000,
			'schema_version'           => '1.0',
			'confidence_threshold'     => 0.7,
			'debug_mode'               => true,
			'publish_warn_pending'     => true,
		);
		$stored = get_option( 'apb_sides_settings', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		// Migrate legacy OpenAI keys once.
		if ( empty( $stored['anthropic_api_key'] ) && ! empty( $stored['ai_api_key'] ) ) {
			$stored['anthropic_api_key'] = $stored['ai_api_key'];
		}
		if ( empty( $stored['anthropic_model'] ) && ! empty( $stored['ai_model'] ) ) {
			$stored['anthropic_model'] = $stored['ai_model'];
		}

		$model_migration = array(
			'claude-haiku-3-5'         => 'claude-haiku-4-5',
			'claude-opus-4-5'          => 'claude-haiku-4-5',
			'claude-3-5-haiku-20241022'  => 'claude-haiku-4-5',
			'claude-3-5-sonnet-20241022' => 'claude-haiku-4-5',
			'claude-3-opus-20240229'     => 'claude-haiku-4-5',
			'gpt-4o'                   => 'claude-haiku-4-5',
		);
		$valid_models = array( 'claude-haiku-4-5', 'claude-sonnet-4-6', 'claude-opus-4-7' );
		if ( isset( $stored['anthropic_model'] ) && isset( $model_migration[ $stored['anthropic_model'] ] ) ) {
			$stored['anthropic_model'] = $model_migration[ $stored['anthropic_model'] ];
		} elseif ( isset( $stored['anthropic_model'] ) && ! in_array( (string) $stored['anthropic_model'], $valid_models, true ) ) {
			$stored['anthropic_model'] = 'claude-haiku-4-5';
		}
		return array_merge( $defaults, $stored );
	}

	/**
	 * Single setting value.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Default if missing.
	 * @return mixed
	 */
	public static function get_setting( string $key, $default = null ) {
		$settings = self::get_settings();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	/**
	 * Absolute upload directory path.
	 */
	public static function get_upload_dir(): string {
		return APB_SIDES_UPLOAD_DIR;
	}

	/**
	 * Public URL for uploads directory.
	 */
	public static function get_upload_url(): string {
		return APB_SIDES_UPLOAD_URL;
	}

	/**
	 * Human-readable status label.
	 */
	public static function status_label( string $status ): string {
		$labels = array(
			APB_Sides_Statuses::UPLOADED       => __( 'Uploaded', 'apb-sides-database' ),
			APB_Sides_Statuses::PARSED         => __( 'Parsed', 'apb-sides-database' ),
			APB_Sides_Statuses::PARSE_FAILED     => __( 'Parse Failed', 'apb-sides-database' ),
			APB_Sides_Statuses::OCR_PENDING      => __( 'OCR Pending', 'apb-sides-database' ),
			APB_Sides_Statuses::AI_PENDING       => __( 'AI Pending', 'apb-sides-database' ),
			APB_Sides_Statuses::AI_COMPLETE      => __( 'AI Complete', 'apb-sides-database' ),
			APB_Sides_Statuses::AI_FAILED        => __( 'AI Failed', 'apb-sides-database' ),
			APB_Sides_Statuses::REVIEW_PENDING   => __( 'Review Pending', 'apb-sides-database' ),
			APB_Sides_Statuses::APPROVED         => __( 'Approved', 'apb-sides-database' ),
			APB_Sides_Statuses::PUBLISHED        => __( 'Published', 'apb-sides-database' ),
			APB_Sides_Statuses::UNPUBLISHED      => __( 'Unpublished', 'apb-sides-database' ),
			APB_Sides_Statuses::REJECTED        => __( 'Rejected', 'apb-sides-database' ),
			'pending'                          => __( 'Pending', 'apb-sides-database' ),
			'draft'                            => __( 'Draft', 'apb-sides-database' ),
		);
		return $labels[ $status ] ?? $status;
	}

	/**
	 * HTML badge for a status.
	 */
	public static function get_status_badge_html( string $status ): string {
		$label = esc_html( self::status_label( $status ) );
		$class = 'apb-status-badge apb-status-' . esc_attr( sanitize_html_class( $status ) );
		return '<span class="' . $class . '">' . $label . '</span>';
	}

	/**
	 * Split CSV string to trimmed non-empty array.
	 *
	 * @return array<int, string>
	 */
	public static function csv_to_array( string $csv ): array {
		if ( $csv === '' ) {
			return array();
		}
		$parts = explode( ',', $csv );
		$out   = array();
		foreach ( $parts as $p ) {
			$t = trim( $p );
			if ( $t !== '' ) {
				$out[] = $t;
			}
		}
		return $out;
	}

	/**
	 * Join array to comma-separated string (single space after comma optional — use ", ").
	 *
	 * @param array<int|string, string> $arr Values.
	 */
	public static function array_to_csv( array $arr ): string {
		$clean = array();
		foreach ( $arr as $v ) {
			$v = trim( (string) $v );
			if ( $v !== '' ) {
				$clean[] = $v;
			}
		}
		return implode( ', ', $clean );
	}

	/**
	 * Heuristic: enough alphabetic words to treat PDF extraction as real text (not binary garbage).
	 */
	public static function pdf_text_is_usable( string $text ): bool {
		$clean = preg_replace( '/[^a-zA-Z\s]/', '', $text );
		if ( ! is_string( $clean ) ) {
			return false;
		}
		return str_word_count( $clean ) >= 50;
	}

	/**
	 * Human-readable label for a snake_case field name.
	 */
	public static function field_label_from_snake( string $field ): string {
		return ucwords( str_replace( '_', ' ', $field ) );
	}

	/**
	 * Display name for a review queue row using normalized extraction JSON (temp_key → name/title).
	 *
	 * @param array<string, mixed>|null $normalized Decoded normalized_json from extraction.
	 */
	public static function review_queue_entity_display( string $entity_type, string $temp_key, ?array $normalized ): string {
		if ( is_array( $normalized ) && 'character' === $entity_type ) {
			foreach ( $normalized['characters'] ?? array() as $c ) {
				if ( ! is_array( $c ) ) {
					continue;
				}
				if ( ( $c['temp_key'] ?? '' ) === $temp_key ) {
					$n = trim( (string) ( $c['name'] ?? '' ) );
					return $n !== '' ? $n : __( '[No name extracted]', 'apb-sides-database' );
				}
			}
		}
		if ( is_array( $normalized ) && 'side' === $entity_type ) {
			foreach ( $normalized['sides'] ?? array() as $s ) {
				if ( ! is_array( $s ) ) {
					continue;
				}
				if ( ( $s['temp_key'] ?? '' ) === $temp_key ) {
					$t = trim( (string) ( $s['title'] ?? '' ) );
					return $t !== '' ? $t : __( '[No title extracted]', 'apb-sides-database' );
				}
			}
		}
		return $temp_key;
	}

	/**
	 * Truncate string with ellipsis.
	 */
	public static function truncate( string $str, int $length = 100 ): string {
		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
			if ( mb_strlen( $str ) <= $length ) {
				return $str;
			}
			return mb_substr( $str, 0, $length ) . '…';
		}
		if ( strlen( $str ) <= $length ) {
			return $str;
		}
		return substr( $str, 0, $length ) . '…';
	}

	/**
	 * Allowed MIME types for uploads (extension => mime).
	 *
	 * @return array<string, string>
	 */
	public static function get_allowed_mime_types(): array {
		return array(
			'pdf'  => 'application/pdf',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
		);
	}

	/**
	 * Encrypt API key (enc2: AES-256-CBC); fallback plain: prefix.
	 */
	public static function encrypt_api_key( string $key ): string {
		if ( '' === $key ) {
			return '';
		}
		if ( function_exists( 'openssl_encrypt' ) && defined( 'AUTH_KEY' ) && AUTH_KEY !== '' ) {
			$method = 'AES-256-CBC';
			$ivlen  = openssl_cipher_iv_length( $method );
			$iv     = openssl_random_pseudo_bytes( $ivlen );
			$secret = substr( hash( 'sha256', wp_salt( 'auth' ), true ), 0, 32 );
			$enc    = openssl_encrypt( $key, $method, $secret, OPENSSL_RAW_DATA, $iv );
			if ( false !== $enc ) {
				return 'enc2:' . base64_encode( $iv ) . ':' . base64_encode( $enc );
			}
		}
		return 'plain:' . $key;
	}

	/**
	 * Decrypt API key from stored format.
	 */
	public static function decrypt_api_key( string $stored ): string {
		if ( '' === $stored ) {
			return '';
		}

		if ( str_starts_with( $stored, 'enc2:' ) ) {
			$parts = explode( ':', $stored, 3 );
			if ( count( $parts ) === 3 && function_exists( 'openssl_decrypt' ) && defined( 'AUTH_KEY' ) && AUTH_KEY !== '' ) {
				$iv        = base64_decode( $parts[1], true );
				$cipher_b64 = $parts[2];
				$cipher    = base64_decode( $cipher_b64, true );
				if ( false !== $iv && false !== $cipher && strlen( $iv ) === (int) openssl_cipher_iv_length( 'AES-256-CBC' ) ) {
					$method = 'AES-256-CBC';
					$secret = substr( hash( 'sha256', wp_salt( 'auth' ), true ), 0, 32 );
					$dec    = openssl_decrypt( $cipher, $method, $secret, OPENSSL_RAW_DATA, $iv );
					return false !== $dec ? (string) $dec : '';
				}
			}
			return '';
		}

		if ( str_starts_with( $stored, 'plain:' ) ) {
			return substr( $stored, 6 );
		}

		if ( str_starts_with( $stored, 'b64:' ) ) {
			$raw = base64_decode( substr( $stored, 4 ), true );
			return false !== $raw ? (string) $raw : '';
		}

		// Legacy enc: single blob (IV + ciphertext) after prefix.
		if ( str_starts_with( $stored, 'enc:' ) && ! str_starts_with( $stored, 'enc2:' ) && function_exists( 'openssl_decrypt' ) && defined( 'AUTH_KEY' ) && AUTH_KEY !== '' ) {
			$bin = base64_decode( substr( $stored, 4 ), true );
			if ( false !== $bin && strlen( $bin ) >= 17 ) {
				$method = 'aes-256-cbc';
				$ivlen  = openssl_cipher_iv_length( $method );
				$iv     = substr( $bin, 0, $ivlen );
				$cipher = substr( $bin, $ivlen );
				$secret = substr( hash( 'sha256', AUTH_KEY ), 0, 32 );
				$dec    = openssl_decrypt( $cipher, $method, $secret, OPENSSL_RAW_DATA, $iv );
				if ( false !== $dec && $dec !== '' ) {
					return (string) $dec;
				}
			}
			$decoded = base64_decode( substr( $stored, 4 ), true );
			return false !== $decoded && $decoded !== '' ? (string) $decoded : substr( $stored, 4 );
		}

		return $stored;
	}

	/**
	 * Decrypted Anthropic API key from settings.
	 */
	public static function get_anthropic_api_key(): string {
		$stored = self::get_setting( 'anthropic_api_key', '' );
		return self::decrypt_api_key( (string) $stored );
	}
}
