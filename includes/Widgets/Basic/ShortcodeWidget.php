<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class ShortcodeWidget extends Widget {

	public function get_name(): string    { return 'shortcode'; }
	public function get_title(): string   { return __( 'Shortcode', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-shortcode'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'shortcode', 'code', 'wordpress' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_shortcode', [
			'label' => __( 'Shortcode', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'shortcode', [
			'label'       => __( 'Enter your shortcode', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXTAREA,
			'placeholder' => '[shortcode]',
			'default'     => '',
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings   = $this->get_settings_for_display();
		$shortcode  = trim( $settings['shortcode'] ?? '' );

		if ( ! $shortcode ) {
			echo '<div class="ghpb-shortcode ghpb-shortcode-empty"><p>' . esc_html__( 'Enter a shortcode.', 'gohigh-page-builder' ) . '</p></div>';
			return;
		}
		echo '<div class="ghpb-shortcode">' . do_shortcode( $shortcode ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
