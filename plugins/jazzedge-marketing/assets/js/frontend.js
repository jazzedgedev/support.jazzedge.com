/**
 * JEM Marketing - Frontend form AJAX submit
 */
(function ($) {
    'use strict';

    $(function () {
        $('.jem-optin-form').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('.jem-submit-btn, .jem-submit');
            var $msg = $form.siblings('.jem-message').length ? $form.siblings('.jem-message') : $form.find('.jem-message');
            var funnelId = $form.data('funnel-id');

            $msg.hide().removeClass('jem-error jem-success').text('');

            if (!window.jemData || !window.jemData.ajaxUrl || !window.jemData.nonce) {
                $msg.addClass('jem-error').text('Configuration error. Please refresh the page.').show();
                return;
            }

            $btn.prop('disabled', true);

            $.ajax({
                url: window.jemData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'jem_optin',
                    nonce: window.jemData.nonce,
                    funnel_id: funnelId,
                    first_name: $form.find('[name="first_name"]').val(),
                    last_name: $form.find('[name="last_name"]').val(),
                    email: $form.find('[name="email"]').val(),
                    jem_hp: $form.find('[name="jem_hp"]').val()
                },
                success: function (response) {
                    if (response.success && response.data && response.data.redirect) {
                        window.location.href = response.data.redirect;
                        return;
                    }
                    $msg.addClass('jem-error').text(response.data && response.data.message ? response.data.message : 'An error occurred.').show();
                    $btn.prop('disabled', false);
                },
                error: function (xhr, status, err) {
                    $msg.addClass('jem-error').text('An error occurred. Please try again.').show();
                    $btn.prop('disabled', false);
                }
            });
        });
    });
})(jQuery);
