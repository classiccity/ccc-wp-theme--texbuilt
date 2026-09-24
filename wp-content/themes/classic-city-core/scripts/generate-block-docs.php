<?php
/**
 * generate-block-docs.php — builds docs/BLOCKS.md from the theme's block code.
 *
 * Standalone CLI script: no WordPress bootstrap. It stubs the handful of
 * WP/ACF/theme functions the blocks' fields.php files call at include time,
 * captures every acf_add_local_field_group() payload, and emits a per-block
 * field-key registry so the docs can never drift from the code.
 *
 * Usage:
 *   php scripts/generate-block-docs.php          # writes docs/BLOCKS.md
 *   npm run docs:blocks                          # same thing
 *
 * Exit codes: 0 on success, 1 if any block fails to parse.
 *
 * @package ClassicCityCore
 */

// ---------------------------------------------------------------------------
// 0. Environment.
// ---------------------------------------------------------------------------

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "This script must be run from the CLI.\n" );
	exit( 1 );
}

$theme_root = dirname( __DIR__ );
$blocks_dir = $theme_root . '/blocks';
$out_file   = $theme_root . '/docs/BLOCKS.md';

if ( ! is_dir( $blocks_dir ) ) {
	fwrite( STDERR, "Cannot find blocks/ directory at {$blocks_dir}\n" );
	exit( 1 );
}

// Collect non-fatal PHP warnings/notices raised while including block files
// so they can be reported per block.
$GLOBALS['ccc_docgen_warnings'] = array();
set_error_handler(
	function ( $errno, $errstr, $errfile, $errline ) {
		$GLOBALS['ccc_docgen_warnings'][] = basename( dirname( $errfile ) ) . '/' . basename( $errfile ) . ":{$errline} — {$errstr}";
		return true; // Swallow; we report at the end.
	}
);

// ---------------------------------------------------------------------------
// 1. WordPress / ACF / theme-helper stubs.
//
// fields.php files run at include time and call a small set of WP functions.
// Define just enough for them to execute standalone. Discovered by running
// against every blocks/*/fields.php + partials/*.php in this repo.
// ---------------------------------------------------------------------------

define( 'ABSPATH', $theme_root . '/' );

/** Translation stubs — return the string untouched. */
function __( $text, $domain = null ) { return $text; }
function _e( $text, $domain = null ) { echo $text; }
function _x( $text, $context, $domain = null ) { return $text; }
function esc_html__( $text, $domain = null ) { return $text; }
function esc_attr__( $text, $domain = null ) { return $text; }

/** Escaping stubs — pass-through is fine for doc generation. */
function esc_html( $text ) { return $text; }
function esc_attr( $text ) { return $text; }
function esc_url( $url ) { return $url; }
function sanitize_html_class( $class, $fallback = '' ) { return $class; }

/** Hook stubs — filters registered in fields.php never need to run here. */
function add_filter( $tag, $cb = null, $priority = 10, $args = 1 ) { return true; }
function add_action( $tag, $cb = null, $priority = 10, $args = 1 ) { return true; }
function apply_filters( $tag, $value ) { return $value; }
function do_action( $tag ) {}

/** Misc WP stubs referenced by partials' render helpers (never executed here). */
function get_field( $selector, $post_id = false ) { return null; }
function wp_prepare_attachment_for_js( $attachment ) { return array(); }

/**
 * Theme helper stub: the real ccc_palette_slug_choices() (inc/enqueue.php)
 * reads the active child theme's theme.json palette. Docs can't know the
 * palette, so return a sentinel that the table renderer translates into
 * "palette slugs (dynamic per child theme)".
 */
define( 'CCC_DOCGEN_PALETTE_SENTINEL', '__ccc_palette__' );
function ccc_palette_slug_choices() {
	return array( CCC_DOCGEN_PALETTE_SENTINEL => 'Palette slugs' );
}

/** Theme helper stub: real one reads settings.custom.textures from theme.json. */
function ccc_get_registered_textures() { return array(); }

