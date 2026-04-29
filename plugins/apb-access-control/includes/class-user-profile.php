<?php
/**
 * User profile: FluentCRM contact, tags, add-to-CRM.
 *
 * @package APB_Access_Control
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class APB_User_Profile
 */
class APB_User_Profile {

	/**
	 * Bootstrap hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'edit_user_profile', [ __CLASS__, 'render_section' ] );
		add_action( 'show_user_profile', [ __CLASS__, 'render_section' ] );
		add_action( 'edit_user_profile_update', [ __CLASS__, 'save_section' ] );
		add_action( 'personal_options_update', [ __CLASS__, 'save_section' ] );
		add_action( 'admin_post_apb_add_to_fluentcrm', [ __CLASS__, 'handle_add_to_crm' ] );
		add_action( 'admin_notices', [ __CLASS__, 'admin_notices' ] );
	}

	/**
	 * Success / error notices after Add to FluentCRM redirect.
	 *
	 * @return void
	 */
	public static function admin_notices(): void {
		if ( isset( $_GET['apb_crm_added'] ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'User added to FluentCRM.', 'apb-access-control' ) . '</p></div>';
		}
		if ( isset( $_GET['apb_crm_error'] ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to add user to FluentCRM. Check that FluentCRM is active and the email is valid.', 'apb-access-control' ) . '</p></div>';
		}
	}

	/**
	 * Render FluentCRM section on user edit screens.
	 *
	 * @param WP_User $user User object.
	 * @return void
	 */
	public static function render_section( WP_User $user ): void {
		if ( ! class_exists( '\FluentCrm\App\Models\Tag' ) ) {
			return;
		}
		if ( ! function_exists( 'FluentCrmApi' ) ) {
			return;
		}

		$contact = FluentCrmApi( 'contacts' )->getContactByUserId( $user->ID );
		$all_tags = \FluentCrm\App\Models\Tag::all();

		echo '<div class="apb-fluentcrm-access">';
		echo '<h2>' . esc_html__( 'FluentCRM Access', 'apb-access-control' ) . '</h2>';

		if ( ! $contact ) {
			$add_url = wp_nonce_url(
				add_query_arg(
					[
						'action'  => 'apb_add_to_fluentcrm',
						'user_id' => $user->ID,
					],
					admin_url( 'admin-post.php' )
				),
				'apb_add_to_crm_' . $user->ID,
				'apb_crm_nonce'
			);
			?>
			<table class="form-table">
				<tr>
					<th><?php echo esc_html__( 'FluentCRM', 'apb-access-control' ); ?></th>
					<td>
						<p><?php echo esc_html__( 'This user is not in FluentCRM.', 'apb-access-control' ); ?></p>
						<a href="<?php echo esc_url( $add_url ); ?>" class="button button-secondary"><?php echo esc_html__( 'Add to FluentCRM', 'apb-access-control' ); ?></a>
					</td>
				</tr>
			</table>
			<?php
			echo '</div>';
			return;
		}

		$contact_tag_ids = $contact->tags->pluck( 'id' )->toArray();
		?>
		<table class="form-table">
			<tr>
				<th><?php echo esc_html__( 'Status', 'apb-access-control' ); ?></th>
				<td><?php echo esc_html( (string) $contact->status ); ?></td>
			</tr>
			<tr>
				<th><?php echo esc_html__( 'Tags', 'apb-access-control' ); ?></th>
				<td>
					<?php wp_nonce_field( 'apb_save_tags_' . $user->ID, 'apb_tags_nonce' ); ?>
					<?php
					foreach ( $all_tags as $tag ) {
						if ( ! is_object( $tag ) || ! isset( $tag->id ) ) {
							continue;
						}
						$tag_id = (int) $tag->id;
						$title  = isset( $tag->title ) ? (string) $tag->title : '';
						?>
						<label>
							<input type="checkbox" name="apb_crm_tags[]" value="<?php echo esc_attr( (string) $tag_id ); ?>"
								<?php checked( in_array( $tag_id, $contact_tag_ids, true ) ); ?>>
							<?php echo esc_html( $title ); ?>
						</label><br>
						<?php
					}
					?>
				</td>
			</tr>
		</table>
		<?php
		echo '</div>';
	}

	/**
	 * Save FluentCRM tags from profile form.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function save_section( int $user_id ): void {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		if ( ! isset( $_POST['apb_tags_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( wp_unslash( $_POST['apb_tags_nonce'] ), 'apb_save_tags_' . $user_id ) ) {
			return;
		}
		if ( ! class_exists( '\FluentCrm\App\Models\Tag' ) || ! function_exists( 'FluentCrmApi' ) ) {
			return;
		}

		$contact = FluentCrmApi( 'contacts' )->getContactByUserId( $user_id );
		if ( ! $contact ) {
			return;
		}

		$new_tag_ids = array_map( 'intval', wp_unslash( $_POST['apb_crm_tags'] ?? [] ) );
		$new_tag_ids = array_values( array_filter( $new_tag_ids ) );

		$all_tag_ids = \FluentCrm\App\Models\Tag::all()->pluck( 'id' )->toArray();

		$contact->detachTags( $all_tag_ids );
		if ( ! empty( $new_tag_ids ) ) {
			$contact->attachTags( $new_tag_ids );
		}

		foreach ( \FluentCrm\App\Models\Tag::all() as $tag ) {
			if ( ! is_object( $tag ) || ! isset( $tag->slug ) ) {
				continue;
			}
			$slug = (string) $tag->slug;
			delete_user_meta( $user_id, '_apb_access_' . $slug );
			delete_user_meta( $user_id, '_apb_access_' . $slug . '_expires' );
		}
	}

	/**
	 * Create FluentCRM contact from WP user (admin-post handler).
	 *
	 * @return void
	 */
	public static function handle_add_to_crm(): void {
		$user_id = isset( $_GET['user_id'] ) ? intval( wp_unslash( $_GET['user_id'] ) ) : 0;
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_die( esc_html__( 'Unauthorized', 'apb-access-control' ) );
		}
		if ( ! wp_verify_nonce( wp_unslash( $_GET['apb_crm_nonce'] ?? '' ), 'apb_add_to_crm_' . $user_id ) ) {
			wp_die( esc_html__( 'Bad nonce', 'apb-access-control' ) );
		}
		if ( ! function_exists( 'FluentCrmApi' ) ) {
			wp_die( esc_html__( 'FluentCRM is not available.', 'apb-access-control' ) );
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			wp_die( esc_html__( 'Invalid user.', 'apb-access-control' ) );
		}

		$result = FluentCrmApi( 'contacts' )->createOrUpdate(
			[
				'email'      => $user->user_email,
				'first_name' => $user->first_name ?: $user->display_name,
				'last_name'  => $user->last_name,
				'status'     => 'subscribed',
				'user_id'    => $user_id,
			]
		);

		if ( ! $result ) {
			wp_safe_redirect(
				esc_url_raw(
					add_query_arg(
						[
							'user_id'       => $user_id,
							'apb_crm_error' => '1',
						],
						admin_url( 'user-edit.php' )
					)
				)
			);
			exit;
		}

		wp_safe_redirect(
			esc_url_raw(
				add_query_arg(
					[
						'user_id'       => $user_id,
						'apb_crm_added' => '1',
					],
					admin_url( 'user-edit.php' )
				)
			)
		);
		exit;
	}
}
