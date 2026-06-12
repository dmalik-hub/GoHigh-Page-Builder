<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class DividerWidget extends Widget {

	public function get_name(): string    { return 'divider'; }
	public function get_title(): string   { return __( 'Divider', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-minus'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'divider', 'line', 'rule', 'separator' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_divider', [
			'label' => __( 'Divider', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'style', [
			'label'     => __( 'Style', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SELECT,
			'options'   => [ 'solid' => 'Solid', 'double' => 'Double', 'dotted' => 'Dotted', 'dashed' => 'Dashed' ],
			'default'   => 'solid',
			'selectors' => [ '{{WRAPPER}} .ghpb-divider-separator' => 'border-top-style: {{VALUE}};' ],
		] );

		$this->add_control( 'color', [
			'label'     => __( 'Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'default'   => '#a4afb7',
			'selectors' => [ '{{WRAPPER}} .ghpb-divider-separator' => 'border-color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'width', [
			'label'      => __( 'Width', 'gohigh-page-builder' ),
			'type'       => ControlsManager::SLIDER,
			'size_units' => [ '%', 'px', 'vw' ],
			'range'      => [ '%' => [ 'min' => 1, 'max' => 100 ], 'px' => [ 'min' => 1, 'max' => 1000 ] ],
			'default'    => [ 'size' => 100, 'unit' => '%' ],
			'selectors'  => [ '{{WRAPPER}} .ghpb-divider-separator' => 'width: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'weight', [
			'label'     => __( 'Weight', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'range'     => [ 'px' => [ 'min' => 1, 'max' => 10, 'step' => 1 ] ],
			'default'   => [ 'size' => 1, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-divider-separator' => 'border-top-width: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'gap', [
			'label'     => __( 'Gap', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
			'default'   => [ 'size' => 15, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-divider' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'align', [
			'label'     => __( 'Alignment', 'gohigh-page-builder' ),
			'type'      => ControlsManager::CHOOSE,
			'options'   => [
				'left'   => [ 'title' => 'Left', 'icon' => 'dashicons dashicons-editor-alignleft' ],
				'center' => [ 'title' => 'Center', 'icon' => 'dashicons dashicons-editor-aligncenter' ],
				'right'  => [ 'title' => 'Right', 'icon' => 'dashicons dashicons-editor-alignright' ],
			],
			'default'   => 'left',
			'selectors' => [ '{{WRAPPER}} .ghpb-divider' => 'text-align: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		?>
		<div class="ghpb-divider">
			<div class="ghpb-divider-separator"></div>
		</div>
		<?php
	}
}
