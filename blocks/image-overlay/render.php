<?php
/**
 * Image + Content Overlay block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Image + Content Overlay.
 *
 * Side convention: default (no modifier) = content-right. `.content-left` modifier
 * flips the column order.
 *
 * Background color/gradient: the block declares color.background + color.gradients
 * supports, but the color goes on the inner `.sg-block-overlay-content` card (not
 * the block root). We read the picker value off `$block` and apply the helper
 * class to the content element manually, then strip WP's auto-injection from the
 * wrapper via ccc_strip_bg_from_wrapper().
 *
 * @package ClassicCityCore
 */

$image        = get_field( 'image' );
$image        = ccc_resolve_image_or_demo( $image, 'overlay', 1200, 800 );
$content_side = get_field( 'content_side' ) ?: 'right';
$card_texture = get_field( 'card_texture' ) ?: '';

$root_classes = array( 'sg-block-overlay', 'alignfull' );
if ( $content_side === 'left' ) {
	$root_classes[] = 'content-left';
}

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes( array( 'class' => implode( ' ', $root_classes ) ) )
);

$content_classes = array( 'sg-block-overlay-content' );
if ( $card_texture ) {
	$content_classes[] = sanitize_html_class( $card_texture );
}

$bg_color = $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' );
$gradient = $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' );
if ( $gradient ) {
	$content_classes[] = 'has-' . sanitize_html_class( $gradient ) . '-gradient-background';
	$content_classes[] = 'has-background';
} elseif ( $bg_color ) {
	$content_classes[] = 'has-' . sanitize_html_class( $bg_color ) . '-background-color';
	$content_classes[] = 'has-background';
}

$image_url = ! empty( $image['url'] ) ? $image['url'] : '';
$image_alt = ! empty( $image['alt'] ) ? $image['alt'] : '';
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php if ( $image_url ) : ?>
	<img class="sg-block-overlay-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
	<?php endif; ?>
	<div class="<?php echo esc_attr( implode( ' ', $content_classes ) ); ?>">
		<InnerBlocks />
	</div>
</div>
