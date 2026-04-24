<?php
/**
 * Hero: Full Image block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Hero: Full Image.
 *
 * @package ClassicCityCore
 */

$image          = get_field( 'image' );
$image          = ccc_resolve_image_or_demo( $image, 'hero-full', 1440, 720 );
$video          = get_field( 'video' );
$title_html     = get_field( 'title_html' );
$gradient_color = get_field( 'gradient_color' );
$card_width     = get_field( 'card_width' ) ?: 'narrow';

$wrapper_classes = array( 'sg-block-hero-full', 'alignfull' );
if ( $gradient_color ) {
	$wrapper_classes[] = 'sg-hero-grad-' . sanitize_html_class( $gradient_color );
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $wrapper_classes ) ) );

$image_url = ! empty( $image['url'] ) ? $image['url'] : '';
$video_url = is_array( $video ) && ! empty( $video['url'] ) ? $video['url'] : '';
$video_mime = is_array( $video ) && ! empty( $video['mime_type'] ) ? $video['mime_type'] : 'video/mp4';
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php if ( $video_url ) : ?>
	<div class="sg-block-hero-full-bg">
		<video
			class="sg-block-hero-full-video"
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
		class="sg-block-hero-full-bg"
		style="background-image: url('<?php echo esc_url( $image_url ); ?>')"
	></div>
	<?php endif; ?>
	<div class="sg-block-hero-full-gradient"></div>
	<?php if ( $title_html ) : ?>
	<div class="sg-block-hero-full-headline">
		<h1><?php echo wp_kses_post( $title_html ); ?></h1>
	</div>
	<?php endif; ?>
	<div class="sg-block-hero-full-card sg-hero-card-<?php echo esc_attr( $card_width ); ?>">
		<InnerBlocks />
	</div>
</div>
