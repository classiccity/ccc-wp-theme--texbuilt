<?php
/**
 * WP-CLI commands for the Style Guide.
 *
 * Registers:
 *   wp style-guide new-client <slug> --name=<name> --industry=<industry>
 *                               --colors=<csv>
 *                               [--heading-font=<family>] [--body-font=<family>]
 *                               [--icon-style=solid|regular|light|sharp-light]
 *                               [--activate]
 *
 *   wp style-guide import <json-path> [--source-fonts-dir=<path>] [--activate]
 *
 * The `new-client` command is the B3 replacement for `npm run new-client` in
 * the Next.js repo. It builds a config array in-memory from flags + defaults.json,
 * then hands it to CCC_Client_Importer for scaffolding.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class CCC_Style_Guide_CLI {

	/**
	 * Scaffold a new sg-{slug} child theme with the given brand tokens.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : URL-safe slug (lowercase, digits, hyphens). Becomes the child theme
	 *   directory name prefixed with `sg-`.
	 *
	 * --name=<name>
	 * : Human-readable client name (used in the theme header and style.css).
	 *
	 * --industry=<industry>
	 * : Industry label. Currently metadata-only; future passes may use it to
	 *   auto-select FontAwesome icons.
	 *
	 * --colors=<csv>
	 * : Comma-separated list of `name:hex` pairs. Hex may be `auto` to derive
	 *   an alt from its base (e.g. `cta-alt:auto` derives from `cta`). Must
	 *   cover every name in the parent theme's defaults.json `colorNames`.
	 *
	 * [--heading-font=<family>]
	 * : Google font family for headings. Defaults to the value in defaults.json.
	 *
	 * [--body-font=<family>]
	 * : Google font family for body copy. Defaults to the value in defaults.json.
	 *
	 * [--icon-style=<style>]
	 * : FontAwesome style. One of solid, regular, light, sharp-light.
	 *
	 * [--overrides=<json-path>]
	 * : Absolute path to a JSON file with partial overrides. Every key in the
	 *   file is deep-merged over the config we built from flags + defaults.
	 *   List-shaped keys (spacing, shadows) replace entirely; associative
	 *   keys (typeScale, buttonPadding, layout) deep-merge. Missing keys fall
	 *   through to defaults. Use this for the batchy typography / spacing /
	 *   shadow / custom-token settings that are tedious to pass as flags.
	 *
	 * [--seed-demo]
	 * : Copy the parent's canonical style-guide pattern into the new child
	 *   theme so `/style-guide` shows the full block sampler out of the box.
	 *   Default: on. Pass `--no-seed-demo` to skip.
	 *
	 * [--activate]
	 * : Switch to the new child theme after scaffolding.
	 *
	 * ## EXAMPLES
	 *
	 *     # Minimal — defaults for everything else.
	 *     wp style-guide new-client acme --name="Acme Corp" --industry=construction \
	 *         --colors=cta:#FF6B35,cta-alt:auto,primary:#2E86AB,primary-alt:auto,secondary:#A23B72,secondary-alt:auto,tertiary:#F18F01,tertiary-alt:auto,light:#F5F5F5,light-alt:auto,dark:#1A1A1A,dark-alt:auto
	 *
	 *     # Rich — with an overrides JSON for the batchy settings.
	 *     wp style-guide new-client acme --name="Acme Corp" --industry=construction \
	 *         --colors=... --heading-font=Inter --body-font=Roboto \
	 *         --overrides=/tmp/acme-overrides.json --activate
	 *
	 *   Where /tmp/acme-overrides.json might look like:
	 *     {
	 *       "typeScale": { "h1": 56, "h2": 42 },
	 *       "spacing":   [12, 24, 36, 48, 60],
	 *       "shadows":   [{ "name": "md", "value": "0 8px 20px rgba(0,0,0,.1)" }],
	 *       "buttonPadding": { "y": "1em", "x": "2em" },
	 *       "radius": "4px",
	 *       "bodyBackgroundColor": "#FAFAFA",
	 *       "layout": { "contentSize": "1100px" }
	 *     }
	 *
	 * @when after_wp_load
	 */
	public function new_client( $args, $assoc_args ) {
		list( $slug ) = $args;

		$slug = $this->validate_slug( $slug );
		$name     = $this->required( $assoc_args, 'name' );
		$industry = $this->required( $assoc_args, 'industry' );
		$colors_csv = $this->required( $assoc_args, 'colors' );

		$defaults = $this->load_defaults();

		$colors = $this->parse_colors_csv( $colors_csv, $defaults['colorNames'] );

		$heading_family = $assoc_args['heading-font'] ?? $defaults['fonts']['heading']['family'];
		$body_family    = $assoc_args['body-font']    ?? $defaults['fonts']['body']['family'];
		$icon_style     = $assoc_args['icon-style']   ?? $defaults['icons']['style'];

		$allowed_styles = array( 'solid', 'regular', 'light', 'sharp-light' );
		if ( ! in_array( $icon_style, $allowed_styles, true ) ) {
			WP_CLI::error( "Invalid --icon-style: {$icon_style}. Must be one of: " . implode( ', ', $allowed_styles ) );
		}

		// Build the config array — same shape CCC_Client_Importer expects when
		// reading a clients/*.json file.
		$config = array(
			'slug'                 => $slug,
			'name'                 => $name,
			'industry'             => $industry,
			'colors'               => $colors,
			'fonts'                => array(
				'heading' => array(
					'type'    => 'google',
					'family'  => $heading_family,
					'weights' => $defaults['fonts']['heading']['weights'],
				),
				'body'    => array(
					'type'    => 'google',
					'family'  => $body_family,
					'weights' => $defaults['fonts']['body']['weights'],
				),
			),
			'typeScale'            => $defaults['typeScale'],
			'radius'               => $defaults['radius'],
			'headingLetterSpacing' => $defaults['headingLetterSpacing'],
			'eyebrowLetterSpacing' => $defaults['eyebrowLetterSpacing'],
			'headingBaseFontSize'  => $defaults['headingBaseFontSize'],
			'bodyBaseFontSize'     => $defaults['bodyBaseFontSize'],
			'bodyBackgroundColor'  => $defaults['bodyBackgroundColor'],
			'buttonPadding'        => $defaults['buttonPadding'],
			'layout'               => $defaults['layout'],
			'spacing'              => $defaults['spacing'],
			'shadows'              => $defaults['shadows'],
			'icons'                => array(
				'style'    => $icon_style,
				'selected' => array(),
			),
		);

		// Apply --overrides if provided.
		if ( ! empty( $assoc_args['overrides'] ) ) {
			$overrides_path = $assoc_args['overrides'];
			if ( ! file_exists( $overrides_path ) ) {
				WP_CLI::error( "Overrides file not found: {$overrides_path}" );
			}
			$overrides_raw = file_get_contents( $overrides_path );
			$overrides     = json_decode( $overrides_raw, true );
			if ( ! is_array( $overrides ) ) {
				WP_CLI::error( "Could not parse overrides JSON: {$overrides_path}" );
			}
			// Guard: block keys that flags already own. Slug/name/industry/colors/
			// fonts/icons are authoritative from flags — if someone puts them in
			// overrides by mistake, we want a loud error, not a silent swap.
			$reserved = array( 'slug', 'name', 'industry', 'colors', 'fonts', 'icons' );
			$collision = array_intersect( array_keys( $overrides ), $reserved );
			if ( $collision ) {
				WP_CLI::error( 'Overrides JSON cannot set flag-owned keys: ' . implode( ', ', $collision ) );
			}
			$config = self::deep_merge( $config, $overrides );
			WP_CLI::log( '  overrides applied: ' . implode( ', ', array_keys( $overrides ) ) );
		}

		try {
			$importer = new CCC_Client_Importer( $config );
			$log = $importer->import();
		} catch ( Exception $e ) {
			WP_CLI::error( 'Scaffold failed: ' . $e->getMessage() );
		}

		foreach ( $log as $line ) {
			WP_CLI::log( '  ' . $line );
		}

		// Default-on --seed-demo. WP-CLI maps `--no-seed-demo` to seed-demo=false.
		$seed_demo = ! array_key_exists( 'seed-demo', $assoc_args ) || ! empty( $assoc_args['seed-demo'] );
		if ( $seed_demo ) {
			$this->seed_child_demo_pattern( $slug );
		}

		WP_CLI::success( "Created child theme sg-{$slug}" );

		if ( ! empty( $assoc_args['activate'] ) ) {
			switch_theme( 'sg-' . $slug );
			WP_CLI::success( "Activated sg-{$slug}" );
		} else {
			WP_CLI::log( "Activate with: wp theme activate sg-{$slug}" );
		}
	}

	/**
	 * Write a per-client style-guide pattern file into the new child theme.
	 *
	 * The file registers a pattern with slug `sg-{slug}/style-guide-content`
	 * whose body is a PHP `include` of the parent's canonical pattern output.
	 * This means: out of the box, the new client gets the full sampler; the
	 * user can later replace the include with their own block markup to
	 * customize per-client content without touching the parent theme.
	 */
	private function seed_child_demo_pattern( $slug ) {
		$parent_dir = get_template_directory();
		$child_dir  = dirname( $parent_dir ) . '/sg-' . $slug;
		$patterns_dir = $child_dir . '/patterns';
		if ( ! is_dir( $patterns_dir ) ) {
			mkdir( $patterns_dir, 0755, true );
		}

		$body  = "<?php\n";
		$body .= "/**\n";
		$body .= " * Title: Style Guide (per-client override)\n";
		$body .= " * Slug: sg-{$slug}/style-guide-content\n";
		$body .= " * Inserter: no\n";
		$body .= " *\n";
		$body .= " * Default body defers to the parent theme's canonical pattern so the\n";
		$body .= " * child theme gets the full sampler out of the box. Replace the\n";
		$body .= " * include below with your own block markup to customize what\n";
		$body .= " * /style-guide renders for this client — only the FIRST time you\n";
		$body .= " * want to diverge; after that, just edit the markup in place.\n";
		$body .= " */\n\n";
		$body .= "\$parent_pattern = get_template_directory() . '/patterns/style-guide.php';\n";
		$body .= "if ( file_exists( \$parent_pattern ) ) {\n";
		$body .= "\tinclude \$parent_pattern;\n";
		$body .= "}\n";

		$target = $patterns_dir . '/style-guide.php';
		file_put_contents( $target, $body );
		WP_CLI::log( "  wrote: patterns/style-guide.php (seed-demo)" );
	}

	/**
	 * Import an existing clients/*.json config (legacy path).
	 *
	 * ## OPTIONS
	 *
	 * <json-path>
	 * : Absolute path to the client JSON file.
	 *
	 * [--source-fonts-dir=<path>]
	 * : Absolute path to the Next.js public/ directory. Needed when the JSON
	 *   references custom (non-Google) fonts or per-client assets.
	 *
	 * [--activate]
	 * : Switch to the new child theme after importing.
	 *
	 * @when after_wp_load
	 */
	public function import( $args, $assoc_args ) {
		list( $json_path ) = $args;

		$source_fonts_dir = $assoc_args['source-fonts-dir'] ?? null;

		try {
			$importer = new CCC_Client_Importer( $json_path, $source_fonts_dir );
			$log = $importer->import();
		} catch ( Exception $e ) {
			WP_CLI::error( 'Import failed: ' . $e->getMessage() );
		}

		foreach ( $log as $line ) {
			WP_CLI::log( '  ' . $line );
		}

		$raw = json_decode( file_get_contents( $json_path ), true );
		$slug = $raw['slug'] ?? '';

		WP_CLI::success( "Imported sg-{$slug}" );

		if ( ! empty( $assoc_args['activate'] ) && $slug ) {
			switch_theme( 'sg-' . $slug );
			WP_CLI::success( "Activated sg-{$slug}" );
		}
	}

	/**
	 * Port a child theme's legacy `--bg-texture*` CSS custom properties into
	 * the new `settings.custom.textures` registry in its theme.json.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : Client slug (without the `sg-` prefix). The target theme must exist
	 *   at `wp-content/themes/sg-<slug>/`.
	 *
	 * [--dry-run]
	 * : Print what would change without touching any files.
	 *
	 * ## EXAMPLES
	 *
	 *     wp style-guide port-textures lumberock
	 *     wp style-guide port-textures lumberock --dry-run
	 *
	 * @when after_wp_load
	 */
	public function port_textures( $args, $assoc_args ) {
		list( $slug ) = $args;
		$slug = $this->validate_slug( $slug );
		$dry  = ! empty( $assoc_args['dry-run'] );

		$theme_dir = get_theme_root() . '/sg-' . $slug;
		if ( ! is_dir( $theme_dir ) ) {
			WP_CLI::error( "Theme not found: {$theme_dir}" );
		}
		$css_path        = $theme_dir . '/custom.css';
		$theme_json_path = $theme_dir . '/theme.json';
		if ( ! file_exists( $css_path ) ) {
			WP_CLI::error( "custom.css not found at {$css_path}" );
		}
		if ( ! file_exists( $theme_json_path ) ) {
			WP_CLI::error( "theme.json not found at {$theme_json_path}" );
		}

		$css = file_get_contents( $css_path );

		// 1. Extract the body { ... } declaration block that owns --bg-texture*.
		if ( ! preg_match( '/body\s*\{([^}]*)\}/s', $css, $body_match, PREG_OFFSET_CAPTURE ) ) {
			WP_CLI::error( "No `body { ... }` block found in custom.css" );
		}
		$body_block_raw = $body_match[0][0];
		$body_inner_raw = $body_match[1][0];

		// 2. Parse --bg-texture{,-{name}}:url('...'); entries + shared size/angle.
		//    `default` is the bare `--bg-texture`; other slugs come from the suffix.
		$textures = array();
		if ( preg_match_all( '/--bg-texture(?:-([a-z0-9][a-z0-9-]*))?\s*:\s*url\(\s*[\'"]?([^\'")]+)[\'"]?\s*\)\s*;?/i', $body_inner_raw, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $m ) {
				$suffix = isset( $m[1] ) ? strtolower( $m[1] ) : '';
				if ( in_array( $suffix, array( 'size', 'angle' ), true ) ) {
					// These are shared params, not texture definitions.
					continue;
				}
				$texture_slug = ( $suffix === '' ) ? 'default' : $suffix;
				$textures[ $texture_slug ] = trim( $m[2] );
			}
		}
		if ( ! $textures ) {
			WP_CLI::warning( 'No `--bg-texture*` url() entries found. Nothing to port.' );
			return;
		}

		// Shared size + angle (optional).
		$size  = null;
		$angle = null;
		if ( preg_match( '/--bg-texture-size\s*:\s*([^;]+);?/i', $body_inner_raw, $m ) ) {
			$size = trim( $m[1] );
		}
		if ( preg_match( '/--bg-texture-angle\s*:\s*([^;]+);?/i', $body_inner_raw, $m ) ) {
			$angle = trim( $m[1] );
		}

		// 3. For each texture URL, resolve to a local file + register as attachment.
		$upload_dir     = wp_get_upload_dir();
		$theme_assets   = $theme_dir . '/assets/';
		$theme_root_url = get_theme_root_uri() . '/sg-' . $slug . '/';

		$new_entries  = array();
		$attachments  = array();
		$log_lines    = array();

		foreach ( $textures as $t_slug => $url ) {
			// Strip the site URL/theme URL prefix to get the in-theme relative path.
			$relative_in_theme = null;
			if ( strpos( $url, $theme_root_url ) === 0 ) {
				$relative_in_theme = substr( $url, strlen( $theme_root_url ) );
			} else {
				// Try a looser match — url might use a different scheme/host variant.
				$host_rel = preg_replace( '#^https?://[^/]+/#', '/', $url );
				$site_rel = preg_replace( '#^https?://[^/]+/#', '/', $theme_root_url );
				if ( strpos( $host_rel, $site_rel ) === 0 ) {
					$relative_in_theme = substr( $host_rel, strlen( $site_rel ) );
				}
			}
			if ( ! $relative_in_theme ) {
				WP_CLI::warning( "Skipping '{$t_slug}' — URL doesn't resolve to this theme: {$url}" );
				continue;
			}

			$src_path = $theme_dir . '/' . ltrim( $relative_in_theme, '/' );
			if ( ! file_exists( $src_path ) ) {
				WP_CLI::warning( "Skipping '{$t_slug}' — file not found on disk: {$src_path}" );
				continue;
			}

			$filename  = basename( $src_path );
			$dest_path = trailingslashit( $upload_dir['path'] ) . $filename;
			$dest_url  = trailingslashit( $upload_dir['url'] ) . $filename;

			if ( $dry ) {
				$log_lines[] = "DRY: would copy {$src_path} → {$dest_path}";
				$log_lines[] = "DRY: would register attachment for {$dest_url}";
				$attachment_id = 0;
			} else {
				if ( ! file_exists( $dest_path ) && ! copy( $src_path, $dest_path ) ) {
					WP_CLI::warning( "Skipping '{$t_slug}' — copy failed: {$src_path} → {$dest_path}" );
					continue;
				}
				$filetype = wp_check_filetype( $filename, null );
				$attachment = array(
					'guid'           => $dest_url,
					'post_mime_type' => $filetype['type'] ?: 'image/png',
					'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);
				$attachment_id = wp_insert_attachment( $attachment, $dest_path );
				if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
					WP_CLI::warning( "Skipping '{$t_slug}' — wp_insert_attachment failed" );
					continue;
				}
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$metadata = wp_generate_attachment_metadata( $attachment_id, $dest_path );
				wp_update_attachment_metadata( $attachment_id, $metadata );
				$log_lines[] = "Imported {$filename} → attachment #{$attachment_id}";
			}

			$entry = array(
				'image'      => (int) $attachment_id,
				'mode'       => 'cover',
				'opacity'    => 1.0,
				'blend-mode' => 'normal',
				'size'       => $size  ?: '50% auto',
				'angle'      => $angle ?: '0deg',
			);
			$new_entries[ $t_slug ] = $entry;
			$attachments[ $t_slug ] = $attachment_id;
		}

		if ( ! $new_entries ) {
			WP_CLI::error( 'No textures could be ported. See warnings above.' );
		}

		// 4. Write into theme.json.
		$tj = json_decode( file_get_contents( $theme_json_path ), true );
		if ( ! is_array( $tj ) ) {
			WP_CLI::error( 'Could not parse theme.json' );
		}
		if ( ! isset( $tj['settings']['custom']['textures'] ) || ! is_array( $tj['settings']['custom']['textures'] ) ) {
			$tj['settings']['custom']['textures'] = array();
		}
		foreach ( $new_entries as $t_slug => $entry ) {
			$tj['settings']['custom']['textures'][ $t_slug ] = $entry;
		}

		// 5. Strip the `--bg-texture*` lines from custom.css body {} block.
		// Match one full line at a time — leading horizontal-whitespace only
		// (no \s, which would eat the preceding newline and de-indent the
		// following line). End the match at the line terminator.
		$stripped_inner = preg_replace(
			'/^[ \t]*--bg-texture(?:-[a-z0-9-]+)?[ \t]*:[^;\n]*;?[ \t]*(?:\r\n|\n|$)/m',
			'',
			$body_inner_raw
		);
		$new_body_block = str_replace( $body_inner_raw, $stripped_inner, $body_block_raw );
		// If the body {} is now empty (only whitespace), drop the whole block.
		if ( trim( $stripped_inner ) === '' ) {
			$new_css = str_replace( $body_block_raw, '', $css );
		} else {
			$new_css = str_replace( $body_block_raw, $new_body_block, $css );
		}

		if ( $dry ) {
			WP_CLI::log( '--- DRY RUN ---' );
			foreach ( $log_lines as $l ) WP_CLI::log( '  ' . $l );
			WP_CLI::log( sprintf( '  Would register %d texture(s): %s', count( $new_entries ), implode( ', ', array_keys( $new_entries ) ) ) );
			WP_CLI::log( '  Would strip --bg-texture* from custom.css body block.' );
			WP_CLI::log( '  (Nothing written.)' );
			return;
		}

		file_put_contents( $theme_json_path, wp_json_encode( $tj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		file_put_contents( $css_path, $new_css );

		foreach ( $log_lines as $l ) WP_CLI::log( '  ' . $l );
		WP_CLI::success( sprintf(
			'Ported %d texture(s) into sg-%s: %s',
			count( $new_entries ),
			$slug,
			implode( ', ', array_keys( $new_entries ) )
		) );
		WP_CLI::log( '  custom.css updated (--bg-texture* vars removed).' );
		WP_CLI::log( '  You may now delete the orphaned files in ' . $theme_dir . '/assets/ manually if desired.' );
	}

	/** ------------------------------------------------------------------ */
	/** Helpers                                                             */
	/** ------------------------------------------------------------------ */

	private function validate_slug( $slug ) {
		$slug = strtolower( trim( $slug ) );
		if ( ! preg_match( '/^[a-z0-9][a-z0-9-]*$/', $slug ) ) {
			WP_CLI::error( "Invalid slug '{$slug}'. Use lowercase letters, digits, and hyphens only." );
		}
		return $slug;
	}

	private function required( $assoc_args, $key ) {
		if ( empty( $assoc_args[ $key ] ) ) {
			WP_CLI::error( "Missing required --{$key}=<value>" );
		}
		return $assoc_args[ $key ];
	}

	private function load_defaults() {
		$path = get_template_directory() . '/config/defaults.json';
		if ( ! file_exists( $path ) ) {
			WP_CLI::error( "Missing defaults.json at: {$path}" );
		}
		$raw = json_decode( file_get_contents( $path ), true );
		if ( ! is_array( $raw ) ) {
			WP_CLI::error( "Invalid JSON in {$path}" );
		}
		return $raw;
	}

	/**
	 * Parse `name:hex,name:hex,...` into the [{ name, hex }] shape the importer
	 * consumes. Validates every expected color name is present — no silent
	 * fallback to random hexes.
	 */
	private function parse_colors_csv( $csv, $expected_names ) {
		$pairs = array_filter( array_map( 'trim', explode( ',', $csv ) ) );
		$by_name = array();
		foreach ( $pairs as $pair ) {
			if ( strpos( $pair, ':' ) === false ) {
				WP_CLI::error( "Bad --colors entry '{$pair}'. Expected format: name:hex" );
			}
			list( $name, $hex ) = array_map( 'trim', explode( ':', $pair, 2 ) );
			if ( $name === '' || $hex === '' ) {
				WP_CLI::error( "Bad --colors entry '{$pair}'. Empty name or hex." );
			}
			if ( $hex !== 'auto' && ! preg_match( '/^#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $hex ) ) {
				WP_CLI::error( "Bad hex for '{$name}': '{$hex}'. Use #RGB, #RRGGBB, or 'auto'." );
			}
			$by_name[ $name ] = $hex;
		}

		$missing = array();
		foreach ( $expected_names as $expected ) {
			if ( ! array_key_exists( $expected, $by_name ) ) {
				$missing[] = $expected;
			}
		}
		if ( $missing ) {
			WP_CLI::error( '--colors missing required name(s): ' . implode( ', ', $missing ) );
		}

		$colors = array();
		foreach ( $expected_names as $name ) {
			$colors[] = array(
				'name' => $name,
				'hex'  => $by_name[ $name ],
			);
		}
		return $colors;
	}

	/**
	 * Deep-merge an overrides array over a base array.
	 *
	 * Rules:
	 * - Associative arrays (e.g. `typeScale`, `buttonPadding`) recurse, so
	 *   partial overrides preserve sibling keys.
	 * - List-shaped arrays (e.g. `spacing`, `shadows`) REPLACE the base
	 *   entirely. Rationale: you rarely want to "patch in" one shadow entry;
	 *   when you override these, you usually mean the whole set.
	 * - Scalars replace.
	 *
	 * Uses `array_is_list()` (PHP 8.1+) for the list-vs-assoc check, which
	 * WP 6.2+ ships polyfilled.
	 */
	public static function deep_merge( array $base, array $overrides ) {
		foreach ( $overrides as $key => $value ) {
			if (
				is_array( $value )
				&& isset( $base[ $key ] )
				&& is_array( $base[ $key ] )
				&& ! array_is_list( $value )
				&& ! array_is_list( $base[ $key ] )
			) {
				$base[ $key ] = self::deep_merge( $base[ $key ], $value );
			} else {
				$base[ $key ] = $value;
			}
		}
		return $base;
	}
}

WP_CLI::add_command( 'style-guide', 'CCC_Style_Guide_CLI' );
