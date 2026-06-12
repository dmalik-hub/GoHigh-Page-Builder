<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class CounterWidget extends Widget {

	public function get_name(): string      { return 'counter'; }
	public function get_title(): string     { return __( 'Counter', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-chart-bar'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'counter', 'number', 'stat', 'count', 'animated' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_counter', [
			'label' => __( 'Counter', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'starting_number', [
			'label'   => __( 'Starting Number', 'gohigh-page-builder' ),
			'type'    => ControlsManager::NUMBER,
			'default' => 0,
		] );

		$this->add_control( 'ending_number', [
			'label'   => __( 'Ending Number', 'gohigh-page-builder' ),
			'type'    => ControlsManager::NUMBER,
			'default' => 100,
		] );

		$this->add_control( 'prefix', [
			'label'   => __( 'Number Prefix', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => '',
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'suffix', [
			'label'   => __( 'Number Suffix', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => '',
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'title', [
			'label'   => __( 'Title', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Cool Number', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'duration', [
			'label'   => __( 'Animation Duration (ms)', 'gohigh-page-builder' ),
			'type'    => ControlsManager::NUMBER,
			'default' => 2000,
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_counter_style', [
			'label' => __( 'Counter', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'number_color', [
			'label'     => __( 'Number Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-counter-number-wrapper' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Title Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-counter-title' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'number_size', [
			'label'     => __( 'Number Size', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 48, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-counter-number-wrapper' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'title_size', [
			'label'     => __( 'Title Size', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 16, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-counter-title' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings        = $this->get_settings_for_display();
		$starting_number = intval( $settings['starting_number'] ?? 0 );
		$ending_number   = intval( $settings['ending_number'] ?? 100 );
		$prefix          = $settings['prefix'] ?? '';
		$suffix          = $settings['suffix'] ?? '';
		$title           = $settings['title'] ?? '';
		$duration        = intval( $settings['duration'] ?? 2000 );

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-counter' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="ghpb-counter">
				<div class="ghpb-counter-number-wrapper">
					<?php if ( $prefix ) : ?>
						<span class="ghpb-counter-prefix"><?php echo esc_html( $prefix ); ?></span>
					<?php endif; ?>
					<span
						class="ghpb-counter-number"
						data-start="<?php echo esc_attr( $starting_number ); ?>"
						data-end="<?php echo esc_attr( $ending_number ); ?>"
						data-duration="<?php echo esc_attr( $duration ); ?>"
					><?php echo esc_html( $starting_number ); ?></span>
					<?php if ( $suffix ) : ?>
						<span class="ghpb-counter-suffix"><?php echo esc_html( $suffix ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $title ) : ?>
					<div class="ghpb-counter-title"><?php echo esc_html( $title ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
