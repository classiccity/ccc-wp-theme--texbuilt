<?php
/**
 * Card with Detail Rows block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Markup contract:
 *   .sg-block-card-detail-rows            (the grid root, .is-mobile-{stack|scroll})
 *     .sg-block-card-detail-rows__card      (one per repeater row; carries the bg class)
 *       p.is-style-eyebrow
 *       h3
 *       ul.sg-block-card-detail-rows__rows
 *         li.sg-block-card-detail-rows__row
 *           span.sg-block-card-detail-rows__label
 *           span.sg-block-card-detail-rows__value
 *       .sg-block-card-detail-rows__desc    (WYSIWYG output)
 *       .sg-block-card-detail-rows__actions (margin-top:auto → pins to card bottom)
 *         .wp-block-button > a.wp-block-button__link
 *
 * BACKGROUND lands on the CARDS, not the grid. The admin picks one color (or
 * gradient) with WP's native picker at the block level; WP injects the helper
 * class onto the block root, so we strip it back off with
 * ccc_strip_bg_from_wrapper() and re-apply it to every card. Same mechanism as
 * feature-grid. The paired `-opposite` text color rides along on the helper
 * class, so card text stays readable on light and dark surfaces alike.
 *
 * BORDER COLOR is derived, not authored: it defaults to the palette PARTNER of
 * whatever background was picked (`primary` card → `primary-alt` border) via
 * ccc_palette_partner_slug(). No color math — the palette is already built in
 * base/-alt pairs. Three cases fall back to the default border token instead:
 * a gradient background (no single slug to pair with), an unpaired surface slug
 * (canvas / panel / ink / ink-soft), and no background at all. Either way the
 * value arrives as `--ccc-cdr-border-color` on the grid root, so a child theme
 * can override it in one line without forking the block.
 *
 * @package ClassicCityCore
 */

$cards         = get_field( 'cards' );
$columns       = (int) ( get_field( 'desktop_columns' ) ?: 3 );
$mobile_layout = get_field( 'mobile_layout' ) ?: 'stack';

if ( ! is_array( $cards ) || empty( $cards ) ) {
	return;
}

$columns = max( 1, min( 4, $columns ) );

if ( ! in_array( $mobile_layout, array( 'stack', 'scroll' ), true ) ) {
	$mobile_layout = 'stack';
}

// Block-level background from the native picker — solid slug or gradient slug.
$bg_slug       = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );
$gradient_slug = sanitize_html_class( $block['gradient'] ?? ( $block['attrs']['gradient'] ?? '' ) );

$card_classes = array( 'sg-block-card-detail-rows__card' );
if ( $gradient_slug ) {
	$card_classes[] = 'has-' . $gradient_slug . '-gradient-background';
	$card_classes[] = 'has-background';
} elseif ( $bg_slug ) {
	$card_classes[] = 'has-' . $bg_slug . '-background-color';
	$card_classes[] = 'has-background';
}

// Border = the background's palette partner. Gradients have no single slug to
// pair against, so they fall through to the CSS default like an unpainted card.
$partner_slug = $gradient_slug ? '' : ccc_palette_partner_slug( $bg_slug );

$root_style = '--block-columns: ' . $columns . ';';
if ( $partner_slug ) {
	$root_style .= '--ccc-cdr-border-color: var(--wp--preset--color--' . $partner_slug . ');';
}

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes(
		array(
			'class' => 'sg-block-card-detail-rows is-mobile-' . $mobile_layout,
			'style' => $root_style,
		)
	)
);
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by get_block_wrapper_attributes(). ?>>
	<?php
	foreach ( $cards as $card ) :
		$eyebrow     = $card['eyebrow'] ?? '';
		$heading     = $card['heading'] ?? '';
		$rows        = $card['rows'] ?? array();
		$description = $card['description'] ?? '';
		$button_text = $card['button_text'] ?? '';
		$button_link = $card['button_link'] ?? '';

		// Only 'outline' and 'text' need a class — 'fill' is the base button.
		$button_style   = $card['button_style'] ?? 'fill';
		$button_classes = array( 'wp-block-button', 'sg-block-card-detail-rows__button' );
		if ( 'outline' === $button_style ) {
			$button_classes[] = 'is-style-outline';
		} elseif ( 'text' === $button_style ) {
			$button_classes[] = 'is-style-text';
		}
		?>
	<div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
		<?php if ( $eyebrow ) : ?>
		<p class="is-style-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
		<h3><?php echo esc_html( $heading ); ?></h3>
		<?php endif; ?>

		<?php if ( is_array( $rows ) && ! empty( $rows ) ) : ?>
		<ul class="sg-block-card-detail-rows__rows">
			<?php foreach ( $rows as $row ) : ?>
			<li class="sg-block-card-detail-rows__row">
				<span class="sg-block-card-detail-rows__label"><?php echo esc_html( $row['label'] ?? '' ); ?></span>
				<span class="sg-block-card-detail-rows__value"><?php echo esc_html( $row['value'] ?? '' ); ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>

		<?php if ( $description ) : ?>
		<div class="sg-block-card-detail-rows__desc has-small-font-size"><?php echo wp_kses_post( $description ); ?></div>
		<?php endif; ?>

		<?php if ( $button_text && $button_link ) : ?>
		<div class="sg-block-card-detail-rows__actions">
			<div class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>">
				<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $button_link ); ?>"><?php echo esc_html( $button_text ); ?></a>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
</div>
