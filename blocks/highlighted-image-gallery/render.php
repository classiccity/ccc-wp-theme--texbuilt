<?php
/**
 * Highlighted Image Gallery block render template.
 *
 * Markup contract:
 *   .sg-block-highlighted-image-gallery (root, has --ccc-gallery-cols inline var)
 *     img.sg-block-highlighted-image-gallery__primary  (large primary image)
 *     .sg-block-highlighted-image-gallery__thumbs       (grid)
 *       button.sg-block-highlighted-image-gallery__thumb (.is-active on the one
 *         whose image is currently primary)
 *         img (thumbnail rendition)
 *
 * Interaction lives in view.js (auto-enqueued via block.json viewScript).
 *
 * @package ClassicCityCore
 */

$images  = get_field( 'images' );
$columns = (int) ( get_field( 'columns' ) ?: 5 );

if ( empty( $images ) || ! is_array( $images ) ) {
	return;
}

$primary = $images[0];

$block_attrs = array(
	'class' => 'sg-block-highlighted-image-gallery',
	'style' => '--ccc-gallery-cols: ' . max( 1, $columns ) . ';',
);
$wrapper_attributes = get_block_wrapper_attributes( $block_attrs );

// Pick a good thumbnail size from the attachment's sizes object; fall back
// to the original URL if WP didn't generate the expected size.
$pick_size = function ( $img, $size ) {
	if ( ! empty( $img['sizes'][ $size ] ) ) {
		return $img['sizes'][ $size ];
	}
	return $img['url'] ?? '';
};

$image_count = count( $images );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore ?>>
	<img
		class="sg-block-highlighted-image-gallery__primary"
		src="<?php echo esc_url( $pick_size( $primary, 'large' ) ); ?>"
		alt="<?php echo esc_attr( $primary['alt'] ?? '' ); ?>"
		loading="lazy"
		decoding="async"
	/>
	<?php if ( $image_count > 1 ) : ?>
		<div class="sg-block-highlighted-image-gallery__thumbs" role="list">
			<?php foreach ( $images as $index => $img ) :
				$thumb_src      = $pick_size( $img, 'medium' );
				$full_src       = $pick_size( $img, 'large' );
				$alt            = $img['alt'] ?? '';
				$is_active      = 0 === $index;
				$thumb_classes  = 'sg-block-highlighted-image-gallery__thumb';
				$thumb_classes .= $is_active ? ' is-active' : '';
				?>
				<button
					type="button"
					class="<?php echo esc_attr( $thumb_classes ); ?>"
					data-image-src="<?php echo esc_url( $full_src ); ?>"
					data-image-alt="<?php echo esc_attr( $alt ); ?>"
					aria-label="<?php
						/* translators: %d is the image position in the gallery */
						echo esc_attr( sprintf( __( 'Show image %d', 'classic-city-core' ), $index + 1 ) );
					?>"
					aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
					role="listitem"
				>
					<img
						src="<?php echo esc_url( $thumb_src ); ?>"
						alt="<?php echo esc_attr( $alt ); ?>"
						loading="lazy"
						decoding="async"
					/>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
