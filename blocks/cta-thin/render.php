<?php
/**
 * Thin CTA block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Thin CTA.
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

$style = '';
$bg_image_url = ! empty( $bg_image['url'] ) ? $bg_image['url'] : '';
if ( $bg_image_url ) {
	$style = 'background-image: url(\'' . esc_url( $bg_image_url ) . '\'); --sg-cta-overlay-opacity: ' . max( 0, min( 100, $bg_opacity ) ) / 100;
}

$wrapper_attrs = get_block_wrapper_attributes(
	array_filter(
		array(
			'class' => implode( ' ', $classes ),
			'style' => $style,
		)
	)
);
?>
<div <?php echo $wrapper_attrs; ?>>
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
