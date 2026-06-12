<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class TextareaControl extends Control {
	public function get_type(): string { return 'textarea'; }
	public function get_default_settings(): array {
		return [ 'rows' => 5, 'placeholder' => '' ];
	}
	public function sanitize( mixed $value ): string {
		return sanitize_textarea_field( (string) $value );
	}
}
