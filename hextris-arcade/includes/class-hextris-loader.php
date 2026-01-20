<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @package Hextris_Arcade
 */

class Hextris_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @var array
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @var array
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcode();
        $this->define_rest_api();
        $this->define_block();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once HEXTRIS_ARCADE_PATH . 'includes/class-hextris-i18n.php';
        require_once HEXTRIS_ARCADE_PATH . 'includes/class-hextris-shortcode.php';
        require_once HEXTRIS_ARCADE_PATH . 'includes/class-hextris-rest-api.php';
        require_once HEXTRIS_ARCADE_PATH . 'admin/class-hextris-admin.php';
        require_once HEXTRIS_ARCADE_PATH . 'public/class-hextris-public.php';
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new Hextris_i18n();
        $this->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Hextris_Admin();

        $this->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
        $this->add_action( 'admin_init', $plugin_admin, 'register_settings' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new Hextris_Public();

        $this->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_styles' );
        $this->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_scripts' );
    }

    /**
     * Register the shortcode.
     */
    private function define_shortcode() {
        $shortcode = new Hextris_Shortcode();
        $this->add_action( 'init', $shortcode, 'register_shortcode' );
    }

    /**
     * Register the REST API endpoints.
     */
    private function define_rest_api() {
        $rest_api = new Hextris_REST_API();
        $this->add_action( 'rest_api_init', $rest_api, 'register_routes' );
    }

    /**
     * Register the Gutenberg block.
     */
    private function define_block() {
        $this->add_action( 'init', $this, 'register_block' );
    }

    /**
     * Register the Gutenberg block.
     */
    public function register_block() {
        if ( function_exists( 'register_block_type' ) ) {
            register_block_type( HEXTRIS_ARCADE_PATH . 'blocks/hextris-block' );
        }
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress action.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function definition.
     * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the callback. Default is 1.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress filter.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function definition.
     * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the callback. Default is 1.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * A utility function that is used to register the actions and hooks into a single collection.
     *
     * @param array  $hooks         The collection of hooks that is being registered.
     * @param string $hook          The name of the WordPress filter.
     * @param object $component     A reference to the instance of the object.
     * @param string $callback      The name of the function definition.
     * @param int    $priority      The priority at which the function should be fired.
     * @param int    $accepted_args The number of arguments that should be passed to the callback.
     * @return array The collection of actions and filters registered with WordPress.
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }
    }
}
