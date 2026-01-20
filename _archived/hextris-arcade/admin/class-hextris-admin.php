<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package Hextris_Arcade
 */

class Hextris_Admin {

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
     * Register the stylesheets for the admin area.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_styles( $hook ) {
        if ( 'settings_page_hextris-arcade' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            HEXTRIS_ARCADE_URL . 'admin/css/hextris-admin.css',
            array( 'wp-color-picker' ),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'settings_page_hextris-arcade' !== $hook ) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            HEXTRIS_ARCADE_URL . 'admin/js/hextris-admin.js',
            array( 'jquery', 'wp-color-picker' ),
            $this->version,
            true
        );
    }

    /**
     * Add the admin menu page.
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'Hextris Arcade Settings', 'hextris-arcade' ),
            __( 'Hextris Arcade', 'hextris-arcade' ),
            'manage_options',
            'hextris-arcade',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Register the plugin settings.
     */
    public function register_settings() {
        register_setting(
            'hextris_settings',
            'hextris_settings',
            array( $this, 'sanitize_settings' )
        );

        // General Settings Section
        add_settings_section(
            'hextris_general_settings',
            __( 'General Settings', 'hextris-arcade' ),
            array( $this, 'general_settings_callback' ),
            'hextris-arcade'
        );

        $this->add_general_fields();

        // Gameplay Settings Section
        add_settings_section(
            'hextris_gameplay_settings',
            __( 'Gameplay Settings', 'hextris-arcade' ),
            array( $this, 'gameplay_settings_callback' ),
            'hextris-arcade'
        );

        $this->add_gameplay_fields();

        // Appearance Settings Section
        add_settings_section(
            'hextris_appearance_settings',
            __( 'Appearance', 'hextris-arcade' ),
            array( $this, 'appearance_settings_callback' ),
            'hextris-arcade'
        );

        $this->add_appearance_fields();

        // Leaderboard Settings Section
        add_settings_section(
            'hextris_leaderboard_settings',
            __( 'Leaderboard Settings', 'hextris-arcade' ),
            array( $this, 'leaderboard_settings_callback' ),
            'hextris-arcade'
        );

        $this->add_leaderboard_fields();

        // Advanced Settings Section
        add_settings_section(
            'hextris_advanced_settings',
            __( 'Advanced Settings', 'hextris-arcade' ),
            array( $this, 'advanced_settings_callback' ),
            'hextris-arcade'
        );

        $this->add_advanced_fields();
    }

    /**
     * Add general settings fields.
     */
    private function add_general_fields() {
        $fields = array(
            'default_width'     => array(
                'label'       => __( 'Default Width', 'hextris-arcade' ),
                'type'        => 'text',
                'default'     => '100%',
                'description' => __( 'e.g., 100%, 600px, 50vw', 'hextris-arcade' ),
            ),
            'default_height'    => array(
                'label'       => __( 'Default Height', 'hextris-arcade' ),
                'type'        => 'text',
                'default'     => '600px',
                'description' => __( 'e.g., 600px, 80vh', 'hextris-arcade' ),
            ),
            'default_max_width' => array(
                'label'       => __( 'Default Max Width', 'hextris-arcade' ),
                'type'        => 'text',
                'default'     => '800px',
                'description' => __( 'Maximum container width', 'hextris-arcade' ),
            ),
        );

        foreach ( $fields as $id => $field ) {
            add_settings_field(
                $id,
                $field['label'],
                array( $this, 'render_field' ),
                'hextris-arcade',
                'hextris_general_settings',
                array_merge( $field, array( 'id' => $id ) )
            );
        }
    }

    /**
     * Add gameplay settings fields.
     */
    private function add_gameplay_fields() {
        $fields = array(
            'default_difficulty' => array(
                'label'   => __( 'Default Difficulty', 'hextris-arcade' ),
                'type'    => 'select',
                'options' => array(
                    'easy'   => __( 'Easy', 'hextris-arcade' ),
                    'normal' => __( 'Normal', 'hextris-arcade' ),
                    'hard'   => __( 'Hard', 'hextris-arcade' ),
                ),
                'default' => 'normal',
            ),
            'speed_modifier'     => array(
                'label'       => __( 'Speed Modifier', 'hextris-arcade' ),
                'type'        => 'range',
                'min'         => 0.3,
                'max'         => 1.5,
                'step'        => 0.05,
                'default'     => 0.65,
                'description' => __( 'Lower = faster blocks', 'hextris-arcade' ),
            ),
            'combo_time_ms'      => array(
                'label'       => __( 'Combo Window (ms)', 'hextris-arcade' ),
                'type'        => 'number',
                'min'         => 100,
                'max'         => 1000,
                'default'     => 310,
                'description' => __( 'Time to chain combos', 'hextris-arcade' ),
            ),
            'max_rows'           => array(
                'label'       => __( 'Max Block Rows', 'hextris-arcade' ),
                'type'        => 'number',
                'min'         => 5,
                'max'         => 12,
                'default'     => 8,
                'description' => __( 'Rows before game over', 'hextris-arcade' ),
            ),
        );

        foreach ( $fields as $id => $field ) {
            add_settings_field(
                $id,
                $field['label'],
                array( $this, 'render_field' ),
                'hextris-arcade',
                'hextris_gameplay_settings',
                array_merge( $field, array( 'id' => $id ) )
            );
        }
    }

    /**
     * Add appearance settings fields.
     */
    private function add_appearance_fields() {
        $fields = array(
            'color_scheme'        => array(
                'label'   => __( 'Color Scheme', 'hextris-arcade' ),
                'type'    => 'select',
                'options' => array(
                    'default'    => __( 'Default', 'hextris-arcade' ),
                    'dark'       => __( 'Dark', 'hextris-arcade' ),
                    'colorblind' => __( 'Colorblind Friendly', 'hextris-arcade' ),
                    'custom'     => __( 'Custom', 'hextris-arcade' ),
                ),
                'default' => 'default',
            ),
            'color_1'             => array(
                'label'   => __( 'Color 1 (Red)', 'hextris-arcade' ),
                'type'    => 'color',
                'default' => '#e74c3c',
                'class'   => 'custom-color-field',
            ),
            'color_2'             => array(
                'label'   => __( 'Color 2 (Yellow)', 'hextris-arcade' ),
                'type'    => 'color',
                'default' => '#f1c40f',
                'class'   => 'custom-color-field',
            ),
            'color_3'             => array(
                'label'   => __( 'Color 3 (Blue)', 'hextris-arcade' ),
                'type'    => 'color',
                'default' => '#3498db',
                'class'   => 'custom-color-field',
            ),
            'color_4'             => array(
                'label'   => __( 'Color 4 (Green)', 'hextris-arcade' ),
                'type'    => 'color',
                'default' => '#2ecc71',
                'class'   => 'custom-color-field',
            ),
            'background_color'    => array(
                'label'   => __( 'Background Color', 'hextris-arcade' ),
                'type'    => 'color',
                'default' => '#ecf0f1',
                'class'   => 'custom-color-field',
            ),
            'hexagon_fill_color'  => array(
                'label'   => __( 'Hexagon Fill Color', 'hextris-arcade' ),
                'type'    => 'color',
                'default' => '#2c3e50',
                'class'   => 'custom-color-field',
            ),
            'show_combo_timer'    => array(
                'label'   => __( 'Show Combo Timer', 'hextris-arcade' ),
                'type'    => 'checkbox',
                'default' => true,
            ),
        );

        foreach ( $fields as $id => $field ) {
            add_settings_field(
                $id,
                $field['label'],
                array( $this, 'render_field' ),
                'hextris-arcade',
                'hextris_appearance_settings',
                array_merge( $field, array( 'id' => $id ) )
            );
        }
    }

    /**
     * Add leaderboard settings fields.
     */
    private function add_leaderboard_fields() {
        $fields = array(
            'enable_global_leaderboard' => array(
                'label'   => __( 'Enable Global Leaderboard', 'hextris-arcade' ),
                'type'    => 'checkbox',
                'default' => true,
            ),
            'scores_to_display'         => array(
                'label'   => __( 'Scores to Display', 'hextris-arcade' ),
                'type'    => 'number',
                'min'     => 3,
                'max'     => 20,
                'default' => 10,
            ),
            'track_user_scores'         => array(
                'label'   => __( 'Track Logged-in User Scores', 'hextris-arcade' ),
                'type'    => 'checkbox',
                'default' => true,
            ),
            'allow_anonymous_scores'    => array(
                'label'   => __( 'Allow Anonymous Scores', 'hextris-arcade' ),
                'type'    => 'checkbox',
                'default' => true,
            ),
        );

        foreach ( $fields as $id => $field ) {
            add_settings_field(
                $id,
                $field['label'],
                array( $this, 'render_field' ),
                'hextris-arcade',
                'hextris_leaderboard_settings',
                array_merge( $field, array( 'id' => $id ) )
            );
        }
    }

    /**
     * Add advanced settings fields.
     */
    private function add_advanced_fields() {
        $fields = array(
            'load_scripts_globally' => array(
                'label'       => __( 'Load Scripts on All Pages', 'hextris-arcade' ),
                'type'        => 'checkbox',
                'default'     => false,
                'description' => __( 'By default, scripts only load on pages with the shortcode', 'hextris-arcade' ),
            ),
            'defer_scripts'         => array(
                'label'   => __( 'Defer Script Loading', 'hextris-arcade' ),
                'type'    => 'checkbox',
                'default' => true,
            ),
            'enable_debug_mode'     => array(
                'label'       => __( 'Enable Debug Mode', 'hextris-arcade' ),
                'type'        => 'checkbox',
                'default'     => false,
                'description' => __( 'Outputs debug information to browser console', 'hextris-arcade' ),
            ),
        );

        foreach ( $fields as $id => $field ) {
            add_settings_field(
                $id,
                $field['label'],
                array( $this, 'render_field' ),
                'hextris-arcade',
                'hextris_advanced_settings',
                array_merge( $field, array( 'id' => $id ) )
            );
        }
    }

    /**
     * Render a settings field.
     *
     * @param array $args Field arguments.
     */
    public function render_field( $args ) {
        $settings = get_option( 'hextris_settings', array() );
        $id       = $args['id'];
        $type     = $args['type'];
        $value    = isset( $settings[ $id ] ) ? $settings[ $id ] : ( isset( $args['default'] ) ? $args['default'] : '' );
        $class    = isset( $args['class'] ) ? esc_attr( $args['class'] ) : '';

        switch ( $type ) {
            case 'text':
            case 'number':
                $min  = isset( $args['min'] ) ? ' min="' . esc_attr( $args['min'] ) . '"' : '';
                $max  = isset( $args['max'] ) ? ' max="' . esc_attr( $args['max'] ) . '"' : '';
                $step = isset( $args['step'] ) ? ' step="' . esc_attr( $args['step'] ) . '"' : '';
                printf(
                    '<input type="%s" id="%s" name="hextris_settings[%s]" value="%s" class="regular-text %s"%s%s%s />',
                    esc_attr( $type ),
                    esc_attr( $id ),
                    esc_attr( $id ),
                    esc_attr( $value ),
                    $class,
                    $min,
                    $max,
                    $step
                );
                break;

            case 'range':
                $min  = isset( $args['min'] ) ? $args['min'] : 0;
                $max  = isset( $args['max'] ) ? $args['max'] : 100;
                $step = isset( $args['step'] ) ? $args['step'] : 1;
                printf(
                    '<input type="range" id="%s" name="hextris_settings[%s]" value="%s" min="%s" max="%s" step="%s" class="%s" />
                     <span class="range-value">%s</span>',
                    esc_attr( $id ),
                    esc_attr( $id ),
                    esc_attr( $value ),
                    esc_attr( $min ),
                    esc_attr( $max ),
                    esc_attr( $step ),
                    $class,
                    esc_html( $value )
                );
                break;

            case 'select':
                printf( '<select id="%s" name="hextris_settings[%s]" class="%s">', esc_attr( $id ), esc_attr( $id ), $class );
                foreach ( $args['options'] as $key => $label ) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr( $key ),
                        selected( $value, $key, false ),
                        esc_html( $label )
                    );
                }
                echo '</select>';
                break;

            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%s" name="hextris_settings[%s]" value="1" %s class="%s" />',
                    esc_attr( $id ),
                    esc_attr( $id ),
                    checked( $value, true, false ),
                    $class
                );
                break;

            case 'color':
                printf(
                    '<input type="text" id="%s" name="hextris_settings[%s]" value="%s" class="hextris-color-picker %s" />',
                    esc_attr( $id ),
                    esc_attr( $id ),
                    esc_attr( $value ),
                    $class
                );
                break;
        }

        if ( isset( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    /**
     * General settings section callback.
     */
    public function general_settings_callback() {
        echo '<p>' . esc_html__( 'Configure the default dimensions for the game container.', 'hextris-arcade' ) . '</p>';
    }

    /**
     * Gameplay settings section callback.
     */
    public function gameplay_settings_callback() {
        echo '<p>' . esc_html__( 'Adjust the game difficulty and mechanics.', 'hextris-arcade' ) . '</p>';
    }

    /**
     * Appearance settings section callback.
     */
    public function appearance_settings_callback() {
        echo '<p>' . esc_html__( 'Customize the visual appearance of the game.', 'hextris-arcade' ) . '</p>';
    }

    /**
     * Leaderboard settings section callback.
     */
    public function leaderboard_settings_callback() {
        echo '<p>' . esc_html__( 'Configure the leaderboard and score tracking.', 'hextris-arcade' ) . '</p>';
    }

    /**
     * Advanced settings section callback.
     */
    public function advanced_settings_callback() {
        echo '<p>' . esc_html__( 'Advanced options for developers and performance tuning.', 'hextris-arcade' ) . '</p>';
    }

    /**
     * Sanitize the settings.
     *
     * @param array $input The input array.
     * @return array The sanitized array.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        // Text fields
        $text_fields = array( 'default_width', 'default_height', 'default_max_width' );
        foreach ( $text_fields as $field ) {
            if ( isset( $input[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_text_field( $input[ $field ] );
            }
        }

        // Select fields
        $select_fields = array(
            'default_difficulty' => array( 'easy', 'normal', 'hard' ),
            'color_scheme'       => array( 'default', 'dark', 'colorblind', 'custom' ),
        );
        foreach ( $select_fields as $field => $options ) {
            if ( isset( $input[ $field ] ) && in_array( $input[ $field ], $options, true ) ) {
                $sanitized[ $field ] = $input[ $field ];
            }
        }

        // Float fields
        if ( isset( $input['speed_modifier'] ) ) {
            $sanitized['speed_modifier'] = max( 0.3, min( 1.5, floatval( $input['speed_modifier'] ) ) );
        }

        // Integer fields
        $int_fields = array(
            'combo_time_ms'     => array( 'min' => 100, 'max' => 1000 ),
            'max_rows'          => array( 'min' => 5, 'max' => 12 ),
            'scores_to_display' => array( 'min' => 3, 'max' => 20 ),
        );
        foreach ( $int_fields as $field => $range ) {
            if ( isset( $input[ $field ] ) ) {
                $sanitized[ $field ] = max( $range['min'], min( $range['max'], absint( $input[ $field ] ) ) );
            }
        }

        // Color fields
        $color_fields = array( 'color_1', 'color_2', 'color_3', 'color_4', 'background_color', 'hexagon_fill_color' );
        foreach ( $color_fields as $field ) {
            if ( isset( $input[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_hex_color( $input[ $field ] );
            }
        }

        // Checkbox fields
        $checkbox_fields = array(
            'show_combo_timer',
            'enable_global_leaderboard',
            'track_user_scores',
            'allow_anonymous_scores',
            'load_scripts_globally',
            'defer_scripts',
            'enable_debug_mode',
        );
        foreach ( $checkbox_fields as $field ) {
            $sanitized[ $field ] = isset( $input[ $field ] ) ? true : false;
        }

        // Fire action hook
        do_action( 'hextris_settings_saved', $sanitized );

        return $sanitized;
    }

    /**
     * Display the settings page.
     */
    public function display_settings_page() {
        include HEXTRIS_ARCADE_PATH . 'admin/partials/hextris-admin-display.php';
    }
}
