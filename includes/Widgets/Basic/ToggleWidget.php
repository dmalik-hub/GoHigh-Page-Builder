<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class ToggleWidget extends Widget {

	public function get_name(): string      { return 'toggle'; }
	public function get_title(): string     { return __( 'Toggle', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-randomize'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'toggle', 'show', 'hide', 'collapse', 'reveal' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_toggle', [
			'label' => __( 'Toggle', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'title', [
			'label'   => __( 'Title', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'Toggle Heading', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'content', [
			'label'   => __( 'Content', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXTAREA,
			'default' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis.', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'default_state', [
			'label'   => __( 'Default State', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'open'   => __( 'Open', 'gohigh-page-builder' ),
				'closed' => __( 'Closed', 'gohigh-page-builder' ),
			],
			'default' => 'closed',
		] );

		$this->add_control( 'icon_open', [
			'label'   => __( 'Icon (Open)', 'gohigh-page-builder' ),
			'type'    => ControlsManager::ICON,
			'default' => 'dashicons dashicons-minus',
		] );

		$this->add_control( 'icon_closed', [
			'label'   => __( 'Icon (Closed)', 'gohigh-page-builder' ),
			'type'    => ControlsManager::ICON,
			'default' => 'dashicons dashicons-plus',
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_toggle_style', [
			'label' => __( 'Toggle', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Title Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-toggle-header' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'title_background', [
			'label'     => __( 'Title Background', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-toggle-header' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'content_background', [
			'label'     => __( 'Content Background', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}} .ghpb-toggle-content' => 'background-color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings      = $this->get_settings_for_display();
		$title         = $settings['title'] ?? '';
		$content       = $settings['content'] ?? '';
		$default_state = $settings['default_state'] ?? 'closed';
		$icon_open     = $settings['icon_open'] ?? 'dashicons dashicons-minus';
		$icon_closed   = $settings['icon_closed'] ?? 'dashicons dashicons-plus';
		$is_open       = ( 'open' === $default_state );

		$wrapper_class = 'ghpb-widget-toggle';
		$toggle_class  = 'ghpb-toggle' . ( $is_open ? ' ghpb-toggle-open' : '' );

		$this->add_render_attribute( 'wrapper', 'class', $wrapper_class );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="<?php echo esc_attr( $toggle_class ); ?>">
				<div
					class="ghpb-toggle-header"
					role="button"
					aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
					tabindex="0"
				>
					<span class="ghpb-toggle-title"><?php echo esc_html( $title ); ?></span>
					<span class="ghpb-toggle-icon ghpb-toggle-icon-closed" <?php echo $is_open ? 'style="display:none;"' : ''; ?>>
						<i class="<?php echo esc_attr( $icon_closed ); ?>"></i>
					</span>
					<span class="ghpb-toggle-icon ghpb-toggle-icon-open" <?php echo $is_open ? '' : 'style="display:none;"'; ?>>
						<i class="<?php echo esc_attr( $icon_open ); ?>"></i>
					</span>
				</div>
				<div class="ghpb-toggle-content" <?php echo $is_open ? '' : 'style="display:none;"'; ?>>
					<div class="ghpb-toggle-content-inner">
						<?php echo wp_kses_post( $content ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
