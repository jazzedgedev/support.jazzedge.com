<?php
/**
 * Admin page for SJE CRM Webhook
 *
 * Settings and log viewer.
 *
 * @package SJE_CRM_Webhook
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SJE_CRM_Webhook_Admin_Page {

	/**
	 * Menu slug
	 *
	 * @var string
	 */
	const MENU_SLUG = 'sje-crm-webhook';

	/**
	 * Initialize admin
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_sje_crm_webhook_purge_all', array( $this, 'handle_purge_all' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add menu page under Settings
	 */
	public function add_menu_page() {
		add_options_page(
			__( 'CRM Webhook', 'sje-crm-webhook' ),
			__( 'CRM Webhook', 'sje-crm-webhook' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'sje_crm_webhook_settings', 'sje_crm_api_key', array(
			'type'              => 'string',
			'sanitize_callback'  => 'sanitize_text_field',
			'default'           => '',
		) );

		register_setting( 'sje_crm_webhook_settings', 'sje_crm_log_retention_days', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 30,
		) );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_assets( $hook ) {
		if ( 'settings_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'sje-crm-webhook-admin',
			SJE_CRM_WEBHOOK_PLUGIN_URL . 'assets/admin.css',
			array(),
			SJE_CRM_WEBHOOK_VERSION
		);
	}

	/**
	 * Handle purge all logs action
	 */
	public function handle_purge_all() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'sje-crm-webhook' ) );
		}

		check_admin_referer( 'sje_crm_webhook_purge_all' );

		SJE_CRM_Webhook_Logger::purge_all();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => self::MENU_SLUG,
					'purged'  => '1',
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Render the admin page
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 20;
		$logs_data = SJE_CRM_Webhook_Logger::get_logs( $per_page, $page );
		$logs      = $logs_data['items'];
		$total     = $logs_data['total'];
		$total_pages = (int) ceil( $total / $per_page );

		$api_key = get_option( 'sje_crm_api_key', '' );
		$retention = get_option( 'sje_crm_log_retention_days', 30 );

		// Success notice for purge
		if ( isset( $_GET['purged'] ) && $_GET['purged'] === '1' ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'All logs have been purged.', 'sje-crm-webhook' ) . '</p></div>';
		}
		?>
		<div class="wrap sje-crm-webhook-admin">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<!-- Section 1: Settings -->
			<h2><?php esc_html_e( 'Settings', 'sje-crm-webhook' ); ?></h2>
			<form method="post" action="options.php" id="sje-crm-webhook-settings-form">
				<?php settings_fields( 'sje_crm_webhook_settings' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="sje_crm_api_key"><?php esc_html_e( 'API Key', 'sje-crm-webhook' ); ?></label>
						</th>
						<td>
							<div class="sje-api-key-wrap">
								<input type="password"
									   id="sje_crm_api_key"
									   name="sje_crm_api_key"
									   value="<?php echo esc_attr( $api_key ); ?>"
									   class="regular-text"
									   autocomplete="off" />
								<button type="button" class="button sje-toggle-visibility" aria-label="<?php esc_attr_e( 'Show/Hide', 'sje-crm-webhook' ); ?>">
									<?php esc_html_e( 'Show', 'sje-crm-webhook' ); ?>
								</button>
								<button type="button" class="button sje-regenerate-key">
									<?php esc_html_e( 'Regenerate Key', 'sje-crm-webhook' ); ?>
								</button>
							</div>
							<p class="description">
								<?php esc_html_e( 'Include this key in the api_key field of your webhook payload.', 'sje-crm-webhook' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sje_crm_log_retention_days"><?php esc_html_e( 'Log Retention Days', 'sje-crm-webhook' ); ?></label>
						</th>
						<td>
							<input type="number"
								   id="sje_crm_log_retention_days"
								   name="sje_crm_log_retention_days"
								   value="<?php echo esc_attr( $retention ); ?>"
								   min="1"
								   max="365"
								   class="small-text" />
							<p class="description">
								<?php esc_html_e( 'Logs older than this many days are automatically purged (daily cron).', 'sje-crm-webhook' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'sje-crm-webhook' ), 'primary', 'submit', false ); ?>
			</form>

			<?php
			$api_key_for_define = is_string( $api_key ) ? $api_key : '';
			$wp_config_constants_snippet = "define( 'JE_CRM_ENDPOINT', 'https://support.jazzedge.com/wp-json/jazzedge/v1/crm' );\n"
				. 'define( \'JE_CRM_API_KEY\', \'' . str_replace( array( '\\', "'" ), array( '\\\\', "\\'" ), $api_key_for_define ) . "' );";
			?>
			<div class="sje-wpconfig-constants-panel" style="margin-top: 24px;">
				<h3><?php esc_html_e( 'wp-config.php Constants', 'sje-crm-webhook' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'Add these two lines to wp-config.php on any site that needs to send data to this CRM endpoint.', 'sje-crm-webhook' ); ?>
				</p>
				<pre id="sje-wpconfig-constants-pre" class="sje-wpconfig-constants-pre"><?php echo esc_html( $wp_config_constants_snippet ); ?></pre>
				<p style="margin-top: 10px;">
					<button type="button" class="button" id="sje-wpconfig-constants-copy">
						<?php esc_html_e( 'Copy to Clipboard', 'sje-crm-webhook' ); ?>
					</button>
					<span id="sje-wpconfig-constants-copied" class="sje-wpconfig-constants-copied" style="display: none; margin-left: 8px; color: #1a7f37; font-weight: 600;" aria-live="polite">
						<?php esc_html_e( 'Copied!', 'sje-crm-webhook' ); ?>
					</span>
				</p>
			</div>

			<!-- Section 2: Logs -->
			<h2><?php esc_html_e( 'Logs', 'sje-crm-webhook' ); ?></h2>
			<p>
				<strong><?php
					/* translators: %d: number of log entries */
					echo esc_html( sprintf( __( 'Total logs: %d', 'sje-crm-webhook' ), $total ) );
				?></strong>
				<?php if ( $total > 0 ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block; margin-left: 15px;">
						<?php wp_nonce_field( 'sje_crm_webhook_purge_all' ); ?>
						<input type="hidden" name="action" value="sje_crm_webhook_purge_all" />
						<button type="submit" class="button" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to purge all logs? This cannot be undone.', 'sje-crm-webhook' ) ); ?>');">
							<?php esc_html_e( 'Purge All Logs', 'sje-crm-webhook' ); ?>
						</button>
					</form>
				<?php endif; ?>
			</p>

			<?php if ( empty( $logs ) ) : ?>
				<p><?php esc_html_e( 'No logs yet.', 'sje-crm-webhook' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date/Time', 'sje-crm-webhook' ); ?></th>
							<th><?php esc_html_e( 'Email', 'sje-crm-webhook' ); ?></th>
							<th><?php esc_html_e( 'Source IP', 'sje-crm-webhook' ); ?></th>
							<th><?php esc_html_e( 'Status', 'sje-crm-webhook' ); ?></th>
							<th><?php esc_html_e( 'Message', 'sje-crm-webhook' ); ?></th>
							<th><?php esc_html_e( 'Payload', 'sje-crm-webhook' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><?php echo esc_html( $log['created_at'] ); ?></td>
								<td><?php echo esc_html( $log['email'] ); ?></td>
								<td><?php echo esc_html( $log['source_ip'] ); ?></td>
								<td>
									<span class="sje-status-<?php echo esc_attr( $log['status'] ); ?>">
										<?php echo esc_html( $log['status'] ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $log['message'] ); ?></td>
								<td>
									<button type="button" class="button button-small sje-payload-toggle" data-log-id="<?php echo esc_attr( $log['id'] ); ?>">
										<?php esc_html_e( 'View', 'sje-crm-webhook' ); ?>
									</button>
									<pre class="sje-payload-content" id="sje-payload-<?php echo esc_attr( $log['id'] ); ?>" style="display: none;"><?php echo esc_html( $log['payload'] ); ?></pre>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<span class="pagination-links">
								<?php
								$base_url = add_query_arg( 'page', self::MENU_SLUG, admin_url( 'options-general.php' ) );
								echo paginate_links( array(
									'base'      => add_query_arg( 'paged', '%#%', $base_url ),
									'format'    => '',
									'prev_text' => '&laquo;',
									'next_text' => '&raquo;',
									'total'     => $total_pages,
									'current'   => $page,
								) );
								?>
							</span>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<script>
		(function() {
			// API Key Show/Hide toggle
			var toggleBtn = document.querySelector('.sje-toggle-visibility');
			var apiKeyInput = document.getElementById('sje_crm_api_key');
			if (toggleBtn && apiKeyInput) {
				toggleBtn.addEventListener('click', function() {
					if (apiKeyInput.type === 'password') {
						apiKeyInput.type = 'text';
						toggleBtn.textContent = '<?php echo esc_js( __( 'Hide', 'sje-crm-webhook' ) ); ?>';
					} else {
						apiKeyInput.type = 'password';
						toggleBtn.textContent = '<?php echo esc_js( __( 'Show', 'sje-crm-webhook' ) ); ?>';
					}
				});
			}

			// Regenerate Key
			var regenBtn = document.querySelector('.sje-regenerate-key');
			if (regenBtn && apiKeyInput) {
				regenBtn.addEventListener('click', function() {
					var hex = '0123456789abcdef';
					var key = '';
					for (var i = 0; i < 32; i++) {
						key += hex.charAt(Math.floor(Math.random() * 16));
					}
					apiKeyInput.value = key;
					apiKeyInput.type = 'text';
					if (toggleBtn) toggleBtn.textContent = '<?php echo esc_js( __( 'Hide', 'sje-crm-webhook' ) ); ?>';
				});
			}

			// wp-config constants copy
			var wpconfigPre = document.getElementById('sje-wpconfig-constants-pre');
			var wpconfigCopyBtn = document.getElementById('sje-wpconfig-constants-copy');
			var wpconfigCopied = document.getElementById('sje-wpconfig-constants-copied');
			if (wpconfigCopyBtn && wpconfigPre) {
				wpconfigCopyBtn.addEventListener('click', function() {
					var text = wpconfigPre.textContent || '';
					if (navigator.clipboard && navigator.clipboard.writeText) {
						navigator.clipboard.writeText(text).then(function() {
							if (wpconfigCopied) {
								wpconfigCopied.style.display = 'inline';
								setTimeout(function() {
									wpconfigCopied.style.display = 'none';
								}, 2000);
							}
						}).catch(function() {});
					}
				});
			}

			// Payload View toggle
			document.querySelectorAll('.sje-payload-toggle').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var id = this.getAttribute('data-log-id');
					var pre = document.getElementById('sje-payload-' + id);
					if (pre) {
						pre.style.display = pre.style.display === 'none' ? 'block' : 'none';
						this.textContent = pre.style.display === 'none' ? '<?php echo esc_js( __( 'View', 'sje-crm-webhook' ) ); ?>' : '<?php echo esc_js( __( 'Hide', 'sje-crm-webhook' ) ); ?>';
					}
				});
			});
		})();
		</script>
		<?php
	}
}
