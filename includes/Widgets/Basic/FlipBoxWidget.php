<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class FlipBoxWidget extends Widget {

	public function get_name(): string      { return 'flip-box'; }
	public function get_title(): string     { return __( 'Flip Box', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-image-rotate'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'flip', 'box', 'card', 'hover', 'effect', '3d' ]; }

	protected function _register_controls(): void {

		// ── Front Side ────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_flip_front', [
			'label' => __( 'Front', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'front_title', [
			'label'   => __( 'Title', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Front Title', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'front_description', [
			'label'   => __( 'Description', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXTAREA,
			'default' => __( 'Add some description here.', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'front_image', [
			'label'   => __( 'Image', 'gohigh-page-builder' ),
			'type'    => ControlsManager::MEDIA,
			'default' => [ 'url' => '' ],
		] );

		$this->add_control( 'front_bg_color', [
			'label'   => __( 'Background Color', 'gohigh-page-builder' ),
			'type'    => ControlsManager::COLOR,
			'default' => '#6c63ff',
		] );

		$this->add_control( 'front_color', [
			'label'   => __( 'Text Color', 'gohigh-page-builder' ),
			'type'    => ControlsManager::COLOR,
			'default' => '#ffffff',
		] );

		$this->end_controls_section();

		// ── Back Side ─────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_flip_back', [
			'label' => __( 'Back', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'back_title', [
			'label'   => __( 'Title', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Back Title', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'back_description', [
			'label'   => __( 'Description', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXTAREA,
			'default' => __( 'Add some back description here.', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'back_button_text', [
			'label'   => __( 'Button Text', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Click Here', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'back_button_link', [
			'label'   => __( 'Button Link', 'gohigh-page-builder' ),
			'type'    => ControlsManager::URL,
			'default' => [ 'url' => '#' ],
		] );

		$this->add_control( 'back_bg_color', [
			'label'   => __( 'Background Color', 'gohigh-page-builder' ),
			'type'    => ControlsManager::COLOR,
			'default' => '#574fd6',
		] );

		$this->add_control( 'back_color', [
			'label'   => __( 'Text Color', 'gohigh-page-builder' ),
			'type'    => ControlsManager::COLOR,
			'default' => '#ffffff',
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_flip_style', [
			'label' => __( 'Settings', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'flip_direction', [
			'label'   => __( 'Flip Direction', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'right' => __( 'Right', 'gohigh-page-builder' ),
				'left'  => __( 'Left', 'gohigh-page-builder' ),
				'up'    => __( 'Up', 'gohigh-page-builder' ),
				'down'  => __( 'Down', 'gohigh-page-builder' ),
			],
			'default' => 'right',
		] );

		$this->add_responsive_control( 'height', [
			'label'     => __( 'Height', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 300, 'unit' => 'px' ],
			'range'     => [ 'px' => [ 'min' => 200, 'max' => 800, 'step' => 10 ] ],
			'selectors' => [ '{{WRAPPER}} .ghpb-flip-box' => 'height: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'border_radius', [
			'label'     => __( 'Border Radius', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 8, 'unit' => 'px' ],
			'selectors' => [
				'{{WRAPPER}} .ghpb-flip-front' => 'border-radius: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .ghpb-flip-back'  => 'border-radius: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings        = $this->get_settings_for_display();

		$front_title       = $settings['front_title'] ?? '';
		$front_description = $settings['front_description'] ?? '';
		$front_image       = $settings['front_image'] ?? [];
		$front_img_url     = $front_image['url'] ?? '';
		$front_bg_color    = $settings['front_bg_color'] ?? '#6c63ff';
		$front_color       = $settings['front_color'] ?? '#ffffff';

		$back_title        = $settings['back_title'] ?? '';
		$back_description  = $settings['back_description'] ?? '';
		$back_button_text  = $settings['back_button_text'] ?? '';
		$back_button_link  = $settings['back_button_link'] ?? [];
		$back_button_url   = $back_button_link['url'] ?? '#';
		$back_bg_color     = $settings['back_bg_color'] ?? '#574fd6';
		$back_color        = $settings['back_color'] ?? '#ffffff';

		$flip_direction    = $settings['flip_direction'] ?? 'right';

		$allowed_directions = [ 'right', 'left', 'up', 'down' ];
		if ( ! in_array( $flip_direction, $allowed_directions, true ) ) {
			$flip_direction = 'right';
		}

		$height_data = $settings['height'] ?? [];
		$height      = isset( $height_data['size'] ) ? intval( $height_data['size'] ) : 300;

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-flip-box' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div
				class="ghpb-flip-box ghpb-flip-direction-<?php echo esc_attr( $flip_direction ); ?>"
				style="height: <?php echo esc_attr( $height ); ?>px;"
			>
				<div class="ghpb-flip-inner">

					<!-- Front -->
					<div
						class="ghpb-flip-front"
						style="background-color: <?php echo esc_attr( $front_bg_color ); ?>; color: <?php echo esc_attr( $front_color ); ?>;"
					>
						<?php if ( $front_img_url ) : ?>
							<div class="ghpb-flip-front-image">
								<img src="<?php echo esc_url( $front_img_url ); ?>" alt="<?php echo esc_attr( $front_title ); ?>" loading="lazy">
							</div>
						<?php endif; ?>
						<div class="ghpb-flip-front-content">
							<?php if ( $front_title ) : ?>
								<h3 class="ghpb-flip-title"><?php echo esc_html( $front_title ); ?></h3>
							<?php endif; ?>
							<?php if ( $front_description ) : ?>
								<div class="ghpb-flip-description"><?php echo wp_kses_post( $front_description ); ?></div>
							<?php endif; ?>
						</div>
					</div>

					<!-- Back -->
					<div
						class="ghpb-flip-back"
						style="background-color: <?php echo esc_attr( $back_bg_color ); ?>; color: <?php echo esc_attr( $back_color ); ?>;"
					>
						<div class="ghpb-flip-back-content">
							<?php if ( $back_title ) : ?>
								<h3 class="ghpb-flip-title"><?php echo esc_html( $back_title ); ?></h3>
							<?php endif; ?>
							<?php if ( $back_description ) : ?>
								<div class="ghpb-flip-description"><?php echo wp_kses_post( $back_description ); ?></div>
							<?php endif; ?>
							<?php if ( $back_button_text ) : ?>
								<div class="ghpb-flip-button-wrapper">
									<a
										href="<?php echo esc_url( $back_button_url ); ?>"
										class="ghpb-flip-button"
										<?php if ( ! empty( $back_button_link['is_external'] ) ) : ?>
											target="_blank" rel="noopener noreferrer"
										<?php endif; ?>
									>
										<?php echo esc_html( $back_button_text ); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</div>

				</div><!-- .ghpb-flip-inner -->
			</div><!-- .ghpb-flip-box -->
		</div>
		<?php
	}
}
