<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class BackgroundControl extends Control {
	public function get_type(): string { return 'background'; }
	public function get_default_value(): mixed {
		return [
			'background' => 'classic',
			'color'      => '',
			'image'      => [ 'url' => '', 'id' => 0 ],
			'position'   => 'center center',
			'attachment' => 'scroll',
			'repeat'     => 'no-repeat',
			'size'       => 'cover',
		];
	}
	public function get_default_settings(): array {
		return [
			'types' => [ 'classic', 'gradient', 'image', 'video' ],
		];
	}
	public function sanitize( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return $this->get_default_value();
		}
		return [
			'background' => sanitize_text_field( $value['background'] ?? 'classic' ),
			'color'      => sanitize_text_field( $value['color'] ?? '' ),
			'image'      => [
				'url' => esc_url_raw( $value['image']['url'] ?? '' ),
				'id'  => absint( $value['image']['id'] ?? 0 ),
			],
			'position'   => sanitize_text_field( $value['position'] ?? 'center center' ),
			'attachment' => sanitize_text_field( $value['attachment'] ?? 'scroll' ),
			'repeat'     => sanitize_text_field( $value['repeat'] ?? 'no-repeat' ),
			'size'       => sanitize_text_field( $value['size'] ?? 'cover' ),
		];
	}
}
