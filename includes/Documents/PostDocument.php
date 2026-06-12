<?php
namespace GoHigh\PageBuilder\Documents;

defined( 'ABSPATH' ) || exit;

class PostDocument extends Document {
	public function get_name(): string { return 'post'; }
}
