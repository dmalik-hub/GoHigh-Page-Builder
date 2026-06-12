import Backbone from 'backbone';
import { deepClone, generateId } from '../utils/helpers.js';

/**
 * Holds the full element tree as a plain JS array (nested JSON).
 * History is maintained as a stack of snapshots (whole tree JSON).
 */
const DocumentModel = Backbone.Model.extend( {
	defaults() {
		return {
			id:           0,
			title:        '',
			status:       'publish',
			edit_mode:    'builder',
			elements:     [],
			page_settings: {},
			css:          '',
		};
	},

	initialize() {
		this._history       = [];   // past snapshots
		this._future        = [];   // redo snapshots
		this._maxHistory    = 50;
		this._isDirty       = false;
		this._snapshotLabel = '';
	},

	// ── History ───────────────────────────────────────────────────────────────

	snapshot( label = 'Change' ) {
		// Save current state before the change.
		const snap = {
			label,
			timestamp: Date.now(),
			elements:  deepClone( this.get( 'elements' ) ),
		};
		this._history.push( snap );
		if ( this._history.length > this._maxHistory ) {
			this._history.shift();
		}
		this._future = []; // clear redo stack on new action
		this._isDirty = true;
		this.trigger( 'history:change', this.getHistoryMeta() );
	},

	undo() {
		if ( ! this._history.length ) return;
		// Save current to future.
		this._future.push( {
			label:     'Redo',
			timestamp: Date.now(),
			elements:  deepClone( this.get( 'elements' ) ),
		} );
		const snap = this._history.pop();
		this.set( 'elements', snap.elements );
		this.trigger( 'elements:change' );
		this.trigger( 'history:change', this.getHistoryMeta() );
	},

	redo() {
		if ( ! this._future.length ) return;
		this._history.push( {
			label:     'Undo',
			timestamp: Date.now(),
			elements:  deepClone( this.get( 'elements' ) ),
		} );
		const snap = this._future.pop();
		this.set( 'elements', snap.elements );
		this.trigger( 'elements:change' );
		this.trigger( 'history:change', this.getHistoryMeta() );
	},

	getHistoryMeta() {
		return {
			canUndo: this._history.length > 0,
			canRedo: this._future.length > 0,
			steps:   this._history.length,
		};
	},

	isDirty() { return this._isDirty; },

	markClean() { this._isDirty = false; },

	// ── Element tree mutation ─────────────────────────────────────────────────

	addSection( section, index ) {
		this.snapshot( 'Add Section' );
		const elements = deepClone( this.get( 'elements' ) );
		if ( index === undefined ) {
			elements.push( section );
		} else {
			elements.splice( index, 0, section );
		}
		this.set( 'elements', elements );
		this.trigger( 'elements:change' );
	},

	addWidget( sectionId, columnId, widget, index ) {
		this.snapshot( `Add ${ widget.widgetType }` );
		const elements = deepClone( this.get( 'elements' ) );
		const col = this._findElement( elements, columnId );
		if ( col ) {
			if ( index === undefined ) {
				col.elements.push( widget );
			} else {
				col.elements.splice( index, 0, widget );
			}
		}
		this.set( 'elements', elements );
		this.trigger( 'elements:change' );
	},

	updateElementSettings( elementId, settings ) {
		const elements = deepClone( this.get( 'elements' ) );
		const el = this._findElement( elements, elementId );
		if ( el ) {
			el.settings = Object.assign( {}, el.settings, settings );
			this.set( 'elements', elements );
			this._isDirty = true;
			this.trigger( 'element:updated', elementId, el.settings );
		}
	},

	removeElement( elementId ) {
		this.snapshot( 'Delete Element' );
		const elements = deepClone( this.get( 'elements' ) );
		this._removeElement( elements, elementId );
		this.set( 'elements', elements );
		this.trigger( 'elements:change' );
	},

	duplicateElement( elementId ) {
		this.snapshot( 'Duplicate Element' );
		const elements = deepClone( this.get( 'elements' ) );
		this._duplicateElement( elements, elementId );
		this.set( 'elements', elements );
		this.trigger( 'elements:change' );
	},

	moveElement( elementId, targetParentId, targetIndex ) {
		this.snapshot( 'Move Element' );
		const elements = deepClone( this.get( 'elements' ) );
		const el = this._removeElement( elements, elementId );
		if ( ! el ) return;
		if ( targetParentId === '__root__' ) {
			elements.splice( targetIndex, 0, el );
		} else {
			const parent = this._findElement( elements, targetParentId );
			if ( parent ) {
				parent.elements = parent.elements || [];
				parent.elements.splice( targetIndex, 0, el );
			}
		}
		this.set( 'elements', elements );
		this.trigger( 'elements:change' );
	},

	getElement( elementId ) {
		return this._findElement( this.get( 'elements' ), elementId );
	},

	// ── Private helpers ────────────────────────────────────────────────────────

	_findElement( elements, id ) {
		for ( const el of elements ) {
			if ( el.id === id ) return el;
			if ( el.elements && el.elements.length ) {
				const found = this._findElement( el.elements, id );
				if ( found ) return found;
			}
		}
		return null;
	},

	_removeElement( elements, id ) {
		for ( let i = 0; i < elements.length; i++ ) {
			if ( elements[ i ].id === id ) {
				return elements.splice( i, 1 )[ 0 ];
			}
			if ( elements[ i ].elements ) {
				const removed = this._removeElement( elements[ i ].elements, id );
				if ( removed ) return removed;
			}
		}
		return null;
	},

	_duplicateElement( elements, id ) {
		for ( let i = 0; i < elements.length; i++ ) {
			if ( elements[ i ].id === id ) {
				const clone = deepClone( elements[ i ] );
				clone.id = generateId();
				this._reassignIds( clone );
				elements.splice( i + 1, 0, clone );
				return true;
			}
			if ( elements[ i ].elements ) {
				if ( this._duplicateElement( elements[ i ].elements, id ) ) return true;
			}
		}
		return false;
	},

	_reassignIds( element ) {
		element.id = generateId();
		if ( element.elements ) {
			element.elements.forEach( child => this._reassignIds( child ) );
		}
	},

	// ── Serialise ─────────────────────────────────────────────────────────────

	getSerializedData() {
		return {
			elements:      this.get( 'elements' ),
			page_settings: this.get( 'page_settings' ),
			status:        this.get( 'status' ),
		};
	},
} );

export default DocumentModel;
