<?php
/**
 * Fluent Shortcodes Admin Page
 *
 * @package Fluent_Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_tab = in_array($_GET['tab'] ?? '', array('creator', 'reference', 'saved'), true)
    ? sanitize_key($_GET['tab'])
    : 'creator';

$args = array(
    'post_type'      => 'fluent-products',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
);

$products = get_posts($args);

global $wpdb;
$product_details_raw = $wpdb->get_results(
    "SELECT post_id, id as item_id, min_price FROM {$wpdb->prefix}fct_product_details",
    OBJECT_K
);
$item_ids = array();
$prices = array();
if ($product_details_raw) {
    foreach ($product_details_raw as $post_id => $row) {
        $item_ids[$post_id] = $row->item_id;
        if ($row->min_price !== null) {
            $prices[$post_id] = '$' . number_format($row->min_price / 100, 2);
        }
    }
}
?>

<div class="wrap fluent-shortcodes-wrap">
    <h1><?php esc_html_e('FC Shortcodes', 'fc-shortcodes'); ?></h1>

    <nav class="nav-tab-wrapper wp-clearfix" style="margin-bottom: 0;">
        <a href="<?php echo esc_url(admin_url('admin.php?page=fluent-shortcodes&tab=creator')); ?>" class="nav-tab <?php echo esc_attr($current_tab === 'creator' ? 'nav-tab-active' : ''); ?>"><?php esc_html_e('Shortcode Creator', 'fc-shortcodes'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=fluent-shortcodes&tab=saved')); ?>" class="nav-tab <?php echo esc_attr($current_tab === 'saved' ? 'nav-tab-active' : ''); ?>"><?php esc_html_e('Saved Shortcodes', 'fc-shortcodes'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=fluent-shortcodes&tab=reference')); ?>" class="nav-tab <?php echo esc_attr($current_tab === 'reference' ? 'nav-tab-active' : ''); ?>"><?php esc_html_e('All Shortcodes Reference', 'fc-shortcodes'); ?></a>
    </nav>

<?php if ($current_tab === 'saved') : ?>
    <div class="fluent-shortcodes-section" style="margin-top: 20px;">
        <div id="fluent-sc-saved-panel">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <h2 style="margin:0;"><?php esc_html_e('Saved Shortcodes', 'fc-shortcodes'); ?></h2>
                <button type="button" id="fluent-sc-reload-saved" class="button">↻ <?php esc_html_e('Refresh', 'fc-shortcodes'); ?></button>
            </div>
            <div id="fluent-sc-saved-loading" style="padding:20px; text-align:center; color:#888;"><?php esc_html_e('Loading...', 'fc-shortcodes'); ?></div>
            <div id="fluent-sc-saved-empty" style="display:none; padding:20px; text-align:center; color:#888; background:#f9f9f9; border-radius:6px;"><?php esc_html_e('No saved shortcodes yet. Build one in the Shortcode Creator tab and hit Save.', 'fc-shortcodes'); ?></div>
            <div id="fluent-sc-saved-list"></div>
        </div>
    </div>
<?php elseif ($current_tab === 'reference') : ?>
    <div class="fluent-shortcodes-section" style="margin-top: 20px;">
        <h2><?php esc_html_e('All Shortcodes Reference', 'fc-shortcodes'); ?></h2>
        <p><?php esc_html_e('Use these shortcodes to build custom product layouts with any theme. When on a product page, pid is optional — the current product is used automatically.', 'fc-shortcodes'); ?></p>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 16px;">
            <thead>
                <tr>
                    <th style="width: 20%;"><?php esc_html_e('Shortcode', 'fc-shortcodes'); ?></th>
                    <th style="width: 35%;"><?php esc_html_e('Description', 'fc-shortcodes'); ?></th>
                    <th style="width: 25%;"><?php esc_html_e('Attributes', 'fc-shortcodes'); ?></th>
                    <th style="width: 20%;"><?php esc_html_e('Example', 'fc-shortcodes'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[fluent_shortcode]</code></td>
                    <td><?php esc_html_e('Full product card with image, title, excerpt, price, and action buttons.', 'fc-shortcodes'); ?></td>
                    <td>pid, item_id, show_featured_img, image_height, layout, image_width, margin_top, margin_bottom, show_price, regular_price, sale_price, featured, featured_label, featured_style, featured_color, featured_position, checkout_link, product_link, show_links, checkout_btn_text, product_btn_text, new_tab</td>
                    <td><code>[fluent_shortcode pid="1234" item_id="13"]</code> <button type="button" class="button button-small fluent-copy-btn" data-shortcode="[fluent_shortcode pid=&quot;1234&quot; item_id=&quot;13&quot;]"><?php esc_html_e('Copy', 'fc-shortcodes'); ?></button></td>
                </tr>
                <tr>
                    <td><code>[fluent_price]</code></td>
                    <td><?php esc_html_e('Display product price from fct_product_details.', 'fc-shortcodes'); ?></td>
                    <td>pid (opt), show_original, format, class</td>
                    <td><code>[fluent_price]</code> <button type="button" class="button button-small fluent-copy-btn" data-shortcode="[fluent_price]"><?php esc_html_e('Copy', 'fc-shortcodes'); ?></button></td>
                </tr>
                <tr>
                    <td><code>[fluent_add_to_cart]</code></td>
                    <td><?php esc_html_e('Add to Cart button. redirect="true" goes straight to checkout.', 'fc-shortcodes'); ?></td>
                    <td>pid (opt), label, class, style (button|link), redirect</td>
                    <td><code>[fluent_add_to_cart]</code> <button type="button" class="button button-small fluent-copy-btn" data-shortcode="[fluent_add_to_cart]"><?php esc_html_e('Copy', 'fc-shortcodes'); ?></button></td>
                </tr>
                <tr>
                    <td><code>[fluent_stock]</code></td>
                    <td><?php esc_html_e('Stock status (In Stock / Out of Stock).', 'fc-shortcodes'); ?></td>
                    <td>pid (opt), show_quantity, in_stock_label, out_of_stock_label, class</td>
                    <td><code>[fluent_stock]</code> <button type="button" class="button button-small fluent-copy-btn" data-shortcode="[fluent_stock]"><?php esc_html_e('Copy', 'fc-shortcodes'); ?></button></td>
                </tr>
                <tr>
                    <td><code>[fluent_product_title]</code></td>
                    <td><?php esc_html_e('Product title only.', 'fc-shortcodes'); ?></td>
                    <td>pid (opt), tag (h1-h6,p,span), class, link</td>
                    <td><code>[fluent_product_title tag="h2" link="true"]</code> <button type="button" class="button button-small fluent-copy-btn" data-shortcode="[fluent_product_title tag=&quot;h2&quot; link=&quot;true&quot;]"><?php esc_html_e('Copy', 'fc-shortcodes'); ?></button></td>
                </tr>
                <tr>
                    <td><code>[fluent_product_excerpt]</code></td>
                    <td><?php esc_html_e('Product excerpt or trimmed content.', 'fc-shortcodes'); ?></td>
                    <td>pid (opt), words, class</td>
                    <td><code>[fluent_product_excerpt words="50"]</code> <button type="button" class="button button-small fluent-copy-btn" data-shortcode="[fluent_product_excerpt words=&quot;50&quot;]"><?php esc_html_e('Copy', 'fc-shortcodes'); ?></button></td>
                </tr>
                <tr>
                    <td><code>[fluent_product_image]</code></td>
                    <td><?php esc_html_e('Product image from gallery meta.', 'fc-shortcodes'); ?></td>
                    <td>pid (opt), class, link, index (0=first), image_height</td>
                    <td><code>[fluent_product_image]</code> <button type="button" class="button button-small fluent-copy-btn" data-shortcode="[fluent_product_image]"><?php esc_html_e('Copy', 'fc-shortcodes'); ?></button></td>
                </tr>
            </tbody>
        </table>
        <p style="margin-top: 20px; font-size: 13px; color: #666;">
            <strong><?php esc_html_e('Dynamic PID:', 'fc-shortcodes'); ?></strong>
            <?php esc_html_e('On a fluent-products singular page, omit pid to use the current product. Example: [fluent_price] on a product page shows that product\'s price.', 'fc-shortcodes'); ?>
        </p>
    </div>
<?php else : ?>
    <!-- Section 1: Shortcode Creator -->
    <div class="fluent-shortcodes-section">
        <h2><?php esc_html_e('Shortcode Creator', 'fc-shortcodes'); ?></h2>
        <div class="fluent-shortcodes-creator">
            <div class="fluent-shortcodes-field-group">
                <label for="fluent-sc-product-select"><?php esc_html_e('Select a product', 'fc-shortcodes'); ?></label>
                <select id="fluent-sc-product-select" style="width: 100%">
                    <option value=""><?php esc_html_e('— Search for a product —', 'fc-shortcodes'); ?></option>
                    <?php foreach ($products as $product) : ?>
                        <option value="<?php echo esc_attr($product->ID); ?>">
                            <?php echo esc_html($product->post_title); ?> (ID: <?php echo esc_html($product->ID); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fluent-shortcodes-field-group">
                <label>
                    <input type="checkbox" id="fluent-sc-show-image" class="fluent-sc-show-image">
                    <?php esc_html_e('Show Featured Image', 'fc-shortcodes'); ?>
                </label>
                <div id="fluent-sc-image-height-wrap" style="display:none; margin-top:8px;">
                    <label><?php esc_html_e('Image Height (px)', 'fc-shortcodes'); ?></label>
                    <input type="number" id="fluent-sc-image-height" value="380" min="100" max="800" step="10" style="width:100px;" />
                    <span style="color:#888; font-size:12px; margin-left:6px;"><?php esc_html_e('px (default: 380)', 'fc-shortcodes'); ?></span>
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label><strong><?php esc_html_e('Card Layout', 'fc-shortcodes'); ?></strong></label>
                <select id="fluent-sc-layout" style="width:100%; margin-top:4px;">
                    <option value="vertical"><?php esc_html_e('Vertical (image on top)', 'fc-shortcodes'); ?></option>
                    <option value="horizontal"><?php esc_html_e('Horizontal (image left, content right)', 'fc-shortcodes'); ?></option>
                </select>
            </div>
            <div id="fluent-sc-image-width-wrap" style="display:none; margin-bottom:12px;">
                <label><?php esc_html_e('Image Column Width (%)', 'fc-shortcodes'); ?></label>
                <input type="number" id="fluent-sc-image-width" value="40" min="20" max="60" step="5" style="width:80px;" />
                <span style="color:#888; font-size:12px; margin-left:6px;"><?php esc_html_e('% (20–60, default 40)', 'fc-shortcodes'); ?></span>
            </div>
            <div style="display:flex; gap:16px; margin-bottom:12px;">
                <div>
                    <label><?php esc_html_e('Margin Top (px)', 'fc-shortcodes'); ?></label>
                    <input type="number" id="fluent-sc-margin-top" value="0" min="0" max="200" step="5" style="width:80px; margin-top:4px; display:block;" />
                </div>
                <div>
                    <label><?php esc_html_e('Margin Bottom (px)', 'fc-shortcodes'); ?></label>
                    <input type="number" id="fluent-sc-margin-bottom" value="0" min="0" max="200" step="5" style="width:80px; margin-top:4px; display:block;" />
                </div>
            </div>
            <div class="fluent-shortcodes-field-group">
                <label>
                    <input type="checkbox" id="fluent-sc-show-price">
                    <?php esc_html_e('Show Price', 'fc-shortcodes'); ?>
                </label>
                <div id="fluent-sc-price-fields" style="display:none; margin-top:10px; padding:12px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:4px;">
                    <div style="display:flex; gap:16px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:120px;">
                            <label style="display:block; margin-bottom:4px;">
                                <?php esc_html_e('Regular Price ($)', 'fc-shortcodes'); ?>
                                <span style="font-size:11px; color:#888; font-weight:400;">— <?php esc_html_e('leave blank to use DB value', 'fc-shortcodes'); ?></span>
                            </label>
                            <input type="number" id="fluent-sc-regular-price" placeholder="<?php esc_attr_e('e.g. 39.00', 'fc-shortcodes'); ?>" step="0.01" min="0" style="width:100%;" />
                        </div>
                        <div style="flex:1; min-width:120px;">
                            <label style="display:block; margin-bottom:4px;">
                                <?php esc_html_e('Sale Price ($)', 'fc-shortcodes'); ?>
                                <span style="font-size:11px; color:#888; font-weight:400;">— <?php esc_html_e('shows strikethrough on regular', 'fc-shortcodes'); ?></span>
                            </label>
                            <input type="number" id="fluent-sc-sale-price" placeholder="<?php esc_attr_e('e.g. 19.00', 'fc-shortcodes'); ?>" step="0.01" min="0" style="width:100%;" />
                        </div>
                    </div>
                    <p id="fluent-sc-price-preview" style="margin-top:8px; font-size:12px; color:#555;"></p>
                </div>
            </div>
            <hr style="margin: 16px 0; border:none; border-top:1px solid #ddd;" />
            <div class="fluent-shortcodes-field-group">
                <label><strong><?php esc_html_e('Featured Product Label', 'fc-shortcodes'); ?></strong></label>
                <label style="display:flex; align-items:center; gap:8px; margin-top:8px;">
                    <input type="checkbox" id="fluent-sc-featured">
                    <?php esc_html_e('Show "Featured" label', 'fc-shortcodes'); ?>
                </label>
                <div id="fluent-sc-featured-options" style="display:none; margin-top:12px; padding:12px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:4px;">
                    <div style="margin-bottom:10px;">
                        <label><?php esc_html_e('Label Text', 'fc-shortcodes'); ?></label>
                        <input type="text" id="fluent-sc-featured-label" value="<?php echo esc_attr(__('Featured', 'fc-shortcodes')); ?>" class="regular-text" style="width:100%;">
                    </div>
                    <div style="margin-bottom:10px;">
                        <label><?php esc_html_e('Style', 'fc-shortcodes'); ?></label>
                        <select id="fluent-sc-featured-style" style="width:100%;">
                            <option value="ribbon"><?php esc_html_e('Ribbon (corner)', 'fc-shortcodes'); ?></option>
                            <option value="pill"><?php esc_html_e('Pill (inline)', 'fc-shortcodes'); ?></option>
                        </select>
                    </div>
                    <div id="fluent-sc-ribbon-position-wrap" style="margin-bottom:10px;">
                        <label><?php esc_html_e('Ribbon Position', 'fc-shortcodes'); ?></label>
                        <select id="fluent-sc-featured-position" style="width:100%;">
                            <option value="left"><?php esc_html_e('Left', 'fc-shortcodes'); ?></option>
                            <option value="right"><?php esc_html_e('Right', 'fc-shortcodes'); ?></option>
                        </select>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <div>
                            <label><?php esc_html_e('Color', 'fc-shortcodes'); ?></label>
                            <input type="color" id="fluent-sc-featured-color" value="#e65c00">
                        </div>
                        <div style="flex:1; min-width:200px;">
                            <label><?php esc_html_e('Hex / CSS value', 'fc-shortcodes'); ?></label>
                            <input type="text" id="fluent-sc-featured-color-text" value="#e65c00" placeholder="#e65c00 or rgb() or named color" class="regular-text" style="width:100%;">
                        </div>
                    </div>
                    <p style="font-size:11px; color:#888; margin-top:6px;">
                        <?php esc_html_e('Color picker and text field stay in sync. Text field accepts any valid CSS color.', 'fc-shortcodes'); ?>
                    </p>
                </div>
            </div>
            <div class="fluent-shortcodes-field-group">
                <label for="fluent-sc-checkout-url"><?php esc_html_e('Checkout URL override', 'fc-shortcodes'); ?></label>
                <input type="url" id="fluent-sc-checkout-url" class="fluent-checkout-link regular-text" placeholder="<?php esc_attr_e('Optional — overrides Item ID', 'fc-shortcodes'); ?>">
            </div>
            <div class="fluent-shortcodes-field-group">
                <label for="fluent-sc-product-url"><?php esc_html_e('Product Page URL', 'fc-shortcodes'); ?></label>
                <input type="text" id="fluent-sc-product-url" placeholder="<?php esc_attr_e('Auto-detected from product — override if needed', 'fc-shortcodes'); ?>" style="width:100%;">
                <p id="fluent-sc-url-preview" style="margin-top:4px; font-size:12px;"></p>
            </div>
            <div class="fluent-shortcodes-field-group">
                <label for="fluent-sc-show-links"><?php esc_html_e('Show Links', 'fc-shortcodes'); ?></label>
                <select id="fluent-sc-show-links" class="fluent-show-links">
                    <option value="both"><?php esc_html_e('Both', 'fc-shortcodes'); ?></option>
                    <option value="checkout"><?php esc_html_e('Checkout Only', 'fc-shortcodes'); ?></option>
                    <option value="product"><?php esc_html_e('Product Only', 'fc-shortcodes'); ?></option>
                </select>
            </div>
            <div class="fluent-shortcodes-field-group">
                <label for="fluent-sc-buy-text"><?php esc_html_e('Buy Button Text', 'fc-shortcodes'); ?></label>
                <input type="text" id="fluent-sc-buy-text" class="fluent-checkout-btn-text regular-text" placeholder="<?php esc_attr_e('Buy Now', 'fc-shortcodes'); ?>">
            </div>
            <div class="fluent-shortcodes-field-group">
                <label for="fluent-sc-view-text"><?php esc_html_e('View Button Text', 'fc-shortcodes'); ?></label>
                <input type="text" id="fluent-sc-view-text" class="fluent-product-btn-text regular-text" placeholder="<?php esc_attr_e('Learn More', 'fc-shortcodes'); ?>">
            </div>
            <div class="fluent-shortcodes-field-group">
                <label>
                    <input type="checkbox" id="fluent-sc-new-tab" class="fluent-new-tab">
                    <?php esc_html_e('Open links in new tab', 'fc-shortcodes'); ?>
                </label>
            </div>
            <div class="fluent-sc-output-wrap">
                <label><strong><?php esc_html_e('Your Shortcode', 'fc-shortcodes'); ?></strong></label>
                <div style="display:flex; gap:8px; margin-top:8px; align-items:center;">
                    <input type="text" id="fluent-sc-preview" readonly value="<?php esc_attr_e('Select a product to generate shortcode', 'fc-shortcodes'); ?>" style="flex:1; font-family:monospace; font-size:13px; background:#f6f7f7; padding:8px 12px; border:1px solid #ccc; border-radius:4px;" />
                    <button type="button" id="fluent-sc-copy-btn" class="button button-primary" disabled><?php esc_html_e('Copy Shortcode', 'fc-shortcodes'); ?></button>
                    <button type="button" id="fluent-sc-copy-php-btn" class="button" disabled><?php esc_html_e('Copy PHP', 'fc-shortcodes'); ?></button>
                </div>
                <div id="fluent-sc-php-preview-wrap" style="margin-top:8px; display:none;">
                    <code id="fluent-sc-php-preview" style="display:block; font-size:12px; background:#f0f0f0; padding:8px; border-radius:3px; font-family:monospace; color:#333;"></code>
                </div>
                <div id="fluent-sc-save-wrap" style="display:none; margin-top:16px; padding:16px; background:#f0f7ff; border:1px solid #c5d9ed; border-radius:6px;">
                    <label><strong><?php esc_html_e('Save this shortcode', 'fc-shortcodes'); ?></strong></label>
                    <p style="font-size:12px; color:#555; margin:4px 0 10px;"><?php esc_html_e('Give it a name so you can find it easily later.', 'fc-shortcodes'); ?></p>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <input type="text" id="fluent-sc-save-label" placeholder="<?php esc_attr_e('e.g. Homepage Hero Product, Blog Sidebar Promo...', 'fc-shortcodes'); ?>" style="flex:1; padding:8px 12px; border:1px solid #ccc; border-radius:4px;" />
                        <button type="button" id="fluent-sc-save-btn" class="button button-primary"><?php esc_html_e('Save Shortcode', 'fc-shortcodes'); ?></button>
                    </div>
                    <p id="fluent-sc-save-status" style="margin-top:8px; font-size:12px; display:none;"></p>
                </div>
                <p id="fluent-sc-item-status" style="font-style:italic; color:#666; margin-top:6px; font-size:12px;"></p>
            </div>
            <div id="fluent-sc-preview-panel" style="display:none; margin-top:24px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <strong><?php esc_html_e('Live Preview', 'fc-shortcodes'); ?></strong>
                    <button type="button" id="fluent-sc-refresh-preview" class="button">↻ <?php esc_html_e('Refresh Preview', 'fc-shortcodes'); ?></button>
                </div>
                <div style="display:flex; gap:8px; margin-bottom:12px;">
                    <button type="button" class="button fluent-sc-width-btn active" data-width="100%">🖥 <?php esc_html_e('Full Width', 'fc-shortcodes'); ?></button>
                    <button type="button" class="button fluent-sc-width-btn" data-width="768px">📱 <?php esc_html_e('Tablet (768px)', 'fc-shortcodes'); ?></button>
                    <button type="button" class="button fluent-sc-width-btn" data-width="375px">📱 <?php esc_html_e('Mobile (375px)', 'fc-shortcodes'); ?></button>
                </div>
                <div style="border:1px solid #ddd; border-radius:6px; overflow:hidden; background:#f9f9f9; transition:width 0.3s; width:100%;" id="fluent-sc-preview-container">
                    <div id="fluent-sc-preview-loading" style="display:none; padding:20px; text-align:center; color:#888;"><?php esc_html_e('Loading preview...', 'fc-shortcodes'); ?></div>
                    <div id="fluent-sc-preview-output" style="padding:24px; background:#fff;"></div>
                </div>
                <p style="font-size:11px; color:#888; margin-top:6px;"><?php esc_html_e('Preview renders the actual shortcode output with frontend styles.', 'fc-shortcodes'); ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>
