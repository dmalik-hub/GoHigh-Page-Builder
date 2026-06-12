<?php
namespace GoHigh\PageBuilder\Widgets;

use GoHigh\PageBuilder\Widgets\Basic\{
	HeadingWidget, TextEditorWidget, ImageWidget, ButtonWidget,
	VideoWidget, IconWidget, DividerWidget, SpacerWidget,
	HtmlWidget, ShortcodeWidget, GoogleMapsWidget, ImageBoxWidget
};

defined( 'ABSPATH' ) || exit;

class WidgetsManager {

	private array $widgets = [];

	public function __construct() {
		$this->register_widgets();
	}

	private function register_widgets(): void {
		$built_in = [
			HeadingWidget::class,
			TextEditorWidget::class,
			ImageWidget::class,
			ButtonWidget::class,
			VideoWidget::class,
			IconWidget::class,
			DividerWidget::class,
			SpacerWidget::class,
			HtmlWidget::class,
			ShortcodeWidget::class,
			GoogleMapsWidget::class,
			ImageBoxWidget::class,
		];

		foreach ( $built_in as $class ) {
			/** @var Widget $widget */
			$widget = new $class();
			$this->register_widget( $widget );
		}

		do_action( 'gohigh/widgets/register', $this );
	}

	public function register_widget( Widget $widget ): void {
		$this->widgets[ $widget->get_name() ] = $widget;
	}

	public function get_widget( string $name ): ?Widget {
		return $this->widgets[ $name ] ?? null;
	}

	public function get_widgets(): array {
		return $this->widgets;
	}

	public function unregister_widget( string $name ): void {
		unset( $this->widgets[ $name ] );
	}
}
