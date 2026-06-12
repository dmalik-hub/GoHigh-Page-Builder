<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class TabsWidget extends Widget {

	public function get_name(): string      { return 'tabs'; }
	public function get_title(): string     { return __( 'Tabs', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-table-row-before'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'tabs', 'tabbed', 'content', 'panel', 'section' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_tabs', [
			'label' => __( 'Tabs', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'tabs', [
			'type'    => ControlsManager::REPEATER,
			'label'   => __( 'Tab Items', 'gohigh-page-builder' ),
			'fields'  => [
				[ 'name' => 'title',   'type' => 'text',     'label' => __( 'Title', 'gohigh-page-builder' ),   'default' => __( 'Tab Title', 'gohigh-page-builder' ) ],
				[ 'name' => 'content', 'type' => 'textarea', 'label' => __( 'Content', 'gohigh-page-builder' ), 'default' => __( 'Tab content goes here.', 'gohigh-page-builder' ) ],
				[ 'name' => 'icon',    'type' => 'icon',     'label' => __( 'Icon', 'gohigh-page-builder' ),    'default' => '' ],
			],
			'default' => [
				[ 'title' => __( 'Tab #1', 'gohigh-page-builder' ), 'content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'gohigh-page-builder' ), 'icon' => '' ],
				[ 'title' => __( 'Tab #2', 'gohigh-page-builder' ), 'content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'gohigh-page-builder' ), 'icon' => '' ],
				[ 'title' => __( 'Tab #3', 'gohigh-page-builder' ), 'content' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'gohigh-page-builder' ), 'icon' => '' ],
			],
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_tabs_style', [
			'label' => __( 'Tabs', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'tab_background_color', [
			'label'     => __( 'Tab Background', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-tabs-nav-item' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'tab_active_color', [
			'label'     => __( 'Active Tab Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-tabs-nav-item.ghpb-tab-active' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'content_background', [
			'label'     => __( 'Content Background', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-tabs-content-panel' => 'background-color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$tabs     = $settings['tabs'] ?? [];

		if ( empty( $tabs ) ) {
			return;
		}

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-tabs' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="ghpb-tabs">
				<nav class="ghpb-tabs-nav" role="tablist">
					<?php foreach ( $tabs as $index => $tab ) :
						$tab_id    = 'ghpb-tab-' . $index;
						$is_active = ( 0 === $index );
						$icon_val  = $tab['icon'] ?? '';
						?>
						<button
							class="ghpb-tabs-nav-item<?php echo $is_active ? ' ghpb-tab-active' : ''; ?>"
							role="tab"
							aria-controls="<?php echo esc_attr( $tab_id ); ?>"
							aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
							data-tab="<?php echo esc_attr( $tab_id ); ?>"
						>
							<?php if ( ! empty( $icon_val ) ) : ?>
								<span class="ghpb-tab-icon"><i class="<?php echo esc_attr( $icon_val ); ?>"></i></span>
							<?php endif; ?>
							<span class="ghpb-tab-title"><?php echo esc_html( $tab['title'] ?? '' ); ?></span>
						</button>
					<?php endforeach; ?>
				</nav>
				<div class="ghpb-tabs-content">
					<?php foreach ( $tabs as $index => $tab ) :
						$tab_id    = 'ghpb-tab-' . $index;
						$is_active = ( 0 === $index );
						?>
						<div
							id="<?php echo esc_attr( $tab_id ); ?>"
							class="ghpb-tabs-content-panel<?php echo $is_active ? ' ghpb-tab-panel-active' : ''; ?>"
							role="tabpanel"
							<?php echo $is_active ? '' : 'style="display:none;"'; ?>
						>
							<?php echo wp_kses_post( $tab['content'] ?? '' ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
