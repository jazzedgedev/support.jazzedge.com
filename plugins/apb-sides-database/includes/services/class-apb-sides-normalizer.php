<?php
/**
 * Normalizes and sanitizes AI JSON output.
 *
 * Array fields (e.g. genre) may be stored as comma-separated strings for database TEXT columns.
 * Use arrays_to_csv() when normalizing for persistence and csv_to_array() when reading back.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizer for extraction payloads.
 */
class APB_Sides_Normalizer {

	/**
	 * CSV encoding for array fields destined for DB TEXT columns.
	 */
	public static function arrays_to_csv( array $arr ): string {
		$clean = array();
		foreach ( $arr as $v ) {
			if ( is_scalar( $v ) ) {
				$t = trim( (string) $v );
				if ( $t !== '' ) {
					$clean[] = $t;
				}
			}
		}
		return implode( ', ', $clean );
	}

	/**
	 * Decode CSV string to array (trim, drop empties).
	 *
	 * @return array<int, string>
	 */
	public static function csv_to_array( string $str ): array {
		return APB_Sides_Helpers::csv_to_array( $str );
	}

	/**
	 * @param array<string, mixed> $raw_decoded Decoded JSON (legacy "script" / "characters" / "sides").
	 * @return array<string, mixed>
	 */
	public static function normalize( array $raw_decoded ): array {
		if ( ! isset( $raw_decoded['script'], $raw_decoded['characters'], $raw_decoded['sides'] ) ) {
			return array(
				'script'     => array(),
				'characters' => array(),
				'sides'      => array(),
			);
		}

		$script     = is_array( $raw_decoded['script'] ) ? $raw_decoded['script'] : array();
		$norm_script = array(
			'title'  => isset( $script['title'] ) ? sanitize_text_field( (string) $script['title'] ) : '',
			'medium' => isset( $script['medium'] ) ? sanitize_text_field( (string) $script['medium'] ) : '',
			'genre'  => isset( $script['genre'] ) && is_array( $script['genre'] )
				? self::arrays_to_csv( $script['genre'] )
				: ( isset( $script['genre'] ) ? sanitize_text_field( (string) $script['genre'] ) : '' ),
		);

		$chars_in   = is_array( $raw_decoded['characters'] ) ? $raw_decoded['characters'] : array();
		$characters = array();
		foreach ( $chars_in as $c ) {
			if ( ! is_array( $c ) ) {
				continue;
			}
			$characters[] = array(
				'name'            => isset( $c['name'] ) ? sanitize_text_field( (string) $c['name'] ) : '',
				'gender'         => isset( $c['gender'] ) ? sanitize_text_field( (string) $c['gender'] ) : '',
				'age_range_label' => isset( $c['age_range_label'] ) ? sanitize_text_field( (string) $c['age_range_label'] ) : '',
			);
		}

		$char_names = array();
		foreach ( $chars_in as $c ) {
			if ( is_array( $c ) && ! empty( $c['name'] ) ) {
				$char_names[] = (string) $c['name'];
			}
		}
		$char_names_list = implode( ', ', $char_names );

		$sides_in = is_array( $raw_decoded['sides'] ) ? $raw_decoded['sides'] : array();
		$sides    = array();
		foreach ( $sides_in as $s ) {
			if ( ! is_array( $s ) ) {
				continue;
			}
			$sides[] = array(
				'title'         => isset( $s['title'] ) ? sanitize_text_field( (string) $s['title'] ) : '',
				'scene_context' => isset( $s['scene_context'] ) ? wp_kses_post( (string) $s['scene_context'] ) : '',
				'actor_notes'  => isset( $s['actor_notes'] ) ? wp_kses_post( (string) $s['actor_notes'] ) : '',
				'casting_type'  => $char_names_list,
			);
		}

		return array(
			'script'     => $norm_script,
			'characters' => $characters,
			'sides'      => $sides,
		);
	}

	/**
	 * Normalize the new simple JSON format (has "show" key).
	 * Handles both single-scene and full_script (has "scenes" array).
	 *
	 * @param array<string, mixed> $data Raw decoded JSON.
	 * @return array<string, mixed>
	 */
	public static function normalize_simple( array $data ): array {
		$show  = sanitize_text_field( (string) ( $data['show'] ?? '' ) );
		$type  = sanitize_text_field( (string) ( $data['type'] ?? 'scene' ) );
		$genre = isset( $data['genre'] ) && is_array( $data['genre'] )
			? self::arrays_to_csv( $data['genre'] )
			: sanitize_text_field( (string) ( $data['genre'] ?? '' ) );

		$script = array(
			'title'  => $show,
			'medium' => $type,
			'genre'  => $genre,
		);

		$chars_in   = isset( $data['characters'] ) && is_array( $data['characters'] ) ? $data['characters'] : array();
		$characters = array();
		foreach ( $chars_in as $c ) {
			if ( ! is_array( $c ) ) {
				continue;
			}
			$characters[] = array(
				'name'             => sanitize_text_field( (string) ( $c['name'] ?? '' ) ),
				'gender'           => sanitize_text_field( (string) ( $c['gender'] ?? '' ) ),
				'age_range_label'  => sanitize_text_field( (string) ( $c['age_range'] ?? '' ) ),
			);
		}

		$raw_scenes = array();
		if ( 'full_script' === $type && isset( $data['scenes'] ) && is_array( $data['scenes'] ) ) {
			$raw_scenes = $data['scenes'];
		} else {
			$raw_scenes[] = $data;
		}

		$sides = array();
		foreach ( $raw_scenes as $scene ) {
			if ( ! is_array( $scene ) ) {
				continue;
			}
			$ctx   = wp_kses_post( (string) ( $scene['scene_context'] ?? '' ) );
			$notes = wp_kses_post( (string) ( $scene['actor_notes'] ?? '' ) );
			$title = mb_strlen( $ctx ) > 70
				? rtrim( mb_substr( $ctx, 0, 70 ) ) . '…'
				: $ctx;
			$scene_chars = isset( $scene['characters'] ) && is_array( $scene['characters'] )
				? $scene['characters']
				: $chars_in;
			$char_names   = array_map(
				static function ( $c ) {
					if ( ! is_array( $c ) ) {
						return '';
					}
					return sanitize_text_field( (string) ( $c['name'] ?? '' ) );
				},
				$scene_chars
			);
			$sides[] = array(
				'title'         => $title,
				'scene_context' => $ctx,
				'actor_notes'   => $notes,
				'casting_type'  => implode( ', ', array_filter( $char_names ) ),
			);
		}

		return array(
			'script'     => $script,
			'characters' => $characters,
			'sides'      => $sides,
		);
	}
}
