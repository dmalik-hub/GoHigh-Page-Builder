import Backbone from 'backbone';
import { generateId } from '../utils/helpers.js';

/**
 * A single element node (section, column, or widget) in the page tree.
 */
const ElementModel = Backbone.Model.extend( {
	defaults() {
		return {
			id:         generateId(),
			elType:     'widget',
			widgetType: '',
			settings:   {},
			elements:   [], // child IDs or nested models
		};
	},

	initialize() {
		if ( ! this.get( 'id' ) ) {
			this.set( 'id', generateId() );
		}
	},

	getSetting( key ) {
		const settings = this.get( 'settings' ) || {};
		return settings[ key ];
	},

	setSetting( key, value ) {
		const settings = Object.assign( {}, this.get( 'settings' ) );
		settings[ key ] = value;
		this.set( 'settings', settings );
	},

	updateSettings( updates ) {
		const settings = Object.assign( {}, this.get( 'settings' ), updates );
		this.set( 'settings', settings );
	},

	toJSON() {
		const data = Backbone.Model.prototype.toJSON.call( this );
		// elements are serialised by the DocumentModel/collection.
		return data;
	},
} );

export default ElementModel;
