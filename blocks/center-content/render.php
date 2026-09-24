<?php
/**
 * Center Content block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * @package ClassicCityCore
 */

$image = get_field( 'image' );
$image = ccc_resolve_image_or_demo( $image, 'center', 800, 800 );

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'sg-block-center-content alignfull' ) );

$image_url = ! empty( $image['url'] ) ? $image['url'] : '';
$image_alt = ! empty( $image['alt'] ) ? $image['alt'] : '';
?>
<div <?php echo $wrapper_attrs; ?>>
	<div class="sg-block-center-content-bg has-bg-texture" aria-hidden="true"></div>
	<?php if ( $image_url ) : ?>
	<div
		class="sg-block-center-content-image"
		style="background-image: url('<?php echo esc_url( $image_url ); ?>')"
		role="img"
		aria-label="<?php echo esc_attr( $image_alt ); ?>"
	></div>
	<?php endif; ?>
	<div class="sg-block-center-content-body">
		<InnerBlocks />
	</div>
</div>
