<?php
/**
 * Frontend + editor asset enqueues.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the shared block stylesheet. blocks.css is synced from the Style
 * Guide repo (app/globals.css) via `npm run sync-blocks-css`. See the top
 * banner in assets/blocks.css for provenance.
 */
function ccc_enqueue_block_styles() {
	$handle = 'ccc-blocks';
	$rel    = 'assets/blocks.css';
	$path   = CCC_THEME_DIR . $rel;
	$uri    = CCC_THEME_URI . $rel;

	$ver = file_exists( $path ) ? (string) filemtime( $path ) : CCC_THEME_VERSION;

	wp_enqueue_style( $handle, $uri, array(), $ver );

	// Append the palette-driven opposite-text-color rules. These complete the
	// combined helper behavior (bg + readable text in one class) that our
	// css-generator.ts already emits in the Next.js preview. Runs after
	// blocks.css so it's last in the cascade and wins against WP's bg-only
	// helpers (same specificity, later in order).
	$pair_css = ccc_build_color_pair_helpers_css();
	if ( $pair_css ) {
		wp_add_inline_style( $handle, $pair_css );
	}

	// Dynamic texture CSS — one `.has-bg-texture-{slug}::before` rule per
	// texture registered in the active theme's settings.custom.textures.
	if ( function_exists( 'ccc_build_textures_css' ) ) {
		$tex_css = ccc_build_textures_css();
		if ( $tex_css ) {
			wp_add_inline_style( $handle, $tex_css );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'ccc_enqueue_block_styles' );

/**
 * Enqueue the portfolio-gallery lightbox JS on any page that actually renders
 * the portfolio-gallery block. Vanilla, no deps, loaded in the footer. Skip
 * the wp-admin request because the lightbox only makes sense on the frontend.
 */
function ccc_enqueue_portfolio_lightbox() {
	if ( is_admin() ) return;
	if ( ! has_block( 'classic-city-core/portfolio-gallery' ) ) return;

	$rel  = 'assets/portfolio-lightbox.js';
	$path = CCC_THEME_DIR . $rel;
	$uri  = CCC_THEME_URI . $rel;
	if ( ! file_exists( $path ) ) return;

	wp_enqueue_script(
		'ccc-portfolio-lightbox',
		$uri,
		array(),
		(string) filemtime( $path ),
		true // in footer
	);
}
add_action( 'wp_enqueue_scripts', 'ccc_enqueue_portfolio_lightbox' );

/**
 * Block editor: inject the palette-driven pair helpers INTO the editor iframe
 * (where blocks actually render), not just the editor chrome.
 *
 * `enqueue_block_editor_assets` loads styles into the chrome (sidebar, toolbar)
 * — they never reach the iframe where blocks preview. The canonical way to
 * put additional styles inside the iframe is the `block_editor_settings_all`
 * filter, which appends to `settings.styles`. Gutenberg then injects those
 * inline inside the iframe on load.
 *
 * `add_editor_style('assets/blocks.css')` (in inc/setup.php) already covers
 * blocks.css itself; this filter only adds the dynamic color pairs.
 */
add_filter( 'block_editor_settings_all', 'ccc_add_color_pair_helpers_to_editor' );
function ccc_add_color_pair_helpers_to_editor( $settings ) {
	$blobs = array();
	$pair  = ccc_build_color_pair_helpers_css();
	if ( $pair ) $blobs[] = $pair;
	if ( function_exists( 'ccc_build_textures_css' ) ) {
		$tex = ccc_build_textures_css();
		if ( $tex ) $blobs[] = $tex;
	}
	if ( ! $blobs ) return $settings;

	if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
		$settings['styles'] = array();
	}
	foreach ( $blobs as $css ) {
		$settings['styles'][] = array(
			'css'            => $css,
			'__unstableType' => 'theme',
		);
	}
	return $settings;
}

/**
 * Enqueue FontAwesome Pro 7 core CSS + the active site's style CSS.
 *
 * Core (fontawesome.css) ships class → glyph mappings. The per-style CSS
 * (solid/regular/light/sharp-light) declares the @font-face for that weight,
 * so only the webfont we actually use gets downloaded.
 *
 * Site-wide style is driven by theme.json's settings.custom.icons.style
 * (child theme override). Defaults to 'solid' if unset.
 *
 * Loaded on both frontend and block editor (so ACF block previews show
 * the right icons).
 */
function ccc_enqueue_fontawesome() {
	$base = CCC_THEME_URI . 'assets/fontawesome/css/';
	$path_base = CCC_THEME_DIR . 'assets/fontawesome/css/';

	$core_path = $path_base . 'fontawesome.css';
	$core_ver  = file_exists( $core_path ) ? (string) filemtime( $core_path ) : CCC_THEME_VERSION;

	wp_enqueue_style( 'ccc-fa-core', $base . 'fontawesome.css', array(), $core_ver );

	$style = ccc_resolve_icon_style();
	$style_css_map = array(
		'solid'       => 'solid.css',
		'regular'     => 'regular.css',
		'light'       => 'light.css',
		'sharp-light' => 'sharp-light.css',
	);
	$style_file = $style_css_map[ $style ] ?? 'solid.css';
	$style_path = $path_base . $style_file;
	$style_ver  = file_exists( $style_path ) ? (string) filemtime( $style_path ) : CCC_THEME_VERSION;

	wp_enqueue_style( 'ccc-fa-style', $base . $style_file, array( 'ccc-fa-core' ), $style_ver );
}
add_action( 'wp_enqueue_scripts', 'ccc_enqueue_fontawesome' );
add_action( 'enqueue_block_editor_assets', 'ccc_enqueue_fontawesome' );

/**
 * Resolve the site-wide icon style. Reads theme.json's
 * settings.custom.icons.style (set by the child theme). Filterable via
 * `ccc_icon_style`.
 *
 * @return string One of: 'solid', 'regular', 'light', 'sharp-light'.
 */
function ccc_resolve_icon_style() {
	$settings = wp_get_global_settings();
	$style    = $settings['custom']['icons']['style'] ?? 'solid';
	return apply_filters( 'ccc_icon_style', $style );
}

/**
 * Compose the FA class string for an icon: "fa-solid fa-star".
 *
 * @param string $name Icon slug as entered by the admin — "fa-star" or "star".
 * @return string Full class string, or empty string if name is blank.
 */
function ccc_fa_icon_class( $name ) {
	$name = trim( (string) $name );
	if ( $name === '' ) {
		return '';
	}
	if ( strpos( $name, 'fa-' ) !== 0 ) {
		$name = 'fa-' . $name;
	}
	// sanitize_html_class on "fa-star" keeps it as-is; on garbage it strips.
	$name = sanitize_html_class( $name );

	$style = ccc_resolve_icon_style();
	$style_class_map = array(
		'solid'       => 'fa-solid',
		'regular'     => 'fa-regular',
		'light'       => 'fa-light',
		'sharp-light' => 'fa-sharp fa-light',
	);
	$style_class = $style_class_map[ $style ] ?? 'fa-solid';

	return $style_class . ' ' . $name;
}

/**
 * Strip WP's auto-injected color-background classes + inline bg styles from a
 * wrapper attributes string. Used by blocks that declare color.background /
 * color.gradients supports but apply the chosen color to an INNER element
 * rather than the block root.
 *
 * What WP auto-injects when supports.color.background is on:
 *   - class="... has-{slug}-background-color has-background ..."
 *   - class="... has-{slug}-gradient-background has-background ..."
 *   - style="... background-color: var(--wp--preset--color--{slug}); ..."
 *   - style="... background: var(--wp--preset--gradient--{slug}); ..."
 *
 * @param string $wrapper_attrs Output of get_block_wrapper_attributes().
 * @return string The same string with bg classes + inline bg styles removed.
 */
function ccc_strip_bg_from_wrapper( $wrapper_attrs ) {
	// Strip bg-related classes from class="...".
	$wrapper_attrs = preg_replace_callback(
		'/class="([^"]*)"/',
		function ( $m ) {
			$cleaned = preg_replace(
				'/\b(has-[a-z0-9-]+-(?:background-color|gradient-background)|has-background)\b/',
				'',
				$m[1]
			);
			$cleaned = trim( preg_replace( '/\s+/', ' ', $cleaned ) );
			return 'class="' . $cleaned . '"';
		},
		$wrapper_attrs
	);

	// Strip bg-related declarations from style="...".
	$wrapper_attrs = preg_replace_callback(
		'/style="([^"]*)"/',
		function ( $m ) {
			$cleaned = preg_replace(
				'/\s*(background(?:-color|-image)?)\s*:\s*[^;"]+;?/i',
				'',
				$m[1]
			);
			$cleaned = trim( $cleaned, " \t\n\r\0\x0B;" );
			return $cleaned ? 'style="' . $cleaned . '"' : '';
		},
		$wrapper_attrs
	);

	return $wrapper_attrs;
}

/**
 * Emit palette-driven CSS that WP's theme.json emission can't produce on its own:
 *
 *   1) The opposite-text-color half of `.has-{slug}-background-color` and
 *      `.has-{slug}-gradient-background`. theme.json registers the palette and
 *      WP auto-emits those helpers with ONLY the background side; our combined-
 *      helper convention (bg + readable opposite text in one class) needs the
 *      `color:` half added. We emit it here for each slug that has a matching
 *      `settings.custom.color.{slug}-opposite` entry.
 *
 *   2) The inverse-icon chip rules for Feature Grid + Icon Feature Row. When
 *      a card has `.has-{slug}-background-color`, the icon wrapper inside
 *      flips: its bg becomes `{slug}-opposite` and the glyph becomes `{slug}`.
 *      This creates the "contrasting chip" look on colored cards. Mirrors the
 *      rules the Next.js preview emits dynamically via css-generator.ts.
 *
 * Reads the live palette (parent + child merged) from `wp_get_global_settings()`
 * so child-theme palette changes propagate automatically.
 *
 * Cascade note: hooked off blocks.css which loads after WP's global-styles-
 * inline-css. Same specificity as WP's own helper rules, later in order, our
 * declarations win. No !important needed since WP only marks `background-color`/
 * `background` important — not `color` or nested selectors.
 *
 * @return string Generated CSS (empty if no palette or custom.color block).
 */
function ccc_build_color_pair_helpers_css() {
	$settings = wp_get_global_settings();
	// wp_get_global_settings() groups palette by origin: palette.default[],
	// palette.theme[], palette.custom[], palette.blocks{}... We want every
	// slug that currently renders, so flatten across origins. Default is
	// stripped by our strip-wp-defaults filter, but we merge defensively.
	$palette_by_origin = $settings['color']['palette'] ?? array();
	if ( is_array( $palette_by_origin ) && ! isset( $palette_by_origin[0] ) ) {
		$palette = array();
		foreach ( array( 'default', 'theme', 'custom' ) as $origin ) {
			if ( ! empty( $palette_by_origin[ $origin ] ) && is_array( $palette_by_origin[ $origin ] ) ) {
				$palette = array_merge( $palette, $palette_by_origin[ $origin ] );
			}
		}
	} else {
		// Older WP or alternative: already flat.
		$palette = is_array( $palette_by_origin ) ? $palette_by_origin : array();
	}

	$opposite = $settings['custom']['color'] ?? array();
	if ( empty( $palette ) || empty( $opposite ) ) {
		return '';
	}

	$rules = array();
	foreach ( $palette as $entry ) {
		$slug = $entry['slug'] ?? '';
		if ( ! $slug ) {
			continue;
		}
		// Only emit when we have a matching -opposite custom color. Some
		// slugs may not — in which case the default body text color applies
		// and authors pick a visually safe palette entry.
		$opp_key = $slug . '-opposite';
		if ( ! isset( $opposite[ $opp_key ] ) ) {
			continue;
		}
		$slug_attr = esc_attr( $slug );
		$rules[]   = sprintf(
			// Combined-helper text color (bg helper pairs).
			'.has-%1$s-background-color{color:var(--wp--custom--color--%1$s-opposite);}' .
			'.has-%1$s-gradient-background{color:var(--wp--custom--color--%1$s-opposite);}' .
			// Feature Grid inverse icon — icon wrapper flips to opposite bg + slug text.
			'.sg-block-feature.has-%1$s-background-color .sg-block-feature-icon{' .
				'background-color:var(--wp--custom--color--%1$s-opposite);' .
				'color:var(--wp--preset--color--%1$s);' .
			'}' .
			// Icon Feature Row inverse chip — the <i> glyph doesn't have a wrapper,
			// so FontAwesome's base rule (`.fa-solid { display: inline-block;
			// width: 1.25em }`) wins over our base at equal specificity. We
			// re-declare display/width/height here at higher specificity
			// (descendant selector = 0,3,0 vs FA's 0,1,0) so the chip renders
			// as a 40×40 rounded square with a centered 20px glyph inside.
			'.sg-block-feature-row-item.has-%1$s-background-color .sg-block-feature-row-icon{' .
				'display:inline-flex;' .
				'align-items:center;' .
				'justify-content:center;' .
				'width:var(--wp--preset--spacing--40);' .
				'height:var(--wp--preset--spacing--40);' .
				'background-color:var(--wp--custom--color--%1$s-opposite);' .
				'color:var(--wp--preset--color--%1$s);' .
				'border-radius:var(--wp--custom--radius--default);' .
				'font-size:var(--wp--preset--spacing--20);' .
			'}',
			$slug_attr
		);

		// Per-color button hovers. Only emit for slugs with an -alt sibling.
		//   Solid: bg fades base → -alt, text follows opposite → -alt-opposite.
		//   Outline: text color swaps base → -alt (border follows via currentColor).
		// Both variants use the same hover convention: "a button styled with
		// color X hovers to the -alt of X". Independent of whether the button
		// is solid or outline.
		//
		// `!important` is required here because WP core auto-emits its own
		// `.has-{slug}-background-color` utility rule with !important on the
		// background-color property. Without matching !important on the
		// hover, core's base color wins the cascade and the hover swap never
		// shows. Same reasoning for text color on both solid and outline.
		if ( isset( $opposite[ $slug . '-alt-opposite' ] ) ) {
			$rules[] = sprintf(
				'.wp-block-button__link.has-%1$s-background-color:hover{' .
					'background-color:var(--wp--preset--color--%1$s-alt) !important;' .
					'color:var(--wp--custom--color--%1$s-alt-opposite) !important;' .
				'}' .
				'.wp-block-button.is-style-outline .wp-block-button__link.has-%1$s-color:hover{' .
					'color:var(--wp--preset--color--%1$s-alt) !important;' .
				'}',
				$slug_attr
			);
		}
	}
	return implode( "\n", $rules );
}

/**
 * Palette slug choices used by ACF select fields that let admins pick a
 * per-item background color (feature cards, step cards, column cards, etc.).
 * The resulting slug feeds into `has-{slug}-background-color` on the rendered
 * element, so it must match the color slugs registered in theme.json.
 *
 * Keep this list in sync with settings.color.palette in theme.json.
 *
 * @return array {slug => label} choices array for ACF 'select' fields.
 */
function ccc_palette_slug_choices() {
	return array(
		''             => __( '— Default —', 'classic-city-core' ),
		'cta'          => 'CTA',
		'cta-alt'      => 'CTA Alt',
		'primary'      => 'Primary',
		'primary-alt'  => 'Primary Alt',
		'secondary'    => 'Secondary',
		'secondary-alt'=> 'Secondary Alt',
		'tertiary'     => 'Tertiary',
		'tertiary-alt' => 'Tertiary Alt',
		'light'        => 'Light',
		'light-alt'    => 'Light Alt',
		'dark'         => 'Dark',
		'dark-alt'     => 'Dark Alt',
	);
}
