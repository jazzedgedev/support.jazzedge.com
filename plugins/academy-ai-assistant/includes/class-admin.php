<?php
/**
 * Admin Class for Academy AI Assistant
 * 
 * Handles admin interface, settings, and debug dashboard
 * Security: All methods require manage_options capability
 */

if (!defined('ABSPATH')) {
    exit;
}

class AAA_Admin {
    
    private $database;
    private $feature_flags;
    private $debug_logger;
    private $embedding_search;
    private $keyword_manager;
    
    public function __construct() {
        $this->database = new AAA_Database();
        $this->feature_flags = new AAA_Feature_Flags();
        $this->debug_logger = new AAA_Debug_Logger();
        $this->embedding_search = new AI_Embedding_Search();
        $this->keyword_manager = new AAA_Keyword_Manager();
        
        // Handle form submissions
        add_action('admin_init', array($this, 'handle_settings_save'));
        add_action('admin_init', array($this, 'handle_keyword_save'));
        add_action('admin_init', array($this, 'handle_prompts_save'));
        add_action('admin_init', array($this, 'handle_chip_prompts_save'));
        add_action('admin_init', array($this, 'handle_chip_suggestions_save'));
        
        // Register AJAX handlers
        add_action('wp_ajax_aaa_search_lessons', array($this, 'ajax_search_lessons'));
        add_action('wp_ajax_aaa_search_collections', array($this, 'ajax_search_collections'));
        add_action('wp_ajax_aaa_delete_chip_suggestion', array($this, 'ajax_delete_chip_suggestion'));
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'academy-ai-assistant') === false) {
            return;
        }
        
        // Make sure jQuery is available
        wp_enqueue_script('jquery');
        
        // Add copy functionality for debug logs
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Copy button functionality
                $(document).on("click", ".aaa-copy-btn", function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var textToCopy = $btn.data("copy-text");
                    
                    if (!textToCopy) {
                        alert("No data to copy.");
                        return;
                    }
                    
                    // Create temporary textarea
                    var $temp = $("<textarea>");
                    $("body").append($temp);
                    $temp.val(textToCopy).select();
                    
                    try {
                        var successful = document.execCommand("copy");
                        if (successful) {
                            var originalText = $btn.text();
                            $btn.text("Copied!").css("background-color", "#46b450").css("color", "#fff");
                            setTimeout(function() {
                                $btn.text(originalText).css("background-color", "").css("color", "");
                            }, 2000);
                        } else {
                            // Fallback: select the text
                            $temp.select();
                            alert("Please copy manually (text is selected).");
                        }
                    } catch (err) {
                        // Fallback: select the text
                        $temp.select();
                        alert("Please copy manually (text is selected).");
                    }
                    
                    $temp.remove();
                });
            });
        ');
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        // Security: Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        $status = $this->feature_flags->get_status();
        $table_status = $this->database->get_table_status();
        
        ?>
        <div class="wrap">
            <h1>Academy AI Assistant</h1>
            
            <div class="aaa-status-section">
                <h2>Status</h2>
                <table class="widefat">
                    <tr>
                        <th>Feature Status</th>
                        <td><?php echo $status['enable_for_all'] ? 'Enabled for all users' : 'Test mode (whitelist only)'; ?></td>
                    </tr>
                    <tr>
                        <th>Test Users</th>
                        <td><?php echo esc_html($status['test_user_count']); ?> user(s) in whitelist</td>
                    </tr>
                    <tr>
                        <th>Debug Mode</th>
                        <td><?php echo $status['debug_enabled'] ? 'Enabled' : 'Disabled'; ?></td>
                    </tr>
                    <tr>
                        <th>Current User Access</th>
                        <td><?php echo $status['current_user_can_access'] ? 'Yes' : 'No'; ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="aaa-tables-section">
                <h2>Database Tables</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Status</th>
                            <th>Row Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Display plugin tables first
                        $plugin_tables = array('conversations', 'sessions', 'debug_logs');
                        foreach ($plugin_tables as $name):
                            if (isset($table_status[$name])):
                                $info = $table_status[$name];
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($name); ?></strong></td>
                            <td><?php echo $info['exists'] ? '✓ Exists' : '✗ Missing'; ?></td>
                            <td><?php echo isset($info['row_count']) ? esc_html(number_format($info['row_count'])) : 'N/A'; ?></td>
                        </tr>
                        <?php 
                            endif;
                        endforeach; 
                        
                        // Display embeddings table (external dependency)
                        if (isset($table_status['transcript_embeddings'])):
                            $info = $table_status['transcript_embeddings'];
                        ?>
                        <tr style="border-top: 2px solid #ddd;">
                            <td><strong><?php echo esc_html('transcript_embeddings'); ?></strong> <em>(from chapter-transcription plugin)</em></td>
                            <td><?php echo $info['exists'] ? '✓ Exists' : '✗ Missing'; ?></td>
                            <td><?php echo isset($info['row_count']) ? esc_html(number_format($info['row_count'])) : 'N/A'; ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if (isset($table_status['transcript_embeddings']) && $table_status['transcript_embeddings']['exists']): ?>
                <p class="description" style="margin-top: 10px;">
                    <strong>Embedding Search Status:</strong> 
                    <?php 
                    $stats = $this->embedding_search->get_statistics();
                    if ($stats['available']):
                        echo 'Available - ' . number_format($stats['total_embeddings']) . ' embeddings across ' . number_format($stats['total_transcripts']) . ' transcripts';
                    else:
                        echo 'Not available (tables missing)';
                    endif;
                    ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // Security: Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        $status = $this->feature_flags->get_status();
        $test_user_ids = implode(', ', $status['test_user_ids']);
        
        ?>
        <div class="wrap">
            <h1>AI Assistant Settings</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('aaa_settings_save', 'aaa_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="aaa_enable_for_all">Enable for All Users</label>
                        </th>
                        <td>
                            <input type="checkbox" id="aaa_enable_for_all" name="aaa_enable_for_all" value="1" <?php checked($status['enable_for_all']); ?>>
                            <p class="description">When enabled, all logged-in users can access AI features. When disabled, only test users can access.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="aaa_test_user_ids">Test User IDs</label>
                        </th>
                        <td>
                            <input type="text" id="aaa_test_user_ids" name="aaa_test_user_ids" value="<?php echo esc_attr($test_user_ids); ?>" class="regular-text">
                            <p class="description">Comma-separated list of user IDs who can access AI features when "Enable for All Users" is disabled.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="aaa_debug_enabled">Debug Mode</label>
                        </th>
                        <td>
                            <input type="checkbox" id="aaa_debug_enabled" name="aaa_debug_enabled" value="1" <?php checked($status['debug_enabled']); ?>>
                            <p class="description">Enable debug logging and frontend debug panel for test users.</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="aaa_ai_quota_limit">AI Quota Limit</label>
                        </th>
                        <td>
                            <input type="number" id="aaa_ai_quota_limit" name="aaa_ai_quota_limit" value="<?php echo esc_attr(get_option('aaa_ai_quota_limit', 50000)); ?>" class="small-text">
                            <p class="description">Monthly token limit for AI API calls.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Debug logs page
     */
    public function debug_page() {
        // Security: Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        // Handle debug toggle
        if (isset($_POST['toggle_debug']) && wp_verify_nonce($_POST['_wpnonce'], 'aaa_toggle_debug')) {
            $current_status = get_option('aaa_debug_enabled', false);
            $new_status = !$current_status;
            update_option('aaa_debug_enabled', $new_status);
            echo '<div class="notice notice-success"><p>Debug logging ' . ($new_status ? 'enabled' : 'disabled') . '.</p></div>';
        }
        
        // Handle delete all logs
        if (isset($_POST['delete_all_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'aaa_delete_all_logs')) {
            $deleted = $this->debug_logger->delete_all_logs();
            if ($deleted !== false) {
                echo '<div class="notice notice-success"><p>Deleted all ' . esc_html($deleted) . ' log entries.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to delete all logs. Please check database permissions.</p></div>';
            }
        }
        
        // Handle log clearing
        if (isset($_POST['clear_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'aaa_clear_logs')) {
            $days = isset($_POST['days']) ? absint($_POST['days']) : 30;
            if ($days > 0) {
                $deleted = $this->debug_logger->clear_old_logs($days);
                if ($deleted !== false) {
                    echo '<div class="notice notice-success"><p>Deleted ' . esc_html($deleted) . ' log entries older than ' . esc_html($days) . ' days.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Failed to delete logs. Please check database permissions.</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>Invalid number of days specified.</p></div>';
            }
        }
        
        // Get debug status
        $debug_enabled = $this->debug_logger->is_enabled();
        
        // Get logs
        $logs = $this->debug_logger->get_logs(array('limit' => 100));
        
        ?>
        <div class="wrap">
            <h1>Debug Logs</h1>
            
            <div class="aaa-debug-controls" style="margin: 20px 0; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <div style="flex: 1; min-width: 200px;">
                        <h2 style="margin: 0 0 10px 0; font-size: 16px;">Debug Status</h2>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo $debug_enabled ? '#46b450' : '#dc3232'; ?>;"></span>
                            <strong style="color: <?php echo $debug_enabled ? '#46b450' : '#dc3232'; ?>;">
                                <?php echo $debug_enabled ? 'ENABLED' : 'DISABLED'; ?>
                            </strong>
                        </div>
                        <?php if (!$debug_enabled): ?>
                            <p style="color: #dc3232; margin: 8px 0 0 0; font-size: 13px;">⚠️ No new logs will be written until enabled.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                        <form method="post" action="" style="margin: 0;">
                            <?php wp_nonce_field('aaa_toggle_debug'); ?>
                            <input type="hidden" name="toggle_debug" value="1">
                            <input type="submit" class="button <?php echo $debug_enabled ? 'button-secondary' : 'button-primary'; ?>" 
                                   value="<?php echo $debug_enabled ? 'Disable Logging' : 'Enable Logging'; ?>">
                        </form>
                        
                        <a href="<?php echo esc_url(admin_url('admin.php?page=academy-ai-assistant-debug')); ?>" 
                           class="button button-secondary" 
                           style="text-decoration: none;">
                            <span class="dashicons dashicons-update" style="font-size: 16px; line-height: 1.5; vertical-align: middle; margin-right: 4px;"></span>
                            Refresh
                        </a>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 15px;">
                    <h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 600;">Log Management</h3>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                        <form method="post" action="" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                            <?php wp_nonce_field('aaa_clear_logs'); ?>
                            <label style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                <span style="white-space: nowrap;">Delete logs older than</span>
                                <input type="number" name="days" value="30" min="1" max="365" class="small-text" style="width: 60px; margin: 0;">
                                <span style="white-space: nowrap;">days</span>
                            </label>
                            <input type="submit" name="clear_logs" class="button button-secondary" value="Clear Old Logs" style="margin: 0;">
                        </form>
                        
                        <form method="post" action="" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete ALL debug logs? This action cannot be undone.');">
                            <?php wp_nonce_field('aaa_delete_all_logs'); ?>
                            <input type="hidden" name="delete_all_logs" value="1">
                            <input type="submit" class="button button-secondary" value="Delete All Logs" style="background-color: #dc3232; color: white; border-color: #dc3232; margin: 0;">
                        </form>
                    </div>
                </div>
            </div>
            
            <div style="margin: 20px 0; padding: 12px 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 2px;">
                <strong>Total Logs:</strong> <?php echo count($logs); ?> (showing last 100)
            </div>
            
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Data</th>
                        <th>Response Time</th>
                        <th>Tokens</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="8">No debug logs found.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log->id); ?></td>
                        <td><?php echo esc_html($log->user_id); ?></td>
                        <td><?php echo esc_html($log->log_type); ?></td>
                        <td style="max-width: 400px; word-wrap: break-word;">
                            <?php echo esc_html($log->message); ?>
                            <?php 
                            // Show query prominently for search logs
                            if (in_array($log->log_type, array('embedding_search', 'lesson_search')) && !empty($log->data)) {
                                $data = json_decode($log->data, true);
                                if ($data && isset($data['query'])) {
                                    echo '<br/><strong style="color: #0073aa;">Query: "' . esc_html($data['query']) . '"</strong>';
                                }
                            }
                            ?>
                        </td>
                        <td style="max-width: 300px;">
                            <?php 
                            if (!empty($log->data)) {
                                $data = json_decode($log->data, true);
                                if ($data && is_array($data)) {
                                    // For SQL queries, show SQL prominently
                                    if (in_array($log->log_type, array('lesson_search_sql', 'embedding_search_sql', 'embedding_enrich_sql', 'lesson_lookup_sql', 'keyword_search_sql'))) {
                                        $sql_content = !empty($data['sql']) ? $data['sql'] : '';
                                        $full_data = print_r($data, true);
                                        
                                        echo '<details><summary>View SQL Query</summary>';
                                        echo '<div style="margin-bottom: 10px;">';
                                        echo '<button type="button" class="button button-small aaa-copy-btn" data-copy-text="' . esc_attr($sql_content) . '" style="margin-right: 5px;">Copy SQL</button>';
                                        echo '<button type="button" class="button button-small aaa-copy-btn" data-copy-text="' . esc_attr($full_data) . '">Copy All Data</button>';
                                        echo '</div>';
                                        
                                        if (!empty($data['sql'])) {
                                            echo '<pre class="aaa-copyable" style="font-size: 11px; max-height: 300px; overflow: auto; background: #f5f5f5; padding: 10px; border: 1px solid #ddd; margin: 5px 0; position: relative;">';
                                            echo '<strong>SQL:</strong><br/>';
                                            echo esc_html($data['sql']);
                                            echo '</pre>';
                                        }
                                        if (!empty($data['user_query'])) {
                                            echo '<p><strong>User Query:</strong> ' . esc_html($data['user_query']) . '</p>';
                                        }
                                        if (!empty($data['keywords_extracted'])) {
                                            echo '<p><strong>Keywords Extracted:</strong> ' . esc_html(implode(', ', $data['keywords_extracted'])) . '</p>';
                                        }
                                        echo '<pre class="aaa-copyable" style="font-size: 11px; max-height: 200px; overflow: auto;">';
                                        echo esc_html($full_data);
                                        echo '</pre></details>';
                                    } elseif ($log->log_type === 'embedding_search' && !empty($data['lessons_found'])) {
                                        $lessons_data = print_r($data['lessons_found'], true);
                                        echo '<details><summary>View Details</summary>';
                                        echo '<div style="margin-bottom: 10px;">';
                                        echo '<button type="button" class="button button-small aaa-copy-btn" data-copy-text="' . esc_attr($lessons_data) . '">Copy Data</button>';
                                        echo '</div>';
                                        echo '<pre class="aaa-copyable" style="font-size: 11px; max-height: 200px; overflow: auto;">';
                                        echo esc_html($lessons_data);
                                        echo '</pre></details>';
                                    } else {
                                        $full_data = print_r($data, true);
                                        echo '<details><summary>View Data</summary>';
                                        echo '<div style="margin-bottom: 10px;">';
                                        echo '<button type="button" class="button button-small aaa-copy-btn" data-copy-text="' . esc_attr($full_data) . '">Copy Data</button>';
                                        echo '</div>';
                                        echo '<pre class="aaa-copyable" style="font-size: 11px; max-height: 200px; overflow: auto;">';
                                        echo esc_html($full_data);
                                        echo '</pre></details>';
                                    }
                                } else {
                                    echo '<span style="color: #999;">-</span>';
                                }
                            } else {
                                echo '<span style="color: #999;">-</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo $log->response_time ? esc_html($log->response_time) . 'ms' : '-'; ?></td>
                        <td><?php echo $log->tokens_used ? esc_html($log->tokens_used) : '-'; ?></td>
                        <td><?php echo esc_html($log->created_at); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Handle settings form submission
     */
    public function handle_settings_save() {
        // Security: Check permissions and nonce
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (!isset($_POST['aaa_settings_nonce']) || !wp_verify_nonce($_POST['aaa_settings_nonce'], 'aaa_settings_save')) {
            return;
        }
        
        // Save settings
        $enable_for_all = isset($_POST['aaa_enable_for_all']) ? true : false;
        update_option('aaa_enable_for_all', $enable_for_all);
        
        if (isset($_POST['aaa_test_user_ids'])) {
            $result = $this->feature_flags->save_test_user_ids($_POST['aaa_test_user_ids']);
            if (is_wp_error($result)) {
                add_settings_error('aaa_settings', 'aaa_test_user_ids', $result->get_error_message());
            }
        }
        
        $debug_enabled = isset($_POST['aaa_debug_enabled']) ? true : false;
        update_option('aaa_debug_enabled', $debug_enabled);
        
        if (isset($_POST['aaa_ai_quota_limit'])) {
            $quota = absint($_POST['aaa_ai_quota_limit']);
            update_option('aaa_ai_quota_limit', $quota);
        }
        
        add_settings_error('aaa_settings', 'aaa_settings_saved', 'Settings saved successfully.', 'updated');
    }
    
    /**
     * Handle keyword mapping form submission
     */
    public function handle_keyword_save() {
        // Security: Check permissions and nonce
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            check_admin_referer('delete_keyword_' . $_GET['id']);
            $deleted = $this->keyword_manager->delete_keyword(absint($_GET['id']));
            if ($deleted) {
                add_settings_error('aaa_keywords', 'aaa_keyword_deleted', 'Keyword mapping deleted successfully.', 'updated');
            } else {
                add_settings_error('aaa_keywords', 'aaa_keyword_delete_failed', 'Failed to delete keyword mapping.', 'error');
            }
            return;
        }
        
        // Handle add/edit
        if (!isset($_POST['aaa_keyword_nonce']) || !wp_verify_nonce($_POST['aaa_keyword_nonce'], 'aaa_keyword_save')) {
            return;
        }
        
        if (isset($_POST['aaa_keyword']) && isset($_POST['aaa_lesson_id'])) {
            $keyword = sanitize_text_field($_POST['aaa_keyword']);
            $lesson_id = absint($_POST['aaa_lesson_id']);
            $priority = isset($_POST['aaa_priority']) ? absint($_POST['aaa_priority']) : 10;
            
            $result = $this->keyword_manager->add_keyword($keyword, $lesson_id, $priority);
            
            if (is_wp_error($result)) {
                add_settings_error('aaa_keywords', 'aaa_keyword_error', $result->get_error_message(), 'error');
            } else {
                add_settings_error('aaa_keywords', 'aaa_keyword_saved', 'Keyword mapping saved successfully.', 'updated');
            }
        }
    }
    
    /**
     * Keywords management page
     */
    public function keywords_page() {
        // Security: Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        // Ensure keyword_lessons table exists (in case it wasn't created on activation)
        $this->ensure_keyword_table_exists();
        
        $page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $per_page = 20;
        $mappings = $this->keyword_manager->get_all_mappings($page, $per_page);
        $total = $this->keyword_manager->get_total_count();
        $total_pages = ceil($total / $per_page);
        
        ?>
        <div class="wrap">
            <h1>Keyword Mappings</h1>
            <p>Map keywords to specific lessons. When users search for these keywords, the AI will prioritize these lessons.</p>
            
            <?php settings_errors('aaa_keywords'); ?>
            
            <div style="margin: 20px 0;">
                <h2>Add New Keyword Mapping</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('aaa_keyword_save', 'aaa_keyword_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="aaa_keyword">Keyword</label></th>
                            <td>
                                <input type="text" id="aaa_keyword" name="aaa_keyword" class="regular-text" required />
                                <p class="description">The keyword users might search for (e.g., "pain", "improvisation for beginners")</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="aaa_lesson_search">Lesson</label></th>
                            <td>
                                <input type="text" id="aaa_lesson_search" class="regular-text" placeholder="Search for a lesson..." autocomplete="off" style="width: 100%; max-width: 600px;" />
                                <input type="hidden" id="aaa_lesson_id" name="aaa_lesson_id" value="" required />
                                <div id="aaa_lesson_search_results" style="margin-top: 5px; max-width: 600px; display: none;"></div>
                                <p class="description">Start typing to search for lessons. Click a lesson to select it.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="aaa_priority">Priority</label></th>
                            <td>
                                <input type="number" id="aaa_priority" name="aaa_priority" value="10" min="1" max="100" />
                                <p class="description">Lower numbers = higher priority (1 is highest, 100 is lowest). Default: 10</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Add Keyword Mapping" />
                    </p>
                </form>
            </div>
            
            <hr />
            
            <h2>Existing Mappings</h2>
            <?php if (empty($mappings)): ?>
                <p>No keyword mappings found.</p>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Keyword</th>
                            <th>Lesson</th>
                            <th>Priority</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mappings as $mapping): ?>
                            <tr>
                                <td><?php echo esc_html($mapping['id']); ?></td>
                                <td><strong><?php echo esc_html($mapping['keyword']); ?></strong></td>
                                <td>
                                    <?php if (!empty($mapping['lesson_title'])): ?>
                                        <?php echo esc_html($mapping['lesson_title']); ?>
                                        <?php if (!empty($mapping['post_id'])): ?>
                                            <a href="<?php echo esc_url(get_edit_post_link($mapping['post_id'])); ?>" target="_blank">(Edit)</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <em>Lesson ID <?php echo esc_html($mapping['lesson_id']); ?> (not found)</em>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($mapping['priority']); ?></td>
                                <td><?php echo esc_html($mapping['created_at']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $mapping['id'])), 'delete_keyword_' . $mapping['id'])); ?>" 
                                       class="button button-small" 
                                       onclick="return confirm('Are you sure you want to delete this keyword mapping?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $page
                            ));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var searchTimeout;
            var $searchInput = $('#aaa_lesson_search');
            var $lessonIdInput = $('#aaa_lesson_id');
            var $resultsDiv = $('#aaa_lesson_search_results');
            
            // Search for lessons
            $searchInput.on('input', function() {
                var query = $(this).val().trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    $resultsDiv.hide().empty();
                    $lessonIdInput.val('');
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'aaa_search_lessons',
                            nonce: '<?php echo wp_create_nonce('aaa_search_lessons'); ?>',
                            query: query
                        },
                        success: function(response) {
                            if (response.success && response.data && response.data.length > 0) {
                                var html = '<ul style="list-style:none; padding:0; margin:0; border:1px solid #ddd; background:#fff; max-height:300px; overflow-y:auto; box-shadow:0 2px 5px rgba(0,0,0,0.1);">';
                                $.each(response.data, function(i, lesson) {
                                    if (lesson.lesson_id && lesson.lesson_id > 0) {
                                        var displayText = lesson.lesson_title;
                                        if (lesson.collection_title) {
                                            displayText += ' (' + lesson.collection_title + ')';
                                        }
                                        displayText += ' (ID: ' + lesson.lesson_id + ')';
                                        
                                        html += '<li class="aaa-lesson-result" ';
                                        html += 'data-lesson-id="' + lesson.lesson_id + '" ';
                                        html += 'data-lesson-title="' + $('<div>').text(lesson.lesson_title).html() + '" ';
                                        html += 'style="padding:10px 12px; border-bottom:1px solid #eee; cursor:pointer;" ';
                                        html += 'onmouseover="this.style.background=\'#f5f5f5\'" ';
                                        html += 'onmouseout="this.style.background=\'#fff\'">';
                                        html += '<strong>' + $('<div>').text(lesson.lesson_title).html() + '</strong>';
                                        if (lesson.collection_title) {
                                            html += ' <span style="color:#666;">(' + $('<div>').text(lesson.collection_title).html() + ')</span>';
                                        }
                                        html += ' <span style="color:#999; font-size:11px;">(Lesson ID: ' + lesson.lesson_id + ')</span>';
                                        html += '</li>';
                                    }
                                });
                                html += '</ul>';
                                $resultsDiv.html(html).show();
                                
                                // Bind click handlers
                                $resultsDiv.find('.aaa-lesson-result').on('click', function() {
                                    var lessonId = $(this).data('lesson-id');
                                    var lessonTitle = $(this).data('lesson-title');
                                    var collectionTitle = $(this).find('span').first().text().replace(/[()]/g, '');
                                    var displayText = lessonTitle;
                                    if (collectionTitle) {
                                        displayText += ' (' + collectionTitle + ')';
                                    }
                                    displayText += ' (ID: ' + lessonId + ')';
                                    
                                    $searchInput.val(displayText);
                                    $lessonIdInput.val(lessonId);
                                    $resultsDiv.hide();
                                });
                            } else {
                                $resultsDiv.html('<ul style="list-style:none; padding:8px; margin:0; border:1px solid #ddd; background:#fff;"><li style="color:#666;">No lessons found</li></ul>').show();
                            }
                        },
                        error: function() {
                            $resultsDiv.html('<ul style="list-style:none; padding:8px; margin:0; border:1px solid #ddd; background:#fff;"><li style="color:#a00;">Error searching lessons</li></ul>').show();
                        }
                    });
                }, 300);
            });
            
            // Hide results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#aaa_lesson_search, #aaa_lesson_search_results').length) {
                    $resultsDiv.hide();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for searching lessons
     */
    public function ajax_search_lessons() {
        check_ajax_referer('aaa_search_lessons', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions.'));
        }
        
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (strlen($query) < 2) {
            wp_send_json_success(array());
        }
        
        if (!class_exists('ALM_Database')) {
            wp_send_json_error(array('message' => 'Academy Lesson Manager not available.'));
        }
        
        global $wpdb;
        $alm_db = new ALM_Database();
        $lessons_table = $alm_db->get_table_name('lessons');
        $collections_table = $alm_db->get_table_name('collections');
        
        $lessons = $wpdb->get_results($wpdb->prepare(
            "SELECT l.ID as lesson_id, l.lesson_title, l.post_id, l.collection_id, c.collection_title
             FROM {$lessons_table} l
             LEFT JOIN {$collections_table} c ON l.collection_id = c.ID
             WHERE l.lesson_title LIKE %s AND l.post_id > 0
             ORDER BY c.collection_title ASC, l.lesson_title ASC 
             LIMIT 50",
            '%' . $wpdb->esc_like($query) . '%'
        ), ARRAY_A);
        
        $results = array();
        foreach ($lessons as $lesson) {
            $results[] = array(
                'id' => absint($lesson['lesson_id']),
                'title' => $lesson['lesson_title'],
                'post_id' => absint($lesson['post_id']),
                'lesson_id' => absint($lesson['lesson_id']), // Keep for backward compatibility
                'lesson_title' => $lesson['lesson_title'], // Keep for backward compatibility
                'collection_title' => $lesson['collection_title'] ? $lesson['collection_title'] : ''
            );
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX handler for searching collections
     */
    public function ajax_search_collections() {
        check_ajax_referer('aaa_search_collections', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions.'));
            return;
        }
        
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (strlen($query) < 2) {
            wp_send_json_success(array());
            return;
        }
        
        global $wpdb;
        $collections_table = $wpdb->prefix . 'alm_collections';
        
        $collections = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, collection_title, post_id 
             FROM {$collections_table} 
             WHERE collection_title LIKE %s 
             AND post_id > 0
             ORDER BY collection_title ASC 
             LIMIT 50",
            '%' . $wpdb->esc_like($query) . '%'
        ), ARRAY_A);
        
        $results = array();
        foreach ($collections as $collection) {
            $results[] = array(
                'id' => absint($collection['ID']),
                'title' => $collection['collection_title'],
                'post_id' => absint($collection['post_id'])
            );
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Handle prompts form submission
     */
    public function handle_prompts_save() {
        // Security: Check permissions and nonce
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (!isset($_POST['aaa_prompts_nonce']) || !wp_verify_nonce($_POST['aaa_prompts_nonce'], 'aaa_prompts_save')) {
            return;
        }
        
        if (isset($_POST['aaa_prompts'])) {
            $prompts = array();
            
            // Sanitize all prompts
            foreach ($_POST['aaa_prompts'] as $location => $prompt) {
                $location = sanitize_text_field($location);
                
                // Remove slashes added by WordPress magic quotes
                $prompt = wp_unslash($prompt);
                
                // Sanitize but preserve newlines and formatting
                $prompt = sanitize_textarea_field($prompt);
                
                $prompts[$location] = $prompt;
            }
            
            // Save to options (WordPress will handle serialization)
            update_option('aaa_custom_prompts', $prompts);
        }
        
        // Save custom sections (music theory accuracy and lesson recommendations)
        if (isset($_POST['aaa_sections'])) {
            $sections = array();
            
            foreach ($_POST['aaa_sections'] as $section_key => $section_content) {
                $section_key = sanitize_text_field($section_key);
                
                // Remove slashes added by WordPress magic quotes
                $section_content = wp_unslash($section_content);
                
                // Sanitize but preserve newlines and formatting
                $section_content = sanitize_textarea_field($section_content);
                
                $sections[$section_key] = $section_content;
            }
            
            // Save to options
            update_option('aaa_custom_sections', $sections);
        }
        
        add_settings_error('aaa_prompts', 'aaa_prompts_saved', 'AI Prompts and sections saved successfully.', 'updated');
    }
    
    /**
     * Get available locations for prompts
     * 
     * @return array Array of location data
     */
    private function get_available_locations() {
        return array(
            'main' => array(
                'name' => 'Main Page',
                'description' => 'Full-page AI assistant (default shortcode)',
                'default_prompt' => $this->get_default_prompt()
            ),
            'dashboard' => array(
                'name' => 'Dashboard Widget',
                'description' => 'Smaller widget on the dashboard',
                'default_prompt' => $this->get_default_prompt()
            ),
            'sidebar' => array(
                'name' => 'Sidebar Flyout',
                'description' => 'Sidebar flyout panel',
                'default_prompt' => $this->get_default_prompt()
            )
        );
    }
    
    /**
     * Get default system prompt for Jazzedge AI
     * 
     * @return string Default system prompt
     */
    private function get_default_prompt() {
        return "You are Jazzedge AI, a knowledgeable and friendly music teacher and professional musician. " .
               "You help students learn jazz piano, music theory, and piano technique. " .
               "You are encouraging, patient, and provide clear explanations. " .
               "You recommend relevant lessons from the JazzEdge Academy when appropriate. " .
               "You always provide accurate music theory information and admit when you're uncertain.";
    }
    
    /**
     * Get default sections (music theory accuracy and lesson recommendations)
     * 
     * @return array Default sections
     */
    private function get_default_sections() {
        return array(
            'music_theory_accuracy' => "🎓 CRITICAL: MUSIC THEORY ACCURACY:\n" .
                "1. You MUST provide ACCURATE music theory information. If you're unsure about harmony, chord construction, scales, or music theory, you MUST say so rather than guessing.\n" .
                "2. For chord questions: Know that a minor 7th chord contains root, minor 3rd, perfect 5th, and minor 7th. For example, Em7 = E-G-B-D (D is the 7th, NOT F#).\n" .
                "3. F# over E minor would be the 9th (or 2nd), not the 7th. Be precise with interval names and chord construction.\n" .
                "4. If you make a mistake or are corrected, acknowledge it and provide the correct information.\n" .
                "5. When explaining music theory, use correct terminology and be precise. Students rely on your accuracy.",
            'lesson_recommendations_found' => "🎯 CRITICAL INSTRUCTIONS FOR LESSON RECOMMENDATIONS:\n" .
                "1. The above lessons were found by searching the lesson database and are RELEVANT to the user's question.\n" .
                "2. You MUST PROACTIVELY recommend these lessons whenever they are relevant, even if the user doesn't explicitly ask for recommendations.\n" .
                "3. If the user asks a question about a topic (chords, scales, techniques, songs, etc.), and lessons are found above, you MUST include lesson recommendations in your response.\n" .
                "4. If lessons are marked as 'keyword_match', prioritize those FIRST as they are specifically matched to the user's query.\n" .
                "5. When recommending lessons, naturally integrate them into your answer. For example:\n" .
                "   - 'To learn more about this, check out [Lesson Title](URL).'\n" .
                "   - 'I recommend [Lesson Title](URL) which covers this topic in detail.'\n" .
                "   - 'For hands-on practice, see [Lesson Title - Chapter Name](URL) which demonstrates this at [timestamp].'\n" .
                "6. If a lesson has a chapter title listed (e.g., 'Chapter: Mixolydian Scale'), you MUST mention the chapter name in your response.\n" .
                "7. If a lesson has a timestamp listed (e.g., 'starts at 0:54' or 'at 54 seconds'), you MUST mention this timestamp in your response text (e.g., 'at 54 seconds' or 'starting at 0:54').\n" .
                "8. Chapter URLs use the format ?c=chapter-slug (e.g., ?c=176-part-2) - these link directly to the specific chapter.\n" .
                "9. When creating links, use ONLY the exact URLs provided above in the format: [Lesson Title - Chapter Name](EXACT_URL_FROM_ABOVE).\n" .
                "10. NEVER generate, guess, or create URLs from lesson titles. ONLY use the exact URLs listed above.\n" .
                "11. If a lesson is listed above with a URL, you MUST use that exact URL - do not modify it.\n" .
                "12. Do NOT say you don't have lessons if lessons are listed above with URLs.\n" .
                "13. Copy the URL exactly as shown above - do not change /lesson/ to /lessons/ or modify the slug.\n" .
                "14. PROACTIVELY suggest lessons - don't wait for the user to ask. If lessons are relevant to their question, include them naturally in your response.",
            'lesson_recommendations_none' => "⚠️ CRITICAL: NO LESSONS WERE FOUND IN THE DATABASE SEARCH.\n" .
                "1. You MUST NOT recommend, suggest, or mention any specific lesson titles.\n" .
                "2. You MUST NOT make up lesson names like 'Jazz Piano Basics', 'Introduction to Swing', or any other lesson titles.\n" .
                "3. You can provide general guidance and advice, but you MUST explicitly state that you couldn't find specific lessons in the database.\n" .
                "4. If the user asks for lesson recommendations, you should say something like: 'I couldn't find specific lessons matching your request in the database, but I can provide general guidance...'\n" .
                "5. NEVER invent lesson titles or pretend lessons exist when they don't.",
            'competitor_blocking' => "🚫 CRITICAL: COMPETITOR BLOCKING:\n" .
                "1. You MUST NEVER mention, recommend, or discuss competitors or alternative piano learning platforms.\n" .
                "2. NEVER mention competitors such as: Pianote, PianoWithJonny, Playground Sessions, Flowkey, Simply Piano, Skoove, or any other piano learning platforms.\n" .
                "3. If a user asks about competitors, politely redirect them by saying something like: 'I focus on helping you with JazzEdge Academy content. How can I help you with jazz piano today?'\n" .
                "4. NEVER provide information about competitor platforms, their features, pricing, or content.\n" .
                "5. NEVER compare JazzEdge Academy to competitors.\n" .
                "6. If asked about competitors, simply state that you're here to help with JazzEdge Academy lessons and content only.\n" .
                "7. Focus exclusively on JazzEdge Academy lessons, content, and resources."
        );
    }
    
    /**
     * AI Prompts management page
     */
    public function prompts_page() {
        // Security: Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        // Get available locations
        $locations = $this->get_available_locations();
        
        // Get custom prompts (if any)
        $custom_prompts = get_option('aaa_custom_prompts', array());
        
        ?>
        <div class="wrap">
            <h1>AI Prompts by Location</h1>
            <p>Customize the system prompts for each location where Jazzedge AI is used. These prompts control how the AI responds to users in different contexts.</p>
            
            <?php settings_errors('aaa_prompts'); ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('aaa_prompts_save', 'aaa_prompts_nonce'); ?>
                
                <?php foreach ($locations as $location_id => $location_data): ?>
                    <?php
                    // Get current prompt (custom or default)
                    $current_prompt = '';
                    if (isset($custom_prompts[$location_id]) && !empty($custom_prompts[$location_id])) {
                        // Remove slashes when displaying
                        $current_prompt = wp_unslash($custom_prompts[$location_id]);
                    } else {
                        // Use default prompt
                        $current_prompt = $location_data['default_prompt'];
                    }
                    ?>
                    <div style="margin: 30px 0; padding: 20px; border: 1px solid #ddd; background: #fff;">
                        <h2><?php echo esc_html($location_data['name']); ?></h2>
                        <p style="color: #666; margin-top: -10px;"><?php echo esc_html($location_data['description']); ?></p>
                        <p style="color: #888; font-size: 12px; margin-top: 5px;">
                            <strong>Location ID:</strong> <code><?php echo esc_html($location_id); ?></code>
                            <?php if ($location_id === 'main'): ?>
                                <br><strong>Usage:</strong> <code>[academy_ai_assistant location="main"]</code> or <code>[academy_ai_assistant]</code>
                            <?php else: ?>
                                <br><strong>Usage:</strong> <code>[academy_ai_assistant location="<?php echo esc_attr($location_id); ?>"]</code>
                            <?php endif; ?>
                        </p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="aaa_prompt_<?php echo esc_attr($location_id); ?>">System Prompt</label>
                                </th>
                                <td>
                                    <textarea 
                                        id="aaa_prompt_<?php echo esc_attr($location_id); ?>" 
                                        name="aaa_prompts[<?php echo esc_attr($location_id); ?>]" 
                                        rows="12" 
                                        class="large-text code" 
                                        style="font-family: monospace; font-size: 13px;"
                                    ><?php echo esc_textarea($current_prompt); ?></textarea>
                                    <p class="description">
                                        This prompt defines how Jazzedge AI responds in the <strong><?php echo esc_html($location_data['name']); ?></strong> location. 
                                        You can use placeholders like <code>{lessons}</code> which will be replaced with lesson information when available.
                                    </p>
                                    <p class="description">
                                        <strong>Important:</strong> The system will automatically add lesson recommendation instructions and music theory accuracy requirements. 
                                        You don't need to include those in your custom prompt.
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <p>
                            <button type="button" class="button button-secondary aaa-reset-prompt" data-location="<?php echo esc_attr($location_id); ?>">
                                Reset to Default
                            </button>
                        </p>
                    </div>
                <?php endforeach; ?>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Save All Prompts" />
                </p>
            </form>
            
            <hr style="margin: 40px 0;">
            
            <h2>Automatically Added Sections</h2>
            <p>These sections are automatically added to all prompts. Customize them here to control how the AI handles music theory accuracy and lesson recommendations.</p>
            
            <?php
            // Get custom sections (if any)
            $custom_sections = get_option('aaa_custom_sections', array());
            
            // Get default sections
            $default_sections = $this->get_default_sections();
            ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('aaa_prompts_save', 'aaa_prompts_nonce'); ?>
                
                <!-- Music Theory Accuracy Section -->
                <div style="margin: 30px 0; padding: 20px; border: 1px solid #ddd; background: #fff;">
                    <h3>🎓 Music Theory Accuracy Requirements</h3>
                    <p style="color: #666; margin-top: -10px;">This section is automatically added to ensure the AI provides accurate music theory information.</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aaa_section_music_theory">Music Theory Accuracy Section</label>
                            </th>
                            <td>
                                <?php
                                $current_music_theory = '';
                                if (isset($custom_sections['music_theory_accuracy']) && !empty($custom_sections['music_theory_accuracy'])) {
                                    $current_music_theory = wp_unslash($custom_sections['music_theory_accuracy']);
                                } else {
                                    $current_music_theory = $default_sections['music_theory_accuracy'];
                                }
                                ?>
                                <textarea 
                                    id="aaa_section_music_theory" 
                                    name="aaa_sections[music_theory_accuracy]" 
                                    rows="8" 
                                    class="large-text code" 
                                    style="font-family: monospace; font-size: 13px;"
                                ><?php echo esc_textarea($current_music_theory); ?></textarea>
                                <p class="description">
                                    This section is added automatically to all prompts to ensure accurate music theory responses.
                                </p>
                                <p>
                                    <button type="button" class="button button-secondary aaa-reset-section" data-section="music_theory_accuracy">
                                        Reset to Default
                                    </button>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Lesson Recommendations Section (When Lessons Found) -->
                <div style="margin: 30px 0; padding: 20px; border: 1px solid #ddd; background: #fff;">
                    <h3>🎯 Lesson Recommendations (When Lessons Are Found)</h3>
                    <p style="color: #666; margin-top: -10px;">This section is automatically added when relevant lessons are found in the database.</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aaa_section_lessons_found">Lesson Recommendations Section</label>
                            </th>
                            <td>
                                <?php
                                $current_lessons_found = '';
                                if (isset($custom_sections['lesson_recommendations_found']) && !empty($custom_sections['lesson_recommendations_found'])) {
                                    $current_lessons_found = wp_unslash($custom_sections['lesson_recommendations_found']);
                                } else {
                                    $current_lessons_found = $default_sections['lesson_recommendations_found'];
                                }
                                ?>
                                <textarea 
                                    id="aaa_section_lessons_found" 
                                    name="aaa_sections[lesson_recommendations_found]" 
                                    rows="12" 
                                    class="large-text code" 
                                    style="font-family: monospace; font-size: 13px;"
                                ><?php echo esc_textarea($current_lessons_found); ?></textarea>
                                <p class="description">
                                    This section is added automatically when lessons are found. It instructs the AI to proactively recommend lessons.
                                </p>
                                <p>
                                    <button type="button" class="button button-secondary aaa-reset-section" data-section="lesson_recommendations_found">
                                        Reset to Default
                                    </button>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Lesson Recommendations Section (When No Lessons Found) -->
                <div style="margin: 30px 0; padding: 20px; border: 1px solid #ddd; background: #fff;">
                    <h3>⚠️ Lesson Recommendations (When No Lessons Are Found)</h3>
                    <p style="color: #666; margin-top: -10px;">This section is automatically added when no relevant lessons are found in the database.</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aaa_section_lessons_none">No Lessons Found Section</label>
                            </th>
                            <td>
                                <?php
                                $current_lessons_none = '';
                                if (isset($custom_sections['lesson_recommendations_none']) && !empty($custom_sections['lesson_recommendations_none'])) {
                                    $current_lessons_none = wp_unslash($custom_sections['lesson_recommendations_none']);
                                } else {
                                    $current_lessons_none = $default_sections['lesson_recommendations_none'];
                                }
                                ?>
                                <textarea 
                                    id="aaa_section_lessons_none" 
                                    name="aaa_sections[lesson_recommendations_none]" 
                                    rows="6" 
                                    class="large-text code" 
                                    style="font-family: monospace; font-size: 13px;"
                                ><?php echo esc_textarea($current_lessons_none); ?></textarea>
                                <p class="description">
                                    This section is added automatically when no lessons are found. It prevents the AI from making up lesson titles.
                                </p>
                                <p>
                                    <button type="button" class="button button-secondary aaa-reset-section" data-section="lesson_recommendations_none">
                                        Reset to Default
                                    </button>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Competitor Blocking Section -->
                <div style="margin: 30px 0; padding: 20px; border: 1px solid #ddd; background: #fff;">
                    <h3>🚫 Competitor Blocking</h3>
                    <p style="color: #666; margin-top: -10px;">This section prevents the AI from mentioning or recommending competitors or alternative piano learning platforms.</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aaa_section_competitor_blocking">Competitor Blocking Section</label>
                            </th>
                            <td>
                                <?php
                                $current_competitor_blocking = '';
                                if (isset($custom_sections['competitor_blocking']) && !empty($custom_sections['competitor_blocking'])) {
                                    $current_competitor_blocking = wp_unslash($custom_sections['competitor_blocking']);
                                } else {
                                    $current_competitor_blocking = $default_sections['competitor_blocking'];
                                }
                                ?>
                                <textarea 
                                    id="aaa_section_competitor_blocking" 
                                    name="aaa_sections[competitor_blocking]" 
                                    rows="8" 
                                    class="large-text code" 
                                    style="font-family: monospace; font-size: 13px;"
                                ><?php echo esc_textarea($current_competitor_blocking); ?></textarea>
                                <p class="description">
                                    This section is added automatically to all prompts. It instructs the AI to never mention competitors like Pianote, PianoWithJonny, etc.
                                </p>
                                <p>
                                    <button type="button" class="button button-secondary aaa-reset-section" data-section="competitor_blocking">
                                        Reset to Default
                                    </button>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Save All Sections" />
                </p>
            </form>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Store default prompts
            var defaultPrompts = {};
            <?php foreach ($locations as $location_id => $location_data): ?>
                defaultPrompts['<?php echo esc_js($location_id); ?>'] = <?php echo json_encode($location_data['default_prompt']); ?>;
            <?php endforeach; ?>
            
            // Store default sections
            var defaultSections = {};
            <?php 
            $default_sections = $this->get_default_sections();
            foreach ($default_sections as $section_key => $section_content): 
            ?>
                defaultSections['<?php echo esc_js($section_key); ?>'] = <?php echo json_encode($section_content); ?>;
            <?php endforeach; ?>
            
            // Reset prompt to default
            $('.aaa-reset-prompt').on('click', function() {
                var locationId = $(this).data('location');
                var textarea = $('#aaa_prompt_' + locationId);
                
                if (defaultPrompts[locationId]) {
                    if (confirm('Are you sure you want to reset this prompt to the default? Your current changes will be lost.')) {
                        textarea.val(defaultPrompts[locationId]);
                    }
                }
            });
            
            // Reset section to default
            $('.aaa-reset-section').on('click', function() {
                var sectionKey = $(this).data('section');
                
                // Map section keys to textarea IDs
                var sectionIdMap = {
                    'music_theory_accuracy': 'aaa_section_music_theory',
                    'lesson_recommendations_found': 'aaa_section_lessons_found',
                    'lesson_recommendations_none': 'aaa_section_lessons_none',
                    'competitor_blocking': 'aaa_section_competitor_blocking'
                };
                
                var textareaId = sectionIdMap[sectionKey] || '#aaa_section_' + sectionKey;
                var textarea = $('#' + textareaId);
                
                if (defaultSections[sectionKey]) {
                    if (confirm('Are you sure you want to reset this section to the default? Your current changes will be lost.')) {
                        textarea.val(defaultSections[sectionKey]);
                    }
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Ensure keyword_lessons table exists
     * Creates it if missing (for cases where plugin was activated before table was added)
     */
    private function ensure_keyword_table_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aaa_keyword_lessons';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) == $table_name;
        
        if (!$table_exists) {
            // Table doesn't exist, create it
            $database = new AAA_Database();
            $database->create_tables();
            
            // Show admin notice
            add_settings_error(
                'aaa_keywords',
                'aaa_table_created',
                'The keyword_lessons table was missing and has been created. You can now add keyword mappings.',
                'updated'
            );
        }
    }
    
    /**
     * Ensure chip_suggestions table exists
     */
    private function ensure_chip_suggestions_table_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aaa_chip_suggestions';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) == $table_name;
        
        if (!$table_exists) {
            // Table doesn't exist, create it
            $database = new AAA_Database();
            $database->create_tables();
            
            // Show admin notice
            add_settings_error(
                'aaa_chip_suggestion',
                'aaa_table_created',
                'The chip_suggestions table was missing and has been created. You can now add chip suggestions.',
                'updated'
            );
        }
    }
    
    /**
     * Handle chip prompts form submission
     */
    public function handle_chip_prompts_save() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (!isset($_POST['aaa_chip_prompts_save']) || !check_admin_referer('aaa_chip_prompts_save', 'aaa_chip_prompts_nonce')) {
            return;
        }
        
        $chip_prompts = array();
        $chip_templates = $this->get_chip_templates();
        
        foreach ($chip_templates as $chip_id => $chip_data) {
            $prompt_key = 'aaa_chip_prompt_' . $chip_id;
            if (isset($_POST[$prompt_key])) {
                $chip_prompts[$chip_id] = wp_kses_post(wp_unslash($_POST[$prompt_key]));
            }
        }
        
        update_option('aaa_chip_prompts', $chip_prompts);
        add_settings_error('aaa_chip_prompts', 'aaa_chip_prompts_saved', 'Chip prompts saved successfully.', 'updated');
    }
    
    /**
     * Chip Prompts management page
     */
    public function chip_prompts_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        // Get saved prompts
        $saved_prompts = get_option('aaa_chip_prompts', array());
        $chip_templates = $this->get_chip_templates();
        
        ?>
        <div class="wrap">
            <h1>Chip Prompts</h1>
            <p>Customize the AI's response instructions for each quick-start chip. These prompts are added to the system message when a user uses that specific chip.</p>
            <p><strong>Use this to:</strong> Recommend specific programs, courses, or learning paths based on what users want to learn or their skill level.</p>
            
            <?php settings_errors('aaa_chip_prompts'); ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('aaa_chip_prompts_save', 'aaa_chip_prompts_nonce'); ?>
                
                <?php foreach ($chip_templates as $chip_id => $chip_data): ?>
                    <div style="margin: 30px 0; padding: 20px; border: 1px solid #ddd; background: #fff; border-radius: 4px;">
                        <h2 style="margin-top: 0;"><?php echo esc_html($chip_data['label']); ?></h2>
                        <p style="color: #666; font-style: italic; margin: 5px 0 15px;">
                            Template: <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;"><?php echo esc_html($chip_data['template']); ?></code>
                        </p>
                        
                        <label for="aaa_chip_prompt_<?php echo esc_attr($chip_id); ?>" style="display: block; margin: 15px 0 5px; font-weight: bold;">
                            Custom Instructions for this Chip:
                        </label>
                        <p style="color: #666; font-size: 13px; margin: 0 0 10px;">
                            Add specific instructions for how the AI should respond when users use this chip. 
                            You can mention specific programs, courses, or learning paths to recommend based on the user's input.
                        </p>
                        <textarea 
                            id="aaa_chip_prompt_<?php echo esc_attr($chip_id); ?>" 
                            name="aaa_chip_prompt_<?php echo esc_attr($chip_id); ?>" 
                            rows="10" 
                            style="width: 100%; font-family: monospace; font-size: 13px; padding: 10px;"
                            placeholder="Example: When users ask what to practice next, recommend the 'Jazz Piano Foundations' program for beginners, or 'Advanced Improvisation' for advanced players. For blues topics, suggest the 'Blues Mastery' course..."><?php echo isset($saved_prompts[$chip_id]) ? esc_textarea($saved_prompts[$chip_id]) : ''; ?></textarea>
                        <p style="color: #666; font-size: 12px; margin: 5px 0 0;">
                            Leave empty to use default behavior. This will be appended to the system prompt when this chip is used.
                        </p>
                    </div>
                <?php endforeach; ?>
                
                <p class="submit">
                    <input type="submit" name="aaa_chip_prompts_save" class="button button-primary" value="Save Chip Prompts">
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Handle chip suggestions save
     */
    public function handle_chip_suggestions_save() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (!isset($_POST['aaa_chip_suggestion_save']) || !check_admin_referer('aaa_chip_suggestion_save', 'aaa_chip_suggestion_nonce')) {
            return;
        }
        
        // Get selected chip IDs (can be multiple)
        $chip_ids = array();
        if (isset($_POST['aaa_chip_id']) && is_array($_POST['aaa_chip_id'])) {
            $chip_ids = array_map('sanitize_text_field', $_POST['aaa_chip_id']);
        } elseif (isset($_POST['aaa_chip_id'])) {
            // Single value (backward compatibility)
            $chip_ids = array(sanitize_text_field($_POST['aaa_chip_id']));
        }
        
        $suggestion_type = isset($_POST['aaa_suggestion_type']) ? sanitize_text_field($_POST['aaa_suggestion_type']) : '';
        $priority = isset($_POST['aaa_suggestion_priority']) ? absint($_POST['aaa_suggestion_priority']) : 10;
        
        // Get multiple suggestion IDs from comma-separated string or array
        $suggestion_ids = array();
        if (isset($_POST['aaa_suggestion_ids'])) {
            if (is_array($_POST['aaa_suggestion_ids'])) {
                // Array format (shouldn't happen with our current form, but handle it)
                $suggestion_ids = array_map('absint', $_POST['aaa_suggestion_ids']);
            } else {
                // Comma-separated string format
                $suggestion_ids_str = sanitize_text_field($_POST['aaa_suggestion_ids']);
                if (!empty($suggestion_ids_str)) {
                    $suggestion_ids = array_map('absint', explode(',', $suggestion_ids_str));
                }
            }
        }
        $suggestion_ids = array_filter($suggestion_ids); // Remove zeros and empty values
        
        if (empty($chip_ids)) {
            add_settings_error('aaa_chip_suggestion', 'aaa_chip_suggestion_error', 'Please select at least one interest.', 'error');
            return;
        }
        
        if (empty($suggestion_type)) {
            add_settings_error('aaa_chip_suggestion', 'aaa_suggestion_type_error', 'Please select a type (Lesson or Collection).', 'error');
            return;
        }
        
        if (empty($suggestion_ids)) {
            add_settings_error('aaa_chip_suggestion', 'aaa_suggestion_ids_error', 'Please select at least one ' . $suggestion_type . ' to add.', 'error');
            return;
        }
        
        // Process each selected chip_id and each suggestion_id
        $success_count = 0;
        $error_count = 0;
        $errors = array();
        
        foreach ($chip_ids as $chip_id) {
            // Normalize chip_id: 'all' becomes null for database
            $chip_id_for_db = ($chip_id === 'all' || $chip_id === 'all_chips') ? null : $chip_id;
            
            // Add each suggestion for this chip
            foreach ($suggestion_ids as $suggestion_id) {
                if ($suggestion_id > 0) {
                    $result = $this->database->add_chip_suggestion($chip_id_for_db, $suggestion_type, $suggestion_id, $priority);
                    
                    if (is_wp_error($result)) {
                        $error_count++;
                        $errors[] = $result->get_error_message();
                    } else {
                        $success_count++;
                    }
                }
            }
        }
        
        // Show appropriate message
        if ($success_count > 0 && $error_count === 0) {
            $total_items = count($suggestion_ids);
            $total_chips = count($chip_ids);
            $total_assignments = $total_items * $total_chips;
            $message = sprintf(
                'Successfully assigned %d %s(s) to %d interest(s) (%d total assignments).', 
                $total_items, 
                $suggestion_type,
                $total_chips,
                $total_assignments
            );
            add_settings_error('aaa_chip_suggestion', 'aaa_chip_suggestion_saved', $message, 'updated');
        } elseif ($success_count > 0 && $error_count > 0) {
            add_settings_error('aaa_chip_suggestion', 'aaa_chip_suggestion_partial', 
                sprintf('%d assignment(s) succeeded, but %d failed: %s', $success_count, $error_count, implode(', ', array_unique($errors))), 
                'error');
        } else {
            add_settings_error('aaa_chip_suggestion', 'aaa_chip_suggestion_save_error', 
                'Failed to assign suggestions: ' . implode(', ', array_unique($errors)), 
                'error');
        }
    }
    
    /**
     * AJAX handler to delete improve skill mapping
     */
    public function ajax_delete_improve_skill_mapping() {
        check_ajax_referer('aaa_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $mapping_id = isset($_POST['mapping_id']) ? absint($_POST['mapping_id']) : 0;
        
        if ($mapping_id === 0) {
            wp_send_json_error(array('message' => 'Invalid mapping ID'));
            return;
        }
        
        $deleted = $this->database->delete_improve_skill_mapping($mapping_id);
        
        if ($deleted) {
            wp_send_json_success(array('message' => 'Mapping deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete mapping'));
        }
    }
    
    /**
     * Chip Suggestions management page
     */
    public function chip_suggestions_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        // Ensure chip_suggestions table exists
        $this->ensure_chip_suggestions_table_exists();
        
        // Get all interests (skills from improve_skill chip) and styles (from show_style chip)
        // Aggregate them into a single list for the dropdown
        $improve_skills = array(
            'arranging jazz standards',
            'blues repertoire',
            'jazz repertoire',
            'chord voicings',
            'comping',
            'ear training',
            'improvisation',
            'jazz and blues licks',
            'rhythm',
            'sight reading',
            'slow blues',
            'step-by-step improvisation',
            'technique'
        );
        
        $styles = array(
            'Any',
            'Jazz',
            'Cocktail',
            'Blues',
            'Rock',
            'Funk',
            'Latin',
            'Classical',
            'Smooth Jazz',
            'Holiday',
            'Ballad',
            'Pop',
            'New Age',
            'Gospel',
            'New Orleans',
            'Country',
            'Modal',
            'Stride',
            'Organ',
            'Boogie'
        );
        
        // Combine into interests list (skills + styles)
        $all_interests = array();
        foreach ($improve_skills as $skill) {
            $all_interests[$skill] = ucwords($skill);
        }
        foreach ($styles as $style) {
            // Skip "Any" as it's not a specific interest
            if ($style !== 'Any') {
                $all_interests[$style] = $style;
            }
        }
        
        // Sort alphabetically
        ksort($all_interests);
        
        $chip_options = array('all' => 'All interests (applies to every interest)');
        foreach ($all_interests as $interest_id => $interest_label) {
            $chip_options[$interest_id] = $interest_label;
        }
        
        // Get existing suggestions
        $suggestions = $this->database->get_chip_suggestions_all(1, 200);
        $total = $this->database->get_chip_suggestions_count();
        
        ?>
        <div class="wrap">
            <h1>Chip Suggestions</h1>
            <p>Assign lessons or collections to specific interests (like "improvisation" or "Jazz") or all interests. When a user selects an interest from a chip dropdown, these suggestions will be included in the AI prompt. Suggestions assigned to a specific interest take precedence over "All interests" suggestions.</p>
            
            <?php settings_errors('aaa_chip_suggestion'); ?>
            
            <div style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                <h2 style="margin-top: 0;">Add New Suggestion</h2>
                <form method="post" action="" id="aaa-chip-suggestion-form">
                    <?php wp_nonce_field('aaa_chip_suggestion_save', 'aaa_chip_suggestion_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label>Interests</label>
                            </th>
                            <td>
                                <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">
                                        <input type="checkbox" id="aaa_chip_all" value="all" style="margin-right: 5px;">
                                        <strong>All interests</strong> (applies to every interest)
                                    </label>
                                    <hr style="margin: 10px 0;">
                                    <div id="aaa_chip_checkboxes">
                                        <?php foreach ($chip_options as $chip_id => $chip_label): ?>
                                            <?php if ($chip_id !== 'all'): ?>
                                                <label style="display: block; margin-bottom: 5px; padding: 5px;">
                                                    <input type="checkbox" name="aaa_chip_id[]" value="<?php echo esc_attr($chip_id); ?>" class="aaa-chip-checkbox" style="margin-right: 5px;">
                                                    <?php echo esc_html($chip_label); ?>
                                                </label>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <p class="description">Select one or more interests to assign this lesson/collection to. You can select multiple interests at once.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Lesson</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aaa_lesson_search" 
                                       placeholder="Search for a lesson..." 
                                       style="width: 400px;"
                                       autocomplete="off">
                                <input type="hidden" name="aaa_lesson_id" id="aaa_lesson_id">
                                <div id="aaa_lesson_results" style="margin-top: 5px; display: none; position: absolute; z-index: 1000; background: #fff; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; width: 400px;"></div>
                                <div id="aaa_lesson_selected_list" style="margin-top: 10px; display: none;">
                                    <strong>Selected Lessons:</strong>
                                    <ul id="aaa_lessons_list" style="list-style: none; padding: 0; margin: 5px 0 0 0;"></ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Collection</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aaa_collection_search" 
                                       placeholder="Search for a collection..." 
                                       style="width: 400px;"
                                       autocomplete="off">
                                <input type="hidden" name="aaa_collection_id" id="aaa_collection_id">
                                <div id="aaa_collection_results" style="margin-top: 5px; display: none; position: absolute; z-index: 1000; background: #fff; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; width: 400px;"></div>
                                <div id="aaa_collection_selected_list" style="margin-top: 10px; display: none;">
                                    <strong>Selected Collections:</strong>
                                    <ul id="aaa_collections_list" style="list-style: none; padding: 0; margin: 5px 0 0 0;"></ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Type</label>
                            </th>
                            <td>
                                <select name="aaa_suggestion_type" id="aaa_suggestion_type" required style="width: 200px;">
                                    <option value="">Select type...</option>
                                    <option value="lesson">Lesson</option>
                                    <option value="collection">Collection</option>
                                </select>
                                <p class="description">Select the type based on what you selected above. You can add multiple lessons or collections before submitting.</p>
                            </td>
                        </tr>
                        <tr id="aaa_suggestion_ids_row" style="display: none;">
                            <th scope="row">
                                <label>Selected Items</label>
                            </th>
                            <td>
                                <input type="hidden" name="aaa_suggestion_ids" id="aaa_suggestion_ids" value="">
                                <p class="description">Selected items will appear above. You can add multiple items before submitting.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="aaa_suggestion_priority">Priority</label>
                            </th>
                            <td>
                                <input type="number" 
                                       name="aaa_suggestion_priority" 
                                       id="aaa_suggestion_priority" 
                                       value="10" 
                                       min="1" 
                                       max="100" 
                                       style="width: 100px;">
                                <p class="description">Lower numbers = higher priority (shown first)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="aaa_chip_suggestion_save" class="button button-primary" value="Add Suggestion">
                    </p>
                </form>
            </div>
            
            <div style="margin: 30px 0;">
                <h2>Current Suggestions (<?php echo esc_html($total); ?>)</h2>
                
                <?php if (empty($suggestions)): ?>
                    <p>No suggestions yet. Add one above to get started.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Chip</th>
                                <th style="width: 100px;">Type</th>
                                <th>Lesson/Collection</th>
                                <th style="width: 100px;">Priority</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_chip = '';
                            foreach ($suggestions as $suggestion): 
                                $chip_display = empty($suggestion['chip_id']) ? 'All interests' : (isset($chip_options[$suggestion['chip_id']]) ? $chip_options[$suggestion['chip_id']] : $suggestion['chip_id']);
                                if ($current_chip !== $chip_display):
                                    $current_chip = $chip_display;
                            ?>
                                <tr style="background: #f9f9f9;">
                                    <td colspan="5" style="font-weight: bold; padding: 10px;">
                                        <?php echo esc_html($chip_display); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                                <tr>
                                    <td></td>
                                    <td><?php echo esc_html(ucfirst($suggestion['suggestion_type'])); ?></td>
                                    <td>
                                        <?php if ($suggestion['suggestion_type'] === 'lesson'): ?>
                                            <?php if (!empty($suggestion['lesson_title'])): ?>
                                                <strong><?php echo esc_html($suggestion['lesson_title']); ?></strong>
                                                <?php if (!empty($suggestion['lesson_post_id'])): ?>
                                                    <br><small>ID: <?php echo esc_html($suggestion['suggestion_id']); ?> | Post ID: <?php echo esc_html($suggestion['lesson_post_id']); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <em>Lesson ID <?php echo esc_html($suggestion['suggestion_id']); ?> (not found)</em>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (!empty($suggestion['collection_title'])): ?>
                                                <strong><?php echo esc_html($suggestion['collection_title']); ?></strong>
                                                <?php if (!empty($suggestion['collection_post_id'])): ?>
                                                    <br><small>ID: <?php echo esc_html($suggestion['suggestion_id']); ?> | Post ID: <?php echo esc_html($suggestion['collection_post_id']); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <em>Collection ID <?php echo esc_html($suggestion['suggestion_id']); ?> (not found)</em>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($suggestion['priority']); ?></td>
                                    <td>
                                        <button type="button" 
                                                class="button button-small aaa-delete-chip-suggestion" 
                                                data-suggestion-id="<?php echo esc_attr($suggestion['id']); ?>"
                                                style="color: #d63638;">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            
            // Lesson search
            var lessonSearchTimeout;
            var $lessonSearch = $('#aaa_lesson_search');
            var $lessonId = $('#aaa_lesson_id');
            var $lessonResults = $('#aaa_lesson_results');
            var $lessonSelectedList = $('#aaa_lesson_selected_list');
            var $lessonsList = $('#aaa_lessons_list');
            var selectedLessons = {}; // Store selected lessons: {id: {id: id, title: title}}
            
            $lessonSearch.on('input', function() {
                var query = $(this).val();
                
                clearTimeout(lessonSearchTimeout);
                
                if (query.length < 2) {
                    $lessonResults.hide().empty();
                    return;
                }
                
                lessonSearchTimeout = setTimeout(function() {
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'aaa_search_lessons',
                            query: query,
                            nonce: '<?php echo wp_create_nonce('aaa_search_lessons'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data && response.data.length > 0) {
                                var html = '';
                                response.data.forEach(function(item) {
                                    var itemId = item.id || item.lesson_id;
                                    var itemTitle = item.title || item.lesson_title;
                                    
                                    if (itemId && itemTitle) {
                                        html += '<div class="aaa-lesson-result" style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;" data-id="' + itemId + '" data-title="' + itemTitle.replace(/"/g, '&quot;') + '">';
                                        html += '<strong>' + itemTitle + '</strong>';
                                        if (item.post_id) {
                                            html += ' <small>(ID: ' + itemId + ')</small>';
                                        }
                                        html += '</div>';
                                    }
                                });
                                if (html) {
                                    $lessonResults.html(html).show();
                                } else {
                                    $lessonResults.html('<div style="padding: 8px; color: #666;">No lessons found</div>').show();
                                }
                            } else {
                                $lessonResults.html('<div style="padding: 8px; color: #666;">No lessons found</div>').show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Lesson search error:', status, error, xhr.responseText);
                            $lessonResults.html('<div style="padding: 8px; color: #d63638;">Error searching lessons. Please try again.</div>').show();
                        }
                    });
                }, 300);
            });
            
            // Collection search
            var collectionSearchTimeout;
            var $collectionSearch = $('#aaa_collection_search');
            var $collectionId = $('#aaa_collection_id');
            var $collectionResults = $('#aaa_collection_results');
            var $collectionSelectedList = $('#aaa_collection_selected_list');
            var $collectionsList = $('#aaa_collections_list');
            var selectedCollections = {}; // Store selected collections: {id: {id: id, title: title}}
            
            $collectionSearch.on('input', function() {
                var query = $(this).val();
                
                clearTimeout(collectionSearchTimeout);
                
                if (query.length < 2) {
                    $collectionResults.hide().empty();
                    return;
                }
                
                collectionSearchTimeout = setTimeout(function() {
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'aaa_search_collections',
                            query: query,
                            nonce: '<?php echo wp_create_nonce('aaa_search_collections'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data && response.data.length > 0) {
                                var html = '';
                                response.data.forEach(function(item) {
                                    if (item.id && item.title) {
                                        html += '<div class="aaa-collection-result" style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;" data-id="' + item.id + '" data-title="' + item.title.replace(/"/g, '&quot;') + '">';
                                        html += '<strong>' + item.title + '</strong>';
                                        if (item.post_id) {
                                            html += ' <small>(ID: ' + item.id + ')</small>';
                                        }
                                        html += '</div>';
                                    }
                                });
                                if (html) {
                                    $collectionResults.html(html).show();
                                } else {
                                    $collectionResults.html('<div style="padding: 8px; color: #666;">No collections found</div>').show();
                                }
                            } else {
                                $collectionResults.html('<div style="padding: 8px; color: #666;">No collections found</div>').show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Collection search error:', status, error, xhr.responseText);
                            $collectionResults.html('<div style="padding: 8px; color: #d63638;">Error searching collections. Please try again.</div>').show();
                        }
                    });
                }, 300);
            });
            
            // Function to update selected items display
            function updateSelectedItemsDisplay() {
                // Update lessons list
                if (Object.keys(selectedLessons).length > 0) {
                    $lessonsList.empty();
                    $.each(selectedLessons, function(id, item) {
                        var $li = $('<li style="padding: 8px; margin: 5px 0; background: #f0f0f0; border-radius: 3px; display: flex; justify-content: space-between; align-items: center;">');
                        $li.append('<span><strong>' + item.title + '</strong> <small>(ID: ' + id + ')</small></span>');
                        $li.append('<button type="button" class="button button-small aaa-remove-lesson" data-id="' + id + '" style="color: #d63638; margin-left: 10px;">Remove</button>');
                        $lessonsList.append($li);
                    });
                    $lessonSelectedList.show();
                } else {
                    $lessonSelectedList.hide();
                }
                
                // Update collections list
                if (Object.keys(selectedCollections).length > 0) {
                    $collectionsList.empty();
                    $.each(selectedCollections, function(id, item) {
                        var $li = $('<li style="padding: 8px; margin: 5px 0; background: #f0f0f0; border-radius: 3px; display: flex; justify-content: space-between; align-items: center;">');
                        $li.append('<span><strong>' + item.title + '</strong> <small>(ID: ' + id + ')</small></span>');
                        $li.append('<button type="button" class="button button-small aaa-remove-collection" data-id="' + id + '" style="color: #d63638; margin-left: 10px;">Remove</button>');
                        $collectionsList.append($li);
                    });
                    $collectionSelectedList.show();
                } else {
                    $collectionSelectedList.hide();
                }
                
                // Update hidden input with all selected IDs based on current type
                var allIds = [];
                var type = $('#aaa_suggestion_type').val();
                
                // If type is set, only include items of that type
                if (type === 'lesson') {
                    $.each(selectedLessons, function(id) {
                        allIds.push(id);
                    });
                } else if (type === 'collection') {
                    $.each(selectedCollections, function(id) {
                        allIds.push(id);
                    });
                } else {
                    // If no type is set yet, include both (but this shouldn't happen on submit)
                    $.each(selectedLessons, function(id) {
                        allIds.push(id);
                    });
                    $.each(selectedCollections, function(id) {
                        allIds.push(id);
                    });
                }
                
                $('#aaa_suggestion_ids').val(allIds.join(','));
                
                // Debug: log the value
                console.log('Updated suggestion_ids:', $('#aaa_suggestion_ids').val(), 'Type:', type, 'Lessons:', Object.keys(selectedLessons).length, 'Collections:', Object.keys(selectedCollections).length);
            }
            
            // Select lesson
            $(document).on('click', '.aaa-lesson-result', function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                
                // Add to selected lessons if not already selected
                if (!selectedLessons[id]) {
                    selectedLessons[id] = {id: id, title: title};
                    
                    $lessonSearch.val('');
                    $lessonResults.hide();
                    
                    // Auto-select type if not set
                    if ($('#aaa_suggestion_type').val() === '') {
                        $('#aaa_suggestion_type').val('lesson');
                    }
                    
                    updateSelectedItemsDisplay();
                }
            });
            
            // Select collection
            $(document).on('click', '.aaa-collection-result', function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                
                // Add to selected collections if not already selected
                if (!selectedCollections[id]) {
                    selectedCollections[id] = {id: id, title: title};
                    
                    $collectionSearch.val('');
                    $collectionResults.hide();
                    
                    // Auto-select type if not set
                    if ($('#aaa_suggestion_type').val() === '') {
                        $('#aaa_suggestion_type').val('collection');
                    }
                    
                    updateSelectedItemsDisplay();
                }
            });
            
            // Remove lesson
            $(document).on('click', '.aaa-remove-lesson', function() {
                var id = $(this).data('id');
                delete selectedLessons[id];
                updateSelectedItemsDisplay();
            });
            
            // Remove collection
            $(document).on('click', '.aaa-remove-collection', function() {
                var id = $(this).data('id');
                delete selectedCollections[id];
                updateSelectedItemsDisplay();
            });
            
            // Update display when type changes
            $('#aaa_suggestion_type').on('change', function() {
                updateSelectedItemsDisplay();
            });
            
            // Handle "All interests" checkbox
            $('#aaa_chip_all').on('change', function() {
                if ($(this).is(':checked')) {
                    // Uncheck all individual interests
                    $('.aaa-chip-checkbox').prop('checked', false);
                }
            });
            
            // Handle individual interest checkboxes
            $('.aaa-chip-checkbox').on('change', function() {
                if ($(this).is(':checked')) {
                    // Uncheck "All interests"
                    $('#aaa_chip_all').prop('checked', false);
                }
            });
            
            // Hide results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#aaa_lesson_search, #aaa_lesson_results').length) {
                    $lessonResults.hide();
                }
                if (!$(e.target).closest('#aaa_collection_search, #aaa_collection_results').length) {
                    $collectionResults.hide();
                }
            });
            
            // Form validation - ensure at least one interest is selected and at least one item is selected
            $('#aaa-chip-suggestion-form').on('submit', function(e) {
                var hasAll = $('#aaa_chip_all').is(':checked');
                var hasIndividual = $('.aaa-chip-checkbox:checked').length > 0;
                var type = $('#aaa_suggestion_type').val();
                var hasItems = false;
                var suggestionIds = $('#aaa_suggestion_ids').val();
                
                if (type === 'lesson') {
                    hasItems = Object.keys(selectedLessons).length > 0;
                } else if (type === 'collection') {
                    hasItems = Object.keys(selectedCollections).length > 0;
                }
                
                // Also check the hidden input value
                if (!hasItems && suggestionIds && suggestionIds.trim() !== '') {
                    hasItems = true;
                }
                
                console.log('Form validation:', {
                    hasAll: hasAll,
                    hasIndividual: hasIndividual,
                    type: type,
                    hasItems: hasItems,
                    suggestionIds: suggestionIds,
                    selectedLessons: Object.keys(selectedLessons).length,
                    selectedCollections: Object.keys(selectedCollections).length
                });
                
                if (!hasAll && !hasIndividual) {
                    e.preventDefault();
                    alert('Please select at least one interest.');
                    return false;
                }
                
                if (!hasItems || !suggestionIds || suggestionIds.trim() === '') {
                    e.preventDefault();
                    alert('Please select at least one ' + (type || 'lesson or collection') + ' to add.');
                    return false;
                }
                
                // On successful submit, clear selections after a short delay (to allow page reload)
                // The form will reset on page reload, but we can also clear here for immediate feedback
                setTimeout(function() {
                    selectedLessons = {};
                    selectedCollections = {};
                    updateSelectedItemsDisplay();
                    $lessonSearch.val('');
                    $collectionSearch.val('');
                    $('#aaa_suggestion_type').val('');
                }, 100);
            });
            
            // Delete suggestion
            $(document).on('click', '.aaa-delete-chip-suggestion', function() {
                if (!confirm('Are you sure you want to delete this suggestion?')) {
                    return;
                }
                
                var $btn = $(this);
                var suggestionId = $btn.data('suggestion-id');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'aaa_delete_chip_suggestion',
                        suggestion_id: suggestionId,
                        nonce: '<?php echo wp_create_nonce('aaa_admin_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.closest('tr').fadeOut(300, function() {
                                $(this).remove();
                                // If the chip header row is now empty, remove it too
                                var $chipHeaderRow = $(this).prevAll('tr[style*="background: #f9f9f9;"]').first();
                                if ($chipHeaderRow.length && $chipHeaderRow.nextAll('tr').not('[style*="background: #f9f9f9;"]').length === 0) {
                                    $chipHeaderRow.remove();
                                }
                            });
                        } else {
                            alert('Error: ' + (response.data.message || 'Failed to delete'));
                        }
                    },
                    error: function() {
                        alert('Error: Failed to delete suggestion');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get all chip templates
     */
    private function get_chip_templates() {
        return array(
            'learn_topic' => array(
                'label' => 'I want to learn...',
                'template' => 'I want to learn {topic} and I am a {level} level player'
            ),
            'struggling' => array(
                'label' => 'I\'m struggling with...',
                'template' => 'I\'m struggling with {topic} and need help'
            ),
            'show_lessons' => array(
                'label' => 'Show me lessons about...',
                'template' => 'Show me lessons about {topic}'
            ),
            'how_to_play' => array(
                'label' => 'How do I play...?',
                'template' => 'How do I play {song}?'
            ),
            'explain' => array(
                'label' => 'Explain...',
                'template' => 'Explain {concept} to me'
            ),
            'recommend_lessons' => array(
                'label' => 'Recommend lessons...',
                'template' => 'I\'m a {level} player, recommend lessons for {topic}'
            ),
            'practice_next' => array(
                'label' => 'What should I practice next?',
                'template' => 'What should I practice next as a {level} player?'
            ),
            'improve_skill' => array(
                'label' => 'Improve my...',
                'template' => 'Help me improve my {skill}'
            ),
            'show_style' => array(
                'label' => 'Show me style lessons...',
                'template' => 'Show me {style} {type}'
            )
        );
    }
    
    /**
     * Token Limits settings page
     */
    public function token_limits_page() {
        // Security: Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        
        // Handle form submission
        if (isset($_POST['aaa_token_limits_save']) && wp_verify_nonce($_POST['aaa_token_limits_nonce'], 'aaa_token_limits_save')) {
            $membership_levels = array('free', 'essentials', 'studio', 'premier');
            $periods = array('daily', 'monthly');
            
            foreach ($membership_levels as $level) {
                foreach ($periods as $period) {
                    $option_key = 'aaa_token_limit_' . $level . '_' . $period;
                    $value = isset($_POST[$option_key]) ? absint($_POST[$option_key]) : 0;
                    update_option($option_key, $value);
                }
            }
            
            echo '<div class="notice notice-success"><p>Token limits saved successfully.</p></div>';
        }
        
        // Get current limits
        $limits = array();
        $membership_levels = array(
            'free' => 'Free/Starter',
            'essentials' => 'Essentials',
            'studio' => 'Studio',
            'premier' => 'Premier'
        );
        $periods = array(
            'daily' => 'Daily',
            'monthly' => 'Monthly'
        );
        
        foreach ($membership_levels as $level_key => $level_name) {
            foreach ($periods as $period_key => $period_name) {
                $option_key = 'aaa_token_limit_' . $level_key . '_' . $period_key;
                $limits[$level_key][$period_key] = get_option($option_key, 0);
            }
        }
        
        ?>
        <div class="wrap">
            <h1>Token Limits by Membership Level</h1>
            <p>Set token usage limits for each membership level. Set to 0 for unlimited access.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('aaa_token_limits_save', 'aaa_token_limits_nonce'); ?>
                
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Membership Level</th>
                            <th>Daily Limit</th>
                            <th>Monthly Limit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($membership_levels as $level_key => $level_name): ?>
                        <tr>
                            <td><strong><?php echo esc_html($level_name); ?></strong></td>
                            <td>
                                <input type="number" 
                                       name="aaa_token_limit_<?php echo esc_attr($level_key); ?>_daily" 
                                       value="<?php echo esc_attr($limits[$level_key]['daily']); ?>" 
                                       min="0" 
                                       step="100"
                                       class="small-text">
                                <span class="description">tokens (0 = unlimited)</span>
                            </td>
                            <td>
                                <input type="number" 
                                       name="aaa_token_limit_<?php echo esc_attr($level_key); ?>_monthly" 
                                       value="<?php echo esc_attr($limits[$level_key]['monthly']); ?>" 
                                       min="0" 
                                       step="1000"
                                       class="small-text">
                                <span class="description">tokens (0 = unlimited)</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="aaa_token_limits_save" class="button button-primary" value="Save Token Limits">
                </p>
            </form>
            
            <div style="margin-top: 30px; padding: 15px; background: #f0f0f1; border-left: 4px solid #2271b1;">
                <h3>How Token Limits Work</h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Daily Limit:</strong> Maximum tokens a user can use per day. Resets at midnight (WordPress timezone).</li>
                    <li><strong>Monthly Limit:</strong> Maximum tokens a user can use per calendar month. Resets on the 1st of each month.</li>
                    <li><strong>Unlimited (0):</strong> Setting a limit to 0 means unlimited access for that membership level.</li>
                    <li><strong>Enforcement:</strong> Users who exceed their limit will receive an error message and cannot make additional AI requests until the limit resets.</li>
                    <li><strong>Membership Detection:</strong> The system automatically detects each user's membership level using the Academy Lesson Manager membership checker.</li>
                </ul>
            </div>
        </div>
        <?php
    }
}

