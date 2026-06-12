<?php
namespace GoHigh\PageBuilder\Controls;

use GoHigh\PageBuilder\Controls\Controls\{
	TextControl, TextareaControl, NumberControl, UrlControl, HiddenControl,
	SelectControl, ChooseControl, SwitcherControl, ColorControl, MediaControl,
	IconControl, SliderControl, DimensionsControl, TypographyControl,
	BackgroundControl, RepeaterControl, SectionControl, TabControl, TabsControl,
	HeadingControl, CodeControl
};

defined( 'ABSPATH' ) || exit;

class ControlsManager {

	// Control type constants.
	const TEXT        = 'text';
	const TEXTAREA    = 'textarea';
	const NUMBER      = 'number';
	const URL         = 'url';
	const HIDDEN      = 'hidden';
	const SELECT      = 'select';
	const CHOOSE      = 'choose';
	const SWITCHER    = 'switcher';
	const COLOR       = 'color';
	const MEDIA       = 'media';
	const ICON        = 'icon';
	const SLIDER      = 'slider';
	const DIMENSIONS  = 'dimensions';
	const TYPOGRAPHY  = 'typography';
	const BACKGROUND  = 'background';
	const REPEATER    = 'repeater';
	const CODE        = 'code';

	// UI-only controls (no value stored).
	const SECTION     = 'section';
	const TAB         = 'tab';
	const TABS        = 'tabs';
	const HEADING     = 'heading';

	// Tab constants.
	const TAB_CONTENT  = 'content';
	const TAB_STYLE    = 'style';
	const TAB_ADVANCED = 'advanced';

	private array $controls = [];

	public function __construct() {
		$this->register_controls();
	}

	private function register_controls(): void {
		$controls = [
			self::TEXT       => TextControl::class,
			self::TEXTAREA   => TextareaControl::class,
			self::NUMBER     => NumberControl::class,
			self::URL        => UrlControl::class,
			self::HIDDEN     => HiddenControl::class,
			self::SELECT     => SelectControl::class,
			self::CHOOSE     => ChooseControl::class,
			self::SWITCHER   => SwitcherControl::class,
			self::COLOR      => ColorControl::class,
			self::MEDIA      => MediaControl::class,
			self::ICON       => IconControl::class,
			self::SLIDER     => SliderControl::class,
			self::DIMENSIONS => DimensionsControl::class,
			self::TYPOGRAPHY => TypographyControl::class,
			self::BACKGROUND => BackgroundControl::class,
			self::REPEATER   => RepeaterControl::class,
			self::CODE       => CodeControl::class,
			self::SECTION    => SectionControl::class,
			self::TAB        => TabControl::class,
			self::TABS       => TabsControl::class,
			self::HEADING    => HeadingControl::class,
		];

		foreach ( $controls as $type => $class ) {
			$this->controls[ $type ] = new $class();
		}

		do_action( 'gohigh/controls/register', $this );
	}

	public function get_control( string $type ): ?Control {
		return $this->controls[ $type ] ?? null;
	}

	public function register_control( string $type, Control $control ): void {
		$this->controls[ $type ] = $control;
	}

	public function get_controls(): array {
		return $this->controls;
	}

	/**
	 * Build a complete control definition merging defaults with user-supplied args.
	 */
	public function build( string $type, array $args ): array {
		$control = $this->get_control( $type );
		if ( ! $control ) {
			return $args;
		}
		$definition = $control->build_definition( $args );
		$definition['type'] = $type;
		return $definition;
	}
}
