(function () {
    'use strict';

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function contrastText(hex) {
        var h = String(hex || '').replace('#', '');
        if (h.length !== 6 || /[^0-9a-f]/i.test(h)) {
            return '#ffffff';
        }
        var r = parseInt(h.slice(0, 2), 16);
        var g = parseInt(h.slice(2, 4), 16);
        var b = parseInt(h.slice(4, 6), 16);
        var yiq = (r * 299 + g * 587 + b * 114) / 1000;
        return yiq >= 160 ? '#002A34' : '#ffffff';
    }

    function formatSale(type, amount) {
        var n = parseFloat(amount);
        if (!isFinite(n)) {
            n = 0;
        }
        var rounded = Math.round(n);
        if (type === 'dollar') {
            return '$' + rounded + ' OFF';
        }
        return rounded + '% OFF';
    }

    function parseEndMsFromLocalInput(val) {
        if (!val) {
            return null;
        }
        var d = new Date(val);
        if (isNaN(d.getTime())) {
            return null;
        }
        return d.getTime();
    }

    function formatRemaining(ms) {
        if (ms <= 0) {
            return '\u23f1 0d 0h 0m 0s';
        }
        var totalSec = Math.floor(ms / 1000);
        var d = Math.floor(totalSec / 86400);
        var h = Math.floor((totalSec % 86400) / 3600);
        var m = Math.floor((totalSec % 3600) / 60);
        var s = totalSec % 60;
        return '\u23f1 ' + d + 'd ' + h + 'h ' + m + 'm ' + s + 's';
    }

    function getForm() {
        return document.querySelector('.je-sb-form');
    }

    function readFields(form) {
        var titleEl = form.querySelector('#je_sb_title');
        var descEl = form.querySelector('#je_sb_description');
        var typeEl = form.querySelector('input[name="sale_type"]:checked');
        var amtEl = form.querySelector('#je_sb_amount');
        var ctaLabelEl = form.querySelector('#je_sb_cta_label');
        var ctaUrlEl = form.querySelector('#je_sb_cta_url');
        var tplEl = form.querySelector('input[name="template"]:checked');
        var endEl = form.querySelector('#je_sb_end');
        var couponEl = form.querySelector('#je_sb_coupon');
        var colorEl = form.querySelector('#je_sb_coupon_color');

        return {
            title: titleEl ? titleEl.value : '',
            description: descEl ? descEl.value : '',
            saleType: typeEl ? typeEl.value : 'percent',
            saleAmount: amtEl ? amtEl.value : '0',
            ctaLabel: ctaLabelEl && ctaLabelEl.value ? ctaLabelEl.value : (window.jeSbAdmin && jeSbAdmin.defaults ? jeSbAdmin.defaults.ctaLabel : 'Shop Now'),
            ctaUrl: ctaUrlEl && ctaUrlEl.value ? ctaUrlEl.value : (window.jeSbAdmin && jeSbAdmin.defaults ? jeSbAdmin.defaults.ctaUrl : '#'),
            template: tplEl ? tplEl.value : '1',
            endLocal: endEl ? endEl.value : '',
            coupon: couponEl ? couponEl.value.trim() : '',
            couponColor: colorEl && colorEl.value ? colorEl.value : (window.jeSbAdmin && jeSbAdmin.defaults ? jeSbAdmin.defaults.couponColor : '#F04E23')
        };
    }

    function buildPreviewHtml(fields, timerText) {
        var tpl = Math.max(1, Math.min(6, parseInt(fields.template, 10) || 1));
        var saleLabel = formatSale(fields.saleType, fields.saleAmount);
        var coupon = fields.coupon;
        var fg = contrastText(fields.couponColor);
        var couponBlock = '';
        if (coupon) {
            couponBlock =
                '<span class="je-sale-banner__coupon" style="background-color:' +
                esc(fields.couponColor) +
                ';color:' +
                esc(fg) +
                '">' +
                '<span class="je-sale-banner__coupon-label">' +
                esc(window.jeSbAdmin && jeSbAdmin.i18n ? jeSbAdmin.i18n.useCode : 'Use Code:') +
                '</span>' +
                '<span class="je-sale-banner__coupon-code">' +
                esc(coupon.toUpperCase()) +
                '</span>' +
                '<button type="button" class="je-sale-banner__coupon-copy" disabled aria-disabled="true">' +
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>' +
                '</button></span>';
        }

        var innerNoCoupon = coupon ? '' : ' je-sale-banner__inner--no-coupon';
        var couponDivider = coupon
            ? '<span class="je-sale-banner__divider" aria-hidden="true"></span><div class="je-sale-banner__group je-sale-banner__group--coupon">' +
              couponBlock +
              '</div>'
            : '';

        return (
            '<div class="je-sale-banner je-sale-banner--tpl-' +
            tpl +
            ' je-sale-banner--ctx-preview" data-je-sb-active="1">' +
            '<div class="je-sale-banner__inner' +
            innerNoCoupon +
            '">' +
            '<div class="je-sale-banner__bar">' +
            '<div class="je-sale-banner__group je-sale-banner__group--lead">' +
            '<span class="je-sale-banner__badge">SALE</span>' +
            '<span class="je-sale-banner__headline" style="overflow:hidden;white-space:nowrap;text-overflow:ellipsis;flex:1 1 auto;min-width:0;">' +
            '<span class="je-sale-banner__title">' +
            esc(fields.title || 'Banner title') +
            '</span>' +
            (fields.description
                ? '<span class="je-sale-banner__dot" aria-hidden="true">·</span><span class="je-sale-banner__desc">' +
                  esc(fields.description) +
                  '</span>'
                : '') +
            '</span>' +
            '</div>' +
            couponDivider +
            '<span class="je-sale-banner__divider" aria-hidden="true"></span>' +
            '<div class="je-sale-banner__group je-sale-banner__group--tail">' +
            '<span class="je-sale-banner__timer" aria-live="polite">' +
            '<span data-je-sb-timer-value>' +
            esc(timerText) +
            '</span></span>' +
            '<a class="je-sale-banner__cta" href="' +
            esc(fields.ctaUrl) +
            '">' +
            esc(fields.ctaLabel) +
            '</a>' +
            '<span class="je-sale-banner__amount je-sale-banner__amount--desktop">' +
            esc(saleLabel) +
            '</span>' +
            '</div></div></div></div>'
        );
    }

    function mountLivePreview() {
        var root = document.getElementById('je-sb-live-preview');
        var form = getForm();
        if (!root || !form) {
            return;
        }

        var timerId = null;

        function render() {
            var fields = readFields(form);
            var endMs = parseEndMsFromLocalInput(fields.endLocal);
            var now = Date.now();
            var remaining = endMs == null ? 86400000 : endMs - now;
            var timerText = formatRemaining(remaining);
            root.innerHTML = buildPreviewHtml(fields, timerText);
        }

        function startTimer() {
            if (timerId) {
                window.clearInterval(timerId);
            }
            timerId = window.setInterval(render, 1000);
        }

        form.addEventListener('input', render);
        form.addEventListener('change', render);
        render();
        startTimer();
    }

    function bindDeleteConfirm() {
        document.querySelectorAll('.je-sb-delete-link').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var msg = a.getAttribute('data-confirm') || 'Delete?';
                if (!window.confirm(msg)) {
                    e.preventDefault();
                }
            });
        });
    }

    function bindCopyShortcode() {
        var copyLabel =
            window.jeSbAdmin && jeSbAdmin.i18n && jeSbAdmin.i18n.copyShortcode
                ? jeSbAdmin.i18n.copyShortcode
                : 'Copy Shortcode';
        var copiedLabel =
            window.jeSbAdmin && jeSbAdmin.i18n && jeSbAdmin.i18n.copied ? jeSbAdmin.i18n.copied : 'Copied!';

        function fallbackCopy(text, onSuccess) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try {
                if (document.execCommand('copy')) {
                    onSuccess();
                }
            } catch (err) {}
            document.body.removeChild(ta);
        }

        function showCopied(btn) {
            var icon = btn.querySelector('.dashicons');
            if (icon) {
                icon.classList.remove('dashicons-clipboard');
                icon.classList.add('dashicons-yes');
            }
            btn.title = copiedLabel;
            btn.setAttribute('aria-label', copiedLabel);
            window.setTimeout(function () {
                if (icon) {
                    icon.classList.remove('dashicons-yes');
                    icon.classList.add('dashicons-clipboard');
                }
                btn.title = copyLabel;
                btn.setAttribute('aria-label', copyLabel);
            }, 1500);
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-je-sb-shortcode]');
            if (!btn) {
                return;
            }
            e.preventDefault();
            var text = btn.dataset.jeSbShortcode;
            if (!text) {
                return;
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard
                    .writeText(text)
                    .then(function () {
                        showCopied(btn);
                    })
                    .catch(function () {
                        fallbackCopy(text, function () {
                            showCopied(btn);
                        });
                    });
            } else {
                fallbackCopy(text, function () {
                    showCopied(btn);
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        mountLivePreview();
        bindDeleteConfirm();
        bindCopyShortcode();
    });
})();
