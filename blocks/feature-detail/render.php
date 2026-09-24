<?php
/**
 * Feature Detail block render template.
 *
 * The block is a thin wrapper around partials/feature-detail-section.php —
 * the partial emits the canonical 2-col layout shared with one toggle
 * panel in Product Feature Toggles. Identical inner HTML guarantees
 * identical styling.
 *
 * The wrapper sets `--detail-color` from the per-instance accent slug
 * so descendant `.sg-block-feature-detail__detail` rules pick it up
 * for the colored left bar + icon tint.
 *
 * @package ClassicCityCore
 */

$image          = get_field( 'image' );
$image_subtitle = (string) ( get_field( 'image_subtitle' ) ?? '' );
$image_title    = (string) ( get_field( 'image_title' )    ?? '' );
$headline       = (string) ( get_field( 'headline' )       ?? '' );
$description    = (string) ( get_field( 'description' )    ?? '' );
$button_text    = (string) ( get_field( 'button_text' )    ?? '' );
$button_url     = (string) ( get_field( 'button_url' )     ?? '' );
$detail_color   = sanitize_html_class( get_field( 'detail_color' ) ?: 'secondary' );
$detail_rows    = is_array( get_field( 'detail_rows' ) ) ? get_field( 'detail_rows' ) : array();

// Demo fallback for /style-guide.
if ( function_exists( 'ccc_resolve_image_or_demo' ) ) {
	$image = ccc_resolve_image_or_demo( $image, 'feature-detail', 720, 720 );
}

$wrapper_style = $detail_color
	? '--detail-color: var(--wp--preset--color--' . $detail_color . ');'
	: '';

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'sg-block-feature-detail-wrap',
	'style' => $wrapper_style,
) );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	ccc_render_feature_detail_section( array(
		'image'          => $image,
		'image_subtitle' => $image_subtitle,
		'image_title'    => $image_title,
		'headline'       => $headline,
		'description'    => $description,
		'button_text'    => $button_text,
		'button_url'     => $button_url,
		'detail_rows'    => $detail_rows,
	) );
	?>
</div>
