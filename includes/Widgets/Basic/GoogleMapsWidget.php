<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class GoogleMapsWidget extends Widget {

	public function get_name(): string    { return 'google_maps'; }
	public function get_title(): string   { return __( 'Google Maps', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-location-alt'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'google', 'maps', 'map', 'location', 'embed' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_map', [
			'label' => __( 'Google Maps', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'address', [
			'label'       => __( 'Location', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXT,
			'placeholder' => __( 'London Eye, London, UK', 'gohigh-page-builder' ),
			'default'     => 'London Eye, London, UK',
			'dynamic'     => [ 'active' => true ],
		] );

		$this->add_control( 'zoom', [
			'label'   => __( 'Zoom', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SLIDER,
			'range'   => [ 'px' => [ 'min' => 1, 'max' => 20 ] ],
			'default' => [ 'size' => 10, 'unit' => 'px' ],
		] );

		$this->add_responsive_control( 'height', [
			'label'     => __( 'Height', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'range'     => [ 'px' => [ 'min' => 100, 'max' => 1440 ] ],
			'default'   => [ 'size' => 400, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-google-maps iframe' => 'height: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$address  = rawurlencode( $settings['address'] ?? 'London Eye, London, UK' );
		$zoom     = (int) ( $settings['zoom']['size'] ?? 10 );

		$src = "https://maps.google.com/maps?q={$address}&t=m&z={$zoom}&output=embed&iwloc=near";
		?>
		<div class="ghpb-google-maps">
			<iframe
				src="<?php echo esc_url( $src ); ?>"
				width="100%"
				frameborder="0"
				scrolling="no"
				marginheight="0"
				marginwidth="0"
				aria-hidden="false"
				tabindex="0"
			></iframe>
		</div>
		<?php
	}
}
