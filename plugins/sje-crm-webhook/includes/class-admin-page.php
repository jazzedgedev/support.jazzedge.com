<?php
/**
 * Admin page for SJE CRM Webhook
 *
 * @package SJE_CRM_Webhook
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SJE_CRM_Webhook_Admin_Page {

	const MENU_SLUG = 'sje-crm-webhook';

	public function init() {
		add_action( 'admin_menu',                                    array( $this, 'add_menu_page' ) );
		add_action( 'admin_init',                                    array( $this, 'register_settings' ) );
		add_action( 'admin_post_sje_crm_webhook_purge_all',          array( $this, 'handle_purge_all' ) );
		add_action( 'admin_post_sje_crm_clear_tag',                  array( $this, 'handle_clear_tag' ) );
		add_action( 'admin_post_sje_crm_clear_tag_group',            array( $this, 'handle_clear_tag_group' ) );
		add_action( 'admin_enqueue_scripts',                         array( $this, 'enqueue_assets' ) );
	}

	public function add_menu_page() {
		$icon = 'data:image/svg+xml;base64,' . base64_encode(
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none">
				<circle cx="10" cy="10" r="3" fill="#a7aaad"/>
				<path d="M10 2 C10 2 14 5 14 10 C14 15 10 18 10 18" stroke="#a7aaad" stroke-width="1.5" fill="none" stroke-linecap="round"/>
				<path d="M10 2 C10 2 6 5 6 10 C6 15 10 18 10 18" stroke="#a7aaad" stroke-width="1.5" fill="none" stroke-linecap="round"/>
				<line x1="2" y1="10" x2="18" y2="10" stroke="#a7aaad" stroke-width="1.5" stroke-linecap="round"/>
			</svg>'
		);

		add_menu_page(
			__( 'SJE CRM', 'sje-crm-webhook' ),
			__( 'SJE CRM', 'sje-crm-webhook' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_page' ),
			$icon,
			30
		);
	}

	public function register_settings() {
		register_setting( 'sje_crm_webhook_settings', 'sje_crm_api_key', array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );
		register_setting( 'sje_crm_webhook_settings', 'sje_crm_log_retention_days', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 30,
		) );
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'sje-crm-webhook-admin',
			SJE_CRM_WEBHOOK_PLUGIN_URL . 'assets/admin.css',
			array(),
			SJE_CRM_WEBHOOK_VERSION
		);
	}

	public function handle_purge_all() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'sje-crm-webhook' ) );
		}
		check_admin_referer( 'sje_crm_webhook_purge_all' );
		SJE_CRM_Webhook_Logger::purge_all();
		wp_safe_redirect( add_query_arg( array( 'page' => self::MENU_SLUG, 'tab' => 'logs', 'purged' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public function handle_clear_tag() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'sje-crm-webhook' ) );
		}
		check_admin_referer( 'sje_crm_clear_tag' );

		$tag_id = isset( $_POST['tag_id'] ) ? (int) $_POST['tag_id'] : 0;
		if ( ! $tag_id || ! function_exists( 'FluentCrmApi' ) ) {
			wp_safe_redirect( $this->tab_url( 'tools' ) );
			exit;
		}

		$tag = FluentCrmApi( 'tags' )->find( $tag_id );
		if ( ! $tag ) {
			wp_safe_redirect( $this->tab_url( 'tools' ) );
			exit;
		}

		$subscribers = $tag->subscribers; // collection of Subscriber models
		$count       = count( $subscribers );

		foreach ( $subscribers as $contact ) {
			$contact->detachTags( array( $tag_id ) );
		}

		wp_safe_redirect( add_query_arg( array(
			'page'      => self::MENU_SLUG,
			'tab'       => 'tools',
			'cleared'   => $count,
			'tag_label' => rawurlencode( $tag->title ),
		), admin_url( 'admin.php' ) ) );
		exit;
	}

	public function handle_clear_tag_group() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'sje-crm-webhook' ) );
		}
		check_admin_referer( 'sje_crm_clear_tag_group' );

		$tag_groups = array(
			'academy_members' => array(
				'label' => 'Academy Members (All Tiers)',
				'tags'  => array( 60, 61, 62 ),
			),
		);

		$group_key = isset( $_POST['group_key'] ) ? sanitize_key( $_POST['group_key'] ) : '';
		if ( ! $group_key || ! isset( $tag_groups[ $group_key ] ) || ! function_exists( 'FluentCrmApi' ) ) {
			wp_safe_redirect( $this->tab_url( 'tools' ) );
			exit;
		}

		$group   = $tag_groups[ $group_key ];
		$tag_ids = $group['tags'];

		// Collect all unique contact IDs across all tags in the group
		$all_contact_ids = array();
		foreach ( $tag_ids as $tag_id ) {
			$tag = FluentCrmApi( 'tags' )->find( $tag_id );
			if ( ! $tag ) {
				continue;
			}
			foreach ( $tag->subscribers as $contact ) {
				$all_contact_ids[ $contact->id ] = $contact;
			}
		}

		// Remove all group tags from each contact
		foreach ( $all_contact_ids as $contact ) {
			$contact->detachTags( $tag_ids );
		}

		wp_safe_redirect( add_query_arg( array(
			'page'         => self::MENU_SLUG,
			'tab'          => 'tools',
			'bulk_cleared' => count( $all_contact_ids ),
			'bulk_group'   => rawurlencode( $group['label'] ),
		), admin_url( 'admin.php' ) ) );
		exit;
	}

	private function current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'webhook';
		return in_array( $tab, array( 'webhook', 'settings', 'logs', 'tools' ), true ) ? $tab : 'webhook';
	}

	private function tab_url( $tab ) {
		return add_query_arg( array( 'page' => self::MENU_SLUG, 'tab' => $tab ), admin_url( 'admin.php' ) );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab       = $this->current_tab();
		$api_key   = get_option( 'sje_crm_api_key', '' );
		$retention = get_option( 'sje_crm_log_retention_days', 30 );
		$endpoint  = 'https://support.jazzedge.com/wp-json/jazzedge/v1/crm';
		?>
		<div class="wrap sje-crm-webhook-admin">
			<h1>SJE CRM</h1>

			<nav class="nav-tab-wrapper" style="margin-bottom:20px;">
				<a href="<?php echo esc_url( $this->tab_url( 'webhook' ) ); ?>"
				   class="nav-tab <?php echo $tab === 'webhook'  ? 'nav-tab-active' : ''; ?>">🔗 Webhook</a>
				<a href="<?php echo esc_url( $this->tab_url( 'settings' ) ); ?>"
				   class="nav-tab <?php echo $tab === 'settings' ? 'nav-tab-active' : ''; ?>">⚙️ Settings</a>
				<a href="<?php echo esc_url( $this->tab_url( 'logs' ) ); ?>"
				   class="nav-tab <?php echo $tab === 'logs'     ? 'nav-tab-active' : ''; ?>">📋 Logs</a>
				<a href="<?php echo esc_url( $this->tab_url( 'tools' ) ); ?>"
				   class="nav-tab <?php echo $tab === 'tools' ? 'nav-tab-active' : ''; ?>">🔧 Tools</a>
			</nav>

			<?php if ( $tab === 'webhook' ) : ?>
				<?php $this->render_webhook_tab( $api_key, $endpoint ); ?>
			<?php elseif ( $tab === 'settings' ) : ?>
				<?php $this->render_settings_tab( $api_key, $retention ); ?>
			<?php elseif ( $tab === 'tools' ) : ?>
				<?php $this->render_tools_tab(); ?>
			<?php else : ?>
				<?php $this->render_logs_tab(); ?>
			<?php endif; ?>
		</div>

		<?php $this->render_scripts( $tab, $api_key ); ?>
		<?php
	}

	private function render_webhook_tab( $api_key, $endpoint ) {
		$wp_config_snippet = "define( 'JE_CRM_ENDPOINT', '" . $endpoint . "' );\n"
			. "define( 'JE_CRM_API_KEY', '" . str_replace( array( '\\', "'" ), array( '\\\\', "\\'" ), $api_key ) . "' );";
		?>
			<!-- Endpoint URL -->
			<div class="sje-panel" style="background:#fff; border:1px solid #c3c4c7; box-shadow:0 1px 1px rgba(0,0,0,.04); border-radius:3px; padding:20px; margin-bottom:20px;">
				<h2 style="margin-top:0;">📡 Endpoint URL</h2>
				<p>All POST requests (from JE sites or Keap) go to this single endpoint:</p>
				<div style="display:flex; align-items:center; gap:10px;">
					<code id="sje-endpoint-url" style="font-size:14px; padding:8px 12px; background:#f0f0f0; border-radius:4px; flex:1;">
						<?php echo esc_html( $endpoint ); ?>
					</code>
					<button type="button" class="button" id="sje-copy-endpoint">Copy</button>
					<span id="sje-endpoint-copied" style="display:none; color:#1a7f37; font-weight:600;">Copied!</span>
				</div>
			</div>

			<!-- JE Sites (PHP) -->
			<div class="sje-panel" style="background:#fff; border:1px solid #c3c4c7; box-shadow:0 1px 1px rgba(0,0,0,.04); border-radius:3px; padding:20px; margin-bottom:20px;">
				<h2 style="margin-top:0;">🖥️ JE Sites — wp-config.php Constants</h2>
				<p>Add these two lines to <code>wp-config.php</code> on any site sending data to this endpoint. The <code>JE_CRM_Sender</code> class reads them automatically.</p>
				<pre id="sje-wpconfig-constants-pre" style="background:#f0f0f0; padding:12px; border-radius:4px; overflow-x:auto;"><?php echo esc_html( $wp_config_snippet ); ?></pre>
				<p>
					<button type="button" class="button" id="sje-wpconfig-constants-copy">Copy to Clipboard</button>
					<span id="sje-wpconfig-constants-copied" style="display:none; margin-left:8px; color:#1a7f37; font-weight:600;">Copied!</span>
				</p>
				<h3>Supported Payload Fields</h3>
				<table class="widefat">
					<thead><tr><th>Field</th><th>Type</th><th>Description</th></tr></thead>
					<tbody>
						<tr><td><code>email</code></td><td>string <em>(required)</em></td><td>Contact email — e.g. <code>john@example.com</code></td></tr>
						<tr><td><code>first_name</code></td><td>string</td><td>First name — e.g. <code>John</code></td></tr>
						<tr><td><code>last_name</code></td><td>string</td><td>Last name — e.g. <code>Smith</code></td></tr>
						<tr><td><code>status</code></td><td>string</td><td><code>subscribed</code>, <code>unsubscribed</code>, <code>pending</code></td></tr>
						<tr><td><code>add_tags</code></td><td>int[]</td><td>JSON body: <code>[122, 151]</code> &nbsp;|&nbsp; Query param: <code>122,151</code></td></tr>
						<tr><td><code>remove_tags</code></td><td>int[]</td><td>JSON body: <code>[122, 151]</code> &nbsp;|&nbsp; Query param: <code>122,151</code></td></tr>
						<tr><td><code>add_lists</code></td><td>int[]</td><td>JSON body: <code>[3, 7]</code> &nbsp;|&nbsp; Query param: <code>3,7</code></td></tr>
						<tr><td><code>remove_lists</code></td><td>int[]</td><td>JSON body: <code>[3, 7]</code> &nbsp;|&nbsp; Query param: <code>3,7</code></td></tr>
						<tr><td><code>custom_fields</code></td><td>object</td><td>JSON body only — e.g. <code>{"keap_id": "12345", "last_badge_earned": "Blues Scale"}</code></td></tr>
						<tr><td><code>cf_<em>fieldname</em></code></td><td>string</td><td>Query param shorthand for custom fields — e.g. <code>cf_keap_id=12345</code> sets <code>custom_fields['keap_id']</code>. Use one param per field.</td></tr>
						<tr><td><code>api_key</code></td><td>string <em>(required)</em></td><td>Shared secret from Settings tab</td></tr>
					</tbody>
				</table>
			</div>

			<!-- Keap HTTP Actions -->
			<div class="sje-panel" style="background:#fff; border:1px solid #c3c4c7; box-shadow:0 1px 1px rgba(0,0,0,.04); border-radius:3px; padding:20px; margin-bottom:20px;">
				<h2 style="margin-top:0;">⚙️ Keap HTTP Actions</h2>
				<p>Keap cannot use merge fields inside a JSON body. Use <strong>Query Parameters</strong> instead — no body or Content-Type header needed.</p>
				<table class="widefat" style="margin-bottom:16px;">
					<thead><tr><th>Setting</th><th>Value</th></tr></thead>
					<tbody>
						<tr><td><strong>Method</strong></td><td>POST</td></tr>
						<tr><td><strong>Target URL</strong></td><td><code><?php echo esc_html( $endpoint ); ?></code></td></tr>
						<tr><td><strong>Body</strong></td><td><em>Leave empty</em></td></tr>
					</tbody>
				</table>
				<strong>Query Parameters:</strong>
				<table class="widefat" style="margin-top:8px;">
					<thead><tr><th>Name</th><th>Value</th></tr></thead>
					<tbody>
						<tr><td><code>email</code></td><td><code>[[contact.email1.address]]</code></td></tr>
						<tr><td><code>add_tags</code></td><td><code>153,154</code> <em>(comma-separated IDs)</em></td></tr>
						<tr><td><code>remove_tags</code></td><td><code>153</code> <em>(comma-separated IDs)</em></td></tr>
						<tr><td><code>api_key</code></td><td>your key from Settings tab</td></tr>
					</tbody>
				</table>
			</div>
		<?php
	}

	private function render_settings_tab( $api_key, $retention ) {
		?>
			<div class="sje-panel" style="background:#fff; border:1px solid #c3c4c7; box-shadow:0 1px 1px rgba(0,0,0,.04); border-radius:3px; padding:20px; margin-bottom:20px;">
				<h2 style="margin-top:0;">⚙️ Settings</h2>
				<form method="post" action="options.php" id="sje-crm-webhook-settings-form">
					<?php settings_fields( 'sje_crm_webhook_settings' ); ?>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="sje_crm_api_key"><?php esc_html_e( 'API Key', 'sje-crm-webhook' ); ?></label></th>
							<td>
								<div class="sje-api-key-wrap" style="display:flex; gap:8px; align-items:center;">
									<input type="password" id="sje_crm_api_key" name="sje_crm_api_key"
										value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" autocomplete="off"/>
									<button type="button" class="button sje-toggle-visibility">Show</button>
									<button type="button" class="button sje-regenerate-key">Regenerate</button>
									<button type="button" class="button sje-copy-key">Copy Key</button>
									<span class="sje-key-copied" style="display:none; color:#1a7f37; font-weight:600;">Copied!</span>
								</div>
								<p class="description">Shared secret — include as <code>api_key</code> in every request or as a query parameter.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="sje_crm_log_retention_days"><?php esc_html_e( 'Log Retention Days', 'sje-crm-webhook' ); ?></label></th>
							<td>
								<input type="number" id="sje_crm_log_retention_days" name="sje_crm_log_retention_days"
									value="<?php echo esc_attr( $retention ); ?>" min="1" max="365" class="small-text"/>
								<p class="description">Logs older than this are purged automatically by daily cron.</p>
							</td>
						</tr>
					</table>
					<?php submit_button( 'Save Settings', 'primary', 'submit', false ); ?>
				</form>
			</div>
		<?php
	}

	private function render_tools_tab() {
		// Fetch all FluentCRM tags
		$tags = array();
		if ( function_exists( 'FluentCrmApi' ) ) {
			$tags = FluentCrmApi( 'tags' )->all();
		}

		$cleared_count = isset( $_GET['cleared'] ) ? (int) $_GET['cleared'] : -1;
		$cleared_tag   = isset( $_GET['tag_label'] ) ? sanitize_text_field( urldecode( $_GET['tag_label'] ) ) : '';
		?>
		<div class="sje-panel" style="background:#fff; border:1px solid #c3c4c7; box-shadow:0 1px 1px rgba(0,0,0,.04); border-radius:3px; padding:20px; margin-bottom:20px;">
			<h2 style="margin-top:0;">🏷️ Clear All Contacts from a Tag</h2>
			<p>Select a FluentCRM tag and remove it from every contact currently tagged with it. Use this to reset a tag before sending a mass Keap HTTP post.</p>

			<?php if ( $cleared_count >= 0 ) : ?>
				<div class="notice notice-success is-dismissible" style="margin:0 0 16px;">
					<p>Done — removed tag <strong><?php echo esc_html( $cleared_tag ); ?></strong> from <strong><?php echo $cleared_count; ?></strong> contact(s).</p>
				</div>
			<?php endif; ?>

			<?php if ( empty( $tags ) ) : ?>
				<p><em>No FluentCRM tags found.</em></p>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
					  onsubmit="return confirm('Remove this tag from ALL contacts? This cannot be undone.');">
					<?php wp_nonce_field( 'sje_crm_clear_tag' ); ?>
					<input type="hidden" name="action" value="sje_crm_clear_tag"/>

					<table class="form-table" style="max-width:600px;">
						<tr>
							<th scope="row"><label for="sje_clear_tag_id">Tag</label></th>
							<td>
								<select id="sje_clear_tag_id" name="tag_id" style="min-width:300px;">
									<option value="">— Select a tag —</option>
									<?php foreach ( $tags as $tag ) : ?>
										<option value="<?php echo esc_attr( $tag->id ); ?>">
											<?php echo esc_html( $tag->title ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>

					<p>
						<button type="submit" class="button button-primary" style="background:#d63638; border-color:#d63638;">
							Remove Tag from All Contacts
						</button>
					</p>
				</form>
			<?php endif; ?>
		</div>

		<div class="sje-panel" style="background:#fff; border:1px solid #c3c4c7; box-shadow:0 1px 1px rgba(0,0,0,.04); border-radius:3px; padding:20px; margin-bottom:20px;">
			<h2 style="margin-top:0;">⚡ Bulk Tag Group Clear</h2>
			<p>Remove an entire group of related tags from all contacts in a single operation.</p>

			<?php
			$bulk_cleared = isset( $_GET['bulk_cleared'] ) ? (int) $_GET['bulk_cleared'] : -1;
			$bulk_group   = isset( $_GET['bulk_group'] ) ? sanitize_text_field( urldecode( $_GET['bulk_group'] ) ) : '';
			if ( $bulk_cleared >= 0 ) : ?>
				<div class="notice notice-success is-dismissible" style="margin:0 0 16px;">
					<p>Done — removed <strong><?php echo esc_html( $bulk_group ); ?></strong> tags from <strong><?php echo $bulk_cleared; ?></strong> contact(s).</p>
				</div>
			<?php endif; ?>

			<?php
			$tag_groups = array(
				'academy_members' => array(
					'label'       => 'Academy Members (All Tiers)',
					'description' => 'Clears all three Academy membership level tags — use before a mass Keap HTTP post to re-tag members by tier.',
					'tags'        => array(
						60 => 'Academy Member (Essentials)',
						61 => 'Academy Member (Studio)',
						62 => 'Academy Member (Premier)',
					),
				),
			);
			foreach ( $tag_groups as $group_key => $group ) : ?>
				<div style="border:1px solid #dcdcde; border-radius:4px; padding:16px; margin-bottom:16px; max-width:700px;">
					<strong style="font-size:14px;"><?php echo esc_html( $group['label'] ); ?></strong>
					<p style="margin:6px 0 10px; color:#646970;"><?php echo esc_html( $group['description'] ); ?></p>
					<ul style="margin:0 0 12px 16px;">
						<?php foreach ( $group['tags'] as $tid => $tlabel ) : ?>
							<li><code><?php echo (int) $tid; ?></code> — <?php echo esc_html( $tlabel ); ?></li>
						<?php endforeach; ?>
					</ul>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
						  onsubmit="return confirm('Remove ALL <?php echo esc_js( $group['label'] ); ?> tags from every contact? This cannot be undone.');">
						<?php wp_nonce_field( 'sje_crm_clear_tag_group' ); ?>
						<input type="hidden" name="action"    value="sje_crm_clear_tag_group"/>
						<input type="hidden" name="group_key" value="<?php echo esc_attr( $group_key ); ?>"/>
						<button type="submit" class="button button-primary" style="background:#d63638; border-color:#d63638;">
							Remove All <?php echo esc_html( $group['label'] ); ?> Tags
						</button>
					</form>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	private function render_logs_tab() {
		$page        = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page    = 20;
		$logs_data   = SJE_CRM_Webhook_Logger::get_logs( $per_page, $page );
		$logs        = $logs_data['items'];
		$total       = $logs_data['total'];
		$total_pages = (int) ceil( $total / $per_page );

		if ( isset( $_GET['purged'] ) && $_GET['purged'] === '1' ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'All logs have been purged.', 'sje-crm-webhook' ) . '</p></div>';
		}
		?>
			<p>
				<strong>Total logs: <?php echo (int) $total; ?></strong>
				<?php if ( $total > 0 ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block; margin-left:15px;">
						<?php wp_nonce_field( 'sje_crm_webhook_purge_all' ); ?>
						<input type="hidden" name="action" value="sje_crm_webhook_purge_all"/>
						<button type="submit" class="button"
							onclick="return confirm('Are you sure you want to purge all logs? This cannot be undone.');">
							Purge All Logs
						</button>
					</form>
				<?php endif; ?>
			</p>

			<?php if ( empty( $logs ) ) : ?>
				<p>No logs yet.</p>
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
									<button type="button" class="button button-small sje-payload-toggle"
										data-log-id="<?php echo esc_attr( $log['id'] ); ?>">View</button>
									<pre class="sje-payload-content" id="sje-payload-<?php echo esc_attr( $log['id'] ); ?>"
										style="display:none; white-space:pre-wrap; word-break:break-all;"><?php echo esc_html( $log['payload'] ); ?></pre>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<?php
							echo paginate_links( array(
								'base'      => add_query_arg( 'paged', '%#%', $this->tab_url( 'logs' ) ),
								'format'    => '',
								'prev_text' => '&laquo;',
								'next_text' => '&raquo;',
								'total'     => $total_pages,
								'current'   => $page,
							) );
							?>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php
	}

	private function render_scripts( $tab, $api_key ) {
		?>
		<script>
		(function() {
			// Copy helper
			function copyText(text, confirmedEl) {
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(function() {
						if (confirmedEl) {
							confirmedEl.style.display = 'inline';
							setTimeout(function() { confirmedEl.style.display = 'none'; }, 2000);
						}
					}).catch(function(){});
				}
			}

			// Endpoint URL copy
			var epBtn = document.getElementById('sje-copy-endpoint');
			if (epBtn) {
				epBtn.addEventListener('click', function() {
					copyText(<?php echo wp_json_encode( 'https://support.jazzedge.com/wp-json/jazzedge/v1/crm' ); ?>, document.getElementById('sje-endpoint-copied'));
				});
			}

			// wp-config copy
			var wcBtn = document.getElementById('sje-wpconfig-constants-copy');
			var wcPre = document.getElementById('sje-wpconfig-constants-pre');
			if (wcBtn && wcPre) {
				wcBtn.addEventListener('click', function() {
					copyText(wcPre.textContent || '', document.getElementById('sje-wpconfig-constants-copied'));
				});
			}

			// API key show/hide
			var toggleBtn = document.querySelector('.sje-toggle-visibility');
			var apiInput  = document.getElementById('sje_crm_api_key');
			if (toggleBtn && apiInput) {
				toggleBtn.addEventListener('click', function() {
					var show = apiInput.type === 'password';
					apiInput.type     = show ? 'text' : 'password';
					toggleBtn.textContent = show ? 'Hide' : 'Show';
				});
			}

			// Regenerate key
			var regenBtn = document.querySelector('.sje-regenerate-key');
			if (regenBtn && apiInput) {
				regenBtn.addEventListener('click', function() {
					var hex = '0123456789abcdef', key = '';
					for (var i = 0; i < 32; i++) key += hex[Math.floor(Math.random() * 16)];
					apiInput.value = key;
					apiInput.type  = 'text';
					if (toggleBtn) toggleBtn.textContent = 'Hide';
				});
			}

			// Copy key button
			var copyKeyBtn = document.querySelector('.sje-copy-key');
			var keyCopied  = document.querySelector('.sje-key-copied');
			if (copyKeyBtn && apiInput) {
				copyKeyBtn.addEventListener('click', function() {
					copyText(apiInput.value, keyCopied);
				});
			}

			// Payload toggle
			document.querySelectorAll('.sje-payload-toggle').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var pre = document.getElementById('sje-payload-' + this.getAttribute('data-log-id'));
					if (pre) {
						var show = pre.style.display === 'none';
						pre.style.display  = show ? 'block' : 'none';
						this.textContent   = show ? 'Hide' : 'View';
					}
				});
			});
		})();
		</script>
		<?php
	}
}
