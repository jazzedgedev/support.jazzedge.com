<?php
/**
 * ALM Admin Membership Pricing Class
 * 
 * Handles membership pricing settings as a tab in Academy Lesson Manager Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Admin_Membership_Pricing {
    
    private $option_group = 'je_membership_pricing';
    private $option_name = 'je_membership_pricing_settings';
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting($this->option_group, $this->option_name, array($this, 'sanitize_settings'));
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitize each membership tier
        $tiers = array('essentials', 'studio', 'premier');
        
        foreach ($tiers as $tier) {
            if (isset($input[$tier])) {
                $sanitized[$tier] = array(
                    'retail_monthly' => isset($input[$tier]['retail_monthly']) ? floatval($input[$tier]['retail_monthly']) : 0,
                    'retail_yearly' => isset($input[$tier]['retail_yearly']) ? floatval($input[$tier]['retail_yearly']) : 0,
                    'order_form_monthly' => isset($input[$tier]['order_form_monthly']) ? esc_url_raw($input[$tier]['order_form_monthly']) : '',
                    'order_form_yearly' => isset($input[$tier]['order_form_yearly']) ? esc_url_raw($input[$tier]['order_form_yearly']) : '',
                    'sale_enabled' => isset($input[$tier]['sale_enabled']) ? 1 : 0,
                    'sale_monthly' => isset($input[$tier]['sale_monthly']) ? floatval($input[$tier]['sale_monthly']) : 0,
                    'sale_yearly' => isset($input[$tier]['sale_yearly']) ? floatval($input[$tier]['sale_yearly']) : 0,
                    'sale_start_date' => isset($input[$tier]['sale_start_date']) ? sanitize_text_field($input[$tier]['sale_start_date']) : '',
                    'sale_end_date' => isset($input[$tier]['sale_end_date']) ? sanitize_text_field($input[$tier]['sale_end_date']) : '',
                    'sale_order_form_monthly' => isset($input[$tier]['sale_order_form_monthly']) ? esc_url_raw($input[$tier]['sale_order_form_monthly']) : '',
                    'sale_order_form_yearly' => isset($input[$tier]['sale_order_form_yearly']) ? esc_url_raw($input[$tier]['sale_order_form_yearly']) : '',
                    'doorbuster_enabled' => isset($input[$tier]['doorbuster_enabled']) ? 1 : 0,
                    'doorbuster_yearly' => isset($input[$tier]['doorbuster_yearly']) ? floatval($input[$tier]['doorbuster_yearly']) : 0,
                    'doorbuster_start_date' => isset($input[$tier]['doorbuster_start_date']) ? sanitize_text_field($input[$tier]['doorbuster_start_date']) : '',
                    'doorbuster_end_date' => isset($input[$tier]['doorbuster_end_date']) ? sanitize_text_field($input[$tier]['doorbuster_end_date']) : '',
                    'doorbuster_order_form_yearly' => isset($input[$tier]['doorbuster_order_form_yearly']) ? esc_url_raw($input[$tier]['doorbuster_order_form_yearly']) : '',
                );
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only enqueue on the settings page with memberships tab
        // Hook format: {parent_slug}_page_{menu_slug}
        if (strpos($hook, 'academy-manager-settings') === false) {
            return;
        }
        
        // Check if we're on the memberships tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        if ($current_tab !== 'memberships') {
            return;
        }
        
        wp_enqueue_style('jquery-ui-datepicker', 'https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css');
        wp_enqueue_script('jquery-ui-datepicker');
    }
    
    /**
     * Render membership pricing tab content
     */
    public function render_tab() {
        $settings = get_option($this->option_name, array());
        
        // Default values
        $defaults = array(
            'essentials' => array(
                'retail_monthly' => 0,
                'retail_yearly' => 175,
                'order_form_monthly' => '',
                'order_form_yearly' => 'https://ft217.infusionsoft.com/app/orderForms/JA_YEAR_ESSENTIALS',
            ),
            'studio' => array(
                'retail_monthly' => 39,
                'retail_yearly' => 390,
                'order_form_monthly' => 'https://ft217.infusionsoft.com/app/orderForms/ja_monthly_studio_retail',
                'order_form_yearly' => 'https://ft217.infusionsoft.com/app/orderForms/ja_yearly_studio',
            ),
            'premier' => array(
                'retail_monthly' => 59,
                'retail_yearly' => 649,
                'order_form_monthly' => '',
                'order_form_yearly' => 'https://ft217.infusionsoft.com/app/orderForms/ja_yearly_premier_retail',
            ),
        );
        
        $settings = wp_parse_args($settings, $defaults);
        
        ?>
        <div class="alm-settings-section">
            <h2><?php _e('Membership Pricing Management', 'academy-lesson-manager'); ?></h2>
            <p class="description"><?php _e('Manage pricing, order form links, and sale dates for all membership tiers.', 'academy-lesson-manager'); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields($this->option_group); ?>
                
                <div class="je-pricing-admin je-pricing-cards">
                    <?php
                    $tiers = array(
                        'essentials' => array('label' => 'Essentials', 'monthly' => false),
                        'studio' => array('label' => 'Studio', 'monthly' => true),
                        'premier' => array('label' => 'Premier', 'monthly' => false),
                    );
                    
                    foreach ($tiers as $tier_key => $tier_info):
                        $tier_data = isset($settings[$tier_key]) ? $settings[$tier_key] : array();
                        $tier_data = wp_parse_args($tier_data, $defaults[$tier_key]);
                    ?>
                    <div class="je-tier-card">
                        <div class="je-tier-card-header">
                            <h3><?php echo esc_html($tier_info['label']); ?></h3>
                        </div>
                        <div class="je-tier-card-body">
                            <div class="je-pricing-field-group">
                                <label class="je-pricing-label">Retail Pricing</label>
                                <div class="je-pricing-inputs">
                                    <?php if ($tier_info['monthly']): ?>
                                    <div class="je-pricing-input-row">
                                        <label>Monthly:</label>
                                        <span class="je-currency">$</span>
                                        <input type="number" step="0.01" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][retail_monthly]'); ?>" value="<?php echo esc_attr($tier_data['retail_monthly']); ?>" />
                                    </div>
                                    <?php endif; ?>
                                    <div class="je-pricing-input-row">
                                        <label>Yearly:</label>
                                        <span class="je-currency">$</span>
                                        <input type="number" step="0.01" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][retail_yearly]'); ?>" value="<?php echo esc_attr($tier_data['retail_yearly']); ?>" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="je-pricing-field-group">
                                <label class="je-pricing-label">Order Form Links</label>
                                <div class="je-pricing-inputs">
                                    <?php if ($tier_info['monthly']): ?>
                                    <div class="je-pricing-input-row">
                                        <label>Monthly Link:</label>
                                        <input type="url" class="regular-text" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][order_form_monthly]'); ?>" value="<?php echo esc_attr($tier_data['order_form_monthly']); ?>" />
                                    </div>
                                    <?php endif; ?>
                                    <div class="je-pricing-input-row">
                                        <label>Yearly Link:</label>
                                        <input type="url" class="regular-text" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][order_form_yearly]'); ?>" value="<?php echo esc_attr($tier_data['order_form_yearly']); ?>" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="je-pricing-field-group">
                                <label class="je-pricing-label">
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][sale_enabled]'); ?>" value="1" <?php checked($tier_data['sale_enabled'] ?? 0, 1); ?> />
                                    Enable Sale Pricing
                                </label>
                            </div>
                            
                            <div class="je-pricing-field-group sale-pricing-row" style="<?php echo (empty($tier_data['sale_enabled'])) ? 'display: none;' : ''; ?>">
                                <label class="je-pricing-label">Sale Prices</label>
                                <div class="je-pricing-inputs">
                                    <?php if ($tier_info['monthly']): ?>
                                    <div class="je-pricing-input-row">
                                        <label>Monthly Sale:</label>
                                        <span class="je-currency">$</span>
                                        <input type="number" step="0.01" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][sale_monthly]'); ?>" value="<?php echo esc_attr($tier_data['sale_monthly'] ?? 0); ?>" />
                                    </div>
                                    <?php endif; ?>
                                    <div class="je-pricing-input-row">
                                        <label>Yearly Sale:</label>
                                        <span class="je-currency">$</span>
                                        <input type="number" step="0.01" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][sale_yearly]'); ?>" value="<?php echo esc_attr($tier_data['sale_yearly'] ?? 0); ?>" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="je-pricing-field-group sale-pricing-row" style="<?php echo (empty($tier_data['sale_enabled'])) ? 'display: none;' : ''; ?>">
                                <label class="je-pricing-label">Sale Dates</label>
                                <div class="je-pricing-inputs">
                                    <div class="je-pricing-input-row">
                                        <label>Start Date:</label>
                                        <input type="text" class="datepicker" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][sale_start_date]'); ?>" value="<?php echo esc_attr($tier_data['sale_start_date'] ?? ''); ?>" />
                                    </div>
                                    <div class="je-pricing-input-row">
                                        <label>End Date:</label>
                                        <input type="text" class="datepicker" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][sale_end_date]'); ?>" value="<?php echo esc_attr($tier_data['sale_end_date'] ?? ''); ?>" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="je-pricing-field-group sale-pricing-row" style="<?php echo (empty($tier_data['sale_enabled'])) ? 'display: none;' : ''; ?>">
                                <label class="je-pricing-label">Sale Order Form Links</label>
                                <div class="je-pricing-inputs">
                                    <?php if ($tier_info['monthly']): ?>
                                    <div class="je-pricing-input-row">
                                        <label>Monthly Sale Link:</label>
                                        <input type="url" class="regular-text" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][sale_order_form_monthly]'); ?>" value="<?php echo esc_attr($tier_data['sale_order_form_monthly'] ?? ''); ?>" />
                                    </div>
                                    <?php endif; ?>
                                    <div class="je-pricing-input-row">
                                        <label>Yearly Sale Link:</label>
                                        <input type="url" class="regular-text" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][sale_order_form_yearly]'); ?>" value="<?php echo esc_attr($tier_data['sale_order_form_yearly'] ?? ''); ?>" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="je-pricing-field-group">
                                <label class="je-pricing-label">
                                    <input type="checkbox" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][doorbuster_enabled]'); ?>" value="1" <?php checked($tier_data['doorbuster_enabled'] ?? 0, 1); ?> />
                                    Enable Doorbuster Pricing
                                </label>
                            </div>
                            
                            <div class="je-pricing-field-group doorbuster-pricing-row" style="<?php echo (empty($tier_data['doorbuster_enabled'])) ? 'display: none;' : ''; ?>">
                                <label class="je-pricing-label">Doorbuster Price</label>
                                <div class="je-pricing-inputs">
                                    <div class="je-pricing-input-row">
                                        <label>Yearly Doorbuster:</label>
                                        <span class="je-currency">$</span>
                                        <input type="number" step="0.01" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][doorbuster_yearly]'); ?>" value="<?php echo esc_attr($tier_data['doorbuster_yearly'] ?? 0); ?>" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="je-pricing-field-group doorbuster-pricing-row" style="<?php echo (empty($tier_data['doorbuster_enabled'])) ? 'display: none;' : ''; ?>">
                                <label class="je-pricing-label">Doorbuster Dates</label>
                                <div class="je-pricing-inputs">
                                    <div class="je-pricing-input-row">
                                        <label>Start Date:</label>
                                        <input type="text" class="datepicker" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][doorbuster_start_date]'); ?>" value="<?php echo esc_attr($tier_data['doorbuster_start_date'] ?? ''); ?>" />
                                    </div>
                                    <div class="je-pricing-input-row">
                                        <label>End Date:</label>
                                        <input type="text" class="datepicker" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][doorbuster_end_date]'); ?>" value="<?php echo esc_attr($tier_data['doorbuster_end_date'] ?? ''); ?>" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="je-pricing-field-group doorbuster-pricing-row" style="<?php echo (empty($tier_data['doorbuster_enabled'])) ? 'display: none;' : ''; ?>">
                                <label class="je-pricing-label">Doorbuster Order Form Link</label>
                                <div class="je-pricing-inputs">
                                    <div class="je-pricing-input-row">
                                        <label>Yearly Doorbuster Link:</label>
                                        <input type="url" class="regular-text" name="<?php echo esc_attr($this->option_name . '[' . $tier_key . '][doorbuster_order_form_yearly]'); ?>" value="<?php echo esc_attr($tier_data['doorbuster_order_form_yearly'] ?? ''); ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <style>
        .je-pricing-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        
        .je-tier-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        
        .je-tier-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
        }
        
        .je-tier-card-header {
            background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
            color: #fff;
            padding: 16px 20px;
            border-bottom: 2px solid #005a87;
        }
        
        .je-tier-card-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
        }
        
        .je-tier-card-body {
            padding: 20px;
            flex: 1;
        }
        
        .je-pricing-field-group {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e5e5;
        }
        
        .je-pricing-field-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .je-pricing-label {
            display: block;
            font-weight: 600;
            color: #23282d;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .je-pricing-label input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .je-pricing-inputs {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .je-pricing-input-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .je-pricing-input-row label {
            min-width: 100px;
            font-weight: 500;
            font-size: 13px;
            color: #555;
            margin: 0;
        }
        
        .je-pricing-input-row .je-currency {
            color: #555;
            font-weight: 600;
        }
        
        .je-pricing-input-row input[type="number"],
        .je-pricing-input-row input[type="text"],
        .je-pricing-input-row input[type="url"] {
            flex: 1;
            min-width: 0;
        }
        
        .je-pricing-input-row input[type="url"].regular-text {
            width: 100%;
        }
        
        @media (max-width: 1400px) {
            .je-pricing-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 782px) {
            .je-pricing-cards {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd'
            });
            
            $('input[type="checkbox"][name*="[sale_enabled]"]').change(function() {
                var card = $(this).closest('.je-tier-card');
                var rows = card.find('.sale-pricing-row');
                if ($(this).is(':checked')) {
                    rows.slideDown(200);
                } else {
                    rows.slideUp(200);
                }
            });
            
            $('input[type="checkbox"][name*="[doorbuster_enabled]"]').change(function() {
                var card = $(this).closest('.je-tier-card');
                var rows = card.find('.doorbuster-pricing-row');
                if ($(this).is(':checked')) {
                    rows.slideDown(200);
                } else {
                    rows.slideUp(200);
                }
            });
        });
        </script>
        <?php
    }
}

