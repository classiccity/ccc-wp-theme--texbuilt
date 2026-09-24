<?php
/**
 * Framed Callout block render template.
 *
 * Structure:
 *   .sg-block-framed-callout.alignfull.is-align-{x}.is-height-{y}
 *     padding = global L/R padding on all 4 sides (forms the "frame")
 *     min-height = chosen vh value (or auto)
 *     [Gutenberg-injected bg classes stripped off here — see below]
 *   ├── .sg-block-framed-callout__inner.has-{slug}-background-color?
 *   │   .has-{slug}-gradient-background?
 *   │     fills the framed region; carries the bg color/gradient from
 *   │     the Gutenberg native picker so the frame stays the page bg
 *   │   ├── figure.__bg                              (only when bg_image set)
 *   │   │     absolute, full-bleed, object-fit: cover
 *   │   └── div.__content.content-align-{left|center}
 *   │         max-width: content-size, centered, flex column with
 *   │         block-gap, vertically centered inside __inner. The 40px
 *   │         padding top/bottom sits inside this (= the user's
 *   │         "spacing--40 always there" rule).
 *   │       └── InnerBlocks (default template seeds eyebrow + h1 +
 *   │           large p + cta button; children are narrow-capped via
 *   │           the canonical acf-innerblocks-container fix)
 *
 * Gutenberg bg picker handling: WP auto-injects `has-{slug}-background-
 * color has-background` (or `has-{slug}-gradient-background`) onto the
 * wrapper when the editor picks a color/gradient. We strip those off
 * the outer Container via `ccc_strip_bg_from_wrapper` and reapply the
 * same classes to .__inner so the frame stays transparent and only
 * the inner panel gets painted.
 *
 * @package ClassicCityCore
 */

$alignment = (string) ( get_field( 'alignment' ) ?: 'center' );
$height    = (string) ( get_field( 'height' ) ?: 'auto' );
$bg_image  = get_field( 'bg_image' );

$alignment = in_array( $alignment, array( 'left', 'center' ), true ) ? $alignment : 'center';
$allowed_heights = array( 'auto', '50', '60', '70', '80', '90', '100' );
if ( ! in_array( $height, $allowed_heights, true ) ) {
	$height = 'auto';
}

// Demo fallback for /style-guide.
if ( function_exists( 'ccc_resolve_image_or_demo' ) ) {
	$bg_image = ccc_resolve_image_or_demo( $bg_image, 'framed-callout', 1600, 900 );
}
$img_url = is_array( $bg_image ) && ! empty( $bg_image['url'] ) ? $bg_image['url'] : '';
$img_alt = is_array( $bg_image ) ? (string) ( $bg_image['alt'] ?? '' ) : '';

// Background-image opacity over the bg color (0-100). Default 100 = current
// behavior (image fully covers the color). Lower lets the color show through.
$bg_opacity         = (int) ( get_field( 'bg_opacity' ) ?? 100 );
$bg_opacity_decimal = max( 0, min( 100, $bg_opacity ) ) / 100;

// Read Gutenberg-picked color / gradient slugs from the block attrs.
$bg_slug       = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );
$gradient_slug = sanitize_html_class( $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' ) );

$wrapper_classes = array(
	'sg-block-framed-callout',
	'alignfull',
	'is-align-' . $alignment,
	'is-height-' . $height,
);

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes( array(
		'class' => implode( ' ', $wrapper_classes ),
	) )
);

$inner_classes = array( 'sg-block-framed-callout__inner' );
if ( $bg_slug ) {
	$inner_classes[] = 'has-' . $bg_slug . '-background-color';
	$inner_classes[] = 'has-background';
}
if ( $gradient_slug ) {
	$inner_classes[] = 'has-' . $gradient_slug . '-gradient-background';
	$inner_classes[] = 'has-background';
}

$content_classes = array(
	'sg-block-framed-callout__content',
	'content-align-' . $alignment,
);

// Default InnerBlocks template — eyebrow, h1, large paragraph, single
// CTA button. Authors can edit / remove / re-add any of these once
// the block is inserted; the template only seeds the initial state.
$inner_template = array(
	array(
		'core/paragraph',
		array(
			'className' => 'is-style-eyebrow',
			'content'   => 'Eyebrow',
		),
	),
	array(
		'core/heading',
		array(
			'level'   => 1,
			'content' => 'A headline that earns the room',
		),
	),
	array(
		'core/paragraph',
		array(
			'fontSize' => 'large',
			'content'  => 'A short, generous-sized paragraph supporting the headline above. Two lines is plenty.',
		),
	),
	array(
		'core/buttons',
		array(),
		array(
			array(
				'core/button',
				array(
					'backgroundColor' => 'cta',
					'text'            => 'Get started',
				),
			),
		),
	),
);
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr( implode( ' ', $inner_classes ) ); ?>">
		<?php if ( $img_url ) : ?>
		<figure class="sg-block-framed-callout__bg" aria-hidden="true" style="opacity: <?php echo esc_attr( $bg_opacity_decimal ); ?>;">
			<img
				src="<?php echo esc_url( $img_url ); ?>"
				alt="<?php echo esc_attr( $img_alt ); ?>"
				loading="lazy"
				decoding="async"
			/>
		</figure>
		<?php endif; ?>

		<div class="<?php echo esc_attr( implode( ' ', $content_classes ) ); ?>">
			<InnerBlocks template="<?php echo esc_attr( wp_json_encode( $inner_template ) ); ?>" />
		</div>
	</div>
</div>
