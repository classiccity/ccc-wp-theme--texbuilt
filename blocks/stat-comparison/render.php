<?php
/**
 * Stat Comparison block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Markup contract:
 *   .sg-block-stat-comparison                    (the panel root; carries the bg class)
 *     p.sg-block-stat-comparison__figure         (role="img", aria-label "X to Y")
 *       span.sg-block-stat-comparison__from
 *       span.sg-block-stat-comparison__arrow     (aria-hidden — decorative)
 *       span.sg-block-stat-comparison__to
 *     .sg-block-stat-comparison__text
 *       h3.sg-block-stat-comparison__heading
 *       .sg-block-stat-comparison__body          (WYSIWYG output)
 *
 * THE ARROW is decorative and is hidden from assistive tech. Left exposed, the
 * "→" glyph is announced inconsistently — "rightwards arrow", "black right
 * arrow", or nothing at all depending on the screen reader — so the figure
 * carries role="img" plus an aria-label built here as "{from} to {to}". That
 * single label is what gets read; the three spans inside are presentational.
 *
 * THE FIGURE IS A PARAGRAPH, not a heading. It is not a section title, it has
 * no place in the document outline, and the h3 beside it is the real heading
 * for this content. (The `stats` block uses h3 for its values; that block
 * predates the rule and its values genuinely are the section's content.)
 *
 * THE PANEL is the block root, so WP's native picker injects the background
 * helper class exactly where it belongs — nothing to strip. The paired
 * `-opposite` text color rides along, which is what lets the "from" figure and
 * the arrow resolve from `currentColor` and stay legible on any surface.
 *
 * BORDER COLOR is derived: the palette PARTNER of the picked background
 * (`panel` → `panel-alt`) via ccc_palette_partner_slug(). Gradients and the
 * unpaired surface slugs fall back to the default border token. It arrives as
 * `--ccc-sc-border-color` so a child retargets it in one line.
 *
 * @package ClassicCityCore
 */

$from_value = get_field( 'from_value' );
$to_value   = get_field( 'to_value' );
$heading    = get_field( 'heading' );
$body       = get_field( 'body' );

// Half a comparison is not a comparison — both figures or nothing.
if ( ! $from_value || ! $to_value ) {
	return;
}

/* translators: 1: the "before" figure, 2: the "after" figure. Spoken label for
   the decorative arrow between them. */
$figure_label = sprintf( __( '%1$s to %2$s', 'classic-city-core' ), $from_value, $to_value );

// Block-level background from the native picker — solid slug or gradient slug.
$bg_slug       = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );
$gradient_slug = sanitize_html_class( $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' ) );

$partner_slug = $gradient_slug ? '' : ccc_palette_partner_slug( $bg_slug );

$wrapper_args = array( 'class' => 'sg-block-stat-comparison' );
if ( $partner_slug ) {
	$wrapper_args['style'] = '--ccc-sc-border-color: var(--wp--preset--color--' . $partner_slug . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $wrapper_args );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by get_block_wrapper_attributes(). ?>>
	<p class="sg-block-stat-comparison__figure" role="img" aria-label="<?php echo esc_attr( $figure_label ); ?>">
		<span class="sg-block-stat-comparison__from"><?php echo esc_html( $from_value ); ?></span>
		<span class="sg-block-stat-comparison__arrow" aria-hidden="true">&rarr;</span>
		<span class="sg-block-stat-comparison__to"><?php echo esc_html( $to_value ); ?></span>
	</p>

	<div class="sg-block-stat-comparison__text">
		<?php if ( $heading ) : ?>
		<h3 class="sg-block-stat-comparison__heading"><?php echo esc_html( $heading ); ?></h3>
		<?php endif; ?>

		<?php if ( $body ) : ?>
		<div class="sg-block-stat-comparison__body"><?php echo wp_kses_post( $body ); ?></div>
		<?php endif; ?>
	</div>
</div>
