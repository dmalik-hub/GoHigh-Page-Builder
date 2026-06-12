<?php
/**
 * Preview frame template — loaded inside the editor iframe.
 * URL: ?ghpb-preview=1&post=X
 *
 * Loads the WordPress theme + frontend assets so the builder preview
 * matches the live frontend exactly, plus editor-canvas JS.
 */
defined( 'ABSPATH' ) || exit;

$post_id = absint( $_GET['post'] ?? 0 );
if ( ! $post_id ) {
	wp_die( 'No post ID.' );
}

$raw      = get_post_meta( $post_id, '_gohigh_data', true );
$data     = $raw ? json_decode( $raw, true ) : [];
$elements = is_array( $data ) ? ( $data['content'] ?? [] ) : [];
$css      = get_post_meta( $post_id, '_gohigh_data_css', true );

// We need WordPress CSS + theme CSS in the iframe.
// The simplest correct approach: output a minimal HTML document that loads
// wp_enqueue_scripts styles and our frontend styles.
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( get_the_title( $post_id ) ); ?></title>
	<?php wp_head(); ?>
	<?php if ( $css ) : ?>
		<style id="ghpb-live-styles"><?php echo wp_strip_all_tags( $css ); // phpcs:ignore ?></style>
	<?php else : ?>
		<style id="ghpb-live-styles"></style>
	<?php endif; ?>
	<style>
		/* Editor canvas helpers */
		.ghpb-section.ghpb-edit-mode { outline: 1px dashed rgba(108,99,255,.4); }
		.ghpb-column.ghpb-edit-mode  { outline: 1px dashed rgba(108,99,255,.2); min-height: 40px; }
		.ghpb-widget.ghpb-edit-mode  { outline: 1px dashed transparent; }
		.ghpb-widget.ghpb-edit-mode:hover  { outline-color: rgba(108,99,255,.6); cursor: pointer; }
		.ghpb-element-selected { outline: 2px solid #6c63ff !important; }
		.ghpb-element-hovered  { outline: 2px dashed #6c63ff !important; }
		.ghpb-widget-placeholder {
			min-height: 60px; background: rgba(108,99,255,.05);
			display: flex; align-items: center; justify-content: center;
			color: #9a91f0; border: 2px dashed rgba(108,99,255,.3);
			font-family: sans-serif; font-size: 13px;
		}
		.ghpb-add-section-btn {
			display: flex; align-items: center; justify-content: center;
			padding: 20px; cursor: pointer; color: #6c63ff;
			font-family: sans-serif; font-size: 14px; gap: 8px;
			border: 2px dashed rgba(108,99,255,.3);
			background: rgba(108,99,255,.04); transition: background .2s;
		}
		.ghpb-add-section-btn:hover { background: rgba(108,99,255,.1); }
		.ghpb-section-handle, .ghpb-column-handle, .ghpb-widget-handle {
			position: absolute; z-index: 9; display: none;
			background: #6c63ff; color: #fff;
			padding: 3px 8px; font-size: 11px; border-radius: 4px;
			font-family: sans-serif; gap: 4px; align-items: center;
			white-space: nowrap;
		}
		.ghpb-element-selected > .ghpb-section-handle,
		.ghpb-element-selected > .ghpb-column-wrap > .ghpb-column-handle,
		.ghpb-element-selected > .ghpb-widget-container > .ghpb-widget-handle { display: flex; }
	</style>
</head>
<body class="ghpb-preview-body<?php echo is_admin() ? '' : ' ' . join( ' ', get_body_class() ); ?>">

<div id="ghpb-canvas" class="ghpb-canvas" data-post-id="<?php echo esc_attr( $post_id ); ?>">

	<?php if ( ! empty( $elements ) ) : ?>
		<?php
		$renderer = new \GoHigh\PageBuilder\Frontend\Renderer();
		$renderer->render( $elements );
		?>
	<?php else : ?>
		<div class="ghpb-add-section-btn" id="ghpb-empty-add-section">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'Click to add a section', 'gohigh-page-builder' ); ?>
		</div>
	<?php endif; ?>

</div><!-- #ghpb-canvas -->

<?php wp_footer(); ?>

<script>
// Canvas bridge: notify parent editor when elements are clicked.
(function() {
	var canvas = document.getElementById('ghpb-canvas');
	if (!canvas) return;

	// Mark all elements as editable.
	var sections = canvas.querySelectorAll('.ghpb-section, .ghpb-column, .ghpb-widget');
	sections.forEach(function(el) { el.classList.add('ghpb-edit-mode'); });

	// Click delegation.
	canvas.addEventListener('click', function(e) {
		var target = e.target.closest('[data-id]');
		if (!target) {
			window.parent.postMessage({ type: 'GHPB_CANVAS_CLICK' }, '*');
			return;
		}
		e.stopPropagation();
		var id = target.getAttribute('data-id');
		var elType = target.classList.contains('ghpb-section') ? 'section'
				   : target.classList.contains('ghpb-column') ? 'column' : 'widget';
		window.parent.postMessage({ type: 'GHPB_ELEMENT_CLICK', id: id, elType: elType }, '*');
	});

	// Receive messages from parent.
	window.addEventListener('message', function(e) {
		var msg = e.data;
		if (!msg || !msg.type) return;

		switch(msg.type) {
			case 'GHPB_SELECT_ELEMENT':
				document.querySelectorAll('.ghpb-element-selected').forEach(function(el) {
					el.classList.remove('ghpb-element-selected');
				});
				if (msg.id) {
					var el = document.querySelector('[data-id="' + msg.id + '"]');
					if (el) el.classList.add('ghpb-element-selected');
				}
				break;

			case 'GHPB_UPDATE_STYLES':
				var styleEl = document.getElementById('ghpb-live-styles');
				if (styleEl && msg.css) styleEl.innerHTML = msg.css;
				break;

			case 'GHPB_RELOAD':
				window.location.reload();
				break;
		}
	});

	// Empty canvas click.
	var emptyBtn = document.getElementById('ghpb-empty-add-section');
	if (emptyBtn) {
		emptyBtn.addEventListener('click', function() {
			window.parent.postMessage({ type: 'GHPB_ADD_SECTION_REQUEST' }, '*');
		});
	}
})();
</script>
</body>
</html>
