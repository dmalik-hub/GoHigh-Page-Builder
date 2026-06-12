<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class TypographyControl extends Control {
	public function get_type(): string { return 'typography'; }
	public function get_default_value(): mixed {
		return [
			'font_family'      => '',
			'font_size'        => [ 'size' => '', 'unit' => 'px' ],
			'font_weight'      => '',
			'text_transform'   => '',
			'font_style'       => '',
			'text_decoration'  => '',
			'line_height'      => [ 'size' => '', 'unit' => 'em' ],
			'letter_spacing'   => [ 'size' => '', 'unit' => 'px' ],
			'word_spacing'     => [ 'size' => '', 'unit' => 'em' ],
		];
	}
	public function get_default_settings(): array {
		return [
			'selector' => '',
			// Typography group embeds child controls; config sent to JS.
		];
	}
	public function sanitize( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return $this->get_default_value();
		}
		return [
			'font_family'     => sanitize_text_field( $value['font_family'] ?? '' ),
			'font_size'       => [ 'size' => (float) ( $value['font_size']['size'] ?? 0 ), 'unit' => sanitize_text_field( $value['font_size']['unit'] ?? 'px' ) ],
			'font_weight'     => sanitize_text_field( $value['font_weight'] ?? '' ),
			'text_transform'  => sanitize_text_field( $value['text_transform'] ?? '' ),
			'font_style'      => sanitize_text_field( $value['font_style'] ?? '' ),
			'text_decoration' => sanitize_text_field( $value['text_decoration'] ?? '' ),
			'line_height'     => [ 'size' => (float) ( $value['line_height']['size'] ?? 0 ), 'unit' => sanitize_text_field( $value['line_height']['unit'] ?? 'em' ) ],
			'letter_spacing'  => [ 'size' => (float) ( $value['letter_spacing']['size'] ?? 0 ), 'unit' => sanitize_text_field( $value['letter_spacing']['unit'] ?? 'px' ) ],
			'word_spacing'    => [ 'size' => (float) ( $value['word_spacing']['size'] ?? 0 ), 'unit' => sanitize_text_field( $value['word_spacing']['unit'] ?? 'em' ) ],
		];
	}
}
