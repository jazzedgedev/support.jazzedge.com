<?php
/**
 * Admin Essentials Users Management
 * 
 * Allows admins to view and manage Essentials users and their library selections
 * 
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Essentials_Users {
    
    /**
     * WordPress database instance
     */
    private $wpdb;
    
    /**
     * Table names
     */
    private $selections_table;
    private $library_table;
    private $lessons_table;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->selections_table = $wpdb->prefix . 'alm_essentials_selections';
        $this->library_table = $wpdb->prefix . 'alm_essentials_library';
        $this->lessons_table = $wpdb->prefix . 'alm_lessons';
        
        // Handle actions
        add_action('admin_init', array($this, 'handle_actions'));
    }
    
    /**
     * Handle admin actions (reset count, etc.)
     */
    public function handle_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        
        // Reset available count - check for button OR hidden field
        if ((isset($_POST['alm_reset_selections']) || (isset($_POST['user_id']) && isset($_POST['reset_count']))) && isset($_POST['user_id'])) {
            error_log("ALM Reset form submitted");
            
            // Check nonce
            if (!wp_verify_nonce($_POST['alm_reset_nonce'], 'alm_reset_selections')) {
                error_log("ALM Reset nonce verification failed");
                wp_die('Security check failed. Please try again.');
            }
            
            error_log("ALM Reset nonce verified");
            
            $user_id = intval($_POST['user_id']);
            $reset_count = isset($_POST['reset_count']) ? intval($_POST['reset_count']) : 1;
            
            error_log("ALM Reset: user_id={$user_id}, reset_count={$reset_count}");
            
            $result = $this->reset_user_selections($user_id, $reset_count);
            
            error_log("ALM Reset result: " . var_export($result, true));
            
            if ($result === false) {
                wp_redirect(add_query_arg(array(
                    'page' => 'academy-manager-essentials-users',
                    'view_user' => $user_id,
                    'message' => 'error',
                    'error_msg' => 'Failed to update available count. Please check database.'
                ), admin_url('admin.php')));
            } else {
                wp_redirect(add_query_arg(array(
                    'page' => 'academy-manager-essentials-users',
                    'view_user' => $user_id,
                    'message' => 'selections_reset'
                ), admin_url('admin.php')));
            }
            exit;
        }
        
        // Grant selection manually - check for button OR hidden field
        if ((isset($_POST['alm_grant_selection']) || (isset($_POST['user_id']) && isset($_POST['grant_amount']))) && isset($_POST['user_id'])) {
            error_log("ALM Grant form submitted");
            
            // Check nonce
            if (!wp_verify_nonce($_POST['alm_grant_nonce'], 'alm_grant_selection')) {
                error_log("ALM Grant nonce verification failed");
                wp_die('Security check failed. Please try again.');
            }
            
            error_log("ALM Grant nonce verified");
            
            $user_id = intval($_POST['user_id']);
            $grant_amount = isset($_POST['grant_amount']) ? intval($_POST['grant_amount']) : 1;
            
            error_log("ALM Grant: user_id={$user_id}, grant_amount={$grant_amount}");
            
            $result = $this->grant_selection_manually($user_id, $grant_amount);
            
            error_log("ALM Grant result: " . var_export($result, true));
            
            if ($result === false) {
                wp_redirect(add_query_arg(array(
                    'page' => 'academy-manager-essentials-users',
                    'view_user' => $user_id,
                    'message' => 'error',
                    'error_msg' => 'Failed to grant selections. Please check database.'
                ), admin_url('admin.php')));
            } else {
                wp_redirect(add_query_arg(array(
                    'page' => 'academy-manager-essentials-users',
                    'view_user' => $user_id,
                    'message' => 'selection_granted'
                ), admin_url('admin.php')));
            }
            exit;
        }
    }
    
    /**
     * Reset user's available selections count
     * 
     * @param int $user_id User ID
     * @param int $count Count to set (default 1)
     * @return bool Success
     */
    private function reset_user_selections($user_id, $count = 1) {
        $count = max(0, intval($count)); // Ensure non-negative integer
        
        // Use direct SQL - simpler and more reliable
        $sql = $this->wpdb->prepare(
            "UPDATE {$this->selections_table} SET available_count = %d WHERE user_id = %d",
            $count,
            $user_id
        );
        
        $result = $this->wpdb->query($sql);
        
        // Log for debugging
        error_log("ALM Reset Selections SQL: {$sql}");
        error_log("ALM Reset Selections Result: " . var_export($result, true));
        error_log("ALM Reset Selections Error: " . ($this->wpdb->last_error ?: 'None'));
        
        // Verify it worked
        $verify = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT available_count FROM {$this->selections_table} WHERE user_id = %d",
            $user_id
        ));
        
        error_log("ALM Reset Selections Verify: Expected {$count}, Got {$verify}");
        
        return ($verify == $count);
    }
    
    /**
     * Grant a selection manually (for testing)
     * 
     * @param int $user_id User ID
     * @param int $amount Amount to add (default 1)
     * @return bool Success
     */
    private function grant_selection_manually($user_id, $amount = 1) {
        $amount = max(1, intval($amount)); // Ensure at least 1
        
        // Get current count
        $current = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->selections_table} WHERE user_id = %d",
            $user_id
        ));
        
        if (!$current) {
            // Initialize if doesn't exist
            if (class_exists('ALM_Essentials_Library')) {
                $library = new ALM_Essentials_Library();
                $library->initialize_membership($user_id);
                $current = $this->wpdb->get_row($this->wpdb->prepare(
                    "SELECT * FROM {$this->selections_table} WHERE user_id = %d",
                    $user_id
                ));
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("ALM Grant Selection: ALM_Essentials_Library class not found");
                }
                return false;
            }
        }
        
        if (!$current) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("ALM Grant Selection: Could not initialize or retrieve user record for user_id={$user_id}");
            }
            return false;
        }
        
        $new_count = intval($current->available_count) + $amount; // Allow any amount, no max limit
        $today = current_time('Y-m-d');
        $next_grant = date('Y-m-d', strtotime($today . ' +30 days'));
        
        // Get current value before update
        $old_count = intval($current->available_count);
        
        // Use direct SQL - simpler and more reliable
        $sql = $this->wpdb->prepare(
            "UPDATE {$this->selections_table} SET available_count = %d, last_granted_date = %s, next_grant_date = %s WHERE user_id = %d",
            $new_count,
            $today,
            $next_grant,
            $user_id
        );
        
        $result = $this->wpdb->query($sql);
        
        // Log for debugging
        error_log("ALM Grant Selection SQL: {$sql}");
        error_log("ALM Grant Selection Result: " . var_export($result, true));
        error_log("ALM Grant Selection Error: " . ($this->wpdb->last_error ?: 'None'));
        
        // Verify the update worked
        $verify_count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT available_count FROM {$this->selections_table} WHERE user_id = %d",
            $user_id
        ));
        
        error_log("ALM Grant Selection Verify: Expected {$new_count}, Got {$verify_count}");
        
        return ($verify_count == $new_count);
    }
    
    /**
     * Get all Essentials users
     * 
     * @return array Array of user data with selection info
     */
    private function get_essentials_users() {
        // Get all users who have Essentials selections record
        // These are users who have accessed the library system
        $users = $this->wpdb->get_results(
            "SELECT 
                s.*,
                u.ID as user_id,
                u.user_login,
                u.user_email,
                u.display_name
             FROM {$this->selections_table} s
             INNER JOIN {$this->wpdb->users} u ON u.ID = s.user_id
             ORDER BY s.updated_at DESC"
        );
        
        // Add library count to each user
        foreach ($users as $user) {
            $library_count = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->library_table} WHERE user_id = %d",
                $user->user_id
            ));
            $user->library_count = intval($library_count);
        }
        
        return $users;
    }
    
    /**
     * Get user's library lessons
     * 
     * @param int $user_id User ID
     * @return array Array of lesson objects
     */
    private function get_user_library($user_id) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT l.*, el.selected_at, el.selection_cycle
             FROM {$this->library_table} el
             INNER JOIN {$this->lessons_table} l ON l.ID = el.lesson_id
             WHERE el.user_id = %d
             ORDER BY el.selected_at DESC",
            $user_id
        ));
    }
    
    /**
     * Render the admin page
     */
    public function render_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle messages
        $message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        
        // Get all Essentials users
        $users = $this->get_essentials_users();
        
        // Get expanded user details if viewing single user
        $view_user_id = isset($_GET['view_user']) ? intval($_GET['view_user']) : 0;
        $view_user_library = null;
        if ($view_user_id) {
            $view_user_library = $this->get_user_library($view_user_id);
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Essentials Users', 'academy-lesson-manager'); ?></h1>
            
            <?php if ($message === 'selections_reset'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html__('Available selections count has been reset.', 'academy-lesson-manager'); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($message === 'selection_granted'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html__('Selection has been granted.', 'academy-lesson-manager'); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($message === 'error' && isset($_GET['error_msg'])): ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html($_GET['error_msg']); ?></p>
                <p><strong>Debug Info:</strong> Check browser console and PHP error logs for more details.</p>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): 
                $debug_info = array(
                    'Timestamp' => current_time('mysql'),
                    'User ID' => get_current_user_id(),
                    'User Capabilities' => current_user_can('manage_options') ? 'Yes' : 'No',
                    'POST Data Received' => !empty($_POST) ? 'Yes (' . count($_POST) . ' items)' : 'No',
                    'GET Data' => !empty($_GET) ? print_r($_GET, true) : 'None',
                    'POST Data' => !empty($_POST) ? print_r($_POST, true) : 'None',
                    'Selections Table' => $this->selections_table,
                    'Library Table' => $this->library_table,
                    'Last DB Query' => $this->wpdb->last_query ?: 'None',
                    'Last DB Error' => $this->wpdb->last_error ?: 'None',
                    'Error Log Location' => ini_get('error_log') ?: 'Default PHP error log',
                    'WP_DEBUG' => defined('WP_DEBUG') ? (WP_DEBUG ? 'Enabled' : 'Disabled') : 'Not defined',
                    'WP_DEBUG_LOG' => defined('WP_DEBUG_LOG') ? (WP_DEBUG_LOG ? 'Enabled' : 'Disabled') : 'Not defined',
                    'PHP Version' => PHP_VERSION,
                    'WordPress Version' => get_bloginfo('version'),
                    'Action Hook Registered' => has_action('admin_init', array($this, 'handle_actions')) ? 'Yes' : 'No',
                );
                
                if ($view_user_id) {
                    $debug_info['Viewing User ID'] = $view_user_id;
                    $debug_info['User Data Exists'] = $view_user_data ? 'Yes' : 'No';
                    if ($view_user_data) {
                        $debug_info['Current Available Count'] = $view_user_data->available_count;
                        $debug_info['Record ID'] = $view_user_data->id;
                    }
                }
                
                $debug_text = "=== ALM Essentials Users Debug Information ===\n\n";
                foreach ($debug_info as $key => $value) {
                    $debug_text .= "{$key}: {$value}\n";
                }
                $debug_text .= "\n=== End Debug Information ===";
            ?>
            <div class="notice notice-info is-dismissible" style="position: relative;">
                <button onclick="copyDebugInfo()" class="button button-small" style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                    📋 Copy Debug Info
                </button>
                <p><strong>Debug Information:</strong></p>
                <div id="alm-debug-content" style="max-height: 500px; overflow-y: auto; background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 12px; line-height: 1.6;">
                    <?php foreach ($debug_info as $key => $value): ?>
                    <div style="margin-bottom: 10px;">
                        <strong style="color: #0073aa;"><?php echo esc_html($key); ?>:</strong>
                        <?php if (is_array($value) || (is_string($value) && (strpos($value, "\n") !== false || strlen($value) > 100))): ?>
                            <pre style="background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto; margin: 5px 0 0 0;"><?php echo esc_html($value); ?></pre>
                        <?php else: ?>
                            <span style="color: #333;"><?php echo esc_html($value); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <textarea id="alm-debug-text" style="position: absolute; left: -9999px; width: 1px; height: 1px;"><?php echo esc_textarea($debug_text); ?></textarea>
                <script>
                function copyDebugInfo() {
                    var textarea = document.getElementById('alm-debug-text');
                    textarea.select();
                    textarea.setSelectionRange(0, 99999); // For mobile devices
                    try {
                        document.execCommand('copy');
                        alert('Debug information copied to clipboard!');
                    } catch (err) {
                        // Fallback for older browsers
                        prompt('Copy this text:', textarea.value);
                    }
                }
                </script>
            </div>
            <?php endif; ?>
            
            <?php if ($view_user_id): 
                $view_user = get_user_by('id', $view_user_id);
                $view_user_data = $this->wpdb->get_row($this->wpdb->prepare(
                    "SELECT * FROM {$this->selections_table} WHERE user_id = %d",
                    $view_user_id
                ));
            ?>
            <div class="alm-user-details">
                <h2>User Details: <?php echo esc_html($view_user->display_name); ?> (<?php echo esc_html($view_user->user_email); ?>)</h2>
                
                <?php if ($view_user_data): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>User ID</strong></td>
                            <td><?php echo esc_html($view_user_id); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Membership Start Date</strong></td>
                            <td><?php echo esc_html($view_user_data->membership_start_date ? date('F j, Y', strtotime($view_user_data->membership_start_date)) : 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last Granted Date</strong></td>
                            <td><?php echo esc_html($view_user_data->last_granted_date ? date('F j, Y', strtotime($view_user_data->last_granted_date)) : 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Next Grant Date</strong></td>
                            <td><?php echo esc_html($view_user_data->next_grant_date ? date('F j, Y', strtotime($view_user_data->next_grant_date)) : 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Available Count</strong></td>
                            <td><strong style="color: #F04E23; font-size: 1.2em;"><?php echo esc_html($view_user_data->available_count); ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong>Record ID</strong></td>
                            <td><?php echo esc_html($view_user_data->id); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
                <div style="background: #f0f0f0; padding: 15px; margin-top: 20px; border: 1px solid #ccc; border-radius: 4px;">
                    <h4>Database Debug Info:</h4>
                    <p><strong>Table Name:</strong> <?php echo esc_html($this->selections_table); ?></p>
                    <p><strong>User ID:</strong> <?php echo esc_html($view_user_id); ?></p>
                    <p><strong>Current Available Count (from DB):</strong> <?php echo esc_html($view_user_data->available_count); ?></p>
                    <p><strong>Last DB Query:</strong> <?php echo esc_html($this->wpdb->last_query ?: 'None'); ?></p>
                    <p><strong>Last DB Error:</strong> <?php echo esc_html($this->wpdb->last_error ?: 'None'); ?></p>
                </div>
                <?php endif; ?>
                
                <h3 style="margin-top: 30px;">Testing Tools</h3>
                
                <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 4px; max-width: 600px; margin-bottom: 20px;">
                    <h4 style="margin-top: 0;">Set Available Count</h4>
                    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                        <strong>What this does:</strong> Directly sets the number of lessons this Essentials member can select from the Studio library. 
                        This is useful for testing the selection flow. Normal users get 1 selection every 30 days (max 3 accumulated).
                    </p>
                    <form method="post" action="">
                        <?php wp_nonce_field('alm_reset_selections', 'alm_reset_nonce'); ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr($view_user_id); ?>">
                        <input type="hidden" name="alm_reset_selections" value="1">
                        <p style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                                Set available count to:
                            </label>
                            <input type="number" name="reset_count" value="<?php echo esc_attr($view_user_data ? $view_user_data->available_count : 0); ?>" min="0" step="1" style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <span style="color: #666; font-size: 13px; margin-left: 10px;">(0 or higher, no maximum)</span>
                        </p>
                        <p>
                            <button type="submit" class="button button-primary">
                                Update Available Count
                            </button>
                        </p>
                    </form>
                </div>
                
                <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 4px; max-width: 600px;">
                    <h4 style="margin-top: 0;">Grant Additional Selections</h4>
                    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                        <strong>What this does:</strong> Adds to the current available count. Useful for testing when you want to give the user 
                        more selections without resetting to a specific number. You can grant any amount.
                    </p>
                    <form method="post" action="">
                        <?php wp_nonce_field('alm_grant_selection', 'alm_grant_nonce'); ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr($view_user_id); ?>">
                        <input type="hidden" name="alm_grant_selection" value="1">
                        <p style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                                Grant amount:
                            </label>
                            <input type="number" name="grant_amount" value="1" min="1" step="1" style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <span style="color: #666; font-size: 13px; margin-left: 10px;">(Current: <?php echo esc_html($view_user_data ? $view_user_data->available_count : 0); ?>, will become: <span id="new-count-preview"><?php echo esc_html(($view_user_data ? $view_user_data->available_count : 0) + 1); ?></span>)</span>
                        </p>
                        <p>
                            <button type="submit" class="button button-secondary">
                                Grant Selections
                            </button>
                        </p>
                    </form>
                    <script>
                    (function() {
                        var grantInput = document.querySelector('input[name="grant_amount"]');
                        var currentCount = <?php echo intval($view_user_data ? $view_user_data->available_count : 0); ?>;
                        var preview = document.getElementById('new-count-preview');
                        if (grantInput && preview) {
                            grantInput.addEventListener('input', function() {
                                var amount = parseInt(this.value) || 1;
                                preview.textContent = currentCount + amount;
                            });
                        }
                    })();
                    </script>
                </div>
                
                <?php endif; ?>
                
                <h3 style="margin-top: 30px;">User's Library (<?php echo count($view_user_library); ?> lessons)</h3>
                <?php if (!empty($view_user_library)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Lesson ID</th>
                            <th>Title</th>
                            <th>Selected Date</th>
                            <th>Cycle</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($view_user_library as $lesson): ?>
                        <tr>
                            <td><?php echo esc_html($lesson->ID); ?></td>
                            <td><strong><?php echo esc_html(stripslashes($lesson->lesson_title)); ?></strong></td>
                            <td><?php echo esc_html(date('F j, Y g:i a', strtotime($lesson->selected_at))); ?></td>
                            <td><?php echo esc_html($lesson->selection_cycle); ?></td>
                            <td><?php echo esc_html($this->format_duration($lesson->duration)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No lessons in library yet.</p>
                <?php endif; ?>
                
                <p style="margin-top: 30px;">
                    <a href="<?php echo admin_url('admin.php?page=academy-manager-essentials-users'); ?>" class="button">← Back to All Users</a>
                </p>
            </div>
            <?php else: ?>
            
            <p><?php echo esc_html__('View and manage Essentials members and their lesson library selections.', 'academy-lesson-manager'); ?></p>
            
            <?php if (empty($users)): ?>
            <div class="notice notice-info">
                <p><?php echo esc_html__('No Essentials users found.', 'academy-lesson-manager'); ?></p>
            </div>
            <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Membership Start</th>
                        <th>Available Count</th>
                        <th>Next Grant Date</th>
                        <th>Library Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($user->display_name); ?></strong><br>
                            <small>ID: <?php echo esc_html($user->user_id); ?></small>
                        </td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html($user->membership_start_date ? date('M j, Y', strtotime($user->membership_start_date)) : 'N/A'); ?></td>
                        <td>
                            <strong style="color: #F04E23; font-size: 1.2em;"><?php echo esc_html($user->available_count); ?></strong>
                        </td>
                        <td><?php echo esc_html($user->next_grant_date ? date('M j, Y', strtotime($user->next_grant_date)) : 'N/A'); ?></td>
                        <td><?php echo esc_html($user->library_count); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=academy-manager-essentials-users&view_user=' . $user->user_id); ?>" class="button button-small">
                                View Details
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Format duration
     */
    private function format_duration($seconds) {
        $seconds = intval($seconds);
        if (!$seconds || $seconds < 0) {
            return 'N/A';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0 && $minutes > 0) {
            return $hours . 'h ' . $minutes . 'm';
        } elseif ($hours > 0) {
            return $hours . 'h';
        } elseif ($minutes > 0) {
            return $minutes . 'm';
        }
        
        return '0m';
    }
}

