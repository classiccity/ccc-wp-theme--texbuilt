<?php
/**
 * Split 50/50 block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Split 50/50.
 *
 * Side convention (matches blocks.css): default is image-left. When the admin
 * picks "Right", we add the `.content-left` modifier which flips column order.
 *
 * @package ClassicCityCore
 */

$image       = get_field( 'image' );
$image       = ccc_resolve_image_or_demo( $image, 'split', 960, 720 );
$video       = get_field( 'video' );
$side        = get_field( 'image_side' ) ?: 'left';
$has_texture = (bool) get_field( 'has_texture' );

$classes = array( 'sg-block-split' );
if ( $side === 'right' ) {
	$classes[] = 'content-left';
}
if ( $has_texture ) {
	$classes[] = 'has-bg-texture';
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );

$image_url  = ! empty( $image['url'] ) ? $image['url'] : '';
$image_alt  = ! empty( $image['alt'] ) ? $image['alt'] : '';
$video_url  = is_array( $video ) && ! empty( $video['url'] ) ? $video['url'] : '';
$video_mime = is_array( $video ) && ! empty( $video['mime_type'] ) ? $video['mime_type'] : 'video/mp4';
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php if ( $video_url ) : ?>
	<div class="sg-block-split-image sg-block-split-image--video">
		<video
			class="sg-block-split-video"
			autoplay
			muted
			loop
			playsinline
			<?php if ( $image_url ) : ?>poster="<?php echo esc_url( $image_url ); ?>"<?php endif; ?>
		>
			<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $video_mime ); ?>" />
		</video>
	</div>
	<?php elseif ( $image_url ) : ?>
	<div
		class="sg-block-split-image"
		style="background-image: url('<?php echo esc_url( $image_url ); ?>')"
		role="img"
		aria-label="<?php echo esc_attr( $image_alt ); ?>"
	></div>
	<?php endif; ?>
	<div class="sg-block-split-body">
		<InnerBlocks />
	</div>
</div>
