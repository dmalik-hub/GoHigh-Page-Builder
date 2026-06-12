<?php
namespace GoHigh\PageBuilder\Frontend;

use GoHigh\PageBuilder\Plugin;

defined( 'ABSPATH' ) || exit;

class Renderer {

	public function render( array $elements ): void {
		foreach ( $elements as $element ) {
			$this->render_element( $element );
		}
	}

	public function render_element( array $element ): void {
		$el_type = $element['elType'] ?? '';

		switch ( $el_type ) {
			case 'section':
				$this->render_section( $element );
				break;
			case 'column':
				$this->render_column( $element );
				break;
			case 'widget':
				$this->render_widget( $element );
				break;
		}
	}

	private function render_section( array $element ): void {
		$id       = $element['id'] ?? '';
		$settings = $element['settings'] ?? [];
		$classes  = $this->get_element_classes( 'section', $id, $settings );
		$attrs    = $this->get_element_attributes( $element );

		$tag     = $settings['tag'] ?? 'section';
		$allowed = [ 'section', 'article', 'div', 'header', 'footer', 'main' ];
		$tag     = in_array( $tag, $allowed, true ) ? $tag : 'section';

		$layout = $settings['layout'] ?? 'boxed';
		?>
		<<?php echo esc_attr( $tag ); ?> <?php echo $attrs; // phpcs:ignore ?>>
			<div class="ghpb-container<?php echo 'full_width' === $layout ? ' ghpb-container--full' : ''; ?>">
				<div class="ghpb-row">
					<?php foreach ( $element['elements'] ?? [] as $child ) : ?>
						<?php $this->render_element( $child ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</<?php echo esc_attr( $tag ); ?>>
		<?php
	}

	private function render_column( array $element ): void {
		$id       = $element['id'] ?? '';
		$settings = $element['settings'] ?? [];
		$size     = $settings['_column_size'] ?? 100;
		$classes  = 'ghpb-column ghpb-col-' . $this->normalize_width( $size ) . ' ghpb-element-' . esc_attr( $id );

		$extra_classes = $settings['_css_classes'] ?? '';
		if ( $extra_classes ) {
			$classes .= ' ' . esc_attr( trim( $extra_classes ) );
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-id="<?php echo esc_attr( $id ); ?>">
			<div class="ghpb-column-wrap">
				<div class="ghpb-widget-wrap">
					<?php foreach ( $element['elements'] ?? [] as $child ) : ?>
						<?php $this->render_element( $child ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_widget( array $element ): void {
		$id          = $element['id'] ?? '';
		$widget_type = $element['widgetType'] ?? '';
		$settings    = $element['settings'] ?? [];

		$plugin = Plugin::get_instance();
		$widget = $plugin->widgets_manager->get_widget( $widget_type );

		if ( ! $widget ) {
			return;
		}

		$widget->set_settings( $settings );

		$classes = 'ghpb-widget ghpb-widget-' . esc_attr( $widget_type ) . ' ghpb-element-' . esc_attr( $id );
		$extra   = $settings['_css_classes'] ?? '';
		if ( $extra ) {
			$classes .= ' ' . esc_attr( trim( $extra ) );
		}

		$extra_id = $settings['_element_id'] ?? '';
		$id_attr  = $extra_id ? ' id="' . esc_attr( $extra_id ) . '"' : '';
		?>
		<div class="<?php echo esc_attr( $classes ); ?>"<?php echo $id_attr; // phpcs:ignore ?> data-id="<?php echo esc_attr( $id ); ?>">
			<div class="ghpb-widget-container">
				<?php $widget->render_content(); ?>
			</div>
		</div>
		<?php
	}

	private function get_element_classes( string $type, string $id, array $settings ): string {
		$classes = "ghpb-{$type} ghpb-element-{$id}";
		$extra   = $settings['_css_classes'] ?? '';
		if ( $extra ) {
			$classes .= ' ' . esc_attr( trim( $extra ) );
		}
		return $classes;
	}

	private function get_element_attributes( array $element ): string {
		$id         = $element['id'] ?? '';
		$settings   = $element['settings'] ?? [];
		$classes    = $this->get_element_classes( $element['elType'] ?? 'section', $id, $settings );
		$extra_id   = $settings['_element_id'] ?? '';
		$id_attr    = $extra_id ? ' id="' . esc_attr( $extra_id ) . '"' : '';
		$data_id    = ' data-id="' . esc_attr( $id ) . '"';

		return 'class="' . esc_attr( $classes ) . '"' . $id_attr . $data_id;
	}

	private function normalize_width( float|int|string $size ): string {
		// Converts percent width (e.g. 33.333) to a CSS-class-friendly string.
		$map = [
			100  => '100', 75 => '75', 66.666 => '66', 66.667 => '66', 50 => '50',
			33.333 => '33', 33.334 => '33', 25 => '25', 20 => '20', 16.666 => '16',
		];
		foreach ( $map as $exact => $slug ) {
			if ( abs( (float) $size - (float) $exact ) < 0.5 ) {
				return $slug;
			}
		}
		return (string) (int) $size;
	}
}
