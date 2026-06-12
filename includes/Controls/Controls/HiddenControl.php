<?php
namespace GoHigh\PageBuilder\Controls\Controls;

use GoHigh\PageBuilder\Controls\Control;

defined( 'ABSPATH' ) || exit;

class HiddenControl extends Control {
	public function get_type(): string { return 'hidden'; }
}
