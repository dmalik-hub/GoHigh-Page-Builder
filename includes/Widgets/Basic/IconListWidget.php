<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class IconListWidget extends Widget {

	public function get_name(): string      { return 'icon-list'; }
	public function get_title(): string     { return __( 'Icon List', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-list-view'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'icon', 'list', 'bullet', 'check', 'items' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_icon_list', [
			'label' => __( 'Icon List', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'items', [
			'type'    => ControlsManager::REPEATER,
			'label'   => __( 'Items', 'gohigh-page-builder' ),
			'fields'  => [
				[ 'name' => 'text', 'type' => 'text', 'label' => __( 'Text', 'gohigh-page-builder' ), 'default' => __( 'List Item', 'gohigh-page-builder' ) ],
				[ 'name' => 'icon', 'type' => 'icon', 'label' => __( 'Icon', 'gohigh-page-builder' ), 'default' => 'dashicons dashicons-yes-alt' ],
				[ 'name' => 'link', 'type' => 'url',  'label' => __( 'Link', 'gohigh-page-builder' ), 'default' => [ 'url' => '' ] ],
			],
			'default' => [
				[ 'text' => __( 'List Item #1', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-yes-alt', 'link' => [ 'url' => '' ] ],
				[ 'text' => __( 'List Item #2', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-yes-alt', 'link' => [ 'url' => '' ] ],
				[ 'text' => __( 'List Item #3', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-yes-alt', 'link' => [ 'url' => '' ] ],
			],
		] );

		$this->add_control( 'layout', [
			'label'   => __( 'Layout', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'default' => __( 'Default (Vertical)', 'gohigh-page-builder' ),
				'inline'  => __( 'Inline', 'gohigh-page-builder' ),
			],
			'default' => 'default',
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_icon_list_style', [
			'label' => __( 'Icon List', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'icon_color', [
			'label'     => __( 'Icon Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-icon-list-icon' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'text_color', [
			'label'     => __( 'Text Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-icon-list-text' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'space_between', [
			'label'     => __( 'Space Between', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 8, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-icon-list-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'divider', [
			'label'   => __( 'Divider', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'divider_color', [
			'label'     => __( 'Divider Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-icon-list-item:not(:last-child)' => 'border-bottom-color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$items    = $settings['items'] ?? [];
		$layout   = $settings['layout'] ?? 'default';
		$divider  = ( 'yes' === ( $settings['divider'] ?? '' ) );

		$allowed_layouts = [ 'default', 'inline' ];
		if ( ! in_array( $layout, $allowed_layouts, true ) ) {
			$layout = 'default';
		}

		$list_class = 'ghpb-icon-list ghpb-layout-' . $layout;
		if ( $divider ) {
			$list_class .= ' ghpb-icon-list-divider';
		}

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-icon-list' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<ul class="<?php echo esc_attr( $list_class ); ?>">
				<?php foreach ( $items as $index => $item ) :
					$text      = $item['text'] ?? '';
					$icon      = $item['icon'] ?? '';
					$link_data = $item['link'] ?? [];
					$link_url  = $link_data['url'] ?? '';
					?>
					<li class="ghpb-icon-list-item">
						<?php if ( $link_url ) : ?>
							<a href="<?php echo esc_url( $link_url ); ?>"
								<?php if ( ! empty( $link_data['is_external'] ) ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
								class="ghpb-icon-list-link"
							>
						<?php endif; ?>

						<?php if ( $icon ) : ?>
							<span class="ghpb-icon-list-icon">
								<i class="<?php echo esc_attr( $icon ); ?>"></i>
							</span>
						<?php endif; ?>
						<span class="ghpb-icon-list-text"><?php echo esc_html( $text ); ?></span>

						<?php if ( $link_url ) : ?>
							</a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
