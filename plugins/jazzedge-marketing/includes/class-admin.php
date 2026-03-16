<?php
/**
 * Admin UI for Jazzedge Marketing
 *
 * @package Jazzedge_Marketing
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin pages and funnel CRUD.
 */
class JEM_Admin {

    private $database;

    public function __construct($database) {
        $this->database = $database;
        if (is_admin()) {
            add_action('admin_menu', array($this, 'register_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
            add_action('admin_init', array($this, 'maybe_save_funnel'));
            add_action('admin_init', array($this, 'maybe_save_settings'));
        }
    }

    public function register_menu() {
        add_menu_page(
            __('JEM Marketing', 'jazzedge-marketing'),
            __('JEM Marketing', 'jazzedge-marketing'),
            'manage_options',
            'jem-marketing',
            array($this, 'funnels_page'),
            'dashicons-megaphone',
            30
        );
        add_submenu_page(
            'jem-marketing',
            __('Funnels', 'jazzedge-marketing'),
            __('Funnels', 'jazzedge-marketing'),
            'manage_options',
            'jem-marketing',
            array($this, 'funnels_page')
        );
        add_submenu_page(
            'jem-marketing',
            __('Add Funnel', 'jazzedge-marketing'),
            __('Add Funnel', 'jazzedge-marketing'),
            'manage_options',
            'jem-add-funnel',
            array($this, 'add_funnel_page')
        );
        add_submenu_page(
            'jem-marketing',
            __('Metrics', 'jazzedge-marketing'),
            __('Metrics', 'jazzedge-marketing'),
            'manage_options',
            'jem-metrics',
            array(jem_plugin()->metrics, 'render_page')
        );
        add_submenu_page(
            'jem-marketing',
            __('Settings', 'jazzedge-marketing'),
            __('Settings', 'jazzedge-marketing'),
            'manage_options',
            'jem-settings',
            array($this, 'settings_page')
        );
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'jem-') === false) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style('jem-admin', JEM_PLUGIN_URL . 'assets/css/admin.css', array(), JEM_VERSION);
        wp_enqueue_script('jem-admin', JEM_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), JEM_VERSION, true);
    }

    public function funnels_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'jazzedge-marketing'));
        }
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        if ($action === 'edit' && !empty($_GET['id'])) {
            $this->edit_funnel_page((int) $_GET['id']);
            return;
        }
        if ($action === 'add') {
            $this->add_funnel_page();
            return;
        }

        $message = get_transient('jem_funnel_message');
        if ($message) {
            delete_transient('jem_funnel_message');
            $type = $message[0] === 'success' ? 'success' : 'error';
            echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>' . esc_html($message[1]) . '</p></div>';
        }
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        require_once JEM_PLUGIN_DIR . 'includes/class-funnels-list-table.php';
        $list_table = new JEM_Funnels_List_Table($this->database);
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Funnels', 'jazzedge-marketing'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=jem-add-funnel')); ?>" class="page-title-action"><?php esc_html_e('Add New', 'jazzedge-marketing'); ?></a>
            <hr class="wp-header-end">
            <form method="get">
                <input type="hidden" name="page" value="jem-marketing" />
                <?php $list_table->display(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Handle funnel save when form is posted to the same page (avoids admin-post.php).
     */
    public function maybe_save_funnel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['jem_funnel_nonce'])) {
            return;
        }
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $is_add = ($page === 'jem-add-funnel');
        $is_edit = ($page === 'jem-marketing' && isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id']));
        if ($is_add || $is_edit) {
            $this->save_funnel();
        }
    }

    /**
     * Handle settings save when form is posted to the same page.
     */
    public function maybe_save_settings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['jem_settings_nonce']) || !isset($_GET['page']) || $_GET['page'] !== 'jem-settings') {
            return;
        }
        $this->save_settings();
    }

    public function add_funnel_page() {
        $this->funnel_form(null);
    }

    public function edit_funnel_page($id) {
        $funnel = $this->database->get_funnel($id);
        if (!$funnel) {
            wp_die(__('Funnel not found.', 'jazzedge-marketing'));
        }
        $this->funnel_form($funnel);
    }

    private function funnel_form($funnel) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'jazzedge-marketing'));
        }
        $is_edit = $funnel !== null;
        $title = $is_edit ? __('Edit Funnel', 'jazzedge-marketing') : __('Add Funnel', 'jazzedge-marketing');
        $media_filename = '';
        if ($is_edit && !empty($funnel->media_id)) {
            $path = get_attached_file($funnel->media_id);
            $media_filename = $path ? basename($path) : '';
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($title); ?></h1>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'missing') : $msg = get_transient('jem_funnel_message'); if ($msg) { delete_transient('jem_funnel_message'); echo '<div class="notice notice-error"><p>' . esc_html($msg[1]) . '</p></div>'; } endif; ?>
            <form method="post" action="<?php echo esc_url($is_edit ? add_query_arg(array('page' => 'jem-marketing', 'action' => 'edit', 'id' => $funnel->id), admin_url('admin.php')) : add_query_arg('page', 'jem-add-funnel', admin_url('admin.php'))); ?>" id="jem-funnel-form">
                <?php wp_nonce_field('jem_save_funnel', 'jem_funnel_nonce'); ?>
                <?php if ($is_edit) : ?>
                    <input type="hidden" name="jem_funnel_id" value="<?php echo esc_attr($funnel->id); ?>" />
                <?php endif; ?>

                <table class="form-table">
                    <tr>
                        <th><label for="jem_funnel_name"><?php esc_html_e('Funnel Name', 'jazzedge-marketing'); ?></label></th>
                        <td><input type="text" id="jem_funnel_name" name="jem_funnel_name" value="<?php echo $is_edit ? esc_attr($funnel->name) : ''; ?>" class="regular-text" required /></td>
                    </tr>
                    <tr>
                        <th><label for="jem_webhook_url"><?php esc_html_e('Webhook URL', 'jazzedge-marketing'); ?></label></th>
                        <td><input type="url" id="jem_webhook_url" name="jem_webhook_url" value="<?php echo $is_edit ? esc_attr($funnel->webhook_url) : ''; ?>" class="large-text" required /></td>
                    </tr>
                    <tr>
                        <th><label for="jem_media_id"><?php esc_html_e('Sheet Music File', 'jazzedge-marketing'); ?></label></th>
                        <td>
                            <input type="hidden" id="jem_media_id" name="jem_media_id" value="<?php echo $is_edit ? esc_attr($funnel->media_id) : ''; ?>" />
                            <button type="button" id="jem-media-button" class="button"><?php esc_html_e('Select File', 'jazzedge-marketing'); ?></button>
                            <span id="jem-media-filename"><?php echo esc_html($media_filename); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="jem_product_url"><?php esc_html_e('FluentCart Product URL', 'jazzedge-marketing'); ?></label></th>
                        <td><input type="url" id="jem_product_url" name="jem_product_url" value="<?php echo $is_edit ? esc_attr($funnel->product_url) : ''; ?>" class="large-text" required /></td>
                    </tr>
                    <tr>
                        <th><label for="jem_product_id"><?php esc_html_e('FluentCart Product ID', 'jazzedge-marketing'); ?></label></th>
                        <td><input type="text" id="jem_product_id" name="jem_product_id" value="<?php echo $is_edit ? esc_attr($funnel->product_id) : ''; ?>" class="regular-text" placeholder="<?php esc_attr_e('Used in coupon included_products', 'jazzedge-marketing'); ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="jem_coupon_prefix"><?php esc_html_e('Coupon Prefix', 'jazzedge-marketing'); ?></label></th>
                        <td><input type="text" id="jem_coupon_prefix" name="jem_coupon_prefix" value="<?php echo $is_edit ? esc_attr($funnel->coupon_prefix) : 'JEM'; ?>" class="small-text" maxlength="10" /> <span class="description"><?php esc_html_e('Max 10 chars, uppercase', 'jazzedge-marketing'); ?></span></td>
                    </tr>
                    <tr>
                        <th><label for="jem_discount_pct"><?php esc_html_e('Discount %', 'jazzedge-marketing'); ?></label></th>
                        <td><input type="number" id="jem_discount_pct" name="jem_discount_pct" value="<?php echo $is_edit ? (int) $funnel->discount_pct : 20; ?>" min="1" max="100" class="small-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="jem_coupon_days"><?php esc_html_e('Coupon Duration (days)', 'jazzedge-marketing'); ?></label></th>
                        <td><input type="number" id="jem_coupon_days" name="jem_coupon_days" value="<?php echo $is_edit ? (int) $funnel->coupon_days : 3; ?>" min="1" class="small-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="jem_active"><?php esc_html_e('Active', 'jazzedge-marketing'); ?></label></th>
                        <td><input type="checkbox" id="jem_active" name="jem_active" value="1" <?php checked($is_edit ? (int) $funnel->active : 1, 1); ?> /></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php esc_attr_e('Save Funnel', 'jazzedge-marketing'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    public function save_funnel() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'jazzedge-marketing'));
        }
        if (!isset($_POST['jem_funnel_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['jem_funnel_nonce']), 'jem_save_funnel')) {
            set_transient('jem_funnel_message', array('error', __('Security check failed.', 'jazzedge-marketing')), 30);
            wp_redirect(wp_get_referer() ?: admin_url('admin.php?page=jem-add-funnel'));
            exit;
        }
        $id = isset($_POST['jem_funnel_id']) ? (int) $_POST['jem_funnel_id'] : 0;
        $name = isset($_POST['jem_funnel_name']) ? sanitize_text_field(wp_unslash($_POST['jem_funnel_name'])) : '';
        $webhook_url = isset($_POST['jem_webhook_url']) ? trim(sanitize_text_field(wp_unslash($_POST['jem_webhook_url']))) : '';
        if ($webhook_url && !preg_match('#^https?://#i', $webhook_url)) {
            $webhook_url = '';
        }
        $media_id = isset($_POST['jem_media_id']) ? absint($_POST['jem_media_id']) : null;
        $product_url = isset($_POST['jem_product_url']) ? esc_url_raw(wp_unslash($_POST['jem_product_url'])) : '';
        $product_id = isset($_POST['jem_product_id']) ? sanitize_text_field(wp_unslash($_POST['jem_product_id'])) : '';
        $coupon_prefix = isset($_POST['jem_coupon_prefix']) ? strtoupper(substr(sanitize_text_field(wp_unslash($_POST['jem_coupon_prefix'])), 0, 10)) : 'JEM';
        $discount_pct = isset($_POST['jem_discount_pct']) ? max(1, min(100, (int) $_POST['jem_discount_pct'])) : 20;
        $coupon_days = isset($_POST['jem_coupon_days']) ? max(1, (int) $_POST['jem_coupon_days']) : 3;
        $active = isset($_POST['jem_active']) ? 1 : 0;

        if (empty($name) || empty($webhook_url) || empty($product_url)) {
            set_transient('jem_funnel_message', array('error', __('Please fill in Funnel Name, Webhook URL, and Product URL.', 'jazzedge-marketing')), 30);
            wp_redirect(add_query_arg('error', 'missing', wp_get_referer() ?: admin_url('admin.php?page=jem-add-funnel')));
            exit;
        }

        $data = array(
            'name' => $name,
            'webhook_url' => $webhook_url,
            'media_id' => $media_id ?: null,
            'product_url' => $product_url,
            'product_id' => $product_id,
            'coupon_prefix' => $coupon_prefix,
            'discount_pct' => $discount_pct,
            'coupon_days' => $coupon_days,
            'active' => $active,
            'updated_at' => current_time('mysql'),
        );

        if ($id > 0) {
            $result = $this->database->update_funnel($id, $data);
            $msg = $result ? __('Funnel updated.', 'jazzedge-marketing') : __('Update failed.', 'jazzedge-marketing');
        } else {
            $data['created_at'] = current_time('mysql');
            $insert_id = $this->database->insert_funnel($data);
            $result = (bool) $insert_id;
            $msg = $result ? __('Funnel created.', 'jazzedge-marketing') : __('Failed to create funnel. Tables may not exist — try deactivating and reactivating the plugin.', 'jazzedge-marketing');
        }
        set_transient('jem_funnel_message', array($result ? 'success' : 'error', $msg), 30);
        wp_redirect(admin_url('admin.php?page=jem-marketing'));
        exit;
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'jazzedge-marketing'));
        }
        $saved = get_transient('jem_settings_message');
        if ($saved) {
            delete_transient('jem_settings_message');
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($saved) . '</p></div>';
        }
        $inactive_msg = get_option('jem_inactive_msg', '');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('JEM Marketing Settings', 'jazzedge-marketing'); ?></h1>
            <form method="post" action="<?php echo esc_url(add_query_arg('page', 'jem-settings', admin_url('admin.php'))); ?>">
                <?php wp_nonce_field('jem_save_settings', 'jem_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="jem_inactive_msg"><?php esc_html_e('Inactive Message', 'jazzedge-marketing'); ?></label></th>
                        <td>
                            <textarea id="jem_inactive_msg" name="jem_inactive_msg" rows="3" class="large-text"><?php echo esc_textarea($inactive_msg); ?></textarea>
                            <p class="description"><?php esc_html_e('Shown on the front end when a funnel is inactive. Applies to all funnels.', 'jazzedge-marketing'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'jazzedge-marketing'); ?>" /></p>
            </form>
        </div>
        <?php
    }

    public function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'jazzedge-marketing'));
        }
        if (!isset($_POST['jem_settings_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['jem_settings_nonce']), 'jem_save_settings')) {
            wp_redirect(admin_url('admin.php?page=jem-settings'));
            exit;
        }
        $inactive_msg = isset($_POST['jem_inactive_msg']) ? sanitize_textarea_field(wp_unslash($_POST['jem_inactive_msg'])) : '';
        update_option('jem_inactive_msg', $inactive_msg);
        set_transient('jem_settings_message', __('Settings saved.', 'jazzedge-marketing'), 30);
        wp_redirect(admin_url('admin.php?page=jem-settings'));
        exit;
    }
}
