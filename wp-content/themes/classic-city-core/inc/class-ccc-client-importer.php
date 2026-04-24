<?php
/**
 * Client importer — reads a Style Guide JSON config (from the Next.js repo's
 * clients/{slug}.json) and scaffolds a `sg-{slug}` child theme.
 *
 * Generated files:
 *   wp-content/themes/sg-{slug}/
 *     ├── style.css               — theme header only
 *     ├── theme.json              — palette/typography/spacing/custom overrides
 *     ├── functions.php           — enqueues custom.css + Google Fonts if needed
 *     ├── custom.css              — translated from cfg.customCSS (optional)
 *     └── assets/fonts/{family}/  — copied .woff2/.woff/.ttf files (custom fonts only)
 *
 * Callable from PHP:
 *   CCC_Client_Importer::import( '/path/to/client.json' );
 *
 * Or from WP-CLI (if available):
 *   wp style-guide:import /path/to/client.json [--activate]
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CCC_Client_Importer {

	private $json;
	private $slug;
	private $themes_dir;
	private $child_dir;
	private $child_uri;
	private $source_fonts_dir;
	private $log = array();

	/**
	 * Accepts either a JSON file path (legacy) OR a config array (for the
	 * new-client CLI, which builds the config in-memory from flags + defaults).
	 *
	 * @param string|array $input           JSON path or config array.
	 * @param string|null  $source_fonts_dir Path to the Next.js public/ directory
	 *                                        (needed to resolve custom font file
	 *                                        paths that JSON stores as `/fonts/...`).
	 *                                        Pass null when scaffolding from config.
	 */
	public function __construct( $input, $source_fonts_dir = null ) {
		if ( is_array( $input ) ) {
			$this->json = $input;
		} elseif ( is_string( $input ) ) {
			if ( ! file_exists( $input ) ) {
				throw new Exception( "JSON not found: {$input}" );
			}
			$raw = file_get_contents( $input );
			$this->json = json_decode( $raw, true );
			if ( ! is_array( $this->json ) ) {
				throw new Exception( "Invalid JSON: {$input}" );
			}
		} else {
			throw new Exception( 'CCC_Client_Importer expects a JSON path or config array' );
		}
		$this->slug = $this->json['slug'] ?? '';
		if ( ! $this->slug ) {
			throw new Exception( "JSON missing required 'slug'" );
		}
		$this->themes_dir       = dirname( get_template_directory() ); // parent of classic-city-core
		$this->child_dir        = $this->themes_dir . '/sg-' . $this->slug;
		$this->child_uri        = content_url( '/themes/sg-' . $this->slug );
		$this->source_fonts_dir = $source_fonts_dir;
	}

	/**
	 * Main entry point. Creates the full child theme directory tree.
	 *
	 * @return array Log of actions taken.
	 */
	public function import() {
		if ( ! is_dir( $this->child_dir ) ) {
			mkdir( $this->child_dir, 0755, true );
			$this->log[] = "mkdir: {$this->child_dir}";
		} else {
			$this->log[] = "exists: {$this->child_dir} (will overwrite files)";
		}

		$this->write_style_css();
		$this->write_theme_json();
		$this->copy_custom_fonts();
		$this->copy_client_assets();
		$this->write_custom_css();
		$this->write_functions_php();

		return $this->log;
	}

	/**
	 * Copy top-level image/texture files from the Next.js repo's
	 * `public/clients/{slug}/` directory into the child theme's
	 * `assets/` directory. These typically power customCSS bg textures.
	 * Subdirectories (e.g. `icons/`) are skipped.
	 */
	private function copy_client_assets() {
		if ( ! $this->source_fonts_dir ) return;
		$src_dir = rtrim( $this->source_fonts_dir, '/' ) . '/clients/' . $this->slug;
		if ( ! is_dir( $src_dir ) ) return;

		$dest_dir = $this->child_dir . '/assets';
		if ( ! is_dir( $dest_dir ) ) {
			mkdir( $dest_dir, 0755, true );
		}

		$copied = 0;
		foreach ( scandir( $src_dir ) as $entry ) {
			if ( $entry === '.' || $entry === '..' ) continue;
			$src = $src_dir . '/' . $entry;
			if ( is_file( $src ) ) {
				copy( $src, $dest_dir . '/' . $entry );
				$copied++;
			}
		}
		if ( $copied ) {
			$this->log[] = "copied {$copied} client asset(s) into assets/";
		}
	}

	/** ------------------------------------------------------------------ */
	/** Color utilities — ported from lib/color-utils.ts                    */
	/** ------------------------------------------------------------------ */

	private static function normalize_hex( $hex ) {
		$h = ltrim( trim( $hex ), '#' );
		if ( strlen( $h ) === 3 ) {
			$h = $h[0].$h[0].$h[1].$h[1].$h[2].$h[2];
		}
		if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $h ) ) {
			throw new Exception( "Invalid hex: {$hex}" );
		}
		return '#' . strtoupper( $h );
	}

	private static function hex_to_rgb( $hex ) {
		$h = substr( self::normalize_hex( $hex ), 1 );
		return array(
			'r' => hexdec( substr( $h, 0, 2 ) ),
			'g' => hexdec( substr( $h, 2, 2 ) ),
			'b' => hexdec( substr( $h, 4, 2 ) ),
		);
	}

	private static function rgb_to_hex( $rgb ) {
		$to_hex = function ( $n ) {
			$n = max( 0, min( 255, (int) round( $n ) ) );
			return str_pad( dechex( $n ), 2, '0', STR_PAD_LEFT );
		};
		return '#' . strtoupper( $to_hex( $rgb['r'] ) . $to_hex( $rgb['g'] ) . $to_hex( $rgb['b'] ) );
	}

	private static function rgb_to_hsl( $rgb ) {
		$rn = $rgb['r'] / 255;
		$gn = $rgb['g'] / 255;
		$bn = $rgb['b'] / 255;
		$max = max( $rn, $gn, $bn );
		$min = min( $rn, $gn, $bn );
		$h = 0; $s = 0;
		$l = ( $max + $min ) / 2;
		if ( $max !== $min ) {
			$d = $max - $min;
			$s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );
			if ( $max === $rn ) {
				$h = ( $gn - $bn ) / $d + ( $gn < $bn ? 6 : 0 );
			} elseif ( $max === $gn ) {
				$h = ( $bn - $rn ) / $d + 2;
			} else {
				$h = ( $rn - $gn ) / $d + 4;
			}
			$h /= 6;
		}
		return array( 'h' => $h * 360, 's' => $s * 100, 'l' => $l * 100 );
	}

	private static function hsl_to_rgb( $hsl ) {
		$hn = $hsl['h'] / 360;
		$sn = $hsl['s'] / 100;
		$ln = $hsl['l'] / 100;
		if ( $sn == 0 ) {
			$v = $ln * 255;
			return array( 'r' => $v, 'g' => $v, 'b' => $v );
		}
		$hue2rgb = function ( $p, $q, $t ) {
			if ( $t < 0 ) $t += 1;
			if ( $t > 1 ) $t -= 1;
			if ( $t < 1/6 ) return $p + ( $q - $p ) * 6 * $t;
			if ( $t < 1/2 ) return $q;
			if ( $t < 2/3 ) return $p + ( $q - $p ) * ( 2/3 - $t ) * 6;
			return $p;
		};
		$q = $ln < 0.5 ? $ln * ( 1 + $sn ) : $ln + $sn - $ln * $sn;
		$p = 2 * $ln - $q;
		return array(
			'r' => $hue2rgb( $p, $q, $hn + 1/3 ) * 255,
			'g' => $hue2rgb( $p, $q, $hn ) * 255,
			'b' => $hue2rgb( $p, $q, $hn - 1/3 ) * 255,
		);
	}

	private static function generate_alt( $hex, $delta = 10 ) {
		$hsl = self::rgb_to_hsl( self::hex_to_rgb( $hex ) );
		$direction = $hsl['l'] < 50 ? 1 : -1;
		$hsl['l'] = max( 0, min( 100, $hsl['l'] + $direction * $delta ) );
		return self::rgb_to_hex( self::hsl_to_rgb( $hsl ) );
	}

	private static function relative_luminance( $rgb ) {
		$channel = function ( $c ) {
			$cs = $c / 255;
			return $cs <= 0.03928 ? $cs / 12.92 : pow( ( $cs + 0.055 ) / 1.055, 2.4 );
		};
		return 0.2126 * $channel( $rgb['r'] ) + 0.7152 * $channel( $rgb['g'] ) + 0.0722 * $channel( $rgb['b'] );
	}

	private static function contrast_ratio( $hex_a, $hex_b ) {
		$la = self::relative_luminance( self::hex_to_rgb( $hex_a ) );
		$lb = self::relative_luminance( self::hex_to_rgb( $hex_b ) );
		$light = max( $la, $lb );
		$dark  = min( $la, $lb );
		return ( $light + 0.05 ) / ( $dark + 0.05 );
	}

	private static function opposite_color( $hex ) {
		return self::contrast_ratio( $hex, '#FFFFFF' ) >= self::contrast_ratio( $hex, '#000000' )
			? '#FFFFFF' : '#000000';
	}

	private static function slugify( $name ) {
		$s = strtolower( trim( $name ) );
		$s = preg_replace( '/[^a-z0-9]+/', '-', $s );
		$s = trim( $s, '-' );
		return $s;
	}

	/** Instance-level wrapper so existing import() flow keeps working. */
	private function resolve_colors() {
		$warnings = array();
		$resolved = self::resolve_palette( $this->json['colors'] ?? array(), $warnings );
		foreach ( $warnings as $w ) {
			$this->log[] = $w;
		}
		return $resolved;
	}

	/**
	 * Resolve a palette — returns array of { slug, name, hex, opposite }.
	 *
	 * Public + static so the admin save-handler can call it without
	 * instantiating the full importer.
	 *
	 * Three-pass resolution:
	 *   1. Collect every explicit hex into a name→hex map.
	 *   2. Resolve `auto` entries from their base's hex.
	 *   3. Compute opposites: bases first, then `-alt` (or `derivedFrom`)
	 *      entries INHERIT the base's opposite verbatim. This keeps button
	 *      text color stable when a bg hovers from base to alt (decision #15).
	 *      Rule applies whether the alt's hex is auto or explicit — it's about
	 *      the naming, not the derivation.
	 *
	 * @param array $colors   Array of { name, hex, derivedFrom? }.
	 * @param array $warnings Optional out-param; appended to on recoverable issues.
	 * @return array Resolved palette.
	 * @throws Exception When an `auto` alt has no resolvable base.
	 */
	public static function resolve_palette( array $colors, array &$warnings = array() ) {
		// Pass 1: explicit hexes.
		$hex_by_name = array();
		foreach ( $colors as $c ) {
			if ( ( $c['hex'] ?? '' ) !== 'auto' ) {
				$hex_by_name[ $c['name'] ] = self::normalize_hex( $c['hex'] );
			}
		}

		// Pass 2: resolve auto hexes.
		foreach ( $colors as $c ) {
			if ( ( $c['hex'] ?? '' ) === 'auto' ) {
				$base_name = $c['derivedFrom'] ?? preg_replace( '/-alt$/', '', $c['name'] );
				$base_hex  = $hex_by_name[ $base_name ] ?? null;
				if ( ! $base_hex ) {
					throw new Exception( "Cannot auto-derive {$c['name']}: base '{$base_name}' not found" );
				}
				$hex_by_name[ $c['name'] ] = self::generate_alt( $base_hex );
			}
		}

		// Pass 3: opposites. Base-like colors first; alts inherit.
		$opposite_by_name = array();
		foreach ( $colors as $c ) {
			if ( ! self::is_alt( $c ) ) {
				$opposite_by_name[ $c['name'] ] = self::opposite_color( $hex_by_name[ $c['name'] ] );
			}
		}
		foreach ( $colors as $c ) {
			if ( self::is_alt( $c ) ) {
				$base_name = $c['derivedFrom'] ?? preg_replace( '/-alt$/', '', $c['name'] );
				if ( isset( $opposite_by_name[ $base_name ] ) ) {
					$opposite_by_name[ $c['name'] ] = $opposite_by_name[ $base_name ];
				} else {
					$opposite_by_name[ $c['name'] ] = self::opposite_color( $hex_by_name[ $c['name'] ] );
					$warnings[] = "warning: {$c['name']} has no base '{$base_name}' in palette; opposite computed from own hex";
				}
			}
		}

		$resolved = array();
		foreach ( $colors as $c ) {
			$resolved[] = array(
				'slug'     => self::slugify( $c['name'] ),
				'name'     => $c['name'],
				'hex'      => $hex_by_name[ $c['name'] ],
				'opposite' => $opposite_by_name[ $c['name'] ],
			);
		}
		return $resolved;
	}

	/** Normalize a hex string — public so callers can validate user input. */
	public static function normalize_hex_public( $hex ) {
		return self::normalize_hex( $hex );
	}

	/** A color is an "alt" if named with -alt suffix or has derivedFrom. */
	private static function is_alt( $c ) {
		if ( ! empty( $c['derivedFrom'] ) ) {
			return true;
		}
		return (bool) preg_match( '/-alt$/', $c['name'] ?? '' );
	}

	/** ------------------------------------------------------------------ */
	/** File generators                                                     */
	/** ------------------------------------------------------------------ */

	private function write_style_css() {
		$name     = $this->json['name'] ?? $this->slug;
		$industry = $this->json['industry'] ?? '';
		$desc     = $industry ? "Style guide child theme for {$name} ({$industry})." : "Style guide child theme for {$name}.";

		$css  = "/*\n";
		$css .= "Theme Name:     Style Guide — {$name}\n";
		$css .= "Description:    {$desc} Auto-generated by ccc-client-importer.\n";
		$css .= "Author:         Classic City Core\n";
		$css .= "Template:       classic-city-core\n";
		$css .= "Version:        1.0.0\n";
		$css .= "Text Domain:    sg-{$this->slug}\n";
		$css .= "*/\n";

		file_put_contents( $this->child_dir . '/style.css', $css );
		$this->log[] = "wrote: style.css";
	}

	private function write_theme_json() {
		$resolved = $this->resolve_colors();

		// Palette + opposites.
		$palette = array();
		$opposites = array();
		foreach ( $resolved as $c ) {
			$palette[] = array(
				'slug'  => $c['slug'],
				'name'  => $c['name'],
				'color' => $c['hex'],
			);
			$opposites[ $c['slug'] . '-opposite' ] = $c['opposite'];
		}

		// Heading sizes (custom.fs.h-1 ... h-6 — WP hyphenates letter-digit).
		// Each heading emits its desktop ceiling (h-N) AND its mobile floor
		// (h-N-min). Option C fluid typography wires both into a clamp() on
		// `styles.elements.hN.typography.fontSize` in the PARENT theme.json.
		$ts = $this->json['typeScale'] ?? array();
		$fs = array();
		foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $k ) {
			$n = substr( $k, 1 ); // "1", "2", ...
			if ( isset( $ts[ $k ] ) ) {
				$fs[ 'h-' . $n ] = $ts[ $k ] . 'px';
			}
			$min_key = $k . 'Min';
			if ( isset( $ts[ $min_key ] ) ) {
				$fs[ 'h-' . $n . '-min' ] = $ts[ $min_key ] . 'px';
			}
		}

		// Body font-size presets.
		$body_slug_map = array(
			'bodyXs' => 'x-small',
			'bodySm' => 'small',
			'bodyMd' => 'medium',
			'bodyLg' => 'large',
			'body2xl' => 'x-large',
		);
		$font_sizes = array();
		foreach ( $body_slug_map as $src => $slug ) {
			if ( isset( $ts[ $src ] ) ) {
				$font_sizes[] = array(
					'slug' => $slug,
					'name' => ucwords( str_replace( '-', ' ', $slug ) ),
					'size' => $ts[ $src ] . 'px',
				);
			}
		}

		// Font families.
		$font_families = array();
		foreach ( array( 'heading', 'body' ) as $role ) {
			$def = $this->json['fonts'][ $role ] ?? null;
			if ( ! $def ) continue;
			$family_name = $def['family'];
			$fallback    = $this->font_fallback( $family_name );
			$entry = array(
				'slug'       => $role,
				'name'       => ucfirst( $role ),
				'fontFamily' => "'{$family_name}', {$fallback}",
			);
			// Custom (local) fonts get fontFace entries.
			if ( ( $def['type'] ?? '' ) === 'custom' && ! empty( $def['files'] ) ) {
				$face = array();
				foreach ( $def['files'] as $file ) {
					$filename = basename( $file['path'] );
					$uri = $this->child_uri . '/assets/fonts/' . rawurlencode( $family_name ) . '/' . $filename;
					$face[] = array(
						'fontFamily' => $family_name,
						'fontStyle'  => $file['style'] ?? 'normal',
						'fontWeight' => (string) ( $file['weight'] ?? 400 ),
						'src'        => array( $uri ),
					);
				}
				$entry['fontFace'] = $face;
			}
			$font_families[] = $entry;
		}

		// Spacing.
		$spacing = array();
		foreach ( ( $this->json['spacing'] ?? array() ) as $n ) {
			$spacing[] = array(
				'slug' => (string) $n,
				'name' => (string) $n,
				'size' => $n . 'px',
			);
		}

		// Shadows.
		$shadows = array();
		foreach ( ( $this->json['shadows'] ?? array() ) as $s ) {
			$shadows[] = array(
				'slug'   => $s['name'],
				'name'   => ucfirst( $s['name'] ),
				'shadow' => $s['value'],
			);
		}

		// Custom block.
		$custom = array(
			'color'   => $opposites,
			'fs'      => $fs,
			'radius'  => array( 'default' => $this->json['radius'] ?? '8px' ),
			'heading' => array(
				'letter-spacing'  => $this->json['headingLetterSpacing'] ?? '-0.02em',
				'base-font-size'  => $this->json['headingBaseFontSize'] ?? '1.2rem',
			),
			'eyebrow' => array(
				'letter-spacing' => $this->json['eyebrowLetterSpacing'] ?? '0.12em',
			),
			'body'    => array(
				'base-font-size' => $this->json['bodyBaseFontSize'] ?? '1rem',
				'bg'             => $this->json['bodyBackgroundColor'] ?? '#FFFFFF',
			),
			'btn'     => array(
				'padding-y' => $this->json['buttonPadding']['y'] ?? '0.75em',
				'padding-x' => $this->json['buttonPadding']['x'] ?? '1.5em',
			),
			'layout'  => array(
				'narrow-size' => $this->json['layout']['narrowSize'] ?? '800px',
			),
			'icons'   => array(
				'style' => $this->json['icons']['style'] ?? 'solid',
			),
		);

		$theme_json = array(
			'$schema'  => 'https://schemas.wp.org/trunk/theme.json',
			'version'  => 3,
			'settings' => array(
				'color'      => array( 'palette' => $palette ),
				'typography' => array(
					'fontSizes'    => $font_sizes,
					'fontFamilies' => $font_families,
				),
				'spacing'    => array( 'spacingSizes' => $spacing ),
				'shadow'     => array( 'presets' => $shadows ),
				'layout'     => array(
					'contentSize' => $this->json['layout']['contentSize'] ?? '1200px',
					'wideSize'    => $this->json['layout']['wideSize'] ?? '1400px',
				),
				'custom'     => $custom,
			),
		);

		// Filter: drop empty custom.fs, empty font_families, etc. to keep output tight.
		if ( empty( $fs ) ) unset( $theme_json['settings']['custom']['fs'] );
		if ( empty( $font_families ) ) unset( $theme_json['settings']['typography']['fontFamilies'] );

		file_put_contents(
			$this->child_dir . '/theme.json',
			wp_json_encode( $theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
		);
		$this->log[] = "wrote: theme.json (" . count( $palette ) . " colors, " . count( $font_sizes ) . " body sizes, " . count( $font_families ) . " fonts, " . count( $spacing ) . " spacings, " . count( $shadows ) . " shadows)";
	}

	private function font_fallback( $family ) {
		return self::font_fallback_public( $family );
	}

	/** Public variant so the admin font-family editor can reuse the same mapping. */
	public static function font_fallback_public( $family ) {
		$f = strtolower( $family );
		if ( preg_match( '/serif|garamond|playfair|merriweather|lora/', $f ) ) {
			return 'Georgia, serif';
		}
		if ( preg_match( '/mono|code|courier/', $f ) ) {
			return 'ui-monospace, monospace';
		}
		return 'system-ui, -apple-system, sans-serif';
	}

	private function copy_custom_fonts() {
		$copied = 0;
		foreach ( array( 'heading', 'body' ) as $role ) {
			$def = $this->json['fonts'][ $role ] ?? null;
			if ( ! $def || ( $def['type'] ?? '' ) !== 'custom' ) continue;
			$family = $def['family'];
			$dest_dir = $this->child_dir . '/assets/fonts/' . $family;
			if ( ! is_dir( $dest_dir ) ) {
				mkdir( $dest_dir, 0755, true );
			}
			foreach ( ( $def['files'] ?? array() ) as $file ) {
				// JSON path looks like "/fonts/Otilito%20Sans/TBJOtilito-Light.woff2".
				// Resolve against the Next.js repo's public/ directory.
				$rel_path = rawurldecode( $file['path'] );
				if ( ! $this->source_fonts_dir ) {
					$this->log[] = "  font skipped (no source): {$rel_path}";
					continue;
				}
				$src_file = rtrim( $this->source_fonts_dir, '/' ) . $rel_path;
				if ( ! file_exists( $src_file ) ) {
					$this->log[] = "  font MISSING at source: {$src_file}";
					continue;
				}
				$dest_file = $dest_dir . '/' . basename( $rel_path );
				copy( $src_file, $dest_file );
				$copied++;
			}
		}
		if ( $copied ) {
			$this->log[] = "copied {$copied} font file(s)";
		}
	}

	private function write_custom_css() {
		$css = $this->json['customCSS'] ?? '';
		if ( empty( trim( $css ) ) ) {
			return;
		}

		// Light translation from Next.js preview conventions to WP conventions:
		//   - `.sg-preview` (Next.js preview wrapper) → `body`
		//   - `--color-{slug}` (pre-Phase-1 var name) → `--wp--preset--color--{slug}`
		//   - `/clients/{slug}/...` paths → theme-asset URLs
		$translated = $css;
		$translated = str_replace( '.sg-preview', 'body', $translated );
		$translated = preg_replace( '/var\(--color-([a-z0-9-]+)\)/', 'var(--wp--preset--color--$1)', $translated );
		$translated = preg_replace(
			'#/clients/' . preg_quote( $this->slug, '#' ) . '/([^\'")]+)#',
			$this->child_uri . '/assets/$1',
			$translated
		);

		$header = "/*\n";
		$header .= " * Translated from Next.js clients/{$this->slug}.json customCSS.\n";
		$header .= " * Translations applied: .sg-preview → body, --color-* → --wp--preset--color--*,\n";
		$header .= " * /clients/{$this->slug}/ paths → theme assets URL.\n";
		$header .= " * Review before shipping — some chrome selectors (.sg-h1 span etc.) may be unused.\n";
		$header .= " */\n\n";

		file_put_contents( $this->child_dir . '/custom.css', $header . $translated );
		$this->log[] = "wrote: custom.css (" . strlen( $translated ) . " bytes, translated)";
	}

	private function write_functions_php() {
		$php  = "<?php\n";
		$php .= "/**\n * Child theme bootstrap for sg-{$this->slug}.\n * Auto-generated by ccc-client-importer.\n */\n\n";
		$php .= "if ( ! defined( 'ABSPATH' ) ) exit;\n\n";

		// Enqueue custom.css if it was written.
		if ( file_exists( $this->child_dir . '/custom.css' ) ) {
			$php .= "add_action( 'wp_enqueue_scripts', function() {\n";
			$php .= "\twp_enqueue_style(\n";
			$php .= "\t\t'sg-{$this->slug}-custom',\n";
			$php .= "\t\tget_stylesheet_directory_uri() . '/custom.css',\n";
			$php .= "\t\tarray( 'ccc-blocks' ),\n";
			$php .= "\t\tfilemtime( get_stylesheet_directory() . '/custom.css' )\n";
			$php .= "\t);\n";
			$php .= "}, 20 );\n\n";
		}

		// Enqueue Google Fonts if either family is a Google font.
		$g_families = array();
		foreach ( array( 'heading', 'body' ) as $role ) {
			$def = $this->json['fonts'][ $role ] ?? null;
			if ( ! $def || ( $def['type'] ?? '' ) !== 'google' ) continue;
			$family = $def['family'];
			$weights = $def['weights'] ?? array( 400 );
			sort( $weights );
			$weights = array_unique( $weights );
			$g_families[] = rawurlencode( $family ) . ':wght@' . implode( ';', $weights );
		}
		if ( $g_families ) {
			$url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $g_families ) . '&display=swap';
			$php .= "add_action( 'wp_enqueue_scripts', function() {\n";
			$php .= "\twp_enqueue_style( 'sg-{$this->slug}-google-fonts', " . var_export( $url, true ) . ", array(), null );\n";
			$php .= "} );\n";
		}

		file_put_contents( $this->child_dir . '/functions.php', $php );
		$this->log[] = "wrote: functions.php";
	}
}
