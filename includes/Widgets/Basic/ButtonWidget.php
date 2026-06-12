<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class ButtonWidget extends Widget {

	public function get_name(): string    { return 'button'; }
	public function get_title(): string   { return __( 'Button', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-button'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'button', 'link', 'cta' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_button', [
			'label' => __( 'Button', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'button_type', [
			'label'   => __( 'Type', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'' => 'Default', 'info' => 'Info', 'success' => 'Success',
				'warning' => 'Warning', 'danger' => 'Danger',
			],
			'default' => '',
		] );

		$this->add_control( 'text', [
			'label'   => __( 'Text', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Click Here', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'link', [
			'label'   => __( 'Link', 'gohigh-page-builder' ),
			'type'    => ControlsManager::URL,
			'default' => [ 'url' => '#' ],
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_responsive_control( 'align', [
			'label'     => __( 'Alignment', 'gohigh-page-builder' ),
			'type'      => ControlsManager::CHOOSE,
			'options'   => [
				'left'    => [ 'title' => 'Left', 'icon' => 'dashicons dashicons-editor-alignleft' ],
				'center'  => [ 'title' => 'Center', 'icon' => 'dashicons dashicons-editor-aligncenter' ],
				'right'   => [ 'title' => 'Right', 'icon' => 'dashicons dashicons-editor-alignright' ],
				'justify' => [ 'title' => 'Justify', 'icon' => 'dashicons dashicons-editor-justify' ],
			],
			'selectors' => [ '{{WRAPPER}}' => 'text-align: {{VALUE}};' ],
		] );

		$this->add_control( 'icon', [
			'label' => __( 'Icon', 'gohigh-page-builder' ),
			'type'  => ControlsManager::ICON,
		] );

		$this->add_control( 'icon_align', [
			'label'     => __( 'Icon Position', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SELECT,
			'options'   => [ 'left' => 'Before', 'right' => 'After' ],
			'default'   => 'left',
			'condition' => [ 'icon[value]!' => '' ],
		] );

		$this->end_controls_section();

		// Style section.
		$this->start_controls_section( 'section_style', [
			'label' => __( 'Button', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_group_control( 'typography', [
			'name'     => 'typography',
			'selector' => '{{WRAPPER}} a.ghpb-button',
		] );

		$this->add_control( 'button_text_color', [
			'label'     => __( 'Text Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} a.ghpb-button' => 'fill: {{VALUE}}; color: {{VALUE}};' ],
		] );

		$this->add_control( 'background_color', [
			'label'     => __( 'Background Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} a.ghpb-button' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'border_color', [
			'label'     => __( 'Border Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} a.ghpb-button' => 'border-color: {{VALUE}};' ],
		] );

		$this->add_control( 'border_width', [
			'label'      => __( 'Border Width', 'gohigh-page-builder' ),
			'type'       => ControlsManager::DIMENSIONS,
			'size_units' => [ 'px' ],
			'selectors'  => [ '{{WRAPPER}} a.ghpb-button' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_control( 'border_radius', [
			'label'      => __( 'Border Radius', 'gohigh-page-builder' ),
			'type'       => ControlsManager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [ '{{WRAPPER}} a.ghpb-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'padding', [
			'label'      => __( 'Padding', 'gohigh-page-builder' ),
			'type'       => ControlsManager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'selectors'  => [ '{{WRAPPER}} a.ghpb-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$text     = $settings['text'] ?? '';
		$link     = $settings['link'] ?? [];
		$type     = $settings['button_type'] ? ' ghpb-button-' . esc_attr( $settings['button_type'] ) : '';
		$icon     = $settings['icon'] ?? [];
		$icon_pos = $settings['icon_align'] ?? 'left';

		$this->add_render_attribute( 'button', 'class', 'ghpb-button ghpb-button-default' . $type );
		$this->add_render_attribute( 'button', 'href', esc_url( $link['url'] ?? '#' ) );
		if ( ! empty( $link['is_external'] ) ) {
			$this->add_render_attribute( 'button', 'target', '_blank' );
			$this->add_render_attribute( 'button', 'rel', 'noopener' );
		}
		if ( ! empty( $link['nofollow'] ) ) {
			$this->add_render_attribute( 'button', 'rel', 'nofollow' );
		}

		$icon_html = '';
		if ( ! empty( $icon['value'] ) ) {
			$icon_html = '<span class="ghpb-button-icon ghpb-button-icon-' . esc_attr( $icon_pos ) . '"><i class="' . esc_attr( $icon['value'] ) . '"></i></span>';
		}
		?>
		<div class="ghpb-widget-button">
			<a <?php $this->print_render_attribute_string( 'button' ); ?>>
				<?php if ( $icon_html && 'left' === $icon_pos ) : ?>
					<?php echo $icon_html; // phpcs:ignore ?>
				<?php endif; ?>
				<span class="ghpb-button-text"><?php echo esc_html( $text ); ?></span>
				<?php if ( $icon_html && 'right' === $icon_pos ) : ?>
					<?php echo $icon_html; // phpcs:ignore ?>
				<?php endif; ?>
			</a>
		</div>
		<?php
	}
}
