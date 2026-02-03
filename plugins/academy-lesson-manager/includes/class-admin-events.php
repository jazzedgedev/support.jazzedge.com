<?php
/**
 * Admin Class for Event Keap Tag Management
 * Adds Keap Tag ID field to je_event post type
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Events {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_keap_tag_meta_box'));
        add_action('save_post_je_event', array($this, 'save_keap_tag_id'));
    }
    
    /**
     * Add meta box for Keap Tag ID
     */
    public function add_keap_tag_meta_box() {
        add_meta_box(
            'alm_event_keap_tag',
            __('Keap Tag ID', 'academy-lesson-manager'),
            array($this, 'render_keap_tag_meta_box'),
            'je_event',
            'side',
            'default'
        );
    }
    
    /**
     * Render meta box content
     */
    public function render_keap_tag_meta_box($post) {
        wp_nonce_field('alm_save_event_keap_tag', 'alm_event_keap_tag_nonce');
        
        $keap_tag_id = get_post_meta($post->ID, 'keap_tag_id', true);
        $keap_tag_id = $keap_tag_id ? intval($keap_tag_id) : 0;
        
        ?>
        <p>
            <label for="keap_tag_id"><?php _e('Keap Tag ID:', 'academy-lesson-manager'); ?></label><br>
            <input type="number" id="keap_tag_id" name="keap_tag_id" value="<?php echo esc_attr($keap_tag_id); ?>" class="small-text" min="0" max="99999" step="1" style="width: 100%;" />
        </p>
        <p class="description">
            <?php _e('Optional. If set, users with this Keap tag will have access to this event regardless of membership level. Useful for intensive coaching sessions tied to specific intensive purchases.', 'academy-lesson-manager'); ?>
        </p>
        <?php
    }
    
    /**
     * Save Keap Tag ID
     */
    public function save_keap_tag_id($post_id) {
        // Check nonce
        if (!isset($_POST['alm_event_keap_tag_nonce']) || !wp_verify_nonce($_POST['alm_event_keap_tag_nonce'], 'alm_save_event_keap_tag')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check post type
        if (get_post_type($post_id) !== 'je_event') {
            return;
        }
        
        // Save keap_tag_id
        $keap_tag_id = isset($_POST['keap_tag_id']) ? intval($_POST['keap_tag_id']) : 0;
        
        if ($keap_tag_id > 0) {
            update_post_meta($post_id, 'keap_tag_id', $keap_tag_id);
        } else {
            delete_post_meta($post_id, 'keap_tag_id');
        }
    }
}

// Initialize
new ALM_Admin_Events();
