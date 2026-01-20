<?php
/**
 * The REST API handler for the Hextris game.
 *
 * @package Hextris_Arcade
 */

class Hextris_REST_API {

    /**
     * The namespace for the REST API.
     *
     * @var string
     */
    private $namespace = 'hextris/v1';

    /**
     * Rate limiting transient key prefix.
     *
     * @var string
     */
    private $rate_limit_prefix = 'hextris_rate_limit_';

    /**
     * Register the REST API routes.
     */
    public function register_routes() {
        // Get leaderboard scores
        register_rest_route(
            $this->namespace,
            '/scores',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_scores' ),
                    'permission_callback' => '__return_true',
                    'args'                => $this->get_scores_args(),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'submit_score' ),
                    'permission_callback' => array( $this, 'check_nonce' ),
                    'args'                => $this->submit_score_args(),
                ),
            )
        );

        // Get user's scores
        register_rest_route(
            $this->namespace,
            '/scores/user',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_user_scores' ),
                'permission_callback' => array( $this, 'check_logged_in' ),
            )
        );

        // Get public game settings
        register_rest_route(
            $this->namespace,
            '/settings',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_settings' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * Get leaderboard scores.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response
     */
    public function get_scores( $request ) {
        global $wpdb;

        $limit      = $request->get_param( 'limit' ) ?: 10;
        $offset     = $request->get_param( 'offset' ) ?: 0;
        $difficulty = $request->get_param( 'difficulty' ) ?: 'all';
        $period     = $request->get_param( 'period' ) ?: 'all';

        $table_name = $wpdb->prefix . 'hextris_scores';

        // Build query conditions
        $where_clauses = array( '1=1' );
        $where_values  = array();

        if ( 'all' !== $difficulty ) {
            $where_clauses[] = 'difficulty = %s';
            $where_values[]  = $difficulty;
        }

        if ( 'all' !== $period ) {
            $date_limit = $this->get_date_limit( $period );
            if ( $date_limit ) {
                $where_clauses[] = 'created_at >= %s';
                $where_values[]  = $date_limit;
            }
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table_name WHERE $where_sql";
        if ( ! empty( $where_values ) ) {
            $count_query = $wpdb->prepare( $count_query, $where_values );
        }
        $total = (int) $wpdb->get_var( $count_query );

        // Get scores
        $query = "SELECT id, player_name, score, difficulty, max_combo, created_at
                  FROM $table_name
                  WHERE $where_sql
                  ORDER BY score DESC
                  LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $limit, $offset ) );
        $results      = $wpdb->get_results( $wpdb->prepare( $query, $query_values ) );

        // Add rank to each result
        $scores = array();
        foreach ( $results as $index => $row ) {
            $scores[] = array(
                'rank'        => $offset + $index + 1,
                'id'          => (int) $row->id,
                'playerName'  => esc_html( $row->player_name ),
                'score'       => (int) $row->score,
                'difficulty'  => $row->difficulty,
                'maxCombo'    => (int) $row->max_combo,
                'date'        => $row->created_at,
            );
        }

        return rest_ensure_response(
            array(
                'scores' => $scores,
                'total'  => $total,
            )
        );
    }

    /**
     * Submit a new score.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function submit_score( $request ) {
        global $wpdb;

        // Check rate limiting
        $ip_hash = $this->get_ip_hash();
        if ( $this->is_rate_limited( $ip_hash ) ) {
            return new WP_Error(
                'rate_limited',
                __( 'Please wait before submitting another score.', 'hextris-arcade' ),
                array( 'status' => 429 )
            );
        }

        $score          = absint( $request->get_param( 'score' ) );
        $player_name    = sanitize_text_field( $request->get_param( 'player_name' ) );
        $difficulty     = sanitize_text_field( $request->get_param( 'difficulty' ) );
        $game_duration  = absint( $request->get_param( 'game_duration' ) );
        $max_combo      = absint( $request->get_param( 'max_combo' ) );
        $blocks_cleared = absint( $request->get_param( 'blocks_cleared' ) );

        // Score sanity check
        if ( $score > 1000000 ) {
            return new WP_Error(
                'invalid_score',
                __( 'Score appears to be invalid.', 'hextris-arcade' ),
                array( 'status' => 400 )
            );
        }

        // Default player name
        if ( empty( $player_name ) ) {
            $player_name = is_user_logged_in()
                ? wp_get_current_user()->display_name
                : __( 'Anonymous', 'hextris-arcade' );
        }

        // Validate difficulty
        $valid_difficulties = array( 'easy', 'normal', 'hard' );
        if ( ! in_array( $difficulty, $valid_difficulties, true ) ) {
            $difficulty = 'normal';
        }

        // Apply filter to score data
        $score_data = apply_filters(
            'hextris_score_data',
            array(
                'user_id'        => get_current_user_id() ?: null,
                'player_name'    => $player_name,
                'score'          => $score,
                'difficulty'     => $difficulty,
                'game_duration'  => $game_duration,
                'max_combo'      => $max_combo,
                'blocks_cleared' => $blocks_cleared,
                'ip_hash'        => $ip_hash,
                'user_agent'     => isset( $_SERVER['HTTP_USER_AGENT'] )
                    ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
                    : '',
            )
        );

        $table_name = $wpdb->prefix . 'hextris_scores';

        $result = $wpdb->insert(
            $table_name,
            $score_data,
            array( '%d', '%s', '%d', '%s', '%d', '%d', '%d', '%s', '%s' )
        );

        if ( false === $result ) {
            return new WP_Error(
                'db_error',
                __( 'Failed to save score.', 'hextris-arcade' ),
                array( 'status' => 500 )
            );
        }

        $score_id = $wpdb->insert_id;

        // Set rate limit
        $this->set_rate_limit( $ip_hash );

        // Get rank
        $rank = $this->get_score_rank( $score, $difficulty );

        // Fire action hook
        do_action( 'hextris_after_score_submit', $score_id, $score_data );

        // Clear leaderboard cache
        delete_transient( 'hextris_leaderboard_cache' );

        return rest_ensure_response(
            array(
                'success'  => true,
                'score_id' => $score_id,
                'rank'     => $rank,
            )
        );
    }

    /**
     * Get the current user's scores.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response
     */
    public function get_user_scores( $request ) {
        global $wpdb;

        $user_id    = get_current_user_id();
        $table_name = $wpdb->prefix . 'hextris_scores';

        // Get user's scores
        $scores = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, score, difficulty, max_combo, created_at
                 FROM $table_name
                 WHERE user_id = %d
                 ORDER BY score DESC
                 LIMIT 50",
                $user_id
            )
        );

        // Get best score
        $best_score = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(score) FROM $table_name WHERE user_id = %d",
                $user_id
            )
        );

        // Get total games
        $total_games = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE user_id = %d",
                $user_id
            )
        );

        $formatted_scores = array();
        foreach ( $scores as $row ) {
            $formatted_scores[] = array(
                'id'         => (int) $row->id,
                'score'      => (int) $row->score,
                'difficulty' => $row->difficulty,
                'maxCombo'   => (int) $row->max_combo,
                'date'       => $row->created_at,
            );
        }

        return rest_ensure_response(
            array(
                'scores'     => $formatted_scores,
                'bestScore'  => (int) $best_score,
                'totalGames' => (int) $total_games,
            )
        );
    }

    /**
     * Get public game settings.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response
     */
    public function get_settings( $request ) {
        $settings = get_option( 'hextris_settings', array() );

        return rest_ensure_response(
            array(
                'difficulty'      => isset( $settings['default_difficulty'] ) ? $settings['default_difficulty'] : 'normal',
                'showLeaderboard' => isset( $settings['enable_global_leaderboard'] ) ? (bool) $settings['enable_global_leaderboard'] : true,
                'colorScheme'     => isset( $settings['color_scheme'] ) ? $settings['color_scheme'] : 'default',
            )
        );
    }

    /**
     * Get the arguments for the get_scores endpoint.
     *
     * @return array
     */
    private function get_scores_args() {
        return array(
            'limit'      => array(
                'type'              => 'integer',
                'default'           => 10,
                'maximum'           => 100,
                'sanitize_callback' => 'absint',
            ),
            'offset'     => array(
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
            ),
            'difficulty' => array(
                'type'              => 'string',
                'default'           => 'all',
                'enum'              => array( 'easy', 'normal', 'hard', 'all' ),
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'period'     => array(
                'type'              => 'string',
                'default'           => 'all',
                'enum'              => array( 'all', 'day', 'week', 'month' ),
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }

    /**
     * Get the arguments for the submit_score endpoint.
     *
     * @return array
     */
    private function submit_score_args() {
        return array(
            'score'          => array(
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ),
            'player_name'    => array(
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'difficulty'     => array(
                'type'              => 'string',
                'default'           => 'normal',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'game_duration'  => array(
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
            ),
            'max_combo'      => array(
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
            ),
            'blocks_cleared' => array(
                'type'              => 'integer',
                'default'           => 0,
                'sanitize_callback' => 'absint',
            ),
        );
    }

    /**
     * Check if the request has a valid nonce.
     *
     * @param WP_REST_Request $request The request object.
     * @return bool
     */
    public function check_nonce( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! $nonce ) {
            $nonce = $request->get_param( 'nonce' );
        }

        return wp_verify_nonce( $nonce, 'hextris_submit_score' ) || wp_verify_nonce( $nonce, 'wp_rest' );
    }

    /**
     * Check if the user is logged in.
     *
     * @return bool
     */
    public function check_logged_in() {
        return is_user_logged_in();
    }

    /**
     * Get a hashed IP address for privacy.
     *
     * @return string
     */
    private function get_ip_hash() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        return hash( 'sha256', $ip . wp_salt() );
    }

    /**
     * Check if an IP is rate limited.
     *
     * @param string $ip_hash The hashed IP address.
     * @return bool
     */
    private function is_rate_limited( $ip_hash ) {
        return false !== get_transient( $this->rate_limit_prefix . $ip_hash );
    }

    /**
     * Set rate limit for an IP.
     *
     * @param string $ip_hash The hashed IP address.
     */
    private function set_rate_limit( $ip_hash ) {
        set_transient( $this->rate_limit_prefix . $ip_hash, true, 10 );
    }

    /**
     * Get the date limit for a time period.
     *
     * @param string $period The time period.
     * @return string|null
     */
    private function get_date_limit( $period ) {
        switch ( $period ) {
            case 'day':
                return gmdate( 'Y-m-d H:i:s', strtotime( '-1 day' ) );
            case 'week':
                return gmdate( 'Y-m-d H:i:s', strtotime( '-1 week' ) );
            case 'month':
                return gmdate( 'Y-m-d H:i:s', strtotime( '-1 month' ) );
            default:
                return null;
        }
    }

    /**
     * Get the rank of a score.
     *
     * @param int    $score      The score.
     * @param string $difficulty The difficulty.
     * @return int
     */
    private function get_score_rank( $score, $difficulty ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hextris_scores';

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) + 1 FROM $table_name WHERE score > %d AND difficulty = %s",
                $score,
                $difficulty
            )
        );
    }
}
