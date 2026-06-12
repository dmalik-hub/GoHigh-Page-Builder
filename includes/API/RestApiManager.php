<?php
namespace GoHigh\PageBuilder\API;

use GoHigh\PageBuilder\API\Endpoints\DocumentsEndpoint;
use GoHigh\PageBuilder\API\Endpoints\ElementsEndpoint;

defined( 'ABSPATH' ) || exit;

class RestApiManager {

	const NAMESPACE = 'gohigh/v1';

	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes(): void {
		( new DocumentsEndpoint() )->register_routes();
		( new ElementsEndpoint() )->register_routes();
	}
}
