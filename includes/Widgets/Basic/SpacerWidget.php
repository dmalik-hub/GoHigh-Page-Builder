<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class SpacerWidget extends Widget {

	public function get_name(): string    { return 'spacer'; }
	public function get_title(): string   { return __( 'Spacer', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-sort'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'spacer', 'gap', 'space' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_spacer', [
			'label' => __( 'Spacer', 'gohigh-page-builder' ),
		] );

		$this->add_responsive_control( 'space', [
			'label'     => __( 'Space', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 400 ] ],
			'default'   => [ 'size' => 50, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-spacer-inner' => 'height: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		?>
		<div class="ghpb-spacer">
			<div class="ghpb-spacer-inner"></div>
		</div>
		<?php
	}
}
