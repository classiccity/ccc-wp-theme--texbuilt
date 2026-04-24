<?php
/**
 * Image Portfolio Gallery block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Image Portfolio Gallery.
 *
 * Items are serialized into a JSON array on the wrapper via `data-items`. The
 * lightbox script (assets/portfolio-lightbox.js) reads that JSON, wires up the
 * per-item <button> clicks, and injects its own overlay element into <body>
 * on first use — so there's no inline lightbox markup here.
 *
 * @package ClassicCityCore
 */

$items = get_field( 'items' );
if ( ! is_array( $items ) || empty( $items ) ) {
	return;
}

$data = array();
foreach ( $items as $idx => $row ) {
	$img   = $row['image'] ?? array();
	$img   = ccc_resolve_image_or_demo( $img, 'portfolio-' . $idx, 960, 540 );
	$url   = ! empty( $img['url'] ) ? $img['url'] : '';
	if ( ! $url ) {
		continue;
	}
	$data[] = array(
		'image'   => $url,
		'title'   => $row['title'] ?? '',
		'caption' => $row['caption'] ?? '',
	);
}

if ( empty( $data ) ) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class'      => 'sg-block-portfolio',
		'data-items' => wp_json_encode( $data ),
	)
);
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $data as $i => $item ) : ?>
	<div class="sg-block-portfolio-item">
		<button
			class="sg-block-portfolio-item-btn"
			type="button"
			data-index="<?php echo esc_attr( (string) $i ); ?>"
			aria-label="<?php echo esc_attr( sprintf( __( 'Open %s', 'classic-city-core' ), $item['title'] ) ); ?>"
		></button>
		<div class="sg-block-portfolio-item-image" style="background-image: url('<?php echo esc_url( $item['image'] ); ?>')"></div>
		<?php if ( $item['title'] ) : ?>
		<div class="sg-block-portfolio-item-title"><?php echo esc_html( $item['title'] ); ?></div>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
</div>
