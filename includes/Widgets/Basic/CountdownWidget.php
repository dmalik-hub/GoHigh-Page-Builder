<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class CountdownWidget extends Widget {

	public function get_name(): string      { return 'countdown'; }
	public function get_title(): string     { return __( 'Countdown', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-clock'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'countdown', 'timer', 'count', 'clock', 'deadline' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_countdown', [
			'label' => __( 'Countdown', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'due_date', [
			'label'       => __( 'Due Date', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXT,
			'default'     => '',
			'description' => __( 'Enter date in format: YYYY-MM-DD HH:MM:SS', 'gohigh-page-builder' ),
			'placeholder' => '2026-12-31 23:59:59',
		] );

		$this->add_control( 'label_days', [
			'label'   => __( 'Days Label', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Days', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'label_hours', [
			'label'   => __( 'Hours Label', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Hours', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'label_minutes', [
			'label'   => __( 'Minutes Label', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Minutes', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'label_seconds', [
			'label'   => __( 'Seconds Label', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Seconds', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'show_labels', [
			'label'   => __( 'Show Labels', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
			'default' => 'yes',
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_countdown_style', [
			'label' => __( 'Countdown', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'digit_color', [
			'label'     => __( 'Digit Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-countdown-digit' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'label_color', [
			'label'     => __( 'Label Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-countdown-label' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'digit_size', [
			'label'     => __( 'Digit Size', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 48, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-countdown-digit' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'box_background_color', [
			'label'     => __( 'Box Background', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-countdown-box' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'separator_color', [
			'label'     => __( 'Separator Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-countdown-separator' => 'color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings     = $this->get_settings_for_display();
		$due_date     = $settings['due_date'] ?? '';
		$show_labels  = ( 'yes' === ( $settings['show_labels'] ?? 'yes' ) );
		$label_days   = $settings['label_days'] ?? __( 'Days', 'gohigh-page-builder' );
		$label_hours  = $settings['label_hours'] ?? __( 'Hours', 'gohigh-page-builder' );
		$label_mins   = $settings['label_minutes'] ?? __( 'Minutes', 'gohigh-page-builder' );
		$label_secs   = $settings['label_seconds'] ?? __( 'Seconds', 'gohigh-page-builder' );

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-countdown' );
		$this->add_render_attribute( 'countdown', 'class', 'ghpb-countdown' );
		if ( $due_date ) {
			$this->add_render_attribute( 'countdown', 'data-due', esc_attr( $due_date ) );
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div <?php $this->print_render_attribute_string( 'countdown' ); ?>>
				<div class="ghpb-countdown-box" data-segment="days">
					<span class="ghpb-countdown-digit ghpb-countdown-days">00</span>
					<?php if ( $show_labels ) : ?>
						<span class="ghpb-countdown-label"><?php echo esc_html( $label_days ); ?></span>
					<?php endif; ?>
				</div>
				<div class="ghpb-countdown-separator">:</div>
				<div class="ghpb-countdown-box" data-segment="hours">
					<span class="ghpb-countdown-digit ghpb-countdown-hours">00</span>
					<?php if ( $show_labels ) : ?>
						<span class="ghpb-countdown-label"><?php echo esc_html( $label_hours ); ?></span>
					<?php endif; ?>
				</div>
				<div class="ghpb-countdown-separator">:</div>
				<div class="ghpb-countdown-box" data-segment="minutes">
					<span class="ghpb-countdown-digit ghpb-countdown-minutes">00</span>
					<?php if ( $show_labels ) : ?>
						<span class="ghpb-countdown-label"><?php echo esc_html( $label_mins ); ?></span>
					<?php endif; ?>
				</div>
				<div class="ghpb-countdown-separator">:</div>
				<div class="ghpb-countdown-box" data-segment="seconds">
					<span class="ghpb-countdown-digit ghpb-countdown-seconds">00</span>
					<?php if ( $show_labels ) : ?>
						<span class="ghpb-countdown-label"><?php echo esc_html( $label_secs ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
