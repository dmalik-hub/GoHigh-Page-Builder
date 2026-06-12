<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class ProgressBarWidget extends Widget {

	public function get_name(): string      { return 'progress-bar'; }
	public function get_title(): string     { return __( 'Progress Bar', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-minus'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'progress', 'bar', 'skill', 'percent', 'animated' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_progress', [
			'label' => __( 'Progress Bar', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'title', [
			'label'   => __( 'Title', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Design', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'percent', [
			'label'   => __( 'Percentage', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SLIDER,
			'default' => [ 'size' => 75, 'unit' => '%' ],
			'range'   => [ '%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ],
		] );

		$this->add_control( 'display_percentage', [
			'label'   => __( 'Display Percentage', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'bar_color', [
			'label'   => __( 'Bar Color', 'gohigh-page-builder' ),
			'type'    => ControlsManager::COLOR,
			'default' => '#6c63ff',
		] );

		$this->add_control( 'bar_bg_color', [
			'label'   => __( 'Bar Background Color', 'gohigh-page-builder' ),
			'type'    => ControlsManager::COLOR,
			'default' => '#e5e5e5',
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Title Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'default'   => '#333333',
			'selectors' => [ '{{WRAPPER}} .ghpb-progress-title' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'stripe', [
			'label'   => __( 'Animated Stripes', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
			'default' => '',
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings           = $this->get_settings_for_display();
		$title              = $settings['title'] ?? '';
		$percent_data       = $settings['percent'] ?? [];
		$percent            = isset( $percent_data['size'] ) ? intval( $percent_data['size'] ) : 75;
		$percent            = max( 0, min( 100, $percent ) );
		$display_percentage = ( 'yes' === ( $settings['display_percentage'] ?? 'yes' ) );
		$bar_color          = $settings['bar_color'] ?? '#6c63ff';
		$bar_bg_color       = $settings['bar_bg_color'] ?? '#e5e5e5';
		$stripe             = ( 'yes' === ( $settings['stripe'] ?? '' ) );

		$bar_class = 'ghpb-progress-bar';
		if ( $stripe ) {
			$bar_class .= ' ghpb-progress-bar-striped ghpb-progress-bar-animated';
		}

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-progress-bar' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="ghpb-progress">
				<?php if ( $title || $display_percentage ) : ?>
					<div class="ghpb-progress-label-row">
						<?php if ( $title ) : ?>
							<span class="ghpb-progress-title"><?php echo esc_html( $title ); ?></span>
						<?php endif; ?>
						<?php if ( $display_percentage ) : ?>
							<span class="ghpb-progress-percentage"><?php echo esc_html( $percent ); ?>%</span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div
					class="ghpb-progress-bar-wrap"
					style="background-color: <?php echo esc_attr( $bar_bg_color ); ?>;"
					role="progressbar"
					aria-valuenow="<?php echo esc_attr( $percent ); ?>"
					aria-valuemin="0"
					aria-valuemax="100"
				>
					<div
						class="<?php echo esc_attr( $bar_class ); ?>"
						style="width: <?php echo esc_attr( $percent ); ?>%; background-color: <?php echo esc_attr( $bar_color ); ?>;"
						data-width="<?php echo esc_attr( $percent ); ?>"
					></div>
				</div>
			</div>
		</div>
		<?php
	}
}