/** ACF capture stub — every fields.php funnels its group(s) through here. */
$GLOBALS['ccc_captured_groups'] = array();
function acf_add_local_field_group( $group ) {
	$GLOBALS['ccc_captured_groups'][] = $group;
	return true;
}

// ---------------------------------------------------------------------------
// 2. Load shared partials (they define field helpers like
//    ccc_block_head_fields() that some blocks' fields.php call).
// ---------------------------------------------------------------------------

foreach ( glob( $theme_root . '/partials/*.php' ) as $partial ) {
	try {
		require_once $partial;
	} catch ( Throwable $e ) {
		fwrite( STDERR, 'Failed loading partial ' . basename( $partial ) . ': ' . $e->getMessage() . "\n" );
		exit( 1 );
	}
}

// ---------------------------------------------------------------------------
// 3. Walk blocks/*/ and collect per-block data.
// ---------------------------------------------------------------------------

$block_dirs = glob( $blocks_dir . '/*', GLOB_ONLYDIR );
sort( $block_dirs );

$blocks   = array();
$failures = array();

foreach ( $block_dirs as $dir ) {
	$slug = basename( $dir );

	// block.json --------------------------------------------------------
	$manifest_path = $dir . '/block.json';
	if ( ! is_file( $manifest_path ) ) {
		$failures[] = "{$slug}: missing block.json";
		continue;
	}
	$manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
	if ( ! is_array( $manifest ) ) {
		$failures[] = "{$slug}: block.json is not valid JSON (" . json_last_error_msg() . ')';
		continue;
	}

	// fields.php ----------------------------------------------------------
	$groups = array();
	$fields_path = $dir . '/fields.php';
	$warnings_before = count( $GLOBALS['ccc_docgen_warnings'] );
	if ( is_file( $fields_path ) ) {
		$GLOBALS['ccc_captured_groups'] = array();
		try {
			require $fields_path;
		} catch ( Throwable $e ) {
			$failures[] = "{$slug}: fields.php failed — " . $e->getMessage() . ' @ ' . basename( $e->getFile() ) . ':' . $e->getLine();
			continue;
		}
		$groups = $GLOBALS['ccc_captured_groups'];
	}
	$warnings = array_slice( $GLOBALS['ccc_docgen_warnings'], $warnings_before );

	// render.php ----------------------------------------------------------
	$render_path = $dir . '/render.php';
	$render_src  = is_file( $render_path ) ? (string) file_get_contents( $render_path ) : '';
	if ( '' === $render_src ) {
		$failures[] = "{$slug}: missing or empty render.php";
		continue;
	}

	$blocks[ $slug ] = array(
		'slug'        => $slug,
		'name'        => $manifest['name'] ?? "classic-city-core/{$slug}",
		'title'       => $manifest['title'] ?? $slug,
		'description' => trim( (string) ( $manifest['description'] ?? '' ) ),
		'groups'      => $groups,
		'hybrid'      => ( false !== strpos( $render_src, '<InnerBlocks' ) ),
		'view_js'     => is_file( $dir . '/view.js' ),
		'sg_hero'     => ( false !== strpos( $render_src, 'sg-hero' ) ),
		'warnings'    => $warnings,
	);
}

restore_error_handler();

if ( $failures ) {
	fwrite( STDERR, "Block parse failures:\n  - " . implode( "\n  - ", $failures ) . "\n" );
	exit( 1 );
}

if ( ! $blocks ) {
	fwrite( STDERR, "No blocks found under {$blocks_dir}\n" );
	exit( 1 );
}

// ---------------------------------------------------------------------------
// 4. Markdown helpers.
// ---------------------------------------------------------------------------

/** Escape a string for use inside a markdown table cell. */
function md_cell( $text ) {
	$text = str_replace( array( "\r", "\n" ), ' ', (string) $text );
	$text = str_replace( '|', '\\|', $text );
	return trim( $text );
}

