<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class DimensionsControl extends Control {
	public function get_type(): string { return 'dimensions'; }
	public function get_default_value(): mixed {
		return [ 'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px', 'isLinked' => true ];
	}
	public function get_default_settings(): array {
		return [
			'size_units'  => [ 'px', 'em', '%' ],
			'allowed_dimensions' => [ 'top', 'right', 'bottom', 'left' ],
			'placeholder' => [ 'top' => '', 'right' => '', 'bottom' => '', 'left' => '' ],
		];
	}
	public function sanitize( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return $this->get_default_value();
		}
		$sanitize_dim = fn( $v ) => is_numeric( $v ) ? (float) $v : ( $v === '' ? '' : (float) $v );
		return [
			'top'      => $sanitize_dim( $value['top'] ?? '' ),
			'right'    => $sanitize_dim( $value['right'] ?? '' ),
			'bottom'   => $sanitize_dim( $value['bottom'] ?? '' ),
			'left'     => $sanitize_dim( $value['left'] ?? '' ),
			'unit'     => sanitize_text_field( $value['unit'] ?? 'px' ),
			'isLinked' => (bool) ( $value['isLinked'] ?? true ),
		];
	}
}
