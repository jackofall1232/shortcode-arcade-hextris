=== Hextris Arcade ===
Contributors: hextristeam
Tags: game, puzzle, arcade, hextris, tetris, entertainment, shortcode, block
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A fast-paced puzzle game where players rotate a hexagon to match falling colored blocks. Embed with a shortcode or Gutenberg block.

== Description ==

Hextris Arcade brings the addictive Hextris puzzle game to your WordPress site. Players must rotate a central hexagon to catch falling colored blocks and match three or more of the same color to score points.

= Features =

* **Easy Integration** - Use the `[hextris]` shortcode or Gutenberg block
* **Multiple Difficulty Levels** - Easy, Normal, and Hard modes
* **Customizable Themes** - Default, Dark, Colorblind-friendly, or Custom colors
* **Leaderboard System** - Track high scores with REST API integration
* **Responsive Design** - Works on desktop, tablet, and mobile devices
* **Touch Support** - Swipe to rotate on mobile devices
* **Keyboard Controls** - Arrow keys or A/D keys on desktop
* **Multiple Instances** - Add multiple games to the same page
* **Accessibility** - ARIA labels and reduced motion support

= Shortcode Usage =

Basic usage:
`[hextris]`

With all options:
`[hextris width="100%" height="600px" theme="dark" difficulty="normal" show_leaderboard="true"]`

= Shortcode Attributes =

* `width` - CSS width value (default: 100%)
* `height` - CSS height value (default: 600px)
* `max_width` - Maximum width (default: 800px)
* `theme` - Color theme: default, dark, colorblind, custom (default: default)
* `difficulty` - Game difficulty: easy, normal, hard (default: normal)
* `show_leaderboard` - Show leaderboard: true/false (default: true)

= Gutenberg Block =

Search for "Hextris" in the block inserter to add the game block with visual settings.

== Installation ==

1. Upload the `hextris-arcade` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the `[hextris]` shortcode or Gutenberg block to add the game
4. Configure settings under Settings > Hextris Arcade

== Frequently Asked Questions ==

= How do I play Hextris? =

Use the left/right arrow keys (or A/D keys) to rotate the hexagon. On mobile devices, swipe left or right. Match 3 or more blocks of the same color to clear them and score points. Don't let the blocks stack too high!

= Can I have multiple games on one page? =

Yes! Each shortcode or block creates an independent game instance.

= How do I customize the colors? =

Go to Settings > Hextris Arcade and select "Custom" as the color scheme, then set your preferred colors.

= Is the leaderboard global? =

The leaderboard is per-site. Scores are stored in your WordPress database.

== Screenshots ==

1. Hextris game in action
2. Game over screen with high scores
3. Admin settings panel
4. Gutenberg block editor

== Changelog ==

= 1.0.0 =
* Initial release
* Shortcode support with full customization
* Gutenberg block support
* REST API for score submission
* Multiple difficulty levels
* Multiple color themes
* Responsive design
* Touch and keyboard controls

== Upgrade Notice ==

= 1.0.0 =
Initial release of Hextris Arcade for WordPress.
