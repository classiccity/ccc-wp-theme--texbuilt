/**
 * Highlighted Image Gallery — click a thumbnail to swap the primary image.
 *
 * Auto-enqueued by WP when the block renders on a page (block.json `viewScript`
 * field). Idempotent — wires each gallery instance once, regardless of how many
 * instances live on the page.
 */
( function () {
	'use strict';

	function wireGallery( root ) {
		if ( root.dataset.cccGalleryWired === '1' ) return;
		root.dataset.cccGalleryWired = '1';

		var primary = root.querySelector( '.sg-block-highlighted-image-gallery__primary' );
		var thumbs  = root.querySelectorAll( '.sg-block-highlighted-image-gallery__thumb' );
		if ( ! primary || ! thumbs.length ) return;

		thumbs.forEach( function ( thumb ) {
			thumb.addEventListener( 'click', function () {
				var src = thumb.getAttribute( 'data-image-src' );
				var alt = thumb.getAttribute( 'data-image-alt' ) || '';
				if ( src ) {
					primary.setAttribute( 'src', src );
					primary.setAttribute( 'alt', alt );
				}
				thumbs.forEach( function ( other ) {
					var isMe = other === thumb;
					other.classList.toggle( 'is-active', isMe );
					other.setAttribute( 'aria-pressed', isMe ? 'true' : 'false' );
				} );
			} );
		} );
	}

	function init() {
		document
			.querySelectorAll( '.sg-block-highlighted-image-gallery' )
			.forEach( wireGallery );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
