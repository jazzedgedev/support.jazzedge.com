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
            add_action('admin_post_lead_aggregator_add_or_update_user', array($this, 'add_or_update_user'));
            add_action('admin_post_lead_aggregator_manage_user', array($this, 'manage_user'));
            add_action('admin_post_lead_aggregator_delete_user', array($this, 'delete_user'));
            add_action('admin_post_lead_aggregator_clear_email_logs', array($this, 'clear_email_logs'));
            add_action('admin_post_lead_aggregator_clear_webhook_logs', array($this, 'clear_webhook_logs'));
            add_action('admin_post_lead_aggregator_reconcile_fluentcart_now', array($this, 'reconcile_fluentcart_now'));
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
        wp_add_inline_style('wp-admin', '
            .la-source-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 500; }
            .la-source-fluentcart { background: #e7f5ff; color: #0c6cb8; }
            .la-source-manual { background: #f0f0f1; color: #50575e; }
            .la-set-manual { display: block; margin-top: 6px; font-size: 12px; color: #646970; cursor: pointer; }
            .la-set-manual input { margin-right: 4px; vertical-align: middle; }
            .la-select-compact { min-width: 120px; max-width: 160px; }
            .la-checkbox-inline { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; cursor: pointer; white-space: nowrap; }
            .la-checkbox-inline input { margin: 0; }
            .la-ml-2 { margin-left: 12px; }
            .la-actions-cell { white-space: nowrap; }
            .la-actions-cell .button { margin-right: 8px; }
        ');
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

        wp_add_inline_script(
            'jquery',
            'jQuery(function($){' .
                'var $output = $("#lead-aggregator-fluentcart-plans");' .
                'var $debug = $("#lead-aggregator-fluentcart-debug");' .
                'var nonce = "' . esc_js(wp_create_nonce('wp_rest')) . '";' .
                '$("#lead-aggregator-sync-fluentcart").on("click", function(e){' .
                    'e.preventDefault();' .
                    'var $btn = $(this);' .
                    '$btn.prop("disabled", true).text("Syncing...");' .
                    '$output.empty();' .
                    '$debug.empty();' .
                    'fetch("' . esc_url_raw(rest_url('lead-aggregator/v1/fluentcart/plans')) . '?_wpnonce=" + encodeURIComponent(nonce), {' .
                        'method: "GET",' .
                        'headers: { "X-WP-Nonce": nonce }' .
                    '})' .
                    '.then(function(response){ return response.json(); })' .
                    '.then(function(data){' .
                        'if (!data || !data.success) { throw new Error((data && data.message) ? data.message : "Unable to load FluentCart plans."); }' .
                        'var html = "<p><strong>Available subscriptions</strong></p><p class=\"description\">Show IDs: use these Product IDs in Plan Configuration.</p><ul>";' .
                        'if (!data.data || !data.data.length) {' .
                            'html += "<li>No subscriptions found.</li>";' .
                            '$output.append("<p class=\\"description\\">Debug: Available subscriptions. Show IDs: use these Product IDs in Plan Configuration. No subscriptions found.</p>");' .
                        '}' .
                        'else { data.data.forEach(function(item){ html += "<li>" + item.label + " (ID: " + item.id + ")</li>"; }); }' .
                        'html += "</ul>";' .
                        '$output.html(html);' .
                    '})' .
                    '.catch(function(err){' .
                        'var message = (err && err.message) ? err.message : "Unable to load FluentCart subscriptions.";' .
                        '$output.html("<p>Unable to load FluentCart subscriptions.</p><p class=\\"description\\">Debug: " + message + "</p>");' .
                    '})' .
                    '.finally(function(){ $btn.prop("disabled", false).text("Sync FluentCart Plans"); });' .
                '});' .
                '$("#lead-aggregator-debug-fluentcart").on("click", function(e){' .
                    'e.preventDefault();' .
                    'var $btn = $(this);' .
                    '$btn.prop("disabled", true).text("Debugging...");' .
                    '$debug.empty();' .
                    'fetch("' . esc_url_raw(rest_url('lead-aggregator/v1/fluentcart/debug')) . '?_wpnonce=" + encodeURIComponent(nonce), {' .
                        'method: "GET",' .
                        'headers: { "X-WP-Nonce": nonce }' .
                    '})' .
                    '.then(function(response){ return response.json(); })' .
                    '.then(function(data){' .
                        'var payload = data && data.data ? data.data : data;' .
                        'var output = JSON.stringify(payload, null, 2);' .
                        '$debug.html("<pre id=\\"lead-aggregator-fluentcart-debug-pre\\">" + output + "</pre>");' .
                        '$debug.data("debug-json", output);' .
                    '})' .
                    '.catch(function(err){' .
                        'var message = (err && err.message) ? err.message : "Unable to load FluentCart debug info.";' .
                        '$debug.html("<p class=\\"description\\">Debug error: " + message + "</p>");' .
                    '})' .
                    '.finally(function(){ $btn.prop("disabled", false).text("Debug FluentCart"); });' .
                '});' .
                '$("#lead-aggregator-copy-fluentcart").on("click", function(e){' .
                    'e.preventDefault();' .
                    'var text = $debug.data("debug-json") || "";' .
                    'if (!text) { return; }' .
                    'if (navigator.clipboard && navigator.clipboard.writeText) {' .
                        'navigator.clipboard.writeText(text);' .
                    '}' .
                '});' .
                '$("#lead-aggregator-settings-form").on("submit", function(){' .
                    'var plans = {};' .
                    '$("#lead-aggregator-plans-table tbody tr[data-plan-key]").each(function(){' .
                        'var key = $(this).data("plan-key");' .
                        'var ids = ($(this).find(".la-plan-ids").val() || "").split(",").map(function(s){ return s.trim(); }).filter(Boolean);' .
                        'var statuses = [];' .
                        '$(this).find(".la-plan-status:checked").each(function(){ statuses.push($(this).val()); });' .
                        'if (statuses.length === 0) { statuses = ["active","trialing","grace"]; }' .
                        'plans[key] = { label: $(this).find(".la-plan-label").val() || key, lead_limit: parseInt($(this).find(".la-plan-limit").val(), 10) || 0, fluentcart: { subscription_ids: ids, allowed_statuses: statuses } };' .
                    '});' .
                    '$("#lead-aggregator-plans-json").val(JSON.stringify(plans));' .
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
        $plans = get_option('lead_aggregator_plans', array());
        $fluentcart_secret = get_option('lead_aggregator_fluentcart_webhook_secret', '');
        $fluentcart_secret_constant = defined('LEAD_AGGREGATOR_FLUENTCART_WEBHOOK_SECRET') ? (string) LEAD_AGGREGATOR_FLUENTCART_WEBHOOK_SECRET : '';
        $fluentcart_secret_display = $fluentcart_secret_constant ? $fluentcart_secret_constant : $fluentcart_secret;
        $fluentcart_secret_suffix = $fluentcart_secret_display ? substr($fluentcart_secret_display, -4) : '';
        $webhooks = $this->database->get_webhook_sources();
        $menus = wp_get_nav_menus();
        $pages = get_pages(array('number' => 500));
        $login_page_id = (int) get_option('lead_aggregator_login_page_id', 0);
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
                <?php
                if (isset($_GET['reconcile']) && $_GET['reconcile'] === 'debug') {
                    $reconcile_debug = get_transient('lead_aggregator_reconcile_debug');
                    delete_transient('lead_aggregator_reconcile_debug');
                    if (is_array($reconcile_debug)) {
                        $reconcile_json = wp_json_encode($reconcile_debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        ?>
                        <div class="notice notice-info" style="margin: 15px 0;">
                            <p><strong>Reconcile debug results</strong> — <button type="button" class="button button-small" id="lead-aggregator-copy-reconcile-debug">Copy to clipboard</button></p>
                            <pre id="lead-aggregator-reconcile-debug-output" style="background: #f5f5f5; padding: 12px; overflow: auto; max-height: 400px; font-size: 12px;"><?php echo esc_html($reconcile_json); ?></pre>
                        </div>
                        <script>
                        (function(){
                            var btn = document.getElementById('lead-aggregator-copy-reconcile-debug');
                            var pre = document.getElementById('lead-aggregator-reconcile-debug-output');
                            if (btn && pre) {
                                btn.addEventListener('click', function(){
                                    try {
                                        navigator.clipboard.writeText(pre.textContent);
                                        btn.textContent = 'Copied!';
                                        setTimeout(function(){ btn.textContent = 'Copy to clipboard'; }, 2000);
                                    } catch (e) {
                                        var sel = window.getSelection();
                                        var r = document.createRange();
                                        r.selectNodeContents(pre);
                                        sel.removeAllRanges();
                                        sel.addRange(r);
                                        document.execCommand('copy');
                                        btn.textContent = 'Copied!';
                                        setTimeout(function(){ btn.textContent = 'Copy to clipboard'; }, 2000);
                                    }
                                });
                            }
                        })();
                        </script>
                        <?php
                    }
                }
                ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="lead-aggregator-settings-form">
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

                    <h3>FluentCart Webhook</h3>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Webhook Secret</th>
                            <td>
                                <?php if ($fluentcart_secret_constant) : ?>
                                    <input type="text" value="Saved ••••<?php echo esc_attr($fluentcart_secret_suffix); ?>" class="regular-text" disabled />
                                    <p class="description">Secret is defined via <code>LEAD_AGGREGATOR_FLUENTCART_WEBHOOK_SECRET</code>.</p>
                                <?php else : ?>
                                    <input type="password" name="fluentcart_webhook_secret" value="" class="regular-text" maxlength="200" placeholder="<?php echo $fluentcart_secret_suffix ? 'Saved ••••' . esc_attr($fluentcart_secret_suffix) : ''; ?>" />
                                    <p class="description">Enter a new secret to replace the saved value. Saved value is hidden.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <h3>Plan Configuration</h3>
                    <input type="hidden" name="plans_json" id="lead-aggregator-plans-json" value="<?php echo esc_attr(wp_json_encode($plans)); ?>">
                    <table class="widefat striped" style="margin-top: 10px;" id="lead-aggregator-plans-table">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Lead Limit</th>
                                <th>FluentCart Subscriptions</th>
                                <th>Allowed Statuses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $default_plans = array(
                                'starter' => array('label' => 'Starter', 'lead_limit' => 100),
                                'core' => array('label' => 'Core', 'lead_limit' => 500),
                                'plus' => array('label' => 'Plus', 'lead_limit' => 2500),
                                'enterprise' => array('label' => 'Enterprise', 'lead_limit' => 10000),
                            );
                            foreach ($default_plans as $plan_key => $plan_defaults) :
                                $plan_settings = isset($plans[$plan_key]) && is_array($plans[$plan_key]) ? $plans[$plan_key] : array();
                                $label = isset($plan_settings['label']) ? $plan_settings['label'] : $plan_defaults['label'];
                                $limit = isset($plan_settings['lead_limit']) ? (int) $plan_settings['lead_limit'] : (isset($plan_settings['limit']) ? (int) $plan_settings['limit'] : $plan_defaults['lead_limit']);
                                $fluentcart = isset($plan_settings['fluentcart']) && is_array($plan_settings['fluentcart']) ? $plan_settings['fluentcart'] : array();
                                $subscription_ids = isset($fluentcart['subscription_ids']) ? implode(',', (array) $fluentcart['subscription_ids']) : '';
                                $allowed_statuses = isset($fluentcart['allowed_statuses']) ? (array) $fluentcart['allowed_statuses'] : array('active', 'trialing', 'grace');
                                ?>
                                <tr data-plan-key="<?php echo esc_attr($plan_key); ?>">
                                    <td>
                                        <strong><?php echo esc_html($label); ?></strong>
                                        <input type="hidden" class="la-plan-label" value="<?php echo esc_attr($label); ?>">
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="la-plan-limit" value="<?php echo esc_attr($limit); ?>" />
                                    </td>
                                    <td>
                                        <input type="text" class="la-plan-ids regular-text" value="<?php echo esc_attr($subscription_ids); ?>" placeholder="123,456" />
                                    </td>
                                    <td>
                                        <label><input type="checkbox" class="la-plan-status" value="active" <?php checked(in_array('active', $allowed_statuses, true)); ?>> Active</label><br>
                                        <label><input type="checkbox" class="la-plan-status" value="trialing" <?php checked(in_array('trialing', $allowed_statuses, true)); ?>> Trialing</label><br>
                                        <label><input type="checkbox" class="la-plan-status" value="grace" <?php checked(in_array('grace', $allowed_statuses, true)); ?>> Grace</label><br>
                                        <label><input type="checkbox" class="la-plan-status" value="past_due" <?php checked(in_array('past_due', $allowed_statuses, true)); ?>> Past Due</label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="description">Enter Product IDs in the table above (use the list below to find IDs), then click <strong>Save Settings</strong> at the bottom of this page to save your plan configuration.</p>
                    <p>
                        <button type="button" class="button" id="lead-aggregator-sync-fluentcart">Sync FluentCart Plans</button>
                        <button type="button" class="button" id="lead-aggregator-debug-fluentcart">Debug FluentCart</button>
                        <button type="button" class="button" id="lead-aggregator-copy-fluentcart">Copy Debug</button>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                            <?php wp_nonce_field('lead_aggregator_reconcile_fluentcart_now', 'lead_aggregator_reconcile_nonce'); ?>
                            <input type="hidden" name="action" value="lead_aggregator_reconcile_fluentcart_now">
                            <?php submit_button('Reconcile plans now', 'secondary', 'reconcile_fluentcart', false); ?>
                        </form>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                            <?php wp_nonce_field('lead_aggregator_reconcile_fluentcart_now', 'lead_aggregator_reconcile_nonce'); ?>
                            <input type="hidden" name="action" value="lead_aggregator_reconcile_fluentcart_now">
                            <input type="hidden" name="reconcile_debug" value="1">
                            <?php submit_button('Reconcile with debug', 'secondary', 'reconcile_fluentcart_debug', false); ?>
                        </form>
                        <p class="description" style="margin-top: 6px;">Reconcile plans now re-checks FluentCart for all users with a plan and updates access (e.g. after a cancellation when the webhook did not fire). Use <strong>Reconcile with debug</strong> to see per-user results and copy them.</p>
                    </p>
                    <div id="lead-aggregator-fluentcart-plans" style="margin-top: 10px;"></div>
                    <div id="lead-aggregator-fluentcart-debug" style="margin-top: 10px;"></div>

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
                    <tr>
                        <th scope="row">Login Redirect Page</th>
                        <td>
                            <select name="login_page_id">
                                <option value="0">Default (/login)</option>
                                <?php foreach ($pages as $page) : ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($login_page_id, (int) $page->ID); ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Redirect unauthenticated visitors to this page.</p>
                        </td>
                    </tr>
                    </table>

                    <?php submit_button('Save Settings'); ?>
                </form>

            <?php elseif ($current_tab === 'manage-users') : ?>
                <?php
                if (isset($_GET['updated'])) : ?>
                    <div class="notice notice-success is-dismissible"><p>User updated.</p></div>
                <?php elseif (isset($_GET['created'])) : ?>
                    <div class="notice notice-success is-dismissible"><p>User created. A temporary password was set; it is shown in the table below.</p></div>
                <?php elseif (isset($_GET['deleted'])) : ?>
                    <div class="notice notice-success is-dismissible"><p>User deleted.</p></div>
                <?php elseif (isset($_GET['error'])) :
                    $err_msg = array('email' => 'Please enter a valid email.', 'exists' => 'A user with that email already exists.', 'create' => 'Unable to create user.');
                    $msg = isset($err_msg[$_GET['error']]) ? $err_msg[$_GET['error']] : 'An error occurred.';
                ?>
                    <div class="notice notice-error is-dismissible"><p><?php echo esc_html($msg); ?></p></div>
                <?php endif; ?>
                <h2>Manage Users</h2>
                <p class="description">Manage manager and sub-account access, roles, and passwords.</p>
                <h3>Add or Update User</h3>
                <p class="description">Create a new user or update access for an existing user. Enter email to look up; if the user exists, access is updated. If not, a new user is created.</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('lead_aggregator_add_or_update_user', 'lead_aggregator_add_or_update_user_nonce'); ?>
                    <input type="hidden" name="action" value="lead_aggregator_add_or_update_user">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Name</th>
                            <td><input type="text" name="name" class="regular-text" placeholder="Jane Smith"><span class="description"> Used when creating a new user.</span></td>
                        </tr>
                        <tr>
                            <th scope="row">Email</th>
                            <td><input type="email" name="email" class="regular-text" required placeholder="user@example.com"></td>
                        </tr>
                        <tr>
                            <th scope="row">Type</th>
                            <td>
                                <select name="manager_id">
                                    <option value="0">Set as a Manager</option>
                                    <option value="" disabled>──────────</option>
                                    <optgroup label="Assign to Manager...">
                                        <?php foreach ($manager_options as $manager) : ?>
                                            <option value="<?php echo esc_attr($manager->ID); ?>"><?php echo esc_html($manager->display_name); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
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
                    <?php submit_button('Add or Update User'); ?>
                </form>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Manager</th>
                            <th>Access</th>
                            <th>Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_users)) : ?>
                            <tr>
                                <td colspan="8">No users found.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($all_users as $user) : ?>
                                <?php
                                $manager_id = (int) get_user_meta($user->ID, 'lead_aggregator_manager_id', true);
                                $access_enabled = (int) get_user_meta($user->ID, 'lead_aggregator_access_enabled', true);
                                $access_level = get_user_meta($user->ID, 'lead_aggregator_access_level', true) ?: 'full';
                                $plan_source = get_user_meta($user->ID, 'lead_aggregator_plan_source', true);
                                $type_label = $manager_id > 0 ? 'Sub-account' : 'Manager';
                                $source_label = ($plan_source === 'fluentcart') ? 'FluentCart' : 'Manual';
                                $temp_password = get_user_meta($user->ID, 'lead_aggregator_temp_password', true);
                                ?>
                                <?php $form_id = 'la-user-form-' . (int) $user->ID; ?>
                                <tr>
                                    <td>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="<?php echo esc_attr($form_id); ?>">
                                            <?php wp_nonce_field('lead_aggregator_manage_user', 'lead_aggregator_manage_user_nonce'); ?>
                                            <input type="hidden" name="action" value="lead_aggregator_manage_user">
                                            <input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>">
                                        </form>
                                        <?php echo esc_html($user->display_name); ?>
                                        <?php if ($temp_password) : ?>
                                            <span class="description" style="display: block; margin-top: 2px; color: #d63638;">Temp password: <strong><?php echo esc_html($temp_password); ?></strong></span>
                                            <?php delete_user_meta($user->ID, 'lead_aggregator_temp_password'); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($user->user_email); ?></td>
                                    <td><?php echo esc_html($type_label); ?></td>
                                    <td>
                                        <span class="la-source-badge la-source-<?php echo esc_attr($plan_source === 'fluentcart' ? 'fluentcart' : 'manual'); ?>"><?php echo esc_html($source_label); ?></span>
                                        <?php if ($plan_source === 'fluentcart') : ?>
                                            <label class="la-set-manual">
                                                <input type="checkbox" name="set_source_manual" value="1" form="<?php echo esc_attr($form_id); ?>">
                                                <span>Convert to manual</span>
                                            </label>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <select name="manager_id" form="<?php echo esc_attr($form_id); ?>" class="la-select-compact">
                                            <option value="0" <?php selected($manager_id, 0); ?>>Manager</option>
                                            <option value="" disabled>──────────</option>
                                            <?php foreach ($manager_options as $manager) : ?>
                                                <option value="<?php echo esc_attr($manager->ID); ?>" <?php selected($manager_id, $manager->ID); ?>>
                                                    <?php echo esc_html($manager->display_name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <label class="la-checkbox-inline">
                                            <input type="checkbox" name="access_enabled" value="1" form="<?php echo esc_attr($form_id); ?>" <?php checked($access_enabled, 1); ?>>
                                            <span>Active</span>
                                        </label>
                                    </td>
                                    <td>
                                        <select name="access_level" form="<?php echo esc_attr($form_id); ?>" class="la-select-compact">
                                            <option value="full" <?php selected($access_level, 'full'); ?>>Full</option>
                                            <option value="read" <?php selected($access_level, 'read'); ?>>Read-only</option>
                                        </select>
                                    </td>
                                    <td class="la-actions-cell">
                                        <button type="submit" form="<?php echo esc_attr($form_id); ?>" class="button button-small button-primary">Save</button>
                                        <label class="la-checkbox-inline la-ml-2">
                                            <input type="checkbox" name="reset_password" value="1" form="<?php echo esc_attr($form_id); ?>">
                                            <span>Reset pwd</span>
                                        </label>
                                        <label class="la-checkbox-inline la-ml-2">
                                            <input type="checkbox" name="delete_user" value="1" form="<?php echo esc_attr($form_id); ?>">
                                            <span>Delete</span>
                                        </label>
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
                                <th>User</th>
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
                                $owner = !empty($webhook['user_id']) ? get_user_by('id', (int) $webhook['user_id']) : null;
                                $owner_label = $owner ? esc_html($owner->display_name) . ' <small>(' . esc_html($owner->user_email) . ')</small>' : '—';
                                ?>
                                <tr>
                                    <td><?php echo $owner_label; ?></td>
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
        $login_page_id = isset($_POST['login_page_id']) ? (int) $_POST['login_page_id'] : 0;
        $plans = array();
        if (!empty($_POST['plans_json'])) {
            $raw = json_decode(wp_unslash($_POST['plans_json']), true);
            if (is_array($raw)) {
                $allowed = array('active', 'trialing', 'grace', 'past_due');
                foreach ($raw as $plan_key => $plan_data) {
                    $plan_key = sanitize_key($plan_key);
                    if (!$plan_key || !is_array($plan_data)) {
                        continue;
                    }
                    $subscription_ids = array();
                    if (!empty($plan_data['fluentcart']['subscription_ids']) && is_array($plan_data['fluentcart']['subscription_ids'])) {
                        foreach ($plan_data['fluentcart']['subscription_ids'] as $id) {
                            $id = trim((string) $id);
                            if ($id !== '') {
                                $subscription_ids[] = $id;
                            }
                        }
                    }
                    $statuses = array();
                    if (!empty($plan_data['fluentcart']['allowed_statuses']) && is_array($plan_data['fluentcart']['allowed_statuses'])) {
                        foreach ($plan_data['fluentcart']['allowed_statuses'] as $s) {
                            if (in_array($s, $allowed, true)) {
                                $statuses[] = $s;
                            }
                        }
                    }
                    if (empty($statuses)) {
                        $statuses = array('active', 'trialing', 'grace');
                    }
                    $plans[$plan_key] = array(
                        'label' => isset($plan_data['label']) ? sanitize_text_field($plan_data['label']) : ucfirst($plan_key),
                        'lead_limit' => isset($plan_data['lead_limit']) ? (int) $plan_data['lead_limit'] : 0,
                        'fluentcart' => array(
                            'subscription_ids' => $subscription_ids,
                            'allowed_statuses' => $statuses,
                        ),
                    );
                }
            }
        }
        if (empty($plans)) {
            $plans = get_option('lead_aggregator_plans', array());
        }

        update_option('lead_aggregator_notify_enabled', $notify_enabled);
        update_option('lead_aggregator_app_mode', $app_mode);
        update_option('lead_aggregator_app_menu_id', $app_menu_id);
        update_option('lead_aggregator_app_logo_id', $app_logo_id);
        update_option('lead_aggregator_app_footer_enabled', $footer_enabled);
        update_option('lead_aggregator_app_footer_text', $footer_text);
        update_option('lead_aggregator_webhook_logging', $webhook_logging);
        update_option('lead_aggregator_login_page_id', $login_page_id);
        update_option('lead_aggregator_plans', $plans);

        if (!defined('LEAD_AGGREGATOR_FLUENTCART_WEBHOOK_SECRET') && isset($_POST['fluentcart_webhook_secret'])) {
            $secret = sanitize_text_field(wp_unslash($_POST['fluentcart_webhook_secret']));
            $secret = substr(trim($secret), 0, 200);
            if ($secret !== '') {
                update_option('lead_aggregator_fluentcart_webhook_secret', $secret);
            }
        }

        wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=settings&settings=updated'));
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

    public function add_or_update_user() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_add_or_update_user', 'lead_aggregator_add_or_update_user_nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $manager_id = isset($_POST['manager_id']) ? (int) $_POST['manager_id'] : 0;
        $access_enabled = !empty($_POST['access_enabled']) ? 1 : 0;
        $access_level = isset($_POST['access_level']) ? sanitize_key($_POST['access_level']) : 'full';
        if (!in_array($access_level, array('full', 'read'), true)) {
            $access_level = 'full';
        }

        if (!$email) {
            wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=manage-users&error=email'));
            exit;
        }

        $user = get_user_by('email', $email);
        if ($user) {
            update_user_meta($user->ID, 'lead_aggregator_manager_id', $manager_id);
            update_user_meta($user->ID, 'lead_aggregator_access_enabled', $access_enabled);
            update_user_meta($user->ID, 'lead_aggregator_access_level', $access_level);
            if ($access_enabled) {
                update_user_meta($user->ID, 'lead_aggregator_subscription_status', 'active');
            }
            if ($name) {
                wp_update_user(array('ID' => $user->ID, 'display_name' => $name));
            }
            wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=manage-users&updated=1'));
        } else {
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
        }
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

        $set_source_manual = !empty($_POST['set_source_manual']);

        if ($user_id && $delete_user) {
            wp_delete_user($user_id);
        } elseif ($user_id) {
            update_user_meta($user_id, 'lead_aggregator_manager_id', $manager_id);
            update_user_meta($user_id, 'lead_aggregator_access_enabled', $access_enabled);
            update_user_meta($user_id, 'lead_aggregator_access_level', $access_level);
            if ($access_enabled) {
                update_user_meta($user_id, 'lead_aggregator_subscription_status', 'active');
            }
            if ($set_source_manual) {
                update_user_meta($user_id, 'lead_aggregator_plan_source', 'legacy');
                update_user_meta($user_id, 'lead_aggregator_plan_key', '');
                delete_user_meta($user_id, 'lead_aggregator_plan_cache_expires_at');
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

    public function reconcile_fluentcart_now() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        check_admin_referer('lead_aggregator_reconcile_fluentcart_now', 'lead_aggregator_reconcile_nonce');

        $with_debug = !empty($_POST['reconcile_debug']);
        $plugin = lead_aggregator();
        if ($plugin) {
            if ($with_debug) {
                $debug = $plugin->run_fluentcart_reconcile_now(true);
                set_transient('lead_aggregator_reconcile_debug', $debug, 300);
                wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=settings&reconcile=debug'));
            } else {
                $plugin->run_fluentcart_reconcile_now(false);
                wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=settings&reconcile=done'));
            }
        } else {
            wp_redirect(admin_url('admin.php?page=lead-aggregator&tab=settings'));
        }
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
