<?php
/**
 * Uninstall: drop custom tables and options.
 *
 * Uploaded files under wp-content/uploads/apb-sides/ are NOT removed automatically
 * to avoid accidental data loss; delete that directory manually if desired.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$tables = array(
	'apb_sides_review_queue',
	'apb_sides_side_characters',
	'apb_sides_sides',
	'apb_sides_characters',
	'apb_sides_scripts',
	'apb_sides_ai_extractions',
	'apb_sides_parsed_documents',
	'apb_sides_uploads',
);

foreach ( $tables as $table ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" );
}

delete_option( 'apb_sides_settings' );
delete_option( 'apb_sides_version' );
