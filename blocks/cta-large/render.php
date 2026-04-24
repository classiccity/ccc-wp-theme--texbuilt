<?php
/**
 * Large CTA block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Large CTA.
 *
 * The bg color/gradient is applied to the block root by WP automatically via
 * `get_block_wrapper_attributes()` — we don't need to compose the class manually.
 *
 * @package ClassicCityCore
 */

$bg_image    = get_field( 'bg_image' );
$bg_opacity  = (int) ( get_field( 'bg_opacity' ) ?? 80 );
$has_texture = (bool) get_field( 'has_texture' );

$classes = array( 'sg-block-cta' );
if ( $has_texture ) {
	$classes[] = 'has-bg-texture';
}

$style = '';
$bg_image_url = ! empty( $bg_image['url'] ) ? $bg_image['url'] : '';
if ( $bg_image_url ) {
	$style = 'background-image: url(\'' . esc_url( $bg_image_url ) . '\'); --sg-cta-overlay-opacity: ' . max( 0, min( 100, $bg_opacity ) ) / 100;
}

$wrapper_attrs = get_block_wrapper_attributes(
	array_filter(
		array(
			'class' => implode( ' ', $classes ),
			'style' => $style,
		)
	)
);
?>
<div <?php echo $wrapper_attrs; ?>>
	<div class="sg-block-cta-inner">
		<InnerBlocks />
	</div>
</div>
