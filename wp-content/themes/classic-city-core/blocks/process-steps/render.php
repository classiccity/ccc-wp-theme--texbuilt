<?php
/**
 * Process Steps block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Process Steps.
 *
 * Step numbers are rendered by the `::before` pseudo-element in blocks.css
 * using `counter-increment` on `.sg-block-process-step`.
 *
 * Card bg = BLOCK-LEVEL native picker (solid or gradient). One choice
 * applies to every step card.
 *
 * Number circle color = BLOCK-LEVEL ACF select. One choice applies to every
 * step's badge via the `--step-number-bg` / `--step-number-color` custom
 * props on the process wrapper. When unset, the ::before uses its default
 * primary / primary-opposite fallbacks.
 *
 * @package ClassicCityCore
 */

$steps        = get_field( 'steps' );
$columns      = (int) ( get_field( 'desktop_columns' ) ?: 5 );
$number_slug  = sanitize_html_class( get_field( 'number_bg' ) ?: '' );

if ( ! is_array( $steps ) || empty( $steps ) ) {
	return;
}

$columns = max( 2, min( 6, $columns ) );

// Per-step card bg (block-level).
$bg_color    = $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' );
$bg_gradient = $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' );

$step_classes = array( 'sg-block-process-step' );
if ( $bg_gradient ) {
	$step_classes[] = 'has-' . sanitize_html_class( $bg_gradient ) . '-gradient-background';
	$step_classes[] = 'has-background';
} elseif ( $bg_color ) {
	$step_classes[] = 'has-' . sanitize_html_class( $bg_color ) . '-background-color';
	$step_classes[] = 'has-background';
}

// Inline number-circle custom props — applied ONCE to the wrapper so every
// step's ::before inherits. Skipped when admin leaves it blank (CSS fallbacks
// to primary).
$wrapper_style = '--block-columns: ' . $columns;
if ( $number_slug ) {
	$wrapper_style .= sprintf(
		'; --step-number-bg: var(--wp--preset--color--%1$s); --step-number-color: var(--wp--custom--color--%1$s-opposite)',
		$number_slug
	);
}

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes(
		array(
			'class' => 'sg-block-process',
			'style' => $wrapper_style,
		)
	)
);
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $steps as $step ) : ?>
	<div class="<?php echo esc_attr( implode( ' ', $step_classes ) ); ?>">
		<h5><?php echo esc_html( $step['heading'] ?? '' ); ?></h5>
		<p class="has-small-font-size"><?php echo esc_html( $step['body'] ?? '' ); ?></p>
	</div>
	<?php endforeach; ?>
</div>
