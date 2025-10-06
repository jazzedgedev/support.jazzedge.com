<?php
/**
 * Admin pages for Academy Practice Hub
 * 
 * @package Academy_Practice_Hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JPH_Admin_Pages {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Practice Hub', 'academy-practice-hub'),
            __('Practice Hub', 'academy-practice-hub'),
            'manage_options',
            'academy-practice-hub',
            array($this, 'admin_page'),
            'dashicons-format-audio',
            30
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Dashboard', 'academy-practice-hub'),
            __('Dashboard', 'academy-practice-hub'),
            'manage_options',
            'academy-practice-hub',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Students', 'academy-practice-hub'),
            __('Students', 'academy-practice-hub'),
            'manage_options',
            'aph-students',
            array($this, 'students_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Badges', 'academy-practice-hub'),
            __('Badges', 'academy-practice-hub'),
            'manage_options',
            'aph-badges',
            array($this, 'badges_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Lesson Favorites', 'academy-practice-hub'),
            __('Lesson Favorites', 'academy-practice-hub'),
            'manage_options',
            'aph-lesson-favorites',
            array($this, 'lesson_favorites_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Event Tracking', 'academy-practice-hub'),
            __('Event Tracking', 'academy-practice-hub'),
            'manage_options',
            'aph-fluent-crm-events',
            array($this, 'events_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Documentation', 'academy-practice-hub'),
            __('Documentation', 'academy-practice-hub'),
            'manage_options',
            'aph-documentation',
            array($this, 'documentation_page')
        );
        
        add_submenu_page(
            'academy-practice-hub',
            __('Settings', 'academy-practice-hub'),
            __('Settings', 'academy-practice-hub'),
            'manage_options',
            'aph-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_page() {
        // For now, redirect to the original plugin's admin page
        // This maintains functionality while we're using wire-through
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'admin_page')) {
                $main_plugin->admin_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Practice Hub Dashboard</h1>';
        echo '<p>Admin dashboard functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Students page
     */
    public function students_page() {
        // For now, redirect to the original plugin's students page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'students_page')) {
                $main_plugin->students_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Students</h1>';
        echo '<p>Students management functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Badges page
     */
    public function badges_page() {
        // For now, redirect to the original plugin's badges page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'badges_page')) {
                $main_plugin->badges_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Badges</h1>';
        echo '<p>Badges management functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Lesson favorites page
     */
    public function lesson_favorites_page() {
        // For now, redirect to the original plugin's lesson favorites page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'lesson_favorites_page')) {
                $main_plugin->lesson_favorites_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Lesson Favorites</h1>';
        echo '<p>Lesson favorites functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Events page
     */
    public function events_page() {
        // For now, redirect to the original plugin's events page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'events_page')) {
                $main_plugin->events_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Event Tracking</h1>';
        echo '<p>Event tracking functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Documentation page
     */
    public function documentation_page() {
        // For now, redirect to the original plugin's documentation page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'documentation_page')) {
                $main_plugin->documentation_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Documentation</h1>';
        echo '<p>Documentation functionality will be implemented here.</p>';
        echo '</div>';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // For now, redirect to the original plugin's settings page
        if (class_exists('JazzEdge_Practice_Hub')) {
            $main_plugin = JazzEdge_Practice_Hub::get_instance();
            if (method_exists($main_plugin, 'settings_page')) {
                $main_plugin->settings_page();
                return;
            }
        }
        
        // Fallback if original plugin not available
        echo '<div class="wrap">';
        echo '<h1>Settings</h1>';
        echo '<p>Settings functionality will be implemented here.</p>';
        echo '</div>';
    }
}
