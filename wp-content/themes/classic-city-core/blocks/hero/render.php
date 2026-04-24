<?php
/**
 * Hero block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Hero (standard).
 *
 * @package ClassicCityCore
 */

$image = get_field( 'image' );
$image = ccc_resolve_image_or_demo( $image, 'hero', 960, 720 );
$side  = get_field( 'image_side' ) ?: 'right';

$classes = array( 'sg-block-hero' );
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
