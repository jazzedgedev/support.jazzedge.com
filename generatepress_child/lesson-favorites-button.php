<?php
/**
 * Reusable Lesson Favorites Button Component
 * 
 * Usage: Include this file and call show_lesson_favorites_button()
 * 
 * @param array $args {
 *     Optional. Array of arguments.
 *     
 *     @type string $button_text    Button text. Default '⭐ Add Lesson Favorite'.
 *     @type string $button_class   CSS class for button. Default 'lesson-favorites-btn'.
 *     @type string $button_style   Inline CSS styles. Default ''.
 *     @type bool   $show_icon      Whether to show star icon. Default true.
 *     @type string $icon           Icon to display. Default '⭐'.
 *     @type bool   $auto_detect    Whether to auto-detect URL params. Default true.
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display the lesson favorites button
 */
function show_lesson_favorites_button($args = array()) {
    $defaults = array(
        'button_text' => '⭐ Add Lesson Favorite',
        'button_class' => 'lesson-favorites-btn',
        'button_style' => '',
        'show_icon' => true,
        'icon' => '⭐',
        'auto_detect' => true
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return;
    }
    
    // Auto-detect URL parameters if enabled
    $url_params = array();
    if ($args['auto_detect']) {
        $url_params = get_lesson_favorites_url_params();
    }
    
    // Generate unique ID for this button instance
    $button_id = 'lesson-favorites-btn-' . uniqid();
    
    ?>
    <button 
        type="button" 
        id="<?php echo esc_attr($button_id); ?>"
        class="<?php echo esc_attr($args['button_class']); ?>"
        style="<?php echo esc_attr($args['button_style']); ?>"
        data-url-params='<?php echo json_encode($url_params); ?>'
    >
        <?php if ($args['show_icon']): ?>
            <span class="lesson-favorites-icon"><?php echo esc_html($args['icon']); ?></span>
        <?php endif; ?>
        <span class="lesson-favorites-text"><?php echo esc_html($args['button_text']); ?></span>
    </button>
    
    <!-- Lesson Favorites Modal -->
    <div id="lesson-favorites-modal-<?php echo esc_attr($button_id); ?>" class="lesson-favorites-modal" style="display: none;">
        <div class="lesson-favorites-modal-content">
            <div class="lesson-favorites-modal-header">
                <h3>Add Lesson Favorite</h3>
                <span class="lesson-favorites-close">&times;</span>
            </div>
            <div class="lesson-favorites-modal-body">
                <form id="lesson-favorites-form-<?php echo esc_attr($button_id); ?>" class="lesson-favorites-form">
                    <div class="form-group">
                        <label for="lesson-title">Lesson Title *</label>
                        <input type="text" id="lesson-title" name="title" required 
                               value="<?php echo esc_attr($url_params['title'] ?? ''); ?>"
                               placeholder="e.g., Major Scale Practice">
                    </div>
                    
                    <div class="form-group">
                        <label for="lesson-url">Lesson URL *</label>
                        <input type="url" id="lesson-url" name="url" required 
                               value="<?php echo esc_attr($url_params['url'] ?? ''); ?>"
                               placeholder="https://example.com/lesson">
                    </div>
                    
                    <div class="form-group">
                        <label for="lesson-category">Category</label>
                        <select id="lesson-category" name="category">
                            <option value="lesson" <?php selected($url_params['category'] ?? 'lesson', 'lesson'); ?>>Lesson</option>
                            <option value="technique" <?php selected($url_params['category'] ?? '', 'technique'); ?>>Technique</option>
                            <option value="theory" <?php selected($url_params['category'] ?? '', 'theory'); ?>>Theory</option>
                            <option value="ear-training" <?php selected($url_params['category'] ?? '', 'ear-training'); ?>>Ear Training</option>
                            <option value="repertoire" <?php selected($url_params['category'] ?? '', 'repertoire'); ?>>Repertoire</option>
                            <option value="improvisation" <?php selected($url_params['category'] ?? '', 'improvisation'); ?>>Improvisation</option>
                            <option value="other" <?php selected($url_params['category'] ?? '', 'other'); ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="lesson-description">Description</label>
                        <textarea id="lesson-description" name="description" rows="3" 
                                  placeholder="Optional description of the lesson"><?php echo esc_textarea($url_params['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Add to Favorites</button>
                        <button type="button" class="btn-secondary lesson-favorites-cancel">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php
    // Enqueue the JavaScript and CSS
    enqueue_lesson_favorites_assets($button_id);
}

/**
 * Get URL parameters for lesson favorites
 */
function get_lesson_favorites_url_params() {
    $params = array();
    
    if (isset($_GET['title'])) {
        $params['title'] = sanitize_text_field($_GET['title']);
    }
    
    if (isset($_GET['url'])) {
        $params['url'] = esc_url_raw($_GET['url']);
    }
    
    if (isset($_GET['category'])) {
        $params['category'] = sanitize_text_field($_GET['category']);
    }
    
    if (isset($_GET['description'])) {
        $params['description'] = sanitize_textarea_field($_GET['description']);
    }
    
    return $params;
}

/**
 * Enqueue lesson favorites assets
 */
function enqueue_lesson_favorites_assets($button_id) {
    static $assets_enqueued = false;
    
    if ($assets_enqueued) {
        return;
    }
    
    $assets_enqueued = true;
    
    ?>
    <style>
    /* Lesson Favorites Button Styles */
    .lesson-favorites-btn {
        background: #ff6b35;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .lesson-favorites-btn:hover {
        background: #e55a2b;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }
    
    .lesson-favorites-btn:active {
        transform: translateY(0);
    }
    
    /* Modal Styles */
    .lesson-favorites-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .lesson-favorites-modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .lesson-favorites-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .lesson-favorites-modal-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #2A3940;
    }
    
    .lesson-favorites-close {
        font-size: 24px;
        cursor: pointer;
        color: #666;
        line-height: 1;
    }
    
    .lesson-favorites-close:hover {
        color: #333;
    }
    
    .lesson-favorites-modal-body {
        padding: 24px;
    }
    
    .lesson-favorites-form .form-group {
        margin-bottom: 20px;
    }
    
    .lesson-favorites-form label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #2A3940;
    }
    
    .lesson-favorites-form input,
    .lesson-favorites-form select,
    .lesson-favorites-form textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }
    
    .lesson-favorites-form input:focus,
    .lesson-favorites-form select:focus,
    .lesson-favorites-form textarea:focus {
        outline: none;
        border-color: #ff6b35;
    }
    
    .lesson-favorites-form textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 24px;
    }
    
    .btn-primary {
        background: #ff6b35;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: #e55a2b;
    }
    
    .btn-secondary {
        background: #f8f9fa;
        color: #666;
        border: 2px solid #e0e0e0;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        background: #e9ecef;
        border-color: #ccc;
    }
    
    /* Success/Error Messages */
    .lesson-favorites-message {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .lesson-favorites-message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .lesson-favorites-message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle lesson favorites button clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.lesson-favorites-btn')) {
                const button = e.target.closest('.lesson-favorites-btn');
                const buttonId = button.id;
                const modal = document.getElementById('lesson-favorites-modal-' + buttonId);
                if (modal) {
                    modal.style.display = 'flex';
                }
            }
        });
        
        // Handle modal close
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('lesson-favorites-close') || 
                e.target.classList.contains('lesson-favorites-cancel') ||
                e.target.classList.contains('lesson-favorites-modal')) {
                const modal = e.target.closest('.lesson-favorites-modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            }
        });
        
        // Handle form submission
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('lesson-favorites-form')) {
                e.preventDefault();
                
                const form = e.target;
                const formData = new FormData(form);
                const buttonId = form.id.replace('lesson-favorites-form-', '');
                const modal = document.getElementById('lesson-favorites-modal-' + buttonId);
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Adding...';
                submitBtn.disabled = true;
                
                // Remove existing messages
                const existingMessage = form.querySelector('.lesson-favorites-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                // Make AJAX request
                fetch('<?php echo rest_url('jph/v1/lesson-favorites'); ?>', {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const successMsg = document.createElement('div');
                        successMsg.className = 'lesson-favorites-message success';
                        successMsg.textContent = 'Lesson favorite added successfully!';
                        form.insertBefore(successMsg, form.firstChild);
                        
                        // Reset form
                        form.reset();
                        
                        // Close modal after delay
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 1500);
                    } else {
                        // Show error message
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'lesson-favorites-message error';
                        errorMsg.textContent = 'Error: ' + (data.message || 'Unknown error');
                        form.insertBefore(errorMsg, form.firstChild);
                    }
                })
                .catch(error => {
                    // Show error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'lesson-favorites-message error';
                    errorMsg.textContent = 'Error adding lesson favorite. Please try again.';
                    form.insertBefore(errorMsg, form.firstChild);
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            }
        });
    });
    </script>
    <?php
}
