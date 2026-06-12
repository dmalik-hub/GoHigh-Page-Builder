<?php
namespace GoHigh\PageBuilder\Core;

defined( 'ABSPATH' ) || exit;

class Assets {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
	}

	public function enqueue_editor_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'admin_page_gohigh-editor' !== $screen->id ) {
			return;
		}

		// WordPress core scripts needed in editor.
		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'dashicons' );

		// Backbone + Marionette (bundled in node_modules, output to dist).
		wp_enqueue_script(
			'backbone-marionette',
			GHPB_DIST_URL . 'js/vendor/backbone.marionette.min.js',
			[ 'backbone', 'underscore', 'jquery' ],
			'4.1.3',
			true
		);
		wp_enqueue_script(
			'backbone-radio',
			GHPB_DIST_URL . 'js/vendor/backbone.radio.min.js',
			[ 'backbone' ],
			'2.0.0',
			true
		);

		// Editor bundle.
		wp_enqueue_script(
			'ghpb-editor',
			GHPB_DIST_URL . 'js/editor.js',
			[ 'jquery', 'backbone', 'underscore', 'backbone-marionette', 'backbone-radio',
			  'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable',
			  'jquery-ui-resizable', 'wp-api-fetch', 'wp-hooks' ],
			GHPB_VERSION,
			true
		);
		wp_enqueue_style(
			'ghpb-editor',
			GHPB_DIST_URL . 'css/editor.css',
			[ 'dashicons', 'wp-color-picker' ],
			GHPB_VERSION
		);

		$post_id = absint( $_GET['post'] ?? 0 );
		$post    = $post_id ? get_post( $post_id ) : null;

		// Localise editor data.
		wp_localize_script( 'ghpb-editor', 'ghpbEditorConfig', [
			'postId'        => $post_id,
			'postTitle'     => $post ? esc_html( $post->post_title ) : '',
			'postType'      => $post ? $post->post_type : '',
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'restUrl'       => rest_url( 'gohigh/v1/' ),
			'homeUrl'       => home_url(),
			'adminUrl'      => admin_url(),
			'previewUrl'    => add_query_arg( [ 'ghpb-preview' => '1', 'post' => $post_id ], home_url() ),
			'widgetTypes'   => $this->get_widget_types_config(),
			'breakpoints'   => [
				'desktop' => 99999,
				'tablet'  => 1024,
				'mobile'  => 767,
			],
			'i18n'          => $this->get_i18n_strings(),
		] );
	}

	private function get_widget_types_config(): array {
		$manager = \GoHigh\PageBuilder\Plugin::get_instance()->widgets_manager;
		$config  = [];
		foreach ( $manager->get_widgets() as $name => $widget ) {
			$config[ $name ] = [
				'name'       => $widget->get_name(),
				'title'      => $widget->get_title(),
				'icon'       => $widget->get_icon(),
				'categories' => $widget->get_categories(),
				'keywords'   => $widget->get_keywords(),
				'controls'   => $widget->get_controls_config(),
			];
		}
		return $config;
	}

	private function get_i18n_strings(): array {
		return [
			'save'          => __( 'Save', 'gohigh-page-builder' ),
			'saving'        => __( 'Saving…', 'gohigh-page-builder' ),
			'saved'         => __( 'Saved', 'gohigh-page-builder' ),
			'preview'       => __( 'Preview', 'gohigh-page-builder' ),
			'undo'          => __( 'Undo', 'gohigh-page-builder' ),
			'redo'          => __( 'Redo', 'gohigh-page-builder' ),
			'desktop'       => __( 'Desktop', 'gohigh-page-builder' ),
			'tablet'        => __( 'Tablet', 'gohigh-page-builder' ),
			'mobile'        => __( 'Mobile', 'gohigh-page-builder' ),
			'addElement'    => __( 'Add Element', 'gohigh-page-builder' ),
			'addSection'    => __( 'Add Section', 'gohigh-page-builder' ),
			'deleteElement' => __( 'Delete Element', 'gohigh-page-builder' ),
			'editElement'   => __( 'Edit Element', 'gohigh-page-builder' ),
			'duplicate'     => __( 'Duplicate', 'gohigh-page-builder' ),
			'searchWidgets' => __( 'Search Widgets…', 'gohigh-page-builder' ),
			'content'       => __( 'Content', 'gohigh-page-builder' ),
			'style'         => __( 'Style', 'gohigh-page-builder' ),
			'advanced'      => __( 'Advanced', 'gohigh-page-builder' ),
			'exitEditor'    => __( 'Exit to Dashboard', 'gohigh-page-builder' ),
		];
	}

	public function enqueue_frontend_assets(): void {
		if ( ! $this->should_enqueue_frontend() ) {
			return;
		}

		wp_enqueue_script(
			'ghpb-frontend',
			GHPB_DIST_URL . 'js/frontend.js',
			[ 'jquery' ],
			GHPB_VERSION,
			true
		);
		wp_enqueue_style(
			'ghpb-frontend',
			GHPB_DIST_URL . 'css/frontend.css',
			[],
			GHPB_VERSION
		);

		$post_id = get_the_ID();
		$css     = get_post_meta( $post_id, '_gohigh_data_css', true );
		if ( $css ) {
			wp_add_inline_style( 'ghpb-frontend', wp_strip_all_tags( $css ) );
		}
	}

	private function should_enqueue_frontend(): bool {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return false;
		}
		return get_post_meta( $post_id, '_gohigh_edit_mode', true ) === 'builder';
	}
}
