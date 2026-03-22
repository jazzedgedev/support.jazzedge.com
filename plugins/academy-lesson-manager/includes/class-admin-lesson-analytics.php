<?php
/**
 * Lesson Analytics Admin Class
 *
 * Provides reporting for lesson viewing analytics
 *
 * @package Academy_Lesson_Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Lesson_Analytics {
    /**
     * WordPress database instance
     */
    private $wpdb;

    /**
     * Database helper
     */
    private $database;

    /**
     * Analytics table name
     */
    private $table_name = 'academy_recently_viewed';

    /**
     * Lessons table name
     */
    private $lessons_table_name = '';

    /**
     * Collections table name
     */
    private $collections_table_name = '';

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        $this->lessons_table_name = $this->database->get_table_name('lessons');
        $this->collections_table_name = $this->database->get_table_name('collections');
    }

    /**
     * Render the lesson analytics admin page
     */
    public function render_page() {
        $table_exists = $this->wpdb->get_var(
            $this->wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name)
        ) == $this->table_name;

        echo '<div class="wrap">';
        $this->render_navigation_buttons('analytics');
        echo '<h1>' . __('Lesson Analytics', 'academy-lesson-manager') . '</h1>';

        if (!$table_exists) {
            echo '<div class="notice notice-error"><p><strong>Error:</strong> The lesson analytics table does not exist. Please contact support.</p></div>';
            echo '</div>';
            return;
        }

        $available_types = $this->get_available_types();
        $available_lessons = $this->get_available_lessons();
        $filters = $this->get_filters($available_types, $available_lessons);
        list($where_sql, $params) = $this->build_where_clause($filters);

        $summary = $this->get_summary($where_sql, $params);
        $lesson_stats = $this->get_lesson_performance($where_sql, $params);
        $ai_insights = $this->build_ai_insights($lesson_stats, $filters);
        $views_series = $this->get_views_over_time($filters);
        $top_lessons = $this->get_top_lessons($where_sql, $params);
        $top_users = $this->get_top_users($where_sql, $params);
        $recent_views = $this->get_recent_views($where_sql, $params);
        $unviewed_lessons = $this->get_unviewed_lessons($filters);

        $this->render_filters($filters, $available_types, $available_lessons);
        $this->render_summary($summary, $filters);
        $this->render_ai_insights($ai_insights);
        $this->render_views_chart($views_series, $filters);
        $this->render_analytics_accordion_assets();
        $this->render_top_lessons($top_lessons);
        $this->render_top_users($top_users);
        $this->render_recent_views($recent_views);
        $this->render_unviewed_lessons($unviewed_lessons, $filters);
        $this->render_student_list_modal();
        $this->render_last_webhook_panel();

        echo '</div>';
    }

    /**
     * Render accordion assets for analytics sections
     */
    private function render_analytics_accordion_assets() {
        ?>
        <style>
        .alm-accordion {
            margin-top: 25px;
            border: 1px solid #ccd0d4;
            background: #fff;
        }
        .alm-accordion__toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border: none;
            background: #f6f7f7;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-align: left;
        }
        .alm-accordion__toggle:hover {
            background: #f0f0f1;
        }
        .alm-accordion__panel {
            padding: 16px;
        }
        .alm-accordion__icon {
            font-size: 18px;
            line-height: 1;
        }
        </style>
        <script>
        (function() {
            function initAccordion() {
                document.querySelectorAll('.alm-accordion__toggle').forEach(toggle => {
                    toggle.addEventListener('click', () => {
                        const panelId = toggle.getAttribute('aria-controls');
                        const panel = panelId ? document.getElementById(panelId) : null;
                        if (!panel) return;
                        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                        toggle.setAttribute('aria-expanded', (!isExpanded).toString());
                        panel.hidden = isExpanded;
                        const icon = toggle.querySelector('.alm-accordion__icon');
                        if (icon) {
                            icon.textContent = isExpanded ? '+' : '−';
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAccordion);
            } else {
                initAccordion();
            }
        })();
        </script>
        <?php
    }

    /**
     * Render student list modal and scripts for lesson analytics
     */
    private function render_student_list_modal() {
        ?>
        <div id="alm-lesson-students-modal" class="alm-lesson-students-modal" style="display: none;">
            <div class="alm-lesson-students-modal__content">
                <div class="alm-lesson-students-modal__header">
                    <h2 id="alm-lesson-students-title">Lesson Students</h2>
                    <button type="button" class="button-link delete" id="alm-lesson-students-close" aria-label="<?php esc_attr_e('Close', 'academy-lesson-manager'); ?>">×</button>
                </div>
                <div class="alm-lesson-students-modal__body">
                    <div class="alm-lesson-students-toolbar alm-lesson-crm-toolbar">
                        <?php if ( ! defined( 'JE_CRM_ENDPOINT' ) || ! defined( 'JE_CRM_API_KEY' ) ) : ?>
                            <p class="description" style="flex-basis:100%; margin:0 0 8px;">
                                <?php esc_html_e( 'Ensure JE_CRM_ENDPOINT and JE_CRM_API_KEY are defined in wp-config.php on this server.', 'academy-lesson-manager' ); ?>
                            </p>
                        <?php endif; ?>
                        <label>
                            <?php esc_html_e('Status', 'academy-lesson-manager'); ?>
                            <select id="alm-fluentcrm-status">
                                <option value="subscribed"><?php esc_html_e('subscribed', 'academy-lesson-manager'); ?></option>
                                <option value="pending"><?php esc_html_e('pending', 'academy-lesson-manager'); ?></option>
                                <option value="unsubscribed"><?php esc_html_e('unsubscribed', 'academy-lesson-manager'); ?></option>
                                <option value="bounced"><?php esc_html_e('bounced', 'academy-lesson-manager'); ?></option>
                                <option value="complained"><?php esc_html_e('complained', 'academy-lesson-manager'); ?></option>
                            </select>
                        </label>
                        <label>
                            <?php esc_html_e('Tag ID', 'academy-lesson-manager'); ?>
                            <input type="number" id="alm-fluentcrm-tag-id" min="0" step="1" placeholder="<?php echo esc_attr__( 'e.g. 121', 'academy-lesson-manager' ); ?>" style="width:90px;" />
                        </label>
                        <label>
                            <?php esc_html_e('Custom Field Key', 'academy-lesson-manager'); ?>
                            <input type="text" id="alm-fluentcrm-cf-key" placeholder="<?php echo esc_attr__( 'e.g. lead_source', 'academy-lesson-manager' ); ?>" style="width:140px;" />
                        </label>
                        <label>
                            <?php esc_html_e('Value', 'academy-lesson-manager'); ?>
                            <input type="text" id="alm-fluentcrm-cf-value" placeholder="<?php echo esc_attr__( 'e.g. jazzedge.academy', 'academy-lesson-manager' ); ?>" style="width:140px;" />
                        </label>
                        <button type="button" class="button button-primary" id="alm-lesson-send-fluentcrm">
                            <?php esc_html_e('Send to CRM', 'academy-lesson-manager'); ?>
                        </button>
                        <span id="alm-lesson-fluentcrm-status" class="alm-lesson-webhook-status"></span>
                    </div>
                    <div id="alm-lesson-students-loading" class="alm-lesson-students-loading">
                        <?php esc_html_e('Loading students...', 'academy-lesson-manager'); ?>
                    </div>
                    <div id="alm-lesson-students-empty" class="alm-lesson-students-empty" style="display:none;">
                        <?php esc_html_e('No students found.', 'academy-lesson-manager'); ?>
                    </div>
                    <div class="alm-lesson-students-table-wrapper">
                        <table class="wp-list-table widefat striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="alm-select-all-contacts" title="<?php echo esc_attr__( 'Select All', 'academy-lesson-manager' ); ?>" /></th>
                                    <th><?php esc_html_e('Student', 'academy-lesson-manager'); ?></th>
                                    <th><?php esc_html_e('Email', 'academy-lesson-manager'); ?></th>
                                    <th><?php esc_html_e('Views', 'academy-lesson-manager'); ?></th>
                                    <th><?php esc_html_e('Last Viewed', 'academy-lesson-manager'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="alm-lesson-students-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <style>
        .alm-lesson-student-trigger {
            background: none;
            border: none;
            color: #2271b1;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            font-weight: 600;
        }
        .alm-lesson-student-trigger:hover {
            color: #135e96;
        }
        .alm-lesson-students-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100000;
        }
        .alm-lesson-students-modal__content {
            width: 90%;
            max-width: 980px;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        .alm-lesson-students-modal__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #ccd0d4;
        }
        .alm-lesson-students-modal__body {
            padding: 20px;
            max-height: 70vh;
            overflow: auto;
        }
        .alm-lesson-students-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 15px;
        }
        .alm-lesson-students-toolbar input {
            min-width: 320px;
        }
        .alm-lesson-crm-toolbar select {
            min-width: 180px;
        }
        .alm-lesson-crm-toolbar input[type="number"],
        .alm-lesson-crm-toolbar input[type="text"] {
            min-width: 0;
        }
        .alm-lesson-students-loading {
            margin: 10px 0;
        }
        .alm-lesson-students-empty {
            margin: 10px 0;
            color: #666;
        }
        .alm-lesson-webhook-status {
            font-weight: 600;
        }
        </style>
        <script>
        (function() {
            const modal = document.getElementById('alm-lesson-students-modal');
            const closeBtn = document.getElementById('alm-lesson-students-close');
            const titleEl = document.getElementById('alm-lesson-students-title');
            const tableBody = document.getElementById('alm-lesson-students-body');
            const loadingEl = document.getElementById('alm-lesson-students-loading');
            const emptyEl = document.getElementById('alm-lesson-students-empty');
            const webhookBtn = document.getElementById('alm-lesson-send-webhook');
            const webhookStatus = document.getElementById('alm-lesson-webhook-status');
            const fluentStatusSelect = document.getElementById('alm-fluentcrm-status');
            const fluentTagId = document.getElementById('alm-fluentcrm-tag-id');
            const fluentCfKey = document.getElementById('alm-fluentcrm-cf-key');
            const fluentCfValue = document.getElementById('alm-fluentcrm-cf-value');
            const fluentBtn = document.getElementById('alm-lesson-send-fluentcrm');
            const fluentStatus = document.getElementById('alm-lesson-fluentcrm-status');
            const selectAllChk = document.getElementById('alm-select-all-contacts');
            if (selectAllChk) {
                selectAllChk.addEventListener('change', () => {
                    document.querySelectorAll('.alm-contact-checkbox')
                        .forEach(cb => cb.checked = selectAllChk.checked);
                });
            }

            document.addEventListener('change', e => {
                if (e.target && e.target.classList.contains('alm-contact-checkbox')) {
                    const all = document.querySelectorAll('.alm-contact-checkbox');
                    const checked = document.querySelectorAll('.alm-contact-checkbox:checked');
                    if (selectAllChk) selectAllChk.checked = all.length === checked.length;
                }
            });
            let currentLessonId = null;

            function almEscAttr(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;');
            }

            function openModal() {
                modal.style.display = 'flex';
            }

            function closeModal() {
                modal.style.display = 'none';
                tableBody.innerHTML = '';
                loadingEl.style.display = '';
                emptyEl.style.display = 'none';
                if (webhookStatus) {
                    webhookStatus.textContent = '';
                }
                if (fluentStatus) {
                    fluentStatus.textContent = '';
                }
                if (selectAllChk) {
                    selectAllChk.checked = false;
                }
            }

            function formatDateTime(value) {
                if (!value) return '—';
                const date = new Date(value.replace(' ', 'T'));
                if (isNaN(date.getTime())) return value;
                return date.toLocaleString();
            }

            function fetchStudents(postId, lessonTitle) {
                currentLessonId = postId;
                titleEl.textContent = lessonTitle ? `Students for: ${lessonTitle}` : 'Lesson Students';
                openModal();
                loadingEl.style.display = '';
                emptyEl.style.display = 'none';
                tableBody.innerHTML = '';

                fetch(`<?php echo esc_url_raw(rest_url('alm/v1/lesson-analytics/students')); ?>?lesson_post_id=${postId}`, {
                    method: 'GET',
                    headers: {
                        'X-WP-Nonce': '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loadingEl.style.display = 'none';
                    if (!data.success || !data.students || data.students.length === 0) {
                        emptyEl.style.display = '';
                        return;
                    }
                    const rows = data.students.map(student => `
                        <tr>
                            <td><input type="checkbox" class="alm-contact-checkbox" value="${almEscAttr(student.email)}" /></td>
                            <td>${student.display_name || 'Guest/Unknown'}</td>
                            <td>${student.email || '—'}</td>
                            <td>${student.views || 0}</td>
                            <td>${formatDateTime(student.last_viewed)}</td>
                        </tr>
                    `).join('');
                    tableBody.innerHTML = rows;
                    if (selectAllChk) {
                        selectAllChk.checked = false;
                    }
                })
                .catch(() => {
                    loadingEl.style.display = 'none';
                    emptyEl.style.display = '';
                });
            }

            document.querySelectorAll('.alm-lesson-student-trigger').forEach(btn => {
                btn.addEventListener('click', () => {
                    const postId = btn.getAttribute('data-post-id');
                    const title = btn.getAttribute('data-title') || '';
                    if (postId) {
                        fetchStudents(postId, title);
                        fetch('<?php echo esc_url_raw(rest_url('alm/v1/lesson-analytics/fluentcrm-settings')); ?>', {
                            method: 'GET',
                            headers: {
                                'X-WP-Nonce': '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success || !data.settings) {
                                return;
                            }
                            const s = data.settings;
                            if (fluentStatusSelect) {
                                fluentStatusSelect.value = s.status || 'subscribed';
                            }
                            if (fluentTagId) {
                                const tid = parseInt(s.tag_id, 10);
                                fluentTagId.value = (!isNaN(tid) && tid > 0) ? String(tid) : '';
                            }
                            if (fluentCfKey) {
                                fluentCfKey.value = s.cf_key || '';
                            }
                            if (fluentCfValue) {
                                fluentCfValue.value = s.cf_value || '';
                            }
                        })
                        .catch(() => {});
                    }
                });
            });

            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            if (webhookBtn && webhookStatus) {
                webhookBtn.addEventListener('click', () => {
                    if (!currentLessonId) return;
                    const webhookUrl = window.prompt('<?php echo esc_js( __( 'Enter webhook URL', 'academy-lesson-manager' ) ); ?>', '');
                    if (!webhookUrl || !String(webhookUrl).trim()) {
                        webhookStatus.textContent = '<?php echo esc_js( __( 'Webhook URL required.', 'academy-lesson-manager' ) ); ?>';
                        webhookStatus.style.color = '#b32d2e';
                        return;
                    }
                    const webhookUrlTrim = String(webhookUrl).trim();
                    webhookStatus.textContent = 'Sending...';
                    webhookStatus.style.color = '#1d2327';

                    fetch(`<?php echo esc_url_raw(rest_url('alm/v1/lesson-analytics/send-webhook')); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>'
                        },
                        body: JSON.stringify({
                            lesson_post_id: parseInt(currentLessonId, 10),
                            webhook_url: webhookUrlTrim
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const output = document.getElementById('alm-last-webhook-output');
                        const payloadText = JSON.stringify({
                            sent_at: new Date().toISOString(),
                            success: !!data.success,
                            message: data.message || '',
                            status_code: data.status_code || 0,
                            response_body: data.response_body || '',
                            payload: data.payload || {}
                        }, null, 2);
                        if (output) {
                            output.textContent = payloadText;
                        }
                        if (window.localStorage) {
                            localStorage.setItem('almLastWebhook', payloadText);
                        }

                        if (data.success) {
                            webhookStatus.textContent = data.message || 'Sent successfully.';
                            webhookStatus.style.color = '#1a7f37';
                        } else {
                            webhookStatus.textContent = data.message || 'Failed to send.';
                            webhookStatus.style.color = '#b32d2e';
                        }
                    })
                    .catch(() => {
                        webhookStatus.textContent = 'Failed to send.';
                        webhookStatus.style.color = '#b32d2e';
                    });
                });
            }

            if (fluentBtn) {
                fluentBtn.addEventListener('click', () => {
                    if (!currentLessonId) return;
                    const statusValue = fluentStatusSelect ? fluentStatusSelect.value : 'subscribed';
                    const tagIdNum = fluentTagId ? parseInt(fluentTagId.value, 10) : 0;
                    const cfKeyTrim = fluentCfKey ? fluentCfKey.value.trim() : '';
                    const cfValueTrim = fluentCfValue ? fluentCfValue.value.trim() : '';

                    const allCheckboxes = document.querySelectorAll('.alm-contact-checkbox');
                    const checkedBoxes = document.querySelectorAll('.alm-contact-checkbox:checked');
                    const emailsToSend = checkedBoxes.length > 0
                        ? Array.from(checkedBoxes).map(cb => cb.value)
                        : Array.from(allCheckboxes).map(cb => cb.value);

                    fluentStatus.textContent = 'Sending...';
                    fluentStatus.style.color = '#1d2327';

                    fetch(`<?php echo esc_url_raw(rest_url('alm/v1/lesson-analytics/send-fluentcrm')); ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>'
                        },
                        body: JSON.stringify({
                            lesson_post_id: parseInt(currentLessonId, 10),
                            status: statusValue,
                            tag_id: isNaN(tagIdNum) ? 0 : tagIdNum,
                            cf_key: cfKeyTrim,
                            cf_value: cfValueTrim,
                            emails: emailsToSend
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const output = document.getElementById('alm-last-webhook-output');
                        const payloadText = JSON.stringify({
                            sent_at: new Date().toISOString(),
                            success: !!data.success,
                            message: data.message || '',
                            attempted: data.attempted || 0,
                            successful: data.successful || 0,
                            failures: data.failures || [],
                            status_code: data.status_code || 0,
                            response_body: data.response_body || '',
                            payload: data.payload || {}
                        }, null, 2);
                        if (output) {
                            output.textContent = payloadText;
                        }
                        if (window.localStorage) {
                            localStorage.setItem('almLastWebhook', payloadText);
                        }

                        if (data.success) {
                            const sent = typeof data.successful === 'number' ? data.successful : 0;
                            const y = emailsToSend.length;
                            fluentStatus.textContent = 'Sent ' + sent + ' of ' + y + ' contacts.';
                            fluentStatus.style.color = '#1a7f37';
                        } else {
                            fluentStatus.textContent = data.message || 'Failed to send.';
                            fluentStatus.style.color = '#b32d2e';
                        }
                    })
                    .catch(() => {
                        fluentStatus.textContent = 'Failed to send.';
                        fluentStatus.style.color = '#b32d2e';
                    });
                });
            }
        })();
        </script>
        <?php
    }

    /**
     * Render last webhook payload panel
     */
    private function render_last_webhook_panel() {
        ?>
        <div class="alm-lesson-webhook-panel">
            <h2><?php esc_html_e('Last Webhook', 'academy-lesson-manager'); ?></h2>
            <p><?php esc_html_e('Displays the last webhook payload and response from Lesson Analytics.', 'academy-lesson-manager'); ?></p>
            <div style="margin-bottom: 10px;">
                <button type="button" class="button" id="alm-copy-webhook">
                    <?php esc_html_e('Copy Last Webhook', 'academy-lesson-manager'); ?>
                </button>
                <span id="alm-copy-webhook-status" style="margin-left: 10px;"></span>
            </div>
            <pre id="alm-last-webhook-output" style="background:#fff; border:1px solid #ccd0d4; padding:12px; max-height:300px; overflow:auto;">No webhook sent yet.</pre>
        </div>
        <style>
        .alm-lesson-webhook-panel {
            margin-top: 30px;
        }
        </style>
        <script>
        (function() {
            const output = document.getElementById('alm-last-webhook-output');
            const copyBtn = document.getElementById('alm-copy-webhook');
            const copyStatus = document.getElementById('alm-copy-webhook-status');
            if (!output) return;
            const cached = window.localStorage ? localStorage.getItem('almLastWebhook') : null;
            if (cached) {
                output.textContent = cached;
            }
            if (copyBtn) {
                copyBtn.addEventListener('click', () => {
                    const text = output.textContent || '';
                    if (!text) {
                        copyStatus.textContent = 'Nothing to copy.';
                        copyStatus.style.color = '#b32d2e';
                        return;
                    }
                    navigator.clipboard.writeText(text).then(() => {
                        copyStatus.textContent = 'Copied.';
                        copyStatus.style.color = '#1a7f37';
                        setTimeout(() => {
                            copyStatus.textContent = '';
                        }, 1500);
                    }).catch(() => {
                        copyStatus.textContent = 'Copy failed.';
                        copyStatus.style.color = '#b32d2e';
                    });
                });
            }
        })();
        </script>
        <?php
    }

    /**
     * Render navigation buttons
     */
    private function render_navigation_buttons($current_page) {
        echo '<div class="alm-navigation-buttons" style="margin-bottom: 20px;">';
        echo '<a href="?page=academy-manager" class="button ' . ($current_page === 'collections' ? 'button-primary' : '') . '">' . __('Collections', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-lessons" class="button ' . ($current_page === 'lessons' ? 'button-primary' : '') . '">' . __('Lessons', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-chapters" class="button ' . ($current_page === 'chapters' ? 'button-primary' : '') . '">' . __('Chapters', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-lesson-analytics" class="button ' . ($current_page === 'analytics' ? 'button-primary' : '') . '">' . __('Analytics', 'academy-lesson-manager') . '</a> ';
        echo '<a href="?page=academy-manager-settings" class="button ' . ($current_page === 'settings' ? 'button-primary' : '') . '" style="margin-left: 10px;">' . __('Settings', 'academy-lesson-manager') . '</a>';
        echo '</div>';
    }

    /**
     * Get available lesson types
     */
    private function get_available_types() {
        $types = $this->wpdb->get_col("SELECT DISTINCT type FROM {$this->table_name} WHERE type <> '' ORDER BY type");
        return array_values(array_filter(array_map('strval', $types)));
    }

    /**
     * Get and sanitize filters
     */
    private function get_filters(array $available_types, array $available_lessons) {
        $all_time = isset($_GET['all_time']) && intval($_GET['all_time']) === 1;
        $start_date = isset($_GET['start_date']) ? $this->sanitize_date($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? $this->sanitize_date($_GET['end_date']) : '';
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'lesson';
        $lesson_post_id = isset($_GET['lesson_post_id']) ? intval($_GET['lesson_post_id']) : 0;
        $range_preset = isset($_GET['range_preset']) ? sanitize_text_field($_GET['range_preset']) : '';
        $chart_interval = isset($_GET['chart_interval']) ? sanitize_text_field($_GET['chart_interval']) : 'day';

        if ($type === 'all') {
            $type = '';
        }

        if (!empty($type) && !in_array($type, $available_types, true)) {
            $type = 'lesson';
        }

        if ($lesson_post_id > 0 && !isset($available_lessons[$lesson_post_id])) {
            $lesson_post_id = 0;
        }

        if (!in_array($range_preset, array('', 'last_7', 'last_30', 'last_90', 'last_365'), true)) {
            $range_preset = '';
        }

        if (!in_array($chart_interval, array('day', 'week', 'month', 'quarter', 'year'), true)) {
            $chart_interval = 'day';
        }

        $has_range_inputs = $all_time || !empty($start_date) || !empty($end_date) || !empty($range_preset);
        if (!$has_range_inputs) {
            $range_preset = 'last_30';
        }

        if (!$all_time && !empty($range_preset)) {
            $end_date = date('Y-m-d', current_time('timestamp'));
            switch ($range_preset) {
                case 'last_7':
                    $start_date = date('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS * 7);
                    break;
                case 'last_30':
                    $start_date = date('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS * 30);
                    break;
                case 'last_90':
                    $start_date = date('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS * 90);
                    break;
                case 'last_365':
                    $start_date = date('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS * 365);
                    break;
                default:
                    break;
            }
        }

        if (!$all_time && empty($start_date) && empty($end_date)) {
            $start_date = date('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS * 30);
            $end_date = date('Y-m-d', current_time('timestamp'));
        }

        return array(
            'all_time' => $all_time,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'type' => $type,
            'lesson_post_id' => $lesson_post_id,
            'chart_interval' => $chart_interval,
            'range_preset' => $range_preset,
        );
    }

    /**
     * Build WHERE clause for filters
     * Uses rv. prefix so the clause works in JOIN queries (rv + l) without ambiguous column errors.
     */
    private function build_where_clause(array $filters) {
        $where = array('rv.deleted_at IS NULL');
        $params = array();

        if (!$filters['all_time']) {
            if (!empty($filters['start_date'])) {
                $where[] = 'rv.datetime >= %s';
                $params[] = $filters['start_date'] . ' 00:00:00';
            }
            if (!empty($filters['end_date'])) {
                $where[] = 'rv.datetime <= %s';
                $params[] = $filters['end_date'] . ' 23:59:59';
            }
        }

        if (!empty($filters['type'])) {
            $where[] = 'rv.type = %s';
            $params[] = $filters['type'];
        }

        if (!empty($filters['lesson_post_id'])) {
            $where[] = 'rv.post_id = %d';
            $params[] = $filters['lesson_post_id'];
        }

        return array(implode(' AND ', $where), $params);
    }

    /**
     * Render filters
     */
    private function render_filters(array $filters, array $available_types, array $available_lessons) {
        echo '<div class="tablenav top" style="margin-top: 15px;">';
        echo '<div class="alignleft actions">';
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="page" value="academy-manager-lesson-analytics" />';
        echo '<label style="margin-right: 8px;">' . __('Range', 'academy-lesson-manager') . ' ';
        echo '<select name="range_preset">';
        echo '<option value=""' . selected($filters['range_preset'], '', false) . '>' . __('Custom', 'academy-lesson-manager') . '</option>';
        echo '<option value="last_7"' . selected($filters['range_preset'], 'last_7', false) . '>' . __('Last 7 days', 'academy-lesson-manager') . '</option>';
        echo '<option value="last_30"' . selected($filters['range_preset'], 'last_30', false) . '>' . __('Last 30 days', 'academy-lesson-manager') . '</option>';
        echo '<option value="last_90"' . selected($filters['range_preset'], 'last_90', false) . '>' . __('Last 90 days', 'academy-lesson-manager') . '</option>';
        echo '<option value="last_365"' . selected($filters['range_preset'], 'last_365', false) . '>' . __('Last 365 days', 'academy-lesson-manager') . '</option>';
        echo '</select></label>';

        echo '<label style="margin-right: 8px;">' . __('Start', 'academy-lesson-manager') . ' ';
        echo '<input type="date" name="start_date" value="' . esc_attr($filters['start_date']) . '" /></label>';
        echo '<label style="margin-right: 8px;">' . __('End', 'academy-lesson-manager') . ' ';
        echo '<input type="date" name="end_date" value="' . esc_attr($filters['end_date']) . '" /></label>';
        echo '<label style="margin-right: 8px;"><input type="checkbox" name="all_time" value="1" ' . checked($filters['all_time'], true, false) . ' /> ' . __('All time', 'academy-lesson-manager') . '</label>';

        echo '<label style="margin-right: 8px;">' . __('Type', 'academy-lesson-manager') . ' ';
        echo '<select name="type">';
        echo '<option value="all"' . selected(empty($filters['type']), true, false) . '>' . __('All types', 'academy-lesson-manager') . '</option>';
        foreach ($available_types as $type) {
            echo '<option value="' . esc_attr($type) . '"' . selected($filters['type'], $type, false) . '>' . esc_html(ucwords(str_replace('-', ' ', $type))) . '</option>';
        }
        echo '</select></label>';

        echo '<label style="margin-right: 8px;">' . __('Lesson', 'academy-lesson-manager') . ' ';
        echo '<select name="lesson_post_id">';
        echo '<option value="0"' . selected(empty($filters['lesson_post_id']), true, false) . '>' . __('All lessons', 'academy-lesson-manager') . '</option>';
        foreach ($available_lessons as $post_id => $label) {
            echo '<option value="' . esc_attr($post_id) . '"' . selected($filters['lesson_post_id'], $post_id, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></label>';

        echo '<input type="submit" class="button" value="' . esc_attr__('Filter', 'academy-lesson-manager') . '" />';

        if (!empty($_GET['start_date']) || !empty($_GET['end_date']) || isset($_GET['all_time']) || isset($_GET['type']) || isset($_GET['lesson_post_id']) || isset($_GET['range_preset'])) {
            echo '<a href="' . esc_url(admin_url('admin.php?page=academy-manager-lesson-analytics')) . '" class="button">' . __('Clear', 'academy-lesson-manager') . '</a>';
        }

        echo '</form>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render summary metrics
     */
    private function render_summary(array $summary, array $filters) {
        $range_label = $filters['all_time'] ? __('All time', 'academy-lesson-manager') : sprintf(
            __('%s to %s', 'academy-lesson-manager'),
            $filters['start_date'] ? esc_html($filters['start_date']) : __('(start)', 'academy-lesson-manager'),
            $filters['end_date'] ? esc_html($filters['end_date']) : __('(end)', 'academy-lesson-manager')
        );

        echo '<h2 style="margin-top: 25px;">' . __('Overview', 'academy-lesson-manager') . '</h2>';
        echo '<p><strong>' . __('Range:', 'academy-lesson-manager') . '</strong> ' . $range_label . '</p>';
        echo '<table class="widefat striped" style="max-width: 600px;">';
        echo '<tbody>';
        echo '<tr><th>' . __('Total Views', 'academy-lesson-manager') . '</th><td>' . esc_html(number_format_i18n($summary['total_views'])) . '</td></tr>';
        echo '<tr><th>' . __('Unique Users', 'academy-lesson-manager') . '</th><td>' . esc_html(number_format_i18n($summary['unique_users'])) . '</td></tr>';
        echo '<tr><th>' . __('Unique Lessons', 'academy-lesson-manager') . '</th><td>' . esc_html(number_format_i18n($summary['unique_lessons'])) . '</td></tr>';
        echo '</tbody>';
        echo '</table>';
    }

    /**
     * Render AI insights section
     */
    private function render_ai_insights(array $insights) {
        echo '<h2 style="margin-top: 25px;">' . __('AI Insights', 'academy-lesson-manager') . '</h2>';
        echo '<p style="margin-top: 6px;">' . __('Highlights based on views and unique users for the selected range.', 'academy-lesson-manager') . '</p>';

        if (empty($insights['top']) && empty($insights['bottom'])) {
            echo '<div class="notice notice-info"><p>' . __('Not enough data to generate insights for this range.', 'academy-lesson-manager') . '</p></div>';
            return;
        }

        echo '<div style="display: flex; gap: 20px; flex-wrap: wrap;">';
        echo '<div style="flex: 1; min-width: 320px;">';
        echo '<h3>' . __('Top Performers', 'academy-lesson-manager') . '</h3>';
        echo '<ol style="margin-left: 18px;">';
        if (empty($insights['top'])) {
            echo '<li>' . __('No data available.', 'academy-lesson-manager') . '</li>';
        } else {
            foreach ($insights['top'] as $row) {
                $title_html = $this->format_lesson_title($row);
                echo '<li>' . $title_html . ' <span style="color:#666;">(' . sprintf(__('Score %d', 'academy-lesson-manager'), intval($row['score'])) . ')</span></li>';
            }
        }
        echo '</ol>';
        echo '</div>';

        echo '<div style="flex: 1; min-width: 320px;">';
        echo '<h3>' . __('Needs Attention', 'academy-lesson-manager') . '</h3>';
        echo '<ol style="margin-left: 18px;">';
        if (empty($insights['bottom'])) {
            echo '<li>' . __('No data available.', 'academy-lesson-manager') . '</li>';
        } else {
            foreach ($insights['bottom'] as $row) {
                $title_html = $this->format_lesson_title($row);
                echo '<li>' . $title_html . ' <span style="color:#666;">(' . sprintf(__('Score %d', 'academy-lesson-manager'), intval($row['score'])) . ')</span></li>';
            }
        }
        echo '</ol>';
        echo '<p class="description" style="margin-top: 6px;">' . __('Excludes lessons with zero views; see “Lessons Not Viewed” below.', 'academy-lesson-manager') . '</p>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render views over time chart
     */
    private function render_views_chart(array $series, array $filters) {
        $interval_label = $this->get_chart_interval_label($filters['chart_interval']);

        echo '<h2 style="margin-top: 25px;">' . __('Views Over Time', 'academy-lesson-manager') . '</h2>';
        echo '<p style="margin-top: 6px;">' . sprintf(__('Lesson views per %s.', 'academy-lesson-manager'), strtolower($interval_label)) . '</p>';
        echo '<form method="get" action="" style="margin: 10px 0 0;">';
        echo '<input type="hidden" name="page" value="academy-manager-lesson-analytics" />';
        echo '<input type="hidden" name="start_date" value="' . esc_attr($filters['start_date']) . '" />';
        echo '<input type="hidden" name="end_date" value="' . esc_attr($filters['end_date']) . '" />';
        if (!empty($filters['range_preset'])) {
            echo '<input type="hidden" name="range_preset" value="' . esc_attr($filters['range_preset']) . '" />';
        }
        if ($filters['all_time']) {
            echo '<input type="hidden" name="all_time" value="1" />';
        }
        if (!empty($filters['type'])) {
            echo '<input type="hidden" name="type" value="' . esc_attr($filters['type']) . '" />';
        }
        if (!empty($filters['lesson_post_id'])) {
            echo '<input type="hidden" name="lesson_post_id" value="' . esc_attr($filters['lesson_post_id']) . '" />';
        }
        echo '<label style="margin-right: 8px;">' . __('Interval', 'academy-lesson-manager') . ' ';
        echo '<select name="chart_interval">';
        echo '<option value="day"' . selected($filters['chart_interval'], 'day', false) . '>' . __('Daily', 'academy-lesson-manager') . '</option>';
        echo '<option value="week"' . selected($filters['chart_interval'], 'week', false) . '>' . __('Weekly', 'academy-lesson-manager') . '</option>';
        echo '<option value="month"' . selected($filters['chart_interval'], 'month', false) . '>' . __('Monthly', 'academy-lesson-manager') . '</option>';
        echo '<option value="quarter"' . selected($filters['chart_interval'], 'quarter', false) . '>' . __('Quarterly', 'academy-lesson-manager') . '</option>';
        echo '<option value="year"' . selected($filters['chart_interval'], 'year', false) . '>' . __('Yearly', 'academy-lesson-manager') . '</option>';
        echo '</select></label>';
        echo '<input type="submit" class="button" value="' . esc_attr__('Update', 'academy-lesson-manager') . '" />';
        echo '</form>';

        if (empty($series)) {
            echo '<div class="notice notice-info"><p>' . __('No view data available for the selected range.', 'academy-lesson-manager') . '</p></div>';
            return;
        }

        $counts = wp_list_pluck($series, 'count');
        $max = max(1, max($counts));
        $points = count($series);
        $width = 1000;
        $height = 260;
        $chart_height = 200;
        $top_padding = 20;
        $bar_spacing = 4;
        $bar_width = max(6, floor(($width - ($points * $bar_spacing)) / max(1, $points)));
        $label_step = max(1, (int) ceil($points / 10));

        echo '<div style="overflow-x: auto; background: #fff; border: 1px solid #ccd0d4; padding: 15px;">';
        echo '<svg viewBox="0 0 ' . esc_attr($width) . ' ' . esc_attr($height) . '" role="img" aria-label="' . esc_attr__('Lesson views over time', 'academy-lesson-manager') . '">';
        echo '<rect x="0" y="0" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" fill="#ffffff"></rect>';

        $x = 0;
        foreach ($series as $index => $row) {
            $count = intval($row['count']);
            $bar_height = ($count / $max) * $chart_height;
            $y = $chart_height - $bar_height + $top_padding;
            $bar_x = $x + ($bar_spacing / 2);

            echo '<rect x="' . esc_attr($bar_x) . '" y="' . esc_attr($y) . '" width="' . esc_attr($bar_width) . '" height="' . esc_attr($bar_height) . '" fill="#2271b1"></rect>';
            echo '<text x="' . esc_attr($bar_x + ($bar_width / 2)) . '" y="' . esc_attr($y - 4) . '" font-size="11" text-anchor="middle" fill="#111">' . esc_html(number_format_i18n($count)) . '</text>';

            if ($index % $label_step === 0) {
                $label = esc_html($row['label']);
                echo '<text x="' . esc_attr($bar_x) . '" y="' . esc_attr($chart_height + $top_padding + 20) . '" font-size="12" fill="#444">' . $label . '</text>';
            }

            if ($points > 0) {
                $x += $bar_width + $bar_spacing;
            }
        }

        echo '<line x1="0" y1="' . esc_attr($chart_height + $top_padding) . '" x2="' . esc_attr($width) . '" y2="' . esc_attr($chart_height + $top_padding) . '" stroke="#ccd0d4" stroke-width="1"></line>';
        echo '</svg>';
        echo '</div>';
    }

    /**
     * Render top lessons table
     */
    private function render_top_lessons(array $lessons) {
        echo '<div class="alm-accordion">';
        echo '<button type="button" class="alm-accordion__toggle" aria-expanded="false" aria-controls="alm-accordion-panel-top-lessons">';
        echo '<span class="alm-accordion__title">' . __('Top Lessons', 'academy-lesson-manager') . '</span>';
        echo '<span class="alm-accordion__icon">+</span>';
        echo '</button>';
        echo '<div id="alm-accordion-panel-top-lessons" class="alm-accordion__panel" hidden>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Lesson', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Collection', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Views', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Unique Users', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Last Viewed', 'academy-lesson-manager') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        if (empty($lessons)) {
            echo '<tr><td colspan="5">' . __('No lesson views found for this range.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($lessons as $lesson) {
                $title = !empty($lesson['title']) ? stripslashes($lesson['title']) : __('(Untitled)', 'academy-lesson-manager');
                $collection_title = !empty($lesson['collection_title']) ? stripslashes($lesson['collection_title']) : '';
                $post_id = intval($lesson['post_id']);
                $lesson_link = $post_id > 0 ? get_edit_post_link($post_id) : '';
                $title_html = $lesson_link ? '<a href="' . esc_url($lesson_link) . '">' . esc_html($title) . '</a>' : esc_html($title);

                echo '<tr>';
                echo '<td>' . $title_html . ($post_id > 0 ? ' <span style="color:#666;">(#' . esc_html($post_id) . ')</span>' : '') . '</td>';
                echo '<td>' . ($collection_title ? esc_html($collection_title) : '—') . '</td>';
                echo '<td>' . esc_html(number_format_i18n($lesson['views'])) . '</td>';
                echo '<td>';
                if ($post_id > 0 && intval($lesson['unique_users']) > 0) {
                    echo '<button type="button" class="alm-lesson-student-trigger" data-post-id="' . esc_attr($post_id) . '" data-title="' . esc_attr($title) . '">';
                    echo esc_html(number_format_i18n($lesson['unique_users']));
                    echo '</button>';
                } else {
                    echo esc_html(number_format_i18n($lesson['unique_users']));
                }
                echo '</td>';
                echo '<td>' . esc_html($this->format_datetime($lesson['last_viewed'])) . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render top users table
     */
    private function render_top_users(array $users) {
        echo '<div class="alm-accordion">';
        echo '<button type="button" class="alm-accordion__toggle" aria-expanded="false" aria-controls="alm-accordion-panel-top-users">';
        echo '<span class="alm-accordion__title">' . __('Top Users', 'academy-lesson-manager') . '</span>';
        echo '<span class="alm-accordion__icon">+</span>';
        echo '</button>';
        echo '<div id="alm-accordion-panel-top-users" class="alm-accordion__panel" hidden>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('User', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Views', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Unique Lessons', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Last Viewed', 'academy-lesson-manager') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        if (empty($users)) {
            echo '<tr><td colspan="4">' . __('No user views found for this range.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            $user_ids = array_unique(array_filter(array_map('intval', wp_list_pluck($users, 'user_id'))));
            $user_map = array();
            if (!empty($user_ids)) {
                $loaded_users = get_users(array('include' => $user_ids, 'fields' => array('ID', 'display_name', 'user_email')));
                foreach ($loaded_users as $u) {
                    $user_map[$u->ID] = $u;
                }
            }
            foreach ($users as $row) {
                $user_id = intval($row['user_id']);
                $user = isset($user_map[$user_id]) ? $user_map[$user_id] : null;

                if ($user) {
                    $user_edit_url = admin_url('user-edit.php?user_id=' . $user_id);
                    $user_display = '<a href="' . esc_url($user_edit_url) . '">' . esc_html($user->display_name) . '</a> ';
                    $user_display .= '<span style="color:#666;">(' . esc_html($user->user_email) . ')</span>';
                } else {
                    $user_display = '<em>' . __('Guest/Unknown', 'academy-lesson-manager') . '</em>';
                }

                echo '<tr>';
                echo '<td>' . $user_display . '</td>';
                echo '<td>' . esc_html(number_format_i18n($row['views'])) . '</td>';
                echo '<td>' . esc_html(number_format_i18n($row['unique_lessons'])) . '</td>';
                echo '<td>' . esc_html($this->format_datetime($row['last_viewed'])) . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render recent views table
     */
    private function render_recent_views(array $views) {
        echo '<div class="alm-accordion">';
        echo '<button type="button" class="alm-accordion__toggle" aria-expanded="false" aria-controls="alm-accordion-panel-recent-views">';
        echo '<span class="alm-accordion__title">' . __('Recent Views', 'academy-lesson-manager') . '</span>';
        echo '<span class="alm-accordion__icon">+</span>';
        echo '</button>';
        echo '<div id="alm-accordion-panel-recent-views" class="alm-accordion__panel" hidden>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Lesson', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Collection', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('User', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Type', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Viewed At', 'academy-lesson-manager') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        if (empty($views)) {
            echo '<tr><td colspan="5">' . __('No recent views found for this range.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            $user_ids = array_unique(array_filter(array_map('intval', wp_list_pluck($views, 'user_id'))));
            $user_map = array();
            if (!empty($user_ids)) {
                $loaded_users = get_users(array('include' => $user_ids, 'fields' => array('ID', 'display_name', 'user_email')));
                foreach ($loaded_users as $u) {
                    $user_map[$u->ID] = $u;
                }
            }
            foreach ($views as $view) {
                $title = !empty($view['title']) ? stripslashes($view['title']) : __('(Untitled)', 'academy-lesson-manager');
                $collection_title = !empty($view['collection_title']) ? stripslashes($view['collection_title']) : '';
                $post_id = intval($view['post_id']);
                $lesson_link = $post_id > 0 ? get_edit_post_link($post_id) : '';
                $title_html = $lesson_link ? '<a href="' . esc_url($lesson_link) . '">' . esc_html($title) . '</a>' : esc_html($title);

                $user_id = intval($view['user_id']);
                $user = isset($user_map[$user_id]) ? $user_map[$user_id] : null;
                if ($user) {
                    $user_edit_url = admin_url('user-edit.php?user_id=' . $user_id);
                    $user_display = '<a href="' . esc_url($user_edit_url) . '">' . esc_html($user->display_name) . '</a>';
                } else {
                    $user_display = '<em>' . __('Guest/Unknown', 'academy-lesson-manager') . '</em>';
                }

                echo '<tr>';
                echo '<td>' . $title_html . '</td>';
                echo '<td>' . ($collection_title ? esc_html($collection_title) : '—') . '</td>';
                echo '<td>' . $user_display . '</td>';
                echo '<td>' . esc_html($view['type']) . '</td>';
                echo '<td>' . esc_html($this->format_datetime($view['datetime'])) . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render unviewed lessons table
     */
    private function render_unviewed_lessons(array $lessons, array $filters) {
        $range_label = $filters['all_time'] ? __('All time', 'academy-lesson-manager') : sprintf(
            __('%s to %s', 'academy-lesson-manager'),
            $filters['start_date'] ? esc_html($filters['start_date']) : __('(start)', 'academy-lesson-manager'),
            $filters['end_date'] ? esc_html($filters['end_date']) : __('(end)', 'academy-lesson-manager')
        );

        echo '<div class="alm-accordion">';
        echo '<button type="button" class="alm-accordion__toggle" aria-expanded="false" aria-controls="alm-accordion-panel-unviewed-lessons">';
        echo '<span class="alm-accordion__title">' . __('Lessons Not Viewed', 'academy-lesson-manager') . '</span>';
        echo '<span class="alm-accordion__icon">+</span>';
        echo '</button>';
        echo '<div id="alm-accordion-panel-unviewed-lessons" class="alm-accordion__panel" hidden>';
        echo '<p style="margin-top: 6px;">' . sprintf(__('Shows lessons with zero views in range: %s', 'academy-lesson-manager'), $range_label) . '</p>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Lesson', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Collection', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Status', 'academy-lesson-manager') . '</th>';
        echo '<th>' . __('Post ID', 'academy-lesson-manager') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        if (empty($lessons)) {
            echo '<tr><td colspan="4">' . __('No unviewed lessons found for this range.', 'academy-lesson-manager') . '</td></tr>';
        } else {
            foreach ($lessons as $lesson) {
                $title = !empty($lesson['lesson_title']) ? stripslashes($lesson['lesson_title']) : __('(Untitled)', 'academy-lesson-manager');
                $collection_title = !empty($lesson['collection_title']) ? stripslashes($lesson['collection_title']) : '';
                $post_id = intval($lesson['post_id']);
                $lesson_link = $post_id > 0 ? get_edit_post_link($post_id) : '';
                $title_html = $lesson_link ? '<a href="' . esc_url($lesson_link) . '">' . esc_html($title) . '</a>' : esc_html($title);

                echo '<tr>';
                echo '<td>' . $title_html . '</td>';
                echo '<td>' . ($collection_title ? esc_html($collection_title) : '—') . '</td>';
                echo '<td>' . esc_html($lesson['status']) . '</td>';
                echo '<td>' . ($post_id > 0 ? esc_html($post_id) : '—') . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Get summary metrics
     */
    private function get_summary($where_sql, array $params) {
        $sql = "SELECT COUNT(*) as total_views, COUNT(DISTINCT rv.user_id) as unique_users, COUNT(DISTINCT rv.post_id) as unique_lessons
            FROM {$this->table_name} rv
            WHERE {$where_sql}";

        $row = !empty($params) ? $this->wpdb->get_row($this->wpdb->prepare($sql, $params), ARRAY_A) : $this->wpdb->get_row($sql, ARRAY_A);

        return array(
            'total_views' => isset($row['total_views']) ? intval($row['total_views']) : 0,
            'unique_users' => isset($row['unique_users']) ? intval($row['unique_users']) : 0,
            'unique_lessons' => isset($row['unique_lessons']) ? intval($row['unique_lessons']) : 0,
        );
    }

    /**
     * Get lesson performance stats for insights
     */
    private function get_lesson_performance($where_sql, array $params) {
        $sql = "SELECT rv.post_id, rv.title, COUNT(*) as views, COUNT(DISTINCT rv.user_id) as unique_users, MAX(rv.datetime) as last_viewed
            FROM {$this->table_name} rv
            WHERE {$where_sql}
            GROUP BY rv.post_id, rv.title
            ORDER BY views DESC";

        return !empty($params) ? $this->wpdb->get_results($this->wpdb->prepare($sql, $params), ARRAY_A) : $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Build AI insights payload
     */
    private function build_ai_insights(array $lesson_stats, array $filters) {
        if (empty($lesson_stats)) {
            return array('top' => array(), 'bottom' => array());
        }

        $scored = array();
        foreach ($lesson_stats as $row) {
            $views = intval($row['views']);
            $unique_users = intval($row['unique_users']);
            if ($views <= 0) {
                continue;
            }
            $score = $views + $unique_users;
            $row['score'] = $score;
            $scored[] = $row;
        }

        if (empty($scored)) {
            return array('top' => array(), 'bottom' => array());
        }

        usort($scored, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $top = array_slice($scored, 0, 5);

        $bottom = $scored;
        usort($bottom, function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return $a['views'] <=> $b['views'];
            }
            return $a['score'] <=> $b['score'];
        });
        $bottom = array_slice($bottom, 0, 5);

        $insights = array(
            'top' => $top,
            'bottom' => $bottom,
        );

        return apply_filters('alm_ai_lesson_performance_insights', $insights, $lesson_stats, $filters);
    }

    /**
     * Get top lessons
     */
    private function get_top_lessons($where_sql, array $params) {
        $limit = 25;
        $collections_table = $this->collections_table_name;
        $sql = "SELECT rv.post_id,
                       rv.title,
                       MAX(c.collection_title) as collection_title,
                       COUNT(*) as views,
                       COUNT(DISTINCT rv.user_id) as unique_users,
                       MAX(rv.datetime) as last_viewed
            FROM {$this->table_name} rv
            LEFT JOIN {$this->lessons_table_name} l ON l.post_id = rv.post_id
            LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
            WHERE {$where_sql}
            GROUP BY rv.post_id, rv.title
            ORDER BY views DESC, last_viewed DESC
            LIMIT %d";

        $all_params = array_merge($params, array($limit));
        return $this->wpdb->get_results($this->wpdb->prepare($sql, $all_params), ARRAY_A);
    }

    /**
     * Get top users
     */
    private function get_top_users($where_sql, array $params) {
        $limit = 25;
        $sql = "SELECT rv.user_id, COUNT(*) as views, COUNT(DISTINCT rv.post_id) as unique_lessons, MAX(rv.datetime) as last_viewed
            FROM {$this->table_name} rv
            WHERE {$where_sql}
            GROUP BY rv.user_id
            ORDER BY views DESC, last_viewed DESC
            LIMIT %d";

        $all_params = array_merge($params, array($limit));
        return $this->wpdb->get_results($this->wpdb->prepare($sql, $all_params), ARRAY_A);
    }

    /**
     * Get recent views
     */
    private function get_recent_views($where_sql, array $params) {
        $limit = 50;
        $collections_table = $this->collections_table_name;
        $sql = "SELECT rv.ID,
                       rv.title,
                       rv.type,
                       rv.datetime,
                       rv.user_id,
                       rv.post_id,
                       c.collection_title
            FROM {$this->table_name} rv
            LEFT JOIN {$this->lessons_table_name} l ON l.post_id = rv.post_id
            LEFT JOIN {$collections_table} c ON c.ID = l.collection_id
            WHERE {$where_sql}
            ORDER BY rv.datetime DESC
            LIMIT %d";

        $all_params = array_merge($params, array($limit));
        return $this->wpdb->get_results($this->wpdb->prepare($sql, $all_params), ARRAY_A);
    }

    /**
     * Get views grouped by interval
     */
    private function get_views_over_time(array $filters) {
        list($where_sql, $params) = $this->build_where_clause($filters);

        $interval = $filters['chart_interval'];
        $sql = '';
        $label_format = 'M j';

        switch ($interval) {
            case 'week':
                $period_expr = "DATE_SUB(DATE(rv.datetime), INTERVAL WEEKDAY(rv.datetime) DAY)";
                $label_format = 'M j, Y';
                $sql = "SELECT {$period_expr} as period, COUNT(*) as views
                    FROM {$this->table_name} rv
                    WHERE {$where_sql}
                    GROUP BY period
                    ORDER BY period ASC";
                break;
            case 'month':
                $period_expr = "DATE_FORMAT(rv.datetime, '%Y-%m-01')";
                $label_format = 'M Y';
                $sql = "SELECT {$period_expr} as period, COUNT(*) as views
                    FROM {$this->table_name} rv
                    WHERE {$where_sql}
                    GROUP BY period
                    ORDER BY period ASC";
                break;
            case 'quarter':
                $sql = "SELECT YEAR(rv.datetime) as year, QUARTER(rv.datetime) as quarter, COUNT(*) as views
                    FROM {$this->table_name} rv
                    WHERE {$where_sql}
                    GROUP BY year, quarter
                    ORDER BY year ASC, quarter ASC";
                break;
            case 'year':
                $sql = "SELECT YEAR(rv.datetime) as year, COUNT(*) as views
                    FROM {$this->table_name} rv
                    WHERE {$where_sql}
                    GROUP BY year
                    ORDER BY year ASC";
                break;
            default:
                $period_expr = "DATE(rv.datetime)";
                $label_format = 'M j';
                $sql = "SELECT {$period_expr} as period, COUNT(*) as views
                    FROM {$this->table_name} rv
                    WHERE {$where_sql}
                    GROUP BY period
                    ORDER BY period ASC";
                break;
        }

        $rows = !empty($params) ? $this->wpdb->get_results($this->wpdb->prepare($sql, $params), ARRAY_A) : $this->wpdb->get_results($sql, ARRAY_A);

        $series = array();
        foreach ($rows as $row) {
            $period = '';
            $label = '';
            if ($interval === 'quarter') {
                $year = isset($row['year']) ? intval($row['year']) : 0;
                $quarter = isset($row['quarter']) ? intval($row['quarter']) : 0;
                $label = $year && $quarter ? sprintf('Q%d %d', $quarter, $year) : '';
                $period = $year && $quarter ? sprintf('%d-Q%d', $year, $quarter) : '';
            } elseif ($interval === 'year') {
                $year = isset($row['year']) ? intval($row['year']) : 0;
                $label = $year ? (string) $year : '';
                $period = $label;
            } else {
                $period = isset($row['period']) ? $row['period'] : '';
                $label = $period ? date($label_format, strtotime($period)) : '';
            }
            $series[] = array(
                'period' => $period,
                'label' => $label,
                'count' => intval($row['views']),
            );
        }

        return $series;
    }

    /**
     * Get lessons with zero views in range
     */
    private function get_unviewed_lessons(array $filters) {
        if (empty($this->lessons_table_name)) {
            return array();
        }
        if (empty($this->collections_table_name)) {
            return array();
        }

        $table_exists = $this->wpdb->get_var(
            $this->wpdb->prepare("SHOW TABLES LIKE %s", $this->lessons_table_name)
        ) == $this->lessons_table_name;

        if (!$table_exists) {
            return array();
        }

        $join_conditions = array('rv.post_id = l.post_id', 'rv.deleted_at IS NULL');
        $params = array();

        if (!$filters['all_time']) {
            if (!empty($filters['start_date'])) {
                $join_conditions[] = 'rv.datetime >= %s';
                $params[] = $filters['start_date'] . ' 00:00:00';
            }
            if (!empty($filters['end_date'])) {
                $join_conditions[] = 'rv.datetime <= %s';
                $params[] = $filters['end_date'] . ' 23:59:59';
            }
        }

        if (!empty($filters['type'])) {
            $join_conditions[] = 'rv.type = %s';
            $params[] = $filters['type'];
        }

        $where = array("l.status <> 'archived'", "l.post_id > 0");

        if (!empty($filters['lesson_post_id'])) {
            $where[] = 'l.post_id = %d';
            $params[] = $filters['lesson_post_id'];
        }

        $join_sql = implode(' AND ', $join_conditions);
        $where_sql = implode(' AND ', $where);

        $sql = "SELECT l.post_id, l.lesson_title, l.status, c.collection_title
            FROM {$this->lessons_table_name} l
            LEFT JOIN {$this->table_name} rv ON {$join_sql}
            LEFT JOIN {$this->collections_table_name} c ON c.ID = l.collection_id
            WHERE {$where_sql}
            GROUP BY l.post_id, l.lesson_title, l.status, c.collection_title
            HAVING COUNT(rv.ID) = 0
            ORDER BY l.lesson_title ASC";

        return !empty($params) ? $this->wpdb->get_results($this->wpdb->prepare($sql, $params), ARRAY_A) : $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Format lesson title with admin link when available
     */
    private function format_lesson_title(array $row) {
        $title = !empty($row['title']) ? stripslashes($row['title']) : __('(Untitled)', 'academy-lesson-manager');
        $post_id = intval($row['post_id']);
        $lesson_link = $post_id > 0 ? get_edit_post_link($post_id) : '';
        return $lesson_link ? '<a href="' . esc_url($lesson_link) . '">' . esc_html($title) . '</a>' : esc_html($title);
    }

    /**
     * Get chart interval label
     */
    private function get_chart_interval_label($interval) {
        switch ($interval) {
            case 'week':
                return __('Weekly', 'academy-lesson-manager');
            case 'month':
                return __('Monthly', 'academy-lesson-manager');
            case 'quarter':
                return __('Quarterly', 'academy-lesson-manager');
            case 'year':
                return __('Yearly', 'academy-lesson-manager');
            default:
                return __('Daily', 'academy-lesson-manager');
        }
    }

    /**
     * Sanitize date in Y-m-d format
     */
    private function sanitize_date($date) {
        $date = sanitize_text_field($date);
        if (empty($date)) {
            return '';
        }

        $dt = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dt || $dt->format('Y-m-d') !== $date) {
            return '';
        }

        return $date;
    }

    /**
     * Format datetime output
     */
    private function format_datetime($datetime) {
        if (empty($datetime)) {
            return '—';
        }

        return mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $datetime);
    }

    /**
     * Get lessons for dropdown filter
     */
    private function get_available_lessons() {
        if (empty($this->lessons_table_name)) {
            return array();
        }

        $table_exists = $this->wpdb->get_var(
            $this->wpdb->prepare("SHOW TABLES LIKE %s", $this->lessons_table_name)
        ) == $this->lessons_table_name;

        if (!$table_exists) {
            return array();
        }

        $rows = $this->wpdb->get_results(
            "SELECT post_id, lesson_title
             FROM {$this->lessons_table_name}
             WHERE post_id > 0 AND status <> 'archived'
             ORDER BY lesson_title ASC",
            ARRAY_A
        );

        $lessons = array();
        foreach ($rows as $row) {
            $post_id = intval($row['post_id']);
            if ($post_id <= 0) {
                continue;
            }
            $title = !empty($row['lesson_title']) ? $row['lesson_title'] : __('(Untitled)', 'academy-lesson-manager');
            $lessons[$post_id] = stripslashes($title);
        }

        return $lessons;
    }
}
