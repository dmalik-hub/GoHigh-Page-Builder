<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class SelectControl extends Control {
	public function get_type(): string { return 'select'; }
	public function get_default_settings(): array {
		return [ 'options' => [], 'multiple' => false ];
	}
	public function sanitize( mixed $value ): string {
		return sanitize_text_field( (string) $value );
	}
}
