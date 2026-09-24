/**
 * Swatch Explorer — tab switching + swatch click-to-swap + scroll arrows.
 *
 * Auto-enqueued via block.json viewScript. Idempotent — each block
 * instance wires once.
 *
 * Tabs (scenarios) follow standard WAI-ARIA tablist semantics with
 * keyboard navigation (ArrowLeft / ArrowRight / Home / End). Swatches
 * use button + aria-pressed (toggle-button semantics) rather than
 * tab semantics — they don't reveal/hide content, they swap a single
 * featured image in their parent panel.
 *
 * Scroll arrows scroll the swatch track by ~3 swatch widths per click.
 * The track itself is overflow-x: auto with scroll-snap so swipe and
 * direct scrolling still work natively.
 */
( function () {
	'use strict';

	function wireBlock( root ) {
		if ( root.dataset.cccSwexWired === '1' ) return;
		root.dataset.cccSwexWired = '1';

		var tabs   = Array.prototype.slice.call( root.querySelectorAll( '.sg-block-swatch-explorer__tab' ) );
		var panels = Array.prototype.slice.call( root.querySelectorAll( '.sg-block-swatch-explorer__panel' ) );
		if ( ! tabs.length || ! panels.length ) return;

		function activateTab( idx ) {
			tabs.forEach( function ( tab, i ) {
				var isActive = i === idx;
				tab.classList.toggle( 'is-active', isActive );
				tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
				tab.setAttribute( 'tabindex', isActive ? '0' : '-1' );
			} );
			panels.forEach( function ( panel, i ) {
				var isActive = i === idx;
				panel.classList.toggle( 'is-active', isActive );
				if ( isActive ) {
					panel.removeAttribute( 'hidden' );
				} else {
					panel.setAttribute( 'hidden', '' );
				}
			} );
		}

		tabs.forEach( function ( tab, idx ) {
			tab.addEventListener( 'click', function () {
				activateTab( idx );
				tab.focus();
			} );
			tab.addEventListener( 'keydown', function ( e ) {
				var next = -1;
				switch ( e.key ) {
					case 'ArrowRight': next = ( idx + 1 ) % tabs.length; break;
					case 'ArrowLeft':  next = ( idx - 1 + tabs.length ) % tabs.length; break;
					case 'Home':       next = 0; break;
					case 'End':        next = tabs.length - 1; break;
					default: return;
				}
				e.preventDefault();
				activateTab( next );
				tabs[ next ].focus();
			} );
		} );

		// Wire each panel's swatches + scroll arrows.
		panels.forEach( function ( panel ) {
			var bigImage = panel.querySelector( '.sg-block-swatch-explorer__big-image-el' );
			var swatches = Array.prototype.slice.call( panel.querySelectorAll( '.sg-block-swatch-explorer__swatch' ) );
			var track    = panel.querySelector( '.sg-block-swatch-explorer__swatch-track' );
			var prevBtn  = panel.querySelector( '.sg-block-swatch-explorer__scroll-prev' );
			var nextBtn  = panel.querySelector( '.sg-block-swatch-explorer__scroll-next' );

			function activateSwatch( idx ) {
				swatches.forEach( function ( sw, i ) {
					var isActive = i === idx;
					sw.classList.toggle( 'is-active', isActive );
					sw.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
				} );
				if ( bigImage ) {
					var url = swatches[ idx ].getAttribute( 'data-big-image-url' );
					var alt = swatches[ idx ].getAttribute( 'data-big-image-alt' ) || '';
					if ( url ) {
						bigImage.setAttribute( 'src', url );
						bigImage.setAttribute( 'alt', alt );
					}
				}
			}

			swatches.forEach( function ( swatch, idx ) {
				swatch.addEventListener( 'click', function () {
					activateSwatch( idx );
				} );
			} );

			// Scroll arrows: scroll by ~3 swatch widths per click. Reading
			// the live swatch width on each click handles responsive
			// breakpoint changes (CSS var --swatch-width shrinks on smaller
			// viewports — we just measure rather than hardcode).
			if ( track && prevBtn && nextBtn ) {
				prevBtn.addEventListener( 'click', function () {
					var unit = swatches[ 0 ] ? swatches[ 0 ].offsetWidth + 16 : 120;
					track.scrollBy( { left: unit * -3, behavior: 'smooth' } );
				} );
				nextBtn.addEventListener( 'click', function () {
					var unit = swatches[ 0 ] ? swatches[ 0 ].offsetWidth + 16 : 120;
					track.scrollBy( { left: unit * 3, behavior: 'smooth' } );
				} );

				// Disable arrows at the edges of scroll for visual feedback.
				function updateArrowState() {
					var atStart = track.scrollLeft <= 1;
					var atEnd   = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
					prevBtn.disabled = atStart;
					nextBtn.disabled = atEnd;
				}
				track.addEventListener( 'scroll', updateArrowState );
				updateArrowState();
			}
		} );
	}

	function init() {
		document.querySelectorAll( '.sg-block-swatch-explorer' ).forEach( wireBlock );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
