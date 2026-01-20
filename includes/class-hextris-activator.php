<?php
/**
 * Fired during plugin activation.
 *
 * @package Hextris_Arcade
 */

class Hextris_Activator {

    /**
     * Activate the plugin.
     *
     * Creates the database table for storing scores and sets default options.
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();

        // Flush rewrite rules for REST API
        flush_rewrite_rules();
    }

    /**
     * Create the database tables.
     */
    private static function create_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'hextris_scores';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            player_name varchar(100) DEFAULT 'Anonymous',
            score int(11) UNSIGNED NOT NULL,
            difficulty varchar(20) DEFAULT 'normal',
            game_duration int(11) UNSIGNED DEFAULT 0,
            max_combo int(11) UNSIGNED DEFAULT 0,
            blocks_cleared int(11) UNSIGNED DEFAULT 0,
            ip_hash varchar(64) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            user_agent varchar(255) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY idx_score (score),
            KEY idx_user_id (user_id),
            KEY idx_created_at (created_at),
            KEY idx_difficulty (difficulty)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'hextris_db_version', HEXTRIS_ARCADE_VERSION );
    }

    /**
     * Set the default plugin options.
     */
    private static function set_default_options() {
        $default_settings = array(
            // General Settings
            'default_width'            => '100%',
            'default_height'           => '600px',
            'default_max_width'        => '800px',

            // Gameplay Settings
            'default_difficulty'       => 'normal',
            'speed_modifier'           => 0.65,
            'combo_time_ms'            => 310,
            'max_rows'                 => 8,

            // Appearance
            'color_scheme'             => 'default',
            'color_1'                  => '#e74c3c',
            'color_2'                  => '#f1c40f',
            'color_3'                  => '#3498db',
            'color_4'                  => '#2ecc71',
            'background_color'         => '#ecf0f1',
            'hexagon_fill_color'       => '#2c3e50',
            'show_combo_timer'         => true,

            // Leaderboard Settings
            'enable_global_leaderboard' => true,
            'scores_to_display'         => 10,
            'track_user_scores'         => true,
            'allow_anonymous_scores'    => true,

            // Advanced
            'load_scripts_globally'     => false,
            'defer_scripts'             => true,
            'enable_debug_mode'         => false,
        );

        if ( false === get_option( 'hextris_settings' ) ) {
            add_option( 'hextris_settings', $default_settings );
        }
    }
}
