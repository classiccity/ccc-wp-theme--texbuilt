<?php
/**
 * Chart: Horizontal Bars block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Markup contract:
 *   .sg-block-chart-horizontal-bars           (the card root; carries the bg class)
 *     h3.sg-block-chart-horizontal-bars__title
 *     .sg-block-chart-horizontal-bars__desc      (WYSIWYG output)
 *     .sg-block-chart-horizontal-bars__rows
 *       .sg-block-chart-horizontal-bars__row     (+ .is-highlighted when flagged)
 *         .sg-block-chart-horizontal-bars__label
 *         .sg-block-chart-horizontal-bars__track   (aria-hidden — decorative)
 *           .sg-block-chart-horizontal-bars__fill  (inline --ccc-chb-pct)
 *         .sg-block-chart-horizontal-bars__value
 *     .sg-block-chart-horizontal-bars__footnote  (WYSIWYG output)
 *
 * BAR WIDTHS are computed here, not authored: each row's width is its value as
 * a share of the SUM of all values, so a chart always fills its track exactly
 * once across all rows. Authors type real figures (205 / 41 / 23 / 12) and the
 * block does the arithmetic — there is deliberately no percentage field to get
 * out of sync with the numbers beside it. The percentage is never PRINTED
 * either: the bar already communicates the proportion, and the row shows the
 * raw value. Widths land as `--ccc-chb-pct` so CSS owns the property (and can
 * transition it) rather than having a hardcoded `width:` in the markup.
 *
 * A zero or negative total (every row blank, or values that cancel out) yields
 * 0% bars rather than a division-by-zero — the labels and values still render,
 * so the block degrades to a plain list instead of disappearing.
 *
 * THE CARD is the block root, so unlike card-detail-rows there is nothing to
 * strip and re-apply: WP's native picker injects the background helper class
 * exactly where it belongs. The border is derived from that background via
 * ccc_palette_partner_slug() — a `primary` card gets a `primary-alt` border.
 * Gradients and unpaired surface slugs (canvas / panel / ink / ink-soft) have
 * no partner to resolve, and fall back to the default border token.
 *
 * @package ClassicCityCore
 */

$title       = get_field( 'title' );
$description = get_field( 'description' );
$bars        = get_field( 'bars' );
$footnote    = get_field( 'footnote' );

if ( ! is_array( $bars ) || empty( $bars ) ) {
	return;
}

// Total drives every bar width. Cast once here so the loop below can trust it.
$total = 0.0;
foreach ( $bars as $bar ) {
	$total += (float) ( $bar['value'] ?? 0 );
}

// Block-level background from the native picker — solid slug or gradient slug.
$bg_slug       = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );
$gradient_slug = sanitize_html_class( $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' ) );

$partner_slug = $gradient_slug ? '' : ccc_palette_partner_slug( $bg_slug );

$root_style = '';
if ( $partner_slug ) {
	$root_style = '--ccc-chb-border-color: var(--wp--preset--color--' . $partner_slug . ');';
}

$wrapper_args = array( 'class' => 'sg-block-chart-horizontal-bars' );
if ( $root_style ) {
	$wrapper_args['style'] = $root_style;
}

$wrapper_attrs = get_block_wrapper_attributes( $wrapper_args );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by get_block_wrapper_attributes(). ?>>
	<?php if ( $title ) : ?>
	<h3 class="sg-block-chart-horizontal-bars__title"><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

	<?php if ( $description ) : ?>
	<div class="sg-block-chart-horizontal-bars__desc"><?php echo wp_kses_post( $description ); ?></div>
	<?php endif; ?>

	<div class="sg-block-chart-horizontal-bars__rows">
		<?php
		foreach ( $bars as $bar ) :
			$label = $bar['label'] ?? '';
			$value = (float) ( $bar['value'] ?? 0 );

			$pct = ( $total > 0 ) ? ( $value / $total ) * 100 : 0;
			$pct = max( 0, min( 100, $pct ) );

			/* Print the figure as entered: thousands-separated, carrying only
			   the decimals the number actually has (205 stays "205", 72.9
			   stays "72.9", 72.85 stays "72.85"). Precision is decided BEFORE
			   formatting rather than by trimming zeros off the result — the
			   decimal separator is locale-dependent, so string-trimming
			   number_format_i18n() output breaks outside en_US. */
			$decimals = 0;
			if ( floor( $value ) !== $value ) {
				$decimals = ( round( $value, 1 ) === round( $value, 2 ) ) ? 1 : 2;
			}
			$formatted = number_format_i18n( $value, $decimals );

			$row_classes = array( 'sg-block-chart-horizontal-bars__row' );
			if ( ! empty( $bar['is_highlight'] ) ) {
				$row_classes[] = 'is-highlighted';
			}
			?>
		<div class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
			<span class="sg-block-chart-horizontal-bars__label"><?php echo esc_html( $label ); ?></span>
			<span class="sg-block-chart-horizontal-bars__track" aria-hidden="true">
				<span class="sg-block-chart-horizontal-bars__fill" style="--ccc-chb-pct: <?php echo esc_attr( round( $pct, 2 ) ); ?>%"></span>
			</span>
			<span class="sg-block-chart-horizontal-bars__value"><?php echo esc_html( $formatted ); ?></span>
		</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $footnote ) : ?>
	<div class="sg-block-chart-horizontal-bars__footnote"><?php echo wp_kses_post( $footnote ); ?></div>
	<?php endif; ?>
</div>
