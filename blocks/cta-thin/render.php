<?php
/**
 * Thin CTA block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Fully field-driven — no InnerBlocks. The horizontal L/R layout depends on
 * two direct flex children, so we emit exactly those: `.sg-block-cta-thin-copy`
 * (headline + subtext) and `.wp-block-button.sg-block-cta-button`.
 *
 * @package ClassicCityCore
 */

$headline       = get_field( 'headline' );
$headline_level = get_field( 'headline_level' ) ?: 'h3';
$subtext        = get_field( 'subtext' );
$button_label   = get_field( 'button_label' );
$button_url     = get_field( 'button_url' );
$bg_image       = get_field( 'bg_image' );
$bg_opacity     = (int) ( get_field( 'bg_opacity' ) ?? 80 );
$has_texture    = (bool) get_field( 'has_texture' );

// Allow-list the headline tag to guard against unexpected values saved
// before this field existed or any future schema drift.
$allowed_levels = array( 'p', 'h2', 'h3', 'h4', 'h5', 'h6' );
if ( ! in_array( $headline_level, $allowed_levels, true ) ) {
	$headline_level = 'h3';
}

if ( ! $headline && ! $button_label ) {
	return;
}

$classes = array( 'sg-block-cta-thin', 'alignfull' );
if ( $has_texture ) {
	$classes[] = 'has-bg-texture';
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );

/* Layered background model — same shape as cta-large. Wrapper paints the
   color via WP's helper class (supports.color.background); .sg-block-cta-thin-bg
   is an absolute overlay child carrying the image + opacity; the inner row
   stacks above on z-index. Saved field shapes (bg_image, bg_opacity,
   has_texture) unchanged — render-only refactor. */
$bg_image_url       = ! empty( $bg_image['url'] ) ? $bg_image['url'] : '';
$bg_image_alt       = ! empty( $bg_image['alt'] ) ? $bg_image['alt'] : '';
$bg_opacity_decimal = max( 0, min( 100, $bg_opacity ) ) / 100;
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $bg_image_url ) : ?>
	<div
		class="sg-block-cta-thin-bg"
		role="img"
		aria-label="<?php echo esc_attr( $bg_image_alt ); ?>"
		style="background-image: url('<?php echo esc_url( $bg_image_url ); ?>'); opacity: <?php echo esc_attr( $bg_opacity_decimal ); ?>;"
	></div>
	<?php endif; ?>
	<div class="sg-block-cta-thin-inner">
		<div class="sg-block-cta-thin-copy">
			<?php if ( $headline ) : ?>
				<?php if ( $headline_level === 'p' ) : ?>
				<p class="has-large-font-size"><strong><?php echo esc_html( $headline ); ?></strong></p>
				<?php else : ?>
				<<?php echo esc_attr( $headline_level ); ?>><?php echo esc_html( $headline ); ?></<?php echo esc_attr( $headline_level ); ?>>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ( $subtext ) : ?>
			<p class="has-medium-font-size"><?php echo esc_html( $subtext ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $button_label && $button_url ) : ?>
		<div class="wp-block-button sg-block-cta-button">
			<a
				class="wp-block-button__link wp-element-button has-medium-font-size"
				href="<?php echo esc_url( $button_url ); ?>"
			><?php echo esc_html( $button_label ); ?></a>
		</div>
		<?php endif; ?>
	</div>
</div>
