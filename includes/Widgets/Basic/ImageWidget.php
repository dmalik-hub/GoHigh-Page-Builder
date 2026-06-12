<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class ImageWidget extends Widget {

	public function get_name(): string    { return 'image'; }
	public function get_title(): string   { return __( 'Image', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-format-image'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'image', 'photo', 'picture' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_image', [
			'label' => __( 'Image', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'image', [
			'label'   => __( 'Choose Image', 'gohigh-page-builder' ),
			'type'    => ControlsManager::MEDIA,
			'default' => [ 'url' => '', 'id' => 0 ],
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'image_size', [
			'label'   => __( 'Image Size', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'thumbnail' => 'Thumbnail', 'medium' => 'Medium',
				'medium_large' => 'Medium Large', 'large' => 'Large',
				'full' => 'Full',
			],
			'default' => 'large',
		] );

		$this->add_responsive_control( 'align', [
			'label'     => __( 'Alignment', 'gohigh-page-builder' ),
			'type'      => ControlsManager::CHOOSE,
			'options'   => [
				'left'   => [ 'title' => 'Left', 'icon' => 'dashicons dashicons-editor-alignleft' ],
				'center' => [ 'title' => 'Center', 'icon' => 'dashicons dashicons-editor-aligncenter' ],
				'right'  => [ 'title' => 'Right', 'icon' => 'dashicons dashicons-editor-alignright' ],
			],
			'selectors' => [ '{{WRAPPER}}' => 'text-align: {{VALUE}};' ],
		] );

		$this->add_control( 'link_to', [
			'label'   => __( 'Link', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				''       => __( 'None', 'gohigh-page-builder' ),
				'file'   => __( 'Media File', 'gohigh-page-builder' ),
				'custom' => __( 'Custom URL', 'gohigh-page-builder' ),
			],
			'default' => '',
		] );

		$this->add_control( 'link', [
			'label'     => __( 'Link URL', 'gohigh-page-builder' ),
			'type'      => ControlsManager::URL,
			'condition' => [ 'link_to' => 'custom' ],
			'dynamic'   => [ 'active' => true ],
		] );

		$this->add_control( 'caption_source', [
			'label'   => __( 'Caption', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [ '' => __( 'None', 'gohigh-page-builder' ), 'attachment' => __( 'Attachment Caption', 'gohigh-page-builder' ), 'custom' => __( 'Custom Caption', 'gohigh-page-builder' ) ],
			'default' => '',
		] );

		$this->add_control( 'caption', [
			'label'     => __( 'Custom Caption', 'gohigh-page-builder' ),
			'type'      => ControlsManager::TEXT,
			'condition' => [ 'caption_source' => 'custom' ],
			'dynamic'   => [ 'active' => true ],
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_image', [
			'label' => __( 'Image', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'width', [
			'label'      => __( 'Width', 'gohigh-page-builder' ),
			'type'       => ControlsManager::SLIDER,
			'size_units' => [ 'px', '%', 'vw' ],
			'range'      => [ 'px' => [ 'min' => 1, 'max' => 1000 ], '%' => [ 'min' => 1, 'max' => 100 ] ],
			'selectors'  => [ '{{WRAPPER}} img' => 'width: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'max_width', [
			'label'      => __( 'Max Width', 'gohigh-page-builder' ),
			'type'       => ControlsManager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [ 'px' => [ 'min' => 1, 'max' => 1000 ], '%' => [ 'min' => 1, 'max' => 100 ] ],
			'selectors'  => [ '{{WRAPPER}} img' => 'max-width: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'border_radius', [
			'label'      => __( 'Border Radius', 'gohigh-page-builder' ),
			'type'       => ControlsManager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [ '{{WRAPPER}} img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_control( 'opacity', [
			'label'      => __( 'Opacity', 'gohigh-page-builder' ),
			'type'       => ControlsManager::SLIDER,
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.01 ] ],
			'selectors'  => [ '{{WRAPPER}} img' => 'opacity: {{SIZE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$image    = $settings['image'] ?? [];

		if ( empty( $image['url'] ) && empty( $image['id'] ) ) {
			echo '<div class="ghpb-image ghpb-image-placeholder"><span class="dashicons dashicons-format-image"></span></div>';
			return;
		}

		$size = $settings['image_size'] ?? 'large';
		$link_to = $settings['link_to'] ?? '';

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-image' );

		$img_html = '';
		if ( ! empty( $image['id'] ) ) {
			$img_html = wp_get_attachment_image( $image['id'], $size, false, [ 'class' => 'ghpb-image-img' ] );
		} else {
			$img_html = '<img class="ghpb-image-img" src="' . esc_url( $image['url'] ) . '" alt="">';
		}

		$caption = '';
		if ( 'attachment' === $settings['caption_source'] && ! empty( $image['id'] ) ) {
			$caption = get_post_field( 'post_excerpt', $image['id'] );
		} elseif ( 'custom' === $settings['caption_source'] ) {
			$caption = $settings['caption'] ?? '';
		}
		?>
		<figure <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( 'file' === $link_to && ! empty( $image['url'] ) ) : ?>
				<a href="<?php echo esc_url( $image['url'] ); ?>"><?php echo $img_html; // phpcs:ignore ?></a>
			<?php elseif ( 'custom' === $link_to && ! empty( $settings['link']['url'] ) ) : ?>
				<a href="<?php echo esc_url( $settings['link']['url'] ); ?>"
				   <?php echo ! empty( $settings['link']['is_external'] ) ? 'target="_blank"' : ''; ?>
				   <?php echo ! empty( $settings['link']['nofollow'] ) ? 'rel="nofollow"' : ''; ?>>
					<?php echo $img_html; // phpcs:ignore ?>
				</a>
			<?php else : ?>
				<?php echo $img_html; // phpcs:ignore ?>
			<?php endif; ?>
			<?php if ( $caption ) : ?>
				<figcaption class="ghpb-image-caption"><?php echo wp_kses_post( $caption ); ?></figcaption>
			<?php endif; ?>
		</figure>
		<?php
	}
}
