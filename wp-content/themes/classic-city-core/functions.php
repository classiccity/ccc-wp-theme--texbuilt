<?php
/**
 * Classic City Core — parent theme bootstrap.
 *
 * This file intentionally stays thin. Concerns are split into inc/*.php files
 * so each area (setup, enqueues, block styles, CPTs, patterns) can evolve
 * without turning functions.php into a dumping ground.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CCC_THEME_VERSION', '0.1.0' );
define( 'CCC_THEME_DIR', trailingslashit( get_template_directory() ) );
define( 'CCC_THEME_URI', trailingslashit( get_template_directory_uri() ) );

// Strip WP's default palette / gradients / shadow presets before merge.
require_once CCC_THEME_DIR . 'inc/strip-wp-defaults.php';

// Theme supports, editor styles, core WP behaviors.
require_once CCC_THEME_DIR . 'inc/setup.php';

// Frontend + editor asset enqueues (blocks.css).
require_once CCC_THEME_DIR . 'inc/enqueue.php';

// register_block_style() calls for eyebrow, quote, bg-texture, bg-texture-sand.
require_once CCC_THEME_DIR . 'inc/block-styles.php';

// Testimonial custom post type (title + thumbnail only; Gutenberg disabled).
require_once CCC_THEME_DIR . 'inc/cpt-testimonial.php';

// ACF field group for the Testimonial CPT (company, job title, quote).
require_once CCC_THEME_DIR . 'inc/acf-testimonial.php';

// ACF block auto-registration from blocks/{slug}/ convention.
require_once CCC_THEME_DIR . 'inc/blocks.php';

// ACF-level validation tweaks (e.g., allow bare "#" in url fields).
require_once CCC_THEME_DIR . 'inc/acf-validations.php';

// Client child-theme importer (reads Style Guide JSON configs → scaffolds sg-{slug} child themes).
// Class is loaded but not auto-run; invoke via a shim or WP-CLI command as needed.
require_once CCC_THEME_DIR . 'inc/class-ccc-client-importer.php';

// WP-CLI commands: `wp style-guide new-client <slug> ...` and `wp style-guide import <json>`.
// Self-guards on ! defined('WP_CLI'), so safe to require unconditionally.
require_once CCC_THEME_DIR . 'inc/class-ccc-style-guide-cli.php';

// Admin page: Appearance → Style Guide Tokens (B3.2a read-only; editing phases in next).
if ( is_admin() ) {
	require_once CCC_THEME_DIR . 'inc/class-ccc-style-guide-admin.php';
}

// B4: /style-guide demo page + admin-bar shortcut.
require_once CCC_THEME_DIR . 'inc/class-ccc-demo-page.php';

// B4: substitute picsum placeholders for empty ACF image fields on the
// style-guide page only. Keeps production renders untouched.
require_once CCC_THEME_DIR . 'inc/demo-image-placeholders.php';

// Dynamic textures — reads settings.custom.textures from theme.json and emits
// CSS rules per registered entry. Admin UI for add/edit/delete ships alongside.
require_once CCC_THEME_DIR . 'inc/textures.php';
