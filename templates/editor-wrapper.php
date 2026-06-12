<?php
/**
 * Full-screen editor page template.
 * Loaded via admin.php?page=gohigh-editor&post=X
 */
defined( 'ABSPATH' ) || exit;

$post_id = absint( $_GET['post'] ?? 0 );
$post    = $post_id ? get_post( $post_id ) : null;

if ( ! $post ) {
	wp_die( esc_html__( 'Post not found.', 'gohigh-page-builder' ) );
}

// Load editor assets
do_action( 'admin_enqueue_scripts', 'admin_page_gohigh-editor' );

$preview_url = add_query_arg( [
	'ghpb-preview' => '1',
	'post'         => $post_id,
	'nonce'        => wp_create_nonce( 'ghpb_preview' ),
], home_url() );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $post->post_title ); ?> — <?php esc_html_e( 'GoHigh Editor', 'gohigh-page-builder' ); ?></title>
	<?php wp_head(); ?>
	<style>
		html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; background: #1e1e2e; }
		#wpwrap, #wpcontent, #wpbody, #wpbody-content { height: 100%; }
		#wpadminbar { display: none; }
		.ghpb-editor-loading {
			position: fixed; inset: 0; z-index: 99999;
			display: flex; align-items: center; justify-content: center;
			background: #1e1e2e; color: #fff; flex-direction: column; gap: 16px;
		}
		.ghpb-editor-loading-logo { font-size: 32px; font-weight: 700; letter-spacing: -1px; }
		.ghpb-editor-loading-spinner {
			width: 32px; height: 32px; border: 3px solid rgba(255,255,255,.2);
			border-top-color: #6c63ff; border-radius: 50%; animation: spin .8s linear infinite;
		}
		@keyframes spin { to { transform: rotate(360deg); } }
	</style>
</head>
<body class="ghpb-editor-page wp-admin">

<div id="ghpb-loading-screen" class="ghpb-editor-loading">
	<div class="ghpb-editor-loading-logo">GoHigh</div>
	<div class="ghpb-editor-loading-spinner"></div>
	<span><?php esc_html_e( 'Loading Editor…', 'gohigh-page-builder' ); ?></span>
</div>

<div id="ghpb-editor" class="ghpb-editor" style="display:none;">

	<!-- Toolbar -->
	<div id="ghpb-toolbar" class="ghpb-toolbar">
		<div class="ghpb-toolbar-left">
			<button id="ghpb-panel-toggle" class="ghpb-toolbar-btn" title="<?php esc_attr_e( 'Toggle Panel', 'gohigh-page-builder' ); ?>">
				<span class="dashicons dashicons-menu"></span>
			</button>
			<a href="<?php echo esc_url( admin_url() ); ?>" class="ghpb-toolbar-logo" title="<?php esc_attr_e( 'Exit to Dashboard', 'gohigh-page-builder' ); ?>">
				<span class="ghpb-logo-text">GoHigh</span>
			</a>
		</div>

		<div class="ghpb-toolbar-center">
			<div id="ghpb-responsive-switcher" class="ghpb-responsive-switcher">
				<button class="ghpb-responsive-btn active" data-device="desktop" title="<?php esc_attr_e( 'Desktop', 'gohigh-page-builder' ); ?>">
					<span class="dashicons dashicons-desktop"></span>
				</button>
				<button class="ghpb-responsive-btn" data-device="tablet" title="<?php esc_attr_e( 'Tablet', 'gohigh-page-builder' ); ?>">
					<span class="dashicons dashicons-tablet"></span>
				</button>
				<button class="ghpb-responsive-btn" data-device="mobile" title="<?php esc_attr_e( 'Mobile', 'gohigh-page-builder' ); ?>">
					<span class="dashicons dashicons-smartphone"></span>
				</button>
			</div>
		</div>

		<div class="ghpb-toolbar-right">
			<button id="ghpb-undo-btn" class="ghpb-toolbar-btn" disabled title="<?php esc_attr_e( 'Undo', 'gohigh-page-builder' ); ?>">
				<span class="dashicons dashicons-undo"></span>
			</button>
			<button id="ghpb-redo-btn" class="ghpb-toolbar-btn" disabled title="<?php esc_attr_e( 'Redo', 'gohigh-page-builder' ); ?>">
				<span class="dashicons dashicons-redo"></span>
			</button>
			<button id="ghpb-preview-btn" class="ghpb-toolbar-btn" title="<?php esc_attr_e( 'Preview', 'gohigh-page-builder' ); ?>">
				<span class="dashicons dashicons-visibility"></span>
			</button>
			<button id="ghpb-save-btn" class="ghpb-toolbar-btn ghpb-save-btn" title="<?php esc_attr_e( 'Save', 'gohigh-page-builder' ); ?>">
				<span><?php esc_html_e( 'Update', 'gohigh-page-builder' ); ?></span>
			</button>
		</div>
	</div>

	<!-- Main editor area -->
	<div id="ghpb-editor-main" class="ghpb-editor-main">

		<!-- Left panel -->
		<div id="ghpb-panel" class="ghpb-panel">
			<div class="ghpb-panel-inner">
				<div id="ghpb-panel-header" class="ghpb-panel-header">
					<div id="ghpb-panel-header-kit" class="ghpb-panel-header-kit">
						<button id="ghpb-back-btn" class="ghpb-panel-back" style="display:none;">
							<span class="dashicons dashicons-arrow-left-alt"></span>
						</button>
						<span id="ghpb-panel-title" class="ghpb-panel-title">
							<?php esc_html_e( 'Elements', 'gohigh-page-builder' ); ?>
						</span>
					</div>
				</div>

				<div id="ghpb-panel-content" class="ghpb-panel-content">
					<!-- Filled by JS -->
				</div>
			</div>
		</div>

		<!-- Preview iframe area -->
		<div id="ghpb-preview-area" class="ghpb-preview-area">
			<div id="ghpb-preview-device-wrapper" class="ghpb-preview-device-wrapper" data-device="desktop">
				<iframe
					id="ghpb-preview-frame"
					class="ghpb-preview-frame"
					src="<?php echo esc_url( $preview_url ); ?>"
					frameborder="0"
				></iframe>
			</div>
		</div>

	</div><!-- .ghpb-editor-main -->

</div><!-- #ghpb-editor -->

<?php wp_footer(); ?>

<script>
window.ghpbPreviewUrl = <?php echo wp_json_encode( $preview_url ); ?>;
</script>
</body>
</html>
