/**
 * JEM Marketing - Admin shortcode copy + media picker
 */
(function ($) {
    'use strict';

    var mediaFrame;
    $(function () {
        $('#jem-media-button').on('click', function (e) {
            e.preventDefault();
            if (mediaFrame) {
                mediaFrame.open();
                return;
            }
            mediaFrame = wp.media({
                title: 'Select Sheet Music File',
                button: { text: 'Use this file' },
                multiple: false
            });
            mediaFrame.on('select', function () {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                $('#jem_media_id').val(attachment.id);
                var name = attachment.filename || (attachment.file && attachment.file.filename) || attachment.title || 'Selected';
                $('#jem-media-filename').text(name);
            });
            mediaFrame.open();
        });

        $(document).on('click', '.jem-copy-shortcode, .jem-copy-btn', function (e) {
            var $btn = $(e.target);
            var text = $btn.data('copy') || $btn.closest('.jem-shortcode-wrap').find('.jem-shortcode-input').val() || $btn.closest('.jem-shortcode-cell').find('input').val() || '';
            if (!text) return;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    $btn.text('Copied!');
                    setTimeout(function () { $btn.text('Copy'); }, 2000);
                });
            } else {
                var $input = $btn.closest('.jem-shortcode-cell, .jem-shortcode-wrap').find('input[type="text"]');
                if ($input.length) $input.select();
                document.execCommand('copy');
                $btn.text('Copied!');
                setTimeout(function () { $btn.text('Copy'); }, 2000);
            }
        });
    });
})(jQuery);
