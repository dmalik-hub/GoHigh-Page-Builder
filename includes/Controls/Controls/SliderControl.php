<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class SliderControl extends Control {
	public function get_type(): string { return 'slider'; }
	public function get_default_value(): mixed {
		return [ 'size' => '', 'unit' => 'px' ];
	}
	public function get_default_settings(): array {
		return [
			'size_units' => [ 'px', '%', 'em', 'rem' ],
			'range'      => [
				'px'  => [ 'min' => 0, 'max' => 1000, 'step' => 1 ],
				'%'   => [ 'min' => 0, 'max' => 100,  'step' => 1 ],
				'em'  => [ 'min' => 0, 'max' => 100,  'step' => 0.1 ],
				'rem' => [ 'min' => 0, 'max' => 100,  'step' => 0.1 ],
			],
		];
	}
	public function sanitize( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return $this->get_default_value();
		}
		return [
			'size' => is_numeric( $value['size'] ?? '' ) ? (float) $value['size'] : '',
			'unit' => in_array( $value['unit'] ?? 'px', [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ], true )
				? $value['unit']
				: 'px',
		];
	}
}
