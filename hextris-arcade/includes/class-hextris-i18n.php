<?php
/**
 * Define the internationalization functionality.
 *
 * @package Hextris_Arcade
 */

class Hextris_i18n {

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'hextris-arcade',
            false,
            dirname( HEXTRIS_ARCADE_BASENAME ) . '/languages/'
        );
    }
}
