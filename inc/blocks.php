<?php
/**
 * ACF block auto-registration.
 *
 * Convention: each custom block lives in blocks/{slug}/ with at least a
 * block.json. Optional fields.php in the same directory registers the ACF
 * field group scoped to that block via `post_type == block` or the native
 * ACF block location rule.
 *
 * Block registration runs on `init`. ACF field groups run on
 * `acf/include_fields`. The two hooks fire in the right order for ACF Pro
 * to discover the block and wire up its fields automatically.
 *
 * Adding a new block: mkdir blocks/my-block/, drop block.json + fields.php +
 * render.php in it. No edits to this file needed.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'ccc_register_blocks', 5 );
function ccc_register_blocks() {
	$blocks_dir = CCC_THEME_DIR . 'blocks';
	if ( ! is_dir( $blocks_dir ) ) {
		return;
	}

	foreach ( glob( $blocks_dir . '/*', GLOB_ONLYDIR ) as $block_path ) {
		if ( file_exists( $block_path . '/block.json' ) ) {
			register_block_type_from_metadata( $block_path );
		}
	}
}

add_action( 'acf/include_fields', 'ccc_include_block_fields', 5 );
function ccc_include_block_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	foreach ( glob( CCC_THEME_DIR . 'blocks/*/fields.php' ) as $fields_file ) {
		require_once $fields_file;
	}
}
