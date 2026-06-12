<?php
namespace GoHigh\PageBuilder;

use GoHigh\PageBuilder\Core\Ajax;
use GoHigh\PageBuilder\Core\Assets;
use GoHigh\PageBuilder\Core\Capabilities;
use GoHigh\PageBuilder\Controls\ControlsManager;
use GoHigh\PageBuilder\Widgets\WidgetsManager;
use GoHigh\PageBuilder\Documents\DocumentsManager;
use GoHigh\PageBuilder\Frontend\Frontend;
use GoHigh\PageBuilder\API\RestApiManager;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	private static ?Plugin $instance = null;

	public ControlsManager  $controls_manager;
	public WidgetsManager   $widgets_manager;
	public DocumentsManager $documents_manager;
	public Assets           $assets;
	public Frontend         $frontend;

	private function __construct() {}

	public static function instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
			static::$instance->init();
		}
		return static::$instance;
	}

	private function init(): void {
		$this->load_textdomain();
		$this->init_components();
		$this->register_hooks();
	}

	private function load_textdomain(): void {
		load_plugin_textdomain(
			'gohigh-page-builder',
			false,
			dirname( plugin_basename( GHPB_FILE ) ) . '/languages'
		);
	}

	private function init_components(): void {
		$this->controls_manager  = new ControlsManager();
		$this->widgets_manager   = new WidgetsManager();
		$this->documents_manager = new DocumentsManager();
		$this->assets            = new Assets();
		$this->frontend          = new Frontend();

		( new Ajax() )->init();
		( new Capabilities() )->init();
		( new RestApiManager() )->init();
	}

	private function register_hooks(): void {
		add_action( 'init', [ $this, 'on_init' ] );
		add_filter( 'template_include', [ $this->frontend, 'template_include' ] );
		// Custom admin action — fires VERY early in admin.php, before any admin
		// chrome is rendered. We render our editor template and exit, which
		// gives us a full-screen editor like Elementor (no WP sidebar/topbar).
		add_action( 'admin_action_gohigh_editor', [ $this, 'editor_action' ] );
	}

	public function on_init(): void {
		$this->register_post_meta();
		do_action( 'gohigh/init', $this );
	}

	private function register_post_meta(): void {
		$supported_post_types = apply_filters( 'gohigh/supported_post_types', [ 'post', 'page' ] );
		foreach ( $supported_post_types as $post_type ) {
			register_post_meta( $post_type, '_gohigh_data', [
				'single'       => true,
				'type'         => 'string',
				'show_in_rest' => false,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			] );
			register_post_meta( $post_type, '_gohigh_data_css', [
				'single'       => true,
				'type'         => 'string',
				'show_in_rest' => false,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			] );
			register_post_meta( $post_type, '_gohigh_edit_mode', [
				'single'        => true,
				'type'          => 'string',
				'show_in_rest'  => false,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			] );
		}
	}

	/**
	 * Renders the full-screen editor directly, bypassing all admin chrome.
	 * Hooked to admin_action_gohigh_editor which fires before WP loads sidebar/topbar.
	 */
	public function editor_action(): void {
		$post_id = absint( $_GET['post'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You do not have permission to edit this post.', 'gohigh-page-builder' ) );
		}

		update_post_meta( $post_id, '_gohigh_edit_mode', 'builder' );

		// Set up the post context so wp_head() / templates see the right post.
		global $post;
		$post = get_post( $post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		setup_postdata( $post );

		// Manually enqueue editor assets (no screen context here).
		$this->assets->do_enqueue_editor_assets( $post_id );

		require GHPB_PATH . 'templates/editor-wrapper.php';
		exit;
	}

	public static function activate(): void {
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	public static function get_instance(): ?Plugin {
		return static::$instance;
	}
}
