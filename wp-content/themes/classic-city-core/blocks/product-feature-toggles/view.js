/**
 * Product Feature Toggles — click a tab to swap which panel is visible.
 *
 * Auto-enqueued by WP when the block renders on a page (block.json
 * `viewScript` field). Idempotent — each block instance wires once,
 * regardless of how many instances live on the page.
 *
 * Keyboard support: ArrowLeft / ArrowRight cycle through tabs and
 * activate the focused one (standard WAI-ARIA tablist behavior).
 * Home / End jump to first / last.
 */
( function () {
	'use strict';

	function wireBlock( root ) {
		if ( root.dataset.cccPftWired === '1' ) return;
		root.dataset.cccPftWired = '1';

		var tabs   = Array.prototype.slice.call(
			root.querySelectorAll( '.sg-block-product-feature-toggles__tab' )
		);
		// __tabpanel (not __panel) — the slice refactor renamed the
		// per-tab wrapper to clarify it's the tab's content panel
		// (the inner content panel itself is the shared feature-detail
		// partial). render.php emits `__tabpanel`; this selector has
		// to match.
		var panels = Array.prototype.slice.call(
			root.querySelectorAll( '.sg-block-product-feature-toggles__tabpanel' )
		);
		if ( ! tabs.length || ! panels.length ) return;

		function activate( idx ) {
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
				activate( idx );
				tab.focus();
			} );

			tab.addEventListener( 'keydown', function ( e ) {
				var next = -1;
				switch ( e.key ) {
					case 'ArrowRight':
						next = ( idx + 1 ) % tabs.length;
						break;
					case 'ArrowLeft':
						next = ( idx - 1 + tabs.length ) % tabs.length;
						break;
					case 'Home':
						next = 0;
						break;
					case 'End':
						next = tabs.length - 1;
						break;
					default:
						return;
				}
				e.preventDefault();
				activate( next );
				tabs[ next ].focus();
			} );
		} );
	}

	function init() {
		document
			.querySelectorAll( '.sg-block-product-feature-toggles' )
			.forEach( wireBlock );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
