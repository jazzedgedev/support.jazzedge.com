<?php
/**
 * One-Off Transcription page template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>One-Off Transcription</h1>
    <p>Enter a Bunny CDN m3u8 URL to download the video, create a chapter record, and transcribe it.</p>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 5px; margin-top: 20px; box-shadow: 0 1px 1px rgba(0,0,0,.04); max-width: 800px;">
        <form id="ct-oneoff-form">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="bunny_url">Bunny m3u8 URL <span style="color: #d63638;">*</span></label>
                        </th>
                        <td>
                            <input type="url" 
                                   id="bunny_url" 
                                   name="bunny_url" 
                                   class="regular-text" 
                                   placeholder="https://vz-0696d3da-4b7.b-cdn.net/.../playlist.m3u8"
                                   required
                                   style="width: 100%; max-width: 600px;">
                            <p class="description">Enter the full m3u8 playlist URL from Bunny CDN</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="lesson_id">Lesson <span style="color: #d63638;">*</span></label>
                        </th>
                        <td>
                            <select id="lesson_id" name="lesson_id" class="regular-text" required style="width: 100%; max-width: 400px;">
                                <option value="">-- Select a Lesson --</option>
                                <?php foreach ($lessons as $lesson): ?>
                                    <option value="<?php echo esc_attr($lesson->ID); ?>">
                                        <?php echo esc_html($lesson->lesson_title); ?> (ID: <?php echo esc_html($lesson->ID); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Select the lesson this chapter belongs to</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="chapter_title">Chapter Title <span style="color: #d63638;">*</span></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="chapter_title" 
                                   name="chapter_title" 
                                   class="regular-text" 
                                   required
                                   style="width: 100%; max-width: 400px;">
                            <p class="description">Enter the chapter title</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="menu_order">Menu Order</label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="menu_order" 
                                   name="menu_order" 
                                   class="small-text" 
                                   value="0"
                                   min="0">
                            <p class="description">Order in which this chapter appears (0 = first)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="duration">Duration (seconds)</label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="duration" 
                                   name="duration" 
                                   class="small-text" 
                                   value="0"
                                   min="0">
                            <p class="description">Video duration in seconds (optional, will be detected if not provided)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="free">Free Chapter</label>
                        </th>
                        <td>
                            <select id="free" name="free" class="regular-text" style="width: auto;">
                                <option value="n">No</option>
                                <option value="y">Yes</option>
                            </select>
                            <p class="description">Whether this chapter is free to access</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary button-large" id="ct-oneoff-submit">
                    Create Chapter & Queue Transcription
                </button>
                <span class="spinner" id="ct-oneoff-spinner" style="float: none; margin-left: 10px; visibility: hidden;"></span>
            </p>
            
            <div id="ct-oneoff-message" style="margin-top: 15px; display: none;"></div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ct-oneoff-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submit = $('#ct-oneoff-submit');
        var $spinner = $('#ct-oneoff-spinner');
        var $message = $('#ct-oneoff-message');
        
        // Validate form
        var bunnyUrl = $('#bunny_url').val().trim();
        var lessonId = $('#lesson_id').val();
        var chapterTitle = $('#chapter_title').val().trim();
        
        if (!bunnyUrl) {
            showMessage('error', 'Please enter a Bunny URL');
            return;
        }
        
        if (bunnyUrl.indexOf('.m3u8') === -1) {
            showMessage('error', 'URL must be an m3u8 playlist URL');
            return;
        }
        
        if (!lessonId) {
            showMessage('error', 'Please select a lesson');
            return;
        }
        
        if (!chapterTitle) {
            showMessage('error', 'Please enter a chapter title');
            return;
        }
        
        // Disable form
        $submit.prop('disabled', true);
        $spinner.css('visibility', 'visible');
        $message.hide();
        
        // Submit via AJAX
        $.ajax({
            url: ctAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ct_oneoff_transcribe',
                nonce: ctAdmin.nonce,
                bunny_url: bunnyUrl,
                lesson_id: lessonId,
                chapter_title: chapterTitle,
                menu_order: $('#menu_order').val(),
                duration: $('#duration').val(),
                free: $('#free').val()
            },
            success: function(response) {
                $spinner.css('visibility', 'hidden');
                $submit.prop('disabled', false);
                
                if (response.success) {
                    showMessage('success', response.data.message + ' (Chapter ID: ' + response.data.chapter_id + ')');
                    $form[0].reset();
                    
                    // Optionally redirect to main transcription page
                    setTimeout(function() {
                        window.location.href = '<?php echo admin_url('admin.php?page=chapter-transcription'); ?>';
                    }, 2000);
                } else {
                    showMessage('error', response.data || 'An error occurred');
                }
            },
            error: function(xhr, status, error) {
                $spinner.css('visibility', 'hidden');
                $submit.prop('disabled', false);
                showMessage('error', 'AJAX error: ' + error);
            }
        });
    });
    
    function showMessage(type, message) {
        var $message = $('#ct-oneoff-message');
        $message.removeClass('notice notice-success notice-error')
                .addClass('notice notice-' + (type === 'success' ? 'success' : 'error'))
                .html('<p>' + message + '</p>')
                .show();
    }
});
</script>

