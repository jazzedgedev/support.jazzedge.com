jQuery(document).ready(function($) {

    $(document).on('click', '#jem-purchase-btn, .jem-btn-purchase[data-lead]', function() {
        var leadId = $(this).data('lead');
        var funnelId = $(this).data('funnel');
        if (leadId && funnelId) {
            $.post(jemAjax.ajaxurl, {
                action: 'jem_track_event',
                nonce: jemAjax.nonce,
                lead_id: leadId,
                funnel_id: funnelId,
                event: 'purchase_click'
            });
        }
    });

    $(document).on('click', '#jem-download-btn', function() {
        var leadId = $(this).data('lead');
        var funnelId = $(this).data('funnel');
        if (leadId && funnelId) {
            $.post(jemAjax.ajaxurl, {
                action: 'jem_track_event',
                nonce: jemAjax.nonce,
                lead_id: leadId,
                funnel_id: funnelId,
                event: 'download_click'
            });
        }
    });

    $('#jem-optin-form').on('submit', function(e) {
        e.preventDefault();

        var $form    = $(this);
        var $btn     = $form.find('.jem-submit-btn');
        var $message = $('#jem-form-message');

        $btn.prop('disabled', true).text('Please wait...');
        $message.hide().removeClass('success error');

        $.ajax({
            url:  jemAjax.ajaxurl,
            type: 'POST',
            data: {
                action:     'jem_optin',
                nonce:      jemAjax.nonce,
                invite_code: $form.find('input[name="invite_code"]').val(),
                first_name: $form.find('input[name="first_name"]').val(),
                last_name:  $form.find('input[name="last_name"]').val(),
                email:      $form.find('input[name="email"]').val(),
                jem_hp:     $form.find('input[name="jem_hp"]').val()
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect;
                } else {
                    $message.addClass('error').text(response.data.message).show();
                    $btn.prop('disabled', false).text('Get Free Access →');
                }
            },
            error: function() {
                $message.addClass('error').text('Something went wrong. Please try again.').show();
                $btn.prop('disabled', false).text('Get Free Access →');
            }
        });
    });

});
