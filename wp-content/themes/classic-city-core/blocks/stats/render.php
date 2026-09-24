<?php
/**
 * Stats block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Background color/gradient: admin picks via WP's native color picker
 * (supports.color.background + color.gradients in block.json). WP auto-applies
 * the helper class to the block root via get_block_wrapper_attributes(), so no
 * manual class composition is needed.
 *
 * Column count: admin-picked via ACF select (2–6). We stash it as `--block-columns`
 * inline style and the CSS uses `repeat(var(--block-columns, 4), 1fr)`.
 *
 * Markup contract:
 *   .sg-block-stats                  (.is-style-pods | .is-style-strip switch the treatment)
 *     .sg-block-stat
 *       h3.sg-block-stat__value
 *       p.sg-block-stat__label.has-small-font-size
 *
 * The `__value` / `__label` classes exist so the block STYLES can retint those
 * two elements without a descendant chain: blocks.css targets
 * `h3.sg-block-stat__value` and `p.sg-block-stat__label` directly, and both
 * rules resolve to `currentColor` unless a style sets the variable. Tag-
 * qualifying is not decoration — WP core emits `:root :where(p){font-size:…}`,
 * an explicit declaration on the paragraph that inheritance from any wrapper
 * loses to, so a paragraph rule has to match the paragraph itself.
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
		<h3 class="sg-block-stat__value"><?php echo esc_html( $stat['value'] ?? '' ); ?></h3>
		<p class="sg-block-stat__label has-small-font-size"><?php echo esc_html( $stat['label'] ?? '' ); ?></p>
	</div>
	<?php endforeach; ?>
</div>
