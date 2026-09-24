<?php
/**
 * Split 50/50 block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
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

/* Native bg color / gradient picker should paint the CONTENT side
   only — the image side has its own image. Strip the auto-injected
   bg helpers off the wrapper (else the whole 50/50 paints in the
   chosen color) and route them onto the body div instead. Same
   pattern hero-50-50 uses. */
$bg_color = $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' );
$gradient = $block['gradient']        ?? ( $block['attrs']['gradient']        ?? '' );

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );
if ( function_exists( 'ccc_strip_bg_from_wrapper' ) ) {
	$wrapper_attrs = ccc_strip_bg_from_wrapper( $wrapper_attrs );
}

$body_classes = array( 'sg-block-split-body' );
if ( $gradient ) {
	$body_classes[] = 'has-' . sanitize_html_class( $gradient ) . '-gradient-background';
	$body_classes[] = 'has-background';
} else {
	// Default the content side to a panel surface (paired text via the
	// palette helper) so the block is card-esque out of the box.
	$body_classes[] = 'has-' . sanitize_html_class( $bg_color ?: 'panel' ) . '-background-color';
	$body_classes[] = 'has-background';
}

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
	<div class="<?php echo esc_attr( implode( ' ', $body_classes ) ); ?>">
		<InnerBlocks />
	</div>
</div>
