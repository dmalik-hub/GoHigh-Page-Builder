/**
 * GoHigh Page Builder — Frontend JS
 * Interactive widget handlers + entrance animations.
 */
import './frontend.scss';

( function( $ ) {
	'use strict';

	const GoHighFrontend = {

		init() {
			this._initAnimations();
			this._initAccordion();
			this._initTabs();
			this._initToggle();
			this._initCounters();
			this._initCountdowns();
			this._initProgressBars();
			this._initFlipBoxes();
			this._initAlertDismiss();
			this._initVideoHandlers();
		},

		// ── Entrance animations ───────────────────────────────────────────────

		_initAnimations() {
			if ( ! ( 'IntersectionObserver' in window ) ) {
				$( '.ghpb-widget[data-animation]' ).addClass( 'ghpb-animated' );
				return;
			}

			const observer = new IntersectionObserver( ( entries ) => {
				entries.forEach( entry => {
					if ( entry.isIntersecting ) {
						const el        = entry.target;
						const animation = el.dataset.animation;
						if ( animation ) {
							const delay = parseInt( el.dataset.animationDelay || 0 );
							setTimeout( () => el.classList.add( 'ghpb-animated', `ghpb-anim-${ animation }` ), delay );
						}
						observer.unobserve( el );
					}
				} );
			}, { threshold: 0.1 } );

			$( '[data-animation]' ).each( function() { observer.observe( this ); } );
		},

		// ── Accordion ─────────────────────────────────────────────────────────

		_initAccordion() {
			$( '.ghpb-accordion' ).each( function() {
				const accordion = $( this );

				// Open first item by default if configured
				accordion.find( '.ghpb-accordion-item' ).first().addClass( 'active' );

				accordion.on( 'click', '.ghpb-accordion-header', function() {
					const item     = $( this ).closest( '.ghpb-accordion-item' );
					const isActive = item.hasClass( 'active' );

					// Close all
					accordion.find( '.ghpb-accordion-item' ).removeClass( 'active' );
					accordion.find( '.ghpb-accordion-content' ).stop( true ).slideUp( 250 );

					// Toggle clicked
					if ( ! isActive ) {
						item.addClass( 'active' );
						item.find( '.ghpb-accordion-content' ).stop( true ).slideDown( 250 );
					}
				} );

				// Initialise visible state
				accordion.find( '.ghpb-accordion-item.active .ghpb-accordion-content' ).show();
				accordion.find( '.ghpb-accordion-item:not(.active) .ghpb-accordion-content' ).hide();
			} );
		},

		// ── Tabs ──────────────────────────────────────────────────────────────

		_initTabs() {
			$( '.ghpb-tabs' ).each( function() {
				const tabs = $( this );

				// Show first tab
				tabs.find( '.ghpb-tabs-nav-item' ).first().addClass( 'active' );
				tabs.find( '.ghpb-tabs-panel' ).first().addClass( 'active' );

				tabs.on( 'click', '.ghpb-tabs-nav-item', function() {
					const idx = $( this ).index();
					tabs.find( '.ghpb-tabs-nav-item' ).removeClass( 'active' );
					tabs.find( '.ghpb-tabs-panel' ).removeClass( 'active' );
					$( this ).addClass( 'active' );
					tabs.find( '.ghpb-tabs-panel' ).eq( idx ).addClass( 'active' );
				} );
			} );
		},

		// ── Toggle ────────────────────────────────────────────────────────────

		_initToggle() {
			$( '.ghpb-toggle' ).each( function() {
				const toggle  = $( this );
				const content = toggle.find( '.ghpb-toggle-content' );

				// Default state
				if ( toggle.data( 'default-state' ) === 'open' ) {
					toggle.addClass( 'active' );
					content.show();
				} else {
					content.hide();
				}

				toggle.on( 'click', '.ghpb-toggle-header', function() {
					toggle.toggleClass( 'active' );
					content.stop( true ).slideToggle( 250 );
				} );
			} );
		},

		// ── Animated counter ──────────────────────────────────────────────────

		_initCounters() {
			if ( ! ( 'IntersectionObserver' in window ) ) {
				$( '.ghpb-counter-number' ).each( function() {
					$( this ).text( $( this ).data( 'end' ) );
				} );
				return;
			}

			const observer = new IntersectionObserver( ( entries ) => {
				entries.forEach( entry => {
					if ( ! entry.isIntersecting ) return;
					const el       = entry.target;
					const start    = parseFloat( el.dataset.start ) || 0;
					const end      = parseFloat( el.dataset.end ) || 0;
					const duration = parseFloat( el.dataset.duration ) || 2000;
					const decimals = ( String( end ).split( '.' )[ 1 ] || '' ).length;

					animateValue( el, start, end, duration, decimals );
					observer.unobserve( el );
				} );
			}, { threshold: 0.3 } );

			$( '.ghpb-counter-number' ).each( function() { observer.observe( this ); } );

			function animateValue( el, start, end, duration, decimals ) {
				const startTime = performance.now();
				const update    = ( currentTime ) => {
					const elapsed  = currentTime - startTime;
					const progress = Math.min( elapsed / duration, 1 );
					const eased    = 1 - Math.pow( 1 - progress, 3 ); // ease-out cubic
					const current  = start + ( end - start ) * eased;
					el.textContent = current.toFixed( decimals );
					if ( progress < 1 ) requestAnimationFrame( update );
					else el.textContent = end.toFixed( decimals );
				};
				requestAnimationFrame( update );
			}
		},

		// ── Countdown ─────────────────────────────────────────────────────────

		_initCountdowns() {
			$( '.ghpb-countdown' ).each( function() {
				const el  = $( this );
				const due = new Date( el.data( 'due' ) ).getTime();

				if ( isNaN( due ) ) return;

				const update = () => {
					const now  = Date.now();
					let   diff = Math.max( 0, due - now );

					const days    = Math.floor( diff / 86400000 );      diff %= 86400000;
					const hours   = Math.floor( diff / 3600000 );       diff %= 3600000;
					const minutes = Math.floor( diff / 60000 );         diff %= 60000;
					const seconds = Math.floor( diff / 1000 );

					el.find( '.ghpb-cd-days .ghpb-cd-digit' ).text( String( days ).padStart( 2, '0' ) );
					el.find( '.ghpb-cd-hours .ghpb-cd-digit' ).text( String( hours ).padStart( 2, '0' ) );
					el.find( '.ghpb-cd-minutes .ghpb-cd-digit' ).text( String( minutes ).padStart( 2, '0' ) );
					el.find( '.ghpb-cd-seconds .ghpb-cd-digit' ).text( String( seconds ).padStart( 2, '0' ) );

					if ( due - Date.now() > 0 ) setTimeout( update, 1000 );
				};

				update();
			} );
		},

		// ── Progress bars (animate width on scroll) ───────────────────────────

		_initProgressBars() {
			if ( ! ( 'IntersectionObserver' in window ) ) {
				$( '.ghpb-progress-bar' ).each( function() {
					$( this ).css( 'width', $( this ).data( 'percent' ) + '%' );
				} );
				return;
			}

			const observer = new IntersectionObserver( ( entries ) => {
				entries.forEach( entry => {
					if ( ! entry.isIntersecting ) return;
					const bar = entry.target;
					const pct = bar.dataset.percent || 0;
					bar.style.width = '0%';
					requestAnimationFrame( () => {
						bar.style.transition = 'width 1s ease-in-out';
						bar.style.width = pct + '%';
					} );
					observer.unobserve( bar );
				} );
			}, { threshold: 0.2 } );

			$( '.ghpb-progress-bar' ).each( function() { observer.observe( this ); } );
		},

		// ── Flip boxes ────────────────────────────────────────────────────────

		_initFlipBoxes() {
			// CSS handles the flip on hover; just ensure focus works for a11y.
			$( '.ghpb-flip-box' ).attr( 'tabindex', '0' );
		},

		// ── Alert dismiss ─────────────────────────────────────────────────────

		_initAlertDismiss() {
			$( document ).on( 'click', '.ghpb-alert-dismiss', function() {
				$( this ).closest( '.ghpb-alert' ).fadeOut( 300, function() { $( this ).remove(); } );
			} );
		},

		// ── Video background ──────────────────────────────────────────────────

		_initVideoHandlers() {
			// YouTube iframe API — autoplay video widgets
			$( '.ghpb-widget-video' ).each( function() {
				// Nothing special needed; PHP renders the iframe/embed directly.
			} );
		},
	};

	$( document ).ready( () => GoHighFrontend.init() );

} )( jQuery );
