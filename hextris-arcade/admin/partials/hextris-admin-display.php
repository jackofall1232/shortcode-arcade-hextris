<?php
/**
 * Admin settings page template.
 *
 * @package Hextris_Arcade
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap hextris-admin-wrap">
    <h1>
        <span class="hextris-logo">&#x2B21;</span>
        <?php echo esc_html( get_admin_page_title() ); ?>
    </h1>

    <div class="hextris-admin-container">
        <div class="hextris-admin-main">
            <form action="options.php" method="post">
                <?php
                settings_fields( 'hextris_settings' );
                do_settings_sections( 'hextris-arcade' );
                submit_button( __( 'Save Settings', 'hextris-arcade' ) );
                ?>
            </form>
        </div>

        <div class="hextris-admin-sidebar">
            <div class="hextris-card">
                <h3><?php esc_html_e( 'Quick Start', 'hextris-arcade' ); ?></h3>
                <p><?php esc_html_e( 'Add the game to any page or post using:', 'hextris-arcade' ); ?></p>
                <code>[hextris]</code>

                <h4><?php esc_html_e( 'Shortcode Options', 'hextris-arcade' ); ?></h4>
                <ul class="hextris-shortcode-examples">
                    <li><code>[hextris width="600px" height="800px"]</code></li>
                    <li><code>[hextris theme="dark"]</code></li>
                    <li><code>[hextris difficulty="easy"]</code></li>
                    <li><code>[hextris show_leaderboard="false"]</code></li>
                </ul>
            </div>

            <div class="hextris-card">
                <h3><?php esc_html_e( 'Available Themes', 'hextris-arcade' ); ?></h3>
                <ul>
                    <li><strong>default</strong> - <?php esc_html_e( 'Classic colors', 'hextris-arcade' ); ?></li>
                    <li><strong>dark</strong> - <?php esc_html_e( 'Dark background', 'hextris-arcade' ); ?></li>
                    <li><strong>colorblind</strong> - <?php esc_html_e( 'Accessible palette', 'hextris-arcade' ); ?></li>
                    <li><strong>custom</strong> - <?php esc_html_e( 'Your custom colors', 'hextris-arcade' ); ?></li>
                </ul>
            </div>

            <div class="hextris-card">
                <h3><?php esc_html_e( 'Gutenberg Block', 'hextris-arcade' ); ?></h3>
                <p><?php esc_html_e( 'You can also use the Hextris Game block in the block editor. Search for "Hextris" in the block inserter.', 'hextris-arcade' ); ?></p>
            </div>

            <div class="hextris-card hextris-card-info">
                <h3><?php esc_html_e( 'Need Help?', 'hextris-arcade' ); ?></h3>
                <p><?php esc_html_e( 'Visit the plugin documentation or report issues on GitHub.', 'hextris-arcade' ); ?></p>
                <a href="https://github.com/hextris/hextris" target="_blank" class="button">
                    <?php esc_html_e( 'View Documentation', 'hextris-arcade' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>
