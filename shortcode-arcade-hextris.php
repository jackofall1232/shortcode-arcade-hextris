<?php
/*
 * Plugin Name: Shortcode Arcade Hextris
 * Plugin URI: https://github.com/jackofall1232/shortcode-arcade-hextris
 * Description: A WordPress shortcode plugin that embeds the Hextris puzzle game.
 * Version: 0.1.0
 * Author: Shortcode Arcade
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: shortcode-arcade-hextris
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register all Hextris assets (CSS and JS).
 */
function sacga_hextris_register_assets() {
    $plugin_url = plugin_dir_url( __FILE__ );

    // Register CSS
    wp_register_style(
        'sacga-hextris-fontawesome',
        $plugin_url . 'style/fa/css/font-awesome.min.css',
        array(),
        '0.1.0'
    );

    wp_register_style(
        'sacga-hextris-rrssb',
        $plugin_url . 'style/rrssb.css',
        array(),
        '0.1.0'
    );

    wp_register_style(
        'sacga-hextris-main',
        $plugin_url . 'style/style.css',
        array( 'sacga-hextris-fontawesome', 'sacga-hextris-rrssb' ),
        '0.1.0'
    );

    // Register Vendor JS
    wp_register_script(
        'sacga-hextris-hammer',
        $plugin_url . 'vendor/hammer.min.js',
        array(),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-cookie',
        $plugin_url . 'vendor/js.cookie.js',
        array(),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-jsonfn',
        $plugin_url . 'vendor/jsonfn.min.js',
        array(),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-keypress',
        $plugin_url . 'vendor/keypress.min.js',
        array(),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-sweetalert',
        $plugin_url . 'vendor/sweet-alert.min.js',
        array(),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-rrssb',
        $plugin_url . 'vendor/rrssb.min.js',
        array( 'jquery' ),
        '0.1.0',
        true
    );

    // Register Game JS (in dependency order)
    wp_register_script(
        'sacga-hextris-save-state',
        $plugin_url . 'js/save-state.js',
        array( 'sacga-hextris-cookie', 'sacga-hextris-jsonfn' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-view',
        $plugin_url . 'js/view.js',
        array( 'sacga-hextris-save-state' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-wavegen',
        $plugin_url . 'js/wavegen.js',
        array( 'sacga-hextris-view' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-math',
        $plugin_url . 'js/math.js',
        array( 'sacga-hextris-wavegen' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-block',
        $plugin_url . 'js/Block.js',
        array( 'sacga-hextris-math' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-hex',
        $plugin_url . 'js/Hex.js',
        array( 'sacga-hextris-block' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-text',
        $plugin_url . 'js/Text.js',
        array( 'sacga-hextris-hex' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-combotimer',
        $plugin_url . 'js/comboTimer.js',
        array( 'sacga-hextris-text' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-checking',
        $plugin_url . 'js/checking.js',
        array( 'sacga-hextris-combotimer' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-update',
        $plugin_url . 'js/update.js',
        array( 'sacga-hextris-checking' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-render',
        $plugin_url . 'js/render.js',
        array( 'sacga-hextris-update' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-input',
        $plugin_url . 'js/input.js',
        array( 'sacga-hextris-render', 'sacga-hextris-hammer', 'sacga-hextris-keypress' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-main',
        $plugin_url . 'js/main.js',
        array( 'sacga-hextris-input', 'jquery' ),
        '0.1.0',
        true
    );

    wp_register_script(
        'sacga-hextris-initialization',
        $plugin_url . 'js/initialization.js',
        array( 'sacga-hextris-main', 'sacga-hextris-sweetalert', 'sacga-hextris-rrssb' ),
        '0.1.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'sacga_hextris_register_assets' );

/**
 * Shortcode callback for [sacga_hextris].
 *
 * @return string The Hextris game HTML.
 */
function sacga_hextris_shortcode() {
    // Enqueue assets only when shortcode is used
    wp_enqueue_style( 'sacga-hextris-main' );
    wp_enqueue_script( 'sacga-hextris-initialization' );

    // Pass plugin URL to JavaScript for image paths
    wp_localize_script(
        'sacga-hextris-main',
        'sacgaHextris',
        array(
            'pluginUrl' => plugin_dir_url( __FILE__ ),
            'imagesUrl' => plugin_dir_url( __FILE__ ) . 'images/',
        )
    );

    $plugin_url = plugin_dir_url( __FILE__ );

    ob_start();
    ?>
    <div class="sacga-hextris">
        <canvas id="canvas"></canvas>
        <div id="overlay" class="faded overlay"></div>
        <div id="startBtn"></div>
        <div id="helpScreen" class="unselectable">
            <div id="inst_main_body"></div>
        </div>
        <img id="openSideBar" class="helpText" src="<?php echo esc_url( $plugin_url . 'images/btn_help.svg' ); ?>" alt="Help"/>
        <div class="faded overlay"></div>
        <img id="pauseBtn" src="<?php echo esc_url( $plugin_url . 'images/btn_pause.svg' ); ?>" alt="Pause"/>
        <img id="restartBtn" src="<?php echo esc_url( $plugin_url . 'images/btn_restart.svg' ); ?>" alt="Restart"/>
        <div id="HIGHSCORE">HIGH SCORE</div>
        <div id="highScoreInGameText">
            <div id="highScoreInGameTextHeader">HIGH SCORE</div>
            <div id="currentHighScore">0</div>
        </div>
        <div id="gameoverscreen">
            <div id="container">
                <div id="gameOverBox" class="GOTitle">GAME OVER</div>
                <div id="cScore">0</div>
                <div id="highScoresTitle" class="GOTitle">HIGH SCORES</div>
                <div class="score"><span class="scoreNum">1.</span> <div id="1place" style="display:inline;">0</div></div>
                <div class="score"><span class="scoreNum">2.</span> <div id="2place" style="display:inline;">0</div></div>
                <div class="score"><span class="scoreNum">3.</span> <div id="3place" style="display:inline;">0</div></div>
            </div>
            <div id="bottomContainer">
                <img id="restart" src="<?php echo esc_url( $plugin_url . 'images/btn_restart.svg' ); ?>" height="57px" alt="Restart"/>
                <div id="socialShare">
                    <svg width="224.6377px" height="57px" viewBox="0 0 255 65" version="1.1" xmlns="http://www.w3.org/2000/svg">
                        <title>Share button</title>
                        <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g id="Game-over-" transform="translate(-95.000000, -565.000000)">
                                <g id="Share-button" transform="translate(95.000000, 565.000000)">
                                    <polygon id="Score-hex-2" fill="#3498DB" transform="translate(127.661316, 32.500000) rotate(-90.000000) translate(-127.661316, -32.500000)" points="127.661316 -94.814636 160.137269 -76.064636 160.137269 141.064636 127.661317 159.814636 95.185364 141.064636 95.1853635 -76.064636"></polygon>
                                    <text id="SHARE-MY-SCORE" font-family="Exo" font-size="16" font-weight="420" fill="#FFFFFF">
                                        <tspan x="67" y="39">SHARE MY SCORE!</tspan>
                                    </text>
                                </g>
                            </g>
                        </g>
                    </svg>
                </div>
                <div id="buttonCont">
                    <ul class="rrssb-buttons">
                        <li class="rrssb-facebook">
                            <a href="http://www.facebook.com/sharer.php?s=100&p[url]=hextris.io" class="popup">
                                <span class="rrssb-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" width="29" height="29" viewBox="0 0 29 29">
                                        <path d="M26.4 0H2.6C1.714 0 0 1.715 0 2.6v23.8c0 .884 1.715 2.6 2.6 2.6h12.393V17.988h-3.996v-3.98h3.997v-3.062c0-3.746 2.835-5.97 6.177-5.97 1.6 0 2.444.173 2.845.226v3.792H21.18c-1.817 0-2.156.9-2.156 2.168v2.847h5.045l-.66 3.978h-4.386V29H26.4c.884 0 2.6-1.716 2.6-2.6V2.6c0-.885-1.716-2.6-2.6-2.6z" class="cls-2" fill-rule="evenodd"/>
                                    </svg>
                                </span>
                                <span class="rrssb-text">facebook</span>
                            </a>
                        </li>
                        <li class="rrssb-twitter">
                            <a href="http://twitter.com/home?status=Play Hextris! - http://hextris.github.io/ #hextris" class="popup">
                                <span class="rrssb-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
                                        <path d="M24.253 8.756C24.69 17.08 18.297 24.182 9.97 24.62c-3.122.162-6.22-.646-8.86-2.32 2.702.18 5.375-.648 7.507-2.32-2.072-.248-3.818-1.662-4.49-3.64.802.13 1.62.077 2.4-.154-2.482-.466-4.312-2.586-4.412-5.11.688.276 1.426.408 2.168.387-2.135-1.65-2.73-4.62-1.394-6.965C5.574 7.816 9.54 9.84 13.802 10.07c-.842-2.738.694-5.64 3.434-6.48 2.018-.624 4.212.043 5.546 1.682 1.186-.213 2.318-.662 3.33-1.317-.386 1.256-1.248 2.312-2.4 2.942 1.048-.106 2.07-.394 3.02-.85-.458 1.182-1.343 2.15-2.48 2.71z"/>
                                    </svg>
                                </span>
                                <span class="rrssb-text">twitter</span>
                            </a>
                        </li>
                    </ul>
                    <div id="badges">
                        <a href="https://play.google.com/store/apps/details?id=com.hextris.hextris"><img id="androidBadge" src="<?php echo esc_url( $plugin_url . 'images/android.png' ); ?>" alt="Get it on Google Play"/></a>
                        <a href="https://itunes.apple.com/us/app/hextris/id903769553?mt=8"><img id="iOSBadge" src="<?php echo esc_url( $plugin_url . 'images/appstore.svg' ); ?>" alt="Download on the App Store"/></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'sacga_hextris', 'sacga_hextris_shortcode' );
