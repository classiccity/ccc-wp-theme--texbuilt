<?php
/**
 * Image Tiles block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Image Tiles.
 *
 * Each tile is an <a> so the whole tile is clickable. If no link_url is set,
 * renders as a <div> instead so we don't emit an empty-href anchor.
 *
 * @package ClassicCityCore
 */

$tiles            = get_field( 'tiles' );
$columns          = (int) ( get_field( 'desktop_columns' ) ?: 4 );
$desktop_carousel = (bool) get_field( 'desktop_carousel' );
$aspect           = get_field( 'aspect_ratio' ) ?: 'vertical';

if ( ! is_array( $tiles ) || empty( $tiles ) ) {
	return;
}

$columns = max( 1, min( 4, $columns ) );

$classes = array( 'sg-block-image-tiles', 'is-aspect-' . sanitize_html_class( $aspect ) );
if ( $desktop_carousel ) {
	$classes[] = 'is-desktop-carousel';
}

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $classes ),
	'style' => '--block-columns: ' . $columns,
) );
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $tiles as $idx => $tile ) :
		$image = $tile['image'] ?? array();
		$image = ccc_resolve_image_or_demo( $image, 'tiles-' . $idx, 720, 960 );
		$blurb = $tile['blurb'] ?? '';
		$link  = $tile['link_url'] ?? '';
		$img   = ! empty( $image['url'] ) ? $image['url'] : '';
		$tag   = $link ? 'a' : 'div';
		$attrs = $link ? ' href="' . esc_url( $link ) . '"' : '';
	?>
	<<?php echo $tag; ?> class="sg-block-image-tile"<?php echo $attrs; ?>>
		<?php if ( $img ) : ?>
		<div class="sg-block-image-tile-image" style="background-image: url('<?php echo esc_url( $img ); ?>')"></div>
		<?php endif; ?>
		<div class="sg-block-image-tile-gradient"></div>
		<?php if ( $blurb ) : ?>
		<p class="sg-block-image-tile-blurb"><?php echo esc_html( $blurb ); ?></p>
		<?php endif; ?>
	</<?php echo $tag; ?>>
	<?php endforeach; ?>
</div>
