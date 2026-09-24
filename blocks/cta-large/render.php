<?php
/**
 * Large CTA block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Layered background model:
 *  - Wrapper: paints the COLOR / GRADIENT (via WP's auto-injected
 *    `has-{slug}-background-color` / `has-background` classes from
 *    supports.color.background) — plus optional texture from `has_texture`.
 *  - `.sg-block-cta-bg` (absolute, inset:0, behind content): paints the
 *    optional image at `background-size: cover` with the chosen opacity.
 *  - `.sg-block-cta-inner` (relative, z-index:1): the InnerBlocks content,
 *    always on top.
 *
 * Why the layered approach: in the prior single-layer model, the inline
 * `background-image` painted ON the wrapper, on TOP of the color in the
 * same paint pass, so the color was meaningless whenever an image was
 * set and `bg_opacity` (which only existed as an unread CSS var) couldn't
 * tint anything. Splitting image into its own absolute overlay layer lets
 * opacity work as authors expect AND lets the color show through.
 *
 * Backward-compat: saved field shapes are unchanged (still bg_image,
 * bg_opacity, has_texture). Visual diff vs old: identical for all
 * combinations EXCEPT (image + color + opacity < 100), which previously
 * showed only the image (broken — opacity field had no effect) and now
 * shows the image at chosen opacity over the chosen color.
 *
 * @package ClassicCityCore
 */

$bg_image    = get_field( 'bg_image' );
$bg_opacity  = (int) ( get_field( 'bg_opacity' ) ?? 80 );
$has_texture = (bool) get_field( 'has_texture' );

$classes = array( 'sg-block-cta' );
if ( $has_texture ) {
	$classes[] = 'has-bg-texture';
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );

$bg_image_url = ! empty( $bg_image['url'] ) ? $bg_image['url'] : '';
$bg_image_alt = ! empty( $bg_image['alt'] ) ? $bg_image['alt'] : '';
$bg_opacity_decimal = max( 0, min( 100, $bg_opacity ) ) / 100;
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $bg_image_url ) : ?>
	<div
		class="sg-block-cta-bg"
		role="img"
		aria-label="<?php echo esc_attr( $bg_image_alt ); ?>"
		style="background-image: url('<?php echo esc_url( $bg_image_url ); ?>'); opacity: <?php echo esc_attr( $bg_opacity_decimal ); ?>;"
	></div>
	<?php endif; ?>
	<div class="sg-block-cta-inner">
		<InnerBlocks />
	</div>
</div>
