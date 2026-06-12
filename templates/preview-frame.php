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
		/* ─── Edit mode base ─────────────────────────────────────────────── */
		.ghpb-section.ghpb-edit-mode  { position: relative; }
		.ghpb-column.ghpb-edit-mode   { position: relative; min-height: 40px; }
		.ghpb-widget.ghpb-edit-mode   { position: relative; }

		/* ─── Hover & selection outlines ─────────────────────────────────── */
		.ghpb-section.ghpb-edit-mode:hover  { outline: 1px dashed rgba(108,99,255,.5); }
		.ghpb-widget.ghpb-edit-mode:hover   { outline: 2px solid rgba(108,99,255,.6); cursor: pointer; }
		.ghpb-column.ghpb-edit-mode         { outline: 1px dashed rgba(108,99,255,.2); }
		.ghpb-element-selected              { outline: 2px solid #6c63ff !important; }

		/* ─── Empty canvas ──────────────────────────────────────────────── */
		.ghpb-add-section-btn {
			display: flex; align-items: center; justify-content: center;
			padding: 40px 20px; cursor: pointer; color: #6c63ff;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif; font-size: 15px; gap: 8px;
			border: 2px dashed rgba(108,99,255,.35);
			background: rgba(108,99,255,.04); transition: background .2s; margin: 20px;
			border-radius: 8px;
		}
		.ghpb-add-section-btn:hover { background: rgba(108,99,255,.1); }

		/* ─── Drop zone ──────────────────────────────────────────────────── */
		.ghpb-drop-active {
			background: rgba(108,99,255,.12) !important;
			outline: 2px dashed #6c63ff !important;
			min-height: 80px !important;
		}
		.ghpb-widget-wrap { min-height: 30px; }

		/* ─── Floating toolbar ───────────────────────────────────────────── */
		#ghpb-float-bar {
			position: fixed; z-index: 99999; display: none;
			background: #6c63ff; color: #fff;
			border-radius: 4px 4px 0 0;
			padding: 0 6px; height: 28px;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
			font-size: 11px; align-items: center; gap: 3px;
			box-shadow: 0 -2px 8px rgba(0,0,0,.2);
			pointer-events: all; white-space: nowrap;
			transition: none;
		}
		.ghpb-fb-type {
			font-weight: 700; text-transform: capitalize;
			letter-spacing: .5px; padding-right: 6px;
			border-right: 1px solid rgba(255,255,255,.3); margin-right: 2px;
		}
		.ghpb-fb-btn {
			background: rgba(255,255,255,.12); border: none; color: #fff; cursor: pointer;
			width: 22px; height: 22px; border-radius: 3px;
			display: flex; align-items: center; justify-content: center;
			padding: 0; transition: background .15s; flex-shrink: 0;
		}
		.ghpb-fb-btn:hover { background: rgba(255,255,255,.3); }
		.ghpb-fb-btn .dashicons { font-size: 13px; width: 13px; height: 13px; line-height: 13px; }

		/* ─── Context menu ───────────────────────────────────────────────── */
		#ghpb-ctx-menu {
			position: fixed; z-index: 99998; display: none;
			background: #1e1e2e; border: 1px solid rgba(255,255,255,.1);
			border-radius: 8px; padding: 6px; min-width: 160px;
			box-shadow: 0 4px 20px rgba(0,0,0,.5);
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
		}
		.ghpb-ctx-item {
			padding: 7px 12px; cursor: pointer; border-radius: 5px;
			color: #cdd6f4; font-size: 13px;
			display: flex; align-items: center; gap: 8px; transition: background .12s;
		}
		.ghpb-ctx-item:hover { background: rgba(108,99,255,.2); color: #fff; }
		.ghpb-ctx-delete { color: #f38ba8 !important; border-top: 1px solid rgba(255,255,255,.08); margin-top: 4px; padding-top: 9px; }
		.ghpb-ctx-delete:hover { background: rgba(243,139,168,.15) !important; }
		.ghpb-ctx-item .dashicons { font-size: 14px; width: 14px; height: 14px; line-height: 14px; }

		/* ─── Add-section handle between sections ─────────────────────────── */
		.ghpb-add-between {
			display: flex; align-items: center; justify-content: center;
			height: 0; overflow: visible; position: relative; z-index: 10;
			opacity: 0; transition: opacity .2s;
		}
		.ghpb-add-between:hover, .ghpb-canvas:hover .ghpb-add-between { opacity: 1; }
		.ghpb-add-between-btn {
			background: #6c63ff; color: #fff; border: none; border-radius: 50%;
			width: 24px; height: 24px; cursor: pointer; font-size: 18px; line-height: 1;
			display: flex; align-items: center; justify-content: center;
			box-shadow: 0 2px 6px rgba(108,99,255,.5);
		}
	</style>
</head>
<body class="ghpb-preview-body<?php echo is_admin() ? '' : ' ' . implode( ' ', get_body_class() ); ?>">

<div id="ghpb-canvas" class="ghpb-canvas" data-post-id="<?php echo esc_attr( $post_id ); ?>">

	<?php if ( ! empty( $elements ) ) : ?>
		<?php
		$renderer = new \GoHigh\PageBuilder\Frontend\Renderer();
		$renderer->render( $elements );
		?>
	<?php else : ?>
		<div class="ghpb-add-section-btn" id="ghpb-empty-add-section">
			<span class="dashicons dashicons-plus-alt2"></span>
			<?php esc_html_e( 'Click to add your first section', 'gohigh-page-builder' ); ?>
		</div>
	<?php endif; ?>

</div><!-- #ghpb-canvas -->

<!-- Floating element toolbar -->
<div id="ghpb-float-bar">
	<span class="ghpb-fb-type"></span>
	<button class="ghpb-fb-btn" data-action="edit" title="Edit"><span class="dashicons dashicons-edit"></span></button>
	<button class="ghpb-fb-btn" data-action="duplicate" title="Duplicate"><span class="dashicons dashicons-admin-page"></span></button>
	<button class="ghpb-fb-btn" data-action="move-up" title="Move Up"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
	<button class="ghpb-fb-btn" data-action="move-down" title="Move Down"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
	<button class="ghpb-fb-btn" data-action="delete" title="Delete"><span class="dashicons dashicons-trash"></span></button>
</div>

<!-- Right-click context menu -->
<div id="ghpb-ctx-menu">
	<div class="ghpb-ctx-item" data-action="edit"><span class="dashicons dashicons-edit"></span> Edit</div>
	<div class="ghpb-ctx-item" data-action="duplicate"><span class="dashicons dashicons-admin-page"></span> Duplicate</div>
	<div class="ghpb-ctx-item ghpb-ctx-delete" data-action="delete"><span class="dashicons dashicons-trash"></span> Delete</div>
</div>

<?php wp_footer(); ?>

<script>
(function() {
	'use strict';

	var canvas  = document.getElementById('ghpb-canvas');
	var floatBar = document.getElementById('ghpb-float-bar');
	var ctxMenu  = document.getElementById('ghpb-ctx-menu');
	if (!canvas) return;

	var selectedId   = null;
	var hoveredEl    = null;
	var fbCurrentId  = null;
	var fbCurrentType = null;
	var ctxCurrentId = null;
	var ctxCurrentType = null;
	var floatBarTimer = null;

	// ── Helpers ────────────────────────────────────────────────────────────────
	function getElType(el) {
		if (!el) return 'widget';
		if (el.classList.contains('ghpb-section')) return 'section';
		if (el.classList.contains('ghpb-column')) return 'column';
		return 'widget';
	}

	// ── Edit mode ──────────────────────────────────────────────────────────────
	function initEditMode() {
		canvas.querySelectorAll('.ghpb-section, .ghpb-column, .ghpb-widget').forEach(function(el) {
			el.classList.add('ghpb-edit-mode');
		});
	}
	initEditMode();

	// ── Floating toolbar ───────────────────────────────────────────────────────
	function showFloatBar(el) {
		if (!floatBar) return;
		clearTimeout(floatBarTimer);
		var rect = el.getBoundingClientRect();
		fbCurrentId   = el.getAttribute('data-id');
		fbCurrentType = getElType(el);
		floatBar.querySelector('.ghpb-fb-type').textContent = fbCurrentType;
		floatBar.style.display = 'flex';
		var barTop = rect.top - 28;
		if (barTop < 0) barTop = rect.top; // clamp to viewport
		floatBar.style.top  = barTop + 'px';
		floatBar.style.left = rect.left + 'px';
		floatBar.style.minWidth = Math.min(rect.width, 200) + 'px';
	}

	function hideFloatBar(delay) {
		delay = delay || 0;
		floatBarTimer = setTimeout(function() {
			if (floatBar) floatBar.style.display = 'none';
			fbCurrentId = null;
		}, delay);
	}

	// ── Hover ──────────────────────────────────────────────────────────────────
	canvas.addEventListener('mousemove', function(e) {
		// Prefer widget > section (more specific)
		var el = e.target.closest('.ghpb-widget[data-id], .ghpb-section[data-id]');
		if (el !== hoveredEl) {
			hoveredEl = el;
			if (el) {
				showFloatBar(el);
			} else {
				hideFloatBar(150);
			}
		}
	});

	canvas.addEventListener('mouseleave', function() {
		hoveredEl = null;
		hideFloatBar(200);
	});

	if (floatBar) {
		floatBar.addEventListener('mouseenter', function() {
			clearTimeout(floatBarTimer);
		});
		floatBar.addEventListener('mouseleave', function() {
			hideFloatBar(100);
		});
	}

	// ── Floating toolbar button clicks ─────────────────────────────────────────
	if (floatBar) {
		floatBar.addEventListener('click', function(e) {
			var btn = e.target.closest('.ghpb-fb-btn[data-action]');
			if (!btn || !fbCurrentId) return;
			e.stopPropagation();
			var action = btn.getAttribute('data-action');
			var id = fbCurrentId;
			var type = fbCurrentType;
			hideFloatBar();

			if (action === 'edit') {
				window.parent.postMessage({ type: 'GHPB_ELEMENT_CLICK', id: id, elType: type }, '*');
			} else if (action === 'duplicate') {
				window.parent.postMessage({ type: 'GHPB_ELEMENT_DUPLICATE', id: id }, '*');
			} else if (action === 'delete') {
				window.parent.postMessage({ type: 'GHPB_ELEMENT_DELETE', id: id }, '*');
			} else if (action === 'move-up') {
				window.parent.postMessage({ type: 'GHPB_ELEMENT_MOVE', id: id, direction: 'up' }, '*');
			} else if (action === 'move-down') {
				window.parent.postMessage({ type: 'GHPB_ELEMENT_MOVE', id: id, direction: 'down' }, '*');
			}
		});
	}

	// ── Right-click context menu ───────────────────────────────────────────────
	canvas.addEventListener('contextmenu', function(e) {
		var el = e.target.closest('[data-id]');
		if (!el) { ctxMenu.style.display = 'none'; return; }
		e.preventDefault();
		ctxCurrentId   = el.getAttribute('data-id');
		ctxCurrentType = getElType(el);
		ctxMenu.style.display = 'block';
		// Keep inside viewport
		var x = e.clientX, y = e.clientY;
		if (x + 180 > window.innerWidth) x = window.innerWidth - 185;
		if (y + 120 > window.innerHeight) y = window.innerHeight - 125;
		ctxMenu.style.top  = y + 'px';
		ctxMenu.style.left = x + 'px';
	});

	document.addEventListener('click', function(e) {
		if (!ctxMenu.contains(e.target)) ctxMenu.style.display = 'none';
	});

	if (ctxMenu) {
		ctxMenu.addEventListener('click', function(e) {
			var item = e.target.closest('.ghpb-ctx-item[data-action]');
			if (!item || !ctxCurrentId) return;
			var action = item.getAttribute('data-action');
			var id     = ctxCurrentId;
			var type   = ctxCurrentType;
			ctxMenu.style.display = 'none';

			if (action === 'edit') {
				window.parent.postMessage({ type: 'GHPB_ELEMENT_CLICK', id: id, elType: type }, '*');
			} else if (action === 'duplicate') {
				window.parent.postMessage({ type: 'GHPB_ELEMENT_DUPLICATE', id: id }, '*');
			} else if (action === 'delete') {
				window.parent.postMessage({ type: 'GHPB_ELEMENT_DELETE', id: id }, '*');
			}
		});
	}

	// ── Click delegation ───────────────────────────────────────────────────────
	canvas.addEventListener('click', function(e) {
		// Don't process clicks on the float bar itself (it's outside canvas)
		var target = e.target.closest('[data-id]');
		if (!target) {
			window.parent.postMessage({ type: 'GHPB_CANVAS_CLICK' }, '*');
			return;
		}
		e.stopPropagation();
		var id     = target.getAttribute('data-id');
		var elType = getElType(target);
		window.parent.postMessage({ type: 'GHPB_ELEMENT_CLICK', id: id, elType: elType }, '*');
	});

	// ── Drag & drop from panel ─────────────────────────────────────────────────
	// Tracks whether a panel drag is active via postMessage
	var isDraggingFromPanel = false;

	window.addEventListener('message', function(e) {
		var msg = e.data;
		if (!msg || !msg.type) return;
		if (msg.type === 'GHPB_DRAG_START') isDraggingFromPanel = true;
		if (msg.type === 'GHPB_DRAG_END') isDraggingFromPanel = false;
	});

	canvas.addEventListener('dragenter', function(e) {
		e.preventDefault();
	});

	canvas.addEventListener('dragover', function(e) {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
		var zone = e.target.closest('.ghpb-widget-wrap');
		document.querySelectorAll('.ghpb-drop-active').forEach(function(z) {
			if (z !== zone) z.classList.remove('ghpb-drop-active');
		});
		if (zone) zone.classList.add('ghpb-drop-active');
	});

	canvas.addEventListener('dragleave', function(e) {
		// Only remove if leaving the actual drop zone (not to a child)
		var zone = e.target.closest('.ghpb-widget-wrap');
		if (zone && !zone.contains(e.relatedTarget)) {
			zone.classList.remove('ghpb-drop-active');
		}
	});

	canvas.addEventListener('drop', function(e) {
		e.preventDefault();
		document.querySelectorAll('.ghpb-drop-active').forEach(function(z) {
			z.classList.remove('ghpb-drop-active');
		});
		var widgetType = e.dataTransfer.getData('text/plain');
		if (!widgetType) return;
		var col      = e.target.closest('.ghpb-column[data-id]');
		var columnId = col ? col.getAttribute('data-id') : null;
		window.parent.postMessage({
			type:       'GHPB_WIDGET_DROP',
			widgetType: widgetType,
			columnId:   columnId,
		}, '*');
	});

	// ── Keyboard shortcuts ─────────────────────────────────────────────────────
	document.addEventListener('keydown', function(e) {
		var active = document.activeElement;
		var inInput = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable);
		if (inInput) return;

		if ((e.key === 'Delete' || e.key === 'Backspace') && selectedId) {
			window.parent.postMessage({ type: 'GHPB_ELEMENT_DELETE', id: selectedId }, '*');
		}
		if (e.key === 'Escape') {
			window.parent.postMessage({ type: 'GHPB_CANVAS_CLICK' }, '*');
		}
	});

	// ── Empty canvas button ────────────────────────────────────────────────────
	var emptyBtn = document.getElementById('ghpb-empty-add-section');
	if (emptyBtn) {
		emptyBtn.addEventListener('click', function() {
			window.parent.postMessage({ type: 'GHPB_ADD_SECTION_REQUEST' }, '*');
		});
	}

	// ── Message bridge ─────────────────────────────────────────────────────────
	window.addEventListener('message', function(e) {
		var msg = e.data;
		if (!msg || !msg.type) return;

		switch (msg.type) {
			case 'GHPB_SELECT_ELEMENT':
				document.querySelectorAll('.ghpb-element-selected').forEach(function(el) {
					el.classList.remove('ghpb-element-selected');
				});
				selectedId = msg.id || null;
				if (msg.id) {
					var el = canvas.querySelector('[data-id="' + msg.id + '"]');
					if (el) el.classList.add('ghpb-element-selected');
				}
				break;

			case 'GHPB_UPDATE_STYLES':
				var styleEl = document.getElementById('ghpb-live-styles');
				if (styleEl && msg.css !== undefined) styleEl.innerHTML = msg.css;
				break;

			case 'GHPB_RELOAD':
			case 'GHPB_CANVAS_REFRESH':
				window.location.reload();
				break;
		}
	});

})();
</script>
</body>
</html>
