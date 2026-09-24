<?php
/**
 * Hero — Gradient block render template.
 *
 * Markup contract:
 *   .sg-block-hero-gradient.alignfull.has-{slug}-background-color.has-background
 *     style="--hero-gradient-color: var(--wp--preset--color--{slug});"
 *     .__image (absolute; bg-image inline; opacity 0.7 via CSS)
 *     .__gradient (absolute; linear-gradient transparent→--hero-gradient-color)
 *     .__content (relative; constrained content-width; wraps InnerBlocks)
 *
 * Layer order (z-index):
 *   1. Parent has-{slug}-background-color paints the whole hero in
 *      the palette slug.
 *   2. .__image at z-index 0 with opacity 0.7 sits on top of the bg
 *      color so ~30% of the slug shows through everywhere.
 *   3. .__gradient at z-index 1 with linear-gradient(to bottom left)
 *      adds extra slug-color saturation at the bottom-left where text
 *      sits, fading to transparent in the upper right so the image is
 *      legible there.
 *   4. .__content at z-index 2 carries the InnerBlocks above
 *      everything else.
 *
 * On the demo page, the bg_image falls back to the parent theme's
 * demo placeholder helper so the hero renders without a real upload.
 *
 * @package ClassicCityCore
 */

$bg_color       = sanitize_html_class( (string) ( get_field( 'bg_color' ) ?: 'primary' ) );
$bg_image       = get_field( 'bg_image' );
$content_width  = (string) ( get_field( 'content_width' ) ?: 'content' );
$content_align  = (string) ( get_field( 'content_alignment' ) ?: 'left' );

// Demo fallback — keeps /style-guide visible without requiring uploads.
if ( function_exists( 'ccc_resolve_image_or_demo' ) ) {
	$bg_image = ccc_resolve_image_or_demo( $bg_image, 'hero-gradient', 1600, 900 );
}
$image_url = is_array( $bg_image ) && ! empty( $bg_image['url'] ) ? $bg_image['url'] : '';
$image_alt = is_array( $bg_image ) ? ( $bg_image['alt'] ?? '' ) : '';

/* `sg-hero` shared marker (no CSS) — see CLAUDE.md. */
$wrapper_classes = array( 'sg-hero', 'sg-block-hero-gradient' );
if ( $bg_color ) {
	$wrapper_classes[] = 'has-' . $bg_color . '-background-color';
	$wrapper_classes[] = 'has-background';
}

// Inline CSS var seeds the gradient overlay color to match the
// selected palette slug. The same slug drives the parent's bg-color +
// pair-helper text cascade above.
$wrapper_style = '--hero-gradient-color: var(--wp--preset--color--' . $bg_color . ');';

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
	'style' => $wrapper_style,
) );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $image_url ) : ?>
	<div
		class="sg-block-hero-gradient__image"
		role="img"
		aria-label="<?php echo esc_attr( $image_alt ); ?>"
		style="background-image: url('<?php echo esc_url( $image_url ); ?>');"
	></div>
	<?php endif; ?>
	<div class="sg-block-hero-gradient__gradient" aria-hidden="true"></div>
	<?php
	$content_classes = array( 'sg-block-hero-gradient__content' );
	if ( $content_width === 'narrow' ) {
		$content_classes[] = 'content-width-narrow';
	}
	if ( $content_align === 'center' ) {
		$content_classes[] = 'content-align-center';
	}
	?>
	<div class="<?php echo esc_attr( implode( ' ', $content_classes ) ); ?>">
		<InnerBlocks />
	</div>
</div>
