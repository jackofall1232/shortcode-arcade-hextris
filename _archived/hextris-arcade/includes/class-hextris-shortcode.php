<?php
/**
 * The shortcode handler for the Hextris game.
 *
 * @package Hextris_Arcade
 */

class Hextris_Shortcode {

    /**
     * Register the shortcode.
     */
    public function register_shortcode() {
        add_shortcode( 'hextris', array( $this, 'render_shortcode' ) );
    }

    /**
     * Render the shortcode output.
     *
     * @param array $atts Shortcode attributes.
     * @return string The shortcode HTML output.
     */
    public function render_shortcode( $atts ) {
        $settings = get_option( 'hextris_settings', array() );

        // Parse shortcode attributes with defaults
        $atts = shortcode_atts(
            array(
                'width'           => isset( $settings['default_width'] ) ? $settings['default_width'] : '100%',
                'height'          => isset( $settings['default_height'] ) ? $settings['default_height'] : '600px',
                'max_width'       => isset( $settings['default_max_width'] ) ? $settings['default_max_width'] : '800px',
                'theme'           => isset( $settings['color_scheme'] ) ? $settings['color_scheme'] : 'default',
                'difficulty'      => isset( $settings['default_difficulty'] ) ? $settings['default_difficulty'] : 'normal',
                'show_leaderboard' => isset( $settings['enable_global_leaderboard'] ) ? $settings['enable_global_leaderboard'] : true,
                'user_scores'     => isset( $settings['track_user_scores'] ) ? $settings['track_user_scores'] : false,
            ),
            $atts,
            'hextris'
        );

        // Sanitize attributes
        $width           = esc_attr( $atts['width'] );
        $height          = esc_attr( $atts['height'] );
        $max_width       = esc_attr( $atts['max_width'] );
        $theme           = sanitize_text_field( $atts['theme'] );
        $difficulty      = sanitize_text_field( $atts['difficulty'] );
        $show_leaderboard = filter_var( $atts['show_leaderboard'], FILTER_VALIDATE_BOOLEAN );
        $user_scores     = filter_var( $atts['user_scores'], FILTER_VALIDATE_BOOLEAN );

        // Generate unique ID for this instance
        $unique_id = 'hextris-' . wp_rand( 1000, 9999 ) . '-' . time();

        // Get theme colors
        $colors = $this->get_theme_colors( $theme, $settings );

        // Get difficulty settings
        $difficulty_settings = $this->get_difficulty_settings( $difficulty );

        // Build the game settings object
        $game_settings = array(
            'instanceId'       => $unique_id,
            'theme'            => $theme,
            'difficulty'       => $difficulty,
            'showLeaderboard'  => $show_leaderboard,
            'userScores'       => $user_scores,
            'colors'           => $colors,
            'difficultySettings' => $difficulty_settings,
            'speedModifier'    => isset( $settings['speed_modifier'] ) ? floatval( $settings['speed_modifier'] ) : 0.65,
            'comboTime'        => isset( $settings['combo_time_ms'] ) ? intval( $settings['combo_time_ms'] ) : 310,
            'maxRows'          => isset( $settings['max_rows'] ) ? intval( $settings['max_rows'] ) : 8,
            'showComboTimer'   => isset( $settings['show_combo_timer'] ) ? (bool) $settings['show_combo_timer'] : true,
            'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
            'restUrl'          => esc_url_raw( rest_url( 'hextris/v1' ) ),
            'nonce'            => wp_create_nonce( 'hextris_submit_score' ),
            'userId'           => get_current_user_id(),
            'isLoggedIn'       => is_user_logged_in(),
            'i18n'             => $this->get_i18n_strings(),
        );

        // Apply filter for custom settings
        $game_settings = apply_filters( 'hextris_default_settings', $game_settings );

        // Load the game container template
        ob_start();
        include HEXTRIS_ARCADE_PATH . 'public/partials/hextris-game-container.php';
        $output = ob_get_clean();

        // Apply filter for output
        return apply_filters( 'hextris_shortcode_output', $output, $atts );
    }

