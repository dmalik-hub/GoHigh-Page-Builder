<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class VideoWidget extends Widget {

	public function get_name(): string    { return 'video'; }
	public function get_title(): string   { return __( 'Video', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-video-alt3'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'video', 'youtube', 'vimeo', 'embed' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_video', [
			'label' => __( 'Video', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'video_type', [
			'label'   => __( 'Source', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'youtube'  => 'YouTube',
				'vimeo'    => 'Vimeo',
				'hosted'   => __( 'Self Hosted', 'gohigh-page-builder' ),
			],
			'default' => 'youtube',
		] );

		$this->add_control( 'youtube_url', [
			'label'       => __( 'YouTube URL', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXT,
			'placeholder' => 'https://www.youtube.com/watch?v=...',
			'default'     => 'https://www.youtube.com/watch?v=9xwazD5SyVg',
			'condition'   => [ 'video_type' => 'youtube' ],
			'dynamic'     => [ 'active' => true ],
		] );

		$this->add_control( 'vimeo_url', [
			'label'       => __( 'Vimeo URL', 'gohigh-page-builder' ),
			'type'        => ControlsManager::TEXT,
			'placeholder' => 'https://vimeo.com/...',
			'condition'   => [ 'video_type' => 'vimeo' ],
			'dynamic'     => [ 'active' => true ],
		] );

		$this->add_control( 'hosted_url', [
			'label'     => __( 'Video URL', 'gohigh-page-builder' ),
			'type'      => ControlsManager::MEDIA,
			'condition' => [ 'video_type' => 'hosted' ],
			'dynamic'   => [ 'active' => true ],
		] );

		$this->add_control( 'autoplay', [
			'label'   => __( 'Autoplay', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
		] );

		$this->add_control( 'mute', [
			'label'   => __( 'Mute', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
		] );

		$this->add_control( 'loop', [
			'label'   => __( 'Loop', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
		] );

		$this->add_control( 'controls', [
			'label'   => __( 'Player Controls', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'aspect_ratio', [
			'label'   => __( 'Aspect Ratio', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [ '169' => '16:9', '219' => '21:9', '43' => '4:3', '11' => '1:1' ],
			'default' => '169',
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_video_style', [
			'label' => __( 'Video', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'border_radius', [
			'label'      => __( 'Border Radius', 'gohigh-page-builder' ),
			'type'       => ControlsManager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [ '{{WRAPPER}} .ghpb-video-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$type     = $settings['video_type'] ?? 'youtube';

		$embed_url = '';
		switch ( $type ) {
			case 'youtube':
				$url = $settings['youtube_url'] ?? '';
				preg_match( '/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches );
				$vid = $matches[1] ?? '';
				if ( $vid ) {
					$params = http_build_query( [
						'autoplay' => ! empty( $settings['autoplay'] ) ? 1 : 0,
						'mute'     => ! empty( $settings['mute'] ) ? 1 : 0,
						'loop'     => ! empty( $settings['loop'] ) ? 1 : 0,
						'controls' => 'yes' === $settings['controls'] ? 1 : 0,
					] );
					$embed_url = "https://www.youtube.com/embed/{$vid}?{$params}";
				}
				break;
			case 'vimeo':
				$url = $settings['vimeo_url'] ?? '';
				preg_match( '/vimeo\.com\/(\d+)/', $url, $matches );
				$vid = $matches[1] ?? '';
				if ( $vid ) {
					$params = http_build_query( [
						'autoplay' => ! empty( $settings['autoplay'] ) ? 1 : 0,
						'muted'    => ! empty( $settings['mute'] ) ? 1 : 0,
						'loop'     => ! empty( $settings['loop'] ) ? 1 : 0,
					] );
					$embed_url = "https://player.vimeo.com/video/{$vid}?{$params}";
				}
				break;
			case 'hosted':
				$media = $settings['hosted_url'] ?? [];
				$embed_url = $media['url'] ?? '';
				break;
		}

		$ratio_map = [ '169' => '56.25%', '219' => '47.62%', '43' => '75%', '11' => '100%' ];
		$ratio     = $ratio_map[ $settings['aspect_ratio'] ?? '169' ] ?? '56.25%';

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-video' );
		$this->add_render_attribute( 'video-wrapper', 'class', 'ghpb-video-wrapper' );
		$this->add_render_attribute( 'video-wrapper', 'style', "position:relative; padding-bottom:{$ratio}; height:0; overflow:hidden;" );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div <?php $this->print_render_attribute_string( 'video-wrapper' ); ?>>
				<?php if ( $embed_url && 'hosted' !== $type ) : ?>
					<iframe
						src="<?php echo esc_url( $embed_url ); ?>"
						style="position:absolute; top:0; left:0; width:100%; height:100%;"
						frameborder="0"
						allow="autoplay; encrypted-media"
						allowfullscreen>
					</iframe>
				<?php elseif ( $embed_url && 'hosted' === $type ) : ?>
					<video
						src="<?php echo esc_url( $embed_url ); ?>"
						style="position:absolute; top:0; left:0; width:100%; height:100%;"
						<?php echo ! empty( $settings['autoplay'] ) ? 'autoplay' : ''; ?>
						<?php echo ! empty( $settings['mute'] ) ? 'muted' : ''; ?>
						<?php echo ! empty( $settings['loop'] ) ? 'loop' : ''; ?>
						<?php echo 'yes' === $settings['controls'] ? 'controls' : ''; ?>
					></video>
				<?php else : ?>
					<div class="ghpb-video-placeholder">
						<span class="dashicons dashicons-video-alt3"></span>
						<p><?php esc_html_e( 'Enter a video URL to display.', 'gohigh-page-builder' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
