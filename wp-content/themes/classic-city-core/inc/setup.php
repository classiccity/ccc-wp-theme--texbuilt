<?php
/**
 * Theme setup. Runs on `after_setup_theme`.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declares theme support for block editor features. Most settings flow from
 * theme.json — this function only covers what theme.json can't express.
 */
function ccc_theme_setup() {
	// Let the editor render inside the active theme's style context.
	add_editor_style( 'assets/blocks.css' );

	// Title tag + post thumbnails (testimonials use thumbnails for headshots).
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	// Responsive embeds default.
	add_theme_support( 'responsive-embeds' );

	// Align wide/full comes from theme.json layout, but keep the support flag
	// so core block alignment toolbars work end to end.
	add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'ccc_theme_setup' );
