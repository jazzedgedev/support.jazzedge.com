/**
 * Fluent Shortcodes Admin JavaScript
 */
(function ($) {
    'use strict';

    var PLACEHOLDER = 'Select a product to generate shortcode';

    /**
     * Escape for safe use in HTML attributes (XSS prevention)
     */
    function escAttr(val) {
        return String(val || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    /**
     * Escape for safe use in HTML content (XSS prevention)
     */
    function escapeHtml(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(String(s)));
        return d.innerHTML;
    }

    /**
     * Escape URL for safe use in href (basic XSS prevention)
     */
    function safeUrl(url) {
        if (!url || typeof url !== 'string') return '';
        var trimmed = url.trim();
        if (trimmed.indexOf('http://') === 0 || trimmed.indexOf('https://') === 0) {
            return escapeHtml(trimmed);
        }
        return '';
    }

    /**
     * Build and set shortcode preview from current form values
     */
    function updateShortcodePreview(pid) {
        if (!pid) return;

        var sc = '[fluent_shortcode pid="' + pid + '"';

        var checkoutOverride = $('#fluent-sc-checkout-url').val().trim();
        if (checkoutOverride) {
            sc += ' checkout_link="' + escAttr(checkoutOverride) + '"';
        } else if (fluentSC.currentItemId) {
            sc += ' item_id="' + fluentSC.currentItemId + '"';
        }

        if ($('#fluent-sc-show-image').is(':checked')) {
            sc += ' show_featured_img="true"';
            var imgHeight = $('#fluent-sc-image-height').val();
            if (imgHeight && imgHeight !== '380') {
                sc += ' image_height="' + escAttr(imgHeight) + '"';
            }
        }

        if ($('#fluent-sc-show-price').is(':checked')) {
            sc += ' show_price="true"';
            var regularPrice = $('#fluent-sc-regular-price').val().trim();
            var salePrice = $('#fluent-sc-sale-price').val().trim();
            if (regularPrice) sc += ' regular_price="' + escAttr(regularPrice) + '"';
            if (salePrice) sc += ' sale_price="' + escAttr(salePrice) + '"';
        }

        var showLinks = $('#fluent-sc-show-links').val();
        if (showLinks && showLinks !== 'both') {
            sc += ' show_links="' + escAttr(showLinks) + '"';
        }

        var productUrl = $('#fluent-sc-product-url').val().trim();
        if (productUrl && productUrl !== fluentSC.currentPermalink) {
            sc += ' product_link="' + escAttr(productUrl) + '"';
        }

        var buyText = $('#fluent-sc-buy-text').val().trim();
        if (buyText && buyText !== 'Buy Now') {
            sc += ' checkout_btn_text="' + escAttr(buyText) + '"';
        }

        var viewText = $('#fluent-sc-view-text').val().trim();
        if (viewText && viewText !== 'Learn More') {
            sc += ' product_btn_text="' + escAttr(viewText) + '"';
        }

        if ($('#fluent-sc-new-tab').is(':checked')) {
            sc += ' new_tab="true"';
        }

        if ($('#fluent-sc-featured').is(':checked')) {
            sc += ' featured="true"';

            var featLabel = $('#fluent-sc-featured-label').val().trim();
            if (featLabel && featLabel !== 'Featured') {
                sc += ' featured_label="' + escAttr(featLabel) + '"';
            }

            var featStyle = $('#fluent-sc-featured-style').val();
            if (featStyle && featStyle !== 'ribbon') {
                sc += ' featured_style="' + escAttr(featStyle) + '"';
            }

            var featColor = $('#fluent-sc-featured-color-text').val().trim();
            if (featColor && featColor !== '#e65c00') {
                sc += ' featured_color="' + escAttr(featColor) + '"';
            }

            var featPosition = $('#fluent-sc-featured-position').val();
            if (featPosition && featPosition !== 'left') {
                sc += ' featured_position="' + escAttr(featPosition) + '"';
            }
        }

        var layout = $('#fluent-sc-layout').val();
        if (layout && layout !== 'vertical') {
            sc += ' layout="' + escAttr(layout) + '"';
            var imgWidth = $('#fluent-sc-image-width').val();
            if (imgWidth && imgWidth !== '40') {
                sc += ' image_width="' + escAttr(imgWidth) + '"';
            }
        }

        var marginTop = $('#fluent-sc-margin-top').val();
        var marginBottom = $('#fluent-sc-margin-bottom').val();
        if (marginTop && marginTop !== '0') {
            sc += ' margin_top="' + escAttr(marginTop) + '"';
        }
        if (marginBottom && marginBottom !== '0') {
            sc += ' margin_bottom="' + escAttr(marginBottom) + '"';
        }

        sc += ']';

        $('#fluent-sc-preview').val(sc);
        $('#fluent-sc-copy-btn').prop('disabled', false);
        $('#fluent-sc-copy-php-btn').prop('disabled', false);

        var phpCode = "echo do_shortcode('" + sc.replace(/'/g, "\\'") + "');";
        $('#fluent-sc-php-preview').text(phpCode);
        $('#fluent-sc-php-preview-wrap').show();
        $('#fluent-sc-save-wrap').show();

        var productName = $('#fluent-sc-product-select option:selected').text().replace(/\s*\(ID:.*\)$/, '').trim();
        if (!$('#fluent-sc-save-label').data('user-edited')) {
            $('#fluent-sc-save-label').val(productName);
        }

        schedulePreviewRefresh();
    }

    function showSaveStatus(msg, type) {
        var $s = $('#fluent-sc-save-status');
        $s.text(msg).css('color', type === 'success' ? '#2e7d32' : '#c0392b').show();
        setTimeout(function () { $s.fadeOut(); }, 3000);
    }

    function loadSavedShortcodes() {
        window.fluentScSavedLoaded = true;
        $('#fluent-sc-saved-loading').show();
        $('#fluent-sc-saved-list').html('');
        $('#fluent-sc-saved-empty').hide();

        $.post(fluentSC.ajax_url, {
            action: 'fluent_sc_load_saved',
            nonce: fluentSC.nonce
        }, function (response) {
            $('#fluent-sc-saved-loading').hide();
            if (!response.success || !response.data.length) {
                $('#fluent-sc-saved-empty').show();
                return;
            }

            var rows = response.data.map(function (item) {
                var phpCode = "echo do_shortcode('" + (item.shortcode || '').replace(/'/g, "\\'") + "');";
                var date = new Date(item.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                return '<div class="fluent-sc-saved-item" data-id="' + item.id + '" style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:16px 20px; margin-bottom:12px;">' +
                    '<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">' +
                    '<div style="flex:1; min-width:200px;">' +
                    '<div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">' +
                    '<input type="text" class="fluent-sc-saved-label-input" value="' + escapeHtml(item.label || '') + '" style="font-weight:700; font-size:14px; border:1px solid transparent; background:transparent; padding:2px 6px; border-radius:3px; width:100%; max-width:400px;" title="Click to rename" />' +
                    '</div>' +
                    '<div style="font-size:12px; color:#888; margin-bottom:10px;">' + escapeHtml(item.product_name || '') + ' &nbsp;·&nbsp; Saved ' + escapeHtml(date) + '</div>' +
                    '<code style="display:block; background:#f6f7f7; padding:8px 10px; border-radius:4px; font-size:12px; margin-bottom:6px; word-break:break-all;">' + escapeHtml(item.shortcode || '') + '</code>' +
                    '<code style="display:block; background:#f0f0f0; padding:8px 10px; border-radius:4px; font-size:11px; color:#555; word-break:break-all;">' + escapeHtml(phpCode) + '</code>' +
                    '</div>' +
                    '<div style="display:flex; flex-direction:column; gap:6px; min-width:110px;">' +
                    '<button type="button" class="button button-primary fluent-sc-copy-saved" data-code="' + escAttrForData(item.shortcode || '') + '">Copy Shortcode</button>' +
                    '<button type="button" class="button fluent-sc-copy-saved-php" data-code="' + escAttrForData(phpCode) + '">Copy PHP</button>' +
                    '<button type="button" class="button button-link-delete fluent-sc-delete-saved" data-id="' + item.id + '" style="color:#c0392b; text-align:center;">Delete</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });

            $('#fluent-sc-saved-list').html(rows.join(''));
        });
    }

    function escAttrForData(str) {
        return escAttr(str || '');
    }

    var previewTimeout = null;

    function schedulePreviewRefresh() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(fetchPreview, 600);
    }

    function fetchPreview() {
        var sc = $('#fluent-sc-preview').val();
        if (!sc || sc === PLACEHOLDER || sc.indexOf('[fluent_shortcode') !== 0) return;

        $('#fluent-sc-preview-panel').show();
        $('#fluent-sc-preview-loading').show();
        $('#fluent-sc-preview-output').html('');

        $.post(fluentSC.ajax_url, {
            action: 'fluent_sc_preview',
            nonce: fluentSC.nonce,
            shortcode: sc
        }, function (response) {
            $('#fluent-sc-preview-loading').hide();
            if (response.success) {
                if (!$('#fluent-sc-injected-styles').length) {
                    $('head').append('<style id="fluent-sc-injected-styles">' + response.data.styles + '</style>');
                }
                $('#fluent-sc-preview-output').html(response.data.html || '<p style="color:#888; padding:10px;">No output — check shortcode settings.</p>');
            } else {
                $('#fluent-sc-preview-output').html('<p style="color:red; padding:10px;">Preview failed. Check shortcode settings.</p>');
            }
        }).fail(function () {
            $('#fluent-sc-preview-loading').hide();
            $('#fluent-sc-preview-output').html('<p style="color:red; padding:10px;">Preview failed. Check shortcode settings.</p>');
        });
    }

    /**
     * Fetch item_id and permalink in parallel when product is selected
     */
    function fetchProductData(pid) {
        $('#fluent-sc-item-status').text('Loading product data...');

        var ajaxItemId = $.post(fluentSC.ajax_url, {
            action: 'fluent_sc_get_item_id',
            nonce: fluentSC.nonce,
            pid: pid
        });

        var ajaxPermalink = $.post(fluentSC.ajax_url, {
            action: 'fluent_sc_get_permalink',
            nonce: fluentSC.nonce,
            pid: pid
        });

        $.when(ajaxItemId, ajaxPermalink).done(function (itemRes, permalinkRes) {
            if (itemRes[0].success) {
                fluentSC.currentItemId = itemRes[0].data.item_id;
                $('#fluent-sc-item-status').text('\u2713 Checkout ID: ' + itemRes[0].data.item_id);
            } else {
                fluentSC.currentItemId = null;
                $('#fluent-sc-item-status').text('\u26a0 No checkout item found for this product');
            }

            if (permalinkRes[0].success) {
                fluentSC.currentPermalink = permalinkRes[0].data.permalink;
                if (!$('#fluent-sc-product-url').data('manually-edited')) {
                    $('#fluent-sc-product-url').val(permalinkRes[0].data.permalink);
                    var permalink = permalinkRes[0].data.permalink;
                    var safe = safeUrl(permalink);
                    $('#fluent-sc-url-preview').html(
                        safe ? '<a href="' + safe + '" target="_blank" rel="noopener">' + escapeHtml(permalink) + ' \u2197</a>' : ''
                    );
                }
            } else {
                fluentSC.currentPermalink = null;
            }

            updateShortcodePreview(pid);
        }).fail(function () {
            fluentSC.currentItemId = null;
            fluentSC.currentPermalink = null;
            $('#fluent-sc-item-status').text('\u26a0 Failed to load product data');
            updateShortcodePreview(pid);
        });
    }

    /**
     * Copy text to clipboard and show feedback
     */
    function copyToClipboard(text, $btn) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                var orig = $btn.text();
                $btn.text('Copied!');
                setTimeout(function () { $btn.text(orig); }, 2000);
            }).catch(function () {
                fallbackCopy(text, $btn);
            });
        } else {
            fallbackCopy(text, $btn);
        }
    }

    function fallbackCopy(text, $btn) {
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        try {
            document.execCommand('copy');
            var orig = $btn.text();
            $btn.text('Copied!');
            setTimeout(function () { $btn.text(orig); }, 2000);
        } catch (err) {}
        $temp.remove();
    }

    // Copy buttons (table rows + reference tab) — delegated, works on both tabs
    $(document).on('click', '.fluent-copy-btn', function () {
        var $btn = $(this);
        var shortcode = $btn.data('shortcode');
        if (shortcode) {
            copyToClipboard(shortcode, $btn);
        }
    });

    // Saved tab init
    if ($('#fluent-sc-saved-panel').length) {
        loadSavedShortcodes();
        $('#fluent-sc-reload-saved').on('click', loadSavedShortcodes);
    }

    // Skip creator-specific init when on reference or saved tab
    if (!$('#fluent-sc-product-select').length) {
        return;
    }

    $('#fluent-sc-save-label').on('input', function () {
        $(this).data('user-edited', true);
    });

    $('#fluent-sc-save-btn').on('click', function () {
        var label = $('#fluent-sc-save-label').val().trim();
        var sc = $('#fluent-sc-preview').val();
        var pid = $('#fluent-sc-product-select').val();
        var prodName = $('#fluent-sc-product-select option:selected').text().replace(/\s*\(ID:.*\)$/, '').trim();

        if (!label) {
            showSaveStatus('Please enter a name for this shortcode.', 'error');
            return;
        }
        if (!sc || sc === PLACEHOLDER) {
            showSaveStatus('No shortcode to save yet.', 'error');
            return;
        }

        var $btn = $(this);
        $btn.text('Saving...').prop('disabled', true);

        $.post(fluentSC.ajax_url, {
            action: 'fluent_sc_save',
            nonce: fluentSC.nonce,
            label: label,
            shortcode: sc,
            pid: pid,
            product_name: prodName
        }, function (response) {
            $btn.text('Save Shortcode').prop('disabled', false);
            if (response.success) {
                showSaveStatus('\u2713 Saved successfully!', 'success');
                if (window.fluentScSavedLoaded) {
                    loadSavedShortcodes();
                }
            } else {
                showSaveStatus('\u2717 ' + (response.data || 'Save failed'), 'error');
            }
        }).fail(function () {
            $btn.text('Save Shortcode').prop('disabled', false);
            showSaveStatus('\u2717 Save failed', 'error');
        });
    });

    // Initialize Select2
    $('#fluent-sc-product-select').select2({
        placeholder: 'Search for a product...',
        allowClear: true,
        width: '100%'
    });

    // Select2:select — fires when user picks a product (more reliable than 'change')
    $('#fluent-sc-product-select').on('select2:select', function (e) {
        var pid = $(this).val();
        if (typeof console !== 'undefined' && console.log) {
            console.log('Product selected:', pid);
        }
        $('#fluent-sc-product-url').data('manually-edited', false);
        $('#fluent-sc-save-label').data('user-edited', false);
        fetchProductData(pid);
    });

    // Select2:clear — fires when user clears the selection
    $('#fluent-sc-product-select').on('select2:clear', function () {
        $('#fluent-sc-preview').val(PLACEHOLDER);
        $('#fluent-sc-copy-btn').prop('disabled', true);
        $('#fluent-sc-copy-php-btn').prop('disabled', true);
        $('#fluent-sc-php-preview-wrap').hide();
        $('#fluent-sc-save-wrap').hide();
        $('#fluent-sc-preview-panel').hide();
        $('#fluent-sc-product-url').val('');
        $('#fluent-sc-url-preview').html('');
        $('#fluent-sc-item-status').text('');
        fluentSC.currentItemId = null;
        fluentSC.currentPermalink = null;
    });

    // Product URL: track manual edits and update preview link when user types
    $('#fluent-sc-product-url').on('input', function () {
        $(this).data('manually-edited', true);
        var val = $(this).val().trim();
        if (val) {
            var safe = safeUrl(val);
            $('#fluent-sc-url-preview').html(
                safe ? '<a href="' + safe + '" target="_blank" rel="noopener">' + escapeHtml(val) + ' \u2197</a>' : escapeHtml(val)
            );
        } else {
            $('#fluent-sc-url-preview').html('');
        }
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

    // Copy button (creator)
    $('#fluent-sc-copy-btn').on('click', function () {
        var val = $('#fluent-sc-preview').val();
        if (!val || val === PLACEHOLDER || val.indexOf('[fluent_shortcode') !== 0) return;
        copyToClipboard(val, $(this));
    });

    // Copy PHP button (creator)
    $('#fluent-sc-copy-php-btn').on('click', function () {
        var sc = $('#fluent-sc-preview').val();
        if (!sc || sc === PLACEHOLDER || sc.indexOf('[fluent_shortcode') !== 0) return;
        var phpCode = "echo do_shortcode('" + sc.replace(/'/g, "\\'") + "');";
        var $btn = $(this);
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(phpCode).then(function () {
                $btn.text('Copied!');
                setTimeout(function () { $btn.text('Copy PHP'); }, 2000);
            });
        } else {
            fallbackCopy(phpCode, $btn);
        }
    });

    // Manual refresh preview button
    $(document).on('click', '#fluent-sc-refresh-preview', function () {
        fetchPreview();
    });

    // Device width toggle buttons
    $(document).on('click', '.fluent-sc-width-btn', function () {
        $('.fluent-sc-width-btn').removeClass('active');
        $(this).addClass('active');
        var w = $(this).data('width');
        $('#fluent-sc-preview-container').css('width', w);
        $('#fluent-sc-preview-container').css('margin', w !== '100%' ? '0 auto' : '0');
    });

    // Saved tab: copy shortcode
    $(document).on('click', '.fluent-sc-copy-saved', function () {
        var code = $(this).data('code');
        if (!code) return;
        var $btn = $(this);
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function () {
                $btn.text('Copied!');
                setTimeout(function () { $btn.text('Copy Shortcode'); }, 2000);
            });
        } else {
            fallbackCopy(code, $btn);
        }
    });

    // Saved tab: copy PHP
    $(document).on('click', '.fluent-sc-copy-saved-php', function () {
        var code = $(this).data('code');
        if (!code) return;
        var $btn = $(this);
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function () {
                $btn.text('Copied!');
                setTimeout(function () { $btn.text('Copy PHP'); }, 2000);
            });
        } else {
            fallbackCopy(code, $btn);
        }
    });

    // Saved tab: inline label rename — save on blur
    $(document).on('blur', '.fluent-sc-saved-label-input', function () {
        var $input = $(this);
        var id = $input.closest('.fluent-sc-saved-item').data('id');
        var label = $input.val().trim();
        if (!label) return;

        $.post(fluentSC.ajax_url, {
            action: 'fluent_sc_update_label',
            nonce: fluentSC.nonce,
            id: id,
            label: label
        });
    });

    $(document).on('focus', '.fluent-sc-saved-label-input', function () {
        $(this).css('border-color', '#2271b1').css('background', '#fff');
    });
    $(document).on('blur', '.fluent-sc-saved-label-input', function () {
        $(this).css('border-color', 'transparent').css('background', 'transparent');
    });

    // Saved tab: delete
    $(document).on('click', '.fluent-sc-delete-saved', function () {
        if (!confirm('Delete this saved shortcode? This cannot be undone.')) return;
        var $item = $(this).closest('.fluent-sc-saved-item');
        var id = $(this).data('id');

        $.post(fluentSC.ajax_url, {
            action: 'fluent_sc_delete_saved',
            nonce: fluentSC.nonce,
            id: id
        }, function (response) {
            if (response.success) {
                $item.fadeOut(200, function () {
                    $(this).remove();
                    if (!$('#fluent-sc-saved-list .fluent-sc-saved-item').length) {
                        $('#fluent-sc-saved-empty').show();
                    }
                });
            }
        });
    });

    // Copy PHP button (table rows) — delegated
    $(document).on('click', '.fluent-sc-copy-php', function () {
        var code = $(this).data('code');
        if (!code) return;
        var $btn = $(this);
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function () {
                $btn.text('Copied!');
                setTimeout(function () { $btn.text('Copy PHP'); }, 2000);
            });
        } else {
            fallbackCopy(code, $btn);
        }
    });

    // Show/hide featured options panel
    $('#fluent-sc-featured').on('change', function () {
        if ($(this).is(':checked')) {
            $('#fluent-sc-featured-options').slideDown(150);
        } else {
            $('#fluent-sc-featured-options').slideUp(150);
        }
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

    // Keep color picker and text field in sync
    $('#fluent-sc-featured-color').on('input', function () {
        $('#fluent-sc-featured-color-text').val($(this).val());
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });
    $('#fluent-sc-featured-color-text').on('input', function () {
        var val = $(this).val().trim();
        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
            $('#fluent-sc-featured-color').val(val);
        }
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

    // Show/hide ribbon position based on style selection
    function toggleRibbonPosition() {
        if ($('#fluent-sc-featured-style').val() === 'ribbon') {
            $('#fluent-sc-ribbon-position-wrap').show();
        } else {
            $('#fluent-sc-ribbon-position-wrap').hide();
        }
    }
    $('#fluent-sc-featured-style').on('change', function () {
        toggleRibbonPosition();
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });
    toggleRibbonPosition(); // Initial state

    // Bind other featured fields
    $('#fluent-sc-featured-label, #fluent-sc-featured-position').on('input change', function () {
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

    // Show/hide price fields based on Show Price checkbox
    $('#fluent-sc-show-price').on('change', function () {
        if ($(this).is(':checked')) {
            $('#fluent-sc-price-fields').show();
            updatePricePreview();
        } else {
            $('#fluent-sc-price-fields').hide();
        }
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });
    if ($('#fluent-sc-show-price').is(':checked')) {
        $('#fluent-sc-price-fields').show();
        updatePricePreview();
    }

    function updatePricePreview() {
        var regular = parseFloat($('#fluent-sc-regular-price').val());
        var sale = parseFloat($('#fluent-sc-sale-price').val());
        var $p = $('#fluent-sc-price-preview');

        if (sale && regular && sale < regular) {
            var savings = (regular - sale).toFixed(2);
            $p.html('Preview: <s>$' + regular.toFixed(2) + '</s> ' +
                '<strong style="color:#c0392b;">$' + sale.toFixed(2) + '</strong> ' +
                '<span style="background:#fde8e8;color:#c0392b;padding:1px 6px;border-radius:10px;font-size:11px;">Save $' + savings + '</span>');
        } else if (regular) {
            $p.html('Preview: <strong>$' + regular.toFixed(2) + '</strong>');
        } else if (sale) {
            $p.html('Preview: <strong>$' + sale.toFixed(2) + '</strong>');
        } else {
            $p.html('Prices will be pulled from database.');
        }
    }

    $('#fluent-sc-regular-price, #fluent-sc-sale-price').on('input', function () {
        updatePricePreview();
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

    // Show/hide image height based on Show Featured Image checkbox
    $('#fluent-sc-show-image').on('change', function () {
        if ($(this).is(':checked')) {
            $('#fluent-sc-image-height-wrap').show();
        } else {
            $('#fluent-sc-image-height-wrap').hide();
        }
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });
    // Initial state: show if checked on load
    if ($('#fluent-sc-show-image').is(':checked')) {
        $('#fluent-sc-image-height-wrap').show();
    }

    $('#fluent-sc-image-height').on('input', function () {
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

    // Layout controls
    $('#fluent-sc-layout').on('change', function () {
        if ($(this).val() === 'horizontal') {
            $('#fluent-sc-image-width-wrap').show();
        } else {
            $('#fluent-sc-image-width-wrap').hide();
        }
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });
    if ($('#fluent-sc-layout').val() === 'horizontal') {
        $('#fluent-sc-image-width-wrap').show();
    }

    $('#fluent-sc-image-width').on('input', function () {
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

    $('#fluent-sc-margin-top, #fluent-sc-margin-bottom').on('input', function () {
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

    // Bind updateShortcodePreview to all other form fields
    $('#fluent-sc-show-image, #fluent-sc-show-price, #fluent-sc-new-tab').on('change', function () {
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });
    $('#fluent-sc-show-links, #fluent-sc-checkout-url, #fluent-sc-product-url, #fluent-sc-buy-text, #fluent-sc-view-text').on('input change', function () {
        updateShortcodePreview($('#fluent-sc-product-select').val());
    });

})(jQuery);
