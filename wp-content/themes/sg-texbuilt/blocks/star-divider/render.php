<?php
/**
 * TexBuilt Star Divider — three Font Awesome stars in a row (medium / large /
 * medium), each rotating at a slightly different speed.
 *
 * Color defaults to inherit (so the stars pick up the surrounding text color).
 * When the admin picks a palette slug, WP's auto-generated
 * `.has-{slug}-color` helper colors the wrapper and the icons inherit it.
 *
 * @package SgTexbuilt
 */

$color = get_field( 'color' );

$wrapper_classes = array( 'sg-texbuilt-star-divider' );
if ( $color ) {
	$wrapper_classes[] = 'has-text-color';
	$wrapper_classes[] = 'has-' . sanitize_html_class( $color ) . '-color';
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $wrapper_classes ) ) );

// Parent theme helper composes "fa-solid fa-star" (or whatever FA style is
// set in theme.json's settings.custom.icons.style). Fallback to fa-solid for
// safety if the helper isn't loaded.
$fa_class = function_exists( 'ccc_fa_icon_class' ) ? ccc_fa_icon_class( 'star' ) : 'fa-solid fa-star';
?>
<div <?php echo $wrapper_attrs; ?>>
	<i class="<?php echo esc_attr( $fa_class ); ?> sg-texbuilt-star-divider-star is-left" aria-hidden="true"></i>
	<i class="<?php echo esc_attr( $fa_class ); ?> sg-texbuilt-star-divider-star is-center" aria-hidden="true"></i>
	<i class="<?php echo esc_attr( $fa_class ); ?> sg-texbuilt-star-divider-star is-right" aria-hidden="true"></i>
</div>
