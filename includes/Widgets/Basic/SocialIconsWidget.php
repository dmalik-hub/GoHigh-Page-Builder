<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class SocialIconsWidget extends Widget {

	public function get_name(): string      { return 'social-icons'; }
	public function get_title(): string     { return __( 'Social Icons', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-share'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'social', 'icons', 'facebook', 'twitter', 'instagram', 'share', 'links' ]; }

	/**
	 * Map platform slugs to Dashicons classes and display labels.
	 */
	private function get_platform_map(): array {
		return [
			'facebook'  => [ 'icon' => 'dashicons dashicons-facebook-alt', 'label' => 'Facebook' ],
			'twitter'   => [ 'icon' => 'dashicons dashicons-twitter',       'label' => 'Twitter / X' ],
			'instagram' => [ 'icon' => 'dashicons dashicons-instagram',     'label' => 'Instagram' ],
			'linkedin'  => [ 'icon' => 'dashicons dashicons-linkedin',      'label' => 'LinkedIn' ],
			'youtube'   => [ 'icon' => 'dashicons dashicons-video-alt3',    'label' => 'YouTube' ],
			'tiktok'    => [ 'icon' => 'dashicons dashicons-smartphone',    'label' => 'TikTok' ],
			'pinterest' => [ 'icon' => 'dashicons dashicons-pinterest',     'label' => 'Pinterest' ],
			'github'    => [ 'icon' => 'dashicons dashicons-randomize',     'label' => 'GitHub' ],
		];
	}

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_social_icons', [
			'label' => __( 'Social Icons', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'icons', [
			'type'    => ControlsManager::REPEATER,
			'label'   => __( 'Social Icons', 'gohigh-page-builder' ),
			'fields'  => [
				[
					'name'    => 'platform',
					'type'    => 'select',
					'label'   => __( 'Platform', 'gohigh-page-builder' ),
					'default' => 'facebook',
					'options' => [
						'facebook'  => __( 'Facebook', 'gohigh-page-builder' ),
						'twitter'   => __( 'Twitter / X', 'gohigh-page-builder' ),
						'instagram' => __( 'Instagram', 'gohigh-page-builder' ),
						'linkedin'  => __( 'LinkedIn', 'gohigh-page-builder' ),
						'youtube'   => __( 'YouTube', 'gohigh-page-builder' ),
						'tiktok'    => __( 'TikTok', 'gohigh-page-builder' ),
						'pinterest' => __( 'Pinterest', 'gohigh-page-builder' ),
						'github'    => __( 'GitHub', 'gohigh-page-builder' ),
					],
				],
				[ 'name' => 'url',   'type' => 'url',  'label' => __( 'Link', 'gohigh-page-builder' ),  'default' => [ 'url' => '#' ] ],
				[ 'name' => 'label', 'type' => 'text', 'label' => __( 'Label', 'gohigh-page-builder' ), 'default' => '' ],
			],
			'default' => [
				[ 'platform' => 'facebook',  'url' => [ 'url' => '#' ], 'label' => '' ],
				[ 'platform' => 'twitter',   'url' => [ 'url' => '#' ], 'label' => '' ],
				[ 'platform' => 'instagram', 'url' => [ 'url' => '#' ], 'label' => '' ],
			],
		] );

		$this->add_control( 'shape', [
			'label'   => __( 'Shape', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'rounded' => __( 'Rounded', 'gohigh-page-builder' ),
				'square'  => __( 'Square', 'gohigh-page-builder' ),
				'circle'  => __( 'Circle', 'gohigh-page-builder' ),
			],
			'default' => 'circle',
		] );

		$this->add_control( 'color_type', [
			'label'   => __( 'Color Type', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'official' => __( 'Official Brand Colors', 'gohigh-page-builder' ),
				'custom'   => __( 'Custom', 'gohigh-page-builder' ),
			],
			'default' => 'official',
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_social_style', [
			'label' => __( 'Icons', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'icon_size', [
			'label'     => __( 'Icon Size', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 20, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-social-icon i' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'icon_padding', [
			'label'     => __( 'Padding', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 10, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-social-icon a' => 'padding: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'icon_spacing', [
			'label'     => __( 'Spacing Between Icons', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 8, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-social-icon' => 'margin-right: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings    = $this->get_settings_for_display();
		$icons       = $settings['icons'] ?? [];
		$shape       = $settings['shape'] ?? 'circle';
		$color_type  = $settings['color_type'] ?? 'official';
		$platform_map = $this->get_platform_map();

		$allowed_shapes = [ 'rounded', 'square', 'circle' ];
		if ( ! in_array( $shape, $allowed_shapes, true ) ) {
			$shape = 'circle';
		}

		$wrapper_class = 'ghpb-social-icons ghpb-social-shape-' . $shape;
		if ( 'official' === $color_type ) {
			$wrapper_class .= ' ghpb-social-color-official';
		}

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-social-icons' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="<?php echo esc_attr( $wrapper_class ); ?>">
				<?php foreach ( $icons as $index => $icon_item ) :
					$platform  = $icon_item['platform'] ?? 'facebook';
					$link_data = $icon_item['url'] ?? [];
					$link_url  = $link_data['url'] ?? '#';
					$label     = $icon_item['label'] ?? '';

					$platform_info = $platform_map[ $platform ] ?? $platform_map['facebook'];
					$icon_class    = $platform_info['icon'];
					$aria_label    = $label ?: $platform_info['label'];
					?>
					<span class="ghpb-social-icon ghpb-social-<?php echo esc_attr( $platform ); ?>">
						<a
							href="<?php echo esc_url( $link_url ); ?>"
							aria-label="<?php echo esc_attr( $aria_label ); ?>"
							<?php if ( ! empty( $link_data['is_external'] ) ) : ?>
								target="_blank" rel="noopener noreferrer"
							<?php endif; ?>
						>
							<i class="<?php echo esc_attr( $icon_class ); ?>"></i>
							<?php if ( $label ) : ?>
								<span class="ghpb-social-label"><?php echo esc_html( $label ); ?></span>
							<?php endif; ?>
						</a>
					</span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
