<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class HeadingWidget extends Widget {

	public function get_name(): string    { return 'heading'; }
	public function get_title(): string   { return __( 'Heading', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-editor-textcolor'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'heading', 'title', 'text', 'h1', 'h2', 'h3' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_title', [
			'label' => __( 'Title', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'title', [
			'label'       => __( 'Title', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXTAREA,
			'rows'        => 3,
			'default'     => __( 'Add Your Heading Text Here', 'gohigh-page-builder' ),
			'placeholder' => __( 'Enter your title', 'gohigh-page-builder' ),
			'dynamic'     => [ 'active' => true ],
		] );

		$this->add_control( 'link', [
			'label'     => __( 'Link', 'gohigh-page-builder' ),
			'type'      => ControlsManager::URL,
			'dynamic'   => [ 'active' => true ],
		] );

		$this->add_control( 'header_size', [
			'label'   => __( 'HTML Tag', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'h1'   => 'H1', 'h2' => 'H2', 'h3' => 'H3',
				'h4'   => 'H4', 'h5' => 'H5', 'h6' => 'H6',
				'div'  => 'div', 'span' => 'span', 'p' => 'p',
			],
			'default' => 'h2',
		] );

		$this->add_responsive_control( 'align', [
			'label'     => __( 'Alignment', 'gohigh-page-builder' ),
			'type'      => ControlsManager::CHOOSE,
			'options'   => [
				'left'    => [ 'title' => __( 'Left', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-editor-alignleft' ],
				'center'  => [ 'title' => __( 'Center', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-editor-aligncenter' ],
				'right'   => [ 'title' => __( 'Right', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-editor-alignright' ],
				'justify' => [ 'title' => __( 'Justify', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-editor-justify' ],
			],
			'selectors' => [ '{{WRAPPER}} .ghpb-heading-title' => 'text-align: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_title_style', [
			'label' => __( 'Title', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Text Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-heading-title' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( 'typography', [
			'name'     => 'typography',
			'selector' => '{{WRAPPER}} .ghpb-heading-title',
		] );

		$this->add_control( 'blend_mode', [
			'label'     => __( 'Blend Mode', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SELECT,
			'options'   => [
				'' => 'Normal', 'multiply' => 'Multiply', 'screen' => 'Screen',
				'overlay' => 'Overlay', 'darken' => 'Darken', 'lighten' => 'Lighten',
			],
			'selectors' => [ '{{WRAPPER}} .ghpb-heading-title' => 'mix-blend-mode: {{VALUE}}' ],
			'separator' => 'none',
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings  = $this->get_settings_for_display();
		$title     = $settings['title'] ?? '';
		$tag       = $settings['header_size'] ?? 'h2';
		$tag       = in_array( $tag, [ 'h1','h2','h3','h4','h5','h6','div','span','p' ], true ) ? $tag : 'h2';
		$link      = $settings['link'] ?? [];

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-heading' );
		$this->add_render_attribute( 'title', 'class', 'ghpb-heading-title' );

		if ( ! empty( $link['url'] ) ) {
			$this->add_render_attribute( 'url', 'href', esc_url( $link['url'] ) );
			if ( ! empty( $link['is_external'] ) ) {
				$this->add_render_attribute( 'url', 'target', '_blank' );
			}
			if ( ! empty( $link['nofollow'] ) ) {
				$this->add_render_attribute( 'url', 'rel', 'nofollow' );
			}
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<<?php echo esc_attr( $tag ); ?> <?php $this->print_render_attribute_string( 'title' ); ?>>
			<?php if ( ! empty( $link['url'] ) ) : ?>
				<a <?php $this->print_render_attribute_string( 'url' ); ?>>
					<?php echo wp_kses_post( $title ); ?>
				</a>
			<?php else : ?>
				<?php echo wp_kses_post( $title ); ?>
			<?php endif; ?>
			</<?php echo esc_attr( $tag ); ?>>
		</div>
		<?php
	}
}
