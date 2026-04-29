/**
 * Inlined after admin.js. If admin.js fails to load, this still binds Copy Shortcode.
 * Skips when __jeSbCopyShortcodeBound is already set.
 */
(function () {
    'use strict';

    if (window.__jeSbCopyShortcodeBound) {
        return;
    }
    if (typeof window.jeSbAdmin === 'undefined') {
        return;
    }

    var jeSbAdmin = window.jeSbAdmin;
    var copyLabel = jeSbAdmin.i18n && jeSbAdmin.i18n.copyShortcode ? jeSbAdmin.i18n.copyShortcode : 'Copy Shortcode';
    var copiedLabel = jeSbAdmin.i18n && jeSbAdmin.i18n.copied ? jeSbAdmin.i18n.copied : 'Copied!';
    var manualPrompt =
        jeSbAdmin.i18n && jeSbAdmin.i18n.copyManualPrompt
            ? jeSbAdmin.i18n.copyManualPrompt
            : 'Copy failed — select the shortcode below (Ctrl/Cmd+C):';

    function fallbackCopy(text, onSuccess, onFail) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        ta.style.top = '0';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        ta.setSelectionRange(0, text.length);
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (err) {
            document.body.removeChild(ta);
            if (onFail) {
                onFail(err);
            }
            return;
        }
        document.body.removeChild(ta);
        if (ok) {
            onSuccess();
        } else if (onFail) {
            onFail(new Error('execCommand copy returned false'));
        }
    }

    function showCopied(btn) {
        btn.classList.add('copied');
        btn.textContent = copiedLabel;
        window.setTimeout(function () {
            btn.classList.remove('copied');
            btn.textContent = copyLabel;
        }, 1500);
    }

    function copyFailedFinal(text) {
        window.prompt(manualPrompt, text);
    }

    document.addEventListener(
        'click',
        function (e) {
            var btn = e.target && e.target.closest ? e.target.closest('.je-sb-copy-shortcode') : null;
            if (!btn) {
                return;
            }
            e.preventDefault();
            var text = (btn.getAttribute('data-je-sb-shortcode') || '').trim();
            if (!text && btn.dataset && btn.dataset.jeSbShortcode) {
                text = String(btn.dataset.jeSbShortcode).trim();
            }
            if (!text) {
                return;
            }

            function onOk() {
                showCopied(btn);
            }

            function tryClipboard() {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(onOk).catch(function () {
                        fallbackCopy(
                            text,
                            onOk,
                            function () {
                                copyFailedFinal(text);
                            }
                        );
                    });
                } else {
                    fallbackCopy(
                        text,
                        onOk,
                        function () {
                            copyFailedFinal(text);
                        }
                    );
                }
            }

            tryClipboard();
        },
        true
    );

    window.__jeSbCopyShortcodeBound = true;
})();
