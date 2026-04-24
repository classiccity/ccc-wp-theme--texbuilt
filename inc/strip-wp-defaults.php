<?php
/**
 * Strip WP's built-in preset palettes / gradients / shadows from the merged
 * theme.json data.
 *
 * theme.json's `defaultPalette: false` / `defaultGradients: false` /
 * `defaultPresets: false` flags hide defaults from the editor picker but do
 * NOT prevent WP from emitting their CSS custom properties / helper classes
 * in the global-styles stylesheet (WP 6.8 behavior). This filter does the
 * stripping ourselves so the frontend CSS only carries brand tokens.
 *
 * Runs on `wp_theme_json_data_default` — the earliest hook, so the stripped
 * data cascades through core → blocks → theme → user merge layers.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wp_theme_json_data_default', 'ccc_strip_default_presets', 10, 1 );
function ccc_strip_default_presets( $theme_json ) {
	$raw = $theme_json->get_data();

	// Nuke default palette, gradients, duotone — our curated palette is the
	// only one editors should see or consume.
	if ( isset( $raw['settings']['color']['palette'] ) ) {
		unset( $raw['settings']['color']['palette'] );
	}
	if ( isset( $raw['settings']['color']['gradients'] ) ) {
		unset( $raw['settings']['color']['gradients'] );
	}
	if ( isset( $raw['settings']['color']['duotone'] ) ) {
		unset( $raw['settings']['color']['duotone'] );
	}

	// WP's default shadows (natural / deep / sharp / outlined / crisp) — drop
	// so only our sm/md/lg/xl ship.
	if ( isset( $raw['settings']['shadow']['presets'] ) ) {
		unset( $raw['settings']['shadow']['presets'] );
	}

	// Font sizes / spacing already respect the `default*: false` flags in
	// WP core — nothing to strip.

	return new WP_Theme_JSON_Data( $raw, 'default' );
}
