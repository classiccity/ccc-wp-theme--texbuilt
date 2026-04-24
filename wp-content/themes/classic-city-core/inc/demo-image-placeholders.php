<?php
/**
 * Demo image placeholders for the /style-guide page.
 *
 * Each ACF block's render.php calls `ccc_resolve_image_or_demo( $image,
 * $seed, $w, $h )` right after `get_field('image')`. If the image array is
 * already populated (real uploaded attachment), it passes through untouched.
 * Otherwise, when we're on `/style-guide` (or `?ccc_demo=1` is set), we swap
 * in a picsum.photos URL so the demo page shows visual content instead of
 * collapsed empty blocks.
 *
 * We tried an `acf/format_value/type=image` filter first; ACF's field
 * formatter kept returning false for the 0-stored attachment IDs in our
 * pattern, so render.php's `! empty($image['url'])` guard evaluated false
 * before our filter could substitute. The direct-call approach sidesteps
 * ACF's formatter entirely and is also easier to reason about.
 *
 * Production pages are untouched because (a) our helper bails when the image
 * is populated, and (b) it bails when `is_demo_page()` returns false.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True if we're currently rendering the /style-guide page, or if the user
 * passed ?ccc_demo=1 to force-enable demo placeholders from anywhere.
 * Cached per-request.
 */
function ccc_is_demo_page() {
	static $is_demo = null;
	if ( $is_demo !== null ) {
		return $is_demo;
	}
	if ( isset( $_GET['ccc_demo'] ) && $_GET['ccc_demo'] === '1' ) {
		$is_demo = true;
		return $is_demo;
	}
	$q = function_exists( 'get_queried_object' ) ? get_queried_object() : null;
	$is_demo = ( $q instanceof \WP_Post ) && ( $q->post_name === 'style-guide' );
	return $is_demo;
}

/**
 * Returns an image array populated with a picsum placeholder when the
 * incoming $image is empty AND we're on the demo page. Otherwise returns
 * $image untouched.
 *
 * @param mixed  $image  The ACF image field value (array|false|null).
 * @param string $seed   A slug to vary the picsum URL per block instance.
 * @param int    $w      Preferred width.
 * @param int    $h      Preferred height.
 * @return array An image array with at least 'url' + 'alt'.
 */
function ccc_resolve_image_or_demo( $image, $seed = 'demo', $w = 960, $h = 720 ) {
	if ( is_array( $image ) && ! empty( $image['url'] ) ) {
		return $image;
	}
	if ( ! ccc_is_demo_page() ) {
		return is_array( $image ) ? $image : array();
	}

	static $counter = 0;
	$counter++;
	$slug = sanitize_key( $seed ) . '-' . $counter;
	$url  = "https://picsum.photos/seed/ccc-demo-{$slug}/{$w}/{$h}";
	return array(
		'ID'     => 0,
		'url'    => $url,
		'alt'    => 'Demo placeholder',
		'width'  => $w,
		'height' => $h,
		'sizes'  => array(
			'thumbnail' => "https://picsum.photos/seed/ccc-demo-{$slug}/150/150",
			'medium'    => $url,
			'large'     => $url,
			'full'      => $url,
		),
	);
}

/*
 * Kept the old ACF format_value filter as a belt-and-suspenders layer —
 * harmless if ACF silently ignores it, useful if it does fire.
 */
add_filter( 'acf/format_value/type=image', 'ccc_demo_image_placeholder', 10, 3 );
/**
 * @param mixed $value   The stored attachment ID (or empty/false/0).
 * @param int|string $post_id ACF's post ID context.
 * @param array $field   ACF field definition.
 * @return mixed The original value, or a placeholder array matching ACF's
 *               `return_format=array` shape.
 */
function ccc_demo_image_placeholder( $value, $post_id, $field ) {
	// Keep real attachments. Real return is array|false from acf_get_attachment().
	if ( $value && is_array( $value ) && ! empty( $value['url'] ) ) {
		return $value;
	}

	// Only substitute on the /style-guide page. `is_page()` is flaky under
	// some ACF block contexts (secondary queries during testimonial CPT
	// lookup, REST block-preview rendering), so we compare the main query's
	// queried object's post_name directly. Cache per-request.
	// `?ccc_demo=1` is a diagnostic override so we can tell apart "filter
	// isn't firing" from "detection is wrong" — leave it for now.
	static $is_demo = null;
	if ( $is_demo === null ) {
		if ( isset( $_GET['ccc_demo'] ) && $_GET['ccc_demo'] === '1' ) {
			$is_demo = true;
		} else {
			$q = function_exists( 'get_queried_object' ) ? get_queried_object() : null;
			$is_demo = ( $q instanceof \WP_Post ) && ( $q->post_name === 'style-guide' );
		}
	}
	if ( ! $is_demo ) {
		return $value;
	}

	// Stable-within-request seed: we tick a counter per image we substitute.
	// picsum returns different images per seed so the demo page looks varied
	// instead of being "same photo × 30".
	static $counter = 0;
	$counter++;

	$w = 960;
	$h = 720;
	// Portrait for tile-shaped fields (ccc-block's image-tiles uses 3:4).
	if ( ( $field['name'] ?? '' ) === 'image' && ( $field['parent'] ?? '' ) === 'field_image_tiles_items' ) {
		$w = 720;
		$h = 960;
	}
	return array(
		'ID'        => 0,
		'id'        => 0,
		'url'       => "https://picsum.photos/seed/ccc-demo-{$counter}/{$w}/{$h}",
		'alt'       => 'Demo placeholder',
		'title'     => 'Demo placeholder',
		'caption'   => '',
		'description' => '',
		'mime_type' => 'image/jpeg',
		'type'      => 'image',
		'subtype'   => 'jpeg',
		'icon'      => '',
		'width'     => $w,
		'height'    => $h,
		'sizes'     => array(
			'thumbnail'        => "https://picsum.photos/seed/ccc-demo-{$counter}/150/150",
			'thumbnail-width'  => 150,
			'thumbnail-height' => 150,
			'medium'           => "https://picsum.photos/seed/ccc-demo-{$counter}/{$w}/{$h}",
			'medium-width'     => $w,
			'medium-height'    => $h,
			'large'            => "https://picsum.photos/seed/ccc-demo-{$counter}/{$w}/{$h}",
			'large-width'      => $w,
			'large-height'     => $h,
			'full'             => "https://picsum.photos/seed/ccc-demo-{$counter}/{$w}/{$h}",
			'full-width'       => $w,
			'full-height'      => $h,
		),
	);
}
