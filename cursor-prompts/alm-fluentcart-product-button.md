´# Cursor Prompt: Add "Create FluentCart Product" Button to ALM Lesson Edit Page

## Goal
Add a **"Create FluentCart Product"** button to the Academy Lesson Manager lesson edit page that pre-fills a modal form with lesson data and creates a FluentCart product via AJAX — eliminating the need to copy/paste lesson info manually into FluentCart.

---

## Context & Architecture

### Plugin files to modify
- **Primary:** `plugins/academy-lesson-manager/includes/class-admin-lessons.php`
  - `render_edit_page($id)` starts at line 1394
  - Action buttons row is at lines 1416–1453 (the `<p>` tag with Back/View/Copy/Delete buttons)
  - The edit page outputs inline `<script>` blocks at the bottom of the method (around line 2040+)
- **AJAX registration:** `plugins/academy-lesson-manager/academy-lesson-manager.php`
  - All `wp_ajax_alm_*` actions are registered here (lines 52–170+)
  - Handlers are methods on the `Academy_Lesson_Manager` class in the same file

### How FluentCart products work
FluentCart stores products using **three structures** you must create together:

1. **WordPress post** (`post_type = 'fc_product'`)
   ```php
   wp_insert_post([
       'post_title'   => 'Lesson Title',
       'post_content' => 'Lesson description',
       'post_status'  => 'draft',   // safer default; user can publish in FC admin
       'post_type'    => 'fc_product',
   ]);
   ```

2. **`{prefix}_fct_product_details` row** — stores pricing metadata
   ```php
   // Use wpdb directly since the Model class may not be autoloaded in AJAX context
   $wpdb->insert("{$wpdb->prefix}fct_product_details", [
       'post_id'           => $post_id,
       'fulfillment_type'  => 'digital',
       'variation_type'    => 'simple',
       'min_price'         => $price,
       'max_price'         => $price,
       'stock_availability'=> 'in-stock',
       'manage_stock'      => '0',
       'manage_downloadable'=> '0',
   ]);
   ```

3. **`{prefix}_fct_product_variations` row** — the purchasable variant
   ```php
   $wpdb->insert("{$wpdb->prefix}fct_product_variations", [
       'post_id'          => $post_id,
       'variation_title'  => 'Default',
       'item_price'       => $price,
       'compare_price'    => $compare_price,  // 0 if none
       'payment_type'     => 'onetime',
       'fulfillment_type' => 'digital',
       'stock_status'     => 'in-stock',
   ]);
   ```

### FluentCart detection pattern
Check if FluentCart is active before showing the button (mirrors `class-fluentcart.php` in lead-aggregator):
```php
private function is_fluentcart_active() {
    return (
        class_exists('\FluentCart\App\App') ||
        class_exists('FluentCart\App\App') ||
        class_exists('FluentCartPro\App\App') ||
        function_exists('fluentCartApi') ||
        function_exists('FluentCartApi')
    );
}
```

---

## Implementation Steps

### Step 1 — Add helper method to `ALM_Admin_Lessons`

Add a private `is_fluentcart_active()` method to `class-admin-lessons.php` (place it near the top with other helpers):

```php
private function is_fluentcart_active() {
    return (
        class_exists('\FluentCart\App\App') ||
        class_exists('FluentCart\App\App') ||
        class_exists('FluentCartPro\App\App') ||
        function_exists('fluentCartApi') ||
        function_exists('FluentCartApi')
    );
}
```

---

### Step 2 — Add the button in `render_edit_page()`

In `render_edit_page()`, after the **Copy Transcript** button (around line 1444) and before the Delete form, add:

```php
// Add Create FluentCart Product button (only if FluentCart is active)
if ($this->is_fluentcart_active()) {
    echo '<button type="button" class="button alm-create-fc-product-btn"
        data-lesson-id="' . esc_attr($lesson->ID) . '"
        data-lesson-title="' . esc_attr($lesson->lesson_title) . '"
        data-lesson-description="' . esc_attr($lesson->lesson_description) . '"
        title="' . esc_attr__('Create FluentCart Product from this lesson', 'academy-lesson-manager') . '">
        <span class="dashicons dashicons-cart"></span> ' . esc_html__('Create FluentCart Product', 'academy-lesson-manager') . '
    </button> ';
}
```

---

### Step 3 — Add the modal HTML

At the **end** of `render_edit_page()` (after all other output, before the closing brace), add the modal — but only if FluentCart is active:

