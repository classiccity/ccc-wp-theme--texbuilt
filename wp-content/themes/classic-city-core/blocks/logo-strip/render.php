<?php
/**
 * Logo Strip block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Logo Strip.
 *
 * @package ClassicCityCore
 */

$eyebrow = get_field( 'eyebrow' );
$logos   = get_field( 'logos' );

if ( ! is_array( $logos ) || empty( $logos ) ) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'sg-block-logos' ) );
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php if ( $eyebrow ) : ?>
	<p class="sg-block-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
	<?php endif; ?>
	<div class="sg-block-logos-row">
		<?php foreach ( $logos as $idx => $row ) :
			$img = $row['logo_image'] ?? array();
			$img = ccc_resolve_image_or_demo( $img, 'logo-' . $idx, 240, 80 );
			$url = ! empty( $img['url'] ) ? $img['url'] : '';
			$alt = ! empty( $img['alt'] ) ? $img['alt'] : '';
			if ( ! $url ) {
				continue;
			}
		?>
		<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" />
		<?php endforeach; ?>
	</div>
</div>