/** Human summary of a choices array (select / button_group / radio / checkbox). */
function summarize_choices( $choices ) {
	if ( ! is_array( $choices ) || ! $choices ) {
		return '';
	}
	if ( array_key_exists( CCC_DOCGEN_PALETTE_SENTINEL, $choices ) ) {
		return 'choices: palette slugs (dynamic, per child theme)';
	}
	$keys = array_map( 'strval', array_keys( $choices ) );
	$keys = array_map(
		function ( $k ) {
			return '' === $k ? '(empty)' : "`{$k}`";
		},
		$keys
	);
	return 'choices: ' . implode( ' / ', $keys );
}

/**
 * Flatten a field list (recursing into sub_fields of repeaters/groups) into
 * table-row arrays: [ name_cell, key, type, required, notes ].
 *
 * @param array  $fields      ACF field definitions.
 * @param string $name_prefix Display prefix for nested fields ("rows → ").
 * @param string $key_prefix  Serialized-key prefix pattern ("rows_{i}_").
 */
function flatten_fields( $fields, $name_prefix = '', $key_prefix = '' ) {
	$rows = array();
	if ( ! is_array( $fields ) ) {
		return $rows;
	}
	foreach ( $fields as $field ) {
		if ( ! is_array( $field ) || empty( $field['name'] ) ) {
			continue; // Skip layout-only fields (tabs, messages) with no name.
		}
		$name = (string) $field['name'];
		$type = (string) ( $field['type'] ?? 'text' );
		$key  = (string) ( $field['key'] ?? '' );
		$req  = ! empty( $field['required'] ) ? 'Yes' : '—';

		$notes = array();
		if ( in_array( $type, array( 'select', 'button_group', 'radio', 'checkbox' ), true ) ) {
			$summary = summarize_choices( $field['choices'] ?? array() );
			if ( $summary ) {
				$notes[] = $summary;
			}
		}
		if ( 'image' === $type || 'file' === $type || 'gallery' === $type ) {
			if ( ! empty( $field['return_format'] ) ) {
				$notes[] = 'return_format: `' . $field['return_format'] . '`';
			}
		}
		if ( 'repeater' === $type ) {
			$minmax = array();
			if ( isset( $field['min'] ) && '' !== $field['min'] ) {
				$minmax[] = 'min ' . $field['min'];
			}
			if ( isset( $field['max'] ) && '' !== $field['max'] ) {
				$minmax[] = 'max ' . $field['max'];
			}
			if ( $minmax ) {
				$notes[] = 'rows: ' . implode( ', ', $minmax );
			}
			$notes[] = 'value = row count (int); rows serialize flat as `' . $key_prefix . $name . '_{i}_{subfield}`';
		}
		if ( 'group' === $type ) {
			$notes[] = 'sub-fields serialize flat as `' . $key_prefix . $name . '_{subfield}`';
		}
		if ( isset( $field['default_value'] ) && '' !== $field['default_value'] && ! is_array( $field['default_value'] ) ) {
			$notes[] = 'default: `' . $field['default_value'] . '`';
		}
		// Nested field: spell out its flat serialized key pattern.
		if ( '' !== $key_prefix && 'repeater' !== $type && 'group' !== $type ) {
			$notes[] = 'serialized key: `' . $key_prefix . $name . '`';
		}

		$rows[] = array(
			$name_prefix . '`' . $name . '`',
			$key ? '`' . $key . '`' : '—',
			$type,
			$req,
			implode( '; ', array_map( 'md_cell', $notes ) ),
		);

		// Recurse into repeater / group sub-fields.
		if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
			$child_name_prefix = $name_prefix . $name . ' → ';
			$child_key_prefix  = 'repeater' === $type
				? $key_prefix . $name . '_{i}_'
				: $key_prefix . $name . '_';
			foreach ( flatten_fields( $field['sub_fields'], $child_name_prefix, $child_key_prefix ) as $child_row ) {
				$rows[] = $child_row;
			}
		}
	}
	return $rows;
}

// ---------------------------------------------------------------------------
// 5. Emit docs/BLOCKS.md.
// ---------------------------------------------------------------------------

