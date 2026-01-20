<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Hextris_Arcade
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if we should delete data on uninstall
$settings = get_option( 'hextris_settings', array() );
$delete_data = isset( $settings['delete_data_on_uninstall'] ) ? $settings['delete_data_on_uninstall'] : false;

if ( $delete_data ) {
    global $wpdb;

    // Delete the scores table
    $table_name = $wpdb->prefix . 'hextris_scores';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

    // Delete plugin options
    delete_option( 'hextris_settings' );
    delete_option( 'hextris_db_version' );

    // Delete any transients
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_hextris_%'" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_hextris_%'" );

    // Clear any cached data
    wp_cache_flush();
}
