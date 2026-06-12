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
		try {
			if ( ! window.Backbone ) {
				throw new Error( 'Backbone is not loaded. Check that WordPress core scripts are enqueued.' );
			}
			if ( ! window.ghpbEditorConfig ) {
				throw new Error( 'ghpbEditorConfig is missing. The plugin may not be enqueuing assets correctly.' );
			}

			const app = new EditorApp();

			// Make accessible globally for debugging.
			window.GoHighEditor = app;

			app.start( {} );
		} catch ( err ) {
			console.error( '[GoHigh] Bootstrap failed:', err );
			const loading = document.getElementById( 'ghpb-loading-screen' );
			if ( loading ) {
				loading.innerHTML = '<div style="text-align:center; padding:32px; color:#fff;">'
					+ '<div style="font-size:48px;">⚠️</div>'
					+ '<h2>Editor failed to start</h2>'
					+ '<p style="color:#cdd6f4;">' + ( err.message || 'Unknown error' ) + '</p>'
					+ '<p style="color:#8890b0; font-size:12px;">Open the browser console (F12) for details.</p>'
					+ '</div>';
			}
		}
	} );

} )( jQuery, window );
