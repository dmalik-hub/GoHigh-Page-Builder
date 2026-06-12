<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class CodeControl extends Control {
	public function get_type(): string { return 'code'; }
	public function get_default_value(): mixed { return ''; }
	public function get_default_settings(): array {
		return [ 'language' => 'css', 'rows' => 10 ];
	}
	public function sanitize( mixed $value ): string {
		// Raw code — strip PHP tags only.
		return wp_strip_all_tags( (string) $value );
	}
}
