<?php
/**
 * Testimonial Cards block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Testimonial Cards.
 *
 * @package ClassicCityCore
 */

$ids           = get_field( 'testimonials' );
$columns       = (int) ( get_field( 'desktop_columns' ) ?: 3 );
$mobile_layout = get_field( 'mobile_layout' ) ?: 'column-count';

if ( empty( $ids ) || ! is_array( $ids ) ) {
	return;
}

$columns = max( 1, min( 4, $columns ) );

$classes = array( 'sg-block-testimonial-grid' );
if ( $mobile_layout === 'horizontal-scroll' ) {
	$classes[] = 'mobile-horizontal-scroll';
}

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $classes ),
		'style' => '--testimonial-columns: ' . $columns,
	)
);

// FontAwesome Solid "quote-left" path (free, bundled for inlining).
$quote_svg = '<svg viewBox="0 0 448 512" width="36" height="36" aria-hidden="true" focusable="false"><path fill="currentColor" d="M0 216C0 149.7 53.7 96 120 96l8 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-8 0c-30.9 0-56 25.1-56 56l0 8 64 0c35.3 0 64 28.7 64 64l0 64c0 35.3-28.7 64-64 64l-64 0c-35.3 0-64-28.7-64-64L0 216zm256 0c0-66.3 53.7-120 120-120l8 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-8 0c-30.9 0-56 25.1-56 56l0 8 64 0c35.3 0 64 28.7 64 64l0 64c0 35.3-28.7 64-64 64l-64 0c-35.3 0-64-28.7-64-64l0-136z"/></svg>';
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $ids as $id ) :
		$name    = get_the_title( $id );
		$company = get_field( 'company_name', $id );
		$title   = get_field( 'job_title', $id );
		$quote   = get_field( 'quote', $id );
		$role    = trim( implode( ', ', array_filter( array( $title, $company ) ) ) );
	?>
	<div class="sg-block-testimonial-card">
		<div class="sg-block-testimonial-card-icon"><?php echo $quote_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<p class="sg-block-testimonial-card-quote"><?php echo wp_kses_post( $quote ); ?></p>
		<div>
			<p class="has-small-font-size" style="font-weight: 700"><?php echo esc_html( $name ); ?></p>
			<?php if ( $role ) : ?>
			<p class="has-x-small-font-size" style="opacity: 0.65"><?php echo esc_html( $role ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php endforeach; ?>
</div>
