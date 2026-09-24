<?php
/**
 * Image Card Grid block render template.
 *
 * Renders a grid of image cards using the shared `ccc_render_image_card`
 * partial. Block-level controls layout (columns, mobile behavior,
 * image aspect ratio); the partial handles each card's markup.
 *
 * Markup contract:
 *   .sg-block-image-card-grid.is-mobile-{stack|scroll}
 *     style="--block-columns: N;"
 *     [.sg-image-card × N]   (rendered by ccc_render_image_card)
 *
 * Mobile behavior is class-driven:
 *   - is-mobile-stack: grid collapses to 1 column at the breakpoint.
 *   - is-mobile-scroll: grid becomes a horizontal flex scroller with
 *     scroll-snap. Each card takes ~85% viewport width so the next
 *     one peeks in at the right edge.
 *
 * On the demo page, any card missing an image falls back to one of
 * the parent theme's bundled gallery images so the block always
 * renders cleanly without uploads.
 *
 * @package ClassicCityCore
 */

$column_count       = (int) ( get_field( 'column_count' ) ?: 3 );
$mobile_layout      = (string) ( get_field( 'mobile_layout' ) ?: 'stack' );
$image_aspect_ratio = (string) ( get_field( 'image_aspect_ratio' ) ?: '16/9' );
$card_bg_color      = (string) ( get_field( 'card_bg_color' ) ?: 'panel' );
$cards              = get_field( 'cards' );

if ( ! is_array( $cards ) || empty( $cards ) ) {
	return;
}

$column_count  = max( 1, min( 4, $column_count ) );
$mobile_layout = in_array( $mobile_layout, array( 'stack', 'scroll' ), true ) ? $mobile_layout : 'stack';

$wrapper_classes = array(
	'sg-block-image-card-grid',
	'is-mobile-' . $mobile_layout,
);

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
	'style' => '--block-columns: ' . $column_count . ';',
) );

// On the demo page, pre-resolve fallback images for any card missing
// its own upload. Cycles through bundled gallery photos.
$is_demo     = function_exists( 'ccc_is_demo_page' ) && ccc_is_demo_page();
$demo_images = $is_demo ? ccc_get_demo_gallery_images( max( 1, count( $cards ) ) ) : array();
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $cards as $idx => $card ) :
		$image = $card['image'] ?? array();
		if ( empty( $image['url'] ) && ! empty( $demo_images[ $idx ] ) ) {
			$image = $demo_images[ $idx ];
		}
		ccc_render_image_card( array(
			'image'              => $image,
			'image_aspect_ratio' => $image_aspect_ratio,
			'bg_color'           => $card_bg_color,
			'tag'                => (string) ( $card['tag']             ?? '' ),
			'title'              => (string) ( $card['title']           ?? '' ),
			'description'        => (string) ( $card['description']     ?? '' ),
			'footer_title'       => (string) ( $card['footer_title']    ?? '' ),
			'footer_subtitle'    => (string) ( $card['footer_subtitle'] ?? '' ),
			'footer_link_text'   => (string) ( $card['button_text']     ?? '' ),
			'footer_link_url'    => (string) ( $card['button_link']     ?? '' ),
		) );
	endforeach; ?>
</div>
