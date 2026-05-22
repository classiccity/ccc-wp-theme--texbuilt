<?php
/**
 * Logo Strip block render template.
 *
 * Two layouts:
 *   - grid (default): logos wrap into multiple rows, center-justified.
 *   - scroller: single nowrap row, CSS-animated marquee. Logos are emitted
 *     twice (the duplicate set is aria-hidden) so the animation can loop
 *     seamlessly via translateX(-50%).
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Logo Strip.
 *
 * @package ClassicCityCore
 */

$eyebrow   = get_field( 'eyebrow' );
$logos     = get_field( 'logos' );
$layout    = get_field( 'layout' ) ?: 'grid';
$speed     = get_field( 'scroll_speed' ) ?: 'medium';
$direction = get_field( 'scroll_direction' ) ?: 'left';
$pause     = (bool) get_field( 'pause_on_hover' );

if ( ! is_array( $logos ) || empty( $logos ) ) {
	return;
}

$allowed_layouts = array( 'grid', 'scroller' );
if ( ! in_array( $layout, $allowed_layouts, true ) ) {
	$layout = 'grid';
}
$allowed_speeds = array( 'slow', 'medium', 'fast' );
if ( ! in_array( $speed, $allowed_speeds, true ) ) {
	$speed = 'medium';
}
$allowed_dirs = array( 'left', 'right' );
if ( ! in_array( $direction, $allowed_dirs, true ) ) {
	$direction = 'left';
}

$classes = array( 'sg-block-logos', 'sg-block-logos--' . $layout );
if ( $layout === 'scroller' ) {
	$classes[] = 'sg-block-logos--speed-' . $speed;
	$classes[] = 'sg-block-logos--dir-' . $direction;
	if ( $pause ) {
		$classes[] = 'sg-block-logos--pause-on-hover';
	}
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );

// In scroller mode, render the logo list twice so the marquee loops seamlessly.
$render_passes = ( $layout === 'scroller' ) ? 2 : 1;
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php if ( $eyebrow ) : ?>
	<p class="sg-block-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
	<?php endif; ?>
	<div class="sg-block-logos-row">
		<?php for ( $pass = 0; $pass < $render_passes; $pass++ ) :
			$is_dupe = ( $pass === 1 );
			foreach ( $logos as $idx => $row ) :
				$img = $row['logo_image'] ?? array();
				$img = ccc_resolve_image_or_demo( $img, 'logo-' . $idx, 240, 80 );
				$url = ! empty( $img['url'] ) ? $img['url'] : '';
				$alt = ! empty( $img['alt'] ) ? $img['alt'] : '';
				if ( ! $url ) {
					continue;
				}
				?>
				<img
					src="<?php echo esc_url( $url ); ?>"
					alt="<?php echo $is_dupe ? '' : esc_attr( $alt ); ?>"
					<?php if ( $is_dupe ) : ?>aria-hidden="true"<?php endif; ?>
					loading="lazy"
				/>
				<?php
			endforeach;
		endfor; ?>
	</div>
</div>