```php
// FluentCart Product Creation Modal
if ($this->is_fluentcart_active()) {
    echo '
    <div id="alm-fc-product-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:100000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:6px; padding:28px 32px; width:480px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,0.25);">
            <h2 style="margin-top:0;">Create FluentCart Product</h2>
            <p style="color:#666; margin-bottom:20px;">Review and adjust the details below. The product will be created as a <strong>Draft</strong> — you can publish it from the FluentCart admin.</p>

            <table class="form-table" style="margin:0;">
                <tr>
                    <th style="width:130px;"><label for="alm-fc-title">Product Title</label></th>
                    <td><input type="text" id="alm-fc-title" class="regular-text" style="width:100%;" /></td>
                </tr>
                <tr>
                    <th><label for="alm-fc-description">Description</label></th>
                    <td><textarea id="alm-fc-description" rows="4" style="width:100%;"></textarea></td>
                </tr>
                <tr>
                    <th><label for="alm-fc-price">Price ($)</label></th>
                    <td><input type="number" id="alm-fc-price" step="0.01" min="0" value="0.00" style="width:120px;" /></td>
                </tr>
                <tr>
                    <th><label for="alm-fc-compare-price">Compare Price ($)</label></th>
                    <td><input type="number" id="alm-fc-compare-price" step="0.01" min="0" value="0.00" style="width:120px;" />
                    <p class="description">Original/crossed-out price. Leave 0 to skip.</p></td>
                </tr>
            </table>

            <div id="alm-fc-modal-result" style="margin-top:16px; display:none;"></div>

            <div style="margin-top:24px; text-align:right;">
                <button type="button" id="alm-fc-modal-cancel" class="button" style="margin-right:8px;">Cancel</button>
                <button type="button" id="alm-fc-modal-submit" class="button button-primary">
                    <span class="dashicons dashicons-cart" style="vertical-align:middle; margin-top:-2px;"></span>
                    Create Product
                </button>
            </div>
        </div>
    </div>
    ';
}
```

---

### Step 4 — Add JavaScript for the modal

Still at the end of `render_edit_page()`, after the modal HTML, add a `<script>` block:

```php
if ($this->is_fluentcart_active()) {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modal   = document.getElementById("alm-fc-product-modal");
        var btn     = document.querySelector(".alm-create-fc-product-btn");
        var cancel  = document.getElementById("alm-fc-modal-cancel");
        var submit  = document.getElementById("alm-fc-modal-submit");
        var result  = document.getElementById("alm-fc-modal-result");

        if (!btn || !modal) return;

        // Open modal and pre-fill from lesson data
        btn.addEventListener("click", function() {
            document.getElementById("alm-fc-title").value       = this.getAttribute("data-lesson-title") || "";
            document.getElementById("alm-fc-description").value = this.getAttribute("data-lesson-description") || "";
            document.getElementById("alm-fc-price").value       = "0.00";
            document.getElementById("alm-fc-compare-price").value = "0.00";
            result.style.display = "none";
            result.innerHTML     = "";
            modal.style.display  = "flex";
        });

        // Close modal
        cancel.addEventListener("click", function() { modal.style.display = "none"; });
        modal.addEventListener("click", function(e) { if (e.target === modal) modal.style.display = "none"; });

        // Submit — create the product
        submit.addEventListener("click", function() {
            var title        = document.getElementById("alm-fc-title").value.trim();
            var description  = document.getElementById("alm-fc-description").value.trim();
            var price        = parseFloat(document.getElementById("alm-fc-price").value) || 0;
            var comparePrice = parseFloat(document.getElementById("alm-fc-compare-price").value) || 0;
            var lessonId     = btn.getAttribute("data-lesson-id");

            if (!title) {
                alert("Please enter a product title.");
                return;
            }

            submit.disabled    = true;
            submit.textContent = "Creating…";
            result.style.display = "none";

            jQuery.ajax({
                url:  ajaxurl,
                type: "POST",
                data: {
                    action:        "alm_create_fluentcart_product",
                    lesson_id:     lessonId,
                    title:         title,
                    description:   description,
                    price:         price,
                    compare_price: comparePrice,
                    _ajax_nonce:   "' . wp_create_nonce('alm_create_fc_product') . '"
                },
                success: function(response) {
                    submit.disabled    = false;
                    submit.textContent = "Create Product";
                    if (response.success) {
                        result.style.display    = "block";
                        result.style.background = "#d4edda";
                        result.style.padding    = "12px";
                        result.style.borderRadius = "4px";
                        result.innerHTML = "<strong>Product created!</strong> " +
                            "<a href=\"" + response.data.edit_url + "\" target=\"_blank\">Edit in FluentCart \u2197</a> &nbsp; " +
                            "<a href=\"" + response.data.view_url + "\" target=\"_blank\">View product \u2197</a>";
                    } else {
                        result.style.display    = "block";
                        result.style.background = "#f8d7da";
                        result.style.padding    = "12px";
                        result.style.borderRadius = "4px";
                        result.innerHTML = "<strong>Error:</strong> " + (response.data || "Unknown error");
                    }
                },
                error: function() {
                    submit.disabled    = false;
                    submit.textContent = "Create Product";
                    result.style.display    = "block";
                    result.style.background = "#f8d7da";
                    result.style.padding    = "12px";
                    result.innerHTML = "<strong>AJAX error.</strong> Please try again.";
                }
            });
        });
    });
    </script>';
}
```

---

### Step 5 — Register the AJAX action in the main plugin file

In `academy-lesson-manager.php`, in the `__construct()` or `init()` method where other `wp_ajax_alm_*` actions are registered, add:

```php
add_action('wp_ajax_alm_create_fluentcart_product', array($this, 'ajax_create_fluentcart_product'));
```

---

### Step 6 — Add the AJAX handler method to `Academy_Lesson_Manager`

