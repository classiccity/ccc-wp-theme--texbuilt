<?php
/**
 * Proof Band block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Markup contract:
 *   .sg-block-proof-band                      (the band root; carries the bg class)
 *     p.is-style-eyebrow.sg-block-proof-band__kicker
 *     .sg-block-proof-band__stats
 *       .sg-block-proof-band__stat            (one per repeater row)
 *         span.sg-block-proof-band__value
 *         p.sg-block-proof-band__label
 *     blockquote.sg-block-proof-band__quote
 *       p.sg-block-proof-band__quote-text
 *       cite.sg-block-proof-band__cite
 *     .wp-block-button.sg-block-proof-band__action
 *       a.wp-block-button__link.wp-element-button.sg-block-proof-band__link
 *
 * THE BAND is the block root, so — unlike card-detail-rows, which repaints its
 * cards — WP's native picker injects the background helper class exactly where
 * it belongs and nothing needs stripping. The paired `-opposite` text color
 * rides along on that class, which is what lets every color rule below resolve
 * from `currentColor` and still be legible on a dark band or a light one.
 *
 * BORDER COLOR is derived, not authored: the palette PARTNER of whatever
 * background was picked (`ink` band → `ink-alt` border) via
 * ccc_palette_partner_slug(). Gradients (no single slug to pair with) and the
 * unpaired surface slugs (canvas / panel / ink / ink-soft) fall back to the
 * default border token. It arrives as `--ccc-pb-border-color` so a child theme
 * retargets it in one line.
 *
 * THE QUOTE is a real <blockquote> + <cite>, not a styled paragraph, because
 * content rule 23 reserves quote markup for words somebody actually said — and
 * the field instructions say so. The quotation marks are added HERE rather than
 * typed into the field, so a site can swap them for locale-correct glyphs (or
 * drop them) from CSS without editing content.
 *
 * ACCENT CYCLING is CSS's job, not PHP's: the stats carry no per-row class or
 * index, and blocks.css assigns accent slots with :nth-child(3n+…). Keeping the
 * cycle in one place means a child theme changes the rhythm by redefining three
 * variables instead of hunting for indexes baked into markup.
 *
 * @package ClassicCityCore
 */

$kicker      = get_field( 'kicker' );
$stats       = get_field( 'stats' );
$quote       = get_field( 'quote' );
$attribution = get_field( 'quote_attribution' );
$link_text   = get_field( 'link_text' );
$link_url    = get_field( 'link_url' );

$has_stats = is_array( $stats ) && ! empty( $stats );

// A band with neither figures nor a quote has nothing to prove — render nothing
// rather than an empty painted surface.
if ( ! $has_stats && ! $quote ) {
	return;
}

// Block-level background from the native picker — solid slug or gradient slug.
$bg_slug       = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );
$gradient_slug = sanitize_html_class( $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' ) );

$partner_slug = $gradient_slug ? '' : ccc_palette_partner_slug( $bg_slug );

$wrapper_args = array( 'class' => 'sg-block-proof-band' );
if ( $partner_slug ) {
	$wrapper_args['style'] = '--ccc-pb-border-color: var(--wp--preset--color--' . $partner_slug . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $wrapper_args );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by get_block_wrapper_attributes(). ?>>
	<?php if ( $kicker ) : ?>
	<p class="is-style-eyebrow sg-block-proof-band__kicker"><?php echo esc_html( $kicker ); ?></p>
	<?php endif; ?>

	<?php if ( $has_stats ) : ?>
	<div class="sg-block-proof-band__stats">
		<?php foreach ( $stats as $stat ) : ?>
		<div class="sg-block-proof-band__stat">
			<span class="sg-block-proof-band__value"><?php echo esc_html( $stat['value'] ?? '' ); ?></span>
			<p class="sg-block-proof-band__label"><?php echo esc_html( $stat['label'] ?? '' ); ?></p>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( $quote ) : ?>
	<blockquote class="sg-block-proof-band__quote">
		<p class="sg-block-proof-band__quote-text">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
		<?php if ( $attribution ) : ?>
		<cite class="sg-block-proof-band__cite"><?php echo esc_html( $attribution ); ?></cite>
		<?php endif; ?>
	</blockquote>
	<?php endif; ?>

	<?php if ( $link_text && $link_url ) : ?>
	<div class="wp-block-button sg-block-proof-band__action">
		<a class="wp-block-button__link wp-element-button sg-block-proof-band__link" href="<?php echo esc_url( $link_url ); ?>"><?php echo esc_html( $link_text ); ?></a>
	</div>
	<?php endif; ?>
</div>
