<?php
/**
 * Image Wall block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Image Wall.
 *
 * Each row's images are doubled inline so the CSS `translateX(-50%)` animation
 * produces a seamless loop. The `--wall-count-top` / `--wall-count-bottom`
 * custom props drive per-row timing in blocks.css.
 *
 * @package ClassicCityCore
 */

$items = get_field( 'images' );
if ( ! is_array( $items ) || empty( $items ) ) {
	return;
}

// Split by index: even → top, odd → bottom.
$top = array();
$bot = array();
foreach ( $items as $i => $row ) {
	$img = $row['image'] ?? array();
	$img = ccc_resolve_image_or_demo( $img, 'wall-' . $i, 800, 400 );
	$url = ! empty( $img['url'] ) ? $img['url'] : '';
	if ( ! $url ) {
		continue;
	}
	if ( $i % 2 === 0 ) {
		$top[] = $url;
	} else {
		$bot[] = $url;
	}
}

if ( empty( $top ) && empty( $bot ) ) {
	return;
}

$style = sprintf( '--wall-count-top: %d; --wall-count-bottom: %d', count( $top ), count( $bot ) );
$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'sg-block-image-wall',
		'style' => $style,
	)
);

$render_row = function ( $urls ) {
	// Duplicate the list for seamless loop. Wide-vs-square is decided by the
	// item's position WITHIN the single (un-doubled) row so the two halves line
	// up — otherwise the loop seam would show a size jump.
	$count   = count( $urls );
	$doubled = array_merge( $urls, $urls );
	foreach ( $doubled as $i => $url ) {
		$pos    = $count ? ( $i % $count ) : 0;
		$wide   = ( $pos % 3 === 1 ) ? ' wide' : '';
		printf(
			'<div class="sg-block-image-wall-item%s" style="background-image: url(\'%s\')"></div>',
			esc_attr( $wide ),
			esc_url( $url )
		);
	}
};
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php if ( ! empty( $top ) ) : ?>
	<div class="sg-block-image-wall-strip">
		<?php $render_row( $top ); ?>
	</div>
	<?php endif; ?>
	<?php if ( ! empty( $bot ) ) : ?>
	<div class="sg-block-image-wall-strip">
		<?php $render_row( $bot ); ?>
	</div>
	<?php endif; ?>
</div>
