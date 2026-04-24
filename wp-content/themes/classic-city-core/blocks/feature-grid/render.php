<?php
/**
 * Feature Grid block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Feature Grid.
 *
 * Icons: FontAwesome Pro 7. Admin enters just the icon slug (`fa-star` or
 * bare `star`); `ccc_fa_icon_class()` composes `fa-{style} fa-{name}` using
 * the site-wide style resolved from theme.json (settings.custom.icons.style).
 *
 * Card background is a BLOCK-LEVEL choice — admin picks one color via WP's
 * native color picker and we apply it to every card. The inverse-icon CSS
 * rules (emitted per slug by css-generator.ts) automatically flip the icon
 * wrapper to contrasting colors when the card has a bg class. We strip WP's
 * auto-injected bg class from the block root since it belongs on each card.
 *
 * @package ClassicCityCore
 */

$features = get_field( 'features' );
$columns  = (int) ( get_field( 'desktop_columns' ) ?: 3 );

if ( ! is_array( $features ) || empty( $features ) ) {
	return;
}

$columns = max( 2, min( 5, $columns ) );

// Block-level bg slug from the native picker (gradients disabled in block.json
// so only `backgroundColor` applies here).
$bg_slug = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );

$card_classes = array( 'sg-block-feature' );
if ( $bg_slug ) {
	$card_classes[] = 'has-' . $bg_slug . '-background-color';
	$card_classes[] = 'has-background';
}

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes(
		array(
			'class' => 'sg-block-features',
			'style' => '--block-columns: ' . $columns,
		)
	)
);
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $features as $feature ) :
		$icon_class = ccc_fa_icon_class( $feature['icon_name'] ?? '' );
	?>
	<div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
		<div class="sg-block-feature-icon">
			<?php if ( $icon_class ) : ?>
				<i class="sg-block-feature-icon-glyph <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
			<?php endif; ?>
		</div>
		<div class="sg-block-feature-content">
			<h4><?php echo esc_html( $feature['heading'] ?? '' ); ?></h4>
			<p class="has-small-font-size"><?php echo esc_html( $feature['body'] ?? '' ); ?></p>
		</div>
	</div>
	<?php endforeach; ?>
</div>