Add this method to `academy-lesson-manager.php` in the `Academy_Lesson_Manager` class:

```php
/**
 * AJAX: Create a FluentCart product from a lesson
 */
public function ajax_create_fluentcart_product() {
    // Security check
    check_ajax_referer('alm_create_fc_product', '_ajax_nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions.');
    }

    global $wpdb;

    // Sanitize inputs
    $lesson_id    = isset($_POST['lesson_id'])    ? intval($_POST['lesson_id'])              : 0;
    $title        = isset($_POST['title'])        ? sanitize_text_field($_POST['title'])     : '';
    $description  = isset($_POST['description'])  ? wp_kses_post($_POST['description'])      : '';
    $price        = isset($_POST['price'])        ? round(floatval($_POST['price']), 2)      : 0.00;
    $compare_price= isset($_POST['compare_price'])? round(floatval($_POST['compare_price']),2): 0.00;

    if (empty($title)) {
        wp_send_json_error('Product title is required.');
    }

    // 1. Create the WordPress post (post_type = fc_product)
    $post_id = wp_insert_post([
        'post_title'   => $title,
        'post_content' => $description,
        'post_status'  => 'draft',
        'post_type'    => 'fc_product',
    ], true);

    if (is_wp_error($post_id)) {
        wp_send_json_error('Failed to create post: ' . $post_id->get_error_message());
    }

    // 2. Insert product detail row
    $detail_inserted = $wpdb->insert(
        $wpdb->prefix . 'fct_product_details',
        [
            'post_id'            => $post_id,
            'fulfillment_type'   => 'digital',
            'variation_type'     => 'simple',
            'min_price'          => $price,
            'max_price'          => $price,
            'stock_availability' => 'in-stock',
            'manage_stock'       => '0',
            'manage_downloadable'=> '0',
        ],
        ['%d', '%s', '%s', '%f', '%f', '%s', '%s', '%s']
    );

    if ($detail_inserted === false) {
        // Cleanup orphaned post on failure
        wp_delete_post($post_id, true);
        wp_send_json_error('Failed to create product details: ' . $wpdb->last_error);
    }

    // 3. Insert product variation row (simple = one default variation)
    $variation_inserted = $wpdb->insert(
        $wpdb->prefix . 'fct_product_variations',
        [
            'post_id'          => $post_id,
            'variation_title'  => 'Default',
            'item_price'       => $price,
            'compare_price'    => $compare_price,
            'payment_type'     => 'onetime',
            'fulfillment_type' => 'digital',
            'stock_status'     => 'in-stock',
        ],
        ['%d', '%s', '%f', '%f', '%s', '%s', '%s']
    );

    if ($variation_inserted === false) {
        wp_delete_post($post_id, true);
        $wpdb->delete($wpdb->prefix . 'fct_product_details', ['post_id' => $post_id], ['%d']);
        wp_send_json_error('Failed to create product variation: ' . $wpdb->last_error);
    }

    // 4. Optionally store lesson_id as post meta for traceability
    update_post_meta($post_id, '_alm_lesson_id', $lesson_id);

    // 5. Build response URLs
    // FluentCart edit URL — adjust if the admin slug differs on your install
    $edit_url = admin_url('admin.php?page=fc-products&action=edit&id=' . $post_id);
    $view_url = get_permalink($post_id) ?: admin_url('admin.php?page=fc-products');

    wp_send_json_success([
        'post_id'  => $post_id,
        'edit_url' => $edit_url,
        'view_url' => $view_url,
    ]);
}
```

---

## Verification checklist after implementing

- [ ] Button only appears on lesson edit pages when FluentCart is active
- [ ] Modal opens with lesson title and description pre-filled
- [ ] Price fields default to `0.00` and accept decimals
- [ ] Clicking outside the modal or Cancel closes it without side effects
- [ ] Submitting creates a `draft` post with `post_type = fc_product`
- [ ] A row is inserted in `wp_fct_product_details` linked by `post_id`
- [ ] A row is inserted in `wp_fct_product_variations` linked by `post_id`
- [ ] Success state shows working "Edit in FluentCart" and "View product" links
- [ ] Error states display the specific error message
- [ ] `_alm_lesson_id` post meta links the FC product back to the ALM lesson

---

## Notes & edge cases

- **FluentCart admin URL:** The edit URL `admin.php?page=fc-products&action=edit&id={post_id}` is the standard FluentCart admin route. Verify it matches the actual install — navigate to a FluentCart product and inspect the URL if unsure.
- **Table prefix:** Always use `$wpdb->prefix` (not hardcoded `wp_`) so multisite installs work.
- **No dependency on FC Model classes:** The AJAX handler uses `wpdb` directly rather than `FluentCart\App\Models\Product` to avoid autoloader issues when FluentCart's classes aren't bootstrapped in the AJAX context.
- **Nonce:** The nonce `alm_create_fc_product` is generated inline in the `render_edit_page()` output and verified server-side in the AJAX handler.
- **Draft default:** Products are created as `draft` so they don't appear publicly before the team has set pricing/images/descriptions correctly in FluentCart.