/**
 * Get active pricing for a membership tier
 * Returns current pricing based on doorbuster, sale, or retail (in priority order)
 */
function je_get_membership_pricing($tier, $billing = 'yearly') {
    $settings = get_option('je_membership_pricing_settings', array());
    
    if (!isset($settings[$tier])) {
        return null;
    }
    
    $tier_data = $settings[$tier];
    $today = current_time('Y-m-d');
    
    // Check if doorbuster is active (highest priority)
    $doorbuster_active = false;
    if (!empty($tier_data['doorbuster_enabled'])) {
        $start_date = isset($tier_data['doorbuster_start_date']) ? $tier_data['doorbuster_start_date'] : '';
        $end_date = isset($tier_data['doorbuster_end_date']) ? $tier_data['doorbuster_end_date'] : '';
        
        if ($start_date && $end_date) {
            $doorbuster_active = ($today >= $start_date && $today <= $end_date);
        }
    }
    
    // Check if sale is active (second priority)
    $sale_active = false;
    if (!empty($tier_data['sale_enabled']) && !$doorbuster_active) {
        $start_date = isset($tier_data['sale_start_date']) ? $tier_data['sale_start_date'] : '';
        $end_date = isset($tier_data['sale_end_date']) ? $tier_data['sale_end_date'] : '';
        
        if ($start_date && $end_date) {
            $sale_active = ($today >= $start_date && $today <= $end_date);
        }
    }
    
    // Determine which pricing to use (doorbuster > sale > retail)
    if ($doorbuster_active) {
        $price_key = 'doorbuster_' . $billing;
        $order_form_key = 'doorbuster_order_form_' . $billing;
        $pricing_type = 'doorbuster';
    } elseif ($sale_active) {
        $price_key = 'sale_' . $billing;
        $order_form_key = 'sale_order_form_' . $billing;
        $pricing_type = 'sale';
    } else {
        $price_key = 'retail_' . $billing;
        $order_form_key = 'order_form_' . $billing;
        $pricing_type = 'retail';
    }
    
    $result = array(
        'price' => isset($tier_data[$price_key]) ? floatval($tier_data[$price_key]) : 0,
        'order_form' => isset($tier_data[$order_form_key]) ? $tier_data[$order_form_key] : '',
        'is_sale' => $sale_active,
        'is_doorbuster' => $doorbuster_active,
        'pricing_type' => $pricing_type,
        'retail_price' => isset($tier_data['retail_' . $billing]) ? floatval($tier_data['retail_' . $billing]) : 0,
    );
    
    // Add doorbuster end date for countdown timer
    if ($doorbuster_active && isset($tier_data['doorbuster_end_date'])) {
        $result['doorbuster_end_date'] = $tier_data['doorbuster_end_date'];
    }
    
    return $result;
}

/**
 * Get all membership pricing for display
 */
function je_get_all_membership_pricing() {
    return array(
        'essentials' => array(
            'yearly' => je_get_membership_pricing('essentials', 'yearly'),
        ),
        'studio' => array(
            'monthly' => je_get_membership_pricing('studio', 'monthly'),
            'yearly' => je_get_membership_pricing('studio', 'yearly'),
        ),
        'premier' => array(
            'yearly' => je_get_membership_pricing('premier', 'yearly'),
        ),
    );
}

