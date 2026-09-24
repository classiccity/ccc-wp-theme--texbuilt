<?php
/**
 * Detail Cards block render template.
 *
 * Row of image-backgrounded cards. Each card stacks a stat + stat title
 * at the top (separated by a hairline border in `currentColor`) over a
 * background image, with a description anchored at the bottom.
 *
 * Layout modes:
 *   - autoscroll OFF (default): grid of `column_count` columns at desktop,
 *     `mobile_layout` (stack | scroll) below the tablet breakpoint.
 *   - autoscroll ON: single nowrap row of fixed ~33vw cards, CSS-animated
 *     marquee (same pattern as Logo Strip scroller — cards rendered twice,
 *     duplicate is aria-hidden, animation translates -50% for a seamless
 *     loop). `prefers-reduced-motion` halts the animation.
 *
 * Card background color comes from the Gutenberg native picker
 * (block.json `supports.color.background`). We strip the auto-injected
 * bg class from the wrapper so the row stays transparent and apply
 * `has-{slug}-background-color` to each card instead — that triggers
 * the parent theme's auto-paired text color (`has-{slug}-color` on
 * the slug's opposite). The same slug is exposed as `--card-bg-color`
 * so the top/bottom scrim gradients fade into the card's color.
 *
 * @package ClassicCityCore
 */

$column_count = (int) ( get_field( 'column_count' ) ?: 3 );
// `?:` would treat 0% as "no value" and snap to 100 — explicit empty
// check preserves an author-set 0 (fully transparent image).
$bg_opacity_raw = get_field( 'bg_opacity' );
$bg_opacity    = ( $bg_opacity_raw === null || $bg_opacity_raw === false || $bg_opacity_raw === '' )
	? 100
	: (int) $bg_opacity_raw;
$autoscroll    = (bool) get_field( 'autoscroll' );
$mobile_layout = (string) ( get_field( 'mobile_layout' ) ?: 'stack' );
$aspect_ratio  = (string) ( get_field( 'aspect_ratio' ) ?: '3/4' );
$cards         = get_field( 'cards' );

if ( ! is_array( $cards ) || empty( $cards ) ) {
	return;
}

$column_count  = max( 1, min( 4, $column_count ) );
$bg_opacity    = max( 0, min( 100, $bg_opacity ) );
$mobile_layout = in_array( $mobile_layout, array( 'stack', 'scroll' ), true ) ? $mobile_layout : 'stack';
$allowed_ratios = array( '3/4', '4/5', '1/1', '4/3', '3/2', '16/9' );
if ( ! in_array( $aspect_ratio, $allowed_ratios, true ) ) {
	$aspect_ratio = '3/4';
}

$bg_slug = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );

$wrapper_classes = array( 'sg-block-detail-cards' );
$wrapper_classes[] = $autoscroll ? 'is-autoscroll' : 'is-grid';
$wrapper_classes[] = 'is-mobile-' . $mobile_layout;

$wrapper_style = '--block-columns: ' . $column_count . ';'
	. '--card-aspect-ratio: ' . $aspect_ratio . ';'
	. '--card-bg-opacity: ' . ( $bg_opacity / 100 ) . ';';
if ( $bg_slug ) {
	$wrapper_style .= '--card-bg-color: var(--wp--preset--color--' . $bg_slug . ');';
}

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes(
		array(
			'class' => implode( ' ', $wrapper_classes ),
			'style' => $wrapper_style,
		)
	)
);

$card_classes = array( 'sg-block-detail-cards__card' );
if ( $bg_slug ) {
	$card_classes[] = 'has-' . $bg_slug . '-background-color';
	$card_classes[] = 'has-background';
}
$card_class_attr = implode( ' ', $card_classes );

$is_demo     = function_exists( 'ccc_is_demo_page' ) && ccc_is_demo_page();
$demo_images = $is_demo ? ccc_get_demo_gallery_images( max( 1, count( $cards ) ) ) : array();

// Render the card list once (grid) or twice (autoscroll marquee — the
// duplicate set is aria-hidden so AT skips it).
$render_passes = $autoscroll ? 2 : 1;
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="sg-block-detail-cards__row">
	<?php for ( $pass = 0; $pass < $render_passes; $pass++ ) :
		$is_dupe = ( $pass === 1 );
		foreach ( $cards as $idx => $card ) :
			$stat        = (string) ( $card['stat']        ?? '' );
			$stat_title  = (string) ( $card['stat_title']  ?? '' );
			$description = (string) ( $card['description'] ?? '' );
			$bg_image    = $card['bg_image'] ?? array();
			if ( ( ! is_array( $bg_image ) || empty( $bg_image['url'] ) ) && ! empty( $demo_images[ $idx ] ) ) {
				$bg_image = $demo_images[ $idx ];
			}
			$img_url = ! empty( $bg_image['url'] ) ? $bg_image['url'] : '';
			$img_alt = ! empty( $bg_image['alt'] ) ? $bg_image['alt'] : '';
			?>
			<article
				class="<?php echo esc_attr( $card_class_attr ); ?>"
				<?php if ( $is_dupe ) : ?>aria-hidden="true"<?php endif; ?>
			>
				<?php if ( $img_url ) : ?>
				<figure class="sg-block-detail-cards__bg">
					<img
						src="<?php echo esc_url( $img_url ); ?>"
						alt="<?php echo $is_dupe ? '' : esc_attr( $img_alt ); ?>"
						loading="lazy"
						decoding="async"
					/>
				</figure>
				<?php endif; ?>
				<div class="sg-block-detail-cards__inner">
					<?php if ( $stat || $stat_title ) : ?>
					<div class="sg-block-detail-cards__head">
						<p class="sg-block-detail-cards__stat"><?php echo esc_html( $stat ); ?></p>
						<p class="sg-block-detail-cards__stat-title"><?php echo wp_kses_post( nl2br( esc_html( $stat_title ) ) ); ?></p>
					</div>
					<?php endif; ?>
					<?php if ( $description ) : ?>
					<p class="sg-block-detail-cards__desc"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
				</div>
			</article>
			<?php
		endforeach;
	endfor; ?>
	</div>
</div>
