<?php
/**
 * Chapters bulk management page template
 */

if (!defined('ABSPATH')) {
    exit;
}

extract($template_data);
?>
<div class="wrap">
    <h1>Chapters - Bulk Management</h1>
    
    <div style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <form method="get" action="<?php echo admin_url('admin.php'); ?>" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <input type="hidden" name="page" value="chapter-transcription-chapters">
            <label>
                <input type="checkbox" name="hide_transcribed" value="1" <?php checked($hide_transcribed); ?> onchange="this.form.submit();">
                Hide chapters with transcripts
            </label>
            <label style="display: flex; align-items: center; gap: 5px;">
                <span>Records per page:</span>
                <select name="per_page" onchange="this.form.submit();" style="padding: 3px 5px;">
                    <?php foreach ($allowed_per_page as $option): ?>
                        <option value="<?php echo esc_attr($option); ?>" <?php selected($pagination['per_page'], $option); ?>>
                            <?php echo esc_html($option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if ($hide_transcribed): ?>
                <a href="<?php echo admin_url('admin.php?page=chapter-transcription-chapters&per_page=' . $pagination['per_page']); ?>" class="button">Show All</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 5px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
            <button type="button" id="ct-chapters-select-all" class="button">Select All</button>
            <button type="button" id="ct-chapters-select-none" class="button">Select None</button>
            <span style="margin-left: 10px; font-weight: 600;" id="ct-chapters-selected-count">0 selected</span>
            <div style="margin-left: auto;">
                <button type="button" id="ct-chapters-bulk-download-transcribe" class="button button-primary" disabled>
                    Bulk Download & Transcribe
                </button>
            </div>
        </div>
        <div id="ct-chapters-bulk-progress" style="display: none; margin-top: 15px;">
            <div style="background: #f0f0f1; border-radius: 3px; padding: 10px;">
                <div style="font-weight: 600; margin-bottom: 5px;">Processing...</div>
                <div id="ct-chapters-bulk-status" style="font-size: 12px; color: #666;"></div>
            </div>
        </div>
    </div>
    
    <div style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; border-radius: 5px;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="ct-chapters-select-all-checkbox"></th>
                    <th style="width: 80px;">ID</th>
                    <th style="width: 100px;">Lesson ID</th>
                    <th>Chapter Title</th>
                    <th>Lesson Title</th>
                    <th style="width: 100px;">Duration</th>
                    <th style="width: 120px;">Has Transcript</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($chapters)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">
                            <?php echo $hide_transcribed ? 'No chapters without transcripts found.' : 'No chapters found.'; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($chapters as $chapter): 
                        $has_transcript = isset($transcripted_chapters[$chapter->ID]);
                        $duration = $chapter->duration > 0 ? gmdate('H:i:s', $chapter->duration) : 'N/A';
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="ct-chapter-checkbox" value="<?php echo esc_attr($chapter->ID); ?>">
                            </td>
                            <td style="font-size: 12px;"><?php echo esc_html($chapter->ID); ?></td>
                            <td style="font-size: 12px;"><?php echo esc_html($chapter->lesson_id); ?></td>
                            <td style="font-size: 13px;"><?php echo esc_html($chapter->chapter_title); ?></td>
                            <td style="font-size: 12px;"><strong><?php echo esc_html($chapter->lesson_title ?: 'N/A'); ?></strong></td>
                            <td style="font-size: 12px;"><?php echo esc_html($duration); ?></td>
                            <td style="color: <?php echo $has_transcript ? '#46b450' : '#dc3232'; ?>; font-weight: <?php echo $has_transcript ? '600' : 'normal'; ?>; font-size: 12px; text-align: center;">
                                <?php echo $has_transcript ? 'Yes' : 'No'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom" style="margin-top: 15px;">
                <div class="tablenav-pages">
                    <?php
                    $base_url = admin_url('admin.php?page=chapter-transcription-chapters');
                    if ($hide_transcribed) {
                        $base_url = add_query_arg('hide_transcribed', '1', $base_url);
                    }
                    $base_url = add_query_arg('per_page', $pagination['per_page'], $base_url);
                    
                    $pagination_html = paginate_links(array(
                        'base' => add_query_arg('paged', '%#%', $base_url),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $total_pages,
                        'current' => $current_page
                    ));
                    echo $pagination_html;
                    ?>
                </div>
                <div style="margin-top: 10px; color: #666; font-size: 12px;">
                    Showing <?php echo number_format(($current_page - 1) * $per_page + 1); ?>-<?php echo number_format(min($current_page * $per_page, $total_items)); ?> of <?php echo number_format($total_items); ?> chapters
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update selected count
    function updateSelectedCount() {
        var count = $('.ct-chapter-checkbox:checked').length;
        $('#ct-chapters-selected-count').text(count + ' selected');
        $('#ct-chapters-bulk-download-transcribe').prop('disabled', count === 0);
    }
    
    // Select all checkboxes
    $('#ct-chapters-select-all, #ct-chapters-select-all-checkbox').on('click', function() {
        $('.ct-chapter-checkbox').prop('checked', true);
        $('#ct-chapters-select-all-checkbox').prop('checked', true);
        updateSelectedCount();
    });
    
    // Select none
    $('#ct-chapters-select-none').on('click', function() {
        $('.ct-chapter-checkbox').prop('checked', false);
        $('#ct-chapters-select-all-checkbox').prop('checked', false);
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $(document).on('change', '.ct-chapter-checkbox', function() {
        var allChecked = $('.ct-chapter-checkbox').length === $('.ct-chapter-checkbox:checked').length;
        $('#ct-chapters-select-all-checkbox').prop('checked', allChecked);
        updateSelectedCount();
    });
    
    // Bulk download and transcribe
    $('#ct-chapters-bulk-download-transcribe').on('click', function() {
        var chapterIds = $('.ct-chapter-checkbox:checked').map(function() {
            return parseInt($(this).val());
        }).get();
        
        if (chapterIds.length === 0) {
            alert('Please select at least one chapter');
            return;
        }
        
        var $button = $(this);
        var $progress = $('#ct-chapters-bulk-progress');
        var $status = $('#ct-chapters-bulk-status');
        
        $button.prop('disabled', true);
        $progress.show();
        $status.text('Starting bulk operation...');
        
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_bulk_download_and_transcribe',
                nonce: ctAdmin.nonce,
                chapter_ids: chapterIds
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<strong style="color: #46b450;">' + response.data.message + '</strong><br>' +
                        'Success: ' + response.data.success_count + ', Failed: ' + response.data.error_count);
                    $button.prop('disabled', false);
                    
                    // Refresh page after 3 seconds
                    setTimeout(function() {
                        window.location.reload();
                    }, 3000);
                } else {
                    $status.html('<strong style="color: #dc3232;">Error: ' + response.data + '</strong>');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                $status.html('<strong style="color: #dc3232;">AJAX error occurred</strong>');
                $button.prop('disabled', false);
            }
        });
    });
    
    // Initialize
    updateSelectedCount();
});
</script>

