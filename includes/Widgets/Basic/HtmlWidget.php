<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class HtmlWidget extends Widget {

	public function get_name(): string    { return 'html'; }
	public function get_title(): string   { return __( 'HTML', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-editor-code'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'html', 'code', 'custom', 'embed' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_title', [
			'label' => __( 'HTML Code', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'html', [
			'label'       => __( 'HTML', 'gohigh-page-builder' ),
			'type'        => ControlsManager::CODE,
			'language'    => 'html',
			'rows'        => 20,
			'default'     => '',
			'placeholder' => __( 'Enter your HTML code here', 'gohigh-page-builder' ),
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$html     = $settings['html'] ?? '';
		if ( $html ) {
			// Allow any HTML; this is a code widget intentionally.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="ghpb-html">' . $html . '</div>';
		}
	}
}
