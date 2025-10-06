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
        // For now, redirect to the original plugin's lesson favorites page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'lesson_favorites_page')) {
                $main_plugin->lesson_favorites_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Lesson Favorites</h1>';
        echo '<p>Lesson favorites functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Events page
     */
    public function events_page() {
        // For now, redirect to the original plugin's events page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'events_page')) {
                $main_plugin->events_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Event Tracking</h1>';
        echo '<p>Event tracking functionality will be implemented here.</p>';
        echo '</div>';
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
        // For now, redirect to the original plugin's settings page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'settings_page')) {
                $main_plugin->settings_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Settings</h1>';
        echo '<p>Settings functionality will be implemented here.</p>';
        echo '</div>';
    }
}
