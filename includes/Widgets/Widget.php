<?php
namespace GoHigh\PageBuilder\Widgets;

use GoHigh\PageBuilder\Controls\ControlsManager;
use GoHigh\PageBuilder\Plugin;

defined( 'ABSPATH' ) || exit;

abstract class Widget {

	private array   $settings          = [];
	private array   $controls          = [];
	private array   $render_attributes = [];
	private ?string $current_section   = null;
	private ?string $current_tabs      = null;
	private ?string $current_tab       = null;
	private bool    $controls_built    = false;

	// ── Identity ──────────────────────────────────────────────────────────────

	abstract public function get_name(): string;
	abstract public function get_title(): string;

	public function get_icon(): string    { return 'dashicons dashicons-screenoptions'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return []; }

	// ── Settings ──────────────────────────────────────────────────────────────

	public function set_settings( array $settings ): void {
		$this->settings = $settings;
	}

	public function get_settings( ?string $key = null ): mixed {
		if ( ! $this->controls_built ) {
			$this->ensure_controls();
		}
		if ( null === $key ) {
			return $this->settings;
		}
		return $this->settings[ $key ] ?? $this->get_control_default( $key );
	}

	public function get_settings_for_display( ?string $key = null ): mixed {
		return $this->get_settings( $key );
	}

	private function get_control_default( string $key ): mixed {
		return $this->controls[ $key ]['default'] ?? null;
	}

	// ── Controls registration API ─────────────────────────────────────────────

	final public function ensure_controls(): void {
		if ( $this->controls_built ) {
			return;
		}
		$this->controls_built = true;
		$this->_register_controls();
		$this->register_advanced_section();
	}

	abstract protected function _register_controls(): void;

