<?php
/**
 * Image Hero 50/50 block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Image Hero (50/50).
 *
 * The background color/gradient is applied to the inner `.sg-block-hero-bg-media`
 * panel (NOT the block root), so `get_block_wrapper_attributes()` would attach
 * it in the wrong place. Instead we read `$attributes['backgroundColor']` /
 * `$attributes['gradient']` manually and compose the helper class onto the
 * inner element.
 *
 * @package ClassicCityCore
 */

$image          = get_field( 'image' );
$image          = ccc_resolve_image_or_demo( $image, 'image-hero', 960, 720 );
$video          = get_field( 'video' );
$side           = get_field( 'image_side' ) ?: 'left';
$has_texture    = (bool) get_field( 'has_texture' );
// true_false fields default to on when the field has never been saved (e.g.
// existing blocks created before these toggles existed), so compare against a
// strict '0' for an explicit off.
$media_spacing  = get_field( 'media_spacing' );
$media_spacing  = ( $media_spacing === '' || $media_spacing === null ) ? true : (bool) $media_spacing;
$full_height    = get_field( 'full_height' );
$full_height    = ( $full_height === '' || $full_height === null ) ? true : (bool) $full_height;

// DOM order is media → content, which naturally flows to image-left in the
// grid. The `.image-right` modifier flips the columns via CSS.
$root_classes = array( 'sg-block-hero-bg', 'alignfull' );
if ( $side === 'right' ) {
	$root_classes[] = 'image-right';
}
if ( ! $media_spacing ) {
	$root_classes[] = 'no-media-spacing';
}
if ( ! $full_height ) {
	$root_classes[] = 'dynamic-height';
}

// WP auto-injects the bg helper class + inline background style onto the wrapper
// whenever supports.color.background is on. Since we apply the bg class to the
// inner `.sg-block-hero-bg-media` element (NOT the block root), strip those here.
$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes( array( 'class' => implode( ' ', $root_classes ) ) )
);

$media_classes = array( 'sg-block-hero-bg-media' );

// ACF hoists native block-support attributes onto the `$block` array. Fall
// back to `$block['attrs']` for older ACF versions.
$bg_color = $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' );
$gradient = $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' );

if ( $gradient ) {
	$media_classes[] = 'has-' . sanitize_html_class( $gradient ) . '-gradient-background';
	$media_classes[] = 'has-background';
} elseif ( $bg_color ) {
	$media_classes[] = 'has-' . sanitize_html_class( $bg_color ) . '-background-color';
	$media_classes[] = 'has-background';
}

if ( $has_texture ) {
	$media_classes[] = 'has-bg-texture';
}

$image_url  = ! empty( $image['url'] ) ? $image['url'] : '';
$image_alt  = ! empty( $image['alt'] ) ? $image['alt'] : '';
$video_url  = is_array( $video ) && ! empty( $video['url'] ) ? $video['url'] : '';
$video_mime = is_array( $video ) && ! empty( $video['mime_type'] ) ? $video['mime_type'] : 'video/mp4';
?>
<div <?php echo $wrapper_attrs; ?>>
	<div class="<?php echo esc_attr( implode( ' ', $media_classes ) ); ?>">
		<?php if ( $video_url ) : ?>
		<video
			class="sg-block-hero-bg-video"
			autoplay
			muted
			loop
			playsinline
			<?php if ( $image_url ) : ?>poster="<?php echo esc_url( $image_url ); ?>"<?php endif; ?>
		>
			<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $video_mime ); ?>" />
		</video>
		<?php elseif ( $image_url ) : ?>
		<img class="sg-block-hero-bg-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
		<?php endif; ?>
	</div>
	<div class="sg-block-hero-bg-content">
		<InnerBlocks />
	</div>
</div>
