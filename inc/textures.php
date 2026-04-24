<?php
/**
 * Dynamic textures system.
 *
 * Registers CSS rules for every texture declared in the active theme's
 * `settings.custom.textures` in theme.json. Each entry renders as a
 * `.has-bg-texture-{slug}::before` rule — blocks opt in by applying the
 * matching class.
 *
 * Schema (per entry under settings.custom.textures):
 *   image       (int|string)  attachment ID OR direct URL (future-proofing)
 *   mode        (string)      "cover" | "accent"  — drives placement logic
 *   opacity     (number 0..1) element opacity (applied to ::before)
 *   blend-mode  (string)      CSS mix-blend-mode: normal|multiply|overlay|screen
 *   # cover-only
 *   size        (string)      background-size (e.g. "50% auto", "cover")
 *   angle       (string)      rotation applied via transform (e.g. "45deg")
 *   # accent-only
 *   position    (string)      top-right|top-left|bottom-right|bottom-left|center
 *   fade        (bool)        radial-gradient mask that fades toward edges
 *
 * Block integration (follow-up session) will surface these in an ACF texture
 * picker populated dynamically from the registered set. For now, authors opt
 * in via the block's "Additional CSS class" field with `has-bg-texture-{slug}`.
 *
 * Legacy compat: `has-bg-texture` + `has-bg-texture-sand` are preserved as
 * aliases for `has-bg-texture-default` + `has-bg-texture-sand` (if those slugs
 * exist). Existing blocks keep working.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read the active theme's registered textures from theme.json.
 *
 * Returns a normalized map:
 *   [ slug => [ 'image'=>url, 'mode'=>..., 'opacity'=>..., ... ] ]
 *
 * - `image` is resolved to a URL (attachment ID → wp_get_attachment_url()).
 * - Invalid entries are skipped.
 */
function ccc_get_registered_textures() {
	$settings = wp_get_global_settings();
	$raw      = $settings['custom']['textures'] ?? array();
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$out = array();
	foreach ( $raw as $slug => $entry ) {
		if ( ! is_string( $slug ) || ! is_array( $entry ) ) continue;
		$slug = sanitize_html_class( $slug );
		if ( ! $slug ) continue;

		$image = $entry['image'] ?? '';
		$url   = '';
		if ( is_numeric( $image ) && (int) $image > 0 ) {
			$url = (string) wp_get_attachment_url( (int) $image );
		} elseif ( is_string( $image ) && $image !== '' ) {
			$url = $image; // allow direct URL strings (less common)
		}
		if ( ! $url ) continue;

		$mode = in_array( $entry['mode'] ?? '', array( 'cover', 'accent' ), true )
			? $entry['mode']
			: 'cover';

		$blend = in_array( $entry['blend-mode'] ?? '', array( 'normal', 'multiply', 'overlay', 'screen' ), true )
			? $entry['blend-mode']
			: 'normal';

		$opacity = isset( $entry['opacity'] ) ? (float) $entry['opacity'] : 1.0;
		$opacity = max( 0, min( 1, $opacity ) );

		$normalized = array(
			'image'      => $url,
			'mode'       => $mode,
			'opacity'    => $opacity,
			'blend-mode' => $blend,
		);

		if ( $mode === 'cover' ) {
			$normalized['size']  = is_string( $entry['size']  ?? null ) ? $entry['size']  : '50% auto';
			$normalized['angle'] = is_string( $entry['angle'] ?? null ) ? $entry['angle'] : '0deg';
		} else { // accent
			$allowed_positions = array( 'top-right', 'top-left', 'bottom-right', 'bottom-left', 'center' );
			$normalized['position'] = in_array( $entry['position'] ?? '', $allowed_positions, true ) ? $entry['position'] : 'top-right';
			$normalized['size']     = is_string( $entry['size'] ?? null ) ? $entry['size'] : '400px';
			$normalized['fade']     = ! empty( $entry['fade'] );
		}

		$out[ $slug ] = $normalized;
	}
	return $out;
}

/**
 * Emit CSS for every registered texture — one rule per slug, plus legacy
 * aliases for the old `.has-bg-texture` (unslugged) and `.has-bg-texture-sand`
 * class names so existing blocks keep working during the migration.
 */
