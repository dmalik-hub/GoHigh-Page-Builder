<?php
namespace GoHigh\PageBuilder\Controls;

defined( 'ABSPATH' ) || exit;

abstract class Control {

	abstract public function get_type(): string;

	public function get_default_value(): mixed {
		return '';
	}

	public function get_default_settings(): array {
		return [];
	}

	public function get_config(): array {
		return array_merge( $this->get_default_settings(), [
			'type'    => $this->get_type(),
			'default' => $this->get_default_value(),
		] );
	}

	public function sanitize( mixed $value ): mixed {
		return $value;
	}

	/**
	 * Merge control definition from widget with this control's defaults.
	 */
	public function build_definition( array $args ): array {
		$defaults   = array_merge( $this->get_config(), [
			'label'       => '',
			'description' => '',
			'show_label'  => true,
			'label_block' => false,
			'separator'   => 'default',
			'dynamic'     => [],
			'condition'   => [],
			'conditions'  => [],
			'tab'         => ControlsManager::TAB_CONTENT,
			'selectors'   => [],
			'render_type' => 'template',
		] );
		return array_merge( $defaults, $args );
	}
}
