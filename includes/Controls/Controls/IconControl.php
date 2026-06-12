<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class IconControl extends Control {
	public function get_type(): string { return 'icon'; }
	public function get_default_value(): mixed {
		return [ 'library' => 'solid', 'value' => '' ];
	}
	public function get_default_settings(): array {
		return [ 'include' => [], 'exclude' => [] ];
	}
	public function sanitize( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return $this->get_default_value();
		}
		return [
			'library' => sanitize_text_field( $value['library'] ?? 'solid' ),
			'value'   => sanitize_html_class( $value['value'] ?? '' ),
		];
	}
}
