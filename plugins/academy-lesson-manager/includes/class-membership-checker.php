<?php
/**
 * ALM Membership Checker Class
 * 
 * Handles all membership checking logic moved from oxygen-functions.php
 * 
 * @package Academy_Lesson_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class ALM_Membership_Checker {
    
    /**
     * Initialize the class and register global functions
     */
    public static function init() {
        // Register global function wrappers for backward compatibility
        if (!function_exists('je_has_blocking_tags')) {
            function je_has_blocking_tags() {
                return ALM_Membership_Checker::has_blocking_tags();
            }
        }
        
        if (!function_exists('je_return_active_member')) {
            function je_return_active_member() {
                return ALM_Membership_Checker::return_active_member();
            }
        }
        
        if (!function_exists('je_return_membership_expired')) {
            function je_return_membership_expired() {
                return ALM_Membership_Checker::return_membership_expired();
            }
        }
        
        if (!function_exists('je_return_membership_level')) {
            function je_return_membership_level($return = 'nicename') {
                return ALM_Membership_Checker::return_membership_level($return);
            }
        }
        
        if (!function_exists('je_return_membership_level_old')) {
            function je_return_membership_level_old($return = 'product') {
                return ALM_Membership_Checker::return_membership_level_old($return);
            }
        }
        
        if (!function_exists('ja_return_user_membership_data')) {
            function ja_return_user_membership_data() {
                return ALM_Membership_Checker::return_user_membership_data();
            }
        }
        
        if (!function_exists('ja_is_premier')) {
            function ja_is_premier() {
                return ALM_Membership_Checker::is_premier();
            }
        }
        
        if (!function_exists('ja_is_jazzedge_member')) {
            function ja_is_jazzedge_member() {
                return ALM_Membership_Checker::is_jazzedge_member();
            }
        }
        
        if (!function_exists('ja_is_jpl_member')) {
            function ja_is_jpl_member() {
                return ALM_Membership_Checker::is_jpl_member();
            }
        }
        
        if (!function_exists('ja_is_hsp_member')) {
            function ja_is_hsp_member() {
                return ALM_Membership_Checker::is_hsp_member();
            }
        }
        
        if (!function_exists('ja_is_classes_only_member')) {
            function ja_is_classes_only_member() {
                return ALM_Membership_Checker::is_classes_only_member();
            }
        }
    }
    
    /**
     * Check if user has any blocking tags that should deny access
     * @return bool True if user has blocking tags, false otherwise
     */
    public static function has_blocking_tags() {
        if (!function_exists('memb_hasAnyTags')) {
            return false;
        }
        
        $blocking_tags_str = get_option('alm_keap_blocking_tags', '');
        if (empty($blocking_tags_str)) {
            return false;
        }
        
        // Parse comma-separated tag IDs
        $blocking_tags = array_map('trim', explode(',', $blocking_tags_str));
        $blocking_tags = array_filter($blocking_tags, 'is_numeric');
        $blocking_tags = array_map('intval', $blocking_tags);
        
        if (empty($blocking_tags)) {
            return false;
        }
        
        // Check if user has any blocking tags
        return memb_hasAnyTags($blocking_tags) === true;
    }
    
    /**
     * Check if user has active membership
     * Now includes check for free tag from ALM settings
     * @return string 'true' or 'false'
     */
    public static function return_active_member() {
        // Check for blocking tags first - if user has blocking tags, deny access
        if (self::has_blocking_tags()) {
            return 'false';
        }
        
        if (self::return_membership_expired() == 'true') { 
            return 'false'; 
        }
        
        // Check for free tag from ALM settings
        $keap_tags = get_option('alm_keap_tags', array('free' => ''));
        $free_tag_ids = !empty($keap_tags['free']) ? array_map('trim', explode(',', $keap_tags['free'])) : array();
        $free_tag_ids = array_filter($free_tag_ids, 'is_numeric');
        
        if (!empty($free_tag_ids) && function_exists('memb_hasAnyTags')) {
            $free_tag_ids_int = array_map('intval', $free_tag_ids);
            if (memb_hasAnyTags($free_tag_ids_int) === true) {
                return 'true';
            }
        }
        
        $memberships = [
            'ACADEMY_PREMIER', 'ACADEMY_STUDIO', 'JA_MONTHLY_CLASSES_ONLY', 'JA_MONTHLY_LSN_ONLY',
            'JA_LESSONS_90DAYS', 'ACADEMY_SONG', 'ACADEMY_ACADEMY', 'ACADEMY_ACADEMY_1YR',
            'ACADEMY_ACADEMY_NC', 'JA_MONTHLY_LSN_CLASSES', 'JA_MONTHLY_LSN_COACHING', 'JA_MONTHLY_STUDIO', 'JA_YEAR_STUDIO',
            'JA_MONTHLY_PREMIER', 'JA_MONTHLY_PREMIER_DMP', 'JA_YEAR_LSN_CLASSES', 'JA_YEAR_LSN_COACHING',
            'JA_YEAR_LSN_ONLY', 'JA_YEAR_CLASSES_ONLY', 'JA_YEAR_PREMIER', 'JA_MONTHLY_STUDIO_DMP', 'JA_MONTHLY_ESSENTIALS', 'JA_YEAR_ESSENTIALS'
        ];
        
        $payment_failed = do_shortcode('[memb_has_any_tag tagid=7772]');
        if ($payment_failed === 'Yes') { 
            return 'false'; 
        }
        
        foreach ($memberships as $membership) {
            if (memb_hasMembership($membership) === true) {
                return 'true';
            }
        }
        
        return 'false';
    }
    
    /**
     * Check if membership has expired
     * @return string 'true' if expired, 'false' if not expired or no expiration date
     */
    public static function return_membership_expired() {
        $academy_expiration_date = strtotime(memb_getContactField('_AcademyExpirationDate'));
        if (empty($academy_expiration_date)) {
            return 'false';
        }
        $now = time();
        if ($now <= $academy_expiration_date) {
            return 'false';
        } else {
            return 'true';
        }
    }
    
    /**
     * Return user membership data
     * @return array Membership data array
     */
    public static function return_user_membership_data() {
        // Check for blocking tags first - if user has blocking tags, return empty/free membership
        if (self::has_blocking_tags()) {
            $memb_data = array();
            $memb_data['membership_name'] = 'Free';
            $memb_data['membership_product'] = 'Free';
            $memb_data['membership_numeric'] = 0;
            $memb_data['membership_level'] = 'free';
            $memb_data['fname'] = do_shortcode('[memb_contact fields=FirstName]');
            $memb_data['lname'] = do_shortcode('[memb_contact fields=LastName]');
            $memb_data['email'] = do_shortcode('[memb_contact fields=Email]');
            $memb_data['tags'] = '';
            $memb_data['keap_id'] = do_shortcode('[memb_contact fields=Id]');
            return $memb_data;
        }

        $essentials_access = memb_hasAnyTags([10290,10288]);
        $premier_access = memb_hasAnyTags([9821,9813,10142]);
        $studio_access = memb_hasAnyTags([9954,10136,9807,9827,9819,9956,10136]);
        
        if ($essentials_access || $essentials_access === TRUE || $essentials_access ==='Yes' || $essentials_access === 'true') {
            $memb_data['membership_name'] = 'Essentials';
            $memb_data['membership_product'] = 'Essentials';
            $memb_data['membership_numeric'] = 1;
            $memb_data['membership_level'] = 'essentials';
        } 
        if ($studio_access || $studio_access === TRUE || $studio_access ==='Yes' || $studio_access === 'true') {
            $memb_data['membership_name'] = 'Studio';
            $memb_data['membership_product'] = 'Studio';
            $memb_data['membership_numeric'] = 2;
            $memb_data['membership_level'] = 'studio';
        } 
        if ($premier_access || $premier_access === TRUE || $premier_access === 'Yes' || $premier_access === 'true')  {
            $memb_data['membership_name'] = 'Premier';
            $memb_data['membership_product'] = 'Premier';
            $memb_data['membership_numeric'] = 3;
            $memb_data['membership_level'] = 'premier';
        }
        
        $memb_data['fname'] = do_shortcode('[memb_contact fields=FirstName]');
        $memb_data['lname'] = do_shortcode('[memb_contact fields=LastName]');
        $memb_data['email'] = do_shortcode('[memb_contact fields=Email]');
        $memb_data['tags'] = '';
        $memb_data['keap_id'] = do_shortcode('[memb_contact fields=Id]');
        return $memb_data;
    }
    
    /**
     * Return membership level
     * @param string $return 'nicename', 'product', or 'numeric'
     * @return string|int Membership level
     */
    public static function return_membership_level($return = 'nicename') {
        // Retrieve user membership data from session or Keap
        $membership_data = self::return_user_membership_data();

        // Default to Free membership if no membership data is available
        if (empty($membership_data['membership_level'])) {
            if ($return == 'product') { return 'Free'; }
            elseif ($return == 'nicename') { return 'Free'; }
            elseif ($return == 'numeric') { return 0; }
        }

        // Determine membership level based on the retrieved data
        switch ($membership_data['membership_level']) {
            case 'premier':
                if ($return == 'product') { return 'Premier'; }
                elseif ($return == 'nicename') { return 'Premier'; }
                elseif ($return == 'numeric') { return 99; }
                break;

            case 'studio':
                if ($return == 'product') { return 'Studio'; }
                elseif ($return == 'nicename') { return 'Studio'; }
                elseif ($return == 'numeric') { return 20; }
                break;

            case 'essentials':
                if ($return == 'product') { return 'Essentials'; }
                elseif ($return == 'nicename') { return 'Essentials'; }
                elseif ($return == 'numeric') { return 1; }
                break;

            case 'free':
            default:
                if ($return == 'product') { return 'Free'; }
                elseif ($return == 'nicename') { return 'Free'; }
                elseif ($return == 'numeric') { return 0; }
                break;
        }

        // Fallback if no condition matched
        return $return == 'numeric' ? 0 : 'Free';
    }
    
    /**
     * Return membership level (old method - checks SKUs directly)
     * @param string $return 'product', 'nicename', or 'numeric'
     * @return string|int Membership level
     */
    public static function return_membership_level_old($return = 'product') {
        $academy_level = '';
        
        if (memb_hasMembership('ACADEMY_PREMIER') == TRUE) { 
            if ($return == 'product') { return 'ACADEMY_PREMIER'; }
            elseif ($return == 'nicename') { return 'Premier'; }
            elseif ($return == 'numeric') { return 99; }		
        } elseif (memb_hasMembership('JA_MONTHLY_PREMIER') == TRUE) { 
            if ($return == 'product') { return 'JA_MONTHLY_PREMIER'; }
            elseif ($return == 'nicename') { return 'Premier'; }
            elseif ($return == 'numeric') { return 99; }		
        } elseif (memb_hasMembership('JA_MONTHLY_PREMIER_DMP') == TRUE) { 
            if ($return == 'product') { return 'JA_MONTHLY_PREMIER_DMP'; }
            elseif ($return == 'nicename') { return 'Premier (DMP)'; }
            elseif ($return == 'numeric') { return 99; }		
        } elseif (memb_hasMembership('JA_YEAR_PREMIER') == TRUE) { 
            if ($return == 'product') { return 'JA_YEAR_PREMIER'; }
            elseif ($return == 'nicename') { return 'Premier (Year)'; }
            elseif ($return == 'numeric') { return 99; }		
        } elseif (memb_hasMembership('ACADEMY_ACADEMY') == TRUE) {
            if ($return == 'product') { return 'ACADEMY_ACADEMY'; }
            elseif ($return == 'nicename') { return 'Academy'; }
            elseif ($return == 'numeric') { return 20; }	
        } elseif (memb_hasMembership('ACADEMY_ACADEMY_NC') == TRUE) {
            if ($return == 'product') { return 'ACADEMY_ACADEMY_NC'; }
            elseif ($return == 'nicename') { return 'Academy (No Coaching)'; }
            elseif ($return == 'numeric') { return 20; }	
        } elseif (memb_hasMembership('JA_MONTHLY_LSN_CLASSES') == TRUE) {
            if ($return == 'product') { return 'JA_MONTHLY_LSN_CLASSES'; }
            elseif ($return == 'nicename') { return 'Lessons & Classes'; }
            elseif ($return == 'numeric') { return 20; }	
        } elseif (memb_hasMembership('JA_MONTHLY_LSN_COACHING') == TRUE) {
            if ($return == 'product') { return 'JA_MONTHLY_LSN_COACHING'; }
            elseif ($return == 'nicename') { return 'Lessons & Coaching'; }
            elseif ($return == 'numeric') { return 20; }	
        } elseif (memb_hasMembership('JA_MONTHLY_LSN_ONLY') == TRUE) {
            if ($return == 'product') { return 'JA_MONTHLY_LSN_ONLY'; }
            elseif ($return == 'nicename') { return 'Lessons Only'; }
            elseif ($return == 'numeric') { return 20; }	
        } elseif (memb_hasMembership('JA_MONTHLY_CLASSES_ONLY') == TRUE) {
            if ($return == 'product') { return 'JA_MONTHLY_CLASSES_ONLY'; }
            elseif ($return == 'nicename') { return 'Classes Only'; }
            elseif ($return == 'numeric') { return 20; }	
        } elseif (memb_hasMembership('JA_YEAR_LSN_CLASSES') == TRUE) {
            if ($return == 'product') { return 'JA_YEAR_LSN_CLASSES'; }
            elseif ($return == 'nicename') { return 'Lessons & Classes (Year)'; }
            elseif ($return == 'numeric') { return 21; }	
        } elseif (memb_hasMembership('JA_YEAR_LSN_COACHING') == TRUE) {
            if ($return == 'product') { return 'JA_YEAR_LSN_COACHING'; }
            elseif ($return == 'nicename') { return 'Lessons & Coaching (Year)'; }
            elseif ($return == 'numeric') { return 21; }	
        } elseif (memb_hasMembership('JA_YEAR_LSN_ONLY') == TRUE) {
            if ($return == 'product') { return 'JA_YEAR_LSN_ONLY'; }
            elseif ($return == 'nicename') { return 'Lessons Only (Year)'; }
            elseif ($return == 'numeric') { return 21; }	
        } elseif (memb_hasMembership('ACADEMY_FREE') == TRUE) {
            if ($return == 'product') { return 'ACADEMY_FREE'; }
            elseif ($return == 'nicename') { return 'Free'; }
            elseif ($return == 'numeric') { return 0; }	
        } else {
            return 0; // Default to no membership or unknown
        }
    }
    
    /**
     * Check if user is Premier member
     * @return string 'true' or 'false'
     */
    public static function is_premier() {
        $premier_access = do_shortcode('[memb_has_any_tag tagid=9821,9813,10142]');
        return ($premier_access === 'Yes') ? 'true' : 'false';
    }
    
    /**
     * Check if user is JazzEdge member
     * @return string 'true' or 'false'
     */
    public static function is_jazzedge_member() {
        return (memb_hasAnyTags([8817, 8649, 8645, 8859, 8861]) === true) ? 'true' : 'false';
    }
    
    /**
     * Check if user is JPL member
     * @return string 'true' or 'false'
     */
    public static function is_jpl_member() {
        return (memb_hasAnyTags([9403, 9405]) === true) ? 'true' : 'false';
    }
    
    /**
     * Check if user is HSP member
     * @return string 'true' or 'false'
     */
    public static function is_hsp_member() {
        return (memb_hasAnyTags([7548, 7574, 7578]) === true) ? 'true' : 'false';
    }
    
    /**
     * Check if user is classes only member
     * @return string 'true' or 'false'
     */
    public static function is_classes_only_member() {
        return (memb_hasMembership('JA_YEAR_CLASSES_ONLY') === true || memb_hasMembership('JA_MONTHLY_CLASSES_ONLY') === true) ? 'true' : 'false';
    }
}

