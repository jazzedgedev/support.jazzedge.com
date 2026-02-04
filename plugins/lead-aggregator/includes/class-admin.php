<?php
if (!defined('ABSPATH')) {
    exit;
}

class Lead_Aggregator_Admin {
    private $database;
    private $permissions;

    public function __construct($database, $permissions) {
        $this->database = $database;
        $this->permissions = $permissions;

        if (is_admin()) {
            add_action('admin_menu', array($this, 'register_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
            add_action('admin_post_lead_aggregator_save_settings', array($this, 'save_settings'));
            add_action('admin_post_lead_aggregator_add_webhook', array($this, 'add_webhook_source'));
            add_action('admin_post_lead_aggregator_update_webhook', array($this, 'update_webhook_source'));
            add_action('admin_post_lead_aggregator_delete_webhook', array($this, 'delete_webhook_source'));
            add_action('admin_post_lead_aggregator_grant_access', array($this, 'grant_access'));
            add_action('admin_post_lead_aggregator_manage_user', array($this, 'manage_user'));
            add_action('admin_post_lead_aggregator_create_user', array($this, 'create_user'));
            add_action('admin_post_lead_aggregator_delete_user', array($this, 'delete_user'));
            add_action('admin_post_lead_aggregator_clear_email_logs', array($this, 'clear_email_logs'));
            add_action('admin_post_lead_aggregator_clear_webhook_logs', array($this, 'clear_webhook_logs'));
        }
    }

    public function register_menu() {
        add_menu_page(
            'Lead Aggregator',
            'Lead Aggregator',
            'manage_options',
            'lead-aggregator',
            array($this, 'settings_page'),
            'dashicons-groups',
            32
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'lead-aggregator') === false) {
            return;
        }

        wp_enqueue_media();
        wp_add_inline_script(
            'jquery',
            'jQuery(function($){' .
            'var frame;' .
            '$("#lead-aggregator-upload-logo").on("click", function(e){' .
                'e.preventDefault();' .
                'if (frame) { frame.open(); return; }' .
                'frame = wp.media({ title: "Select Logo", button: { text: "Use this logo" }, multiple: false });' .
                'frame.on("select", function(){' .
                    'var attachment = frame.state().get("selection").first().toJSON();' .
                    '$("#lead-aggregator-app-logo-id").val(attachment.id);' .
                    '$(".lead-aggregator-logo-preview").html("<img src=\'"+attachment.url+"\' style=\'max-width:200px;height:auto;\' />");' .
                '});' .
                'frame.open();' .
            '});' .
            '$("#lead-aggregator-remove-logo").on("click", function(e){' .
                'e.preventDefault();' .
                '$("#lead-aggregator-app-logo-id").val("");' .
                '$(".lead-aggregator-logo-preview").empty();' .
            '});' .
            '});'
        );
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $notifications_enabled = (int) get_option('lead_aggregator_notify_enabled', 1);
        $app_mode = (int) get_option('lead_aggregator_app_mode', 1);
        $app_menu_id = (int) get_option('lead_aggregator_app_menu_id', 0);
        $app_logo_id = (int) get_option('lead_aggregator_app_logo_id', 0);
        $footer_enabled = (int) get_option('lead_aggregator_app_footer_enabled', 0);
        $footer_text = get_option('lead_aggregator_app_footer_text', '');
        $webhook_logging = (int) get_option('lead_aggregator_webhook_logging', 1);
        $stripe_publishable_key = get_option('lead_aggregator_stripe_publishable_key', '');
        $stripe_secret_key = get_option('lead_aggregator_stripe_secret_key', '');
        $stripe_webhook_secret = get_option('lead_aggregator_stripe_webhook_secret', '');
        $stripe_success_url = get_option('lead_aggregator_stripe_success_url', '');
        $stripe_cancel_url = get_option('lead_aggregator_stripe_cancel_url', '');
        $stripe_plans = get_option('lead_aggregator_plans', array());
        $webhooks = $this->database->get_webhook_sources();
        $menus = wp_get_nav_menus();
        $current_user_id = isset($_GET['lead_user']) ? (int) $_GET['lead_user'] : 0;
        $lead_users = $this->database->get_lead_users();
        $admin_leads = $current_user_id ? $this->database->get_leads($current_user_id) : array();
        $email_logs = $this->database->get_email_logs(200);
        $followup_leads = $this->database->get_scheduled_followups();
        $webhook_logs = $this->database->get_webhook_logs(200);
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'shortcodes';
        $all_users = get_users(array(
            'fields' => array('ID', 'display_name', 'user_email'),
            'number' => 500,
        ));
        $manager_options = array();
        foreach ($all_users as $user) {
            $manager_id = (int) get_user_meta($user->ID, 'lead_aggregator_manager_id', true);
            if ($manager_id === 0) {
                $manager_options[$user->ID] = $user;
            }
        }
        $tabs = array(
            'shortcodes' => 'Shortcodes',
            'settings' => 'Settings',
            'manage-users' => 'Manage Users',
            'webhooks' => 'Webhooks',
            'leads' => 'Leads',
            'email-log' => 'Email Log',
            'followups' => 'Follow-ups',
            'webhook-log' => 'Webhook Logs',
        );

        ?>
        <div class="wrap">
            <h1>Lead Aggregator Settings</h1>

            <h2 class="nav-tab-wrapper">
                <?php foreach ($tabs as $tab_key => $label) : ?>
                    <?php
                    $tab_url = add_query_arg(
                        array(
                            'page' => 'lead-aggregator',
                            'tab' => $tab_key,
                        ),
                        admin_url('admin.php')
                    );
                    ?>
                    <a href="<?php echo esc_url($tab_url); ?>" class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </h2>

            <?php if ($current_tab === 'shortcodes') : ?>
                <table class="widefat striped" style="margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[lead_aggregator_inbox]</code></td>
                            <td>Lead inbox and pipeline overview</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_lead_form]</code></td>
                            <td>Manual lead add form</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_lead_detail]</code></td>
                            <td>Single lead view (supports <code>lead_id</code> attribute)</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_calendar]</code></td>
                            <td>Follow-up and due date calendar view</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_manage_tags]</code></td>
                            <td>Manage tags</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_export]</code></td>
                            <td>CSV export download link</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_business_profile]</code></td>
                            <td>Business overview profile form</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_login]</code></td>
                            <td>Front-end login form</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_dashboard]</code></td>
                            <td>All-in-one dashboard with tabs</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_register]</code></td>
                            <td>Front-end account registration form</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_pricing]</code></td>
                            <td>Pricing and checkout view</td>
                        </tr>
                        <tr>
                            <td><code>[lead_aggregator_billing]</code></td>
                            <td>Billing management for logged-in users</td>
                        </tr>
                    </tbody>
                </table>
            <?php elseif ($current_tab === 'settings') : ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('lead_aggregator_save_settings', 'lead_aggregator_nonce'); ?>
                    <input type="hidden" name="action" value="lead_aggregator_save_settings">

                    <h2>Notifications</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Follow-up Emails</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="notify_enabled" value="1" <?php checked($notifications_enabled, 1); ?>>
                                    Enable follow-up reminders
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Webhook Logging</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="webhook_logging" value="1" <?php checked($webhook_logging, 1); ?>>
                                    Log incoming webhook requests
                                </label>
                                <p class="description">Disable once you are done testing.</p>
                            </td>
                        </tr>
                    </table>

                    <h2>Stripe Billing</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Publishable Key</th>
                            <td>
                                <input type="text" name="stripe_publishable_key" value="<?php echo esc_attr($stripe_publishable_key); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Secret Key</th>
                            <td>
                                <input type="text" name="stripe_secret_key" value="<?php echo esc_attr($stripe_secret_key); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Webhook Secret</th>
                            <td>
                                <input type="text" name="stripe_webhook_secret" value="<?php echo esc_attr($stripe_webhook_secret); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Success URL</th>
                            <td>
                                <input type="url" name="stripe_success_url" value="<?php echo esc_attr($stripe_success_url); ?>" class="regular-text" />
                                <p class="description">Where users land after successful payment.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Cancel URL</th>
                            <td>
                                <input type="url" name="stripe_cancel_url" value="<?php echo esc_attr($stripe_cancel_url); ?>" class="regular-text" />
                            </td>
                        </tr>
                    </table>

                    <h3>Plan Configuration</h3>
                    <table class="widefat striped" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Contact Limit</th>
                                <th>Monthly Price ID</th>
                                <th>Annual Price ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $default_plans = array(
                                'starter' => array('label' => 'Starter', 'limit' => 100),
                                'growth' => array('label' => 'Growth', 'limit' => 500),
                                'pro' => array('label' => 'Pro', 'limit' => 2000),
                            );
                            foreach ($default_plans as $plan_key => $plan_defaults) :
                                $plan_settings = isset($stripe_plans[$plan_key]) && is_array($stripe_plans[$plan_key]) ? $stripe_plans[$plan_key] : array();
                                $label = isset($plan_settings['label']) ? $plan_settings['label'] : $plan_defaults['label'];
                                $limit = isset($plan_settings['limit']) ? (int) $plan_settings['limit'] : $plan_defaults['limit'];
                                $monthly_price = isset($plan_settings['monthly_price_id']) ? $plan_settings['monthly_price_id'] : '';
                                $annual_price = isset($plan_settings['annual_price_id']) ? $plan_settings['annual_price_id'] : '';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($label); ?></strong>
                                        <input type="hidden" name="stripe_plans[<?php echo esc_attr($plan_key); ?>][label]" value="<?php echo esc_attr($label); ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" name="stripe_plans[<?php echo esc_attr($plan_key); ?>][limit]" value="<?php echo esc_attr($limit); ?>" />
                                    </td>
                                    <td>
                                        <input type="text" name="stripe_plans[<?php echo esc_attr($plan_key); ?>][monthly_price_id]" value="<?php echo esc_attr($monthly_price); ?>" class="regular-text" />
                                    </td>
                                    <td>
                                        <input type="text" name="stripe_plans[<?php echo esc_attr($plan_key); ?>][annual_price_id]" value="<?php echo esc_attr($annual_price); ?>" class="regular-text" />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h2>App Mode</h2>
                    <table class="form-table">
                    <tr>
                        <th scope="row">Dashboard App Mode</th>
                        <td>
                            <label>
                                <input type="checkbox" name="app_mode" value="1" <?php checked($app_mode, 1); ?>>
                                Disable theme styles and render dashboard as app page
                            </label>
                            <p class="description">Turn this off to show the WordPress menu and footer on the dashboard page.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">App Menu</th>
                        <td>
                            <select name="app_menu_id">
                                <option value="0">No menu</option>
                                <?php foreach ($menus as $menu) : ?>
                                    <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($app_menu_id, (int) $menu->term_id); ?>>
                                        <?php echo esc_html($menu->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Select a WordPress menu to show in the app header.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">App Logo</th>
                        <td>
                            <div class="lead-aggregator-logo-field">
                                <input type="hidden" name="app_logo_id" id="lead-aggregator-app-logo-id" value="<?php echo esc_attr($app_logo_id); ?>">
                                <button type="button" class="button" id="lead-aggregator-upload-logo">Choose Logo</button>
                                <button type="button" class="button" id="lead-aggregator-remove-logo">Remove</button>
                                <div class="lead-aggregator-logo-preview">
                                    <?php if ($app_logo_id) : ?>
                                        <?php echo wp_get_attachment_image($app_logo_id, 'medium'); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">App Footer</th>
                        <td>
                            <label>
                                <input type="checkbox" name="app_footer_enabled" value="1" <?php checked($footer_enabled, 1); ?>>
                                Show footer in app mode
                            </label>
                            <textarea name="app_footer_text" rows="3" class="large-text"><?php echo esc_textarea($footer_text); ?></textarea>
                        </td>
                    </tr>
                    </table>

                    <?php submit_button('Save Settings'); ?>
                </form>

                <h2>Manual Access</h2>
                <p class="description">Grant or revoke access for a user when billing is handled manually.</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('lead_aggregator_grant_access', 'lead_aggregator_grant_access_nonce'); ?>
                    <input type="hidden" name="action" value="lead_aggregator_grant_access">
                    <table class="form-table">
                        <tr>
                            <th scope="row">User Email</th>
                            <td><input type="email" name="user_email" required class="regular-text" placeholder="user@example.com"></td>
                        </tr>
                        <tr>
                            <th scope="row">Access</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="access_enabled" value="1" checked>
                                    Grant access (active)
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Access Level</th>
                            <td>
                                <select name="access_level">
                                    <option value="full">Full access</option>
                                    <option value="read">Read-only</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Update Access'); ?>
                </form>
            <?php elseif ($current_tab === 'manage-users') : ?>
                <h2>Manage Users</h2>
                <p class="description">Manage manager and sub-account access, roles, and passwords.</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 12px 0;">
                    <?php wp_nonce_field('lead_aggregator_create_user', 'lead_aggregator_create_user_nonce'); ?>
                    <input type="hidden" name="action" value="lead_aggregator_create_user">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Name</th>
                            <td><input type="text" name="name" class="regular-text" placeholder="Jane Smith"></td>
                        </tr>
                        <tr>
                            <th scope="row">Email</th>
                            <td><input type="email" name="email" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row">Type</th>
                            <td>
                                <select name="manager_id">
                                    <option value="0">Manager</option>
                                    <?php foreach ($manager_options as $manager) : ?>
                                        <option value="<?php echo esc_attr($manager->ID); ?>"><?php echo esc_html($manager->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Access</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="access_enabled" value="1" checked>
                                    Active
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Access Level</th>
                            <td>
                                <select name="access_level">
                                    <option value="full">Full</option>
                                    <option value="read">Read-only</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Add User'); ?>
                </form>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Manager</th>
                            <th>Access</th>
                            <th>Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_users)) : ?>
                            <tr>
                                <td colspan="7">No users found.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($all_users as $user) : ?>
                                <?php
                                $manager_id = (int) get_user_meta($user->ID, 'lead_aggregator_manager_id', true);
                                $access_enabled = (int) get_user_meta($user->ID, 'lead_aggregator_access_enabled', true);
                                $access_level = get_user_meta($user->ID, 'lead_aggregator_access_level', true) ?: 'full';
                                $type_label = $manager_id > 0 ? 'Sub-account' : 'Manager';
                                $temp_password = get_user_meta($user->ID, 'lead_aggregator_temp_password', true);
                                ?>
                                <tr>
                                    <td>
                                        <?php echo esc_html($user->display_name); ?>
                                        <?php if ($temp_password) : ?>
                                            <div class="description">Temp password: <strong><?php echo esc_html($temp_password); ?></strong></div>
                                            <?php delete_user_meta($user->ID, 'lead_aggregator_temp_password'); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($user->user_email); ?></td>
                                    <td><?php echo esc_html($type_label); ?></td>
                                    <td>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                            <?php wp_nonce_field('lead_aggregator_manage_user', 'lead_aggregator_manage_user_nonce'); ?>
                                            <input type="hidden" name="action" value="lead_aggregator_manage_user">
                                            <input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>">
                                            <select name="manager_id">
                                                <option value="0" <?php selected($manager_id, 0); ?>>Manager</option>
                                                <?php foreach ($manager_options as $manager) : ?>
                                                    <option value="<?php echo esc_attr($manager->ID); ?>" <?php selected($manager_id, $manager->ID); ?>>
                                                        <?php echo esc_html($manager->display_name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                    </td>
                                    <td>
                                            <label>
                                                <input type="checkbox" name="access_enabled" value="1" <?php checked($access_enabled, 1); ?>>
                                                Active
                                            </label>
                                    </td>
                                    <td>
                                            <select name="access_level">
                                                <option value="full" <?php selected($access_level, 'full'); ?>>Full</option>
                                                <option value="read" <?php selected($access_level, 'read'); ?>>Read-only</option>
                                            </select>
                                    </td>
                                    <td>
                                            <label style="margin-right: 8px;">
                                                <input type="checkbox" name="reset_password" value="1">
                                                Reset password
                                            </label>
                                            <label style="margin-right: 8px;">
                                                <input type="checkbox" name="delete_user" value="1">
                                                Delete user
                                            </label>
                                            <?php submit_button('Save', 'secondary', '', false); ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif ($current_tab === 'webhooks') : ?>
            <?php if (empty($webhooks)) : ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('lead_aggregator_add_webhook', 'lead_aggregator_webhook_nonce'); ?>
                    <input type="hidden" name="action" value="lead_aggregator_add_webhook">
                    <p class="description">A shared secret is required to post leads. We will generate one for you.</p>
                    <?php submit_button('Create Webhook'); ?>
                </form>
            <?php endif; ?>

                <?php if (!empty($webhooks)) : ?>
                    <table class="widefat striped" style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th>Webhook Endpoint</th>
                                <th>Shared Secret</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($webhooks as $webhook) : ?>
                                <?php
                                $endpoint = trailingslashit(rest_url('lead-aggregator/v1/webhooks/user/' . $webhook['webhook_token']));
                                ?>
                                <tr>
                                    <td><code><?php echo esc_html($endpoint); ?></code></td>
                                    <td>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                            <?php wp_nonce_field('lead_aggregator_update_webhook', 'lead_aggregator_update_webhook_nonce'); ?>
                                            <input type="hidden" name="action" value="lead_aggregator_update_webhook">
                                            <input type="hidden" name="webhook_id" value="<?php echo esc_attr($webhook['id']); ?>">
                                            <input type="text" name="shared_secret" value="<?php echo esc_attr($webhook['shared_secret']); ?>" readonly>
                                            <input type="hidden" name="regenerate" value="1">
                                            <?php submit_button('Refresh', 'secondary', '', false); ?>
                                        </form>
                                    </td>
                                    <td><?php echo $webhook['is_active'] ? 'Active' : 'Inactive'; ?></td>
                                    <td>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                            <?php wp_nonce_field('lead_aggregator_delete_webhook', 'lead_aggregator_delete_nonce'); ?>
                                            <input type="hidden" name="action" value="lead_aggregator_delete_webhook">
                                            <input type="hidden" name="webhook_id" value="<?php echo esc_attr($webhook['id']); ?>">
                                            <?php submit_button('Delete', 'delete', '', false); ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php elseif ($current_tab === 'email-log') : ?>
                <h2>Email Log</h2>
                <p class="description">Recent follow-up reminder emails sent by the system. Emails run hourly when follow-up or due dates are at or past the current time (and notifications are enabled).</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 12px 0;">
                    <?php wp_nonce_field('lead_aggregator_clear_email_logs', 'lead_aggregator_clear_email_logs_nonce'); ?>
                    <input type="hidden" name="action" value="lead_aggregator_clear_email_logs">
                    <?php submit_button('Clear Email Logs', 'delete', '', false); ?>
                </form>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($email_logs)) : ?>
                            <tr>
                                <td colspan="5">No emails logged yet.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($email_logs as $log) : ?>
                                <?php
                                $meta = array();
                                if (!empty($log['meta'])) {
                                    $decoded = json_decode($log['meta'], true);
                                    if (is_array($decoded)) {
                                        $meta = $decoded;
                                    }
                                }
                                $detail_bits = array();
                                if (!empty($meta['lead_count'])) {
                                    $detail_bits[] = 'Leads: ' . (int) $meta['lead_count'];
                                }
                                if (!empty($log['error_message'])) {
                                    $detail_bits[] = 'Error: ' . $log['error_message'];
                                }
                                $details = $detail_bits ? implode(' | ', $detail_bits) : '—';
                                ?>
                                <tr>
                                    <td><?php echo esc_html($log['created_at']); ?></td>
                                    <td><?php echo esc_html($log['recipient_email']); ?></td>
                                    <td><?php echo esc_html($log['subject']); ?></td>
                                    <td><?php echo esc_html($log['status']); ?></td>
                                    <td><?php echo esc_html($details); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif ($current_tab === 'followups') : ?>
                <h2>Follow-ups Scheduled</h2>
                <p class="description">All leads with follow-up or due dates set.</p>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Followup</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($followup_leads)) : ?>
                            <tr>
                                <td colspan="7">No follow-ups due.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($followup_leads as $lead) : ?>
                                <?php
                                $owner = get_user_by('id', (int) $lead['user_id']);
                                $owner_label = $owner ? $owner->display_name . ' (' . $owner->user_email . ')' : 'User #' . (int) $lead['user_id'];
                                $lead_name = trim($lead['first_name'] . ' ' . $lead['last_name']);
                                if (!$lead_name) {
                                    $lead_name = 'Lead #' . (int) $lead['id'];
                                }
                                ?>
                                <tr>
                                    <td><?php echo esc_html($lead_name); ?></td>
                                    <td><?php echo esc_html($owner_label); ?></td>
                                    <td><?php echo esc_html($lead['email']); ?></td>
                                    <td><?php echo esc_html($lead['followup_at']); ?></td>
                                    <td><?php echo esc_html($lead['due_at']); ?></td>
                                    <td><?php echo esc_html($lead['followup_status']); ?></td>
                                    <td><?php echo esc_html($lead['source']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php elseif ($current_tab === 'leads') : ?>
                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                    <input type="hidden" name="page" value="lead-aggregator">
                    <input type="hidden" name="tab" value="leads">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Select User</th>
                            <td>
                                <select name="lead_user">
                                    <option value="0">Choose a user</option>
                                    <?php foreach ($lead_users as $lead_user) : ?>
                                        <option value="<?php echo esc_attr($lead_user['user_id']); ?>" <?php selected($current_user_id, (int) $lead_user['user_id']); ?>>
                                            <?php echo esc_html($lead_user['display_name'] . ' (' . $lead_user['user_email'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php submit_button('View Leads', 'secondary', '', false); ?>
                            </td>
                        </tr>
                    </table>
                </form>

                <?php if ($current_user_id && empty($admin_leads)) : ?>
                    <p>No leads found for this user.</p>
                <?php elseif (!empty($admin_leads)) : ?>
                    <table class="widefat striped" style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Pipeline Stage</th>
                                <th>Followup</th>
                                <th>Due</th>
                                <th>Source</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admin_leads as $lead) : ?>
                                <tr>
                                    <td><?php echo esc_html(trim($lead['first_name'] . ' ' . $lead['last_name'])); ?></td>
                                    <td><?php echo esc_html($lead['email']); ?></td>
                                    <td><?php echo esc_html($this->format_pipeline_stage($lead['status'])); ?></td>
                                    <td><?php echo esc_html($this->format_admin_datetime($lead['followup_at'])); ?></td>
                                    <td><?php echo esc_html($this->format_admin_datetime($lead['due_at'])); ?></td>
                                    <td><?php echo esc_html($lead['source']); ?></td>
                                    <td><?php echo esc_html($this->format_admin_datetime($lead['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php elseif ($current_tab === 'webhook-log') : ?>
                <h2>Webhook Logs</h2>
                <p class="description">Recent incoming webhook payloads.</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 12px 0;">
                    <?php wp_nonce_field('lead_aggregator_clear_webhook_logs', 'lead_aggregator_clear_webhook_logs_nonce'); ?>
                    <input type="hidden" name="action" value="lead_aggregator_clear_webhook_logs">
                    <?php submit_button('Clear Webhook Logs', 'delete', '', false); ?>
                </form>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Payload</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($webhook_logs)) : ?>
                            <tr>
                                <td colspan="6">No webhook logs yet.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($webhook_logs as $log) : ?>
                                <?php
                                $owner = $log['user_id'] ? get_user_by('id', (int) $log['user_id']) : null;
                                $owner_label = $owner ? $owner->display_name . ' (' . $owner->user_email . ')' : '—';
                                $payload = $log['payload'] ? $log['payload'] : '';
                                $payload_preview = $payload ? wp_trim_words($payload, 40, '…') : '—';
                                ?>
                                <tr>
                                    <td><?php echo esc_html($log['created_at']); ?></td>
                                    <td><?php echo esc_html($owner_label); ?></td>
                                    <td><?php echo esc_html($log['source_key']); ?></td>
                                    <td><?php echo esc_html($log['status']); ?></td>
                                    <td><?php echo esc_html($log['message']); ?></td>
                                    <td><code><?php echo esc_html($payload_preview); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    public function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_save_settings', 'lead_aggregator_nonce');

        $notify_enabled = isset($_POST['notify_enabled']) ? 1 : 0;
        $app_mode = isset($_POST['app_mode']) ? 1 : 0;
        $app_menu_id = isset($_POST['app_menu_id']) ? (int) $_POST['app_menu_id'] : 0;
        $app_logo_id = isset($_POST['app_logo_id']) ? (int) $_POST['app_logo_id'] : 0;
        $footer_enabled = isset($_POST['app_footer_enabled']) ? 1 : 0;
        $footer_text = isset($_POST['app_footer_text']) ? wp_kses_post($_POST['app_footer_text']) : '';
        $webhook_logging = isset($_POST['webhook_logging']) ? 1 : 0;
        $stripe_publishable_key = isset($_POST['stripe_publishable_key']) ? sanitize_text_field($_POST['stripe_publishable_key']) : '';
        $stripe_secret_key = isset($_POST['stripe_secret_key']) ? sanitize_text_field($_POST['stripe_secret_key']) : '';
        $stripe_webhook_secret = isset($_POST['stripe_webhook_secret']) ? sanitize_text_field($_POST['stripe_webhook_secret']) : '';
        $stripe_success_url = isset($_POST['stripe_success_url']) ? esc_url_raw($_POST['stripe_success_url']) : '';
        $stripe_cancel_url = isset($_POST['stripe_cancel_url']) ? esc_url_raw($_POST['stripe_cancel_url']) : '';
        $stripe_plans = array();
        if (isset($_POST['stripe_plans']) && is_array($_POST['stripe_plans'])) {
            foreach ($_POST['stripe_plans'] as $plan_key => $plan_data) {
                $plan_key = sanitize_key($plan_key);
                if (!$plan_key || !is_array($plan_data)) {
                    continue;
                }
                $stripe_plans[$plan_key] = array(
                    'label' => isset($plan_data['label']) ? sanitize_text_field($plan_data['label']) : ucfirst($plan_key),
                    'limit' => isset($plan_data['limit']) ? (int) $plan_data['limit'] : 0,
                    'monthly_price_id' => isset($plan_data['monthly_price_id']) ? sanitize_text_field($plan_data['monthly_price_id']) : '',
                    'annual_price_id' => isset($plan_data['annual_price_id']) ? sanitize_text_field($plan_data['annual_price_id']) : '',
                );
            }
        }

        update_option('lead_aggregator_notify_enabled', $notify_enabled);
        update_option('lead_aggregator_app_mode', $app_mode);
        update_option('lead_aggregator_app_menu_id', $app_menu_id);
        update_option('lead_aggregator_app_logo_id', $app_logo_id);
        update_option('lead_aggregator_app_footer_enabled', $footer_enabled);
        update_option('lead_aggregator_app_footer_text', $footer_text);
        update_option('lead_aggregator_webhook_logging', $webhook_logging);
        update_option('lead_aggregator_stripe_publishable_key', $stripe_publishable_key);
        update_option('lead_aggregator_stripe_secret_key', $stripe_secret_key);
        update_option('lead_aggregator_stripe_webhook_secret', $stripe_webhook_secret);
        update_option('lead_aggregator_stripe_success_url', $stripe_success_url);
        update_option('lead_aggregator_stripe_cancel_url', $stripe_cancel_url);
        update_option('lead_aggregator_plans', $stripe_plans);

        wp_redirect(admin_url('admin.php?page=lead-aggregator&settings=updated'));
        exit;
    }

    public function update_webhook_source() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_update_webhook', 'lead_aggregator_update_webhook_nonce');

        $webhook_id = isset($_POST['webhook_id']) ? (int) $_POST['webhook_id'] : 0;
        $shared_secret = isset($_POST['shared_secret']) ? sanitize_text_field($_POST['shared_secret']) : '';
        $user_id = get_current_user_id();

        if ($webhook_id) {
            $data = array();
            if (!empty($_POST['regenerate'])) {
                $data['shared_secret'] = wp_generate_password(24, false, false);
            } elseif ($shared_secret !== '') {
                $data['shared_secret'] = $shared_secret;
            }
            if (!empty($data)) {
                $this->database->update_webhook_source($user_id, $webhook_id, $data);
            }
        }

        wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=webhooks&webhook=updated'));
        exit;
    }

    public function grant_access() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_grant_access', 'lead_aggregator_grant_access_nonce');

        $email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
        $access_enabled = !empty($_POST['access_enabled']) ? 1 : 0;
        $access_level = isset($_POST['access_level']) ? sanitize_key($_POST['access_level']) : 'full';
        if (!in_array($access_level, array('full', 'read'), true)) {
            $access_level = 'full';
        }

        if ($email) {
            $user = get_user_by('email', $email);
            if ($user) {
                update_user_meta($user->ID, 'lead_aggregator_access_enabled', $access_enabled);
                update_user_meta($user->ID, 'lead_aggregator_access_level', $access_level);
                if ($access_enabled) {
                    update_user_meta($user->ID, 'lead_aggregator_subscription_status', 'active');
                }
            }
        }

        wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=settings&access=updated'));
        exit;
    }

    public function manage_user() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_manage_user', 'lead_aggregator_manage_user_nonce');

        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $manager_id = isset($_POST['manager_id']) ? (int) $_POST['manager_id'] : 0;
        $access_enabled = !empty($_POST['access_enabled']) ? 1 : 0;
        $access_level = isset($_POST['access_level']) ? sanitize_key($_POST['access_level']) : 'full';
        $reset_password = !empty($_POST['reset_password']);
        $delete_user = !empty($_POST['delete_user']);

        if (!in_array($access_level, array('full', 'read'), true)) {
            $access_level = 'full';
        }

        if ($user_id && $delete_user) {
            wp_delete_user($user_id);
        } elseif ($user_id) {
            update_user_meta($user_id, 'lead_aggregator_manager_id', $manager_id);
            update_user_meta($user_id, 'lead_aggregator_access_enabled', $access_enabled);
            update_user_meta($user_id, 'lead_aggregator_access_level', $access_level);
            if ($access_enabled) {
                update_user_meta($user_id, 'lead_aggregator_subscription_status', 'active');
            }
            if ($reset_password) {
                $password = wp_generate_password(16, false, false);
                wp_set_password($password, $user_id);
                update_user_meta($user_id, 'lead_aggregator_temp_password', $password);
            }
        }

        wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=manage-users&updated=1'));
        exit;
    }

    public function create_user() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_create_user', 'lead_aggregator_create_user_nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $manager_id = isset($_POST['manager_id']) ? (int) $_POST['manager_id'] : 0;
        $access_enabled = !empty($_POST['access_enabled']) ? 1 : 0;
        $access_level = isset($_POST['access_level']) ? sanitize_key($_POST['access_level']) : 'full';
        if (!in_array($access_level, array('full', 'read'), true)) {
            $access_level = 'full';
        }

        if (!$email || email_exists($email)) {
            wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=manage-users&error=exists'));
            exit;
        }

        $username = sanitize_user(strstr($email, '@', true));
        if (!$username) {
            $username = 'leaduser';
        }
        $base_username = $username;
        $suffix = 1;
        while (username_exists($username)) {
            $username = $base_username . $suffix;
            $suffix++;
        }
        $password = wp_generate_password(16, false, false);
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=manage-users&error=create'));
            exit;
        }
        if ($name) {
            wp_update_user(array('ID' => $user_id, 'display_name' => $name));
        }
        update_user_meta($user_id, 'lead_aggregator_manager_id', $manager_id);
        update_user_meta($user_id, 'lead_aggregator_access_enabled', $access_enabled);
        update_user_meta($user_id, 'lead_aggregator_access_level', $access_level);
        if ($access_enabled) {
            update_user_meta($user_id, 'lead_aggregator_subscription_status', 'active');
        }
        update_user_meta($user_id, 'lead_aggregator_temp_password', $password);

        wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=manage-users&created=1'));
        exit;
    }

    public function delete_user() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if ($user_id) {
            wp_delete_user($user_id);
        }
        wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=manage-users&deleted=1'));
        exit;
    }

    public function add_webhook_source() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_add_webhook', 'lead_aggregator_webhook_nonce');

        $source_key = isset($_POST['source_key']) ? sanitize_key($_POST['source_key']) : '';
        $shared_secret = isset($_POST['shared_secret']) ? sanitize_text_field($_POST['shared_secret']) : '';
        $user_id = get_current_user_id();

        $existing = $this->database->get_webhook_sources_by_user($user_id);
        if (empty($existing)) {
            if (!$source_key) {
                $source_key = 'user-' . $user_id;
            }
            $this->database->create_webhook_source($user_id, $source_key, $shared_secret, 1);
        }

        wp_redirect(admin_url('admin.php?page=lead-aggregator&webhooks=updated'));
        exit;
    }

    public function delete_webhook_source() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_delete_webhook', 'lead_aggregator_delete_nonce');

        $webhook_id = isset($_POST['webhook_id']) ? (int) $_POST['webhook_id'] : 0;
        if ($webhook_id) {
            $this->database->delete_webhook_source($webhook_id);
        }

        wp_redirect(admin_url('admin.php?page=lead-aggregator&webhooks=deleted'));
        exit;
    }

    public function clear_email_logs() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_clear_email_logs', 'lead_aggregator_clear_email_logs_nonce');

        $this->database->clear_email_logs();

        wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=email-log&emails=cleared'));
        exit;
    }

    public function clear_webhook_logs() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_clear_webhook_logs', 'lead_aggregator_clear_webhook_logs_nonce');

        $this->database->clear_webhook_logs();

        wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=webhook-log&webhooks=cleared'));
        exit;
    }

    private function format_pipeline_stage($value) {
        $value = sanitize_key($value);
        $map = array(
            'open' => 'New',
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal' => 'Proposal',
            'won' => 'Won',
            'lost' => 'Lost',
        );
        return isset($map[$value]) ? $map[$value] : 'New';
    }

    private function format_admin_datetime($value) {
        if (!$value) {
            return '';
        }
        try {
            $date = new DateTime($value, new DateTimeZone('UTC'));
            $date->setTimezone(wp_timezone());
            return $date->format('Y-m-d H:i');
        } catch (Exception $e) {
            return $value;
        }
    }
}