    /**
     * Get theme colors.
     *
     * @param string $theme    The theme name.
     * @param array  $settings Plugin settings.
     * @return array Color configuration.
     */
    private function get_theme_colors( $theme, $settings ) {
        $themes = array(
            'default' => array(
                'colors'     => array( '#e74c3c', '#f1c40f', '#3498db', '#2ecc71' ),
                'background' => '#ecf0f1',
                'hexFill'    => '#2c3e50',
                'hexStroke'  => '#2c3e50',
                'textColor'  => '#2c3e50',
            ),
            'dark' => array(
                'colors'     => array( '#e74c3c', '#f1c40f', '#3498db', '#2ecc71' ),
                'background' => '#1a1a2e',
                'hexFill'    => '#16213e',
                'hexStroke'  => '#0f3460',
                'textColor'  => '#eaeaea',
            ),
            'colorblind' => array(
                'colors'     => array( '#d35400', '#8e44ad', '#2980b9', '#27ae60' ),
                'background' => '#ecf0f1',
                'hexFill'    => '#2c3e50',
                'hexStroke'  => '#2c3e50',
                'textColor'  => '#2c3e50',
            ),
            'custom' => array(
                'colors'     => array(
                    isset( $settings['color_1'] ) ? $settings['color_1'] : '#e74c3c',
                    isset( $settings['color_2'] ) ? $settings['color_2'] : '#f1c40f',
                    isset( $settings['color_3'] ) ? $settings['color_3'] : '#3498db',
                    isset( $settings['color_4'] ) ? $settings['color_4'] : '#2ecc71',
                ),
                'background' => isset( $settings['background_color'] ) ? $settings['background_color'] : '#ecf0f1',
                'hexFill'    => isset( $settings['hexagon_fill_color'] ) ? $settings['hexagon_fill_color'] : '#2c3e50',
                'hexStroke'  => isset( $settings['hexagon_fill_color'] ) ? $settings['hexagon_fill_color'] : '#2c3e50',
                'textColor'  => '#2c3e50',
            ),
        );

        $selected_theme = isset( $themes[ $theme ] ) ? $themes[ $theme ] : $themes['default'];

        return apply_filters( 'hextris_colors', $selected_theme );
    }

    /**
     * Get difficulty settings.
     *
     * @param string $difficulty The difficulty level.
     * @return array Difficulty configuration.
     */
    private function get_difficulty_settings( $difficulty ) {
        $difficulties = array(
            'easy' => array(
                'speedModifier'         => 0.85,
                'creationSpeedModifier' => 0.85,
                'maxDifficulty'         => 20,
                'comboTime'             => 400,
                'rows'                  => 9,
            ),
            'normal' => array(
                'speedModifier'         => 0.65,
                'creationSpeedModifier' => 0.65,
                'maxDifficulty'         => 35,
                'comboTime'             => 310,
                'rows'                  => 8,
            ),
            'hard' => array(
                'speedModifier'         => 0.5,
                'creationSpeedModifier' => 0.5,
                'maxDifficulty'         => 50,
                'comboTime'             => 250,
                'rows'                  => 7,
            ),
        );

        $selected = isset( $difficulties[ $difficulty ] ) ? $difficulties[ $difficulty ] : $difficulties['normal'];

        return apply_filters( 'hextris_difficulty_settings', $selected, $difficulty );
    }

    /**
     * Get internationalization strings.
     *
     * @return array Translatable strings.
     */
    private function get_i18n_strings() {
        return array(
            'play'           => __( 'Play', 'hextris-arcade' ),
            'pause'          => __( 'Pause', 'hextris-arcade' ),
            'resume'         => __( 'Resume', 'hextris-arcade' ),
            'restart'        => __( 'Restart', 'hextris-arcade' ),
            'gameOver'       => __( 'Game Over', 'hextris-arcade' ),
            'score'          => __( 'Score', 'hextris-arcade' ),
            'highScore'      => __( 'High Score', 'hextris-arcade' ),
            'newHighScore'   => __( 'New High Score!', 'hextris-arcade' ),
            'leaderboard'    => __( 'Leaderboard', 'hextris-arcade' ),
            'yourBest'       => __( 'Your Best', 'hextris-arcade' ),
            'rank'           => __( 'Rank', 'hextris-arcade' ),
            'player'         => __( 'Player', 'hextris-arcade' ),
            'combo'          => __( 'Combo', 'hextris-arcade' ),
            'pressAnyKey'    => __( 'Press any key to start', 'hextris-arcade' ),
            'tapToStart'     => __( 'Tap to start', 'hextris-arcade' ),
            'loading'        => __( 'Loading...', 'hextris-arcade' ),
            'errorSavingScore' => __( 'Error saving score.', 'hextris-arcade' ),
            'easy'           => __( 'Easy', 'hextris-arcade' ),
            'normal'         => __( 'Normal', 'hextris-arcade' ),
            'hard'           => __( 'Hard', 'hextris-arcade' ),
            'howToPlay'      => __( 'HOW TO PLAY', 'hextris-arcade' ),
            'rotateInstructions' => __( 'Use arrow keys or tap to rotate the hexagon', 'hextris-arcade' ),
            'matchInstructions' => __( 'Match 3+ blocks of the same color to score', 'hextris-arcade' ),
            'gamePaused'     => __( 'Game Paused', 'hextris-arcade' ),
            'highScores'     => __( 'HIGH SCORES', 'hextris-arcade' ),
        );
    }
}
