<?php
/**
 * Comparison Table block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Markup contract:
 *   .sg-block-comparison-table                      (the card root; carries the bg class)
 *     h3.sg-block-comparison-table__title
 *     .sg-block-comparison-table__desc                 (WYSIWYG output)
 *     .sg-block-comparison-table__metrics
 *       .sg-block-comparison-table__metric             (one per repeater row)
 *         p.sg-block-comparison-table__name
 *         .sg-block-comparison-table__row.is-ours      (+ .has-bars when drawn)
 *           span.sg-block-comparison-table__side
 *           span.sg-block-comparison-table__track      (aria-hidden — decorative)
 *             span.sg-block-comparison-table__fill     (inline --ccc-ct-pct)
 *           span.sg-block-comparison-table__value
 *         .sg-block-comparison-table__row.is-theirs    (same shape)
 *     .sg-block-comparison-table__footnote             (WYSIWYG output)
 *
 * WHY THIS ISN'T `chart-horizontal-bars`. That block sizes every bar as a share
 * of ONE total, which is only honest when the rows partition a single whole
 * (visitors by country). An us-versus-them comparison is the case its own
 * usage.md tells you to avoid it for: each metric is its own scale, and "13 min
 * 09 sec" and "17.0" share no total at all. Here each ROW is scaled
 * independently — the larger of the pair fills the track, the other draws in
 * proportion to it — so what a reader compares is the gap within a row, never
 * one row against the next.
 *
 * BAR LENGTHS ARE PARSED, NOT AUTHORED. The values are free text (they have to
 * be — see fields.php), so `ccc_ct_number()` below pulls the first numeric run
 * out of each: "13 min 09 sec" → 13, "79.1%" → 79.1, "46" → 46. A second field
 * holding the bar width would be a second source of truth that drifts from the
 * figure printed beside it, which is precisely what computing widths prevents.
 *
 * THE SAME-UNIT GUARD is the important part. Parsing only the first number
 * means "1 hr 5 min" reads as 1 and "45 min" reads as 45 — a bar that draws the
 * exact opposite of the truth. So before drawing anything, both values are
 * reduced to their non-numeric residue ("13 min 09 sec" → "minsec", "79.1%" →
 * "%", "46" → "") and the two must match. When they don't, or when either value
 * has no number in it, or when the larger of the pair is not positive, the row
 * renders its label and its two figures with NO bars and no `.has-bars` class.
 * A missing bar is a small loss; a confidently wrong one is a lie on a page
 * whose entire job is being believed.
 *
 * THE CARD is the block root, so WP's native picker injects the background
 * helper class where it belongs — nothing to strip. The border derives from
 * that background via ccc_palette_partner_slug(); gradients and the unpaired
 * surface slugs (canvas / panel / ink / ink-soft) fall back to the default
 * border token.
 *
 * @package ClassicCityCore
 */

$title       = get_field( 'title' );
$description = get_field( 'description' );
$our_label   = get_field( 'our_label' );
$their_label = get_field( 'their_label' );
$rows        = get_field( 'rows' );
$footnote    = get_field( 'footnote' );

if ( ! is_array( $rows ) || empty( $rows ) ) {
	return;
}

/* Closures rather than named functions: render.php is included once per block
   INSTANCE, so a named function would fatal on the second instance of the
   block on the same page. */

/**
 * First numeric run in a free-text value, or null when there isn't one.
 * "13 min 09 sec" → 13.0 · "79.1%" → 79.1 · "5,000+" → 5.0 · "n/a" → null
 */
$ccc_ct_number = static function ( $value ) {
	if ( preg_match( '/-?\d+(?:\.\d+)?/', (string) $value, $m ) ) {
		return (float) $m[0];
	}
	return null;
};

/**
 * The non-numeric residue of a value — its unit signature. Digits, the decimal
 * point, thousands commas and all whitespace come out; everything else stays,
 * lowercased. Two values may only be drawn against each other when their
 * residues are identical.
 */
$ccc_ct_unit = static function ( $value ) {
	$residue = preg_replace( '/[\d.,\s]+/u', '', (string) $value );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $residue, 'UTF-8' ) : strtolower( $residue );
};

// Block-level background from the native picker — solid slug or gradient slug.
$bg_slug       = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );
$gradient_slug = sanitize_html_class( $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' ) );

$partner_slug = $gradient_slug ? '' : ccc_palette_partner_slug( $bg_slug );

$wrapper_args = array( 'class' => 'sg-block-comparison-table' );
if ( $partner_slug ) {
	$wrapper_args['style'] = '--ccc-ct-border-color: var(--wp--preset--color--' . $partner_slug . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $wrapper_args );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by get_block_wrapper_attributes(). ?>>
	<?php if ( $title ) : ?>
	<h3 class="sg-block-comparison-table__title"><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>

	<?php if ( $description ) : ?>
	<div class="sg-block-comparison-table__desc"><?php echo wp_kses_post( $description ); ?></div>
	<?php endif; ?>

	<div class="sg-block-comparison-table__metrics">
		<?php
		foreach ( $rows as $row ) :
			$label       = $row['label'] ?? '';
			$our_value   = $row['our_value'] ?? '';
			$their_value = $row['their_value'] ?? '';

			$ours   = $ccc_ct_number( $our_value );
			$theirs = $ccc_ct_number( $their_value );

			// Both parse, both carry the same unit, and the pair has a positive
			// maximum to scale against — otherwise the row goes bar-less.
			$peak      = ( null !== $ours && null !== $theirs ) ? max( $ours, $theirs ) : 0;
			$same_unit = $ccc_ct_unit( $our_value ) === $ccc_ct_unit( $their_value );
			$draw_bars = ( null !== $ours && null !== $theirs && $same_unit && $peak > 0 );

			$sides = array(
				'ours'   => array(
					'class' => 'is-ours',
					'label' => $our_label,
					'value' => $our_value,
					'pct'   => $draw_bars ? max( 0, min( 100, ( $ours / $peak ) * 100 ) ) : 0,
				),
				'theirs' => array(
					'class' => 'is-theirs',
					'label' => $their_label,
					'value' => $their_value,
					'pct'   => $draw_bars ? max( 0, min( 100, ( $theirs / $peak ) * 100 ) ) : 0,
				),
			);
			?>
		<div class="sg-block-comparison-table__metric">
			<?php if ( $label ) : ?>
			<p class="sg-block-comparison-table__name"><?php echo esc_html( $label ); ?></p>
			<?php endif; ?>

			<?php foreach ( $sides as $side ) : ?>
			<div class="sg-block-comparison-table__row <?php echo esc_attr( $side['class'] ); ?><?php echo $draw_bars ? ' has-bars' : ''; ?>">
				<span class="sg-block-comparison-table__side"><?php echo esc_html( $side['label'] ); ?></span>
				<?php if ( $draw_bars ) : ?>
				<span class="sg-block-comparison-table__track" aria-hidden="true">
					<span class="sg-block-comparison-table__fill" style="--ccc-ct-pct: <?php echo esc_attr( round( $side['pct'], 2 ) ); ?>%"></span>
				</span>
				<?php endif; ?>
				<span class="sg-block-comparison-table__value"><?php echo esc_html( $side['value'] ); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $footnote ) : ?>
	<div class="sg-block-comparison-table__footnote"><?php echo wp_kses_post( $footnote ); ?></div>
	<?php endif; ?>
</div>
