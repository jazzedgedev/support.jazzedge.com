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
        // Main Practice Hub dashboard as the primary entry point
        
        
        add_menu_page(
            __('Practice Hub', 'academy-practice-hub'),
            __('Practice Hub', 'academy-practice-hub'),
            'manage_options',
            'aph-practice-hub',
            array($this, 'students_page'),
            'dashicons-format-audio',
            30
        );
        
        add_submenu_page(
            'aph-practice-hub',
            __('Badges', 'academy-practice-hub'),
            __('Badges', 'academy-practice-hub'),
            'manage_options',
            'aph-badges',
            array($this, 'badges_page')
        );
        
        add_submenu_page(
            'aph-practice-hub',
            __('Lesson Favorites', 'academy-practice-hub'),
            __('Lesson Favorites', 'academy-practice-hub'),
            'manage_options',
            'aph-lesson-favorites',
            array($this, 'lesson_favorites_page')
        );
        
        add_submenu_page(
            'aph-practice-hub',
            __('Event Tracking', 'academy-practice-hub'),
            __('Event Tracking', 'academy-practice-hub'),
            'manage_options',
            'aph-fluent-crm-events',
            array($this, 'events_page')
        );
        
        add_submenu_page(
            'aph-practice-hub',
            __('Documentation', 'academy-practice-hub'),
            __('Documentation', 'academy-practice-hub'),
            'manage_options',
            'aph-documentation',
            array($this, 'documentation_page')
        );
        
        add_submenu_page(
            'aph-practice-hub',
            __('AI Settings', 'academy-practice-hub'),
            __('AI Settings', 'academy-practice-hub'),
            'manage_options',
            'aph-ai-settings',
            array($this, 'ai_settings_page')
        );
        
        add_submenu_page(
            'aph-practice-hub',
            __('Widgets', 'academy-practice-hub'),
            __('Widgets', 'academy-practice-hub'),
            'manage_options',
            'aph-widgets',
            array($this, 'widgets_page')
        );
        
        add_submenu_page(
            'aph-practice-hub',
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
     * Students page (main dashboard)
     */
    public function students_page() {
        ?>
        <div class="wrap">
            <h1>👥 Practice Hub Students</h1>
            
            <div class="jph-students-overview">
                <div class="jph-students-stats">
                    <div class="jph-stat-card">
                        <h3>Total Students</h3>
                        <p id="total-students">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Active This Week</h3>
                        <p id="active-students">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Total Practice Hours</h3>
                        <p id="total-hours">Loading...</p>
                    </div>
                    <div class="jph-stat-card">
                        <h3>Average Level</h3>
                        <p id="average-level">Loading...</p>
                    </div>
                </div>
            </div>
            
            <div class="jph-students-filters">
                <div class="jph-filter-group">
                    <label for="student-search">Search Students:</label>
                    <input type="text" id="student-search" placeholder="Search by name or email...">
                </div>
                <div class="jph-filter-group">
                    <label for="level-filter">Filter by Level:</label>
                    <select id="level-filter">
                        <option value="">All Levels</option>
                        <option value="1">Level 1</option>
                        <option value="2">Level 2</option>
                        <option value="3">Level 3+</option>
                    </select>
                </div>
                <div class="jph-filter-group">
                    <label for="activity-filter">Activity Status:</label>
                    <select id="activity-filter">
                        <option value="">All Students</option>
                        <option value="active">Active (7 days)</option>
                        <option value="inactive">Inactive (30+ days)</option>
                    </select>
                </div>
                <div class="jph-filter-group">
                    <button type="button" class="button button-primary" id="search-students-btn">🔍 Search</button>
                    <button type="button" class="button button-secondary" id="clear-filters-btn">Clear Filters</button>
                </div>
            </div>
            
            <div class="jph-students-table-container">
                <table class="jph-students-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Student</th>
                            <th>Level</th>
                            <th>XP</th>
                            <th>Current Streak</th>
                            <th>Longest Streak</th>
                            <th>Badges</th>
                            <th>Shields</th>
                            <th>Last Practice</th>
                            <th>Total Sessions</th>
                            <th>Total Hours</th>
                            <th>Gems</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body">
                        <tr>
                            <td colspan="13" class="jph-loading">Loading students...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="jph-students-actions">
                <button type="button" class="button button-primary" onclick="refreshStudents()">Refresh Data</button>
                <button type="button" class="button button-secondary" onclick="exportStudents()">Export CSV</button>
                <button type="button" class="button button-secondary" onclick="showStudentAnalytics()">View Analytics</button>
            </div>
        </div>
        
        <!-- View Student Modal -->
        <div id="jph-view-student-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>👤 Student Details</h2>
                    <button class="jph-modal-close" onclick="closeViewStudentModal()">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="jph-modal-body" id="jph-view-student-content">
                    <div class="jph-loading">Loading student details...</div>
                </div>
            </div>
        </div>
        
        <!-- Edit Student Modal -->
        <div id="jph-edit-student-modal" class="jph-modal" style="display: none;">
            <div class="jph-modal-content">
                <div class="jph-modal-header">
                    <h2>✏️ Edit Student Stats</h2>
                    <button class="jph-modal-close" onclick="closeEditStudentModal()">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="jph-modal-body" id="jph-edit-student-content">
                    <div class="jph-loading">Loading student data...</div>
                </div>
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
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }
        
        .jph-stat-card p {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #0073aa;
        }
        
        .jph-students-filters {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .jph-filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .jph-filter-group label {
            font-weight: 600;
            font-size: 14px;
        }
        
        .jph-filter-group input,
        .jph-filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 150px;
        }
        
        .jph-students-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 20px 0;
        }
        
        .jph-students-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .jph-students-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
        }
        
        .jph-students-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .jph-students-table tr:hover {
            background: #f8f9fa;
        }
        
        .jph-students-actions {
            margin: 20px 0;
        }
        
        .jph-loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .jph-modal {
            position: fixed;
            z-index: 100000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .jph-modal-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            max-height: 90vh;
            max-width: 90vw;
            width: 90%;
            overflow-y: auto;
            position: relative;
            margin: auto;
            padding: 30px;
        }
        
        .jph-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .jph-modal-header h2 {
            margin: 0;
            color: #333;
        }
        
        .jph-modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }
        
        .jph-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            z-index: 10;
        }
        
        .jph-close:hover {
            color: #333;
        }
        
        .jph-modal-body {
            padding: 20px;
        }
        
        .jph-edit-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .jph-edit-form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .jph-edit-form-group label {
            font-weight: 600;
            font-size: 14px;
        }
        
        .jph-edit-form-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .jph-edit-form-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        
        @media (max-width: 768px) {
            .explanation-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Stats Help Button Styling */
        .jph-stats-help-btn {
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(10px);
            padding: 10px 16px !important;
            font-size: 14px !important;
            white-space: nowrap;
        }
        
        .jph-stats-help-btn:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            transform: translateY(-1px);
        }
        
        .jph-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .jph-btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }
        
        .jph-btn-secondary:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        
        .btn-icon {
            font-size: 16px;
        }
        </style>
        
        <script>
        // Load students data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStudentsStats();
            loadStudents();
            
            // Search functionality
            document.getElementById('search-students-btn').addEventListener('click', function() {
                loadStudents();
            });
            
            document.getElementById('clear-filters-btn').addEventListener('click', function() {
                document.getElementById('student-search').value = '';
                document.getElementById('level-filter').value = '';
                document.getElementById('activity-filter').value = '';
                loadStudents();
            });
        });
        
        function formatDate(dateString) {
            if (!dateString) return 'Never';
            
            // Handle YYYY-MM-DD format from database
            const date = new Date(dateString + 'T00:00:00');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            const year = date.getFullYear();
            
            return `${month}/${day}/${year}`;
        }
        
        function loadStudentsStats() {
            fetch('<?php echo rest_url('aph/v1/students/stats'); ?>', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('total-students').textContent = data.stats.total_students;
                    document.getElementById('active-students').textContent = data.stats.active_students;
                    document.getElementById('total-hours').textContent = data.stats.total_hours;
                    document.getElementById('average-level').textContent = data.stats.average_level;
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
        }
        
        function loadStudents() {
            const search = document.getElementById('student-search').value;
            const level = document.getElementById('level-filter').value;
            const activity = document.getElementById('activity-filter').value;
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (level) params.append('level', level);
            if (activity) params.append('activity', activity);
            
            const url = '<?php echo rest_url('aph/v1/students'); ?>' + (params.toString() ? '?' + params.toString() : '');
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayStudents(data.students);
                } else {
                    document.getElementById('students-table-body').innerHTML = '<tr><td colspan="13">Error loading students</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                document.getElementById('students-table-body').innerHTML = '<tr><td colspan="13">Error loading students</td></tr>';
            });
        }
        
        function displayStudents(students) {
            const tbody = document.getElementById('students-table-body');
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="13">No students found</td></tr>';
                return;
            }
            
            tbody.innerHTML = students.map(student => `
                <tr>
                    <td>
                        <a href="<?php echo admin_url('user-edit.php?user_id='); ?>${student.ID}" target="_blank">
                            ${student.ID}
                        </a>
                    </td>
                    <td>
                        <strong>${student.display_name || 'Unknown'}</strong><br>
                        <small>${student.user_email || ''}</small>
                    </td>
                    <td>${student.current_level || 1}</td>
                    <td>${student.total_xp || 0}</td>
                    <td>${student.current_streak || 0}</td>
                    <td>${student.longest_streak || 0}</td>
                    <td>${student.badges_earned || 0}</td>
                    <td>${student.streak_shield_count || 0}</td>
                    <td>${student.last_practice_date ? formatDate(student.last_practice_date) : 'Never'}</td>
                    <td>${student.total_sessions || 0}</td>
                    <td>${Math.round((student.total_minutes || 0) / 60 * 10) / 10}h</td>
                    <td>${student.gems_balance || 0}</td>
                    <td>
                        <button type="button" class="button button-small" onclick="viewStudent(${student.ID})">View</button>
                        <button type="button" class="button button-small" onclick="editStudentStats(${student.ID})">Edit</button>
                    </td>
                </tr>
            `).join('');
        }
        
        function viewStudent(userId) {
            const modal = document.getElementById('jph-view-student-modal');
            const content = document.getElementById('jph-view-student-content');
            
            modal.style.display = 'flex';
            content.innerHTML = '<div class="jph-loading">Loading student details...</div>';
            
            fetch(`<?php echo rest_url('aph/v1/students/'); ?>${userId}`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const student = data.student;
                    content.innerHTML = `
                        <div class="jph-student-details">
                            <h3>${student.display_name || 'Unknown'}</h3>
                            <p><strong>Email:</strong> ${student.user_email || 'N/A'}</p>
                            <p><strong>Total XP:</strong> ${student.total_xp || 0}</p>
                            <p><strong>Level:</strong> ${student.current_level || 1}</p>
                            <p><strong>Current Streak:</strong> ${student.current_streak || 0}</p>
                            <p><strong>Longest Streak:</strong> ${student.longest_streak || 0}</p>
                            <p><strong>Badges Earned:</strong> ${student.badges_earned || 0}</p>
                            <p><strong>Total Sessions:</strong> ${student.total_sessions || 0}</p>
                            <p><strong>Total Minutes:</strong> ${student.total_minutes || 0}</p>
                            <p><strong>Gems Balance:</strong> ${student.gems_balance || 0}</p>
                            <p><strong>Hearts Count:</strong> ${student.hearts_count || 0}</p>
                            <p><strong>Streak Shields:</strong> ${student.streak_shield_count || 0}</p>
                            <p><strong>Last Practice:</strong> ${student.last_practice_date ? formatDate(student.last_practice_date) : 'Never'}</p>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<p>Error loading student details</p>';
                }
            })
            .catch(error => {
                console.error('Error loading student:', error);
                content.innerHTML = '<p>Error loading student details</p>';
            });
        }
        
        function editStudentStats(userId) {
            const modal = document.getElementById('jph-edit-student-modal');
            const content = document.getElementById('jph-edit-student-content');
            
            modal.style.display = 'flex';
            content.innerHTML = '<div class="jph-loading">Loading student data...</div>';
            
            fetch(`<?php echo rest_url('aph/v1/students/'); ?>${userId}`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderEditStudentForm(data.student);
                } else {
                    content.innerHTML = '<p>Error loading student data</p>';
                }
            })
            .catch(error => {
                console.error('Error loading student:', error);
                content.innerHTML = '<p>Error loading student data</p>';
            });
        }
        
        function renderEditStudentForm(student) {
            const content = document.getElementById('jph-edit-student-content');
            
            content.innerHTML = `
                <form id="jph-edit-student-form" onsubmit="saveStudentStats(event, ${student.ID})">
                    <div class="jph-edit-form">
                        <div class="jph-edit-form-group">
                            <label>Total XP</label>
                            <input type="number" name="total_xp" value="${student.total_xp || 0}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Current Level</label>
                            <input type="number" name="current_level" value="${student.current_level || 1}" min="1" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Current Streak</label>
                            <input type="number" name="current_streak" value="${student.current_streak || 0}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Longest Streak</label>
                            <input type="number" name="longest_streak" value="${student.longest_streak || 0}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Total Sessions</label>
                            <input type="number" name="total_sessions" value="${student.total_sessions || 0}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Total Minutes</label>
                            <input type="number" name="total_minutes" value="${student.total_minutes || 0}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Hearts Count</label>
                            <input type="number" name="hearts_count" value="${student.hearts_count || 0}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Gems Balance</label>
                            <input type="number" name="gems_balance" value="${student.gems_balance || 0}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Streak Shields</label>
                            <input type="number" name="streak_shield_count" value="${student.streak_shield_count || 0}" min="0" max="3" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Badges Earned</label>
                            <input type="number" name="badges_earned" value="${student.badges_earned || 0}" min="0" required>
                        </div>
                        <div class="jph-edit-form-group">
                            <label>Last Practice Date</label>
                            <input type="date" name="last_practice_date" value="${student.last_practice_date || ''}">
                        </div>
                        <div class="jph-edit-form-actions">
                            <button type="button" class="button button-secondary" onclick="closeEditStudentModal()">Cancel</button>
                            <button type="submit" class="button button-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            `;
        }
        
        function saveStudentStats(event, userId) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());
            
            fetch(`<?php echo rest_url('aph/v1/students/'); ?>${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Student updated successfully!');
                    closeEditStudentModal();
                    loadStudents();
                    loadStudentsStats();
                } else {
                    alert('Error updating student: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error updating student:', error);
                alert('Error updating student');
            });
        }
        
        function closeViewStudentModal() {
            document.getElementById('jph-view-student-modal').style.display = 'none';
        }
        
        function closeEditStudentModal() {
            document.getElementById('jph-edit-student-modal').style.display = 'none';
        }
        
        function refreshStudents() {
            loadStudentsStats();
            loadStudents();
        }
        
        function exportStudents() {
            window.location.href = '<?php echo rest_url('aph/v1/export-students'); ?>';
        }
        
        function showStudentAnalytics() {
            alert('Student analytics - Coming Soon');
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('jph-view-student-modal');
            const editModal = document.getElementById('jph-edit-student-modal');
            
            if (event.target === viewModal) {
                closeViewStudentModal();
            }
            if (event.target === editModal) {
                closeEditStudentModal();
            }
        }
        </script>
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
                                            <div style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 4px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>
                                                </svg>
                                            </div>
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
                            <label for="badge-image-url">Image URL:</label>
                            <input type="url" id="badge-image-url" name="image_url" placeholder="https://example.com/badge-image.png">
                            <small>Enter the full URL to the badge image (PNG, JPG, or SVG recommended)</small>
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
                            <label for="edit-badge-image-url">Image URL:</label>
                            <input type="url" id="edit-badge-image-url" name="image_url" placeholder="https://example.com/badge-image.png">
                            <small>Enter the full URL to the badge image (PNG, JPG, or SVG recommended)</small>
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
            fetch('<?php echo rest_url('aph/v1/admin/badges'); ?>', {
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
            document.getElementById('edit-badge-image-url').value = badge.image_url || '';
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
            
            fetch('<?php echo rest_url('aph/v1/badges/key/'); ?>' + badgeKey, {
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
                fetch('<?php echo rest_url('aph/v1/badges/key/'); ?>' + badgeKey, {
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
                    
                    fetch('<?php echo rest_url('aph/v1/admin/badges'); ?>', {
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
        
        function clearCache() {
            if (confirm('Are you sure you want to clear all cache? This will refresh leaderboard data.')) {
                jQuery('#test-results').html('<p>🔄 Clearing cache...</p>').show().removeClass('success error');
                
                jQuery.ajax({
                    url: '<?php echo rest_url('aph/v1/admin/clear-cache'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery('#test-results').html(
                                '<p>✅ Cache cleared successfully!</p>' +
                                '<p><strong>Leaderboard will refresh on next load.</strong></p>'
                            ).addClass('success');
                        } else {
                            jQuery('#test-results').html('<p>❌ Clear failed: ' + response.message + '</p>').addClass('error');
                        }
                    },
                    error: function() {
                        jQuery('#test-results').html('<p>❌ Clear failed: Network error</p>').addClass('error');
                    }
                });
            }
        }
        
        function updateBadgesSchema() {
            if (confirm('Are you sure you want to update the badges schema? This will add image_url column and remove icon column.')) {
                jQuery('#test-results').html('<p>🔄 Updating badges schema...</p>').show().removeClass('success error');
                
                jQuery.ajax({
                    url: '<?php echo rest_url('aph/v1/admin/update-badges-schema'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery('#test-results').html(
                                '<p>✅ Badges schema updated successfully!</p>' +
                                '<p><strong>Badge images should now display correctly.</strong></p>'
                            ).addClass('success');
                        } else {
                            jQuery('#test-results').html('<p>❌ Update failed: ' + response.message + '</p>').addClass('error');
                        }
                    },
                    error: function() {
                        jQuery('#test-results').html('<p>❌ Update failed: Network error</p>').addClass('error');
                    }
                });
            }
        }
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
                console.log('Refresh button clicked');
                $(this).prop('disabled', true).text('🔄 Refreshing...');
                
                // Show loading state
                $('#favorites-tbody').html('<tr><td colspan="7" class="loading">Refreshing lesson favorites...</td></tr>');
                $('#total-favorites').text('Loading...');
                $('#active-users').text('Loading...');
                $('#popular-category').text('Loading...');
                
                // Load data
                loadLessonFavoritesData();
                loadLessonFavoritesStats();
                
                // Re-enable button after a short delay
                setTimeout(() => {
                    $('#refresh-favorites-btn').prop('disabled', false).text('🔄 Refresh');
                }, 1000);
            });
            
            // Export button
            $('#export-favorites-btn').on('click', function() {
                window.location.href = '<?php echo rest_url('aph/v1/export-lesson-favorites'); ?>';
            });
            
            function loadLessonFavoritesData() {
                console.log('Loading lesson favorites data...');
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/admin/lesson-favorites'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        console.log('Lesson favorites response:', response);
                        if (response.success) {
                            displayLessonFavorites(response.favorites);
                        } else {
                            console.error('Lesson favorites error:', response);
                            $('#favorites-tbody').html('<tr><td colspan="7" class="error">Error loading lesson favorites: ' + (response.message || 'Unknown error') + '</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Lesson favorites AJAX error:', xhr, status, error);
                        $('#favorites-tbody').html('<tr><td colspan="7" class="error">Error loading lesson favorites: ' + error + '</td></tr>');
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
                console.log('Loading lesson favorites stats...');
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/admin/lesson-favorites-stats'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        console.log('Lesson favorites stats response:', response);
                        if (response.success) {
                            $('#total-favorites').text(response.stats.total_favorites);
                            $('#active-users').text(response.stats.active_users);
                            $('#popular-category').text(response.stats.popular_category);
                        } else {
                            console.error('Lesson favorites stats error:', response);
                            $('#total-favorites').text('Error');
                            $('#active-users').text('Error');
                            $('#popular-category').text('Error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Lesson favorites stats AJAX error:', xhr, status, error);
                        $('#total-favorites').text('Error');
                        $('#active-users').text('Error');
                        $('#popular-category').text('Error');
                    }
                });
            }
            
            // Edit favorite function
            window.editFavorite = function(favoriteId) {
                // Find the favorite data
                $.ajax({
                    url: '<?php echo rest_url('aph/v1/admin/lesson-favorites'); ?>',
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            const favorite = response.favorites.find(f => f.id == favoriteId);
                            if (favorite) {
                                showEditModal(favorite);
                            }
                        }
                    }
                });
            };
            
            // Delete favorite function
            window.deleteFavorite = function(favoriteId) {
                if (confirm('Are you sure you want to delete this lesson favorite? This action cannot be undone.')) {
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/admin/lesson-favorites'); ?>',
                        method: 'DELETE',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: {
                            favorite_id: favoriteId
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Lesson favorite deleted successfully!');
                                loadLessonFavoritesData();
                                loadLessonFavoritesStats();
                            } else {
                                alert('Error deleting lesson favorite: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error deleting lesson favorite');
                        }
                    });
                }
            };
            
            // Show edit modal
            function showEditModal(favorite) {
                const modal = `
                    <div id="edit-favorite-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;">
                        <div style="background: white; padding: 30px; border-radius: 8px; width: 500px; max-width: 90%;">
                            <h3>Edit Lesson Favorite</h3>
                            <form id="edit-favorite-form">
                                <input type="hidden" id="edit-favorite-id" value="${favorite.id}">
                                <table class="form-table">
                                    <tr>
                                        <th><label for="edit-title">Title</label></th>
                                        <td><input type="text" id="edit-title" value="${favorite.title}" style="width: 100%;" required></td>
                                    </tr>
                                    <tr>
                                        <th><label for="edit-category">Category</label></th>
                                        <td>
                                            <select id="edit-category" style="width: 100%;">
                                                <option value="lesson" ${favorite.category === 'lesson' ? 'selected' : ''}>Lesson</option>
                                                <option value="technique" ${favorite.category === 'technique' ? 'selected' : ''}>Technique</option>
                                                <option value="theory" ${favorite.category === 'theory' ? 'selected' : ''}>Theory</option>
                                                <option value="ear-training" ${favorite.category === 'ear-training' ? 'selected' : ''}>Ear Training</option>
                                                <option value="repertoire" ${favorite.category === 'repertoire' ? 'selected' : ''}>Repertoire</option>
                                                <option value="improvisation" ${favorite.category === 'improvisation' ? 'selected' : ''}>Improvisation</option>
                                                <option value="other" ${favorite.category === 'other' ? 'selected' : ''}>Other</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="edit-url">URL</label></th>
                                        <td><input type="url" id="edit-url" value="${favorite.url}" style="width: 100%;" required></td>
                                    </tr>
                                    <tr>
                                        <th><label for="edit-description">Description</label></th>
                                        <td><textarea id="edit-description" style="width: 100%; height: 80px;">${favorite.description || ''}</textarea></td>
                                    </tr>
                                </table>
                                <div style="margin-top: 20px; text-align: right;">
                                    <button type="button" class="button" onclick="closeEditModal()">Cancel</button>
                                    <button type="submit" class="button button-primary" style="margin-left: 10px;">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                
                $('body').append(modal);
                
                $('#edit-favorite-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = {
                        favorite_id: $('#edit-favorite-id').val(),
                        title: $('#edit-title').val(),
                        category: $('#edit-category').val(),
                        url: $('#edit-url').val(),
                        description: $('#edit-description').val()
                    };
                    
                    $.ajax({
                        url: '<?php echo rest_url('aph/v1/admin/lesson-favorites'); ?>',
                        method: 'PUT',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                alert('Lesson favorite updated successfully!');
                                closeEditModal();
                                loadLessonFavoritesData();
                            } else {
                                alert('Error updating lesson favorite: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error updating lesson favorite');
                        }
                    });
                });
            }
            
            // Close edit modal
            window.closeEditModal = function() {
                $('#edit-favorite-modal').remove();
            };
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
                    
                    <!-- Event Tracking Logs -->
                    <div class="jph-event-section">
                        <h2>📋 Event Tracking Logs</h2>
                        <p>View recent FluentCRM event activity and badge event tracking logs.</p>
                        
                        <div class="event-logs-tabs">
                            <button type="button" class="tab-button active" onclick="showTab('badge-logs')">🏆 Badge Events</button>
                            <button type="button" class="tab-button" onclick="showTab('fluentcrm-logs')">🔗 FluentCRM Events</button>
                        </div>
                        
                        <!-- Badge Event Logs -->
                        <div id="badge-logs" class="tab-content active">
                            <div class="logs-controls">
                                <button type="button" class="button button-primary" onclick="refreshBadgeEventLogs()">🔄 Refresh Badge Logs</button>
                                <button type="button" class="button button-secondary" onclick="clearBadgeEventLogs()">🗑️ Clear Badge Logs</button>
                            </div>
                            <div id="badge-event-logs-content" class="webhook-logs-content">
                                <!-- Badge event logs will be loaded via AJAX -->
                            </div>
                        </div>
                        
                        <!-- FluentCRM Event Logs -->
                        <div id="fluentcrm-logs" class="tab-content">
                            <div class="logs-controls">
                                <button type="button" class="button button-primary" onclick="loadEventLogs()">🔄 Refresh FluentCRM Logs</button>
                                <button type="button" class="button button-secondary" onclick="emptyEventTrackingTable()">🗑️ Empty Event Table</button>
                            </div>
                            <div id="event-logs-results" class="webhook-logs-content">
                                <!-- FluentCRM event logs will be loaded via AJAX -->
                            </div>
                        </div>
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
        
        /* Event Logs Tabs */
        .event-logs-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .tab-button {
            background: #f8f9fa;
            border: 1px solid #e1e1e1;
            border-bottom: none;
            padding: 12px 20px;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.2s ease;
        }
        
        .tab-button:hover {
            background: #e9ecef;
            color: #333;
        }
        
        .tab-button.active {
            background: #fff;
            color: #007cba;
            border-color: #007cba;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        
        .tab-content {
            display: none;
            padding: 20px 0;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .logs-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .webhook-logs-content {
            background: #f8f9fa;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .log-entry {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #ccc;
        }
        
        .log-entry.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .log-entry.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .log-entry.info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        
        .log-user-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        </style>
        
        <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            jQuery('.tab-content').removeClass('active');
            jQuery('.tab-button').removeClass('active');
            
            // Show selected tab
            jQuery('#' + tabName).addClass('active');
            jQuery('button[onclick="showTab(\'' + tabName + '\')"]').addClass('active');
            
            // Load content for the selected tab
            if (tabName === 'badge-logs') {
                refreshBadgeEventLogs();
            } else if (tabName === 'fluentcrm-logs') {
                loadEventLogs();
            }
        }
        
        // Badge Event Logs Functions
        function refreshBadgeEventLogs() {
            const logsDiv = document.getElementById('badge-event-logs-content');
            logsDiv.innerHTML = 'Loading badge event logs...';
            
            jQuery.ajax({
                url: '<?php echo rest_url('aph/v1/event-logs/badge'); ?>',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        logsDiv.innerHTML = response.data;
                    } else {
                        logsDiv.innerHTML = 'Error loading badge logs: ' + response.message;
                    }
                },
                error: function() {
                    logsDiv.innerHTML = 'Error loading badge event logs.';
                }
            });
        }
        
        function clearBadgeEventLogs() {
            if (confirm('Are you sure you want to clear all badge event tracking logs?')) {
                jQuery.ajax({
                    url: '<?php echo rest_url('aph/v1/event-logs/clear-badge'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        refreshBadgeEventLogs();
                    },
                    error: function() {
                        alert('Error clearing badge logs');
                    }
                });
            }
        }
        
        // FluentCRM Event Logs Functions
        function loadEventLogs() {
            const logsDiv = document.getElementById('event-logs-results');
            logsDiv.innerHTML = 'Loading FluentCRM event logs...';
            
            jQuery.ajax({
                url: '<?php echo rest_url('aph/v1/event-logs/fluentcrm'); ?>',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        logsDiv.innerHTML = '<pre>' + response.data + '</pre>';
                    } else {
                        logsDiv.innerHTML = 'Error loading logs: ' + response.message;
                    }
                },
                error: function() {
                    logsDiv.innerHTML = 'Error loading FluentCRM event logs.';
                }
            });
        }
        
        function emptyEventTrackingTable() {
            if (confirm('Are you sure you want to empty the FluentCRM event tracking table? This action cannot be undone.')) {
                jQuery.ajax({
                    url: '<?php echo rest_url('aph/v1/event-logs/empty-fluentcrm'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        loadEventLogs();
                    },
                    error: function() {
                        alert('Error emptying event tracking table');
                    }
                });
            }
        }
        
        function testBadgeEvent(badgeKey) {
            jQuery('#webhook-test-results').html('<p>Testing badge event: ' + badgeKey + '...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('aph/v1/test-badge-event'); ?>',
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
                url: '<?php echo rest_url('aph/v1/debug-user-badges'); ?>',
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
                url: '<?php echo rest_url('aph/v1/test-badge-assignment'); ?>',
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
        
        function inspectBadgeDatabase() {
            jQuery('#badge-debug-results').html('<p>Inspecting badge database...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('aph/v1/debug-badge-database'); ?>',
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
                url: '<?php echo rest_url('aph/v1/debug-practice-sessions'); ?>',
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
        
        // Initialize event tracking logs on page load
        jQuery(document).ready(function() {
            // Load badge event logs by default
            refreshBadgeEventLogs();
        });
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
     * AI Settings page
     */
    public function ai_settings_page() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['ai_settings_nonce'], 'ai_settings_action')) {
            $ai_prompt = sanitize_textarea_field($_POST['ai_prompt']);
            $ai_system_message = sanitize_textarea_field($_POST['ai_system_message']);
            $ai_model = sanitize_text_field($_POST['ai_model']);
            $ai_max_tokens = intval($_POST['ai_max_tokens']);
            $ai_temperature = floatval($_POST['ai_temperature']);
            
            update_option('aph_ai_prompt', $ai_prompt);
            update_option('aph_ai_system_message', $ai_system_message);
            update_option('aph_ai_model', $ai_model);
            update_option('aph_ai_max_tokens', $ai_max_tokens);
            update_option('aph_ai_temperature', $ai_temperature);
            
            echo '<div class="notice notice-success"><p>AI settings saved successfully!</p></div>';
        }
        
        // Get current settings
        $ai_prompt = get_option('aph_ai_prompt', 'IMPORTANT: Respond with plain text only. No emojis, no markdown, no bold text, no section headers. Use only simple paragraph text.

Analyze this piano practice data from the last 30 days and provide insights in 2–3 sentences. Be encouraging, specific, and actionable. Use the data to highlight positive progress, consistency, and areas for small improvements.

Practice Sessions: {total_sessions} sessions
Total Practice Time: {total_minutes} minutes
Average Session Length: {avg_duration} minutes
Average Mood/Sentiment: {avg_sentiment}/5 (1=frustrating, 5=excellent)
Improvement Rate: {improvement_rate}% of sessions showed improvement
Most Frequent Practice Day: {most_frequent_day}
Most Practiced Item: {most_practiced_item}
Current Level: {current_level}
Current Streak: {current_streak} days

Provide specific, motivational insights about their practice habits and suggest 1–2 focused next steps for improvement. Keep it uplifting, practical, and concise. When recommending lessons, use these titles naturally where relevant: Technique - Jazzedge Practice Curriculum™; Improvisation - The Confident Improviser™; Accompaniment - Piano Accompaniment Essentials™; Jazz Standards - Standards By The Dozen™; Super Easy Jazz Standards - Super Simple Standards™.

Remember: Plain text only, no formatting.');
        
        $ai_system_message = get_option('aph_ai_system_message', 'You are a helpful piano practice coach. Provide encouraging, specific insights about practice patterns. CRITICAL: Always respond with plain text only - no emojis, no markdown formatting, no bold text, no section headers. Use only simple paragraph text.');
        $ai_model = get_option('aph_ai_model', 'gpt-3.5-turbo');
        $ai_max_tokens = get_option('aph_ai_max_tokens', 300);
        $ai_temperature = get_option('aph_ai_temperature', 0.3);
        ?>
        <div class="wrap">
            <h1>🤖 AI Practice Analysis Settings</h1>
            <p>Configure the AI prompt and settings used for generating practice analysis insights.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('ai_settings_action', 'ai_settings_nonce'); ?>
                
                <div class="jph-ai-settings-sections">
                    <!-- AI Prompt Configuration -->
                    <div class="jph-ai-settings-section">
                        <h2>📝 AI Prompt Configuration</h2>
                        <p>Customize the prompt sent to the AI for practice analysis. Use placeholders like {total_sessions}, {total_minutes}, etc.</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="ai_prompt">Analysis Prompt</label>
                                </th>
                                <td>
                                    <textarea id="ai_prompt" name="ai_prompt" rows="12" cols="80" class="large-text code"><?php echo esc_textarea($ai_prompt); ?></textarea>
                                    <p class="description">
                                        <strong>Available placeholders:</strong><br>
                                        <code>{total_sessions}</code> - Number of practice sessions<br>
                                        <code>{total_minutes}</code> - Total practice time in minutes<br>
                                        <code>{avg_duration}</code> - Average session length<br>
                                        <code>{avg_sentiment}</code> - Average mood/sentiment score<br>
                                        <code>{improvement_rate}</code> - Percentage of sessions with improvement<br>
                                        <code>{most_frequent_day}</code> - Most common practice day<br>
                                        <code>{most_practiced_item}</code> - Most practiced item<br>
                                        <code>{current_level}</code> - User's current level<br>
                                        <code>{current_streak}</code> - Current practice streak
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="ai_system_message">System Message</label>
                                </th>
                                <td>
                                    <textarea id="ai_system_message" name="ai_system_message" rows="3" cols="80" class="large-text"><?php echo esc_textarea($ai_system_message); ?></textarea>
                                    <p class="description">The system message that sets the AI's role and behavior.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- AI Model Settings -->
                    <div class="jph-ai-settings-section">
                        <h2>⚙️ AI Model Settings</h2>
                        <p>Configure the AI model parameters for analysis generation.</p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="ai_model">AI Model</label>
                                </th>
                                <td>
                                    <select id="ai_model" name="ai_model">
                                        <option value="gpt-3.5-turbo" <?php selected($ai_model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                                        <option value="gpt-4" <?php selected($ai_model, 'gpt-4'); ?>>GPT-4</option>
                                        <option value="gpt-4-turbo" <?php selected($ai_model, 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                                    </select>
                                    <p class="description">Choose the AI model for generating analysis.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="ai_max_tokens">Max Tokens</label>
                                </th>
                                <td>
                                    <input type="number" id="ai_max_tokens" name="ai_max_tokens" value="<?php echo esc_attr($ai_max_tokens); ?>" min="50" max="1000" class="small-text">
                                    <p class="description">Maximum number of tokens in the AI response (50-1000).</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="ai_temperature">Temperature</label>
                                </th>
                                <td>
                                    <input type="number" id="ai_temperature" name="ai_temperature" value="<?php echo esc_attr($ai_temperature); ?>" min="0" max="2" step="0.1" class="small-text">
                                    <p class="description">Controls randomness (0.0 = deterministic, 2.0 = very creative).</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Test Section -->
                    <div class="jph-ai-settings-section">
                        <h2>🧪 Test AI Analysis</h2>
                        <p>Test the current AI settings with sample data.</p>
                        
                        <div class="test-controls">
                            <div class="test-input-group">
                                <label for="test-user-id">User ID to test:</label>
                                <input type="number" id="test-user-id" name="test_user_id" placeholder="Enter user ID (leave empty for current user)" min="1" style="width: 200px; margin-left: 10px;">
                            </div>
                            <div class="test-buttons">
                                <button type="button" class="button button-primary" onclick="testAIAnalysis()">Test AI Analysis</button>
                                <button type="button" class="button button-secondary" onclick="resetToDefaults()">Reset to Defaults</button>
                            </div>
                        </div>
                        
                        <div id="ai-test-results" class="ai-test-results"></div>
                    </div>
                </div>
                
                <?php submit_button('Save AI Settings'); ?>
            </form>
        </div>
        
        <style>
        .jph-ai-settings-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin: 25px 0;
        }
        
        .jph-ai-settings-section {
            background: #fff;
            border: 1px solid #e1e1e1;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .jph-ai-settings-section h2 {
            margin: 0 0 15px 0;
            color: #1e1e1e;
            font-size: 20px;
            font-weight: 600;
        }
        
        .test-controls {
            margin-bottom: 20px;
        }
        
        .test-input-group {
            margin-bottom: 15px;
        }
        
        .test-input-group label {
            font-weight: 600;
            color: #1e1e1e;
        }
        
        .test-buttons {
            display: flex;
            gap: 10px;
        }
        
        .ai-test-results {
            background: #f8f9fa;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            min-height: 100px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .form-table th {
            width: 200px;
            padding: 20px 10px 20px 0;
            vertical-align: top;
        }
        
        .form-table td {
            padding: 15px 10px;
        }
        
        .large-text.code {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.4;
        }
        </style>
        
        <script>
        function testAIAnalysis() {
            const resultsDiv = document.getElementById('ai-test-results');
            const userIdInput = document.getElementById('test-user-id');
            const userId = userIdInput.value.trim();
            
            resultsDiv.innerHTML = 'Testing AI analysis with current settings...';
            
            jQuery.ajax({
                url: '<?php echo rest_url('aph/v1/ai-analysis'); ?>',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                data: {
                    refresh: 1,
                    user_id: userId || undefined
                },
                success: function(response) {
                    if (response.success) {
                        let html = '<h4>AI Analysis Test Results:</h4>';
                        html += '<p><strong>Tested User ID:</strong> ' + (userId || 'Current User') + '</p>';
                        html += '<p><strong>Generated:</strong> ' + response.data.generated_at + '</p>';
                        html += '<p><strong>Data Period:</strong> ' + response.data.data_period + '</p>';
                        html += '<h5>AI Response:</h5>';
                        html += '<div style="background: #fff; padding: 15px; border-radius: 6px; border-left: 4px solid #007cba;">';
                        html += response.data.analysis;
                        html += '</div>';
                        
                        if (response.data.debug_prompt) {
                            html += '<h5>Prompt Sent to AI:</h5>';
                            html += '<div style="background: #fff; padding: 15px; border-radius: 6px; border-left: 4px solid #28a745; font-family: monospace; font-size: 12px; white-space: pre-wrap;">';
                            html += response.data.debug_prompt;
                            html += '</div>';
                        }
                        
                        // Add comprehensive debug information
                        html += '<h5>🔍 DEBUG INFORMATION:</h5>';
                        html += '<div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border: 1px solid #dee2e6; font-family: monospace; font-size: 11px;">';
                        
                        if (response.data.debug_info) {
                            html += '<strong>System Message:</strong><br>';
                            html += '<div style="background: #fff; padding: 10px; margin: 5px 0; border-radius: 4px; border-left: 3px solid #007cba;">';
                            html += response.data.debug_info.system_message || 'Not available';
                            html += '</div>';
                            
                            html += '<strong>AI Model:</strong> ' + (response.data.debug_info.ai_model || 'Not available') + '<br>';
                            html += '<strong>Temperature:</strong> ' + (response.data.debug_info.temperature || 'Not available') + '<br>';
                            html += '<strong>Max Tokens:</strong> ' + (response.data.debug_info.max_tokens || 'Not available') + '<br>';
                            
                            if (response.data.debug_info.request_data) {
                                html += '<strong>Full Request Data:</strong><br>';
                                html += '<div style="background: #fff; padding: 10px; margin: 5px 0; border-radius: 4px; border-left: 3px solid #28a745; white-space: pre-wrap;">';
                                html += JSON.stringify(response.data.debug_info.request_data, null, 2);
                                html += '</div>';
                            }
                        }
                        
                        html += '</div>';
                        
                        resultsDiv.innerHTML = html;
                    } else {
                        resultsDiv.innerHTML = '<p style="color: red;">Error: ' + response.message + '</p>';
                    }
                },
                error: function() {
                    resultsDiv.innerHTML = '<p style="color: red;">Error testing AI analysis</p>';
                }
            });
        }
        
        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all AI settings to defaults?')) {
                document.getElementById('ai_prompt').value = 'Analyze this piano practice data from the last 30 days and provide insights in 2-3 sentences. Be encouraging and specific:\n\nPractice Sessions: {total_sessions} sessions\nTotal Practice Time: {total_minutes} minutes\nAverage Session Length: {avg_duration} minutes\nAverage Mood/Sentiment: {avg_sentiment}/5 (1=frustrating, 5=excellent)\nImprovement Rate: {improvement_rate}% of sessions showed improvement\nMost Frequent Practice Day: {most_frequent_day}\nMost Practiced Item: {most_practiced_item}\nCurrent Level: {current_level}\nCurrent Streak: {current_streak} days\n\nProvide specific, actionable insights about their practice patterns and suggestions for improvement. Keep it positive and motivating.';
                document.getElementById('ai_system_message').value = 'You are a helpful piano practice coach. Provide encouraging, specific insights about practice patterns.';
                document.getElementById('ai_model').value = 'gpt-3.5-turbo';
                document.getElementById('ai_max_tokens').value = '300';
                document.getElementById('ai_temperature').value = '0.7';
            }
        }
        </script>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // Handle form submission
        if (isset($_POST['jph_settings_submit']) && wp_verify_nonce($_POST['jph_settings_nonce'], 'jph_settings')) {
            $practice_hub_page_id = intval($_POST['jph_practice_hub_page_id']);
            update_option('jph_practice_hub_page_id', $practice_hub_page_id);
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $current_page_id = get_option('jph_practice_hub_page_id', '');
        ?>
        <div class="wrap">
            <h1>⚙️ Practice Hub Settings</h1>
            
            <div class="jph-settings-sections">
                <!-- Practice Hub Page Settings -->
                <div class="jph-settings-section jph-page-settings">
                    <h2>🎯 Practice Hub Page Settings</h2>
                    <p>Configure which page contains your practice hub dashboard.</p>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('jph_settings', 'jph_settings_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="jph_practice_hub_page_id">Practice Hub Page</label>
                                </th>
                                <td>
                                    <?php
                                    $pages = get_pages(array(
                                        'post_status' => 'publish',
                                        'sort_column' => 'post_title',
                                        'sort_order' => 'ASC'
                                    ));
                                    ?>
                                    <select name="jph_practice_hub_page_id" id="jph_practice_hub_page_id" style="width: 300px;">
                                        <option value="">-- Select Practice Hub Page --</option>
                                        <?php foreach ($pages as $page): ?>
                                            <option value="<?php echo $page->ID; ?>" <?php selected($current_page_id, $page->ID); ?>>
                                                <?php echo esc_html($page->post_title); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">
                                        Select the page that contains your practice hub dashboard (with the <code>[jph_dashboard]</code> shortcode).
                                        This page will be used for the "Go to Practice Hub" links in widgets.
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button('Save Settings', 'primary', 'jph_settings_submit'); ?>
                    </form>
                    
                    <?php if ($current_page_id): ?>
                        <div class="jph-current-page-info">
                            <h3>📄 Current Practice Hub Page</h3>
                            <?php
                            $current_page = get_post($current_page_id);
                            if ($current_page): ?>
                                <p><strong>Page:</strong> <?php echo esc_html($current_page->post_title); ?></p>
                                <p><strong>URL:</strong> <a href="<?php echo esc_url(get_permalink($current_page_id)); ?>" target="_blank"><?php echo esc_url(get_permalink($current_page_id)); ?></a></p>
                                <p><strong>Status:</strong> <?php echo ucfirst($current_page->post_status); ?></p>
                            <?php else: ?>
                                <p class="jph-error">⚠️ Selected page not found. Please select a valid page.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Data Backup & Restore Section -->
                <div class="jph-settings-section jph-backup-section">
                    <h2>💾 Data Backup & Restore</h2>
                    <p><strong>SAFETY TOOL:</strong> Export all user data for backup before making changes to the plugin.</p>
                    
                    <div class="backup-actions">
                        <div class="backup-export">
                            <h3>📤 Export User Data</h3>
                            <p>Create a backup file containing all user data:</p>
                            <ul>
                                <li>📝 All practice sessions and items</li>
                                <li>👥 All user statistics (XP, levels, streaks)</li>
                                <li>🎖️ All earned badges (user badges)</li>
                                <li>💎 All gem transactions and balances</li>
                                <li>❤️ All lesson favorites</li>
                            </ul>
                            
                            <button type="button" class="button button-primary jph-export-btn" onclick="exportUserData()">
                                📥 Export User Data
                            </button>
                        </div>
                        
                        <div class="backup-import">
                            <h3>📥 Restore User Data</h3>
                            <p>Restore user data from a previously exported backup file:</p>
                            
                            <div class="import-controls">
                                <input type="file" id="backup-file" accept=".json" style="margin-bottom: 10px;">
                                <br>
                                <button type="button" class="button button-secondary jph-import-btn" onclick="importUserData()">
                                    📤 Restore from Backup
                                </button>
                            </div>
                            
                            <p class="import-warning"><strong>⚠️ Warning:</strong> This will replace all existing user data with the backup data.</p>
                        </div>
                    </div>
                    
                    <div id="backup-results" class="backup-results"></div>
                </div>
                
                <!-- Test Data Generation Section -->
                <div class="jph-settings-section jph-test-section">
                    <h2>🧪 Test Data Generation</h2>
                    <p><strong>TESTING TOOL:</strong> Generate realistic test data for leaderboard testing.</p>
                    
                    <div class="test-data-section">
                        <h3>👥 Generate Test Students</h3>
                        <p>This will create 50 test students with realistic practice data:</p>
                        <ul>
                            <li>📝 Random practice sessions (1-100 per student)</li>
                            <li>🎯 Various practice items (songs, exercises, scales)</li>
                            <li>👥 Realistic XP progression and levels</li>
                            <li>🔥 Random streaks (0-30 days)</li>
                            <li>🎖️ Random badge awards</li>
                            <li>💎 Random gem balances</li>
                            <li>📊 Varied practice durations and sentiments</li>
                        </ul>
                        <p><strong>Note:</strong> This will NOT affect existing real user data.</p>
                        
                        <button type="button" class="button button-primary jph-generate-test-btn" onclick="generateTestStudents()">
                            🎭 Generate 50 Test Students
                        </button>
                        
                        <button type="button" class="button button-secondary jph-clear-test-btn" onclick="clearTestData()" style="margin-left: 10px;">
                            🗑️ Clear Test Data
                        </button>
                        
                        <button type="button" class="button button-secondary jph-clear-cache-btn" onclick="clearCache()" style="margin-left: 10px;">
                            🔄 Clear Cache
                        </button>
                        <button type="button" class="button button-secondary" onclick="updateBadgesSchema()" style="margin-left: 10px;">
                            🏆 Update Badges Schema
                        </button>
                    </div>
                    
                    <div id="test-results" class="test-results"></div>
                </div>
                
                <div class="jph-settings-section jph-danger-section">
                    <h2>🧪 DATA MANAGEMENT FOR TESTING</h2>
                    <p><strong>DEVELOPMENT/TESTING TOOL:</strong> This will permanently delete ALL user data and cannot be undone!</p>
                    
                    <div class="clear-all-section">
                        <p>This action will clear:</p>
                        <ul>
                            <li>📝 All practice sessions</li>
                            <li>🎯 All practice items (custom items created by users)</li>
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
        }
        
        .jph-backup-section {
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
        }
        
        .jph-backup-section h2 {
            color: #28a745;
            margin-top: 0;
        }
        
        .jph-test-section {
            border: 2px solid #0073aa;
            border-radius: 8px;
            padding: 20px;
        }
        
        .jph-test-section h2 {
            color: #0073aa;
            margin-top: 0;
        }
        
        .backup-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .backup-export, .backup-import {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #f9f9f9;
        }
        
        .backup-export h3, .backup-import h3 {
            margin-top: 0;
            color: #333;
        }
        
        .backup-export ul, .backup-import ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .import-warning {
            color: #d63384;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .jph-export-btn, .jph-import-btn {
            margin-top: 15px;
        }
        
        .backup-results {
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
            display: none;
        }
        
        .backup-results.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .backup-results.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
        
        .test-results {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            min-height: 50px;
        }
        
        .test-results.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .test-results.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
        function exportUserData() {
            jQuery('#backup-results').html('<p>Exporting user data...</p>').show().removeClass('success error');
            
            jQuery.ajax({
                url: '<?php echo rest_url('aph/v1/admin/export-user-data'); ?>',
                method: 'GET',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Create download link
                        const dataStr = JSON.stringify(response.data, null, 2);
                        const dataBlob = new Blob([dataStr], {type: 'application/json'});
                        const url = URL.createObjectURL(dataBlob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'practice-hub-backup-' + new Date().toISOString().split('T')[0] + '.json';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(url);
                        
                        jQuery('#backup-results').html('<p>✅ User data exported successfully! Download started automatically.</p>').addClass('success');
                    } else {
                        jQuery('#backup-results').html('<p>❌ Export failed: ' + response.message + '</p>').addClass('error');
                    }
                },
                error: function() {
                    jQuery('#backup-results').html('<p>❌ Export failed: Network error</p>').addClass('error');
                }
            });
        }
        
        function importUserData() {
            const fileInput = document.getElementById('backup-file');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a backup file to import.');
                return;
            }
            
            if (!confirm('⚠️ WARNING: This will replace ALL existing user data with the backup data.\n\nThis action cannot be undone!\n\nAre you sure you want to continue?')) {
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const backupData = JSON.parse(e.target.result);
                    
                    jQuery('#backup-results').html('<p>Importing user data...</p>').show().removeClass('success error');
                    
                    jQuery.ajax({
                        url: '<?php echo rest_url('aph/v1/admin/import-user-data'); ?>',
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        data: {
                            backup_data: JSON.stringify(backupData)
                        },
                        success: function(response) {
                            if (response.success) {
                                jQuery('#backup-results').html('<p>✅ User data imported successfully! ' + response.message + '</p>').addClass('success');
                            } else {
                                jQuery('#backup-results').html('<p>❌ Import failed: ' + response.message + '</p>').addClass('error');
                            }
                        },
                        error: function() {
                            jQuery('#backup-results').html('<p>❌ Import failed: Network error</p>').addClass('error');
                        }
                    });
                } catch (error) {
                    jQuery('#backup-results').html('<p>❌ Invalid backup file format</p>').addClass('error');
                }
            };
            reader.readAsText(file);
        }
        
        function generateTestStudents() {
            jQuery('#test-results').html('<p>🎭 Generating 50 test students with realistic data...</p>').show().removeClass('success error');
            
            jQuery.ajax({
                url: '<?php echo rest_url('aph/v1/admin/generate-test-students'); ?>',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        jQuery('#test-results').html(
                            '<p>✅ Test data generated successfully!</p>' +
                            '<ul>' +
                            '<li>👥 ' + response.data.students_created + ' test students created</li>' +
                            '<li>📝 ' + response.data.sessions_created + ' practice sessions generated</li>' +
                            '<li>🎯 ' + response.data.items_created + ' practice items created</li>' +
                            '<li>🎖️ ' + response.data.badges_awarded + ' badges awarded</li>' +
                            '</ul>' +
                            '<p><strong>You can now test the leaderboard with realistic data!</strong></p>'
                        ).addClass('success');
                    } else {
                        jQuery('#test-results').html('<p>❌ Generation failed: ' + response.message + '</p>').addClass('error');
                    }
                },
                error: function() {
                    jQuery('#test-results').html('<p>❌ Generation failed: Network error</p>').addClass('error');
                }
            });
        }
        
        function clearTestData() {
            if (confirm('Are you sure you want to clear all test data? This will delete all test students and their associated data. This action cannot be undone.')) {
                jQuery('#test-results').html('<p>🗑️ Clearing test data...</p>').show().removeClass('success error');
                
                jQuery.ajax({
                    url: '<?php echo rest_url('aph/v1/admin/clear-test-data'); ?>',
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery('#test-results').html(
                                '<p>✅ Test data cleared successfully!</p>' +
                                '<ul>' +
                                '<li>👥 ' + response.data.users_deleted + ' test users deleted</li>' +
                                '<li>📝 ' + response.data.sessions_deleted + ' practice sessions deleted</li>' +
                                '<li>🎯 ' + response.data.items_deleted + ' practice items deleted</li>' +
                                '<li>🎖️ ' + response.data.badges_deleted + ' badges deleted</li>' +
                                '</ul>' +
                                '<p><strong>All test data has been removed.</strong></p>'
                            ).addClass('success');
                        } else {
                            jQuery('#test-results').html('<p>❌ Clear failed: ' + response.message + '</p>').addClass('error');
                        }
                    },
                    error: function() {
                        jQuery('#test-results').html('<p>❌ Clear failed: Network error</p>').addClass('error');
                    }
                });
            }
        }
        
        function confirmClearAllUserData() {
            if (confirm('⚠️ DANGER: This will permanently delete ALL user data including:\n\n• All practice sessions\n• All practice items (custom items created by users)\n• All user statistics (XP, levels, streaks)\n• All earned badges\n• All gem transactions and balances\n• All lesson favorites\n\nThis action CANNOT be undone!\n\nAre you absolutely sure you want to continue?')) {
                clearAllUserData();
            }
        }
        
        function clearAllUserData() {
            jQuery('#danger-results').html('<p>Clearing all user data...</p>');
            
            jQuery.ajax({
                url: '<?php echo rest_url('aph/v1/admin/clear-all-user-data'); ?>',
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
    
    /**
     * Widgets documentation page
     */
    public function widgets_page() {
        ?>
        <div class="wrap">
            <h1>📊 Practice Hub Widgets</h1>
            
            <div class="jph-widgets-documentation">
                <!-- Practice Stats Widget -->
                <div class="jph-widget-section">
                    <h2>📈 Practice Stats Widget</h2>
                    <p>Display user practice statistics in a customizable format.</p>
                    
                    <div class="jph-widget-demo">
                        <h3>🎯 Shortcode Usage</h3>
                        <div class="jph-code-block">
                            <code>[jph_stats_widget]</code>
                        </div>
                    </div>
                    
                    <div class="jph-widget-attributes">
                        <h3>⚙️ Attributes</h3>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>user_id</code></td>
                                    <td><code>current</code></td>
                                    <td>User ID to display stats for (<code>current</code> for logged-in user or specific user ID)</td>
                                </tr>
                                <tr>
                                    <td><code>show</code></td>
                                    <td><code>xp,level,streak,badges</code></td>
                                    <td>Comma-separated list of stats to display</td>
                                </tr>
                                <tr>
                                    <td><code>style</code></td>
                                    <td><code>compact</code></td>
                                    <td>Widget style (<code>compact</code> or <code>detailed</code>)</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td><code>Practice Stats</code></td>
                                    <td>Widget title</td>
                                </tr>
                                <tr>
                                    <td><code>show_title</code></td>
                                    <td><code>true</code></td>
                                    <td>Show/hide widget title</td>
                                </tr>
                                <tr>
                                    <td><code>show_practice_hub_link</code></td>
                                    <td><code>false</code></td>
                                    <td>Show/hide "Go to Practice Hub" link</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="jph-widget-stats">
                        <h3>📊 Available Stats</h3>
                        <div class="jph-stats-grid">
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">⭐</span>
                                <span class="jph-stat-label">XP</span>
                                <span class="jph-stat-desc">Total XP earned</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">🏆</span>
                                <span class="jph-stat-label">Level</span>
                                <span class="jph-stat-desc">Current level</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">🔥</span>
                                <span class="jph-stat-label">Streak</span>
                                <span class="jph-stat-desc">Current practice streak</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">🎖️</span>
                                <span class="jph-stat-label">Badges</span>
                                <span class="jph-stat-desc">Number of badges earned</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">📝</span>
                                <span class="jph-stat-label">Sessions</span>
                                <span class="jph-stat-desc">Total practice sessions</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">⏱️</span>
                                <span class="jph-stat-label">Minutes</span>
                                <span class="jph-stat-desc">Total practice minutes</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">💎</span>
                                <span class="jph-stat-label">Gems</span>
                                <span class="jph-stat-desc">Gem balance</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">❤️</span>
                                <span class="jph-stat-label">Hearts</span>
                                <span class="jph-stat-desc">Hearts count</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="jph-widget-examples">
                        <h3>💡 Examples</h3>
                        
                        <div class="jph-example">
                            <h4>Basic Usage</h4>
                            <div class="jph-code-block">
                                <code id="example-basic">[jph_stats_widget]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('example-basic')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>Custom Stats Selection</h4>
                            <div class="jph-code-block">
                                <code id="example-custom">[jph_stats_widget show="xp,level,streak"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('example-custom')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>Detailed Style</h4>
                            <div class="jph-code-block">
                                <code id="example-detailed">[jph_stats_widget style="detailed" title="My Progress"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('example-detailed')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>Specific User</h4>
                            <div class="jph-code-block">
                                <code id="example-user">[jph_stats_widget user_id="123" title="Student Progress"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('example-user')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>Minimal Widget</h4>
                            <div class="jph-code-block">
                                <code id="example-minimal">[jph_stats_widget show="xp,level" show_title="false"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('example-minimal')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>With Practice Hub Link</h4>
                            <div class="jph-code-block">
                                <code id="example-hub-link">[jph_stats_widget show_practice_hub_link="true"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('example-hub-link')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="jph-widget-features">
                        <h3>✨ Features</h3>
                        <ul>
                            <li>🎨 <strong>Customizable Stats:</strong> Choose which stats to display</li>
                            <li>📱 <strong>Responsive Design:</strong> Adapts to different screen sizes</li>
                            <li>👤 <strong>User Targeting:</strong> Show stats for current user or specific user</li>
                            <li>🎯 <strong>Two Styles:</strong> Compact and detailed layouts</li>
                            <li>🔒 <strong>Secure:</strong> All output is sanitized to prevent XSS</li>
                            <li>⚡ <strong>Performance:</strong> Efficient database queries</li>
                        </ul>
                    </div>
                    
                    <div class="jph-widget-styling">
                        <h3>🎨 Customization</h3>
                        <p>You can customize the widget appearance by adding CSS to your theme:</p>
                        <div class="jph-code-block">
                            <code>
.jph-stats-widget {<br>
&nbsp;&nbsp;/* Custom widget container styles */<br>
}<br><br>
.jph-stat-item {<br>
&nbsp;&nbsp;/* Custom stat item styles */<br>
}<br><br>
.jph-stat-value {<br>
&nbsp;&nbsp;/* Custom value styles */<br>
}<br><br>
.jph-stat-label {<br>
&nbsp;&nbsp;/* Custom label styles */<br>
}
                            </code>
                        </div>
                    </div>
                    
                    <div class="jph-widget-troubleshooting">
                        <h3>🔧 Troubleshooting</h3>
                        <div class="jph-troubleshooting-item">
                            <h4>Widget not displaying:</h4>
                            <ul>
                                <li>Check if user is logged in (for <code>user_id="current"</code>)</li>
                                <li>Verify user ID exists (for specific user IDs)</li>
                                <li>Ensure valid stats are selected in <code>show</code> attribute</li>
                            </ul>
                        </div>
                        <div class="jph-troubleshooting-item">
                            <h4>Styling issues:</h4>
                            <ul>
                                <li>Check for CSS conflicts with theme</li>
                                <li>Verify widget CSS is loading properly</li>
                                <li>Test responsive behavior on different screen sizes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Practice Widget -->
                <div class="jph-widget-section">
                    <h2>📋 Recent Practice Widget</h2>
                    <p>Display recent practice sessions with customizable details.</p>
                    
                    <div class="jph-widget-demo">
                        <h3>🎯 Shortcode Usage</h3>
                        <div class="jph-code-block">
                            <code id="recent-basic">[jph_recent_practice_widget]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('recent-basic')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="jph-widget-attributes">
                        <h3>⚙️ Attributes</h3>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>user_id</code></td>
                                    <td><code>current</code></td>
                                    <td>User ID to display sessions for (<code>current</code> for logged-in user or specific user ID)</td>
                                </tr>
                                <tr>
                                    <td><code>limit</code></td>
                                    <td><code>5</code></td>
                                    <td>Number of recent sessions to display (1-20)</td>
                                </tr>
                                <tr>
                                    <td><code>show</code></td>
                                    <td><code>date,duration,items,sentiment</code></td>
                                    <td>Comma-separated list of fields to display</td>
                                </tr>
                                <tr>
                                    <td><code>style</code></td>
                                    <td><code>compact</code></td>
                                    <td>Widget style (<code>compact</code> or <code>detailed</code>)</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td><code>Recent Practice</code></td>
                                    <td>Widget title</td>
                                </tr>
                                <tr>
                                    <td><code>show_title</code></td>
                                    <td><code>true</code></td>
                                    <td>Show/hide widget title</td>
                                </tr>
                                <tr>
                                    <td><code>date_format</code></td>
                                    <td><code>relative</code></td>
                                    <td>Date format (<code>relative</code> or <code>absolute</code>)</td>
                                </tr>
                                <tr>
                                    <td><code>show_practice_hub_link</code></td>
                                    <td><code>false</code></td>
                                    <td>Show/hide "Go to Practice Hub" link</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="jph-widget-stats">
                        <h3>📊 Available Fields</h3>
                        <div class="jph-stats-grid">
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">📅</span>
                                <span class="jph-stat-label">Date</span>
                                <span class="jph-stat-desc">Session date (relative or absolute)</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">⏱️</span>
                                <span class="jph-stat-label">Duration</span>
                                <span class="jph-stat-desc">Practice duration in minutes</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">🎯</span>
                                <span class="jph-stat-label">Items</span>
                                <span class="jph-stat-desc">Practice item name</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">😊</span>
                                <span class="jph-stat-label">Sentiment</span>
                                <span class="jph-stat-desc">How the practice felt</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">📝</span>
                                <span class="jph-stat-label">Notes</span>
                                <span class="jph-stat-desc">Practice session notes</span>
                            </div>
                            <div class="jph-stat-item">
                                <span class="jph-stat-icon">⭐</span>
                                <span class="jph-stat-label">XP</span>
                                <span class="jph-stat-desc">XP earned from session</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="jph-widget-examples">
                        <h3>💡 Examples</h3>
                        
                        <div class="jph-example">
                            <h4>Basic Usage</h4>
                            <div class="jph-code-block">
                                <code id="recent-basic-ex">[jph_recent_practice_widget]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('recent-basic-ex')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>Custom Fields</h4>
                            <div class="jph-code-block">
                                <code id="recent-custom">[jph_recent_practice_widget show="date,duration,sentiment" limit="3"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('recent-custom')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>Detailed Style</h4>
                            <div class="jph-code-block">
                                <code id="recent-detailed">[jph_recent_practice_widget style="detailed" show="date,duration,items,sentiment,notes,xp"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('recent-detailed')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>Specific User</h4>
                            <div class="jph-code-block">
                                <code id="recent-user">[jph_recent_practice_widget user_id="123" title="Student's Recent Practice"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('recent-user')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>Absolute Dates</h4>
                            <div class="jph-code-block">
                                <code id="recent-absolute">[jph_recent_practice_widget date_format="absolute" show="date,duration,items"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('recent-absolute')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="jph-example">
                            <h4>With Practice Hub Link</h4>
                            <div class="jph-code-block">
                                <code id="recent-hub-link">[jph_recent_practice_widget show_practice_hub_link="true"]</code>
                                <button type="button" class="jph-copy-btn" onclick="copyToClipboard('recent-hub-link')" title="Copy to clipboard">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="jph-widget-features">
                        <h3>✨ Features</h3>
                        <ul>
                            <li>📋 <strong>Recent Sessions:</strong> Show latest practice sessions</li>
                            <li>🎨 <strong>Customizable Fields:</strong> Choose which details to display</li>
                            <li>📱 <strong>Responsive Design:</strong> Adapts to different screen sizes</li>
                            <li>👤 <strong>User Targeting:</strong> Show sessions for current user or specific user</li>
                            <li>🎯 <strong>Two Styles:</strong> Compact and detailed layouts</li>
                            <li>📅 <strong>Date Formats:</strong> Relative (e.g., "2 hours ago") or absolute (e.g., "Oct 9, 2024")</li>
                            <li>😊 <strong>Sentiment Display:</strong> Visual sentiment indicators with emojis</li>
                            <li>🔒 <strong>Secure:</strong> All output is sanitized to prevent XSS</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Leaderboard Widget -->
                <div class="jph-widget-section">
                    <h2>🏆 Leaderboard Widget</h2>
                    <p>Display a leaderboard of top performers with customizable columns and sorting.</p>
                    
                    <div class="jph-widget-demo">
                        <h3>🎯 Shortcode Usage</h3>
                        <div class="jph-code-block">
                            <code id="leaderboard-basic">[jph_leaderboard_widget]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('leaderboard-basic')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                        
                        <div class="jph-code-block">
                            <code id="leaderboard-custom">[jph_leaderboard_widget limit="15" sort_by="current_level" show="rank,name,level,streak" title="Top Performers"]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('leaderboard-custom')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                        
                        <div class="jph-code-block">
                            <code id="leaderboard-with-link">[jph_leaderboard_widget show_practice_hub_link="true" highlight_user="true"]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('leaderboard-with-link')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="jph-widget-attributes">
                        <h3>📝 Available Attributes</h3>
                        <table class="jph-attributes-table">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>limit</code></td>
                                    <td>10</td>
                                    <td>Number of users to display (1-50)</td>
                                </tr>
                                <tr>
                                    <td><code>sort_by</code></td>
                                    <td>total_xp</td>
                                    <td>Sort by: total_xp, current_level, current_streak, badges_earned</td>
                                </tr>
                                <tr>
                                    <td><code>sort_order</code></td>
                                    <td>desc</td>
                                    <td>Sort order: asc, desc</td>
                                </tr>
                                <tr>
                                    <td><code>show</code></td>
                                    <td>rank,name,xp,level</td>
                                    <td>Columns to display: rank, name, xp, level, streak, badges</td>
                                </tr>
                                <tr>
                                    <td><code>style</code></td>
                                    <td>compact</td>
                                    <td>Widget style: compact, detailed</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td>Leaderboard</td>
                                    <td>Widget title</td>
                                </tr>
                                <tr>
                                    <td><code>show_title</code></td>
                                    <td>true</td>
                                    <td>Show widget title: true, false</td>
                                </tr>
                                <tr>
                                    <td><code>highlight_user</code></td>
                                    <td>true</td>
                                    <td>Highlight current user: true, false</td>
                                </tr>
                                <tr>
                                    <td><code>show_practice_hub_link</code></td>
                                    <td>false</td>
                                    <td>Show "Go to Practice Hub" link: true, false</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="jph-widget-features">
                        <h3>✨ Features</h3>
                        <ul>
                            <li>🏅 <strong>Medal Icons:</strong> Gold, silver, bronze for top 3</li>
                            <li>👤 <strong>User Highlighting:</strong> Highlight current user with "You" badge</li>
                            <li>📱 <strong>Responsive Design:</strong> Mobile-friendly layout</li>
                            <li>🎨 <strong>Customizable Style:</strong> Compact or detailed view</li>
                            <li>🔗 <strong>Practice Hub Link:</strong> Optional link to main hub</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Progress Chart Widget -->
                <div class="jph-widget-section">
                    <h2>📊 Progress Chart Widget</h2>
                    <p>Display visual progress charts for user statistics.</p>
                    
                    <div class="jph-widget-demo">
                        <h3>🎯 Shortcode Usage</h3>
                        <div class="jph-code-block">
                            <code id="progress-chart-basic">[jph_progress_chart_widget]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('progress-chart-basic')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                        
                        <div class="jph-code-block">
                            <code id="progress-chart-custom">[jph_progress_chart_widget chart_type="level" period="60" height="400" title="Level Progress"]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('progress-chart-custom')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="jph-widget-attributes">
                        <h3>📝 Available Attributes</h3>
                        <table class="jph-attributes-table">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>user_id</code></td>
                                    <td>current</td>
                                    <td>User ID or 'current' for logged-in user</td>
                                </tr>
                                <tr>
                                    <td><code>chart_type</code></td>
                                    <td>xp</td>
                                    <td>Chart type: xp, level, streak, sessions</td>
                                </tr>
                                <tr>
                                    <td><code>period</code></td>
                                    <td>30</td>
                                    <td>Number of days to display (7-365)</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td>Progress Chart</td>
                                    <td>Widget title</td>
                                </tr>
                                <tr>
                                    <td><code>show_title</code></td>
                                    <td>true</td>
                                    <td>Show widget title: true, false</td>
                                </tr>
                                <tr>
                                    <td><code>height</code></td>
                                    <td>300</td>
                                    <td>Chart height in pixels</td>
                                </tr>
                                <tr>
                                    <td><code>show_practice_hub_link</code></td>
                                    <td>false</td>
                                    <td>Show "Go to Practice Hub" link: true, false</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="jph-widget-features">
                        <h3>✨ Features</h3>
                        <ul>
                            <li>📈 <strong>Interactive Charts:</strong> Powered by Chart.js</li>
                            <li>📊 <strong>Multiple Types:</strong> XP, Level, Streak, Sessions</li>
                            <li>📅 <strong>Customizable Period:</strong> 7-365 days</li>
                            <li>📱 <strong>Responsive Design:</strong> Mobile-friendly</li>
                            <li>🎨 <strong>Customizable Height:</strong> Adjustable chart size</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Badges Widget -->
                <div class="jph-widget-section">
                    <h2>🎖️ Badges Widget</h2>
                    <p>Display earned badges with customizable layouts.</p>
                    
                    <div class="jph-widget-demo">
                        <h3>🎯 Shortcode Usage</h3>
                        <div class="jph-code-block">
                            <code id="badges-basic">[jph_badges_widget]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('badges-basic')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                        
                        <div class="jph-code-block">
                            <code id="badges-list">[jph_badges_widget layout="list" limit="10" title="My Achievements"]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('badges-list')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="jph-widget-attributes">
                        <h3>📝 Available Attributes</h3>
                        <table class="jph-attributes-table">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>user_id</code></td>
                                    <td>current</td>
                                    <td>User ID or 'current' for logged-in user</td>
                                </tr>
                                <tr>
                                    <td><code>limit</code></td>
                                    <td>6</td>
                                    <td>Number of badges to display (1-20)</td>
                                </tr>
                                <tr>
                                    <td><code>layout</code></td>
                                    <td>grid</td>
                                    <td>Layout style: grid, list</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td>Earned Badges</td>
                                    <td>Widget title</td>
                                </tr>
                                <tr>
                                    <td><code>show_title</code></td>
                                    <td>true</td>
                                    <td>Show widget title: true, false</td>
                                </tr>
                                <tr>
                                    <td><code>show_practice_hub_link</code></td>
                                    <td>false</td>
                                    <td>Show "Go to Practice Hub" link: true, false</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="jph-widget-features">
                        <h3>✨ Features</h3>
                        <ul>
                            <li>🏅 <strong>Badge Display:</strong> Shows earned badges with icons</li>
                            <li>📅 <strong>Earned Dates:</strong> Shows when badges were earned</li>
                            <li>🎨 <strong>Layout Options:</strong> Grid or list layout</li>
                            <li>💡 <strong>Tooltips:</strong> Hover for badge descriptions</li>
                            <li>📱 <strong>Responsive Design:</strong> Mobile-friendly</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Gems Widget -->
                <div class="jph-widget-section">
                    <h2>💎 Gems Widget</h2>
                    <p>Display gem balance and transaction history.</p>
                    
                    <div class="jph-widget-demo">
                        <h3>🎯 Shortcode Usage</h3>
                        <div class="jph-code-block">
                            <code id="gems-basic">[jph_gems_widget]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('gems-basic')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                        
                        <div class="jph-code-block">
                            <code id="gems-no-transactions">[jph_gems_widget show_transactions="false" title="Gem Balance"]</code>
                            <button type="button" class="jph-copy-btn" onclick="copyToClipboard('gems-no-transactions')" title="Copy to clipboard">
                                📋 Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="jph-widget-attributes">
                        <h3>📝 Available Attributes</h3>
                        <table class="jph-attributes-table">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Default</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>user_id</code></td>
                                    <td>current</td>
                                    <td>User ID or 'current' for logged-in user</td>
                                </tr>
                                <tr>
                                    <td><code>show_transactions</code></td>
                                    <td>true</td>
                                    <td>Show transaction history: true, false</td>
                                </tr>
                                <tr>
                                    <td><code>limit</code></td>
                                    <td>5</td>
                                    <td>Number of transactions to show (1-10)</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td>Gems Balance</td>
                                    <td>Widget title</td>
                                </tr>
                                <tr>
                                    <td><code>show_title</code></td>
                                    <td>true</td>
                                    <td>Show widget title: true, false</td>
                                </tr>
                                <tr>
                                    <td><code>show_practice_hub_link</code></td>
                                    <td>false</td>
                                    <td>Show "Go to Practice Hub" link: true, false</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="jph-widget-features">
                        <h3>✨ Features</h3>
                        <ul>
                            <li>💎 <strong>Balance Display:</strong> Shows current gem balance</li>
                            <li>📊 <strong>Transaction History:</strong> Recent earned/spent gems</li>
                            <li>🎨 <strong>Gradient Design:</strong> Beautiful gem-themed styling</li>
                            <li>📱 <strong>Responsive Design:</strong> Mobile-friendly</li>
                            <li>⚡ <strong>Real-time Data:</strong> Shows actual user gem balance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .jph-widgets-documentation {
            max-width: 1200px;
        }
        
        .jph-widget-section {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-widget-section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        
        .jph-code-block {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .jph-code-block code {
            background: none;
            padding: 0;
            color: #333;
            flex: 1;
            margin-right: 10px;
        }
        
        .jph-copy-btn {
            background: #0073aa;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .jph-copy-btn:hover {
            background: #005a87;
            transform: translateY(-1px);
        }
        
        .jph-copy-btn:active {
            transform: translateY(0);
        }
        
        .jph-copy-btn.copied {
            background: #28a745;
        }
        
        .jph-copy-btn.copied::after {
            content: " ✓";
        }
        
        .jph-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        
        .jph-stat-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #0073aa;
        }
        
        .jph-stat-icon {
            font-size: 20px;
            margin-right: 10px;
            min-width: 20px;
        }
        
        .jph-stat-label {
            font-weight: 600;
            margin-right: 8px;
            min-width: 60px;
        }
        
        .jph-stat-desc {
            color: #666;
            font-size: 13px;
        }
        
        .jph-example {
            margin: 15px 0;
        }
        
        .jph-example h4 {
            margin: 10px 0 5px 0;
            color: #333;
        }
        
        .jph-widget-features ul,
        .jph-troubleshooting-item ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .jph-widget-features li,
        .jph-troubleshooting-item li {
            margin: 5px 0;
        }
        
        .jph-future-widgets {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-color: #0073aa;
        }
        
        .jph-future-widgets h2 {
            color: #0073aa;
        }
        
        .jph-troubleshooting-item {
            margin: 20px 0;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
        }
        
        .jph-troubleshooting-item h4 {
            margin-top: 0;
            color: #856404;
        }
        
        table.widefat {
            margin: 15px 0;
        }
        
        table.widefat th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        table.widefat code {
            background: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .jph-page-settings {
            background: #fff;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .jph-page-settings h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        
        .jph-current-page-info {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .jph-current-page-info h3 {
            margin-top: 0;
            color: #333;
        }
        
        .jph-current-page-info p {
            margin: 8px 0;
        }
        
        .jph-current-page-info a {
            color: #0073aa;
            text-decoration: none;
        }
        
        .jph-current-page-info a:hover {
            text-decoration: underline;
        }
        
        .jph-error {
            color: #d63384;
            font-weight: 500;
        }
        </style>
        
        <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            const button = element.nextElementSibling;
            
            // Create a temporary textarea element
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            
            // Select and copy the text
            textarea.select();
            textarea.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                
                // Visual feedback
                button.classList.add('copied');
                button.textContent = 'Copied ✓';
                
                // Reset after 2 seconds
                setTimeout(() => {
                    button.classList.remove('copied');
                    button.textContent = '📋 Copy';
                }, 2000);
                
            } catch (err) {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy to clipboard');
            }
            
            // Clean up
            document.body.removeChild(textarea);
        }
        </script>
        <?php
    }
}
