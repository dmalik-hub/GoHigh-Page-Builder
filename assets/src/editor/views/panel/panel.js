import Backbone from 'backbone';

/**
 * Stub panel view — the app.js directly manages the panel DOM.
 * This file exists to satisfy the import.
 */
const PanelView = Backbone.View.extend( {
	el: '#ghpb-panel',
	initialize() {},
} );

export default PanelView;
