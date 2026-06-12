<?php
namespace GoHigh\PageBuilder\API\Endpoints;

use GoHigh\PageBuilder\API\RestApiManager;
use GoHigh\PageBuilder\Plugin;

defined( 'ABSPATH' ) || exit;

class ElementsEndpoint {

	public function register_routes(): void {
		register_rest_route( RestApiManager::NAMESPACE, '/elements/types', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_widget_types' ],
			'permission_callback' => fn() => current_user_can( 'edit_posts' ),
		] );

		register_rest_route( RestApiManager::NAMESPACE, '/elements/categories', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_categories' ],
			'permission_callback' => fn() => current_user_can( 'edit_posts' ),
		] );
	}

	public function get_widget_types(): \WP_REST_Response {
		$plugin  = Plugin::get_instance();
		$widgets = $plugin->widgets_manager->get_widgets();
		$types   = [];

		foreach ( $widgets as $name => $widget ) {
			$types[ $name ] = [
				'name'       => $widget->get_name(),
				'title'      => $widget->get_title(),
				'icon'       => $widget->get_icon(),
				'categories' => $widget->get_categories(),
				'keywords'   => $widget->get_keywords(),
				'controls'   => $widget->get_controls_config(),
			];
		}

		return rest_ensure_response( $types );
	}

	public function get_categories(): \WP_REST_Response {
		$categories = apply_filters( 'gohigh/widget_categories', [
			'basic'   => [ 'title' => __( 'Basic', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-admin-generic' ],
			'general' => [ 'title' => __( 'General', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-screenoptions' ],
			'pro'     => [ 'title' => __( 'Pro', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-star-filled' ],
		] );

		return rest_ensure_response( $categories );
	}
}
