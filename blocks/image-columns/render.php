<?php
/**
 * Image Columns block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Image Columns (cards).
 *
 * Card body bg = block-level native picker (solid OR gradient — both get the
 * combined `.has-{slug}-background-color` / `.has-{slug}-gradient-background`
 * helper which sets bg and opposite text color). The bg helper class goes on
 * the inner `.sg-block-card-body` (the image on top of each card keeps its
 * own styling).
 *
 * Button color = block-level ACF select. One value applies to every card's
 * CTA. Defaults to `cta` which matches the original hardcoded behavior.
 *
 * @package ClassicCityCore
 */

$columns_data = get_field( 'columns' );
$col_count    = (int) ( get_field( 'desktop_columns' ) ?: 3 );
$cta_slug     = sanitize_html_class( get_field( 'cta_color' ) ?: 'cta' ) ?: 'cta';

if ( ! is_array( $columns_data ) || empty( $columns_data ) ) {
	return;
}

$col_count = max( 2, min( 5, $col_count ) );

// Resolve the block-level bg (solid or gradient).
$bg_color    = $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' );
$bg_gradient = $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' );

$body_classes = array( 'sg-block-card-body' );
if ( $bg_gradient ) {
	$body_classes[] = 'has-' . sanitize_html_class( $bg_gradient ) . '-gradient-background';
	$body_classes[] = 'has-background';
} elseif ( $bg_color ) {
	$body_classes[] = 'has-' . sanitize_html_class( $bg_color ) . '-background-color';
	$body_classes[] = 'has-background';
}

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes(
		array(
			'class' => 'sg-block-columns',
			'style' => '--block-columns: ' . $col_count,
		)
	)
);
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $columns_data as $idx => $col ) :
		$image    = $col['image'] ?? array();
		$image    = ccc_resolve_image_or_demo( $image, 'columns-' . $idx, 640, 360 );
		$heading  = $col['heading'] ?? '';
		$body     = $col['body'] ?? '';
		$cta_text = $col['cta_text'] ?? '';
		$cta_url  = $col['cta_url'] ?? '';
		$img_url  = ! empty( $image['url'] ) ? $image['url'] : '';
		$img_alt  = ! empty( $image['alt'] ) ? $image['alt'] : '';
	?>
	<div class="sg-block-card">
		<?php if ( $img_url ) : ?>
		<div
			class="sg-block-card-image"
			style="background-image: url('<?php echo esc_url( $img_url ); ?>')"
			role="img"
			aria-label="<?php echo esc_attr( $img_alt ); ?>"
		></div>
		<?php endif; ?>
		<div class="<?php echo esc_attr( implode( ' ', $body_classes ) ); ?>">
			<?php if ( $heading ) : ?><h4><?php echo esc_html( $heading ); ?></h4><?php endif; ?>
			<?php if ( $body ) : ?>
			<p class="sg-block-card-copy has-small-font-size"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
			<?php if ( $cta_text && $cta_url ) : ?>
			<div class="wp-block-button sg-block-card-btn">
				<a
					class="wp-block-button__link wp-element-button has-<?php echo esc_attr( $cta_slug ); ?>-background-color has-background has-medium-font-size"
					href="<?php echo esc_url( $cta_url ); ?>"
				><?php echo esc_html( $cta_text ); ?></a>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>
</div>
