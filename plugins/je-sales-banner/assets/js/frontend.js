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

    var POPUP_SNOOZE_MS = 86400000; // 24 hours — do not show again until this elapses

    function popupStorageKey(id) {
        return 'je_sb_popup_' + id;
    }

    /**
     * @param {string} id
     * @param {string} campaignEnd data-je-sb-popup-end (unix seconds)
     */
    function isPopupSnoozed(id, campaignEnd) {
        var key = popupStorageKey(id);
        var raw;
        try {
            raw = localStorage.getItem(key);
        } catch (e) {
            return false;
        }
        if (raw == null || raw === '') {
            return false;
        }
        if (raw.indexOf('t:') === 0) {
            var ms = parseInt(raw.slice(2), 10);
            if (!isFinite(ms)) {
                return false;
            }
            return Date.now() - ms < POPUP_SNOOZE_MS;
        }
        // Legacy: stored campaign end only — treat as dismissed for this campaign
        if (String(raw) === String(campaignEnd)) {
            return true;
        }
        return false;
    }

    function dismissPopup(root) {
        if (!root) {
            return;
        }
        if (root._jeSbPopupTimerId) {
            window.clearInterval(root._jeSbPopupTimerId);
            root._jeSbPopupTimerId = null;
        }
        var id = root.getAttribute('data-je-sb-popup-id');
        if (id) {
            try {
                localStorage.setItem(popupStorageKey(id), 't:' + Date.now());
            } catch (e) {}
        }
        root.setAttribute('hidden', '');
        root.style.display = '';
        root.classList.remove('je-sb-popup--open');
        document.body.classList.remove('je-sb-popup-active');
    }

    function bindPopupTimer(root) {
        var endRaw = root.getAttribute('data-je-sb-popup-end');
        if (!endRaw) {
            return;
        }
        var endSec = parseInt(endRaw, 10);
        if (!isFinite(endSec)) {
            return;
        }
        var endMs = endSec * 1000;
        var timerEl = root.querySelector('[data-je-sb-timer-value]');
        if (!timerEl) {
            return;
        }

        function tick() {
            var now = Date.now();
            if (now >= endMs) {
                if (root._jeSbPopupTimerId) {
                    window.clearInterval(root._jeSbPopupTimerId);
                    root._jeSbPopupTimerId = null;
                }
                dismissPopup(root);
                return;
            }
            var txt = formatRemaining(endMs - now);
            if (txt) {
                timerEl.textContent = '\u23f1 ' + txt;
            }
        }

        tick();
        root._jeSbPopupTimerId = window.setInterval(tick, 1000);
    }

    function bindPopup(root) {
        var id = root.getAttribute('data-je-sb-popup-id');
        var end = root.getAttribute('data-je-sb-popup-end') || '';
        if (!id) {
            return;
        }
        if (isPopupSnoozed(id, end)) {
            return;
        }

        root.removeAttribute('hidden');
        root.style.display = 'flex';
        root.classList.add('je-sb-popup--open');
        document.body.classList.add('je-sb-popup-active');

        function onDismiss(e) {
            if (e) {
                e.preventDefault();
            }
            dismissPopup(root);
            document.removeEventListener('keydown', onKey);
        }

        function onKey(ev) {
            if (ev.key === 'Escape') {
                onDismiss(ev);
            }
        }

        document.addEventListener('keydown', onKey);

        var closeBtn = root.querySelector('[data-je-sb-popup-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', onDismiss);
        }
        var backdrop = root.querySelector('[data-je-sb-popup-dismiss]');
        if (backdrop) {
            backdrop.addEventListener('click', onDismiss);
        }

        var cta = root.querySelector('a.je-sb-popup__cta');
        if (cta) {
            cta.addEventListener('click', function () {
                dismissPopup(root);
                document.removeEventListener('keydown', onKey);
            });
        }

        var dialog = root.querySelector('[role="dialog"]');
        if (dialog) {
            dialog.addEventListener('click', function (ev) {
                ev.stopPropagation();
            });
        }

        bindPopupTimer(root);
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

        var popup = document.querySelector('[data-je-sb-popup="1"]');
        if (!popup) {
            return;
        }

        var delayRaw = popup.getAttribute('data-je-sb-popup-delay') || '0';
        var delayMs = Math.max(0, parseInt(delayRaw, 10) || 0) * 1000;

        function showPopup() {
            bindPopup(popup);
        }

        if (delayMs > 0) {
            window.setTimeout(showPopup, delayMs);
        } else {
            showPopup();
        }
    });
})();
