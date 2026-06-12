/**
 * GoHigh Page Builder — Editor Entry Point
 *
 * Bootstraps the Backbone/Marionette application once the DOM is ready.
 */
import './editor.scss';
import EditorApp from './app.js';

( function( $, window ) {
	'use strict';

	// Set up wp.apiFetch nonce if not already set.
	if ( window.wp && window.wp.apiFetch && window.ghpbEditorConfig ) {
		window.wp.apiFetch.use( window.wp.apiFetch.createNonceMiddleware( window.ghpbEditorConfig.nonce ) );
		window.wp.apiFetch.use( window.wp.apiFetch.createRootURLMiddleware( window.ghpbEditorConfig.restUrl.replace( /\/gohigh\/v1\/?$/, '/' ) ) );
	}

	// Boot the editor application.
	$( document ).ready( function() {
		const app = new EditorApp();

		// Make accessible globally for debugging.
		window.GoHighEditor = app;

		app.start( {} );
	} );

} )( jQuery, window );
