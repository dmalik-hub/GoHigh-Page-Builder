<?php
namespace GoHigh\PageBuilder\Core;

defined( 'ABSPATH' ) || exit;

class Ajax {

	public function init(): void {
		add_action( 'wp_ajax_ghpb_render_widget_preview', [ $this, 'render_widget_preview' ] );
		add_action( 'wp_ajax_ghpb_save_draft', [ $this, 'save_draft' ] );
	}

	public function render_widget_preview(): void {
		check_ajax_referer( 'ghpb_ajax', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied.' ], 403 );
		}

		$widget_type = sanitize_key( $_POST['widgetType'] ?? '' );
		$settings    = json_decode( stripslashes( $_POST['settings'] ?? '{}' ), true );
		$post_id     = absint( $_POST['postId'] ?? 0 );

		if ( ! $widget_type ) {
			wp_send_json_error( [ 'message' => 'Missing widget type.' ] );
		}

		$plugin = \GoHigh\PageBuilder\Plugin::get_instance();
		$widget = $plugin->widgets_manager->get_widget( $widget_type );

		if ( ! $widget ) {
			wp_send_json_error( [ 'message' => 'Widget type not found.' ] );
		}

		$widget->set_settings( $settings ?? [] );

		// Run in the context of the edited post.
		if ( $post_id ) {
			global $post;
			$post = get_post( $post_id ); // phpcs:ignore
			setup_postdata( $post );
		}

		ob_start();
		$widget->render_content();
		$html = ob_get_clean();

		if ( $post_id ) {
			wp_reset_postdata();
		}

		wp_send_json_success( [ 'html' => $html ] );
	}

	public function save_draft(): void {
		check_ajax_referer( 'ghpb_ajax', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Permission denied.', 403 );
		}

		$post_id = absint( $_POST['postId'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Invalid post.' );
		}

		$data = stripslashes( $_POST['data'] ?? '' );
		update_post_meta( $post_id, '_gohigh_draft', sanitize_textarea_field( $data ) );

		wp_send_json_success( [ 'saved' => true ] );
	}
}
