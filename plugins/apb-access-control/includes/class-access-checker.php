<?php
/**
 * Front-end gate: FluentCRM tag + user meta cache.
 *
 * @package APB_Access_Control
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class APB_Access_Checker
 */
class APB_Access_Checker {

	/**
	 * Default redirect if option missing (matches settings default).
	 */
	private const DEFAULT_REDIRECT = '/join-the-actorplaybook-community/';

	/**
	 * Bootstrap hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'template_redirect', [ __CLASS__, 'maybe_gate_page' ], 10 );
		add_action( 'fluentcrm_contact_tags_detached', [ __CLASS__, 'clear_cache_on_detach' ], 10, 2 );
		add_action( 'wp_login', [ __CLASS__, 'clear_cache_on_login' ], 10, 2 );
	}

	/**
	 * Clear access cache when FluentCRM detaches tags from a contact.
	 *
	 * @param mixed $contact Contact model.
	 * @param mixed $tag_ids Detached tag IDs (unused; all tag keys cleared).
	 * @return void
	 */
	public static function clear_cache_on_detach( $contact, $tag_ids ): void {
		if ( ! class_exists( '\FluentCrm\App\Models\Tag' ) ) {
			return;
		}
		if ( ! is_object( $contact ) || empty( $contact->user_id ) ) {
			return;
		}
		$wp_user_id = absint( $contact->user_id );
		if ( ! $wp_user_id ) {
			return;
		}

		foreach ( \FluentCrm\App\Models\Tag::all() as $tag ) {
			if ( ! is_object( $tag ) || ! isset( $tag->slug ) ) {
				continue;
			}
			$slug = (string) $tag->slug;
			delete_user_meta( $wp_user_id, '_apb_access_' . $slug );
			delete_user_meta( $wp_user_id, '_apb_access_' . $slug . '_expires' );
		}
	}

	/**
	 * Clear access cache on login so tag checks always refresh for a new session.
	 *
	 * @param string  $user_login Username.
	 * @param WP_User $user       User object.
	 * @return void
	 */
	public static function clear_cache_on_login( string $user_login, WP_User $user ): void {
		if ( ! class_exists( '\FluentCrm\App\Models\Tag' ) ) {
			return;
		}

		foreach ( \FluentCrm\App\Models\Tag::all() as $tag ) {
			if ( ! is_object( $tag ) || ! isset( $tag->slug ) ) {
				continue;
			}
			$slug = (string) $tag->slug;
			delete_user_meta( $user->ID, '_apb_access_' . $slug );
			delete_user_meta( $user->ID, '_apb_access_' . $slug . '_expires' );
		}
	}

	/**
	 * Gate singular pages that require a tag.
	 *
	 * @return void
	 */
	public static function maybe_gate_page(): void {
		if ( ! is_singular( 'page' ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		$tag_slug = get_post_meta( $post_id, APB_Meta_Box::META_KEY, true );
		if ( ! is_string( $tag_slug ) || '' === trim( $tag_slug ) ) {
			return;
		}
		$tag_slug = sanitize_text_field( $tag_slug );

		if ( ! is_user_logged_in() ) {
			self::redirect_denied();
		}

		if ( ! self::user_has_access( $tag_slug ) ) {
			self::redirect_denied();
		}
	}

	/**
	 * Whether the current user has the required tag (cached or via FluentCRM).
	 *
	 * @param string $tag_slug FluentCRM tag slug.
	 * @return bool
	 */
	public static function user_has_access( string $tag_slug ): bool {
		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			return false;
		}

		$cache_key   = self::cache_flag_meta_key( $tag_slug );
		$expires_key = self::cache_expires_meta_key( $tag_slug );

		$cached = get_user_meta( $user_id, $cache_key, true );
		$expires = get_user_meta( $user_id, $expires_key, true );

		if ( '1' === (string) $cached && self::is_future_timestamp( $expires ) ) {
			return true;
		}

		if ( ! function_exists( 'FluentCrmApi' ) ) {
			delete_user_meta( $user_id, $cache_key );
			delete_user_meta( $user_id, $expires_key );
			return false;
		}

		$contact = FluentCrmApi( 'contacts' )->getCurrentContact();
		if ( ! $contact ) {
			delete_user_meta( $user_id, $cache_key );
			delete_user_meta( $user_id, $expires_key );
			return false;
		}

		$has_tag = $contact->tags->contains( 'slug', $tag_slug );

		if ( $has_tag ) {
			update_user_meta( $user_id, $cache_key, '1' );
			update_user_meta( $user_id, $expires_key, (string) ( time() + DAY_IN_SECONDS ) );
			return true;
		}

		delete_user_meta( $user_id, $cache_key );
		delete_user_meta( $user_id, $expires_key );
		return false;
	}

	/**
	 * Redirect to denied URL and exit.
	 *
	 * @return void
	 */
	private static function redirect_denied(): void {
		$url = get_option( 'apb_access_redirect_url', self::DEFAULT_REDIRECT );
		if ( ! is_string( $url ) ) {
			$url = self::DEFAULT_REDIRECT;
		}
		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * User meta key for cached access flag.
	 *
	 * @param string $tag_slug Tag slug.
	 * @return string
	 */
	private static function cache_flag_meta_key( string $tag_slug ): string {
		return '_apb_access_' . $tag_slug;
	}

	/**
	 * User meta key for cache expiry.
	 *
	 * @param string $tag_slug Tag slug.
	 * @return string
	 */
	private static function cache_expires_meta_key( string $tag_slug ): string {
		return '_apb_access_' . $tag_slug . '_expires';
	}

	/**
	 * Whether stored value is a unix timestamp in the future.
	 *
	 * @param mixed $value Stored expires value.
	 * @return bool
	 */
	private static function is_future_timestamp( $value ): bool {
		if ( ! is_numeric( $value ) ) {
			return false;
		}
		$ts = (int) $value;
		return $ts > time();
	}
}
