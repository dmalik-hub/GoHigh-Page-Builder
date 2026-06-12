<?php
namespace GoHigh\PageBuilder\Documents;

defined( 'ABSPATH' ) || exit;

abstract class Document {

	protected int     $post_id;
	protected ?\WP_Post $post;

	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
		$this->post    = get_post( $post_id ) ?: null;
	}

	abstract public function get_name(): string;

	public function get_post_id(): int {
		return $this->post_id;
	}

	public function get_elements_data(): array {
		$raw = get_post_meta( $this->post_id, '_gohigh_data', true );
		if ( ! $raw ) {
			return [];
		}
		$data = json_decode( $raw, true );
		return is_array( $data ) ? ( $data['content'] ?? [] ) : [];
	}

	public function save( array $elements, array $page_settings = [], string $status = 'publish' ): bool {
		$data = [
			'version'       => GHPB_VERSION,
			'page_settings' => $page_settings,
			'content'       => $elements,
		];

		update_post_meta( $this->post_id, '_gohigh_data', wp_json_encode( $data ) );
		update_post_meta( $this->post_id, '_gohigh_edit_mode', 'builder' );

		// Regenerate CSS cache.
		$css = \GoHigh\PageBuilder\Frontend\CSSGenerator::instance()->generate( $this->post_id, $elements );
		update_post_meta( $this->post_id, '_gohigh_data_css', $css );

		// Update post status if needed.
		if ( $this->post && $this->post->post_status !== $status ) {
			wp_update_post( [
				'ID'          => $this->post_id,
				'post_status' => $status,
			] );
		}

		do_action( 'gohigh/document/after_save', $this, $elements );

		return true;
	}

	public function get_edit_url(): string {
		return admin_url( 'admin.php?page=gohigh-editor&post=' . $this->post_id );
	}

	public function get_preview_url(): string {
		return add_query_arg( [ 'ghpb-preview' => '1', 'post' => $this->post_id, 'nonce' => wp_create_nonce( 'ghpb_preview' ) ], home_url() );
	}

	public function get_page_settings(): array {
		$raw = get_post_meta( $this->post_id, '_gohigh_data', true );
		if ( ! $raw ) {
			return [];
		}
		$data = json_decode( $raw, true );
		return is_array( $data ) ? ( $data['page_settings'] ?? [] ) : [];
	}
}
