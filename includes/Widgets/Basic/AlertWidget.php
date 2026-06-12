<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class AlertWidget extends Widget {

	public function get_name(): string      { return 'alert'; }
	public function get_title(): string     { return __( 'Alert', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-warning'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'alert', 'notice', 'warning', 'info', 'message', 'notification' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_alert', [
			'label' => __( 'Alert', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'alert_type', [
			'label'   => __( 'Alert Type', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'info'    => __( 'Info', 'gohigh-page-builder' ),
				'success' => __( 'Success', 'gohigh-page-builder' ),
				'warning' => __( 'Warning', 'gohigh-page-builder' ),
				'danger'  => __( 'Danger', 'gohigh-page-builder' ),
			],
			'default' => 'info',
		] );

		$this->add_control( 'icon', [
			'label'   => __( 'Icon', 'gohigh-page-builder' ),
			'type'    => ControlsManager::ICON,
			'default' => 'dashicons dashicons-info',
		] );

		$this->add_control( 'title', [
			'label'   => __( 'Title', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => __( 'This is an Alert', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'description', [
			'label'   => __( 'Description', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXTAREA,
			'default' => __( 'I am a description. Click the edit button to change this text.', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_control( 'show_dismiss', [
			'label'   => __( 'Dismiss Button', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
			'default' => 'yes',
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings     = $this->get_settings_for_display();
		$alert_type   = $settings['alert_type'] ?? 'info';
		$title        = $settings['title'] ?? '';
		$description  = $settings['description'] ?? '';
		$show_dismiss = ( 'yes' === ( $settings['show_dismiss'] ?? 'yes' ) );
		$icon         = $settings['icon'] ?? '';

		$allowed_types = [ 'info', 'success', 'warning', 'danger' ];
		if ( ! in_array( $alert_type, $allowed_types, true ) ) {
			$alert_type = 'info';
		}

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-alert' );
		$this->add_render_attribute( 'alert', 'class', 'ghpb-alert ghpb-alert-' . $alert_type );
		$this->add_render_attribute( 'alert', 'role', 'alert' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div <?php $this->print_render_attribute_string( 'alert' ); ?>>
				<?php if ( $icon ) : ?>
					<span class="ghpb-alert-icon">
						<i class="<?php echo esc_attr( $icon ); ?>"></i>
					</span>
				<?php endif; ?>
				<div class="ghpb-alert-body">
					<?php if ( $title ) : ?>
						<span class="ghpb-alert-title"><?php echo esc_html( $title ); ?></span>
					<?php endif; ?>
					<?php if ( $description ) : ?>
						<span class="ghpb-alert-description"><?php echo wp_kses_post( $description ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $show_dismiss ) : ?>
					<button class="ghpb-alert-dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'gohigh-page-builder' ); ?>" type="button">
						<span class="dashicons dashicons-no-alt"></span>
					</button>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
