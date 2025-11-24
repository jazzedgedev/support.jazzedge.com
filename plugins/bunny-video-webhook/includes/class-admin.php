<?php
/**
 * Admin Class for Bunny Video Webhook
 * 
 * Handles admin interface and settings
 * 
 * @package Bunny_Video_Webhook
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Bunny_Video_Webhook_Admin {
    
    /**
     * Logger instance
     */
    private $logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->logger = new Bunny_Video_Webhook_Logger();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Bunny Video Webhook',
            'Bunny Webhook',
            'manage_options',
            'bunny-video-webhook',
            array($this, 'render_settings_page'),
            'dashicons-video-alt3',
            30
        );
        
        add_submenu_page(
            'bunny-video-webhook',
            'Settings',
            'Settings',
            'manage_options',
            'bunny-video-webhook',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'bunny-video-webhook',
            'Logs',
            'Logs',
            'manage_options',
            'bunny-video-webhook-logs',
            array($this, 'render_logs_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('bunny_video_webhook_settings', 'bunny_video_webhook_secret');
        register_setting('bunny_video_webhook_settings', 'bunny_video_webhook_cdn_hostname');
        register_setting('bunny_video_webhook_settings', 'bunny_video_webhook_library_id');
        register_setting('bunny_video_webhook_settings', 'bunny_video_webhook_api_key');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (isset($_POST['submit']) && check_admin_referer('bunny_video_webhook_settings')) {
            update_option('bunny_video_webhook_secret', sanitize_text_field($_POST['bunny_video_webhook_secret']));
            update_option('bunny_video_webhook_cdn_hostname', sanitize_text_field($_POST['bunny_video_webhook_cdn_hostname']));
            update_option('bunny_video_webhook_library_id', sanitize_text_field($_POST['bunny_video_webhook_library_id']));
            // Only update API key if a new value was provided
            if (isset($_POST['bunny_video_webhook_api_key']) && !empty($_POST['bunny_video_webhook_api_key'])) {
                update_option('bunny_video_webhook_api_key', sanitize_text_field($_POST['bunny_video_webhook_api_key']));
            }
            
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $webhook_secret = get_option('bunny_video_webhook_secret', '');
        $cdn_hostname = get_option('bunny_video_webhook_cdn_hostname', '');
        $library_id = get_option('bunny_video_webhook_library_id', '');
        $api_key = get_option('bunny_video_webhook_api_key', '');
        
        // Check if using ALM settings as fallback
        $using_alm_library = empty($library_id) && !empty(get_option('alm_bunny_library_id', ''));
        $using_alm_api = empty($api_key) && !empty(get_option('alm_bunny_api_key', ''));
        
        $webhook_url = rest_url('bunny-video-webhook/v1/webhook');
        
        ?>
        <div class="wrap">
            <h1>Bunny Video Webhook Settings</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('bunny_video_webhook_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="webhook_url">Webhook URL</label>
                        </th>
                        <td>
                            <input type="text" id="webhook_url" value="<?php echo esc_attr($webhook_url); ?>" class="regular-text" readonly />
                            <p class="description">Use this URL when configuring your Bunny.net webhook.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bunny_video_webhook_secret">Webhook Secret</label>
                        </th>
                        <td>
                            <input type="text" id="bunny_video_webhook_secret" name="bunny_video_webhook_secret" value="<?php echo esc_attr($webhook_secret); ?>" class="regular-text" />
                            <p class="description">Optional: Set a secret to secure your webhook. Bunny.net should send this in the X-Webhook-Secret header or as a 'secret' query parameter.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bunny_video_webhook_cdn_hostname">CDN Hostname</label>
                        </th>
                        <td>
                            <input type="text" id="bunny_video_webhook_cdn_hostname" name="bunny_video_webhook_cdn_hostname" value="<?php echo esc_attr($cdn_hostname); ?>" class="regular-text" placeholder="vz-0696d3da-4b7.b-cdn.net" />
                            <p class="description">Your Bunny.net CDN hostname (e.g., vz-0696d3da-4b7.b-cdn.net). If left empty, a default will be used.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bunny_video_webhook_library_id">Video Library ID</label>
                        </th>
                        <td>
                            <input type="text" id="bunny_video_webhook_library_id" name="bunny_video_webhook_library_id" value="<?php echo esc_attr($library_id); ?>" class="regular-text" />
                            <?php if ($using_alm_library): ?>
                                <p class="description" style="color: #0073aa;">Currently using Library ID from Academy Lesson Manager plugin.</p>
                            <?php else: ?>
                                <p class="description">Your Bunny.net Video Library ID. Required to fetch video titles from the API. If left empty, will use Academy Lesson Manager settings if available.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bunny_video_webhook_api_key">API Key</label>
                        </th>
                        <td>
                            <input type="password" id="bunny_video_webhook_api_key" name="bunny_video_webhook_api_key" value="" class="regular-text" placeholder="<?php echo !empty($api_key) || $using_alm_api ? 'Leave blank to keep current value' : 'Enter your Bunny.net API Key'; ?>" />
                            <?php if ($using_alm_api): ?>
                                <p class="description" style="color: #0073aa;">Currently using API Key from Academy Lesson Manager plugin.</p>
                            <?php elseif (!empty($api_key)): ?>
                                <p class="description" style="color: #0073aa;">API Key is configured. Leave blank to keep current value.</p>
                            <?php else: ?>
                                <p class="description">Your Bunny.net API Key. Required to fetch video titles from the API. If left empty, will use Academy Lesson Manager settings if available.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2>How It Works</h2>
            <ol>
                <li>Configure your Bunny.net webhook to send POST requests to the Webhook URL above.</li>
                <li>When a video with "-sample" in the filename is uploaded, the webhook will be triggered.</li>
                <li>The plugin parses the filename to extract the lesson ID and chapter ID.</li>
                <li>The lesson database is automatically updated with the HLS Playlist URL and chapter ID.</li>
                <li>All events are logged for review in the Logs page.</li>
            </ol>
            
            <h3>Filename Format</h3>
            <p>Videos must follow this naming convention:</p>
            <code>{lesson-id}-{chapter-id}-id{chapter-id}-{title}-sample.mp4</code>
            <p>Example: <code>78-797-id797-Finding-Scale-Secrets-sample.mp4</code></p>
            <ul>
                <li>First number (78) = Lesson ID</li>
                <li>Second number (797) = Chapter ID</li>
                <li>Must contain "-sample" in the filename</li>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        // Handle clear all logs
        if (isset($_POST['clear_all_logs']) && check_admin_referer('bunny_video_webhook_clear_all')) {
            $deleted = $this->logger->delete_all_logs();
            echo '<div class="notice notice-success"><p>' . sprintf('Deleted all %d log entries.', $deleted) . '</p></div>';
        }
        
        // Handle log cleanup
        if (isset($_POST['cleanup_logs']) && check_admin_referer('bunny_video_webhook_cleanup')) {
            $days = intval($_POST['cleanup_days']);
            $deleted = $this->logger->cleanup_logs($days);
            echo '<div class="notice notice-success"><p>' . sprintf('Deleted %d log entries older than %d days.', $deleted, $days) . '</p></div>';
        }
        
        // Get filter parameters
        $log_type = isset($_GET['log_type']) ? sanitize_text_field($_GET['log_type']) : '';
        $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 50;
        $offset = ($page - 1) * $per_page;
        
        // Get logs
        $logs = $this->logger->get_logs(array(
            'log_type' => $log_type,
            'limit' => $per_page,
            'offset' => $offset
        ));
        
        // Get counts
        $total_logs = $this->logger->get_log_count();
        $error_count = $this->logger->get_log_count('error');
        $success_count = $this->logger->get_log_count('success');
        $info_count = $this->logger->get_log_count('info');
        
        ?>
        <div class="wrap">
            <h1>Bunny Video Webhook Logs</h1>
            
            <div class="bunny-webhook-stats" style="margin: 20px 0;">
                <div style="display: flex; gap: 20px;">
                    <div>
                        <strong>Total Logs:</strong> <?php echo number_format($total_logs); ?>
                    </div>
                    <div>
                        <strong>Success:</strong> <span style="color: green;"><?php echo number_format($success_count); ?></span>
                    </div>
                    <div>
                        <strong>Errors:</strong> <span style="color: red;"><?php echo number_format($error_count); ?></span>
                    </div>
                    <div>
                        <strong>Info:</strong> <?php echo number_format($info_count); ?>
                    </div>
                </div>
            </div>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <form method="get" style="display: inline-block;">
                        <input type="hidden" name="page" value="bunny-video-webhook-logs">
                        <select name="log_type">
                            <option value="">All Types</option>
                            <option value="success" <?php selected($log_type, 'success'); ?>>Success</option>
                            <option value="error" <?php selected($log_type, 'error'); ?>>Error</option>
                            <option value="info" <?php selected($log_type, 'info'); ?>>Info</option>
                            <option value="warning" <?php selected($log_type, 'warning'); ?>>Warning</option>
                        </select>
                        <input type="submit" class="button" value="Filter">
                    </form>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 150px;">Date/Time</th>
                        <th style="width: 100px;">Type</th>
                        <th>Message</th>
                        <th style="width: 100px;">Lesson ID</th>
                        <th style="width: 100px;">Chapter ID</th>
                        <th style="width: 200px;">Video ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6">No logs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log['created_at']); ?></td>
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; 
                                        <?php 
                                        if ($log['log_type'] === 'error') echo 'background: #ffebee; color: #c62828;';
                                        elseif ($log['log_type'] === 'success') echo 'background: #e8f5e9; color: #2e7d32;';
                                        elseif ($log['log_type'] === 'warning') echo 'background: #fff3e0; color: #e65100;';
                                        else echo 'background: #e3f2fd; color: #1565c0;';
                                        ?>">
                                        <?php echo esc_html(strtoupper($log['log_type'])); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($log['message']); ?></td>
                                <td><?php echo esc_html($log['lesson_id'] ?: '-'); ?></td>
                                <td><?php echo esc_html($log['chapter_id'] ?: '-'); ?></td>
                                <td><?php echo esc_html($log['video_id'] ?: '-'); ?></td>
                            </tr>
                            <?php if (!empty($log['error_details']) || !empty($log['webhook_data'])): ?>
                                <tr style="background: #f9f9f9;">
                                    <td colspan="6" style="padding-left: 30px; font-size: 11px; color: #666;">
                                        <?php if (!empty($log['error_details'])): ?>
                                            <strong>Error Details:</strong> <?php echo esc_html($log['error_details']); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($log['webhook_data'])): ?>
                                            <strong>Webhook Data:</strong> <pre style="display: inline; margin: 0;"><?php echo esc_html($log['webhook_data']); ?></pre>
                                        <?php endif; ?>
                                        <?php if (!empty($log['video_filename'])): ?>
                                            <strong>Filename:</strong> <?php echo esc_html($log['video_filename']); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($log['bunny_url'])): ?>
                                            <strong>Bunny URL:</strong> <a href="<?php echo esc_url($log['bunny_url']); ?>" target="_blank"><?php echo esc_html($log['bunny_url']); ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <hr>
            
            <h2>Log Management</h2>
            
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1; border: 1px solid #ccc; padding: 15px; border-radius: 4px;">
                    <h3 style="margin-top: 0;">Clear All Logs</h3>
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete ALL logs? This action cannot be undone.');" style="margin: 0;">
                        <?php wp_nonce_field('bunny_video_webhook_clear_all'); ?>
                        <p style="margin: 0 0 10px 0;">
                            This will permanently delete all <?php echo number_format($total_logs); ?> log entries.
                        </p>
                        <input type="submit" name="clear_all_logs" class="button button-secondary" value="Clear All Logs" style="background: #dc3232; border-color: #dc3232; color: #fff;">
                    </form>
                </div>
                
                <div style="flex: 1; border: 1px solid #ccc; padding: 15px; border-radius: 4px;">
                    <h3 style="margin-top: 0;">Cleanup Old Logs</h3>
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete old logs?');" style="margin: 0;">
                        <?php wp_nonce_field('bunny_video_webhook_cleanup'); ?>
                        <p style="margin: 0 0 10px 0;">
                            Delete logs older than 
                            <input type="number" name="cleanup_days" value="30" min="1" style="width: 60px;"> days
                        </p>
                        <input type="submit" name="cleanup_logs" class="button" value="Cleanup">
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}

