<?php
/**
 * Icon Feature Row block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Icon Feature Row.
 *
 * Item background is a BLOCK-LEVEL choice — one native picker value applies
 * to every row item. The inverse-icon chip styling (CSS emitted per-slug by
 * css-generator.ts) kicks in automatically.
 *
 * @package ClassicCityCore
 */

$features = get_field( 'features' );
$columns  = (int) ( get_field( 'desktop_columns' ) ?: 4 );

if ( ! is_array( $features ) || empty( $features ) ) {
	return;
}

$columns = max( 2, min( 6, $columns ) );

$bg_slug = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );

$item_classes = array( 'sg-block-feature-row-item' );
if ( $bg_slug ) {
	$item_classes[] = 'has-' . $bg_slug . '-background-color';
	$item_classes[] = 'has-background';
}

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes(
		array(
			'class' => 'sg-block-feature-row',
			'style' => '--block-columns: ' . $columns,
		)
	)
);
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $features as $feature ) :
		$icon_class = ccc_fa_icon_class( $feature['icon_name'] ?? '' );
	?>
	<div class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
		<div class="sg-block-feature-row-head">
			<?php if ( $icon_class ) : ?>
			<i class="sg-block-feature-row-icon <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
			<?php endif; ?>
			<h5><?php echo esc_html( $feature['heading'] ?? '' ); ?></h5>
		</div>
		<p class="has-small-font-size"><?php echo esc_html( $feature['body'] ?? '' ); ?></p>
	</div>
	<?php endforeach; ?>
</div>
