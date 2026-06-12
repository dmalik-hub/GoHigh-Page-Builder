<?php
/**
 * Plugin Name: GoHigh Page Builder
 * Plugin URI:  https://gohigh.io/page-builder
 * Description: Standalone drag-and-drop page builder with Elementor Pro-level features. Zero external dependencies.
 * Version:     1.0.1
 * Author:      GoHigh
 * Author URI:  https://gohigh.io
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gohigh-page-builder
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP:  8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'GHPB_VERSION',     '1.0.1' );
define( 'GHPB_FILE',        __FILE__ );
define( 'GHPB_PATH',        plugin_dir_path( __FILE__ ) );
define( 'GHPB_URL',         plugin_dir_url( __FILE__ ) );
define( 'GHPB_ASSETS_URL',  GHPB_URL . 'assets/' );
define( 'GHPB_DIST_URL',    GHPB_URL . 'dist/' );
define( 'GHPB_MINIMUM_PHP', '8.0' );
define( 'GHPB_MINIMUM_WP',  '6.0' );

if ( version_compare( PHP_VERSION, GHPB_MINIMUM_PHP, '<' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>' .
			 sprintf( esc_html__( 'GoHigh Page Builder requires PHP %s or higher.', 'gohigh-page-builder' ), GHPB_MINIMUM_PHP ) .
			 '</p></div>';
	} );
	return;
}

if ( version_compare( $GLOBALS['wp_version'], GHPB_MINIMUM_WP, '<' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>' .
			 sprintf( esc_html__( 'GoHigh Page Builder requires WordPress %s or higher.', 'gohigh-page-builder' ), GHPB_MINIMUM_WP ) .
			 '</p></div>';
	} );
	return;
}

require_once GHPB_PATH . 'vendor/autoload.php';

add_action( 'plugins_loaded', [ \GoHigh\PageBuilder\Plugin::class, 'instance' ] );

register_activation_hook( __FILE__, [ \GoHigh\PageBuilder\Plugin::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \GoHigh\PageBuilder\Plugin::class, 'deactivate' ] );