$md   = array();
$md[] = '<!-- GENERATED FILE — do not edit by hand. Regenerate: npm run docs:blocks -->';
$md[] = '';
$md[] = '# Block Field-Key Registry';
$md[] = '';
$md[] = 'Generated from `blocks/*/block.json`, `blocks/*/fields.php`, `blocks/*/render.php`,';
$md[] = 'and the authored `blocks/*/usage.md` by `scripts/generate-block-docs.php`. If this';
$md[] = 'file disagrees with the code, regenerate it.';
$md[] = '';
$md[] = '## How ACF block data is stored';
$md[] = '';
$md[] = 'ACF blocks persist their data inside the Gutenberg block comment as a `data` object.';
$md[] = 'Every field appears **twice**:';
$md[] = '';
$md[] = '```';
$md[] = '"image": 123,                     ← field name : value';
$md[] = '"_image": "field_hero_image"      ← _name : the ACF field KEY';
$md[] = '```';
$md[] = '';
$md[] = 'The `_name` entry tells ACF which field definition the value belongs to. **A wrong';
$md[] = 'field key renders blank on the front end — always use the keys from this file.**';
$md[] = '';
$md[] = '**Repeaters** flatten: the repeater\'s own entry is the row count (integer), and each';
$md[] = 'row\'s fields are flat keys `{repeater}_{i}_{subfield}` with matching';
$md[] = '`_{repeater}_{i}_{subfield}` key entries (e.g. `cards_0_title` / `_cards_0_title`).';
$md[] = '';
$md[] = '**Hybrid vs field-driven:** *Hybrid (InnerBlocks)* blocks render an `<InnerBlocks />`';
$md[] = 'slot, so authored child blocks live between the opening and closing block comments.';
$md[] = '*Field-driven* blocks have no slot and are written self-closing (`<!-- wp:… /-->`).';
$md[] = '';

// Summary index.
// ---------------------------------------------------------------------------
// Usage layer: blocks/{slug}/usage.md — authored semantic guidance (purpose,
// content shape, use-when / avoid-when). Required for EVERY block; the run
// fails if any are missing so a new block cannot ship without one.
// ---------------------------------------------------------------------------
$missing_usage = array();
foreach ( $blocks as &$b ) {
	$usage_file   = $blocks_dir . '/' . $b['slug'] . '/usage.md';
	$b['usage']   = '';
	$b['purpose'] = '';
	if ( is_file( $usage_file ) ) {
		$usage = trim( (string) file_get_contents( $usage_file ) );
		$usage = preg_replace( '/^#[^\n]*\n+/', '', $usage ); // Drop its own H1; BLOCKS.md owns headings.
		$b['usage'] = $usage;
		if ( preg_match( '/\*\*Purpose:\*\*\s*(.+)/', $usage, $m ) ) {
			$b['purpose'] = trim( $m[1] );
		}
	} else {
		$missing_usage[] = $b['slug'];
	}
}
unset( $b );

$md[] = '## Choosing blocks (the usage layer)';
$md[] = '';
$md[] = 'Every block folder carries an authored `usage.md` — purpose, content shape,';
$md[] = 'use-when / avoid-when — inlined into its section below. That layer is what a';
$md[] = 'content-import session uses to map source content onto blocks. Before reaching';
$md[] = 'for ANY ACF block, check whether plain core blocks are the right tool:';
$md[] = '[`CORE_BLOCKS_USAGE.md`](./CORE_BLOCKS_USAGE.md). The non-negotiable composition';
$md[] = 'rules live in [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md).';
$md[] = '';
$md[] = '## Index';
$md[] = '';
$md[] = '| Block (slug) | Title | Kind | Purpose |';
$md[] = '| --- | --- | --- | --- |';
foreach ( $blocks as $b ) {
	$kind = $b['hybrid'] ? 'Hybrid (InnerBlocks)' : 'Field-driven';
	$md[] = '| [`' . $b['slug'] . '`](#' . $b['slug'] . ') | ' . md_cell( $b['title'] ) . ' | ' . $kind . ' | ' . md_cell( $b['purpose'] ) . ' |';
}
$md[] = '';
$md[] = '---';
$md[] = '';

