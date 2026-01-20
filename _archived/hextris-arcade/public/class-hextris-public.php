<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package Hextris_Arcade
 */

class Hextris_Public {

    /**
     * The ID of this plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        $this->plugin_name = 'hextris-arcade';
        $this->version     = HEXTRIS_ARCADE_VERSION;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function register_styles() {
        wp_register_style(
            'hextris-public',
            HEXTRIS_ARCADE_URL . 'public/css/hextris-public.css',
            array(),
            $this->version,
            'all'
        );

        wp_register_style(
            'hextris-responsive',
            HEXTRIS_ARCADE_URL . 'public/css/hextris-responsive.css',
            array( 'hextris-public' ),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function register_scripts() {
        $settings = get_option( 'hextris_settings', array() );
        $defer    = isset( $settings['defer_scripts'] ) && $settings['defer_scripts'];

        // Vendor scripts
        wp_register_script(
            'hextris-hammer',
            HEXTRIS_ARCADE_URL . 'public/js/vendor/hammer.min.js',
            array(),
            '2.0.8',
            true
        );

        wp_register_script(
            'hextris-keypress',
            HEXTRIS_ARCADE_URL . 'public/js/vendor/keypress.min.js',
            array(),
            '2.1.5',
            true
        );

        // Main game bundle
        wp_register_script(
            'hextris-game',
            HEXTRIS_ARCADE_URL . 'public/js/hextris-game.js',
            array( 'hextris-hammer', 'hextris-keypress' ),
            $this->version,
            true
        );

        // WordPress bridge
        wp_register_script(
            'hextris-wp-bridge',
            HEXTRIS_ARCADE_URL . 'public/js/hextris-wp-bridge.js',
            array( 'hextris-game' ),
            $this->version,
            true
        );
    }

    /**
     * Enqueue game assets when the shortcode is present.
     */
    public function enqueue_assets_for_shortcode() {
        if ( ! is_singular() ) {
            return;
        }

        $post = get_post();
        if ( ! $post instanceof WP_Post ) {
            return;
        }

        if ( ! has_shortcode( $post->post_content, 'hextris' ) ) {
            return;
        }

        wp_enqueue_style( 'hextris-public' );
        wp_enqueue_style( 'hextris-responsive' );
        $this->enqueue_game_scripts();
    }

    /**
     * Enqueue game scripts and localize data.
     */
    public function enqueue_game_scripts() {
        wp_enqueue_script( 'hextris-hammer' );
        wp_enqueue_script( 'hextris-keypress' );
        wp_enqueue_script( 'hextris-game' );
        wp_enqueue_script( 'hextris-wp-bridge' );

        $settings = get_option( 'hextris_settings', array() );

        // Localize script data
        wp_localize_script(
            'hextris-wp-bridge',
            'hextrisGlobal',
            array(
                'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
                'restUrl'      => esc_url_raw( rest_url( 'hextris/v1' ) ),
                'nonce'        => wp_create_nonce( 'hextris_submit_score' ),
                'restNonce'    => wp_create_nonce( 'wp_rest' ),
                'userId'       => get_current_user_id(),
                'isLoggedIn'   => is_user_logged_in(),
                'debugMode'    => isset( $settings['enable_debug_mode'] ) ? (bool) $settings['enable_debug_mode'] : false,
                'pluginUrl'    => HEXTRIS_ARCADE_URL,
            )
        );
    }
}
