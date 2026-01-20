<?php
/**
 * Hextris Arcade loader.
 *
 * This file is kept as an internal loader for bundled assets and should not be
 * treated as a standalone WordPress plugin entry file.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'HEXTRIS_ARCADE_VERSION', '1.0.0' );

/**
 * Plugin base path.
 */
define( 'HEXTRIS_ARCADE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin base URL.
 */
define( 'HEXTRIS_ARCADE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'HEXTRIS_ARCADE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_hextris_arcade() {
    require_once HEXTRIS_ARCADE_PATH . 'includes/class-hextris-activator.php';
    Hextris_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_hextris_arcade() {
    require_once HEXTRIS_ARCADE_PATH . 'includes/class-hextris-deactivator.php';
    Hextris_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_hextris_arcade' );
register_deactivation_hook( __FILE__, 'deactivate_hextris_arcade' );

/**
 * The core plugin class.
 */
require HEXTRIS_ARCADE_PATH . 'includes/class-hextris-loader.php';

/**
 * Begins execution of the plugin.
 */
function run_hextris_arcade() {
    $plugin = new Hextris_Loader();
    $plugin->run();
}

run_hextris_arcade();
