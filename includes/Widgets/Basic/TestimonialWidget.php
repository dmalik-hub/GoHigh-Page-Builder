<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class TestimonialWidget extends Widget {

	public function get_name(): string      { return 'testimonial'; }
	public function get_title(): string     { return __( 'Testimonial', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-format-quote'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'testimonial', 'quote', 'review', 'customer', 'feedback' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_testimonial', [
			'label' => __( 'Testimonial', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'content', [
			'label'   => __( 'Content', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXTAREA,
			'default' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'name', [
			'label'   => __( 'Name', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'John Doe', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'title', [
			'label'   => __( 'Title', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'CEO, Company', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'image', [
			'label'   => __( 'Image', 'gohigh-page-builder' ),
			'type'    => ControlsManager::MEDIA,
			'default' => [ 'url' => '' ],
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_testimonial_style', [
			'label' => __( 'Testimonial', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'content_color', [
			'label'     => __( 'Content Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-testimonial-content' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'name_color', [
			'label'     => __( 'Name Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-testimonial-name' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Title Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-testimonial-meta-title' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'content_size', [
			'label'     => __( 'Content Font Size', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 16, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-testimonial-content' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$content  = $settings['content'] ?? '';
		$name     = $settings['name'] ?? '';
		$title    = $settings['title'] ?? '';
		$image    = $settings['image'] ?? [];
		$img_url  = $image['url'] ?? '';

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-testimonial' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="ghpb-testimonial">
				<?php if ( $content ) : ?>
					<div class="ghpb-testimonial-content">
						<span class="ghpb-testimonial-quote-icon dashicons dashicons-format-quote"></span>
						<?php echo wp_kses_post( $content ); ?>
					</div>
				<?php endif; ?>
				<div class="ghpb-testimonial-meta">
					<?php if ( $img_url ) : ?>
						<div class="ghpb-testimonial-avatar">
							<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
						</div>
					<?php endif; ?>
					<div class="ghpb-testimonial-meta-info">
						<?php if ( $name ) : ?>
							<span class="ghpb-testimonial-name"><?php echo esc_html( $name ); ?></span>
						<?php endif; ?>
						<?php if ( $title ) : ?>
							<span class="ghpb-testimonial-meta-title"><?php echo esc_html( $title ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
