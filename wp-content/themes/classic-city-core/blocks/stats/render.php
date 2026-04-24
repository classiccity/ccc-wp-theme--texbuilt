<?php
/**
 * Stats block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Stats.
 *
 * Background color/gradient: admin picks via WP's native color picker
 * (supports.color.background + color.gradients in block.json). WP auto-applies
 * the helper class to the block root via get_block_wrapper_attributes(), so no
 * manual class composition is needed.
 *
 * Column count: admin-picked via ACF select (2–6). We stash it as `--block-columns`
 * inline style and the CSS uses `repeat(var(--block-columns, 4), 1fr)`.
 *
 * @package ClassicCityCore
 */

$stats   = get_field( 'stats' );
$columns = (int) ( get_field( 'desktop_columns' ) ?: 4 );

if ( ! is_array( $stats ) || empty( $stats ) ) {
	return;
}

$columns = max( 2, min( 6, $columns ) );

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'sg-block-stats',
		'style' => '--block-columns: ' . $columns,
	)
);
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $stats as $stat ) : ?>
	<div class="sg-block-stat">
		<h3><?php echo esc_html( $stat['value'] ?? '' ); ?></h3>
		<p class="has-small-font-size"><?php echo esc_html( $stat['label'] ?? '' ); ?></p>
	</div>
	<?php endforeach; ?>
</div>
