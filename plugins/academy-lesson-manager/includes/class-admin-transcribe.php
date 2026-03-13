<?php
/**
 * Transcribe Admin Class
 *
 * Handles audio upload and transcription via Whisper API
 *
 * @package Academy_Lesson_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Transcribe {

    private $wpdb;
    private $database;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->database = new ALM_Database();
        $this->table_name = $this->database->get_table_name('whisper_transcripts');
    }

    /**
     * Render the Transcribe page
     */
    public function render_page() {
        $api_key = get_option('katahdin_ai_hub_openai_key', '');
        if (empty($api_key)) {
            $api_key = get_option('fluent_support_ai_openai_key', '');
        }

        echo '<div class="wrap alm-transcribe-wrap">';
        echo '<h1>' . esc_html__('Transcribe', 'academy-lesson-manager') . '</h1>';
        echo '<p class="description">' . esc_html__('Upload an audio file to transcribe using OpenAI Whisper API. Choose text or WebVTT output format.', 'academy-lesson-manager') . '</p>';

        if (empty($api_key)) {
            echo '<div class="notice notice-error"><p>' . esc_html__('OpenAI API key not configured. Please set it in Katahdin AI Hub or Fluent Support AI settings.', 'academy-lesson-manager') . '</p></div>';
        } else {
            $this->render_upload_form();
        }

        $this->render_saved_transcripts();
        echo '</div>';

        $this->enqueue_scripts();
    }

    /**
     * Render upload form
     */
    private function render_upload_form() {
        ?>
        <div class="alm-transcribe-upload card" style="max-width: 600px; padding: 20px; margin: 20px 0;">
            <h2><?php esc_html_e('Upload Audio', 'academy-lesson-manager'); ?></h2>
            <form id="alm-transcribe-form" enctype="multipart/form-data">
                <?php wp_nonce_field('alm_transcribe_upload', 'alm_transcribe_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="alm-audio-file"><?php esc_html_e('Audio File', 'academy-lesson-manager'); ?></label></th>
                        <td>
                            <input type="file" id="alm-audio-file" name="audio_file" accept="audio/*,video/mp4,video/webm" required />
                            <p class="description"><?php esc_html_e('MP3, M4A, WAV, MP4, WebM. Max 25 MB.', 'academy-lesson-manager'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="alm-output-format"><?php esc_html_e('Output Format', 'academy-lesson-manager'); ?></label></th>
                        <td>
                            <select id="alm-output-format" name="output_format">
                                <option value="text"><?php esc_html_e('Plain Text', 'academy-lesson-manager'); ?></option>
                                <option value="vtt"><?php esc_html_e('WebVTT', 'academy-lesson-manager'); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e('WebVTT includes timestamps for subtitles.', 'academy-lesson-manager'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" id="alm-transcribe-submit">
                        <?php esc_html_e('Transcribe', 'academy-lesson-manager'); ?>
                    </button>
                    <span class="alm-transcribe-spinner" style="display:none; margin-left: 10px;">
                        <span class="spinner is-active" style="float:none;"></span>
                        <span class="alm-transcribe-status"><?php esc_html_e('Processing...', 'academy-lesson-manager'); ?></span>
                    </span>
                </p>
                <div id="alm-transcribe-message" class="notice" style="display:none; margin-top: 10px;"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Render list of saved transcripts
     */
    private function render_saved_transcripts() {
        $transcripts = $this->get_transcripts();

        echo '<div class="alm-transcribe-list card" style="max-width: 900px; padding: 20px; margin: 20px 0;">';
        echo '<h2>' . esc_html__('Saved Transcripts', 'academy-lesson-manager') . '</h2>';

        if (empty($transcripts)) {
            echo '<p class="description">' . esc_html__('No transcripts yet. Upload an audio file above to get started.', 'academy-lesson-manager') . '</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>' . esc_html__('File', 'academy-lesson-manager') . '</th>';
            echo '<th>' . esc_html__('Format', 'academy-lesson-manager') . '</th>';
            echo '<th>' . esc_html__('Date', 'academy-lesson-manager') . '</th>';
            echo '<th style="width: 180px;">' . esc_html__('Actions', 'academy-lesson-manager') . '</th>';
            echo '</tr></thead><tbody>';

            foreach ($transcripts as $t) {
                $created = mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $t->created_at);
                $preview = wp_trim_words(strip_tags($t->content), 15);
                echo '<tr data-id="' . esc_attr($t->ID) . '">';
                echo '<td><strong>' . esc_html($t->file_name) . '</strong><br><small style="color:#666;">' . esc_html($preview) . '</small></td>';
                echo '<td>' . esc_html(strtoupper($t->output_format)) . '</td>';
                echo '<td>' . esc_html($created) . '</td>';
                echo '<td>';
                echo '<button type="button" class="button button-small alm-copy-transcript" data-id="' . esc_attr($t->ID) . '" title="' . esc_attr__('Copy to clipboard', 'academy-lesson-manager') . '">' . esc_html__('Copy', 'academy-lesson-manager') . '</button> ';
                echo '<button type="button" class="button button-small alm-view-transcript" data-id="' . esc_attr($t->ID) . '" title="' . esc_attr__('View full transcript', 'academy-lesson-manager') . '">' . esc_html__('View', 'academy-lesson-manager') . '</button> ';
                echo '<button type="button" class="button button-small button-link-delete alm-delete-transcript" data-id="' . esc_attr($t->ID) . '" title="' . esc_attr__('Delete', 'academy-lesson-manager') . '">' . esc_html__('Delete', 'academy-lesson-manager') . '</button>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        }

        echo '</div>';

        // Modal for viewing full transcript
        ?>
        <div id="alm-transcript-modal" class="alm-modal" style="display:none;">
            <div class="alm-modal-backdrop"></div>
            <div class="alm-modal-content" style="max-width: 700px; max-height: 80vh; overflow: auto; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                <h3 class="alm-modal-title"></h3>
                <pre id="alm-transcript-content" style="white-space: pre-wrap; word-wrap: break-word; max-height: 400px; overflow-y: auto; background: #f6f7f7; padding: 15px; border-radius: 4px;"></pre>
                <p class="alm-modal-actions">
                    <button type="button" class="button alm-copy-from-modal"><?php esc_html_e('Copy', 'academy-lesson-manager'); ?></button>
                    <button type="button" class="button alm-close-modal"><?php esc_html_e('Close', 'academy-lesson-manager'); ?></button>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Get all saved transcripts
     */
    private function get_transcripts() {
        if (!$this->table_name) {
            return array();
        }
        return $this->wpdb->get_results(
            "SELECT ID, file_name, output_format, content, created_at FROM {$this->table_name} ORDER BY created_at DESC"
        );
    }

    /**
     * Enqueue scripts
     */
    private function enqueue_scripts() {
        ?>
        <style>
            .alm-transcribe-wrap .card { box-shadow: 0 1px 1px rgba(0,0,0,.04); }
            .alm-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 100000; display: flex; align-items: center; justify-content: center; }
            .alm-modal-backdrop { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); }
            .alm-modal-content { position: relative; z-index: 1; margin: 20px; }
            .alm-modal-actions { margin-top: 15px; }
        </style>
        <script>
        jQuery(function($) {
            var $form = $('#alm-transcribe-form');
            var $spinner = $('.alm-transcribe-spinner');
            var $status = $('.alm-transcribe-status');
            var $message = $('#alm-transcribe-message');
            var $submit = $('#alm-transcribe-submit');

            $form.on('submit', function(e) {
                e.preventDefault();
                var fileInput = $('#alm-audio-file')[0];
                if (!fileInput.files.length) {
                    showMessage('error', '<?php echo esc_js(__('Please select an audio file.', 'academy-lesson-manager')); ?>');
                    return;
                }
                var formData = new FormData();
                formData.append('action', 'alm_transcribe_upload');
                formData.append('nonce', '<?php echo esc_js(wp_create_nonce('alm_transcribe_upload')); ?>');
                formData.append('audio_file', fileInput.files[0]);
                formData.append('output_format', $('#alm-output-format').val());

                $submit.prop('disabled', true);
                $spinner.show();
                $status.text('<?php echo esc_js(__('Uploading and transcribing... This may take a few minutes.', 'academy-lesson-manager')); ?>');
                $message.hide();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 300000,
                    success: function(res) {
                        $spinner.hide();
                        $submit.prop('disabled', false);
                        if (res.success) {
                            showMessage('success', res.data.message);
                            setTimeout(function() { location.reload(); }, 1500);
                        } else {
                            showMessage('error', res.data && res.data.message ? res.data.message : '<?php echo esc_js(__('An error occurred.', 'academy-lesson-manager')); ?>');
                        }
                    },
                    error: function(xhr, status, err) {
                        $spinner.hide();
                        $submit.prop('disabled', false);
                        var msg = status === 'timeout' ? '<?php echo esc_js(__('Request timed out. Try a shorter file.', 'academy-lesson-manager')); ?>' : (err || '<?php echo esc_js(__('Request failed.', 'academy-lesson-manager')); ?>');
                        showMessage('error', msg);
                    }
                });
            });

            function showMessage(type, text) {
                $message.removeClass('notice-error notice-success').addClass('notice-' + type).html('<p>' + text + '</p>').show();
            }

            $(document).on('click', '.alm-copy-transcript', function() {
                var id = $(this).data('id');
                $.post(ajaxurl, { action: 'alm_get_transcript', nonce: '<?php echo esc_js(wp_create_nonce('alm_transcribe_upload')); ?>', id: id }, function(res) {
                    if (res.success && res.data.content) {
                        copyToClipboard(res.data.content);
                        var $btn = $('.alm-copy-transcript[data-id="' + id + '"]');
                        var orig = $btn.text();
                        $btn.text('<?php echo esc_js(__('Copied!', 'academy-lesson-manager')); ?>');
                        setTimeout(function() { $btn.text(orig); }, 2000);
                    }
                });
            });

            $(document).on('click', '.alm-view-transcript', function() {
                var id = $(this).data('id');
                $.post(ajaxurl, { action: 'alm_get_transcript', nonce: '<?php echo esc_js(wp_create_nonce('alm_transcribe_upload')); ?>', id: id }, function(res) {
                    if (res.success && res.data) {
                        $('#alm-transcript-content').text(res.data.content);
                        $('.alm-modal-title').text(res.data.file_name);
                        $('#alm-transcript-modal').data('content', res.data.content).show();
                    }
                });
            });

            $(document).on('click', '.alm-copy-from-modal', function() {
                var content = $('#alm-transcript-modal').data('content');
                if (content) copyToClipboard(content);
            });

            $('.alm-close-modal, .alm-modal-backdrop').on('click', function() {
                $('#alm-transcript-modal').hide();
            });

            $(document).on('click', '.alm-delete-transcript', function() {
                if (!confirm('<?php echo esc_js(__('Delete this transcript?', 'academy-lesson-manager')); ?>')) return;
                var id = $(this).data('id');
                var $row = $(this).closest('tr');
                $.post(ajaxurl, { action: 'alm_delete_transcript', nonce: '<?php echo esc_js(wp_create_nonce('alm_transcribe_upload')); ?>', id: id }, function(res) {
                    if (res.success) $row.fadeOut(function() { $(this).remove(); });
                });
            });

            function copyToClipboard(text) {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
        });
        </script>
        <?php
    }
}
