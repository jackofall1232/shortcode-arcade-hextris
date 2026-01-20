<?php
/**
 * Game container template.
 *
 * @package Hextris_Arcade
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Variables available: $unique_id, $width, $height, $max_width, $theme, $game_settings
?>

<div class="hextris-wrapper" style="width: <?php echo esc_attr( $width ); ?>; max-width: <?php echo esc_attr( $max_width ); ?>; margin: 0 auto;">
    <div class="hextris-container hextris-theme-<?php echo esc_attr( $theme ); ?>"
         id="<?php echo esc_attr( $unique_id ); ?>"
         data-settings="<?php echo esc_attr( wp_json_encode( $game_settings ) ); ?>"
         style="height: <?php echo esc_attr( $height ); ?>;">

        <!-- Canvas -->
        <canvas id="<?php echo esc_attr( $unique_id ); ?>-canvas" class="hextris-canvas"></canvas>

        <!-- Overlay for paused/game over states -->
        <div id="<?php echo esc_attr( $unique_id ); ?>-overlay" class="hextris-overlay"></div>

        <!-- Start button area -->
        <div id="<?php echo esc_attr( $unique_id ); ?>-startBtn" class="hextris-start-btn"></div>

        <!-- Help screen -->
        <div id="<?php echo esc_attr( $unique_id ); ?>-helpScreen" class="hextris-help-screen">
            <div class="hextris-help-body">
                <div class="hextris-help-header"><?php esc_html_e( 'HOW TO PLAY', 'hextris-arcade' ); ?></div>
                <p><?php esc_html_e( 'The goal of Hextris is to stop blocks from leaving the inside of the outer gray hexagon.', 'hextris-arcade' ); ?></p>
                <p class="hextris-desktop-only"><?php esc_html_e( 'Press the right and left arrow keys to rotate the Hexagon. Press down to speed up the falling block.', 'hextris-arcade' ); ?></p>
                <p class="hextris-mobile-only"><?php esc_html_e( 'Tap the left and right sides of the screen to rotate the Hexagon.', 'hextris-arcade' ); ?></p>
                <p><?php esc_html_e( 'Clear blocks and get points by making 3 or more blocks of the same color touch.', 'hextris-arcade' ); ?></p>
                <p><?php esc_html_e( 'Time left before your combo streak disappears is indicated by the colored lines on the outer hexagon.', 'hextris-arcade' ); ?></p>
            </div>
        </div>

        <!-- Control buttons -->
        <img id="<?php echo esc_attr( $unique_id ); ?>-openSideBar"
             class="hextris-help-btn hextris-btn"
             src="<?php echo esc_url( HEXTRIS_ARCADE_URL . 'public/images/btn_help.svg' ); ?>"
             alt="<?php esc_attr_e( 'Help', 'hextris-arcade' ); ?>" />

        <img id="<?php echo esc_attr( $unique_id ); ?>-pauseBtn"
             class="hextris-pause-btn hextris-btn"
             src="<?php echo esc_url( HEXTRIS_ARCADE_URL . 'public/images/btn_pause.svg' ); ?>"
             alt="<?php esc_attr_e( 'Pause', 'hextris-arcade' ); ?>" />

        <img id="<?php echo esc_attr( $unique_id ); ?>-restartBtn"
             class="hextris-restart-btn hextris-btn"
             src="<?php echo esc_url( HEXTRIS_ARCADE_URL . 'public/images/btn_restart.svg' ); ?>"
             alt="<?php esc_attr_e( 'Restart', 'hextris-arcade' ); ?>" />

        <!-- High Score Display -->
        <div id="<?php echo esc_attr( $unique_id ); ?>-highScoreInGameText" class="hextris-high-score-display">
            <div class="hextris-high-score-label"><?php esc_html_e( 'HIGH SCORE', 'hextris-arcade' ); ?></div>
            <div id="<?php echo esc_attr( $unique_id ); ?>-currentHighScore" class="hextris-high-score-value">0</div>
        </div>

        <!-- Game Over Screen -->
        <div id="<?php echo esc_attr( $unique_id ); ?>-gameoverscreen" class="hextris-game-over-screen">
            <div class="hextris-game-over-container">
                <div class="hextris-game-over-title"><?php esc_html_e( 'GAME OVER', 'hextris-arcade' ); ?></div>
                <div id="<?php echo esc_attr( $unique_id ); ?>-cScore" class="hextris-final-score">0</div>
                <div class="hextris-high-scores-title"><?php esc_html_e( 'HIGH SCORES', 'hextris-arcade' ); ?></div>
                <div class="hextris-high-scores-list">
                    <div class="hextris-score-row"><span class="hextris-score-rank">1.</span> <span id="<?php echo esc_attr( $unique_id ); ?>-1place">0</span></div>
                    <div class="hextris-score-row"><span class="hextris-score-rank">2.</span> <span id="<?php echo esc_attr( $unique_id ); ?>-2place">0</span></div>
                    <div class="hextris-score-row"><span class="hextris-score-rank">3.</span> <span id="<?php echo esc_attr( $unique_id ); ?>-3place">0</span></div>
                </div>
            </div>
            <div class="hextris-game-over-bottom">
                <img id="<?php echo esc_attr( $unique_id ); ?>-restart"
                     class="hextris-restart-game-btn"
                     src="<?php echo esc_url( HEXTRIS_ARCADE_URL . 'public/images/btn_restart.svg' ); ?>"
                     alt="<?php esc_attr_e( 'Restart', 'hextris-arcade' ); ?>" />
            </div>
        </div>

        <?php if ( $show_leaderboard ) : ?>
        <!-- Global Leaderboard (optional) -->
        <div id="<?php echo esc_attr( $unique_id ); ?>-leaderboard" class="hextris-leaderboard" style="display: none;">
            <h3><?php esc_html_e( 'Leaderboard', 'hextris-arcade' ); ?></h3>
            <div class="hextris-leaderboard-list"></div>
        </div>
        <?php endif; ?>

    </div>
</div>
