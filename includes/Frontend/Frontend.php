<?php
namespace GoHigh\PageBuilder\Frontend;

defined( 'ABSPATH' ) || exit;

class Frontend {

	private Renderer $renderer;

	public function __construct() {
		$this->renderer = new Renderer();
		add_filter( 'the_content', [ $this, 'filter_content' ], 1 );
	}

	/**
	 * Intercepts ?ghpb-preview=1 requests and outputs a preview frame.
	 */
	public function template_include( string $template ): string {
		if ( ! $this->is_preview_request() ) {
			return $template;
		}

		$post_id = absint( $_GET['post'] ?? 0 );
		if ( ! $post_id ) {
			return $template;
		}

		// Verify the nonce for non-logged-in users, or require edit capability.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			$nonce = $_GET['nonce'] ?? '';
			if ( ! wp_verify_nonce( $nonce, 'ghpb_preview' ) ) {
				wp_die( esc_html__( 'Access denied.', 'gohigh-page-builder' ) );
			}
		}

		global $post;
		$post = get_post( $post_id ); // phpcs:ignore
		if ( $post ) {
			setup_postdata( $post );
		}

		// Replace template with our preview frame.
		return GHPB_PATH . 'templates/preview-frame.php';
	}

	/**
	 * Replaces post content with page builder output for published builder pages.
	 */
	public function filter_content( string $content ): string {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		if ( get_post_meta( $post_id, '_gohigh_edit_mode', true ) !== 'builder' ) {
			return $content;
		}

		$raw = get_post_meta( $post_id, '_gohigh_data', true );
		if ( ! $raw ) {
			return $content;
		}

		$data     = json_decode( $raw, true );
		$elements = is_array( $data ) ? ( $data['content'] ?? [] ) : [];

		if ( empty( $elements ) ) {
			return $content;
		}

		ob_start();
		echo '<div class="ghpb-page-builder-content">';
		$this->renderer->render( $elements );
		echo '</div>';
		return ob_get_clean();
	}

	public function render_document( int $post_id ): void {
		$raw = get_post_meta( $post_id, '_gohigh_data', true );
		if ( ! $raw ) {
			return;
		}
		$data     = json_decode( $raw, true );
		$elements = is_array( $data ) ? ( $data['content'] ?? [] ) : [];
		$this->renderer->render( $elements );
	}

	private function is_preview_request(): bool {
		return isset( $_GET['ghpb-preview'] ) && '1' === $_GET['ghpb-preview'];
	}
}
