<?php
/**
 * Highlight Tiles block render template.
 *
 * Grid of marketing tiles (2×2 or 3×2 on desktop, single-column stack
 * on mobile). Each tile gets a per-card palette bg color, a top-left
 * headline (author-controlled line breaks preserved), and an optional
 * image with one of two treatments:
 *
 *   - background: full-bleed `<figure>` behind the headline, cover-
 *     cropped center/center, with a top/bottom scrim driven by the
 *     tile's own bg color so the headline reads on any image.
 *   - inline: image rendered after the headline at a constrained
 *     max-width, horizontally centered. Does not bleed.
 *
 * Per-card bg color is applied via `has-{slug}-background-color` so
 * the parent theme's auto pair-helper sets the matching text color
 * for free. The same slug is exposed to CSS as `--card-bg-color` so
 * the scrim gradient blends into the tile's own color instead of an
 * arbitrary black tint.
 *
 * The repeater is capped at 6; we render whichever count the author
 * selected (4 or 6) and ignore extras. If they entered fewer than the
 * selected count, we render whatever they entered — no padding.
 *
 * @package ClassicCityCore
 */

$card_count = (int) ( get_field( 'card_count' ) ?: 6 );
$cards      = get_field( 'cards' );

if ( ! is_array( $cards ) || empty( $cards ) ) {
	return;
}

$card_count = in_array( $card_count, array( 4, 6 ), true ) ? $card_count : 6;
$cards      = array_slice( $cards, 0, $card_count );

$wrapper_classes = array(
	'sg-block-highlight-tiles',
	'is-' . $card_count . '-tiles',
);

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $wrapper_classes ),
) );

$is_demo     = function_exists( 'ccc_is_demo_page' ) && ccc_is_demo_page();
$demo_images = $is_demo ? ccc_get_demo_gallery_images( max( 1, count( $cards ) ) ) : array();
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $cards as $idx => $card ) :
		$headline  = (string) ( $card['headline'] ?? '' );
		$bg_slug   = sanitize_html_class( (string) ( $card['bg_color'] ?? '' ) );
		$treatment = (string) ( $card['image_treatment'] ?? 'background' );
		$image     = $card['image'] ?? array();
		if ( ! in_array( $treatment, array( 'background', 'inline' ), true ) ) {
			$treatment = 'background';
		}
		if ( ( ! is_array( $image ) || empty( $image['url'] ) ) && ! empty( $demo_images[ $idx ] ) ) {
			$image = $demo_images[ $idx ];
		}
		$img_url = ! empty( $image['url'] ) ? $image['url'] : '';
		$img_alt = ! empty( $image['alt'] ) ? $image['alt'] : '';

		$tile_classes = array( 'sg-block-highlight-tiles__tile', 'is-treatment-' . $treatment );
		if ( $bg_slug ) {
			$tile_classes[] = 'has-' . $bg_slug . '-background-color';
			$tile_classes[] = 'has-background';
		}
		if ( ! $img_url ) {
			$tile_classes[] = 'is-text-only';
		}

		$tile_style = $bg_slug
			? '--card-bg-color: var(--wp--preset--color--' . $bg_slug . ');'
			: '';
		?>
		<article
			class="<?php echo esc_attr( implode( ' ', $tile_classes ) ); ?>"
			<?php if ( $tile_style ) : ?>style="<?php echo esc_attr( $tile_style ); ?>"<?php endif; ?>
		>
			<?php if ( $img_url && $treatment === 'background' ) : ?>
			<figure class="sg-block-highlight-tiles__bg">
				<img
					src="<?php echo esc_url( $img_url ); ?>"
					alt="<?php echo esc_attr( $img_alt ); ?>"
					loading="lazy"
					decoding="async"
				/>
			</figure>
			<?php endif; ?>

			<div class="sg-block-highlight-tiles__inner">
				<?php if ( $headline ) : ?>
				<p class="sg-block-highlight-tiles__headline">
					<?php echo wp_kses_post( nl2br( esc_html( $headline ) ) ); ?>
				</p>
				<?php endif; ?>

				<?php if ( $img_url && $treatment === 'inline' ) : ?>
				<figure class="sg-block-highlight-tiles__inline">
					<img
						src="<?php echo esc_url( $img_url ); ?>"
						alt="<?php echo esc_attr( $img_alt ); ?>"
						loading="lazy"
						decoding="async"
					/>
				</figure>
				<?php endif; ?>
			</div>
		</article>
		<?php
	endforeach; ?>
</div>
