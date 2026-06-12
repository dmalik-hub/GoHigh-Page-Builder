<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class ChooseControl extends Control {
	public function get_type(): string { return 'choose'; }
	public function get_default_settings(): array {
		return [
			'options' => [],
			// Each option: [ 'title' => '', 'icon' => 'dashicons-...' ]
		];
	}
	public function sanitize( mixed $value ): string {
		return sanitize_text_field( (string) $value );
	}
}
