import Backbone from 'backbone';
import * as Channels from '../../../channels/channels.js';

/**
 * Left panel — widget library browser.
 */
const ElementsPanelView = Backbone.View.extend( {
	className: 'ghpb-elements-panel',

	events: {
		'input .ghpb-widget-search': 'onSearch',
		'click .ghpb-add-section-btn-panel': 'onAddSection',
	},

	initialize( options ) {
		this.widgetTypes = options.widgetTypes || {};
		this.onDragStart = options.onDragStart || function() {};
		this.filter      = '';
	},

	render() {
		this.el.innerHTML = this._template();
		this._renderWidgets();
		return this;
	},

	_template() {
		return `
		<div class="ghpb-elements-search">
			<input type="text" class="ghpb-widget-search" placeholder="Search Widgets…" autocomplete="off">
		</div>
		<div class="ghpb-add-section-row">
			<button class="ghpb-add-section-btn-panel">
				<span class="dashicons dashicons-plus-alt2"></span> Add Section
			</button>
		</div>
		<div class="ghpb-widgets-list"></div>
		`;
	},

	_renderWidgets() {
		const list = this.el.querySelector( '.ghpb-widgets-list' );
		if ( ! list ) return;
		list.innerHTML = '';

		const categories = {};
		for ( const [ name, widget ] of Object.entries( this.widgetTypes ) ) {
			if ( this.filter ) {
				const search = this.filter.toLowerCase();
				const matches = widget.title.toLowerCase().includes( search )
					|| ( widget.keywords || [] ).some( k => k.toLowerCase().includes( search ) );
				if ( ! matches ) continue;
			}
			const cats = widget.categories || [ 'basic' ];
			cats.forEach( cat => {
				if ( ! categories[ cat ] ) categories[ cat ] = [];
				categories[ cat ].push( { name, ...widget } );
			} );
		}

		const catLabels = {
			basic:   'Basic',
			general: 'General',
			pro:     'Pro',
		};

		for ( const [ cat, widgets ] of Object.entries( categories ) ) {
			const section = document.createElement( 'div' );
			section.className = 'ghpb-widget-category';
			section.innerHTML = `
				<div class="ghpb-category-title">${ catLabels[ cat ] || cat }</div>
				<div class="ghpb-category-items"></div>
			`;

			const items = section.querySelector( '.ghpb-category-items' );
			widgets.forEach( widget => {
				const item = document.createElement( 'div' );
				item.className = 'ghpb-widget-item';
				item.draggable = true;
				item.dataset.widgetType = widget.name;
				item.innerHTML = `
					<span class="${ widget.icon || 'dashicons dashicons-screenoptions' }"></span>
					<span class="ghpb-widget-item-title">${ widget.title }</span>
				`;
				item.addEventListener( 'dragstart', ( e ) => {
					e.dataTransfer.setData( 'text/plain', widget.name );
					e.dataTransfer.effectAllowed = 'copy';
					this.onDragStart( widget.name );
					item.classList.add( 'ghpb-dragging' );
				} );
				item.addEventListener( 'dragend', () => {
					item.classList.remove( 'ghpb-dragging' );
				} );
				items.appendChild( item );
			} );

			list.appendChild( section );
		}
	},

	onSearch( e ) {
		this.filter = e.target.value.trim();
		this._renderWidgets();
	},

	onAddSection() {
		Channels.elements.trigger( 'section:add' );
	},
} );

export default ElementsPanelView;
