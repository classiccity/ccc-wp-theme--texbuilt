<?php
/**
 * Hero block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * @package ClassicCityCore
 */

$image = get_field( 'image' );
$image = ccc_resolve_image_or_demo( $image, 'hero', 960, 720 );
$side  = get_field( 'image_side' ) ?: 'right';

/* `sg-hero` shared "this is the page's hero" marker — appears on
   every hero variant. Marker-only (no CSS); see CLAUDE.md. */
$classes = array( 'sg-hero', 'sg-block-hero' );
if ( $side === 'left' ) {
	$classes[] = 'image-left';
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );

$image_url = ! empty( $image['url'] ) ? $image['url'] : '';
$image_alt = ! empty( $image['alt'] ) ? $image['alt'] : '';
?>
<div <?php echo $wrapper_attrs; ?>>
	<div class="sg-block-hero-content">
		<InnerBlocks />
	</div>
	<?php if ( $image_url ) : ?>
	<div
		class="sg-block-hero-image"
		style="background-image: url('<?php echo esc_url( $image_url ); ?>')"
		role="img"
		aria-label="<?php echo esc_attr( $image_alt ); ?>"
	></div>
	<?php endif; ?>
</div>
