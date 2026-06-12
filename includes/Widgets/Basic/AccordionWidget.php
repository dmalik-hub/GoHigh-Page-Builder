<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class AccordionWidget extends Widget {

	public function get_name(): string      { return 'accordion'; }
	public function get_title(): string     { return __( 'Accordion', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-arrow-down-alt2'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'accordion', 'faq', 'toggle', 'collapse', 'expand' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_accordion', [
			'label' => __( 'Accordion', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'items', [
			'type'    => ControlsManager::REPEATER,
			'label'   => __( 'Items', 'gohigh-page-builder' ),
			'fields'  => [
				[ 'name' => 'title',   'type' => 'text',     'label' => __( 'Title', 'gohigh-page-builder' ),   'default' => __( 'Accordion Item', 'gohigh-page-builder' ) ],
				[ 'name' => 'content', 'type' => 'textarea', 'label' => __( 'Content', 'gohigh-page-builder' ), 'default' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'gohigh-page-builder' ) ],
			],
			'default' => [
				[ 'title' => __( 'Accordion Item #1', 'gohigh-page-builder' ), 'content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis.', 'gohigh-page-builder' ) ],
				[ 'title' => __( 'Accordion Item #2', 'gohigh-page-builder' ), 'content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis.', 'gohigh-page-builder' ) ],
				[ 'title' => __( 'Accordion Item #3', 'gohigh-page-builder' ), 'content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis.', 'gohigh-page-builder' ) ],
			],
		] );

		$this->add_control( 'active_item', [
			'label'   => __( 'Default Active Item', 'gohigh-page-builder' ),
			'type'    => ControlsManager::NUMBER,
			'default' => 1,
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_title_style', [
			'label' => __( 'Title', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Title Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-accordion-header' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'title_background', [
			'label'     => __( 'Title Background', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-accordion-header' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'content_color', [
			'label'     => __( 'Content Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-accordion-content' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'border_color', [
			'label'     => __( 'Border Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-accordion-item' => 'border-color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings    = $this->get_settings_for_display();
		$items       = $settings['items'] ?? [];
		$active_item = intval( $settings['active_item'] ?? 1 );

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-accordion' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="ghpb-accordion" role="list">
				<?php foreach ( $items as $index => $item ) :
					$item_number = $index + 1;
					$is_active   = ( $item_number === $active_item );
					$item_class  = 'ghpb-accordion-item' . ( $is_active ? ' ghpb-accordion-item-active' : '' );
					?>
					<div class="<?php echo esc_attr( $item_class ); ?>" role="listitem">
						<div class="ghpb-accordion-header" role="button" aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>" tabindex="0">
							<span class="ghpb-accordion-title"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
							<span class="ghpb-accordion-icon">
								<span class="dashicons dashicons-arrow-down-alt2"></span>
							</span>
						</div>
						<div class="ghpb-accordion-content" <?php echo $is_active ? '' : 'style="display:none;"'; ?>>
							<div class="ghpb-accordion-content-inner">
								<?php echo wp_kses_post( $item['content'] ?? '' ); ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
