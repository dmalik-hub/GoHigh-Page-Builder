<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class RepeaterControl extends Control {
	public function get_type(): string { return 'repeater'; }
	public function get_default_value(): mixed { return []; }
	public function get_default_settings(): array {
		return [
			'fields'      => [],
			'item_actions' => [ 'duplicate', 'delete' ],
			'title_field' => '',
		];
	}
	public function sanitize( mixed $value ): array {
		return is_array( $value ) ? $value : [];
	}
}
