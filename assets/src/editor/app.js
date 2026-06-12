import Backbone from 'backbone';
import Marionette from 'backbone.marionette';
import * as Channels from './channels/channels.js';
import DocumentModel from './models/document.js';
import api from './utils/api.js';
import { createSectionWithColumns, createWidget, deepClone, generateId, generateCSS } from './utils/helpers.js';

import ElementsPanelView from './views/panel/elements/elements-panel.js';
import EditPanelView from './views/panel/editor/edit-panel.js';

const EditorApp = Marionette.Application.extend( {
	region: '#ghpb-editor',

	initialize( options = {} ) {
		this.config       = window.ghpbEditorConfig || {};
		this.postId       = this.config.postId || 0;
		this.widgetTypes  = this.config.widgetTypes || {};
		this.i18n         = this.config.i18n || {};
		this.device       = 'desktop';
		this.document     = new DocumentModel( { id: this.postId } );
		this.selectedId   = null;
		this.previewFrame = null;
		this._autosaveTimer = null;
	},

	onStart() {
		this._loadDocument();
		this._initToolbar();
		this._initPreviewFrame();
		this._initChannels();
		this._initKeyboardShortcuts();
	},

	// ── Data loading ──────────────────────────────────────────────────────────

	async _loadDocument() {
		try {
			const data = await api.getDocument( this.postId );
			this.document.set( {
				id:            data.id,
				title:         data.title,
				status:        data.status,
				elements:      data.elements || [],
				page_settings: data.page_settings || {},
				css:           data.css || '',
			} );
			this.document.markClean();

			document.getElementById( 'ghpb-loading-screen' ).style.display = 'none';
			document.getElementById( 'ghpb-editor' ).style.display = 'flex';

			this._renderPanel();
			this._updateHistoryButtons();

		} catch ( err ) {
			console.error( '[GoHigh] Failed to load document:', err );
			this._showLoadError( err );
		}
	},

	_showLoadError( err ) {
		const loading = document.getElementById( 'ghpb-loading-screen' );
		if ( ! loading ) return;
		const message = ( err && err.message ) ? err.message : 'Unknown error';
		loading.innerHTML = `
			<div style="text-align:center; max-width:480px; padding:32px;">
				<div style="font-size:48px; margin-bottom:16px;">⚠️</div>
				<h2 style="margin:0 0 12px; color:#fff;">Couldn't load the editor</h2>
				<p style="color:#cdd6f4; margin:0 0 8px; font-size:14px;">${ message }</p>
				<p style="color:#8890b0; font-size:13px; margin:16px 0;">
					Open your browser's developer console (F12) and look at the
					Console + Network tabs for more detail.
				</p>
				<a href="${ window.ghpbEditorConfig?.adminUrl || '/wp-admin/' }"
				   style="display:inline-block; margin-top:16px; padding:8px 18px;
				          background:#6c63ff; color:#fff; text-decoration:none;
				          border-radius:6px; font-size:13px;">
					Back to Dashboard
				</a>
			</div>
		`;
	},

	// ── Panel ─────────────────────────────────────────────────────────────────

	_renderPanel() {
		const panelContent = document.getElementById( 'ghpb-panel-content' );
		if ( ! panelContent ) return;
		this._showElementsPanel();
	},

	_showElementsPanel() {
		const panelContent = document.getElementById( 'ghpb-panel-content' );
		panelContent.innerHTML = '';

		document.getElementById( 'ghpb-panel-title' ).textContent = this.i18n.addElement || 'Elements';
		document.getElementById( 'ghpb-back-btn' ).style.display = 'none';

		const view = new ElementsPanelView( {
			widgetTypes: this.widgetTypes,
			onDragStart: ( widgetType ) => this._onPanelDragStart( widgetType ),
		} );
		view.render();
		panelContent.appendChild( view.el );
	},

	// ── Layout picker ─────────────────────────────────────────────────────────

	_showLayoutPicker() {
		const panelContent = document.getElementById( 'ghpb-panel-content' );
		panelContent.innerHTML = '';

		document.getElementById( 'ghpb-panel-title' ).textContent = 'Choose Structure';
		document.getElementById( 'ghpb-back-btn' ).style.display = 'flex';

		const layouts = [
			{ columns: [ 100 ],                    label: '1 Column' },
			{ columns: [ 50, 50 ],                  label: '2 Columns' },
			{ columns: [ 33.333, 66.666 ],           label: '1/3 + 2/3' },
			{ columns: [ 66.666, 33.333 ],           label: '2/3 + 1/3' },
			{ columns: [ 33.333, 33.333, 33.333 ],   label: '3 Columns' },
			{ columns: [ 25, 25, 25, 25 ],           label: '4 Columns' },
			{ columns: [ 25, 50, 25 ],               label: 'Side + Center' },
			{ columns: [ 20, 20, 20, 20, 20 ],       label: '5 Columns' },
		];

		const grid = document.createElement( 'div' );
		grid.className = 'ghpb-layout-picker';
		grid.innerHTML = `<p class="ghpb-layout-hint">Choose the column structure for this section</p><div class="ghpb-layout-grid"></div>`;

		const gridEl = grid.querySelector( '.ghpb-layout-grid' );
		layouts.forEach( layout => {
			const btn = document.createElement( 'button' );
			btn.className = 'ghpb-layout-option';
			btn.innerHTML = `
				<div class="ghpb-layout-preview">
					${ layout.columns.map( w => `<div class="ghpb-layout-col" style="flex:${w}"></div>` ).join( '' ) }
				</div>
				<span class="ghpb-layout-label">${ layout.label }</span>
			`;
			btn.addEventListener( 'click', () => {
				this._addSectionWithLayout( layout.columns );
			} );
			gridEl.appendChild( btn );
		} );

		panelContent.appendChild( grid );
	},

	_addSectionWithLayout( columnWidths ) {
		const section = createSectionWithColumns( columnWidths );
		this.document.addSection( section );
		this._reloadPreview();
		this._showElementsPanel();
	},

	// ── Section / Column editing ──────────────────────────────────────────────

	_getSectionControls() {
		return {
			title: 'Section',
			controls: [
				{ type: 'section', name: 'sec_layout', label: 'Layout', tab: 'content' },
				{ type: 'select', name: 'layout', label: 'Content Width', default: 'boxed',
					options: { boxed: 'Boxed', full_width: 'Full Width' }, selectors: {} },
				{ type: 'select', name: 'tag', label: 'HTML Tag', default: 'section',
					options: { section: '&lt;section&gt;', div: '&lt;div&gt;', header: '&lt;header&gt;', footer: '&lt;footer&gt;', article: '&lt;article&gt;' }, selectors: {} },

				{ type: 'section', name: 'sec_bg', label: 'Background', tab: 'style' },
				{ type: 'color', name: 'background_color', label: 'Background Color',
					selectors: { '{{WRAPPER}}': 'background-color: {{VALUE}};' } },

				{ type: 'section', name: 'sec_spacing', label: 'Spacing', tab: 'style' },
				{ type: 'dimensions', name: 'padding', label: 'Padding', responsive: true,
					selectors: { '{{WRAPPER}}': 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};' } },
				{ type: 'dimensions', name: 'margin', label: 'Margin', responsive: true,
					selectors: { '{{WRAPPER}}': 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};' } },

				{ type: 'section', name: 'sec_border', label: 'Border', tab: 'style' },
				{ type: 'select', name: 'border_type', label: 'Border Type', default: '',
					options: { '': 'None', solid: 'Solid', dashed: 'Dashed', dotted: 'Dotted', double: 'Double' },
					selectors: { '{{WRAPPER}}': 'border-style: {{VALUE}};' } },
				{ type: 'slider', name: 'border_width', label: 'Border Width', default: { size: 1, unit: 'px' },
					size_units: [ 'px' ], range: { px: { min: 0, max: 20 } },
					selectors: { '{{WRAPPER}}': 'border-width: {{SIZE}}{{UNIT}};' } },
				{ type: 'color', name: 'border_color', label: 'Border Color',
					selectors: { '{{WRAPPER}}': 'border-color: {{VALUE}};' } },
				{ type: 'slider', name: 'border_radius', label: 'Border Radius', default: { size: 0, unit: 'px' },
					size_units: [ 'px', '%' ], range: { px: { min: 0, max: 200 } },
					selectors: { '{{WRAPPER}}': 'border-radius: {{SIZE}}{{UNIT}};' } },

				{ type: 'section', name: 'sec_adv', label: 'Advanced', tab: 'advanced' },
				{ type: 'text', name: '_element_id', label: 'CSS ID', selectors: {} },
				{ type: 'text', name: '_css_classes', label: 'CSS Classes', selectors: {} },
				{ type: 'code', name: '_custom_css', label: 'Custom CSS', selectors: {} },
			],
		};
	},

	_getColumnControls() {
		return {
			title: 'Column',
			controls: [
				{ type: 'section', name: 'col_bg', label: 'Background', tab: 'style' },
				{ type: 'color', name: 'background_color', label: 'Background Color',
					selectors: { '{{WRAPPER}}': 'background-color: {{VALUE}};' } },

				{ type: 'section', name: 'col_spacing', label: 'Spacing', tab: 'style' },
				{ type: 'dimensions', name: 'padding', label: 'Padding', responsive: true,
					selectors: { '{{WRAPPER}} > .ghpb-column-wrap': 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};' } },

				{ type: 'section', name: 'col_adv', label: 'Advanced', tab: 'advanced' },
				{ type: 'text', name: '_css_classes', label: 'CSS Classes', selectors: {} },
			],
		};
	},

	_showSectionEditPanel( elementId ) {
		const element = this.document.getElement( elementId );
		if ( ! element ) return;
		const panelContent = document.getElementById( 'ghpb-panel-content' );
		panelContent.innerHTML = '';
		document.getElementById( 'ghpb-panel-title' ).textContent = 'Edit Section';
		document.getElementById( 'ghpb-back-btn' ).style.display = 'flex';
		const view = new EditPanelView( {
			element,
			widgetConfig: this._getSectionControls(),
			onChange: ( controlKey, value ) => this._onControlChange( elementId, controlKey, value ),
		} );
		view.render();
		panelContent.appendChild( view.el );
	},

	_showColumnEditPanel( elementId ) {
		const element = this.document.getElement( elementId );
		if ( ! element ) return;
		const panelContent = document.getElementById( 'ghpb-panel-content' );
		panelContent.innerHTML = '';
		document.getElementById( 'ghpb-panel-title' ).textContent = 'Edit Column';
		document.getElementById( 'ghpb-back-btn' ).style.display = 'flex';
		const view = new EditPanelView( {
			element,
			widgetConfig: this._getColumnControls(),
			onChange: ( controlKey, value ) => this._onControlChange( elementId, controlKey, value ),
		} );
		view.render();
		panelContent.appendChild( view.el );
	},

	_showEditPanel( elementId ) {
		const element = this.document.getElement( elementId );
		if ( ! element ) return;

		// Route to the right panel based on element type
		if ( element.elType === 'section' ) { this._showSectionEditPanel( elementId ); return; }
		if ( element.elType === 'column' )  { this._showColumnEditPanel( elementId );  return; }

		const widgetConfig = element.widgetType ? this.widgetTypes[ element.widgetType ] : null;
		if ( ! widgetConfig ) { this._showElementsPanel(); return; }

		const panelContent = document.getElementById( 'ghpb-panel-content' );
		panelContent.innerHTML = '';
		document.getElementById( 'ghpb-panel-title' ).textContent = widgetConfig.title || 'Edit';
		document.getElementById( 'ghpb-back-btn' ).style.display = 'flex';

		const view = new EditPanelView( {
			element,
			widgetConfig,
			onChange: ( controlKey, value ) => this._onControlChange( elementId, controlKey, value ),
		} );
		view.render();
		panelContent.appendChild( view.el );
	},

	// ── Control changes ────────────────────────────────────────────────────────

	_onControlChange( elementId, controlKey, value ) {
		this.document.snapshot( 'Change' );
		this.document.updateElementSettings( elementId, { [ controlKey ]: value } );
		this._pushLiveStyles();
		this._scheduleAutosave();
	},

	_pushLiveStyles() {
		this._sendToPreview( { type: 'GHPB_UPDATE_STYLES', css: this._buildFullCSS() } );
	},

	_buildFullCSS() {
		let css = '';
		const walk = ( elements ) => {
			for ( const el of elements ) {
				// Get element's own selectors config
				let controls = [];
				if ( el.widgetType && this.widgetTypes[ el.widgetType ] ) {
					controls = this.widgetTypes[ el.widgetType ].controls || [];
				} else if ( el.elType === 'section' ) {
					controls = this._getSectionControls().controls;
				} else if ( el.elType === 'column' ) {
					controls = this._getColumnControls().controls;
				}
				css += generateCSS( el.id, controls, el.settings || {}, this.device );
				if ( el.elements ) walk( el.elements );
			}
		};
		walk( this.document.get( 'elements' ) );
		return css;
	},

	// ── Preview frame ─────────────────────────────────────────────────────────

	_initPreviewFrame() {
		const iframe = document.getElementById( 'ghpb-preview-frame' );
		if ( ! iframe ) return;
		this.previewFrame = iframe;
		window.addEventListener( 'message', ( e ) => this._onPreviewMessage( e ) );
	},

	_onPreviewMessage( event ) {
		const msg = event.data;
		if ( ! msg || ! msg.type ) return;

		switch ( msg.type ) {
			case 'GHPB_ELEMENT_CLICK':
				this._selectElement( msg.id, msg.elType );
				break;

			case 'GHPB_CANVAS_CLICK':
				this._deselectElement();
				break;

			case 'GHPB_ADD_SECTION_REQUEST':
				this._showLayoutPicker();
				break;

			case 'GHPB_WIDGET_DROP':
				this._addWidget( msg.widgetType, msg.columnId );
				break;

			case 'GHPB_ELEMENT_DELETE':
				this._deleteElement( msg.id );
				break;

			case 'GHPB_ELEMENT_DUPLICATE':
				this.document.duplicateElement( msg.id );
				this._reloadPreview();
				break;

			case 'GHPB_ELEMENT_MOVE':
				this._moveElement( msg.id, msg.direction );
				break;
		}
	},

	_sendToPreview( message ) {
		if ( this.previewFrame && this.previewFrame.contentWindow ) {
			this.previewFrame.contentWindow.postMessage( message, '*' );
		}
	},

	// ── Element selection ────────────────────────────────────────────────────

	_selectElement( elementId, elType ) {
		this.selectedId = elementId;
		this._sendToPreview( { type: 'GHPB_SELECT_ELEMENT', id: elementId } );
		this._showEditPanel( elementId );
	},

	_deselectElement() {
		this.selectedId = null;
		this._sendToPreview( { type: 'GHPB_SELECT_ELEMENT', id: null } );
		this._showElementsPanel();
	},

	// ── DnD from panel ────────────────────────────────────────────────────────

	_onPanelDragStart( widgetType ) {
		this._pendingDragType = widgetType;
		// Notify iframe that a drag has started (used for drop-zone activation)
		this._sendToPreview( { type: 'GHPB_DRAG_START', widgetType } );
	},

	// ── Element mutations ─────────────────────────────────────────────────────

	_addWidget( widgetType, columnId ) {
		const widget = createWidget( widgetType );

		if ( ! columnId ) {
			// No column — create a new section with the widget
			const section = createSectionWithColumns( [ 100 ] );
			section.elements[ 0 ].elements.push( widget );
			this.document.addSection( section );
		} else {
			this.document.addWidget( null, columnId, widget );
		}
		this._reloadPreview();
	},

	_deleteElement( elementId ) {
		this.document.removeElement( elementId );
		if ( this.selectedId === elementId ) {
			this.selectedId = null;
			this._showElementsPanel();
		}
		this._reloadPreview();
	},

	_moveElement( elementId, direction ) {
		const elements = deepClone( this.document.get( 'elements' ) );

		const moveInArray = ( arr ) => {
			for ( let i = 0; i < arr.length; i++ ) {
				if ( arr[ i ].id === elementId ) {
					if ( direction === 'up' && i > 0 ) {
						[ arr[ i - 1 ], arr[ i ] ] = [ arr[ i ], arr[ i - 1 ] ];
						return true;
					}
					if ( direction === 'down' && i < arr.length - 1 ) {
						[ arr[ i ], arr[ i + 1 ] ] = [ arr[ i + 1 ], arr[ i ] ];
						return true;
					}
					return false;
				}
				if ( arr[ i ].elements && moveInArray( arr[ i ].elements ) ) return true;
			}
			return false;
		};

		if ( moveInArray( elements ) ) {
			this.document.snapshot( 'Move Element' );
			this.document.set( 'elements', elements );
			this._reloadPreview();
		}
	},

	// ── Preview refresh ───────────────────────────────────────────────────────

	_reloadPreview() {
		const elements     = this.document.get( 'elements' );
		const pageSettings = this.document.get( 'page_settings' );

		this._showStatus( 'saving' );

		api.saveDocument( this.postId, elements, pageSettings, this.document.get( 'status' ) || 'publish' )
			.then( () => {
				this._showStatus( 'saved' );
				if ( this.previewFrame ) {
					this.previewFrame.contentWindow.location.reload();
				}
			} )
			.catch( err => {
				console.error( '[GoHigh] Save failed:', err );
				this._showStatus( 'error', err.message );
				alert( 'GoHigh save failed: ' + ( err.message || 'Unknown error' ) + '\n\nOpen the browser console (F12) for full detail.' );
			} );
	},

	_showStatus( state ) {
		const saveBtn = document.getElementById( 'ghpb-save-btn' );
		if ( ! saveBtn ) return;
		const span = saveBtn.querySelector( 'span' );
		if ( ! span ) return;
		switch ( state ) {
			case 'saving': span.textContent = this.i18n.saving || 'Saving…'; break;
			case 'saved':
				span.textContent = this.i18n.saved || 'Saved';
				setTimeout( () => { span.textContent = this.i18n.save || 'Update'; }, 1200 );
				break;
			case 'error': span.textContent = 'Error!'; break;
		}
	},

	// ── Toolbar ───────────────────────────────────────────────────────────────

	_initToolbar() {
		const saveBtn     = document.getElementById( 'ghpb-save-btn' );
		const previewBtn  = document.getElementById( 'ghpb-preview-btn' );
		const undoBtn     = document.getElementById( 'ghpb-undo-btn' );
		const redoBtn     = document.getElementById( 'ghpb-redo-btn' );
		const panelToggle = document.getElementById( 'ghpb-panel-toggle' );
		const backBtn     = document.getElementById( 'ghpb-back-btn' );
		const respBtns    = document.querySelectorAll( '.ghpb-responsive-btn' );

		saveBtn?.addEventListener( 'click', () => this._save() );
		previewBtn?.addEventListener( 'click', () => this._openPreview() );
		undoBtn?.addEventListener( 'click', () => this._undo() );
		redoBtn?.addEventListener( 'click', () => this._redo() );
		panelToggle?.addEventListener( 'click', () => this._togglePanel() );
		backBtn?.addEventListener( 'click', () => this._deselectElement() );

		respBtns.forEach( btn => {
			btn.addEventListener( 'click', () => {
				respBtns.forEach( b => b.classList.remove( 'active' ) );
				btn.classList.add( 'active' );
				this._switchDevice( btn.dataset.device );
			} );
		} );

		this.document.on( 'history:change', ( meta ) => {
			if ( undoBtn ) undoBtn.disabled = ! meta.canUndo;
			if ( redoBtn ) redoBtn.disabled = ! meta.canRedo;
		} );
	},

	async _save() {
		const saveBtn = document.getElementById( 'ghpb-save-btn' );
		if ( saveBtn ) saveBtn.querySelector( 'span' ).textContent = this.i18n.saving || 'Saving…';

		try {
			await api.saveDocument(
				this.postId,
				this.document.get( 'elements' ),
				this.document.get( 'page_settings' ),
				'publish'
			);
			this.document.markClean();
			if ( saveBtn ) saveBtn.querySelector( 'span' ).textContent = this.i18n.saved || 'Saved';
			setTimeout( () => {
				if ( saveBtn ) saveBtn.querySelector( 'span' ).textContent = this.i18n.save || 'Update';
			}, 2000 );
		} catch ( err ) {
			console.error( '[GoHigh] Save failed:', err );
			if ( saveBtn ) saveBtn.querySelector( 'span' ).textContent = 'Error!';
		}
	},

	_openPreview() {
		window.open( this.config.previewUrl, '_blank' );
	},

	_undo() {
		this.document.undo();
		this._reloadPreview();
		this._showElementsPanel();
	},

	_redo() {
		this.document.redo();
		this._reloadPreview();
		this._showElementsPanel();
	},

	_togglePanel() {
		const panel = document.getElementById( 'ghpb-panel' );
		if ( panel ) panel.classList.toggle( 'ghpb-panel-collapsed' );
	},

	_switchDevice( device ) {
		this.device = device;
		const wrapper = document.getElementById( 'ghpb-preview-device-wrapper' );
		if ( wrapper ) wrapper.setAttribute( 'data-device', device );
		this._sendToPreview( { type: 'GHPB_UPDATE_STYLES', css: this._buildFullCSS() } );
	},

	_updateHistoryButtons() {
		const meta  = this.document.getHistoryMeta();
		const undo  = document.getElementById( 'ghpb-undo-btn' );
		const redo  = document.getElementById( 'ghpb-redo-btn' );
		if ( undo ) undo.disabled = ! meta.canUndo;
		if ( redo ) redo.disabled = ! meta.canRedo;
	},

	// ── Autosave ──────────────────────────────────────────────────────────────

	_scheduleAutosave() {
		clearTimeout( this._autosaveTimer );
		this._autosaveTimer = setTimeout( () => {
			api.autosave(
				this.postId,
				this.document.get( 'elements' ),
				this.document.get( 'page_settings' )
			).catch( err => console.warn( '[GoHigh] Autosave failed:', err ) );
		}, 30000 );
	},

	// ── Channels ──────────────────────────────────────────────────────────────

	_initChannels() {
		Channels.elements.reply( 'get:document', () => this.document );

		Channels.elements.on( 'element:add', ( { widgetType, columnId } ) => {
			this._addWidget( widgetType, columnId );
		} );

		// Show layout picker instead of immediately adding a section
		Channels.elements.on( 'section:add', () => {
			this._showLayoutPicker();
		} );
	},

	// ── Keyboard shortcuts ────────────────────────────────────────────────────

	_initKeyboardShortcuts() {
		document.addEventListener( 'keydown', ( e ) => {
			const ctrl = e.ctrlKey || e.metaKey;
			if ( ! ctrl ) return;
			if ( e.key === 'z' && ! e.shiftKey ) { e.preventDefault(); this._undo(); }
			if ( e.key === 'y' || ( e.key === 'z' && e.shiftKey ) ) { e.preventDefault(); this._redo(); }
			if ( e.key === 's' ) { e.preventDefault(); this._save(); }
		} );
	},
} );

export default EditorApp;
