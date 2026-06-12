<?php
namespace GoHigh\PageBuilder\Documents;

defined( 'ABSPATH' ) || exit;

class DocumentsManager {

	private array $document_types = [];

	public function __construct() {
		$this->register_document_types();
	}

	private function register_document_types(): void {
		$this->register( 'page', PageDocument::class );
		$this->register( 'post', PostDocument::class );
		do_action( 'gohigh/documents/register', $this );
	}

	public function register( string $type, string $class ): void {
		$this->document_types[ $type ] = $class;
	}

	public function get( int $post_id ): ?Document {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}
		$class = $this->document_types[ $post->post_type ] ?? Document\PageDocument::class;
		if ( ! class_exists( $class ) ) {
			$class = PageDocument::class;
		}
		return new $class( $post_id );
	}

	public function get_document_types(): array {
		return $this->document_types;
	}
}
