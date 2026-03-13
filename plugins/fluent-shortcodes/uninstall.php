<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fluent_sc_saved");

delete_option('fc_sc_db_version');
delete_option('fc_sc_settings');

$wpdb->query(
    "DELETE FROM {$wpdb->options} 
     WHERE option_name LIKE '_transient_fc_sc_%' 
     OR option_name LIKE '_transient_timeout_fc_sc_%'"
);
