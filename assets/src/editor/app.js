import Backbone from 'backbone';
import Marionette from 'backbone.marionette';
import * as Channels from './channels/channels.js';
import DocumentModel from './models/document.js';
import api from './utils/api.js';
import { createDefaultSection, createWidget, deepClone, generateId, generateCSS } from './utils/helpers.js';

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

			// Show the editor, hide loading screen.
			document.getElementById( 'ghpb-loading-screen' ).style.display = 'none';
			document.getElementById( 'ghpb-editor' ).style.display = 'flex';

			this._renderPanel();
			this._updateHistoryButtons();

		} catch ( err ) {
			console.error( '[GoHigh] Failed to load document:', err );
		}
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

	_showEditPanel( elementId ) {
		const element = this.document.getElement( elementId );
		if ( ! element ) return;

		const widgetConfig = element.widgetType ? this.widgetTypes[ element.widgetType ] : null;
		if ( ! widgetConfig ) return;

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
		this.document.updateElementSettings( elementId, { [ controlKey ]: value } );
		this._pushLiveStyles( elementId );
		this._scheduleAutosave();
	},

	_pushLiveStyles( elementId ) {
		const element = this.document.getElement( elementId );
		if ( ! element || ! element.widgetType ) return;

		const widgetConfig = this.widgetTypes[ element.widgetType ];
		if ( ! widgetConfig ) return;

		const css = generateCSS( elementId, widgetConfig.controls || [], element.settings, this.device );
		this._sendToPreview( { type: 'GHPB_UPDATE_STYLES', css: this._buildFullCSS() } );
	},

	_buildFullCSS() {
		// Rebuild CSS for all elements — sends full stylesheet.
		let css = '';
		const walk = ( elements ) => {
			for ( const el of elements ) {
				if ( el.widgetType && this.widgetTypes[ el.widgetType ] ) {
					css += generateCSS( el.id, this.widgetTypes[ el.widgetType ].controls || [], el.settings, this.device );
				}
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
				this._addDefaultSection();
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

		if ( elType === 'widget' ) {
			this._showEditPanel( elementId );
		} else {
			// For sections/columns show a simplified settings panel (future).
			this._showEditPanel( elementId );
		}
	},

	_deselectElement() {
		this.selectedId = null;
		this._sendToPreview( { type: 'GHPB_SELECT_ELEMENT', id: null } );
		this._showElementsPanel();
	},

	// ── DnD from panel ────────────────────────────────────────────────────────

	_onPanelDragStart( widgetType ) {
		// Handled by the ElementsPanelView via HTML5 dragstart.
		// On drop (received via postMessage from canvas or via native drag events),
		// we create the widget and add it to the document.
		this._pendingDragType = widgetType;
	},

	// ── Element mutations ─────────────────────────────────────────────────────

	_addDefaultSection() {
		const section = createDefaultSection();
		this.document.addSection( section );
		this._reloadPreview();
	},

	_addWidget( widgetType, columnId ) {
		const widget = createWidget( widgetType );
		const elements = deepClone( this.document.get( 'elements' ) );

		// Find the first column if not specified.
		if ( ! columnId ) {
			// Add new section with widget.
			const section = createDefaultSection();
			section.elements[ 0 ].elements.push( widget );
			this.document.addSection( section );
		} else {
			this.document.addWidget( null, columnId, widget );
		}
		this._reloadPreview();
	},

	// ── Preview refresh ───────────────────────────────────────────────────────

	_reloadPreview() {
		// Full reload of the iframe with new data.
		// We save draft first, then reload the iframe.
		const elements     = this.document.get( 'elements' );
		const pageSettings = this.document.get( 'page_settings' );

		api.autosave( this.postId, elements, pageSettings ).then( () => {
			if ( this.previewFrame ) {
				this.previewFrame.contentWindow.location.reload();
			}
		} );
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
		// Re-inject styles for the new breakpoint.
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
		Channels.elements.on( 'section:add', () => {
			this._addDefaultSection();
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
