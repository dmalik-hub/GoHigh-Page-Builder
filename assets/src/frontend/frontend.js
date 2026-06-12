/**
 * GoHigh Page Builder — Frontend JS
 * Handles entrance animations and basic widget interactions.
 */
import './frontend.scss';

( function( $ ) {
	'use strict';

	const GoHighFrontend = {

		init() {
			this._initAnimations();
		},

		_initAnimations() {
			if ( ! ( 'IntersectionObserver' in window ) ) {
				$( '.ghpb-widget' ).addClass( 'ghpb-animated' );
				return;
			}

			const observer = new IntersectionObserver( ( entries ) => {
				entries.forEach( entry => {
					if ( entry.isIntersecting ) {
						const el = entry.target;
						const animation = el.dataset.animation;
						if ( animation ) {
							const delay = parseInt( el.dataset.animationDelay || 0 );
							setTimeout( () => {
								el.classList.add( 'ghpb-animated', `ghpb-anim-${ animation }` );
							}, delay );
						}
						observer.unobserve( el );
					}
				} );
			}, { threshold: 0.1 } );

			$( '[data-animation]' ).each( function() {
				observer.observe( this );
			} );
		},
	};

	$( document ).ready( () => GoHighFrontend.init() );

} )( jQuery );
