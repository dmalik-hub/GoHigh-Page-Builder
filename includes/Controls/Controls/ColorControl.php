<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class ColorControl extends Control {
	public function get_type(): string { return 'color'; }
	public function get_default_value(): mixed { return ''; }
	public function get_default_settings(): array {
		return [ 'alpha' => true ];
	}
	public function sanitize( mixed $value ): string {
		// Allow hex, rgba(), hsla() and empty.
		$value = (string) $value;
		if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $value ) ) {
			return $value;
		}
		if ( preg_match( '/^rgba?\s*\([\d\s,.%]+\)$/', $value ) ) {
			return $value;
		}
		if ( preg_match( '/^hsla?\s*\([\d\s,.%]+\)$/', $value ) ) {
			return $value;
		}
		return '';
	}
}
