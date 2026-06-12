<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class NumberControl extends Control {
	public function get_type(): string { return 'number'; }
	public function get_default_value(): mixed { return ''; }
	public function get_default_settings(): array {
		return [ 'min' => null, 'max' => null, 'step' => 1, 'placeholder' => '' ];
	}
	public function sanitize( mixed $value ): float|int|string {
		return is_numeric( $value ) ? $value + 0 : '';
	}
}
