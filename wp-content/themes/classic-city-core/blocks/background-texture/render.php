<?php
/**
 * Background Texture block render template.
 *
 * The block itself is rendered with `height: 1px` so it's selectable in
 * the editor but doesn't contribute meaningful vertical space to the
 * document flow. A `::before` pseudo carries the texture image and is
 * absolutely positioned to project up, down, or both directions from
 * the block's drop point — see the `--bottom` / `--top` / `--middle`
 * modifier classes in blocks.css.
 *
 * Stacking: pseudo gets `z-index: -1` so the texture sits behind
 * sibling content. Only visible where adjacent content/section bg is
 * transparent (i.e., the page background shows through). Authors who
 * need it visible against opaque section bgs should consider mounting
 * the block as a sibling of those sections instead of nested inside.
 *
 * Source: pulls from `ccc_get_registered_textures()` so the texture
 * URL, opacity, and blend-mode all come from the child theme's
 * theme.json. Bails silently if the selected slug isn't registered
 * (e.g., theme switch removed the entry).
 *
 * Markup contract:
 *   div.sg-block-background-texture.sg-block-background-texture--{bottom|top|middle}.alignfull
 *     style="--texture-url: url('...'); --texture-opacity: 0.X; --texture-blend-mode: multiply;"
 *     aria-hidden="true"
 *
 * @package ClassicCityCore
 */

$slug        = sanitize_html_class( (string) ( get_field( 'texture_slug' ) ?: '' ) );
$arrangement = (string) ( get_field( 'arrangement' ) ?: 'bottom' );

if ( ! in_array( $arrangement, array( 'bottom', 'top', 'middle' ), true ) ) {
	$arrangement = 'bottom';
}

if ( ! $slug || ! function_exists( 'ccc_get_registered_textures' ) ) {
	return;
}

$textures = ccc_get_registered_textures();
$texture  = $textures[ $slug ] ?? null;
if ( ! is_array( $texture ) || empty( $texture['image'] ) ) {
	return;
}

$opacity    = isset( $texture['opacity'] ) ? (float) $texture['opacity'] : 1.0;
$blend_mode = $texture['blend-mode'] ?? 'normal';

$wrapper_classes = array(
	'sg-block-background-texture',
	'sg-block-background-texture--' . $arrangement,
);

// Inline CSS vars seed the pseudo's background image, opacity, and
// blend mode. Keeping them on the wrapper means a single set of CSS
// rules in blocks.css handles every texture slug without per-slug
// rule generation.
$wrapper_style = sprintf(
	"--texture-url: url('%s'); --texture-opacity: %s; --texture-blend-mode: %s;",
	esc_url_raw( $texture['image'] ),
	number_format( max( 0, min( 1, $opacity ) ), 2, '.', '' ),
	sanitize_html_class( $blend_mode )
);

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
	'style' => $wrapper_style,
	'aria-hidden' => 'true',
) );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>