// Per-block sections.
foreach ( $blocks as $b ) {
	$md[] = '### ' . $b['slug'];
	$md[] = '';
	$line = '**' . md_cell( $b['title'] ) . '** (`' . $b['name'] . '`)';
	if ( '' !== $b['description'] ) {
		$line .= ' — ' . md_cell( $b['description'] );
	}
	$md[] = $line;
	$md[] = '';

	$meta   = array();
	$meta[] = $b['hybrid'] ? 'Kind: Hybrid (InnerBlocks)' : 'Kind: Field-driven';
	$meta[] = 'view.js: ' . ( $b['view_js'] ? 'yes' : 'no' );
	if ( $b['sg_hero'] ) {
		$meta[] = 'Emits `.sg-hero` marker';
	}
	$md[] = '- ' . implode( "\n- ", $meta );
	$md[] = '';

	if ( '' !== $b['usage'] ) {
		$md[] = $b['usage'];
		$md[] = '';
	} else {
		$md[] = '> ⚠️ **Missing `blocks/' . $b['slug'] . '/usage.md`** — every block requires one; see the checklist in `docs/BLOCK_AUTHORING.md`.';
		$md[] = '';
	}

	$rows = array();
	foreach ( $b['groups'] as $group ) {
		$rows = array_merge( $rows, flatten_fields( $group['fields'] ?? array() ) );
	}

	if ( $rows ) {
		$md[] = '| Field name | ACF key | Type | Required | Notes |';
		$md[] = '| --- | --- | --- | --- | --- |';
		foreach ( $rows as $r ) {
			$md[] = '| ' . implode( ' | ', $r ) . ' |';
		}
	} else {
		$md[] = '_No ACF fields — all content is authored via InnerBlocks or block supports._';
	}

	if ( $b['warnings'] ) {
		$md[] = '';
		$md[] = '> Parse warnings: ' . md_cell( implode( ' • ', $b['warnings'] ) );
	}

	$md[] = '';
}

$output = implode( "\n", $md );
// Normalize trailing whitespace / final newline.
$output = preg_replace( '/[ \t]+$/m', '', $output );
$output = rtrim( $output ) . "\n";

if ( false === file_put_contents( $out_file, $output ) ) {
	fwrite( STDERR, "Failed writing {$out_file}\n" );
	exit( 1 );
}

// ---------------------------------------------------------------------------
// 6. Console summary.
// ---------------------------------------------------------------------------

$hybrid_count = count( array_filter( $blocks, function ( $b ) { return $b['hybrid']; } ) );
$field_count  = count( $blocks ) - $hybrid_count;
$no_fields    = array_keys( array_filter( $blocks, function ( $b ) { return empty( $b['groups'] ) || ! array_filter( array_map( function ( $g ) { return $g['fields'] ?? array(); }, $b['groups'] ) ); } ) );
$warned       = array_keys( array_filter( $blocks, function ( $b ) { return ! empty( $b['warnings'] ); } ) );

echo 'Wrote ' . $out_file . "\n";
echo 'Blocks: ' . count( $blocks ) . ' (' . $hybrid_count . ' hybrid, ' . $field_count . " field-driven)\n";
if ( $no_fields ) {
	echo 'Blocks with no ACF fields: ' . implode( ', ', $no_fields ) . "\n";
}
if ( $warned ) {
	echo 'Blocks with parse warnings: ' . implode( ', ', $warned ) . "\n";
}
if ( $missing_usage ) {
	echo "\nFAIL — blocks missing usage.md (required for every block, parent or child):\n";
	echo '  ' . implode( ', ', $missing_usage ) . "\n";
	echo "Write blocks/{slug}/usage.md (format: docs/BLOCK_AUTHORING.md checklist step 7), then re-run.\n";
	exit( 1 );
}
exit( 0 );
