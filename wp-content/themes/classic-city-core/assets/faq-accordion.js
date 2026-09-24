/**
 * FAQ accordion — animation only.
 *
 * The accordion itself is <details>/<summary>, built server-side in
 * inc/faq-accordion.php. It is fully working before this file loads and
 * fully working if this file never loads: opening, closing, keyboard,
 * screen readers and deep links are all the platform's job. Everything
 * here is decoration.
 *
 * That is the contract to keep in mind when editing: if a branch below
 * throws, the accordion must degrade to an instant native toggle, never
 * to a dead control. Hence the early bail-outs rather than polyfills.
 *
 * Two behaviours:
 *
 * 1. ANIMATED OPEN/CLOSE. <details> cannot be transitioned in CSS in any
 *    portable way — while closed, the browser hides the panel outright,
 *    so there is no "from" box to animate out of and no closing frame to
 *    animate into. The fix is to own the `open` attribute: on the way
 *    open, set it first and animate up from zero height; on the way
 *    shut, animate down to zero FIRST and only then unset it.
 *
 * 2. DEEP LINKS STILL LAND. Every row keeps its id, so /page#faq-… is a
 *    link to one question. A closed row would swallow it, so the target
 *    is opened — instantly, with no animation, because it happens before
 *    the reader has looked at it.
 *
 * Duration comes from --ccc-faq-motion-duration so a child theme retunes
 * the timing in CSS, next to the transition it has to stay in step with,
 * instead of here.
 */
( function () {
	'use strict';

	var lists = document.querySelectorAll( '.schema-faq.is-ccc-faq-accordion' );
	if ( ! lists.length ) {
		return;
	}

	// WAAPI. Everything that supports <details> supports this, but the
	// whole point of the file is that it is optional — so check.
	if ( typeof Element === 'undefined' || ! Element.prototype.animate ) {
		return;
	}

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	/**
	 * Read the CSS motion duration, in milliseconds.
	 *
	 * @param {Element} el Element to resolve the custom property against.
	 * @return {number} Duration in ms.
	 */
	function motionDuration( el ) {
		var raw = getComputedStyle( el )
			.getPropertyValue( '--ccc-faq-motion-duration' )
			.trim();

		if ( ! raw ) {
			return 320;
		}

		// CSS times are either `320ms` or `0.32s`.
		var value = parseFloat( raw );
		if ( isNaN( value ) ) {
			return 320;
		}
		return raw.slice( -2 ) === 'ms' ? value : value * 1000;
	}

	/**
	 * Wire one row.
	 *
	 * @param {HTMLDetailsElement} details The row.
	 */
	function bind( details ) {
		var summary = details.querySelector( '.ccc-faq-trigger' );
		var panel = details.querySelector( '.ccc-faq-panel' );

		if ( ! summary || ! panel ) {
			return;
		}

		summary.addEventListener( 'click', function ( event ) {
			// Reduced motion: hand the click straight back to the
			// browser. Not an animation with duration 0 — no animation.
			if ( reduceMotion.matches ) {
				return;
			}

			// Mid-flight clicks are ignored rather than queued. The
			// alternative is reversing an animation whose end handler is
			// about to flip `open` underneath it.
			if ( details.dataset.cccFaqAnimating ) {
				event.preventDefault();
				return;
			}

			event.preventDefault();

			var duration = motionDuration( details );
			var opening = ! details.open;
			var frames;

			if ( opening ) {
				// Must be open to be measurable — a closed <details>
				// gives its panel no box at all.
				details.open = true;
				frames = { height: [ '0px', panel.offsetHeight + 'px' ], opacity: [ 0, 1 ] };
			} else {
				// Measure BEFORE `open` comes off, for the same reason.
				// `is-closing` drives the marker back now rather than
				// when the animation finishes, so the chevron travels
				// with the panel instead of snapping after it.
				frames = { height: [ panel.offsetHeight + 'px', '0px' ], opacity: [ 1, 0 ] };
				details.classList.add( 'is-closing' );
			}

			details.dataset.cccFaqAnimating = '1';

			var animation = panel.animate( frames, {
				duration: duration,
				easing: 'ease',
			} );

			animation.onfinish = function () {
				if ( ! opening ) {
					details.open = false;
					details.classList.remove( 'is-closing' );
				}
				delete details.dataset.cccFaqAnimating;
			};
		} );
	}

	/**
	 * Open the row a fragment points at, without animating.
	 */
	function openTarget() {
		if ( ! window.location.hash || window.location.hash.length < 2 ) {
			return;
		}

		var target;
		try {
			// A hash is user-supplied and need not be a valid selector.
			target = document.getElementById(
				decodeURIComponent( window.location.hash.slice( 1 ) )
			);
		} catch ( e ) {
			return;
		}

		if ( target && 'DETAILS' === target.tagName && ! target.open ) {
			target.open = true;
			// The browser scrolled to this row while it was collapsed,
			// so the position it chose is now stale by the height of the
			// answer. scroll-margin-top on the row is respected here.
			target.scrollIntoView();
		}
	}

	Array.prototype.forEach.call( lists, function ( list ) {
		Array.prototype.forEach.call(
			list.querySelectorAll( 'details.schema-faq-section' ),
			bind
		);
	} );

	openTarget();
	window.addEventListener( 'hashchange', openTarget );
} )();
