(function () {
    'use strict';

    function padUnit(n) {
        return String(n);
    }

    function formatRemaining(ms) {
        if (ms <= 0) {
            return null;
        }
        var totalSec = Math.floor(ms / 1000);
        var d = Math.floor(totalSec / 86400);
        var h = Math.floor((totalSec % 86400) / 3600);
        var m = Math.floor((totalSec % 3600) / 60);
        var s = totalSec % 60;
        return padUnit(d) + 'd ' + padUnit(h) + 'h ' + padUnit(m) + 'm ' + padUnit(s) + 's';
    }

    function hideBanner(el) {
        if (el && el.parentNode) {
            el.style.display = 'none';
            el.setAttribute('data-je-sb-hidden', '1');
        }
    }

    function bindBanner(el) {
        var endRaw = el.getAttribute('data-je-sb-end');
        if (!endRaw) {
            return;
        }
        var endSec = parseInt(endRaw, 10);
        if (!isFinite(endSec)) {
            return;
        }
        var endMs = endSec * 1000;
        var timerEl = el.querySelector('[data-je-sb-timer-value]');

        function tick() {
            var now = Date.now();
            if (now >= endMs) {
                hideBanner(el);
                return;
            }
            var txt = formatRemaining(endMs - now);
            if (timerEl && txt) {
                timerEl.textContent = '\u23f1 ' + txt;
            }
        }

        tick();
        setInterval(tick, 1000);
    }

    function bindCopyButtons(root) {
        root.querySelectorAll('[data-je-sb-copy]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var code = btn.getAttribute('data-je-sb-copy') || '';
                var tip = btn.querySelector('[data-je-sb-tip]');
                function showTip() {
                    if (!tip) {
                        return;
                    }
                    tip.hidden = false;
                    window.setTimeout(function () {
                        tip.hidden = true;
                    }, 1600);
                }
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(code).then(showTip).catch(function () {
                        window.prompt('Copy:', code);
                    });
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = code;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'absolute';
                    ta.style.left = '-9999px';
                    document.body.appendChild(ta);
                    ta.select();
                    try {
                        document.execCommand('copy');
                        showTip();
                    } catch (e) {
                        window.prompt('Copy:', code);
                    }
                    document.body.removeChild(ta);
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.je-sale-banner[data-je-sb-end]').forEach(function (el) {
            var endRaw = el.getAttribute('data-je-sb-end');
            var endSec = parseInt(endRaw, 10);
            if (isFinite(endSec) && Date.now() >= endSec * 1000) {
                hideBanner(el);
                return;
            }
            bindBanner(el);
        });
        bindCopyButtons(document);
    });
})();
