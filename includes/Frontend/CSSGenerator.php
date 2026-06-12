<?php
namespace GoHigh\PageBuilder\Frontend;

use GoHigh\PageBuilder\Plugin;

defined( 'ABSPATH' ) || exit;

class CSSGenerator {

	private static ?CSSGenerator $instance = null;

	private array $breakpoints = [
		'tablet' => 1024,
		'mobile' => 767,
	];

	public static function instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function generate( int $post_id, array $elements ): string {
		$css = '';
		$this->walk_elements( $elements, function ( array $element ) use ( &$css ) {
			$css .= $this->generate_element_css( $element );
		} );
		return $css;
	}

	private function walk_elements( array $elements, callable $callback ): void {
		foreach ( $elements as $element ) {
			$callback( $element );
			if ( ! empty( $element['elements'] ) ) {
				$this->walk_elements( $element['elements'], $callback );
			}
		}
	}

	private function generate_element_css( array $element ): string {
		$id       = $element['id'] ?? '';
		$settings = $element['settings'] ?? [];
		$el_type  = $element['elType'] ?? '';

		if ( ! $id ) {
			return '';
		}

		$wrapper_selector = ".ghpb-element-{$id}";
		$css              = '';

		// Get controls for this element.
		$controls = $this->get_controls( $element );

		foreach ( $controls as $control_key => $control ) {
			if ( empty( $control['selectors'] ) ) {
				continue;
			}

			// Desktop value.
			$value = $settings[ $control_key ] ?? $control['default'] ?? null;
			if ( $value !== null && $value !== '' ) {
				$css .= $this->compile_selectors( $control['selectors'], $wrapper_selector, $value, $control );
			}

			// Responsive variants.
			if ( ! empty( $control['responsive'] ) ) {
				foreach ( $this->breakpoints as $device => $max_width ) {
					$device_key = "{$control_key}_{$device}";
					$device_val = $settings[ $device_key ] ?? null;
					if ( $device_val !== null && $device_val !== '' ) {
						$device_css = $this->compile_selectors( $control['selectors'], $wrapper_selector, $device_val, $control );
						if ( $device_css ) {
							$css .= "@media (max-width:{$max_width}px) { {$device_css} }";
						}
					}
				}
			}
		}

		// Custom CSS.
		$custom_css = $settings['_custom_css'] ?? '';
		if ( $custom_css ) {
			$css .= str_replace( 'selector', $wrapper_selector, $custom_css );
		}

		// Section-level settings (background, padding, etc.).
		if ( 'section' === $el_type ) {
			$css .= $this->generate_section_css( $wrapper_selector, $settings );
		}

		if ( 'column' === $el_type ) {
			$css .= $this->generate_column_css( $wrapper_selector, $settings );
		}

		return $css;
	}

	private function get_controls( array $element ): array {
		$el_type     = $element['elType'] ?? '';
		$widget_type = $element['widgetType'] ?? '';

		if ( 'widget' === $el_type && $widget_type ) {
			$plugin = Plugin::get_instance();
			$widget = $plugin->widgets_manager->get_widget( $widget_type );
			if ( $widget ) {
				return $widget->get_controls();
			}
		}
		return [];
	}

	private function compile_selectors( array $selectors, string $wrapper, mixed $value, array $control = [] ): string {
		$css = '';
		foreach ( $selectors as $selector => $rule_template ) {
			$resolved_selector = str_replace( '{{WRAPPER}}', $wrapper, $selector );
			$rule = $this->parse_rule( $rule_template, $value, $control );
			if ( $rule ) {
				$css .= "{$resolved_selector} { {$rule} }";
			}
		}
		return $css;
	}

	private function parse_rule( string $template, mixed $value, array $control = [] ): string {
		// Handle slider values: { size, unit }.
		if ( is_array( $value ) && isset( $value['size'] ) ) {
			$size = $value['size'];
			$unit = $value['unit'] ?? 'px';
			if ( $size === '' || $size === null ) {
				return '';
			}
			$template = str_replace( '{{SIZE}}', (string) $size, $template );
			$template = str_replace( '{{UNIT}}', $unit, $template );
			$template = str_replace( '{{VALUE}}', $size . $unit, $template );
			return $template;
		}

		// Handle dimension values: { top, right, bottom, left, unit }.
		if ( is_array( $value ) && isset( $value['top'] ) ) {
			$unit = $value['unit'] ?? 'px';
			$template = str_replace( '{{TOP}}', (string) $value['top'], $template );
			$template = str_replace( '{{RIGHT}}', (string) $value['right'], $template );
			$template = str_replace( '{{BOTTOM}}', (string) $value['bottom'], $template );
			$template = str_replace( '{{LEFT}}', (string) $value['left'], $template );
			$template = str_replace( '{{UNIT}}', $unit, $template );
			return $template;
		}

		// Scalar.
		if ( is_array( $value ) ) {
			// Other arrays (e.g. icon, media) — skip, no CSS output.
			return '';
		}

		if ( $value === '' || $value === null ) {
			return '';
		}

		$template = str_replace( '{{VALUE}}', esc_attr( (string) $value ), $template );
		return $template;
	}

	private function generate_section_css( string $wrapper, array $settings ): string {
		$css = '';

		// Background.
		$bg = $settings['background'] ?? '';
		$bg_color = $settings['background_color'] ?? '';
		if ( $bg_color ) {
			$css .= "{$wrapper} { background-color: {$bg_color}; }";
		}
		$bg_image = $settings['background_image'] ?? [];
		if ( ! empty( $bg_image['url'] ) ) {
			$css .= "{$wrapper} { background-image: url({$bg_image['url']}); background-size: " . ( $settings['background_size'] ?? 'cover' ) . '; background-position: ' . ( $settings['background_position'] ?? 'center center' ) . '; background-repeat: ' . ( $settings['background_repeat'] ?? 'no-repeat' ) . '; }';
		}

		// Padding.
		$pad = $settings['padding'] ?? null;
		if ( $pad && is_array( $pad ) ) {
			$u = $pad['unit'] ?? 'px';
			$css .= "{$wrapper} { padding: {$pad['top']}{$u} {$pad['right']}{$u} {$pad['bottom']}{$u} {$pad['left']}{$u}; }";
		}
		// Margin.
		$mar = $settings['margin'] ?? null;
		if ( $mar && is_array( $mar ) ) {
			$u = $mar['unit'] ?? 'px';
			$css .= "{$wrapper} { margin: {$mar['top']}{$u} {$mar['right']}{$u} {$mar['bottom']}{$u} {$mar['left']}{$u}; }";
		}

		return $css;
	}

	private function generate_column_css( string $wrapper, array $settings ): string {
		$css = '';
		$pad = $settings['padding'] ?? null;
		if ( $pad && is_array( $pad ) ) {
			$u = $pad['unit'] ?? 'px';
			$css .= "{$wrapper} { padding: {$pad['top']}{$u} {$pad['right']}{$u} {$pad['bottom']}{$u} {$pad['left']}{$u}; }";
		}
		return $css;
	}
}
