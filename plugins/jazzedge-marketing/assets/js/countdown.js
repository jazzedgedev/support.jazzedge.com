/**
 * JEM Marketing - Thank you page countdown timer
 * Reads expiry from jemData.couponExpires (ISO 8601), uses jemData.serverNow for drift
 */
(function ($) {
    'use strict';

    function pad(n) {
        return n < 10 ? '0' + n : n;
    }

    function formatRemaining(secs) {
        if (secs <= 0) {
            return { days: 0, hours: 0, mins: 0, secs: 0, expired: true };
        }
        var d = Math.floor(secs / 86400);
        var h = Math.floor((secs % 86400) / 3600);
        var m = Math.floor((secs % 3600) / 60);
        var s = Math.floor(secs % 60);
        return { days: d, hours: h, mins: m, secs: s, expired: false };
    }

    $(function () {
        var jem = window.jemData || {};
        if (jem.expired || !jem.couponExpires) {
            if (!jem.expired) {
                $('#jem-countdown').closest('.jem-offer-section').addClass('jem-hidden');
                $('.jem-expired-section').removeClass('jem-hidden');
            }
            return;
        }

        var serverNow = jem.serverNow ? new Date(jem.serverNow) : new Date();
        var clientNow = new Date();
        var drift = (clientNow.getTime() - serverNow.getTime()) / 1000;

        var expiry = new Date(jem.couponExpires);
        var intervalId;

        function tick() {
            var now = new Date();
            var remaining = Math.max(0, (expiry.getTime() - now.getTime()) / 1000 - drift);
            var f = formatRemaining(remaining);
            if (f.expired || remaining <= 0) {
                clearInterval(intervalId);
                $('#jem-countdown').closest('.jem-offer-section').addClass('jem-hidden');
                $('#jem-purchase-btn').closest('p').addClass('jem-hidden');
                $('.jem-expired-section').removeClass('jem-hidden');
                return;
            }
            $('#jem-countdown').html(
                pad(f.days) + 'd ' + pad(f.hours) + 'h ' + pad(f.mins) + 'm ' + pad(f.secs) + 's'
            );
        }

        tick();
        intervalId = setInterval(tick, 1000);

        if (jem.productUrl && jem.leadId && jem.funnelId) {
            $('#jem-purchase-btn').on('click', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                $.post(jem.ajaxUrl, {
                    action: 'jem_track_purchase_click',
                    nonce: jem.nonce,
                    lead_id: jem.leadId,
                    funnel_id: jem.funnelId
                }).always(function () {
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        }
    });
})(jQuery);
