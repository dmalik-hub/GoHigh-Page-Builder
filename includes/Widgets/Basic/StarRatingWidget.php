<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class StarRatingWidget extends Widget {

	public function get_name(): string      { return 'star-rating'; }
	public function get_title(): string     { return __( 'Star Rating', 'gohigh-page-builder' ); }
	public function get_icon(): string      { return 'dashicons dashicons-star-filled'; }
	public function get_categories(): array { return [ 'general' ]; }
	public function get_keywords(): array   { return [ 'star', 'rating', 'review', 'score', 'stars' ]; }

	protected function _register_controls(): void {

		// ── Content ──────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_star_rating', [
			'label' => __( 'Star Rating', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'rating', [
			'label'   => __( 'Rating', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SLIDER,
			'default' => [ 'size' => 5, 'unit' => '' ],
			'range'   => [ '' => [ 'min' => 0, 'max' => 5, 'step' => 0.1 ] ],
		] );

		$this->add_control( 'max_stars', [
			'label'   => __( 'Max Stars', 'gohigh-page-builder' ),
			'type'    => ControlsManager::NUMBER,
			'default' => 5,
		] );

		$this->add_control( 'star_style', [
			'label'   => __( 'Star Style', 'gohigh-page-builder' ),
			'type'    => ControlsManager::SELECT,
			'options' => [
				'filled'   => __( 'Filled', 'gohigh-page-builder' ),
				'outlined' => __( 'Outlined', 'gohigh-page-builder' ),
			],
			'default' => 'filled',
		] );

		$this->add_control( 'title', [
			'label'   => __( 'Title', 'gohigh-page-builder' ),
			'type'    => ControlsManager::TEXT,
			'default' => '',
			'dynamic' => [ 'active' => true ],
		] );

		$this->end_controls_section();

		// ── Style ─────────────────────────────────────────────────────────────

		$this->start_controls_section( 'section_star_rating_style', [
			'label' => __( 'Stars', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'color', [
			'label'     => __( 'Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'default'   => '#FFD700',
			'selectors' => [ '{{WRAPPER}} .ghpb-star-marked' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'unmarked_color', [
			'label'     => __( 'Unmarked Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'default'   => '#cccccc',
			'selectors' => [ '{{WRAPPER}} .ghpb-star-unmarked' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'star_size', [
			'label'     => __( 'Star Size', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 24, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-star-rating-stars i' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'title_size', [
			'label'     => __( 'Title Size', 'gohigh-page-builder' ),
			'type'      => ControlsManager::SLIDER,
			'default'   => [ 'size' => 14, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .ghpb-star-rating-title' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings    = $this->get_settings_for_display();
		$rating_data = $settings['rating'] ?? [];
		$rating      = isset( $rating_data['size'] ) ? floatval( $rating_data['size'] ) : 5.0;
		$max_stars   = max( 1, intval( $settings['max_stars'] ?? 5 ) );
		$star_style  = $settings['star_style'] ?? 'filled';
		$title       = $settings['title'] ?? '';

		$rating    = max( 0, min( (float) $max_stars, $rating ) );
		$full_icon = ( 'outlined' === $star_style ) ? 'dashicons dashicons-star-empty' : 'dashicons dashicons-star-filled';
		$half_icon = 'dashicons dashicons-star-half';

		$this->add_render_attribute( 'wrapper', 'class', 'ghpb-widget-star-rating' );
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div
				class="ghpb-star-rating"
				aria-label="<?php printf( esc_attr__( 'Rated %1$s out of %2$s', 'gohigh-page-builder' ), esc_attr( $rating ), esc_attr( $max_stars ) ); ?>"
				role="img"
			>
				<div class="ghpb-star-rating-stars">
					<?php for ( $i = 1; $i <= $max_stars; $i++ ) :
						$diff = $rating - ( $i - 1 );
						if ( $diff >= 1 ) {
							// Full star
							echo '<i class="' . esc_attr( $full_icon ) . ' ghpb-star-marked"></i>';
						} elseif ( $diff >= 0.5 ) {
							// Half star
							echo '<i class="' . esc_attr( $half_icon ) . ' ghpb-star-marked"></i>';
						} else {
							// Empty star
							echo '<i class="dashicons dashicons-star-empty ghpb-star-unmarked"></i>';
						}
					endfor; ?>
				</div>
				<?php if ( $title ) : ?>
					<span class="ghpb-star-rating-title"><?php echo esc_html( $title ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
