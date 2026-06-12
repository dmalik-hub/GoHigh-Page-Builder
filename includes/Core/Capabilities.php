<?php
namespace GoHigh\PageBuilder\Core;

defined( 'ABSPATH' ) || exit;

class Capabilities {

	public function init(): void {
		add_filter( 'user_has_cap', [ $this, 'filter_user_has_cap' ], 10, 3 );
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 200 );
		add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );
		add_filter( 'page_row_actions', [ $this, 'post_row_actions' ], 10, 2 );
	}

	public function filter_user_has_cap( array $allcaps, array $caps, array $args ): array {
		return $allcaps;
	}

	public function admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_singular() || ! current_user_can( 'edit_post', get_the_ID() ) ) {
			return;
		}
		$post_id = get_the_ID();
		$wp_admin_bar->add_node( [
			'id'    => 'gohigh-edit-with-builder',
			'title' => __( 'Edit with GoHigh', 'gohigh-page-builder' ),
			'href'  => $this->get_editor_url( $post_id ),
			'meta'  => [ 'class' => 'gohigh-admin-bar-edit' ],
		] );
	}

	public function post_row_actions( array $actions, \WP_Post $post ): array {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}
		$actions['gohigh_edit'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $this->get_editor_url( $post->ID ) ),
			esc_html__( 'Edit with GoHigh', 'gohigh-page-builder' )
		);
		return $actions;
	}

	private function get_editor_url( int $post_id ): string {
		return admin_url( 'admin.php?action=gohigh_editor&post=' . $post_id );
	}
}
