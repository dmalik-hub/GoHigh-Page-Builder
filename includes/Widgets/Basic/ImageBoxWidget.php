<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class ImageBoxWidget extends Widget {

	public function get_name(): string    { return 'image-box'; }
	public function get_title(): string   { return __( 'Image Box', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-index-card'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'image', 'box', 'icon', 'card', 'feature' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_image', [
			'label' => __( 'Image Box', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'image', [
			'label'   => __( 'Image', 'gohigh-page-builder' ),
			'type'    => ControlsManager::MEDIA,
			'default' => [ 'url' => '', 'id' => 0 ],
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'title_text', [
			'label'       => __( 'Title', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXT,
			'default'     => __( 'This is the heading', 'gohigh-page-builder' ),
			'placeholder' => __( 'Enter your title', 'gohigh-page-builder' ),
			'dynamic'     => [ 'active' => true ],
		] );

		$this->add_control( 'description_text', [
			'label'       => __( 'Description', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXTAREA,
			'default'     => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'gohigh-page-builder' ),
			'placeholder' => __( 'Enter your description', 'gohigh-page-builder' ),
			'rows'        => 6,
			'dynamic'     => [ 'active' => true ],
		] );

		$this->add_control( 'link', [
			'label'   => __( 'Link', 'gohigh-page-builder' ),
			'type'    => ControlsManager::URL,
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_responsive_control( 'position', [
			'label'   => __( 'Image Position', 'gohigh-page-builder' ),
			'type'    => ControlsManager::CHOOSE,
			'options' => [
				'left'  => [ 'title' => 'Left', 'icon' => 'dashicons dashicons-align-left' ],
				'top'   => [ 'title' => 'Top', 'icon' => 'dashicons dashicons-align-center' ],
				'right' => [ 'title' => 'Right', 'icon' => 'dashicons dashicons-align-right' ],
			],
			'default' => 'top',
		] );

		$this->add_responsive_control( 'align', [
			'label'     => __( 'Alignment', 'gohigh-page-builder' ),
			'type'      => ControlsManager::CHOOSE,
			'options'   => [
				'left'   => [ 'title' => 'Left', 'icon' => 'dashicons dashicons-editor-alignleft' ],
				'center' => [ 'title' => 'Center', 'icon' => 'dashicons dashicons-editor-aligncenter' ],
				'right'  => [ 'title' => 'Right', 'icon' => 'dashicons dashicons-editor-alignright' ],
			],
			'selectors' => [ '{{WRAPPER}} .ghpb-image-box' => 'text-align: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', [
			'label' => __( 'Image Box', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'image_width', [
			'label'      => __( 'Image Width', 'gohigh-page-builder' ),
			'type'       => ControlsManager::SLIDER,
			'size_units' => [ 'px', '%' ],
			'range'      => [ 'px' => [ 'min' => 10, 'max' => 500 ], '%' => [ 'min' => 5, 'max' => 100 ] ],
			'selectors'  => [ '{{WRAPPER}} .ghpb-image-box-img' => 'width: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Title Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-image-box-title' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( 'typography', [
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .ghpb-image-box-title',
		] );

		$this->add_control( 'description_color', [
			'label'     => __( 'Description Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-image-box-description' => 'color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings   = $this->get_settings_for_display();
		$image      = $settings['image'] ?? [];
		$title      = $settings['title_text'] ?? '';
		$desc       = $settings['description_text'] ?? '';
		$link       = $settings['link'] ?? [];
		$position   = $settings['position'] ?? 'top';

		$wrap_class = 'ghpb-image-box ghpb-image-box-' . esc_attr( $position );

		$img_html = '';
		if ( ! empty( $image['id'] ) ) {
			$img_html = wp_get_attachment_image( $image['id'], 'medium', false, [ 'class' => 'ghpb-image-box-img' ] );
		} elseif ( ! empty( $image['url'] ) ) {
			$img_html = '<img class="ghpb-image-box-img" src="' . esc_url( $image['url'] ) . '" alt="">';
		}

		$tag = ! empty( $link['url'] ) ? 'a' : 'div';
		$link_open  = '';
		$link_close = '';
		if ( 'a' === $tag ) {
			$link_open  = '<a href="' . esc_url( $link['url'] ) . '"' . ( ! empty( $link['is_external'] ) ? ' target="_blank"' : '' ) . '>';
			$link_close = '</a>';
		}
		?>
		<div class="<?php echo esc_attr( $wrap_class ); ?>">
			<?php if ( $img_html ) : ?>
				<div class="ghpb-image-box-image">
					<?php echo $link_open . $img_html . $link_close; // phpcs:ignore ?>
				</div>
			<?php endif; ?>
			<div class="ghpb-image-box-content">
				<?php if ( $title ) : ?>
					<h3 class="ghpb-image-box-title">
						<?php echo $link_open . esc_html( $title ) . $link_close; // phpcs:ignore ?>
					</h3>
				<?php endif; ?>
				<?php if ( $desc ) : ?>
					<p class="ghpb-image-box-description"><?php echo wp_kses_post( $desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
