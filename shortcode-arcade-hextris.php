<?php
/**
 * Plugin Name: Shortcode Arcade Hextris
 * Plugin URI: https://example.com/shortcode-arcade-hextris
 * Description: Registers Hextris assets for future shortcode rendering.
 * Version: 0.1.1
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Register Hextris assets without enqueuing them.
 */
function shortcode_arcade_hextris_register_assets() {
	$base_url = plugin_dir_url( __FILE__ );

	wp_register_style(
		'shortcode-arcade-hextris',
		$base_url . 'style/style.css',
		array(),
		'0.1.1'
	);

	wp_register_script(
		'shortcode-arcade-hextris',
		$base_url . 'js/main.js',
		array(),
		'0.1.1',
		true
	);
}
add_action( 'init', 'shortcode_arcade_hextris_register_assets' );
