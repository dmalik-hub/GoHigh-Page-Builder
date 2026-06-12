<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class IconWidget extends Widget {

	public function get_name(): string    { return 'icon'; }
	public function get_title(): string   { return __( 'Icon', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-star-filled'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'icon', 'symbol', 'svg' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_icon', [
			'label' => __( 'Icon', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'selected_icon', [
			'label'   => __( 'Icon', 'gohigh-page-builder' ),
			'type'    => ControlsManager::ICON,
			'default' => [ 'library' => 'solid', 'value' => 'dashicons dashicons-star-filled' ],
		] );

		$this->add_responsive_control( 'align', [
			'label'     => __( 'Alignment', 'gohigh-page-builder' ),
			'type'      => ControlsManager::CHOOSE,
			'options'   => [
				'left'   => [ 'title' => 'Left', 'icon' => 'dashicons dashicons-editor-alignleft' ],
				'center' => [ 'title' => 'Center', 'icon' => 'dashicons dashicons-editor-aligncenter' ],
				'right'  => [ 'title' => 'Right', 'icon' => 'dashicons dashicons-editor-alignright' ],
			],
			'default'   => 'center',
			'selectors' => [ '{{WRAPPER}} .ghpb-icon' => 'text-align: {{VALUE}};' ],
		] );

		$this->add_control( 'link', [
			'label'   => __( 'Link', 'gohigh-page-builder' ),
			'type'    => ControlsManager::URL,
			'dynamic' => [ 'active' => true ],
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_icon', [
			'label' => __( 'Icon', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'size', [
			'label'     => __( 'Size', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'range'     => [ 'px' => [ 'min' => 6, 'max' => 300 ] ],
			'selectors' => [ '{{WRAPPER}} .ghpb-icon i' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'primary_color', [
			'label'     => __( 'Primary Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-icon i' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'rotate', [
			'label'     => __( 'Rotate', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 360 ] ],
			'selectors' => [ '{{WRAPPER}} .ghpb-icon i' => 'transform: rotate({{SIZE}}deg);' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$icon     = $settings['selected_icon'] ?? [];
		$link     = $settings['link'] ?? [];

		$icon_class = ! empty( $icon['value'] ) ? esc_attr( $icon['value'] ) : 'dashicons dashicons-star-filled';
		?>
		<div class="ghpb-icon">
			<?php if ( ! empty( $link['url'] ) ) : ?>
				<a href="<?php echo esc_url( $link['url'] ); ?>"
				   <?php echo ! empty( $link['is_external'] ) ? 'target="_blank" rel="noopener"' : ''; ?>>
					<i class="<?php echo $icon_class; // phpcs:ignore ?>"></i>
				</a>
			<?php else : ?>
				<i class="<?php echo $icon_class; // phpcs:ignore ?>"></i>
			<?php endif; ?>
		</div>
		<?php
	}
}
