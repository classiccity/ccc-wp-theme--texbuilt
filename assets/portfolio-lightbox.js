/**
 * Portfolio-gallery lightbox.
 *
 * Vanilla, dependency-free. Wires up every `.sg-block-portfolio` gallery on the
 * page independently — each gallery has its own `data-items` JSON (emitted by
 * blocks/portfolio-gallery/render.php) and its own button list, so two
 * galleries on the same page don't cross-populate.
 *
 * Image URLs come from the wrapper's `data-items` attribute rather than from
 * the DOM — the rendered items use a background-image <div>, not an <img>, so
 * scraping the button wouldn't find a usable src.
 *
 * Keyboard: Esc closes, ← / → navigate.
 */
( function () {
	'use strict';

	function init() {
		var galleries = document.querySelectorAll( '.sg-block-portfolio[data-items]' );
		if ( ! galleries.length ) return;

		var overlay = buildOverlay();
		document.body.appendChild( overlay.root );

		var activeItems = [];
		var current = 0;

		function open( items, idx, returnFocus ) {
			activeItems = items;
			current = idx;
			overlay.lastTrigger = returnFocus || null;
			render();
			overlay.root.classList.add( 'is-open' );
			document.documentElement.style.overflow = 'hidden';
			overlay.closeBtn.focus();
		}
		function close() {
			overlay.root.classList.remove( 'is-open' );
			document.documentElement.style.overflow = '';
			if ( typeof overlay.lastTrigger === 'function' ) overlay.lastTrigger();
		}
		function step( delta ) {
			if ( ! activeItems.length ) return;
			current = ( current + delta + activeItems.length ) % activeItems.length;
			render();
		}
		function render() {
			var item = activeItems[ current ];
			if ( ! item ) return;
			overlay.img.src = item.image || '';
			overlay.img.alt = item.title || '';
			overlay.title.textContent = item.title || '';
			overlay.counter.textContent = ( current + 1 ) + ' / ' + activeItems.length;
		}

		Array.prototype.forEach.call( galleries, function ( gallery ) {
			var items;
			try {
				items = JSON.parse( gallery.getAttribute( 'data-items' ) || '[]' );
			} catch ( e ) {
				return;
			}
			if ( ! Array.isArray( items ) || ! items.length ) return;

			var buttons = gallery.querySelectorAll( '.sg-block-portfolio-item-btn' );
			Array.prototype.forEach.call( buttons, function ( btn, i ) {
				btn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					open( items, i, function () { btn.focus(); } );
				} );
			} );
		} );

		overlay.closeBtn.addEventListener( 'click', close );
		overlay.prevBtn.addEventListener( 'click', function () { step( -1 ); } );
		overlay.nextBtn.addEventListener( 'click', function () { step(  1 ); } );
		overlay.backdrop.addEventListener( 'click', close );

		document.addEventListener( 'keydown', function ( e ) {
			if ( ! overlay.root.classList.contains( 'is-open' ) ) return;
			if ( e.key === 'Escape' )     close();
			if ( e.key === 'ArrowLeft' )  step( -1 );
			if ( e.key === 'ArrowRight' ) step(  1 );
		} );
	}

	function buildOverlay() {
		var root = document.createElement( 'div' );
		root.className = 'sg-portfolio-lightbox';
		root.setAttribute( 'role', 'dialog' );
		root.setAttribute( 'aria-modal', 'true' );
		root.setAttribute( 'aria-label', 'Portfolio gallery' );
		root.innerHTML = [
			'<div class="sg-portfolio-lightbox-backdrop"></div>',
			'<button type="button" class="sg-portfolio-lightbox-close" aria-label="Close">&times;</button>',
			'<button type="button" class="sg-portfolio-lightbox-prev"  aria-label="Previous">&lsaquo;</button>',
			'<button type="button" class="sg-portfolio-lightbox-next"  aria-label="Next">&rsaquo;</button>',
			'<figure class="sg-portfolio-lightbox-figure">',
			'  <img class="sg-portfolio-lightbox-image" alt="">',
			'  <figcaption class="sg-portfolio-lightbox-caption">',
			'    <span class="sg-portfolio-lightbox-title"></span>',
			'    <span class="sg-portfolio-lightbox-counter"></span>',
			'  </figcaption>',
			'</figure>',
		].join( '' );
		return {
			root:     root,
			backdrop: root.querySelector( '.sg-portfolio-lightbox-backdrop' ),
			closeBtn: root.querySelector( '.sg-portfolio-lightbox-close' ),
			prevBtn:  root.querySelector( '.sg-portfolio-lightbox-prev' ),
			nextBtn:  root.querySelector( '.sg-portfolio-lightbox-next' ),
			img:      root.querySelector( '.sg-portfolio-lightbox-image' ),
			title:    root.querySelector( '.sg-portfolio-lightbox-title' ),
			counter:  root.querySelector( '.sg-portfolio-lightbox-counter' ),
		};
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
