<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class TextControl extends Control {
	public function get_type(): string { return 'text'; }
	public function get_default_settings(): array {
		return [
			'placeholder' => '',
			'input_type'  => 'text',
			'maxlength'   => null,
		];
	}
	public function sanitize( mixed $value ): string {
		return sanitize_text_field( (string) $value );
	}
}
