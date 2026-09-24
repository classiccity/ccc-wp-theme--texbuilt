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

/**
 * Opt every CCC + child-theme block into ACF Block V3 behavior.
 *
 * Two different "version" concepts caused us confusion: Gutenberg's
 * `apiVersion` (in block.json, top-level) and ACF Pro's internal
 * `acf_block_version` (a separate field that controls ACF-specific
 * features). Our block.json files all set `apiVersion: 3` but never
 * touched ACF's field, which defaults to **1**.
 *
 * That default-1 state silently disables several V3 affordances —
 * most importantly the "Open Expanded Editor" button (the V3
 * replacement for V2's preview/edit toggle, gated server-side by
 * `$block['acf_block_version'] >= 3` in ACF Pro's blocks.php around
 * line 388 and JS-side by an `expanded_editor_buttons` setting auto-
 * defaulted to true on V3 blocks).
 *
 * Filter `acf/blocks/default_block_version` lets us change the global
 * default without editing 28 block.json files. We scope by namespace
 * so this never accidentally upgrades a third-party block that wasn't
 * designed for V3 behavior. Per-block `acf.blockVersion` in block.json
 * still wins if explicitly set (covered by the !isset() check inside
 * ACF's registration flow).
 *
 * Side note: V3 enables ACF's auto-inline-editing pipeline for text /
 * textarea fields. That's mostly transparent — our render.php files
 * already echo field values via standard PHP, which ACF's V3 DOM
 * walker handles natively. If a specific block needs to opt out for
 * any reason, add `"blockVersion": 2` under `"acf"` in that block.json.
 */
add_filter( 'acf/blocks/default_block_version', 'ccc_default_acf_block_version_to_v3', 10, 2 );
function ccc_default_acf_block_version_to_v3( $default_version, $block ) {
	$name = isset( $block['name'] ) ? (string) $block['name'] : '';
	if ( strpos( $name, 'classic-city-core/' ) === 0 || strpos( $name, 'sg-' ) === 0 ) {
		return 3;
	}
	return $default_version;
}

/**
 * Version block assets by file mtime, not by WordPress's version.
 * ---------------------------------------------------------------------------
 * `register_block_type_from_metadata()` registers the handles named in
 * block.json's `style` / `viewScript` / `editorStyle` keys, and — unless
 * block.json carries a `version` — stamps them with the WORDPRESS CORE
 * version. None of ours carry one, so on 2026-08-21 every block stylesheet on
 * every Classic City site was being served as `?ver=7.0.4`.
 *
 * That is a cache-busting string that changes when WordPress updates and at no
 * other time. Edit a block's CSS, deploy it, and the file on the server is new
 * while the URL is identical — so a returning visitor keeps the old one. WP
 * Engine serves these with `cache-control: public, max-age=31536000`, so
 * "keeps the old one" means A YEAR.
 *
 * This was found the hard way: a block's CSS was rewritten, deployed, verified
 * correct with curl, and still rendered with the previous rules in a browser
 * that had seen the page before. Nothing about the symptom points at caching —
 * it reads as "the deploy didn't work" or "the selector is wrong", and the
 * server serves the right bytes to anything without a cache.
 *
 * The rest of this theme has always been right about this: every enqueue in
 * `inc/enqueue.php` uses `filemtime()`. Block registration simply never got
 * the same treatment, because WordPress does that registration for us.
 *
 * Scoped to files inside the parent or the active child theme — plugin assets
 * are their author's business.
 */
add_action( 'wp_enqueue_scripts', 'ccc_version_theme_assets_by_mtime', 1 );
add_action( 'admin_enqueue_scripts', 'ccc_version_theme_assets_by_mtime', 1 );
add_action( 'enqueue_block_assets', 'ccc_version_theme_assets_by_mtime', 1 );
function ccc_version_theme_assets_by_mtime() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$roots = array_unique( array( get_template_directory(), get_stylesheet_directory() ) );

	foreach ( array( wp_styles(), wp_scripts() ) as $dep ) {
		if ( ! $dep instanceof WP_Dependencies ) {
			continue;
		}
		foreach ( $dep->registered as $handle => $obj ) {
			if ( empty( $obj->src ) || ! is_string( $obj->src ) ) {
				continue;
			}
			$path = ccc_asset_src_to_path( $obj->src, $roots );
			if ( $path ) {
				$dep->registered[ $handle ]->ver = (string) filemtime( $path );
			}
		}
	}
}

/**
 * Resolve an enqueued src to a real file inside one of $roots, or '' if it is
 * not ours. Returns '' rather than false so callers can treat it as a string.
 *
 * @param string   $src   The registered src.
 * @param string[] $roots Absolute theme directories we are willing to touch.
 * @return string Absolute path, or '' when the src is external or not ours.
 */
function ccc_asset_src_to_path( $src, $roots ) {
	$src = strtok( $src, '?' );            // drop any existing ?ver=
	if ( 0 === strpos( $src, '//' ) ) {
		$src = 'https:' . $src;
	}

	$content_url = content_url();
	if ( 0 !== strpos( $src, $content_url ) ) {
		return '';
	}

	$path = WP_CONTENT_DIR . substr( $src, strlen( $content_url ) );
	$real = realpath( $path );
	if ( ! $real || ! is_file( $real ) ) {
		return '';
	}

	foreach ( $roots as $root ) {
		$root = realpath( $root );
		if ( $root && 0 === strpos( $real, $root . DIRECTORY_SEPARATOR ) ) {
			return $real;
		}
	}
	return '';
}
