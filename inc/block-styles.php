<?php
/**
 * Core block style registrations.
 *
 * These mirror the styles the Style Guide preview uses (see
 * WORDPRESS_CONVERSION_PLAN.md decisions #13, #16). Visual CSS lives in
 * assets/blocks.css — this file only registers the slug ↔ label mapping
 * so the editor sidebar exposes them in the "Styles" panel.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ccc_register_block_styles() {

	// core/paragraph — eyebrow. Small caps lead-in used above headings in
	// hero + split blocks.
	register_block_style(
		'core/paragraph',
		array(
			'name'  => 'eyebrow',
			'label' => __( 'Eyebrow', 'classic-city-core' ),
		)
	);

	// core/quote — brand-colored icon glyph above the quote text (see
	// WORDPRESS_EXPORT_NOTES.md for the CSS rule).
	register_block_style(
		'core/quote',
		array(
			'name'  => 'quote',
			'label' => __( 'Quote', 'classic-city-core' ),
		)
	);

	// core/group — background texture overlays.
	register_block_style(
		'core/group',
		array(
			'name'  => 'bg-texture',
			'label' => __( 'BG Texture', 'classic-city-core' ),
		)
	);

	register_block_style(
		'core/group',
		array(
			'name'  => 'bg-texture-sand',
			'label' => __( 'BG Texture (Sand)', 'classic-city-core' ),
		)
	);
}
add_action( 'init', 'ccc_register_block_styles' );
