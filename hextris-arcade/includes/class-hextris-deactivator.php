<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Hextris_Arcade
 */

class Hextris_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Clean up temporary data and flush rewrite rules.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear any transients
        delete_transient( 'hextris_leaderboard_cache' );
    }
}
