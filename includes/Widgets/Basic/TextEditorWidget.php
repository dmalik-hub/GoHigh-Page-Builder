<?php
namespace GoHigh\PageBuilder\Widgets\Basic;

use GoHigh\PageBuilder\Widgets\Widget;
use GoHigh\PageBuilder\Controls\ControlsManager;

defined( 'ABSPATH' ) || exit;

class TextEditorWidget extends Widget {

	public function get_name(): string    { return 'text-editor'; }
	public function get_title(): string   { return __( 'Text Editor', 'gohigh-page-builder' ); }
	public function get_icon(): string    { return 'dashicons dashicons-editor-paragraph'; }
	public function get_categories(): array { return [ 'basic' ]; }
	public function get_keywords(): array   { return [ 'text', 'editor', 'paragraph', 'content', 'wysiwyg' ]; }

	protected function _register_controls(): void {

		$this->start_controls_section( 'section_editor', [
			'label' => __( 'Text Editor', 'gohigh-page-builder' ),
		] );

		$this->add_control( 'editor', [
			'label'   => '',
			'type'    => ControlsManager::TEXTAREA,
			'rows'    => 12,
			'default' => __( '<p>Click here to start editing this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>', 'gohigh-page-builder' ),
			'dynamic' => [ 'active' => true ],
		] );

		$this->add_responsive_control( 'align', [
			'label'     => __( 'Text Alignment', 'gohigh-page-builder' ),
			'type'      => ControlsManager::CHOOSE,
			'options'   => [
				'left'    => [ 'title' => __( 'Left', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-editor-alignleft' ],
				'center'  => [ 'title' => __( 'Center', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-editor-aligncenter' ],
				'right'   => [ 'title' => __( 'Right', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-editor-alignright' ],
				'justify' => [ 'title' => __( 'Justify', 'gohigh-page-builder' ), 'icon' => 'dashicons dashicons-editor-justify' ],
			],
			'selectors' => [ '{{WRAPPER}}' => 'text-align: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', [
			'label' => __( 'Text Editor', 'gohigh-page-builder' ),
			'tab'   => ControlsManager::TAB_STYLE,
		] );

		$this->add_control( 'text_color', [
			'label'     => __( 'Text Color', 'gohigh-page-builder' ),
			'type'      => ControlsManager::COLOR,
			'selectors' => [ '{{WRAPPER}}' => 'color: {{VALUE}};' ],
		] );

		$this->add_group_control( 'typography', [
			'name'     => 'typography',
			'selector' => '{{WRAPPER}}',
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$content  = $settings['editor'] ?? '';
		?>
		<div class="ghpb-text-editor">
			<?php echo wp_kses_post( $content ); ?>
		</div>
		<?php
	}
}
