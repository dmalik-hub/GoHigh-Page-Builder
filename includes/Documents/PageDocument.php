<?php
namespace GoHigh\PageBuilder\Documents;

defined( 'ABSPATH' ) || exit;

class PageDocument extends Document {
	public function get_name(): string { return 'page'; }
}
