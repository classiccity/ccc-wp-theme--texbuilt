<?php
/**
 * Image Links with Icons block render template.
 *
 * Markup contract:
 *   .sg-block-image-link-cards (root grid; --block-columns inline var)
 *     .sg-block-head.is-layout-flow (shared head partial — eyebrow + h2)
 *     a.sg-block-image-link-cards__card (whole card is the link)
 *       .sg-block-image-link-cards__image (square aspect-ratio frame)
 *         img.sg-block-image-link-cards__img (object-fit cover)
 *       .sg-block-image-link-cards__bar.has-{slug}-background-color.has-background
 *         i.sg-block-image-link-cards__icon (FA glyph, palette-opposite color)
 *         span.sg-block-image-link-cards__title
 *         i.sg-block-image-link-cards__arrow (fa-arrow-right)
 *
 * The footer bar uses the standard `has-{slug}-background-color has-background`
 * helper so the parent theme's per-palette pair CSS (emitted by
 * inc/enqueue.php) auto-flips text + icon color to the slug's opposite.
 * Padding on cards uses the WP block-gap token so grid spacing inherits
 * theme.json defaults.
 *
 * @package ClassicCityCore
 */

$columns = (int) ( get_field( 'columns' ) ?: 4 );
$cards   = get_field( 'cards' );

if ( ! is_array( $cards ) || empty( $cards ) ) {
	return;
}

$columns = max( 1, min( 6, $columns ) );

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'sg-block-image-link-cards',
	'style' => '--block-columns: ' . $columns . ';',
) );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	// Shared block-head partial: eyebrow + h2 with palette colors,
	// centered alignment, and the `is-layout-flow` wrapper that makes
	// the parent's eyebrow→heading collapse rule fire correctly. All
	// the spacing-trap commentary lives in inc/block-head.php now.
	ccc_render_block_head( array(
		'eyebrow_text'  => get_field( 'eyebrow_text' ),
		'eyebrow_color' => get_field( 'eyebrow_color' ),
		'header_text'   => get_field( 'header_text' ),
		'header_color'  => get_field( 'header_color' ),
	) );
	?>

	<div class="sg-block-image-link-cards__grid">
	<?php foreach ( $cards as $card ) :
		$title     = (string) ( $card['title'] ?? '' );
		$bg_slug   = sanitize_html_class( (string) ( $card['bg_color'] ?? '' ) );
		$icon_name = (string) ( $card['icon_name'] ?? '' );
		$link_url  = (string) ( $card['link_url'] ?? '' );
		$image     = $card['image'] ?? array();

		// Skip cards missing the link URL — the block IS a clickable grid.
		// Cards without URLs would silently degrade to non-anchors which
		// breaks the whole-card-as-link contract.
		if ( ! $link_url ) {
			continue;
		}

		// Resolve image — supports the parent's demo fallback so the block
		// renders on /style-guide without uploaded media.
		if ( function_exists( 'ccc_resolve_image_or_demo' ) ) {
			$image = ccc_resolve_image_or_demo( $image, 'image-link-cards-' . sanitize_key( $title ), 800, 800 );
		}
		$image_url = is_array( $image ) ? ( $image['url'] ?? '' ) : '';
		$image_alt = is_array( $image ) ? ( $image['alt'] ?? $title ) : $title;

		$bar_classes = array( 'sg-block-image-link-cards__bar' );
		if ( $bg_slug ) {
			$bar_classes[] = 'has-' . $bg_slug . '-background-color';
			$bar_classes[] = 'has-background';
		}

		$icon_class  = function_exists( 'ccc_fa_icon_class' ) ? ccc_fa_icon_class( $icon_name ) : '';
		$arrow_class = function_exists( 'ccc_fa_icon_class' ) ? ccc_fa_icon_class( 'arrow-right' ) : 'fa-solid fa-arrow-right';
	?>
		<a class="sg-block-image-link-cards__card" href="<?php echo esc_url( $link_url ); ?>">
			<div class="sg-block-image-link-cards__image">
				<?php if ( $image_url ) : ?>
				<img class="sg-block-image-link-cards__img" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy" decoding="async" />
				<?php endif; ?>
			</div>
			<div class="<?php echo esc_attr( implode( ' ', $bar_classes ) ); ?>">
				<?php if ( $icon_class ) : ?>
				<i class="sg-block-image-link-cards__icon <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
				<?php endif; ?>
				<span class="sg-block-image-link-cards__title"><?php echo esc_html( $title ); ?></span>
				<i class="sg-block-image-link-cards__arrow <?php echo esc_attr( $arrow_class ); ?>" aria-hidden="true"></i>
			</div>
		</a>
	<?php endforeach; ?>
	</div>
</div>
