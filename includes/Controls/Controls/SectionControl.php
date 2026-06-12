<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class SectionControl extends Control {
	public function get_type(): string { return 'section'; }
	public function get_default_settings(): array {
		return [ 'tab' => 'content', 'label' => '', 'collapsed' => false ];
	}
}

class TabControl extends Control {
	public function get_type(): string { return 'tab'; }
	public function get_default_settings(): array {
		return [ 'label' => '', 'tab' => 'content' ];
	}
}

class TabsControl extends Control {
	public function get_type(): string { return 'tabs'; }
}

class HeadingControl extends Control {
	public function get_type(): string { return 'heading'; }
	public function get_default_settings(): array {
		return [ 'label' => '', 'separator' => 'default' ];
	}
}
