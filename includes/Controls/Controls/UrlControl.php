<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class UrlControl extends Control {
	public function get_type(): string { return 'url'; }
	public function get_default_value(): mixed {
		return [ 'url' => '', 'is_external' => '', 'nofollow' => '', 'custom_attributes' => '' ];
	}
	public function get_default_settings(): array {
		return [ 'placeholder' => '', 'show_external' => true ];
	}
	public function sanitize( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return $this->get_default_value();
		}
		return [
			'url'               => esc_url_raw( $value['url'] ?? '' ),
			'is_external'       => ! empty( $value['is_external'] ) ? 'on' : '',
			'nofollow'          => ! empty( $value['nofollow'] ) ? 'on' : '',
			'custom_attributes' => sanitize_text_field( $value['custom_attributes'] ?? '' ),
		];
	}
}