function ccc_build_textures_css() {
	$textures = ccc_get_registered_textures();
	if ( ! $textures ) {
		return '';
	}

	$rules = array();

	// Positioning scaffold shared by every ::before. Emitted once.
	$rules[] =
		'[class*="has-bg-texture-"]{position:relative;isolation:isolate;}' .
		'[class*="has-bg-texture-"]::before{' .
			'content:"";position:absolute;z-index:-1;pointer-events:none;' .
			'background-repeat:no-repeat;' .
		'}';

	foreach ( $textures as $slug => $t ) {
		$selector   = '.has-bg-texture-' . $slug . '::before';
		$decls      = array();
		$image_url  = esc_url( $t['image'] );

		$decls[] = "background-image:url('{$image_url}')";
		$decls[] = 'opacity:' . rtrim( rtrim( sprintf( '%.4f', $t['opacity'] ), '0' ), '.' );
		if ( $t['blend-mode'] !== 'normal' ) {
			$decls[] = 'mix-blend-mode:' . $t['blend-mode'];
		}

		if ( $t['mode'] === 'cover' ) {
			// Full-bleed: ::before fills the box; rotated if angle != 0.
			$decls[] = 'inset:0';
			$decls[] = 'background-size:' . $t['size'];
			$decls[] = 'background-position:center';
			if ( $t['angle'] !== '0deg' ) {
				$decls[] = 'transform-origin:center';
				$decls[] = 'transform:rotate(' . $t['angle'] . ')';
				// Overscan prevents rotation corners clipping. 141% covers any angle.
				$decls[] = 'width:141%';
				$decls[] = 'height:141%';
				$decls[] = 'left:-20.5%';
				$decls[] = 'top:-20.5%';
			}
		} else { // accent
			$decls[] = 'background-size:' . $t['size'] . ' auto';
			// Anchor per position token. Default top-right.
			switch ( $t['position'] ) {
				case 'top-left':     $decls[] = 'top:0;left:0;right:auto;bottom:auto;background-position:top left'; break;
				case 'bottom-right': $decls[] = 'bottom:0;right:0;top:auto;left:auto;background-position:bottom right'; break;
				case 'bottom-left':  $decls[] = 'bottom:0;left:0;top:auto;right:auto;background-position:bottom left'; break;
				case 'center':       $decls[] = 'inset:0;background-position:center'; break;
				case 'top-right':
				default:             $decls[] = 'top:0;right:0;bottom:auto;left:auto;background-position:top right'; break;
			}
			// Size the ::before to the image's natural size so background-size
			// is effective. Use the declared size as width; height auto keeps
			// the aspect ratio.
			$decls[] = 'width:' . $t['size'];
			$decls[] = 'aspect-ratio:auto';
			// Height naturally follows the image proportion via bg-size; but
			// the ::before itself needs a height. Use the declared size as a
			// square fallback — users can override with custom CSS if their
			// graphic needs a different aspect.
			$decls[] = 'height:' . $t['size'];
			if ( $t['fade'] ) {
				// Radial mask fades the texture toward all edges. Uses
				// mask-image; Safari needs -webkit- prefix.
				$fade_mask = 'radial-gradient(closest-side at 50% 50%, rgba(0,0,0,1) 40%, rgba(0,0,0,0) 100%)';
				$decls[] = '-webkit-mask-image:' . $fade_mask;
				$decls[] = 'mask-image:' . $fade_mask;
			}
		}

		$rules[] = $selector . '{' . implode( ';', $decls ) . '}';
	}

	// Note: legacy `.has-bg-texture` / `.has-bg-texture-sand` rules live in
	// blocks.css and still work by reading the child theme's CSS custom
	// properties (`--bg-texture` etc.) set in custom.css. That old system
	// coexists with the new `.has-bg-texture-{slug}` registry until each
	// child theme is migrated via the admin UI.

	return implode( "\n", $rules );
}

/**
 * Populate ACF select choices with the active theme's registered textures.
 *
 * Fires on any ACF select whose `name` is `texture` or `card_texture`. The
 * stored value is the full class name (`has-bg-texture-{slug}`) so the
 * render.php path can do `sanitize_html_class()` and drop it directly into
 * the element's class list.
 *
 * Legacy aliases (`has-bg-texture`, `has-bg-texture-sand`) are preserved as
 * fallback choices unless the corresponding slug is already registered —
 * this keeps existing saved field values from reverting to "None" while a
 * child theme is mid-migration.
 */
function ccc_acf_load_texture_choices( $field ) {
	$choices = array( '' => __( 'None', 'classic-city-core' ) );

	foreach ( ccc_get_registered_textures() as $slug => $_entry ) {
		$label = ucwords( str_replace( '-', ' ', $slug ) );
		$choices[ 'has-bg-texture-' . $slug ] = $label;
	}

	// Legacy fallback entries. Only surface them when the new registry does
	// not already own the same slot.
	if ( ! isset( $choices['has-bg-texture-default'] ) ) {
		$choices['has-bg-texture'] = __( 'Line Texture (legacy)', 'classic-city-core' );
	}
	if ( ! isset( $choices['has-bg-texture-sand'] ) ) {
		$choices['has-bg-texture-sand'] = __( 'Sand Texture (legacy)', 'classic-city-core' );
	}

	$field['choices'] = $choices;
	return $field;
}
add_filter( 'acf/load_field/name=card_texture', 'ccc_acf_load_texture_choices' );
add_filter( 'acf/load_field/name=texture',      'ccc_acf_load_texture_choices' );