	private function register_advanced_section(): void {
		$this->start_controls_section( '_advanced', [
			'label' => __( 'Advanced', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_ADVANCED,
		] );

		$this->add_control( '_element_id', [
			'label'       => __( 'CSS ID', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXT,
			'placeholder' => __( 'my-id', 'gohigh-page-builder' ),
			'description' => __( 'Set a unique CSS ID for this element.', 'gohigh-page-builder' ),
		] );

		$this->add_control( '_css_classes', [
			'label'       => __( 'CSS Classes', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXT,
			'placeholder' => __( 'my-class another-class', 'gohigh-page-builder' ),
		] );

		$this->add_control( '_custom_css', [
			'label'    => __( 'Custom CSS', 'gohigh-page-builder' ),
			'type'     => ControlsManager::CODE,
			'language' => 'css',
			'description' => __( 'Use "selector" to target this element.', 'gohigh-page-builder' ),
		] );

		$this->end_controls_section();
	}

	protected function start_controls_section( string $id, array $args = [] ): void {
		$this->current_section = $id;
		$this->controls[ $id ] = array_merge( [
			'name'      => $id,
			'type'      => ControlsManager::SECTION,
			'tab'       => ControlsManager::TAB_CONTENT,
			'label'     => '',
			'collapsed' => false,
		], $args );
	}

	protected function end_controls_section(): void {
		$this->current_section = null;
	}

	protected function start_controls_tabs( string $id ): void {
		$this->current_tabs = $id;
	}

	protected function end_controls_tabs(): void {
		$this->current_tabs = null;
	}

	protected function start_controls_tab( string $id, array $args = [] ): void {
		$this->current_tab   = $id;
		$this->controls[ $id ] = array_merge( [
			'name'  => $id,
			'type'  => ControlsManager::TAB,
			'label' => '',
		], $args );
	}

	protected function end_controls_tab(): void {
		$this->current_tab = null;
	}

	protected function add_control( string $id, array $args ): void {
		$controls_manager = Plugin::get_instance()->controls_manager;
		$type             = $args['type'] ?? ControlsManager::TEXT;
		$definition       = $controls_manager->build( $type, $args );

		$definition['name']    = $id;
		$definition['section'] = $this->current_section;
		$definition['tab_id']  = $this->current_tab;

		$this->controls[ $id ] = $definition;
	}

	protected function add_responsive_control( string $id, array $args ): void {
		$args['responsive'] = true;
		$this->add_control( $id, $args );

		// Register tablet + mobile variant entries in the controls list
		// (so JS knows they exist and PHP can read them).
		foreach ( [ 'tablet', 'mobile' ] as $device ) {
			$device_args           = $args;
			$device_args['responsive'] = false;
			$device_args['device'] = $device;
			$device_id             = "{$id}_{$device}";
			$this->add_control( $device_id, $device_args );
		}
	}

	protected function add_group_control( string $type, array $args ): void {
		$name     = $args['name'] ?? $type;
		$selector = $args['selector'] ?? '';

		// Typography group inlines its child controls.
		if ( $type === 'typography' ) {
			$this->add_control( "{$name}_font_family", [
				'label'     => __( 'Font Family', 'gohigh-page-builder' ),
				'type'      => ControlsManager::TEXT,
				'group'     => $name,
				'selectors' => [ $selector => 'font-family: {{VALUE}};' ],
				'section'   => $this->current_section,
			] );
			$this->add_responsive_control( "{$name}_font_size", [
				'label'      => __( 'Font Size', 'gohigh-page-builder' ),
				'type'       => ControlsManager::SLIDER,
				'group'      => $name,
				'size_units' => [ 'px', 'em', 'rem', 'vw' ],
				'selectors'  => [ $selector => 'font-size: {{SIZE}}{{UNIT}};' ],
			] );
			$this->add_control( "{$name}_font_weight", [
				'label'     => __( 'Font Weight', 'gohigh-page-builder' ),
				'type'      => ControlsManager::SELECT,
				'group'     => $name,
				'options'   => [ '' => 'Default', '100' => '100', '200' => '200', '300' => '300', '400' => 'Normal', '500' => '500', '600' => '600', '700' => 'Bold', '800' => '800', '900' => '900' ],
				'selectors' => [ $selector => 'font-weight: {{VALUE}};' ],
			] );
			$this->add_control( "{$name}_text_transform", [
				'label'     => __( 'Text Transform', 'gohigh-page-builder' ),
				'type'      => ControlsManager::SELECT,
				'group'     => $name,
				'options'   => [ '' => 'Default', 'uppercase' => 'Uppercase', 'lowercase' => 'Lowercase', 'capitalize' => 'Capitalize', 'none' => 'None' ],
				'selectors' => [ $selector => 'text-transform: {{VALUE}};' ],
			] );
			$this->add_control( "{$name}_font_style", [
				'label'     => __( 'Font Style', 'gohigh-page-builder' ),
				'type'      => ControlsManager::SELECT,
				'group'     => $name,
				'options'   => [ '' => 'Default', 'normal' => 'Normal', 'italic' => 'Italic', 'oblique' => 'Oblique' ],
				'selectors' => [ $selector => 'font-style: {{VALUE}};' ],
			] );
			$this->add_responsive_control( "{$name}_line_height", [
				'label'      => __( 'Line Height', 'gohigh-page-builder' ),
				'type'       => ControlsManager::SLIDER,
				'group'      => $name,
				'size_units' => [ 'em', 'px' ],
				'range'      => [ 'em' => [ 'min' => 0, 'max' => 10, 'step' => 0.1 ] ],
				'selectors'  => [ $selector => 'line-height: {{SIZE}}{{UNIT}};' ],
			] );
			$this->add_responsive_control( "{$name}_letter_spacing", [
				'label'      => __( 'Letter Spacing', 'gohigh-page-builder' ),
				'type'       => ControlsManager::SLIDER,
				'group'      => $name,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [ $selector => 'letter-spacing: {{SIZE}}{{UNIT}};' ],
			] );
		}
	}

	protected function update_control( string $id, array $args ): void {
		if ( isset( $this->controls[ $id ] ) ) {
			$this->controls[ $id ] = array_merge( $this->controls[ $id ], $args );
		}
	}

	protected function remove_control( string $id ): void {
		unset( $this->controls[ $id ] );
	}

	// ── Render attributes API ─────────────────────────────────────────────────

	protected function add_render_attribute( string $element, string|array $key, mixed $value = null, bool $overwrite = false ): void {
		if ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				$this->add_render_attribute( $element, $k, $v, $overwrite );
			}
			return;
		}

		if ( ! isset( $this->render_attributes[ $element ][ $key ] ) ) {
			$this->render_attributes[ $element ][ $key ] = [];
		}

		if ( $overwrite ) {
			$this->render_attributes[ $element ][ $key ] = (array) $value;
		} else {
			$this->render_attributes[ $element ][ $key ] = array_merge(
				$this->render_attributes[ $element ][ $key ],
				(array) $value
			);
		}
	}

	protected function get_render_attribute_string( string $element ): string {
		if ( empty( $this->render_attributes[ $element ] ) ) {
			return '';
		}
		$output = '';
		foreach ( $this->render_attributes[ $element ] as $attribute => $values ) {
			$values  = array_filter( $values, fn( $v ) => '' !== $v && null !== $v );
			$output .= sprintf( ' %s="%s"', esc_attr( $attribute ), esc_attr( implode( ' ', $values ) ) );
		}
		return $output;
	}

	protected function print_render_attribute_string( string $element ): void {
		echo $this->get_render_attribute_string( $element ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	// ── Rendering ─────────────────────────────────────────────────────────────

	abstract protected function render(): void;

	final public function render_content(): void {
		$this->render_attributes = [];
		$this->render();
	}

	// ── Controls export (for editor JS) ──────────────────────────────────────

	public function get_controls_config(): array {
		$this->ensure_controls();
		return array_values( $this->controls );
	}

	public function get_controls(): array {
		$this->ensure_controls();
		return $this->controls;
	}

	// ── URL helper ────────────────────────────────────────────────────────────

	protected function get_link_url( array $link ): string {
		if ( empty( $link['url'] ) ) {
			return '';
		}
		$url = esc_url( $link['url'] );
		$attrs = [];
		if ( ! empty( $link['is_external'] ) ) {
			$attrs[] = 'target="_blank"';
		}
		if ( ! empty( $link['nofollow'] ) ) {
			$attrs[] = 'rel="nofollow"';
		}
		return $url;
	}
}
