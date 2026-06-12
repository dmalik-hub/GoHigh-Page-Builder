<?php
namespace GoHigh\PageBuilder\API\Endpoints;

use GoHigh\PageBuilder\API\RestApiManager;
use GoHigh\PageBuilder\Plugin;

defined( 'ABSPATH' ) || exit;

class DocumentsEndpoint {

	public function register_routes(): void {
		register_rest_route( RestApiManager::NAMESPACE, '/documents/(?P<id>[\d]+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_document' ],
				'permission_callback' => [ $this, 'permission_check' ],
				'args'                => [ 'id' => [ 'validate_callback' => fn( $v ) => is_numeric( $v ) ] ],
			],
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_document' ],
				'permission_callback' => [ $this, 'permission_check' ],
			],
		] );

		register_rest_route( RestApiManager::NAMESPACE, '/documents/(?P<id>[\d]+)/autosave', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'autosave_document' ],
			'permission_callback' => [ $this, 'permission_check' ],
		] );
	}

	public function permission_check( \WP_REST_Request $request ): bool|\WP_Error {
		$post_id = (int) $request->get_param( 'id' );
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You do not have permission to edit this post.', 'gohigh-page-builder' ), [ 'status' => 403 ] );
		}
		return true;
	}

	public function get_document( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$post_id = (int) $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error( 'not_found', 'Post not found.', [ 'status' => 404 ] );
		}

		$plugin   = Plugin::get_instance();
		$document = $plugin->documents_manager->get( $post_id );
		$elements = $document->get_elements_data();
		$settings = $document->get_page_settings();
		$css      = get_post_meta( $post_id, '_gohigh_data_css', true );

		return rest_ensure_response( [
			'id'            => $post_id,
			'title'         => $post->post_title,
			'status'        => $post->post_status,
			'type'          => $post->post_type,
			'edit_mode'     => get_post_meta( $post_id, '_gohigh_edit_mode', true ),
			'elements'      => $elements,
			'page_settings' => $settings,
			'css'           => $css,
			'version'       => GHPB_VERSION,
		] );
	}

	public function save_document( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$post_id  = (int) $request->get_param( 'id' );
		$body     = $request->get_json_params();
		$elements = $body['elements'] ?? [];
		$settings = $body['page_settings'] ?? [];
		$status   = sanitize_text_field( $body['status'] ?? 'publish' );

		if ( ! is_array( $elements ) ) {
			return new \WP_Error( 'invalid_data', 'Elements must be an array.', [ 'status' => 400 ] );
		}

		$plugin   = Plugin::get_instance();
		$document = $plugin->documents_manager->get( $post_id );

		if ( ! $document ) {
			return new \WP_Error( 'not_found', 'Document not found.', [ 'status' => 404 ] );
		}

		$result = $document->save( $elements, $settings, $status );

		if ( ! $result ) {
			return new \WP_Error( 'save_failed', 'Failed to save document.', [ 'status' => 500 ] );
		}

		$css = get_post_meta( $post_id, '_gohigh_data_css', true );

		return rest_ensure_response( [
			'id'       => $post_id,
			'saved_at' => current_time( 'c' ),
			'css'      => $css,
		] );
	}

	public function autosave_document( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$post_id  = (int) $request->get_param( 'id' );
		$body     = $request->get_json_params();
		$elements = $body['elements'] ?? [];
		$settings = $body['page_settings'] ?? [];

		if ( ! is_array( $elements ) ) {
			return new \WP_Error( 'invalid_data', 'Elements must be an array.', [ 'status' => 400 ] );
		}

		$data = wp_json_encode( [
			'version'       => GHPB_VERSION,
			'page_settings' => $settings,
			'content'       => $elements,
		] );

		update_post_meta( $post_id, '_gohigh_draft', $data );

		return rest_ensure_response( [
			'autosaved_at' => current_time( 'c' ),
		] );
	}
}
