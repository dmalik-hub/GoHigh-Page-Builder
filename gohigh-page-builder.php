<?php
/**
 * Plugin Name: GoHigh Page Builder
 * Description: The complete drag-and-drop page builder — Pro widgets, Theme Builder, Popup Builder, Forms, WooCommerce, Dynamic Tags, and more. Fully standalone, no external dependencies.
 * Plugin URI: https://gohigh.io/
 * Version: 2.0.0
 * Author: GoHigh
 * Author URI: https://gohigh.io/
 * License: GPL-2.0-or-later
 * Requires PHP: 7.4
 * Requires at least: 6.6
 * Text Domain: gohigh-page-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GHPB_VERSION', '2.0.0' );
define( 'GHPB_FILE',    __FILE__ );
define( 'GHPB_PATH',    plugin_dir_path( __FILE__ ) );
define( 'GHPB_URL',     plugins_url( '/', __FILE__ ) );

// ── Elementor free constants ─────────────────────────────────────────────────
// Pre-defined here so the bundled elementor.php's define() calls become no-ops.
define( 'ELEMENTOR_VERSION',       '4.1.3' );
define( 'ELEMENTOR__FILE__',       GHPB_PATH . 'vendor-elementor/elementor.php' );
define( 'ELEMENTOR_PLUGIN_BASE',   plugin_basename( ELEMENTOR__FILE__ ) );
define( 'ELEMENTOR_PATH',          GHPB_PATH . 'vendor-elementor/' );
define( 'ELEMENTOR_URL',           GHPB_URL  . 'vendor-elementor/' );
define( 'ELEMENTOR_MODULES_PATH',  ELEMENTOR_PATH . 'modules' );
define( 'ELEMENTOR_ASSETS_PATH',   ELEMENTOR_PATH . 'assets/' );
define( 'ELEMENTOR_ASSETS_URL',    ELEMENTOR_URL  . 'assets/' );
// Blank token disables Mixpanel telemetry.
if ( ! defined( 'ELEMENTOR_EDITOR_EVENTS_MIXPANEL_TOKEN' ) ) {
	define( 'ELEMENTOR_EDITOR_EVENTS_MIXPANEL_TOKEN', '' );
}

// ── Elementor Pro constants ───────────────────────────────────────────────────
define( 'ELEMENTOR_PRO_VERSION',                   '4.1.1' );
define( 'ELEMENTOR_PRO__FILE__',                   GHPB_PATH . 'elementor-pro/elementor-pro.php' );
define( 'ELEMENTOR_PRO_PLUGIN_BASE',               plugin_basename( ELEMENTOR_PRO__FILE__ ) );
define( 'ELEMENTOR_PRO_PATH',                      GHPB_PATH . 'elementor-pro/' );
define( 'ELEMENTOR_PRO_URL',                       GHPB_URL  . 'elementor-pro/' );
define( 'ELEMENTOR_PRO_ASSETS_PATH',               ELEMENTOR_PRO_PATH . 'assets/' );
define( 'ELEMENTOR_PRO_ASSETS_URL',                ELEMENTOR_PRO_URL  . 'assets/' );
define( 'ELEMENTOR_PRO_MODULES_PATH',              ELEMENTOR_PRO_PATH . 'modules/' );
define( 'ELEMENTOR_PRO_MODULES_URL',               ELEMENTOR_PRO_URL  . 'modules/' );
define( 'ELEMENTOR_PRO_REQUIRED_CORE_VERSION',     '3.35' );
define( 'ELEMENTOR_PRO_RECOMMENDED_CORE_VERSION',  '4.1' );

// ── License bypass ────────────────────────────────────────────────────────────
// Injects a valid-looking license record directly into WP options and mocks
// the remote API response so no external validation call is ever needed.
update_option( 'elementor_pro_license_key', 'gohigh-standalone' );
update_option( '_elementor_pro_license_v2_data', [
	'timeout' => strtotime( '+12 hours', current_time( 'timestamp' ) ),
	'value'   => wp_json_encode( [
		'success'  => true,
		'license'  => 'valid',
		'expires'  => '01.01.2030',
		'features' => [
			'custom-attributes', 'custom_code', 'custom-css', 'global-css',
			'display-conditions', 'dynamic-tags-acf', 'dynamic-tags-pods',
			'dynamic-tags-toolset', 'element-manager-permissions', 'global-widget',
			'editor_comments', 'stripe-button', 'popup', 'role-manager',
			'woocommerce-menu-cart', 'product-single', 'product-archive',
			'settings-woocommerce-pages', 'settings-woocommerce-notices',
			'dynamic-tags-wc', 'atomic-custom-attributes', 'theme-builder',
			'form-submissions', 'akismet', 'activity-log', 'cf7db', 'transitions',
			'size-variable', 'notes', 'atomic-custom-css',
		],
	] ),
] );

add_filter( 'elementor/connect/additional-connect-info', '__return_empty_array', 999 );

// Block all outbound Elementor API calls (license validation, usage telemetry,
// update checks, etc.). Must run early so nothing slips through on plugins_loaded.
add_filter( 'pre_http_request', 'ghpb_intercept_elementor_requests', 1, 3 );

function ghpb_intercept_elementor_requests( $pre, $parsed_args, $url ) {
	if ( strpos( $url, 'my.elementor.com/api/v2/licenses' ) !== false ) {
		return [
			'response' => [ 'code' => 200, 'message' => 'OK' ],
			'body'     => wp_json_encode( [
				'success' => true,
				'license' => 'valid',
				'expires' => '01.01.2030',
			] ),
			'headers'  => [],
			'cookies'  => [],
			'filename' => null,
		];
	}

	// Block remaining elementor.com API calls (telemetry, updates, connect, etc.)
	if (
		strpos( $url, 'my.elementor.com' ) !== false ||
		strpos( $url, 'elementor.com/api' ) !== false ||
		strpos( $url, 'go.elementor.com' ) !== false
	) {
		return new WP_Error( 'ghpb_blocked', 'External Elementor API calls are disabled in GoHigh Page Builder.' );
	}

	return $pre;
}

// ── Bootstrap Elementor Pro after free loads ─────────────────────────────────
// Must be registered BEFORE including plugin.php, because plugin.php calls
// Plugin::instance() immediately, which fires do_action('elementor/loaded').
add_action( 'elementor/loaded', function() {
	if ( file_exists( ELEMENTOR_PRO_PATH . 'vendor/autoload.php' ) ) {
		require_once ELEMENTOR_PRO_PATH . 'vendor/autoload.php';
	}
	if ( file_exists( ELEMENTOR_PRO_PATH . 'plugin.php' ) ) {
		require_once ELEMENTOR_PRO_PATH . 'plugin.php';
	}
}, 0 );

// ── Bootstrap Elementor free ─────────────────────────────────────────────────
if ( file_exists( ELEMENTOR_PATH . 'vendor/autoload.php' ) ) {
	require_once ELEMENTOR_PATH . 'vendor/autoload.php';
}

$_ghpb_dep_func = ELEMENTOR_PATH . 'vendor_prefixed/twig/symfony/deprecation-contracts/function.php';
if ( file_exists( $_ghpb_dep_func ) ) {
	require_once $_ghpb_dep_func;
	if ( ! function_exists( 'trigger_deprecation' ) ) {
		function trigger_deprecation( string $package, string $version, string $message, ...$args ): void {
			\ElementorDeps\trigger_deprecation( $package, $version, $message, ...$args );
		}
	}
}
unset( $_ghpb_dep_func );

if ( version_compare( PHP_VERSION, '7.4', '>=' ) && version_compare( get_bloginfo( 'version' ), '6.5', '>=' ) ) {
	// Including this file calls Plugin::instance() at the bottom, which fires
	// do_action('elementor/loaded') — triggering our Pro bootstrap above.
	require_once ELEMENTOR_PATH . 'includes/plugin.php';
} else {
	add_action( 'admin_notices', function() {
		echo '<div class="notice notice-error"><p><strong>GoHigh Page Builder</strong> requires PHP 7.4+ and WordPress 6.5+.</p></div>';
	} );
}
