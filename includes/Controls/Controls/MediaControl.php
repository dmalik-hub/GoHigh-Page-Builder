<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class MediaControl extends Control {
	public function get_type(): string { return 'media'; }
	public function get_default_value(): mixed {
		return [ 'url' => '', 'id' => 0, 'alt' => '' ];
	}
	public function get_default_settings(): array {
		return [
			'media_type' => 'image', // 'image' | 'video' | 'audio' | 'application'
		];
	}
	public function sanitize( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return $this->get_default_value();
		}
		return [
			'url' => esc_url_raw( $value['url'] ?? '' ),
			'id'  => absint( $value['id'] ?? 0 ),
			'alt' => sanitize_text_field( $value['alt'] ?? '' ),
		];
	}
}
