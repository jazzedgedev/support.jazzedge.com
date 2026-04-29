<?php
/**
 * Public shortcodes.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes for search and detail views.
 */
class APB_Sides_Shortcodes {

	public function apb_sides_register(): void {
		add_shortcode( 'apb_sides_search', array( $this, 'render_search' ) );
		add_shortcode( 'apb_side_detail', array( $this, 'render_side_detail' ) );
		add_shortcode( 'apb_script_detail', array( $this, 'render_script_detail' ) );
	}

	/**
	 * @param array<string, string>|string $atts Attributes.
	 */
	public function render_search( $atts ): string {
		APB_Sides_Frontend::apb_sides_enqueue_assets();
		global $wpdb;
		$pub = APB_Sides_Statuses::PUBLISHED;

		// Distinct shows (script id + title) for dropdown.
		$shows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title FROM {$wpdb->prefix}apb_sides_scripts WHERE status = %s ORDER BY title ASC",
				$pub
			),
			ARRAY_A
		);

		// Distinct genres from scripts.
		$genres = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT genre FROM {$wpdb->prefix}apb_sides_scripts WHERE status = %s AND genre IS NOT NULL AND genre != '' ORDER BY genre ASC",
				$pub
			)
		);

		// Distinct mediums from scripts.
		$mediums = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT medium FROM {$wpdb->prefix}apb_sides_scripts WHERE status = %s AND medium IS NOT NULL AND medium != '' ORDER BY medium ASC",
				$pub
			)
		);

		// Distinct genders from characters (via published sides).
		$genders = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT ch.gender
			 FROM {$wpdb->prefix}apb_sides_characters ch
			 JOIN {$wpdb->prefix}apb_sides_side_characters pv ON pv.character_id = ch.id
			 JOIN {$wpdb->prefix}apb_sides_sides si ON si.id = pv.side_id
			 WHERE si.status = %s AND ch.gender IS NOT NULL AND ch.gender != ''
			 ORDER BY ch.gender ASC",
				$pub
			)
		);

		$data = array(
			'filters' => array(
				'shows'   => is_array( $shows ) ? $shows : array(),
				'genres'  => is_array( $genres ) ? $genres : array(),
				'mediums' => is_array( $mediums ) ? $mediums : array(),
				'genders' => is_array( $genders ) ? $genders : array(),
			),
		);

		ob_start();
		include APB_SIDES_PLUGIN_DIR . 'templates/frontend/search.php';
		return (string) ob_get_clean();
	}

	/**
	 * @param array<string, string>|string $atts Attributes.
	 */
	public function render_script_detail( $atts ): string {
		APB_Sides_Frontend::apb_sides_enqueue_assets();
		$atts = is_array( $atts ) ? $atts : array();
		$id    = absint( $atts['id'] ?? 0 ) ?: absint( isset( $_GET['script_id'] ) ? wp_unslash( $_GET['script_id'] ) : 0 );
		if ( ! $id ) {
			return '';
		}
		$repo   = new APB_Sides_Script_Repo();
		$script = $repo->get_by_id( $id );
		if ( ! $script ) {
			return '';
		}
		$sides = ( new APB_Sides_Side_Repo() )->get_published_by_script_id( $id );
		ob_start();
		$data = array(
			'script' => $script,
			'sides'  => $sides,
		);
		include APB_SIDES_PLUGIN_DIR . 'templates/frontend/script-detail.php';
		return (string) ob_get_clean();
	}

	/**
	 * @param array<string, string> $atts Attributes.
	 */
	public function render_side_detail( $atts ): string {
		APB_Sides_Frontend::apb_sides_enqueue_assets();
		$atts = shortcode_atts( array( 'id' => '0' ), $atts, 'apb_side_detail' );
		$id   = absint( $atts['id'] )
			?: absint( isset( $_GET['side_id'] ) ? wp_unslash( $_GET['side_id'] ) : 0 );
		$repo  = new APB_Sides_Side_Repo();
		$side  = $repo->get_by_id( $id );
		if ( ! $side ) {
			return '';
		}
		$can_see = ( (string) $side['status'] === APB_Sides_Statuses::PUBLISHED ) || current_user_can( 'manage_options' );
		if ( ! $can_see ) {
			return '';
		}

		$script_repo = new APB_Sides_Script_Repo();
		$pivot_repo  = new APB_Sides_Side_Character_Repo();
		$script      = $script_repo->get_by_id( (int) $side['script_id'] );
		$characters  = $pivot_repo->get_characters_for_side( $id );

		$data = array(
			'side'       => $side,
			'script'     => $script,
			'characters' => $characters,
		);

		ob_start();
		include APB_SIDES_PLUGIN_DIR . 'templates/frontend/side-detail.php';
		return (string) ob_get_clean();
	}
}
