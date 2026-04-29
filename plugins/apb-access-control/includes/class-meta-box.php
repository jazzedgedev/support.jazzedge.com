<?php
/**
 * Page meta box: required FluentCRM tag.
 *
 * @package APB_Access_Control
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class APB_Meta_Box
 */
class APB_Meta_Box {

	public const META_KEY = '_apb_required_tag';

	/**
	 * Bootstrap hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'add_meta_boxes', [ __CLASS__, 'register_meta_box' ] );
		add_action( 'save_post', [ __CLASS__, 'save_post' ], 10, 2 );
		add_action( 'admin_notices', [ __CLASS__, 'maybe_fluentcrm_notice' ] );
	}

	/**
	 * Show notice when FluentCRM API is unavailable.
	 *
	 * @return void
	 */
	public static function maybe_fluentcrm_notice(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'page' !== $screen->post_type || 'post' !== $screen->base ) {
			return;
		}
		if ( class_exists( '\FluentCrm\App\Models\Tag' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>';
		echo esc_html__( 'APB Access Control: FluentCRM is not active. Tag list cannot be loaded.', 'apb-access-control' );
		echo '</p></div>';
	}

	/**
	 * Register Access Control meta box on pages.
	 *
	 * @return void
	 */
	public static function register_meta_box(): void {
		add_meta_box(
			'apb_access_control',
			'Access Control',
			[ __CLASS__, 'render_meta_box' ],
			'page',
			'side',
			'default'
		);
	}

	/**
	 * Render meta box content.
	 *
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public static function render_meta_box( $post ): void {
		wp_nonce_field( 'apb_access_save', 'apb_access_nonce' );

		if ( ! class_exists( '\FluentCrm\App\Models\Tag' ) ) {
			echo '<p>' . esc_html__( 'FluentCRM is not active.', 'apb-access-control' ) . '</p>';
			return;
		}

		$current = get_post_meta( $post->ID, self::META_KEY, true );
		if ( ! is_string( $current ) ) {
			$current = '';
		}

		echo '<p><label for="apb_required_tag">';
		echo esc_html__( 'Required FluentCRM tag', 'apb-access-control' );
		echo '</label></p>';
		echo '<select name="apb_required_tag" id="apb_required_tag" class="widefat">';

		$blank_label = esc_html__( '— Public (no restriction) —', 'apb-access-control' );
		printf(
			'<option value=""%s>%s</option>',
			selected( $current, '', false ),
			$blank_label
		);

		$tags = \FluentCrm\App\Models\Tag::all();
		foreach ( $tags as $tag ) {
			if ( ! is_object( $tag ) ) {
				continue;
			}
			$slug  = isset( $tag->slug ) ? (string) $tag->slug : '';
			$title = isset( $tag->title ) ? (string) $tag->title : $slug;
			if ( '' === $slug ) {
				continue;
			}
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $slug ),
				selected( $current, $slug, false ),
				esc_html( $title )
			);
		}

		echo '</select>';
	}

	/**
	 * Save required tag meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public static function save_post( $post_id, $post ): void {
		if ( ! $post || 'page' !== $post->post_type ) {
			return;
		}
		if ( ! isset( $_POST['apb_access_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( wp_unslash( $_POST['apb_access_nonce'] ), 'apb_access_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['apb_required_tag'] ) ) {
			return;
		}

		$value = sanitize_text_field( wp_unslash( $_POST['apb_required_tag'] ) );
		if ( '' === $value ) {
			delete_post_meta( $post_id, self::META_KEY );
			return;
		}

		update_post_meta( $post_id, self::META_KEY, $value );
	}
}
