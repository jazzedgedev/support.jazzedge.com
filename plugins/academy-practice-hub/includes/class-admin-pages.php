<?php
/**
 * Admin pages for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Admin_Pages {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Practice Hub', 'academy-practice-hub'),
            __('Practice Hub', 'academy-practice-hub'),
            'manage_options',
            'academy-practice-hub',
            array($this, 'admin_page'),
            'dashicons-format-audio',
            30
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Dashboard', 'academy-practice-hub'),
            __('Dashboard', 'academy-practice-hub'),
            'manage_options',
            'academy-practice-hub',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Students', 'academy-practice-hub'),
            __('Students', 'academy-practice-hub'),
            'manage_options',
            'aph-students',
            array($this, 'students_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Badges', 'academy-practice-hub'),
            __('Badges', 'academy-practice-hub'),
            'manage_options',
            'aph-badges',
            array($this, 'badges_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Lesson Favorites', 'academy-practice-hub'),
            __('Lesson Favorites', 'academy-practice-hub'),
            'manage_options',
            'aph-lesson-favorites',
            array($this, 'lesson_favorites_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Event Tracking', 'academy-practice-hub'),
            __('Event Tracking', 'academy-practice-hub'),
            'manage_options',
            'aph-fluent-crm-events',
            array($this, 'events_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Documentation', 'academy-practice-hub'),
            __('Documentation', 'academy-practice-hub'),
            'manage_options',
            'aph-documentation',
            array($this, 'documentation_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Settings', 'academy-practice-hub'),
            __('Settings', 'academy-practice-hub'),
            'manage_options',
            'aph-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_page() {
        // For now, redirect to the original plugin's admin page
        // This maintains functionality while we're using wire-through
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'admin_page')) {
                $main_plugin->admin_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Practice Hub Dashboard</h1>';
        echo '<p>Admin dashboard functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Students page
     */
    public function students_page() {
        global $wpdb;
        
        // Get all users with practice stats
        $stats_table = $wpdb->prefix . 'jph_user_stats';
        $users = $wpdb->get_results(
            "SELECT u.ID, u.display_name, u.user_email, s.total_sessions, s.total_minutes, s.gems_balance, s.current_level
             FROM {$wpdb->users} u
             LEFT JOIN {$stats_table} s ON u.ID = s.user_id
             WHERE s.user_id IS NOT NULL
             ORDER BY s.total_sessions DESC"
        );
        ?>
        <div class="wrap">
            <h1>👥 Students</h1>
            
            <div class="jph-students-overview">
                <div class="jph-students-stats">
                    <div class="jph-stat-card">
                        <h3>Total Students</h3>
                        <p><?php echo count($users); ?></p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Active Students</h3>
                        <p><?php echo count(array_filter($users, function($u) { return $u->total_sessions > 0; })); ?></p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Total Sessions</h3>
                        <p><?php echo array_sum(array_column($users, 'total_sessions')); ?></p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Total Minutes</h3>
                        <p><?php echo array_sum(array_column($users, 'total_minutes')); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="jph-students-actions">
                <button type="button" class="button button-secondary" onclick="location.reload()">🔄 Refresh</button>
                <button type="button" class="button button-secondary" onclick="alert('Export functionality coming soon')">📊 Export CSV</button>
            </div>
            
            <div class="jph-students-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Total Sessions</th>
                            <th>Total Hours</th>
                            <th>Level</th>
                            <th>Gems</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6">No students found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                        <small><?php echo esc_html($user->user_email); ?></small>
                                    </td>
                                    <td><?php echo esc_html($user->total_sessions ?: 0); ?></td>
                                    <td><?php echo esc_html(round(($user->total_minutes ?: 0) / 60, 1)); ?>h</td>
                                    <td><?php echo esc_html($user->current_level ?: 1); ?></td>
                                    <td><?php echo esc_html($user->gems_balance ?: 0); ?></td>
                                    <td>
                                        <button type="button" class="button button-small" onclick="alert('View functionality coming soon')">View</button>
                                        <button type="button" class="button button-small" onclick="alert('Edit functionality coming soon')">Edit</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <style>
        .jph-students-overview {
            margin: 20px 0;
        }
        
        .jph-students-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .jph-stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-stat-card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .jph-students-actions {
            margin: 20px 0;
        }
        
        .jph-students-actions .button {
            margin-right: 10px;
        }
        </style>
        <?php
    }
    
    /**
     * Badges page
     */
    public function badges_page() {
        $database = new JPH_Database();
        $badges = $database->get_badges();
        ?>
        <div class="wrap">
            <h1>🏆 Badge Management</h1>
            
            <div class="jph-badges-overview">
                <div class="jph-badges-stats">
                    <div class="jph-stat-card">
                        <h3>Total Badges</h3>
                        <p><?php echo count($badges); ?></p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Active Badges</h3>
                        <p><?php echo count(array_filter($badges, function($b) { return $b['is_active']; })); ?></p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Categories</h3>
                        <p><?php echo count(array_unique(array_column($badges, 'category'))); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="jph-badges-actions">
                <button type="button" class="button button-primary" onclick="showAddBadgeModal()">➕ Add New Badge</button>
                <button type="button" class="button button-secondary" onclick="location.reload()">🔄 Refresh</button>
            </div>
            
            <div class="jph-badges-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Badge</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>XP Reward</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($badges)): ?>
                            <tr>
                                <td colspan="7">No badges found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($badges as $badge): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($badge['image_url'])): ?>
                                            <img src="<?php echo esc_url($badge['image_url']); ?>" alt="<?php echo esc_attr($badge['name']); ?>" style="width: 32px; height: 32px;">
                                        <?php else: ?>
                                            <span style="font-size: 24px;"><?php echo esc_html($badge['icon'] ?: '🏆'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo esc_html($badge['name']); ?></strong></td>
                                    <td><?php echo esc_html($badge['description']); ?></td>
                                    <td><?php echo esc_html(ucfirst($badge['category'])); ?></td>
                                    <td><?php echo esc_html($badge['xp_reward']); ?> XP</td>
                                    <td>
                                        <span class="badge-status <?php echo $badge['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $badge['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="button button-small" onclick="editBadge('<?php echo $badge['badge_key']; ?>')">Edit</button>
                                        <button type="button" class="button button-small button-link-delete" onclick="deleteBadge('<?php echo $badge['badge_key']; ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Add Badge Modal -->
        <div id="jph-add-badge-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>🏆 Add New Badge</h2>
                    <button class="jph-modal-close" onclick="closeAddBadgeModal()">×</button>
                </div>
                <div class="jph-modal-body">
                    <form id="jph-add-badge-form">
                        <div class="jph-form-group">
                            <label for="badge-name">Badge Name:</label>
                            <input type="text" id="badge-name" name="name" required placeholder="e.g., First Session">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-description">Description:</label>
                            <textarea id="badge-description" name="description" rows="3" placeholder="Describe what this badge represents..."></textarea>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-icon">Icon:</label>
                            <input type="text" id="badge-icon" name="icon" placeholder="🏆" value="🏆">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-category">Category:</label>
                            <select id="badge-category" name="category">
                                <option value="achievement">Achievement</option>
                                <option value="milestone">Milestone</option>
                                <option value="special">Special</option>
                                <option value="streak">Streak</option>
                                <option value="level">Level</option>
                                <option value="practice">Practice</option>
                                <option value="improvement">Improvement</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-criteria-type">Criteria Type:</label>
                            <select id="badge-criteria-type" name="criteria_type">
                                <option value="total_xp">Total XP ≥ value</option>
                                <option value="practice_sessions">Practice Sessions ≥ value</option>
                                <option value="streak">Streak Days ≥ value</option>
                                <option value="streak_7">7-day streak</option>
                                <option value="streak_30">30-day streak</option>
                                <option value="streak_100">100-day streak</option>
                                <option value="long_session">Long session (≥ minutes)</option>
                                <option value="improvement_count">Improvements reported ≥ value</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-criteria-value">Criteria Value:</label>
                            <input type="number" id="badge-criteria-value" name="criteria_value" min="1" required placeholder="e.g., 10">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-xp-reward">XP Reward:</label>
                            <input type="number" id="badge-xp-reward" name="xp_reward" min="0" value="50">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="badge-gem-reward">Gem Reward:</label>
                            <input type="number" id="badge-gem-reward" name="gem_reward" min="0" value="10">
                        </div>
                        
                        <div class="jph-modal-actions">
                            <button type="button" class="button button-secondary" onclick="closeAddBadgeModal()">Cancel</button>
                            <button type="submit" class="button button-primary">Create Badge</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Edit Badge Modal -->
        <div id="jph-edit-badge-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h3>Edit Badge</h3>
                    <span class="jph-modal-close" onclick="closeEditBadgeModal()">&times;</span>
                </div>
                <div class="jph-modal-body">
                    <form id="jph-edit-badge-form">
                        <input type="hidden" id="edit-badge-key" name="badge_key">
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-name">Badge Name:</label>
                            <input type="text" id="edit-badge-name" name="name" required placeholder="e.g., First Session">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-description">Description:</label>
                            <textarea id="edit-badge-description" name="description" rows="3" placeholder="Describe what this badge represents..."></textarea>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-icon">Icon:</label>
                            <input type="text" id="edit-badge-icon" name="icon" placeholder="e.g., 🏆 or icon-class">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-category">Category:</label>
                            <select id="edit-badge-category" name="category">
                                <option value="achievement">Achievement</option>
                                <option value="milestone">Milestone</option>
                                <option value="special">Special</option>
                                <option value="streak">Streak</option>
                                <option value="level">Level</option>
                                <option value="practice">Practice</option>
                                <option value="improvement">Improvement</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-criteria-type">Criteria Type:</label>
                            <select id="edit-badge-criteria-type" name="criteria_type">
                                <option value="total_xp">Total XP ≥ value</option>
                                <option value="practice_sessions">Practice Sessions ≥ value</option>
                                <option value="streak">Streak Days ≥ value</option>
                                <option value="streak_7">7-day streak</option>
                                <option value="streak_30">30-day streak</option>
                                <option value="streak_100">100-day streak</option>
                                <option value="long_session">Long session (≥ minutes)</option>
                                <option value="improvement_count">Improvements reported ≥ value</option>
                            </select>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-criteria-value">Criteria Value:</label>
                            <input type="number" id="edit-badge-criteria-value" name="criteria_value" min="0" required placeholder="e.g., 10">
                            <small>Meaning depends on criteria type (e.g., XP amount, session count, minutes, streak days).</small>
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-xp-reward">XP Reward:</label>
                            <input type="number" id="edit-badge-xp-reward" name="xp_reward" min="0" value="0">
                        </div>
                        
                        <div class="jph-form-group">
                            <label for="edit-badge-gem-reward">Gem Reward:</label>
                            <input type="number" id="edit-badge-gem-reward" name="gem_reward" min="0" value="10">
                        </div>
                        
                        <div class="jph-form-group">
                            <label>
                                <input type="checkbox" id="edit-badge-is-active" name="is_active" value="1" checked>
                                Active
                            </label>
                        </div>
                        
                        <div class="jph-modal-actions">
                            <button type="button" class="button button-secondary" onclick="closeEditBadgeModal()">Cancel</button>
                            <button type="button" class="button button-primary" onclick="saveEditBadge()">Update Badge</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
        function showAddBadgeModal() {
            console.log('Showing add badge modal');
            const modal = document.getElementById('jph-add-badge-modal');
            if (modal) {
                modal.style.display = 'flex';
                console.log('Modal displayed');
            } else {
                console.error('Modal not found');
            }
        }
        
        function closeAddBadgeModal() {
            document.getElementById('jph-add-badge-modal').style.display = 'none';
            document.getElementById('jph-add-badge-form').reset();
        }
        
        function editBadge(badgeKey) {
            console.log('Editing badge key:', badgeKey);
            
            // Fetch badge data
            fetch('<?php echo rest_url('jph/v1/admin/badges'); ?>', {
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Find the badge by key
                    const badge = data.badges.find(b => b.badge_key === badgeKey);
                    if (badge) {
                        showEditBadgeModal(badge);
                    } else {
                        alert('Badge not found');
                    }
                } else {
                    alert('Failed to load badge data');
                }
            })
            .catch(error => {
                console.error('Error loading badge:', error);
                alert('Error loading badge data');
            });
        }
        
        function showEditBadgeModal(badge) {
            // Populate the edit form with badge data
            document.getElementById('edit-badge-key').value = badge.badge_key;
            document.getElementById('edit-badge-name').value = badge.name || '';
            document.getElementById('edit-badge-description').value = badge.description || '';
            document.getElementById('edit-badge-icon').value = badge.icon || '';
            document.getElementById('edit-badge-category').value = badge.category || 'achievement';
            document.getElementById('edit-badge-criteria-type').value = badge.criteria_type || 'practice_sessions';
            document.getElementById('edit-badge-criteria-value').value = badge.criteria_value || 0;
            document.getElementById('edit-badge-xp-reward').value = badge.xp_reward || 0;
            document.getElementById('edit-badge-gem-reward').value = badge.gem_reward || 0;
            document.getElementById('edit-badge-is-active').checked = badge.is_active == 1;
            
            // Show the modal
            document.getElementById('jph-edit-badge-modal').style.display = 'block';
        }
        
        function closeEditBadgeModal() {
            document.getElementById('jph-edit-badge-modal').style.display = 'none';
            document.getElementById('jph-edit-badge-form').reset();
        }
        
        function saveEditBadge() {
            const form = document.getElementById('jph-edit-badge-form');
            const formData = new FormData(form);
            const badgeKey = document.getElementById('edit-badge-key').value;
            
            // Convert FormData to JSON
            const badgeData = {
                name: formData.get('name'),
                description: formData.get('description'),
                icon: formData.get('icon'),
                category: formData.get('category'),
                criteria_type: formData.get('criteria_type'),
                criteria_value: parseInt(formData.get('criteria_value')),
                xp_reward: parseInt(formData.get('xp_reward')),
                gem_reward: parseInt(formData.get('gem_reward')),
                is_active: document.getElementById('edit-badge-is-active').checked ? 1 : 0
            };
            
            console.log('Updating badge:', badgeKey, badgeData);
            
            fetch('<?php echo rest_url('jph/v1/badges/key/'); ?>' + badgeKey, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(badgeData)
            })
            .then(response => {
                console.log('Update response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Update response data:', data);
                if (data.success) {
                    alert('Badge updated successfully!');
                    closeEditBadgeModal();
                    // Refresh the page to show updated data
                    location.reload();
                } else {
                    alert('Failed to update badge: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Update error:', error);
                alert('Error updating badge');
            });
        }
        
        function deleteBadge(badgeKey) {
            if (confirm('Are you sure you want to delete this badge? This action cannot be undone.')) {
                console.log('Deleting badge key:', badgeKey);
                fetch('<?php echo rest_url('jph/v1/badges/key/'); ?>' + badgeKey, {
                    method: 'DELETE',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    }
                })
                .then(response => {
                    console.log('Delete response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Delete response data:', data);
                    if (data.success) {
                        alert('Badge deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error deleting badge: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert('Error deleting badge: ' + error.message);
                });
            }
        }
        
        // Handle add badge form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('jph-add-badge-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    
                    console.log('Creating badge with data:', data);
                    
                    fetch('<?php echo rest_url('jph/v1/admin/badges'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        console.log('Add badge response status:', response.status);
                        return response.json();
                    })
                    .then(result => {
                        console.log('Add badge response data:', result);
                        if (result.success) {
                            alert('Badge created successfully!');
                            closeAddBadgeModal();
                            location.reload();
                        } else {
                            alert('Error creating badge: ' + (result.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Add badge error:', error);
                        alert('Error creating badge: ' + error.message);
                    });
                });
            }
        });
        </script>
        
        <style>
        .jph-badges-overview {
            margin: 20px 0;
        }
        
        .jph-badges-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .jph-stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-stat-card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .jph-badges-actions {
            margin: 20px 0;
        }
        
        .jph-badges-actions .button {
            margin-right: 10px;
        }
        
        .badge-status.active {
            color: #46b450;
            font-weight: bold;
        }
        
        .badge-status.inactive {
            color: #dc3232;
            font-weight: bold;
        }
        
        /* Modal Styles */
        .jph-modal {
            display: none;
            position: fixed;
            z-index: 100000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        
        .jph-modal-content {
            background-color: #fff;
            margin: auto;
            padding: 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .jph-modal-header {
            background: #f1f1f1;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .jph-modal-header h2 {
            margin: 0;
            font-size: 18px;
        }
        
        .jph-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .jph-modal-close:hover {
            color: #000;
        }
        
        .jph-modal-body {
            padding: 20px;
        }
        
        .jph-form-group {
            margin-bottom: 15px;
        }
        
        .jph-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .jph-form-group input,
        .jph-form-group textarea,
        .jph-form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .jph-modal-actions {
            margin-top: 20px;
            text-align: right;
        }
        
        .jph-modal-actions .button {
            margin-left: 10px;
        }
        
        /* Badge Category Styles */
        .jph-badge-category {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .jph-badge-category-achievement { background: #e3f2fd; color: #1976d2; }
        .jph-badge-category-milestone { background: #f3e5f5; color: #7b1fa2; }
        .jph-badge-category-special { background: #fff3e0; color: #f57c00; }
        .jph-badge-category-streak { background: #ffebee; color: #d32f2f; }
        .jph-badge-category-level { background: #e8f5e8; color: #388e3c; }
        .jph-badge-category-practice { background: #e0f2f1; color: #00796b; }
        .jph-badge-category-improvement { background: #fce4ec; color: #c2185b; }
        </style>
        <?php
    }
    
    /**
     * Lesson favorites page
     */
    public function lesson_favorites_page() {
        ?>
        <div class="wrap">
            <h1>📚 Lesson Favorites Management</h1>
            
            <div class="jph-admin-stats">
                <div class="jph-stat-card">
                    <h3>Total Favorites</h3>
                    <div class="jph-stat-number" id="total-favorites">Loading...</div>
                </div>
                <div class="jph-stat-card">
                    <h3>Active Users</h3>
                    <div class="jph-stat-number" id="active-users">Loading...</div>
                </div>
                <div class="jph-stat-card">
                    <h3>Most Popular Category</h3>
                    <div class="jph-stat-number" id="popular-category">Loading...</div>
                </div>
            </div>
            
            <div class="jph-admin-actions">
                <button type="button" class="button button-primary" id="refresh-favorites-btn">🔄 Refresh</button>
                <button type="button" class="button button-secondary" id="export-favorites-btn">📊 Export CSV</button>
            </div>
            
            <div class="jph-favorites-container">
                <div class="jph-favorites-filters">
                    <select id="user-filter">
                        <option value="">All Users</option>
                    </select>
                    <select id="category-filter">
                        <option value="">All Categories</option>
                        <option value="lesson">Lesson</option>
                        <option value="technique">Technique</option>
                        <option value="theory">Theory</option>
                        <option value="ear-training">Ear Training</option>
                        <option value="repertoire">Repertoire</option>
                        <option value="improvisation">Improvisation</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="text" id="search-filter" placeholder="Search favorites...">
                </div>
                
                <div class="jph-favorites-table-container">
                    <table class="wp-list-table widefat fixed striped" id="favorites-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>URL</th>
                                <th>Description</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="favorites-tbody">
                            <tr>
                                <td colspan="7" class="loading">Loading lesson favorites...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <style>
        .jph-admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .jph-stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .jph-stat-card h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }
        
        .jph-stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #0073aa;
        }
        
        .jph-admin-actions {
            margin: 20px 0;
        }
        
        .jph-favorites-filters {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            align-items: center;
        }
        
        .jph-favorites-filters select,
        .jph-favorites-filters input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .jph-favorites-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .category-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            background: #e9ecef;
            color: #495057;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Load lesson favorites data
            loadLessonFavoritesData();
            loadLessonFavoritesStats();
            
            // Refresh button
            $('#refresh-favorites-btn').on('click', function() {
                loadLessonFavoritesData();
                loadLessonFavoritesStats();
            });
            
            // Export button
            $('#export-favorites-btn').on('click', function() {
                window.location.href = '<?php echo rest_url('jph/v1/export-lesson-favorites'); ?>';
            });
            
            function loadLessonFavoritesData() {
                $.ajax({
                    url: '<?php echo rest_url('jph/v1/admin/lesson-favorites'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            displayLessonFavorites(response.favorites);
                        } else {
                            $('#favorites-tbody').html('<tr><td colspan="7" class="error">Error loading lesson favorites</td></tr>');
                        }
                    },
                    error: function() {
                        $('#favorites-tbody').html('<tr><td colspan="7" class="error">Error loading lesson favorites</td></tr>');
                    }
                });
            }
            
            function displayLessonFavorites(favorites) {
                let html = '';
                if (favorites.length === 0) {
                    html = '<tr><td colspan="7" class="no-data">No lesson favorites found</td></tr>';
                } else {
                    favorites.forEach(favorite => {
                        const date = new Date(favorite.created_at).toLocaleDateString();
                        html += `
                            <tr>
                                <td>${favorite.user_name || 'Unknown'}</td>
                                <td>${favorite.title}</td>
                                <td><span class="category-badge">${favorite.category}</span></td>
                                <td><a href="${favorite.url}" target="_blank">View</a></td>
                                <td>${favorite.description || ''}</td>
                                <td>${date}</td>
                                <td>
                                    <button class="button button-small" onclick="editFavorite(${favorite.id})">Edit</button>
                                    <button class="button button-small button-link-delete" onclick="deleteFavorite(${favorite.id})">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#favorites-tbody').html(html);
            }
            
            function loadLessonFavoritesStats() {
                $.ajax({
                    url: '<?php echo rest_url('jph/v1/admin/lesson-favorites-stats'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#total-favorites').text(response.stats.total_favorites);
                            $('#active-users').text(response.stats.active_users);
                            $('#popular-category').text(response.stats.popular_category);
                        }
                    },
                    error: function() {
                        $('#total-favorites').text('Error');
                        $('#active-users').text('Error');
                        $('#popular-category').text('Error');
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * Events page
     */
    public function events_page() {
        ?>
        <div class="wrap">
            <h1>🔗 FluentCRM Event Tracking</h1>
            <p>Monitor FluentCRM event tracking from badge achievements and manage event logging.</p>
            
            <div class="jph-event-sections">
                    
                    <!-- Badge Event Information -->
                    <div class="jph-event-section">
                        <h2>🏆 Badge Event Configuration</h2>
                        <p>Badge events are now configured directly within each badge in the <a href="<?php echo admin_url('admin.php?page=aph-badges'); ?>">Badge Management</a> section.</p>
                        
                        <div class="badge-info-grid">
                            <div class="badge-info-item">
                                <h3>✅ Enabled Badges</h3>
                                <p>Badges with FluentCRM tracking enabled will automatically fire events when earned.</p>
                            </div>
                            <div class="badge-info-item">
                                <h3>🔧 Individual Configuration</h3>
                                <p>Each badge can have its own custom event key and title for FluentCRM.</p>
                            </div>
                            <div class="badge-info-item">
                                <h3>⚡ Automatic Tracking</h3>
                                <p>Events are fired automatically when badges are awarded to users.</p>
                            </div>
                        </div>
                            
                        <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border-left: 4px solid #007cba; border-radius: 4px;">
                            <strong>💡 Note:</strong> To configure event tracking for badges, go to <strong>Badge Management</strong> and enable "FluentCRM Event Tracking" for individual badges. This gives you granular control over which badge achievements trigger events.
                        </div>
                    </div>
                    
                    <!-- Event Tracking Testing -->
                    <div class="jph-event-section">
                        <h2>🧪 Event Tracking Testing</h2>
                        <p>Test your FluentCRM event tracking to ensure it's working correctly.</p>
                        
                        <div class="event-test-buttons">
                            <button type="button" class="button button-primary" onclick="testBadgeEvent('first_steps')">
                                🏆 Test First Steps Badge
                            </button>
                            <button type="button" class="button button-secondary" onclick="testBadgeEvent('marathon')">
                                🏆 Test Marathon Badge
                            </button>
                            <button type="button" class="button button-secondary" onclick="testBadgeEvent('streak_protector')">
                                🏆 Test Streak Protector Badge
                            </button>
                            <button type="button" class="button button-secondary" onclick="testAllBadgeEvents()">
                                🏆 Test All Badge Events
                            </button>
                        </div>
                        
                        <div id="webhook-test-results" class="webhook-test-results"></div>
                    </div>
                    
                    <div class="jph-debug-section">
                        <h2>🔍 Badge Assignment Debugging</h2>
                        <p>Comprehensive tools to debug and test badge assignment logic.</p>
                        
                        <div class="badge-debug-controls">
                            <h3>User Badge Status Check</h3>
                            <div class="debug-form-group">
                                <label for="debug-user-id">User ID:</label>
                                <input type="number" id="debug-user-id" value="<?php echo get_current_user_id(); ?>" min="1">
                                <button type="button" onclick="checkUserBadgeStatus()" class="button button-primary">
                                    🔍 Check Badge Status
                                </button>
                            </div>
                            
                            <h3>Badge Assignment Testing</h3>
                            <div class="debug-form-group">
                                <button type="button" onclick="runBadgeAssignmentTest()" class="button button-primary">
                                    ⚡ Run Badge Assignment Test
                                </button>
                                <button type="button" onclick="debugMarathonBadge()" class="button button-secondary">
                                    🏃 Debug Marathon Badge
                                </button>
                                <button type="button" onclick="simulateBadgeCheck()" class="button button-secondary">
                                    🎯 Simulate Badge Check
                                </button>
                            </div>
                            
                            <h3>Database Inspection</h3>
                            <div class="debug-form-group">
                                <button type="button" onclick="inspectBadgeDatabase()" class="button button-primary">
                                    📊 Inspect Badge Database
                                </button>
                                <button type="button" onclick="checkPracticeSessions()" class="button button-secondary">
                                    ⏱️ Check Practice Sessions
                                </button>
                            </div>
                        </div>
                        
                        <div id="badge-debug-results" class="badge-debug-results"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .jph-event-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin: 25px 0;
        }
        
        .jph-event-section {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .jph-event-section h2 {
            margin: 0 0 15px 0;
            color: #1e1e1e;
            font-size: 20px;
            font-weight: 600;
        }
        
        .badge-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .badge-info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
        }
        
        .badge-info-item h3 {
            margin: 0 0 10px 0;
            color: #007cba;
            font-size: 16px;
        }
        
        .event-test-buttons {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .webhook-test-results {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            min-height: 50px;
        }
        
        .jph-debug-section {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .badge-debug-controls {
            margin: 20px 0;
        }
        
        .debug-form-group {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 15px 0;
        }
        
        .debug-form-group label {
            font-weight: 600;
            min-width: 80px;
        }
        
        .debug-form-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .badge-debug-results {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            min-height: 50px;
        }
        </style>
        
        <script>
        function testBadgeEvent(badgeKey) {
            jQuery('#webhook-test-results').html('<p>Testing badge event: ' + badgeKey + '...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('jph/v1/test-badge-event'); ?>',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                data: {
                    badge_key: badgeKey
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#webhook-test-results').html('<p style="color: green;">✅ ' + response.message + '</p>');
                    } else {
                        jQuery('#webhook-test-results').html('<p style="color: red;">❌ ' + response.message + '</p>');
                    }
                },
                error: function() {
                    jQuery('#webhook-test-results').html('<p style="color: red;">❌ Error testing badge event</p>');
                }
            });
        }
        
        function testAllBadgeEvents() {
            jQuery('#webhook-test-results').html('<p>Testing all badge events...</p>');
            
            const badges = ['first_steps', 'marathon', 'streak_protector'];
            let results = [];
            
            badges.forEach((badge, index) => {
                setTimeout(() => {
                    testBadgeEvent(badge);
                }, index * 1000);
            });
        }
        
        function checkUserBadgeStatus() {
            const userId = jQuery('#debug-user-id').val();
            jQuery('#badge-debug-results').html('<p>Checking badge status for user: ' + userId + '...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('jph/v1/debug-user-badges'); ?>',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                data: {
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#badge-debug-results').html('<pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
                    } else {
                        jQuery('#badge-debug-results').html('<p style="color: red;">❌ ' + response.message + '</p>');
                    }
                },
                error: function() {
                    jQuery('#badge-debug-results').html('<p style="color: red;">❌ Error checking badge status</p>');
                }
            });
        }
        
        function runBadgeAssignmentTest() {
            jQuery('#badge-debug-results').html('<p>Running badge assignment test...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('jph/v1/test-badge-assignment'); ?>',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#badge-debug-results').html('<pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
                    } else {
                        jQuery('#badge-debug-results').html('<p style="color: red;">❌ ' + response.message + '</p>');
                    }
                },
                error: function() {
                    jQuery('#badge-debug-results').html('<p style="color: red;">❌ Error running badge assignment test</p>');
                }
            });
        }
        
        function debugMarathonBadge() {
            jQuery('#badge-debug-results').html('<p>Debugging marathon badge...</p>');
            // Placeholder for marathon badge debugging
            jQuery('#badge-debug-results').html('<p>Marathon badge debugging functionality will be implemented.</p>');
        }
        
        function simulateBadgeCheck() {
            jQuery('#badge-debug-results').html('<p>Simulating badge check...</p>');
            // Placeholder for badge check simulation
            jQuery('#badge-debug-results').html('<p>Badge check simulation functionality will be implemented.</p>');
        }
        
        function inspectBadgeDatabase() {
            jQuery('#badge-debug-results').html('<p>Inspecting badge database...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('jph/v1/debug-badge-database'); ?>',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#badge-debug-results').html('<pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
                    } else {
                        jQuery('#badge-debug-results').html('<p style="color: red;">❌ ' + response.message + '</p>');
                    }
                },
                error: function() {
                    jQuery('#badge-debug-results').html('<p style="color: red;">❌ Error inspecting badge database</p>');
                }
            });
        }
        
        function checkPracticeSessions() {
            jQuery('#badge-debug-results').html('<p>Checking practice sessions...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('jph/v1/debug-practice-sessions'); ?>',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#badge-debug-results').html('<pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
                    } else {
                        jQuery('#badge-debug-results').html('<p style="color: red;">❌ ' + response.message + '</p>');
                    }
                },
                error: function() {
                    jQuery('#badge-debug-results').html('<p style="color: red;">❌ Error checking practice sessions</p>');
                }
            });
        }
        </script>
        <?php
    }
    
    /**
     * Documentation page
     */
    public function documentation_page() {
        // For now, redirect to the original plugin's documentation page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'documentation_page')) {
                $main_plugin->documentation_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Documentation</h1>';
        echo '<p>Documentation functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>⚙️ Practice Hub Settings</h1>
            
            <div class="jph-settings-sections">
                <div class="jph-settings-section jph-danger-section">
                    <h2>🧪 DATA MANAGEMENT FOR TESTING</h2>
                    <p><strong>DEVELOPMENT/TESTING TOOL:</strong> This will permanently delete ALL user data and cannot be undone!</p>
                    
                    <div class="clear-all-section">
                        <p>This action will clear:</p>
                        <ul>
                            <li>📝 All practice sessions and items</li>
                            <li>👥 All user statistics (XP, levels, streaks)</li>
                            <li>🎖️ All earned badges (user badges)</li>
                            <li>💎 All gem transactions and balances</li>
                            <li>❤️ All lesson favorites</li>
                        </ul>
                        <p><strong>Note:</strong> This will NOT delete badge definitions or plugin settings.</p>
                        
                        <button type="button" class="button button-danger jph-clear-all-btn" onclick="confirmClearAllUserData()">
                            💥 CLEAR ALL USER DATA
                        </button>
                    </div>
                    
                    <div id="danger-results" class="danger-results"></div>
                </div>
            </div>
        </div>
        
        <style>
        .jph-settings-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin: 25px 0;
            max-width: 900px;
        }
        
        .jph-settings-section {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .jph-settings-section:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        
        .jph-settings-section h2 {
            margin: 0 0 15px 0;
            color: #1e1e1e;
            font-size: 20px;
            font-weight: 600;
            border-bottom: 2px solid #f5f5f5;
            padding-bottom: 10px;
        }
        
        .jph-settings-section p {
            margin: 8px 0;
            color: #555;
            font-size: 15px;
            line-height: 1.5;
        }
        
        /* Danger Zone Styles */
        .jph-danger-section {
            border: 2px solid #dc3545 !important;
            background: #fff5f5 !important;
        }
        
        .jph-danger-section h2 {
            color: #dc3545 !important;
            font-weight: bold;
        }
        
        .jph-danger-section p {
            color: #721c24 !important;
            font-weight: 500;
        }
        
        .clear-all-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .clear-all-section ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .clear-all-section li {
            margin: 5px 0;
            color: #721c24;
        }
        
        .button-danger {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
            font-weight: bold;
            padding: 12px 24px;
            font-size: 16px;
        }
        
        .button-danger:hover {
            background: #c82333 !important;
            border-color: #bd2130 !important;
        }
        
        .danger-results {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            min-height: 50px;
        }
        </style>
        
        <script>
        function confirmClearAllUserData() {
            if (confirm('⚠️ DANGER: This will permanently delete ALL user data including:\n\n• All practice sessions and items\n• All user statistics (XP, levels, streaks)\n• All earned badges\n• All gem transactions and balances\n• All lesson favorites\n\nThis action CANNOT be undone!\n\nAre you absolutely sure you want to continue?')) {
                clearAllUserData();
            }
        }
        
        function clearAllUserData() {
            jQuery('#danger-results').html('<p>Clearing all user data...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('jph/v1/admin/clear-all-user-data'); ?>',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#danger-results').html('<p style="color: green;">✅ ' + response.message + '</p>');
                    } else {
                        jQuery('#danger-results').html('<p style="color: red;">❌ ' + response.message + '</p>');
                    }
                },
                error: function() {
                    jQuery('#danger-results').html('<p style="color: red;">❌ Error clearing user data</p>');
                }
            });
        }
        </script>
        <?php
    }
}
