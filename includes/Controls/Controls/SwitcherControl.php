<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class SwitcherControl extends Control {
	public function get_type(): string { return 'switcher'; }
	public function get_default_value(): mixed { return ''; }
	public function get_default_settings(): array {
		return [
			'label_on'  => __( 'Yes', 'gohigh-page-builder' ),
			'label_off' => __( 'No', 'gohigh-page-builder' ),
			'return_value' => 'yes',
		];
	}
	public function sanitize( mixed $value ): string {
		return 'yes' === $value ? 'yes' : '';
	}
}
