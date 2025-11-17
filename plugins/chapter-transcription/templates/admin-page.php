<?php
/**
 * Admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Chapter Transcription Management</h1>
    
    <?php
    // Get lessons for filter dropdown (passed from main file)
    $all_lessons = isset($template_all_lessons) ? $template_all_lessons : array();
    $queue = isset($queue_stats) ? $queue_stats : array();
    ?>
    
    <div style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <form method="get" action="<?php echo admin_url('admin.php'); ?>" style="display: flex; align-items: center; gap: 10px;">
            <input type="hidden" name="page" value="chapter-transcription">
            <label for="filter_lesson" style="font-weight: 600;">Filter by Lesson:</label>
            <select name="filter_lesson" id="filter_lesson" style="min-width: 300px; padding: 5px;">
                <option value="0">All Lessons</option>
                <?php foreach ($all_lessons as $lesson): ?>
                    <option value="<?php echo esc_attr($lesson->ID); ?>" <?php selected($pagination_data['filter_lesson_id'], $lesson->ID); ?>>
                        <?php echo esc_html($lesson->lesson_title); ?> (ID: <?php echo esc_html($lesson->ID); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">Filter</button>
            <?php if ($pagination_data['filter_lesson_id'] > 0): ?>
                <a href="<?php echo admin_url('admin.php?page=chapter-transcription'); ?>" class="button">Clear Filter</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
        <div style="flex: 1 1 280px; background: #fff; border: 1px solid #ccd0d4; border-radius: 6px; padding: 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2 style="margin-top: 0; font-size: 16px;">Queue Overview</h2>
            <ul style="margin: 0; padding-left: 16px; font-size: 13px; line-height: 1.6;">
                <li><strong>Pending:</strong> <?php echo isset($queue['pending']) ? intval($queue['pending']) : 0; ?></li>
                <li><strong>Processing:</strong> <?php echo isset($queue['processing']) ? intval($queue['processing']) : 0; ?></li>
                <li><strong>Completed (24h):</strong> <?php echo isset($queue['completed_24h']) ? intval($queue['completed_24h']) : 0; ?></li>
                <li><strong>Failed (24h):</strong> <?php echo isset($queue['failed_24h']) ? intval($queue['failed_24h']) : 0; ?></li>
                <li><strong>Queue Locked:</strong> <?php echo !empty($queue['locked']) ? '<span style="color:#dc3232;font-weight:600;">Yes</span>' : 'No'; ?></li>
                <li><strong>Next Run:</strong> <?php echo !empty($queue['next_run']) ? date_i18n('M j, g:i:s a', $queue['next_run']) : 'Not scheduled'; ?></li>
                <li><strong>Last Run:</strong> <?php echo !empty($queue['last_run']) ? date_i18n('M j, g:i:s a', $queue['last_run']) : 'Unknown'; ?></li>
            </ul>
        </div>
        <div style="flex: 1 1 320px; background: #fff; border: 1px solid #ccd0d4; border-radius: 6px; padding: 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2 style="margin-top: 0; font-size: 16px;">Recent Queue Activity</h2>
            <?php if (!empty($queue['recent_jobs'])): ?>
                <table class="widefat striped" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Job</th>
                            <th style="width: 70px;">Chapter</th>
                            <th style="width: 110px;">Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queue['recent_jobs'] as $job): ?>
                            <tr>
                                <td>#<?php echo intval($job->ID); ?></td>
                                <td><?php echo intval($job->chapter_id); ?></td>
                                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $job->job_type))); ?></td>
                                <td>
                                    <strong><?php echo esc_html(ucfirst($job->status)); ?></strong><br>
                                    <small>
                                        <?php if ($job->status === 'pending'): ?>
                                            Added <?php echo date_i18n('g:i a', strtotime($job->created_at)); ?>
                                        <?php elseif ($job->status === 'processing'): ?>
                                            Started <?php echo date_i18n('g:i a', strtotime($job->started_at)); ?>
                                        <?php else: ?>
                                            Done <?php echo date_i18n('g:i a', strtotime($job->completed_at)); ?>
                                        <?php endif; ?>
                                    </small>
                                    <?php if (!empty($job->message)): ?>
                                        <br><small style="color:#666;"><?php echo esc_html($job->message); ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="margin: 0; color: #555;">No recent jobs found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    // Show debug info if there are active transcriptions
    $active_transcriptions = array();
    global $wpdb;
    $transient_keys = $wpdb->get_col(
        "SELECT option_name FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_ct_transcription_%' 
         AND option_name NOT LIKE '_transient_timeout_%'"
    );
    
    foreach ($transient_keys as $key) {
        $chapter_id = str_replace('_transient_ct_transcription_', '', $key);
        $status = get_transient('ct_transcription_' . $chapter_id);
        if ($status && $status['status'] === 'processing') {
            $active_transcriptions[$chapter_id] = $status;
        }
    }
    
    // Check if debug log exists
    $debug_log_path = WP_CONTENT_DIR . '/debug.log';
    $debug_log_exists = file_exists($debug_log_path);
    $debug_log_enabled = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
    
    if (!empty($active_transcriptions) || !$debug_log_enabled): ?>
        <div style="background: #fff3cd; padding: 15px; border: 1px solid #ffb900; border-radius: 5px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin-top: 0;">Debug Information</h3>
                <?php if (!empty($active_transcriptions)): ?>
                    <button type="button" id="ct-clear-all-stuck" class="button" style="background: #dc3232; color: #fff; border-color: #dc3232;">
                        Clear All Stuck Transcriptions
                    </button>
                <?php endif; ?>
            </div>
            
            <?php if (!$debug_log_enabled): ?>
                <div style="background: #fff; padding: 10px; margin-bottom: 10px; border-left: 3px solid #dc3232;">
                    <strong>Error Logging is Disabled</strong><br>
                    <p>To enable detailed transcription logs, add this to your <code>wp-config.php</code> file (before "That's all, stop editing!"):</p>
                    <pre style="background: #f0f0f1; padding: 10px; border-radius: 3px; overflow-x: auto;">define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false); // Don't show errors on frontend</pre>
                    <p><small>After adding this, logs will be saved to: <code><?php echo esc_html($debug_log_path); ?></code></small></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($active_transcriptions)): ?>
                <h4>Active Transcriptions:</h4>
                <?php foreach ($active_transcriptions as $chapter_id => $status): 
                    $elapsed = isset($status['started_at']) ? time() - $status['started_at'] : 0;
                    $minutes = floor($elapsed / 60);
                    $seconds = $elapsed % 60;
                    $is_stuck = $elapsed > 300 && $status['message'] === 'Initializing transcription process...'; // Stuck for >5 min
                ?>
                    <div style="margin-bottom: 10px; padding: 10px; background: #fff; border-left: 3px solid <?php echo $is_stuck ? '#dc3232' : '#ffb900'; ?>;">
                        <strong>Chapter ID: <?php echo esc_html($chapter_id); ?></strong>
                        <button type="button" class="button button-small ct-view-logs" data-chapter-id="<?php echo esc_attr($chapter_id); ?>" style="margin-left: 10px;">
                            View Logs
                        </button>
                        <button type="button" class="button button-small ct-export-debug" data-chapter-id="<?php echo esc_attr($chapter_id); ?>" style="margin-left: 5px;" title="Export copyable debug log">
                            Export Debug
                        </button>
                        <?php if ($is_stuck): ?>
                            <button type="button" class="button button-small ct-retry-transcription" data-chapter-id="<?php echo esc_attr($chapter_id); ?>" style="margin-left: 10px; background: #dc3232; color: #fff;">
                                Retry Now
                            </button>
                        <?php endif; ?>
                        <br>
                        <strong>Status:</strong> <?php echo esc_html($status['status']); ?><br>
                        <strong>Message:</strong> <?php echo esc_html($status['message']); ?><br>
                        <strong>Elapsed Time:</strong> <?php echo $minutes; ?>m <?php echo $seconds; ?>s
                        <?php if ($is_stuck): ?>
                            <span style="color: #dc3232; font-weight: bold;">STUCK - Click Retry Now</span>
                        <?php endif; ?>
                        <br>
                        <?php if (isset($status['time_formatted'])): ?>
                            <strong>Last Update:</strong> <?php echo esc_html($status['time_formatted']); ?><br>
                        <?php endif; ?>
                        <div class="ct-log-viewer" id="ct-logs-<?php echo esc_attr($chapter_id); ?>" style="display: none; margin-top: 10px; padding: 10px; background: #f0f0f1; border-radius: 3px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 11px;">
                            <div class="ct-log-content">Loading logs...</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="cost-summary" style="background: #f0f0f1; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin-top: 0;">Cost Summary</h3>
        <p><strong>Total Spent:</strong> $<?php echo number_format($total_cost, 4); ?></p>
        <p><small>OpenAI Whisper API: $0.006 per minute of audio</small></p>
    </div>
    
    <?php 
    // Generate pagination links (used in both top and bottom)
    $pagination_links = '';
    if ($pagination_data['total_items'] > 0) {
        $base_url = admin_url('admin.php?page=chapter-transcription');
        
        // Preserve filter in pagination
        if ($pagination_data['filter_lesson_id'] > 0) {
            $base_url = add_query_arg('filter_lesson', $pagination_data['filter_lesson_id'], $base_url);
        }
        
        $pagination_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%', $base_url),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => $pagination_data['total_pages'],
            'current' => $pagination_data['current_page'],
            'type' => 'plain'
        ));
    }
    ?>
    
    <?php if ($pagination_data['total_items'] > 0): ?>
        <div class="tablenav top">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php 
                    $start = ($pagination_data['current_page'] - 1) * $pagination_data['per_page'] + 1;
                    $end = min($pagination_data['current_page'] * $pagination_data['per_page'], $pagination_data['total_items']);
                    echo sprintf(
                        'Showing %d-%d of %d chapters',
                        $start,
                        $end,
                        $pagination_data['total_items']
                    );
                    ?>
                </span>
                <?php echo $pagination_links; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" id="ct-select-all" class="button">Select All</button>
            <button type="button" id="ct-select-none" class="button">Select None</button>
            <span style="margin-left: 10px; font-weight: 600;" id="ct-selected-count">0 selected</span>
            <div style="margin-left: auto; display: flex; gap: 10px;">
                <button type="button" id="ct-bulk-download" class="button button-primary" disabled>
                    Bulk Download MP4
                </button>
                <button type="button" id="ct-bulk-transcribe" class="button button-primary" disabled>
                    Bulk Transcribe
                </button>
            </div>
        </div>
        <div id="ct-bulk-progress" style="margin-top: 15px; display: none;">
            <div style="background: #f0f0f1; border-radius: 3px; padding: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span id="ct-bulk-status">Processing...</span>
                    <span id="ct-bulk-progress-text">0 / 0</span>
                </div>
                <div style="background: #fff; border-radius: 3px; height: 20px; overflow: hidden;">
                    <div id="ct-bulk-progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 40px;"><input type="checkbox" id="ct-select-all-checkbox"></th>
                <th style="width: 50px;">ID</th>
                <th style="min-width: 150px;">Chapter Title</th>
                <th style="min-width: 200px;">Lesson Title</th>
                <th style="width: 70px;">Lesson ID</th>
                <th style="width: 100px;">Video Source</th>
                <th style="width: 80px;">Preview</th>
                <th style="width: 140px;">Status</th>
                <th style="width: 100px;">Has Transcript</th>
                <th style="min-width: 200px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($chapters)): ?>
                <tr>
                    <td colspan="10">No chapters with video sources found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($chapters as $chapter): 
                    $has_transcript = !empty($chapter->transcript_id);
                    $estimated_cost = $chapter->duration > 0 ? ($chapter->duration / 60) * 0.006 : 0;
                    $video_source = isset($chapter->video_source) ? $chapter->video_source : 'none';
                ?>
                    <tr data-chapter-id="<?php echo esc_attr($chapter->ID); ?>">
                        <td>
                            <input type="checkbox" class="ct-chapter-checkbox" value="<?php echo esc_attr($chapter->ID); ?>">
                        </td>
                        <td style="font-size: 12px;"><?php echo esc_html($chapter->ID); ?></td>
                        <td style="font-size: 13px;"><?php echo esc_html($chapter->chapter_title); ?></td>
                        <td style="font-size: 12px;"><strong><?php echo esc_html($chapter->lesson_title ?: 'N/A'); ?></strong></td>
                        <td style="font-size: 12px;"><?php echo esc_html($chapter->lesson_id); ?></td>
                        <td style="font-size: 12px;">
                            <?php if ($video_source === 'bunny'): ?>
                                <span style="color: #2271b1;">Bunny</span>
                            <?php elseif ($video_source === 'vimeo'): ?>
                                <span style="color: #1ab7ea;">Vimeo</span>
                            <?php else: ?>
                                <span style="color: #dc3232;">None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $preview_url = '';
                            if ($video_source === 'bunny' && !empty($chapter->bunny_url)) {
                                $preview_url = $chapter->bunny_url;
                            } elseif ($video_source === 'vimeo' && !empty($chapter->vimeo_id)) {
                                $preview_url = 'https://vimeo.com/' . intval($chapter->vimeo_id);
                            }
                            
                            if ($preview_url): ?>
                                <a href="<?php echo esc_url($preview_url); ?>" target="_blank" class="button button-small" title="Preview video in new tab" style="font-size: 11px; padding: 2px 8px;">
                                    Preview
                                </a>
                            <?php else: ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 12px; line-height: 1.6;">
                            <div><strong>Duration:</strong> <?php echo $chapter->duration > 0 ? gmdate('H:i:s', $chapter->duration) : 'N/A'; ?></div>
                            <div><strong>Cost:</strong> $<?php echo number_format($estimated_cost, 4); ?></div>
                            <div style="color: <?php echo isset($chapter->mp4_exists) && $chapter->mp4_exists ? '#46b450' : '#dc3232'; ?>; font-weight: <?php echo isset($chapter->mp4_exists) && $chapter->mp4_exists ? '600' : 'normal'; ?>;">
                                <strong>MP4:</strong> <?php echo isset($chapter->mp4_exists) && $chapter->mp4_exists ? 'Downloaded' : 'Not Downloaded'; ?>
                            </div>
                        </td>
                        <td style="color: <?php echo $has_transcript ? '#46b450' : '#dc3232'; ?>; font-weight: <?php echo $has_transcript ? '600' : 'normal'; ?>; font-size: 12px; text-align: center;">
                            <?php echo $has_transcript ? 'Yes' : 'No'; ?>
                        </td>
                        <td style="min-width: 200px;">
                            <?php if ($has_transcript): ?>
                                <div style="background: #fff3cd; border-left: 4px solid #ffb900; padding: 8px; margin-bottom: 8px; font-size: 12px;">
                                    Transcript exists
                                </div>
                            <?php endif; ?>
                            
                            <?php
                            // Build action URLs with pagination and filter
                            $base_params = array(
                                'chapter_id' => $chapter->ID,
                                'paged' => $pagination_data['current_page']
                            );
                            
                            if ($pagination_data['filter_lesson_id'] > 0) {
                                $base_params['filter_lesson'] = $pagination_data['filter_lesson_id'];
                            }
                            
                            // Download button
                            $download_params = array_merge($base_params, array('action' => 'download'));
                            $download_url = add_query_arg($download_params, admin_url('admin.php?page=chapter-transcription'));
                            ?>
                            
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                <a href="<?php echo esc_url(wp_nonce_url($download_url, 'download_chapter_' . $chapter->ID)); ?>" 
                                   class="button button-small <?php echo (isset($chapter->mp4_exists) && $chapter->mp4_exists) ? '' : 'button-primary'; ?>"
                                   style="font-size: 11px; padding: 2px 8px; line-height: 1.4;">
                                    <?php echo (isset($chapter->mp4_exists) && $chapter->mp4_exists) ? 'Re-download' : 'Download'; ?>
                                </a>
                                
                                <?php if (isset($chapter->mp4_exists) && $chapter->mp4_exists): ?>
                                    <button type="button" 
                                            class="button button-primary button-small ct-transcribe-btn" 
                                            data-chapter-id="<?php echo esc_attr($chapter->ID); ?>"
                                            style="font-size: 11px; padding: 2px 8px; line-height: 1.4;">
                                        <?php echo $has_transcript ? 'Re-transcribe' : 'Transcribe'; ?>
                                    </button>
                                    <div class="ct-transcription-status" style="margin-top: 3px; font-size: 11px;"></div>
                                <?php else: ?>
                                    <span class="button button-small" style="opacity: 0.5; cursor: not-allowed; font-size: 11px; padding: 2px 8px;" title="Download MP4 first">
                                        Transcribe
                                    </span>
                                <?php endif; ?>
                                
                                <button type="button" 
                                        class="button button-secondary button-small ct-download-transcribe-btn" 
                                        data-chapter-id="<?php echo esc_attr($chapter->ID); ?>"
                                        style="font-size: 11px; padding: 2px 8px; line-height: 1.4;">
                                    Download & Transcribe
                                </button>
                                <div class="ct-download-transcribe-status" style="margin-top: 3px; font-size: 11px;"></div>
                                
                                <button type="button" 
                                        class="button button-small ct-export-debug" 
                                        data-chapter-id="<?php echo esc_attr($chapter->ID); ?>"
                                        style="font-size: 11px; padding: 2px 8px; line-height: 1.4;"
                                        title="Export copyable debug log for troubleshooting">
                                    Debug
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
    </table>
    
    <?php if ($pagination_data['total_items'] > 0): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php 
                    $start = ($pagination_data['current_page'] - 1) * $pagination_data['per_page'] + 1;
                    $end = min($pagination_data['current_page'] * $pagination_data['per_page'], $pagination_data['total_items']);
                    echo sprintf(
                        'Showing %d-%d of %d chapters',
                        $start,
                        $end,
                        $pagination_data['total_items']
                    );
                    ?>
                </span>
                <?php echo $pagination_links; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($cost_log)): ?>
        <h2>Recent Cost Log</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Chapter ID</th>
                    <th>Duration (min)</th>
                    <th>Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cost_log as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log->created_at); ?></td>
                        <td><?php echo esc_html($log->chapter_id); ?></td>
                        <td><?php echo number_format($log->duration_minutes, 2); ?></td>
                        <td>$<?php echo number_format($log->cost, 4); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

