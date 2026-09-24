<?php
/**
 * Post Card block render template.
 *
 * A thin adapter for the Query Loop: it pulls the CURRENT post's data
 * (featured image, first category, title, excerpt, permalink) and hands it
 * to the shared `ccc_render_image_card()` partial — so the emitted markup
 * and CSS classes are byte-for-byte identical to an image-card-grid card.
 * The only authorable inputs are presentation toggles (see fields.php).
 *
 * Drop it inside a core/post-template; outside the loop it renders nothing.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The partial is the whole point of this block; bail if it isn't loaded.
if ( ! function_exists( 'ccc_render_image_card' ) ) {
	return;
}

$post_id = get_the_ID();
if ( ! $post_id ) {
	return; // Not in a post context (e.g. inserted outside a loop).
}

$aspect = (string) ( get_field( 'image_aspect_ratio' ) ?: '16/9' );

// true_false fields default to ON when the block carries no saved value
// (e.g. hand-placed in a template), so a bare post-card still shows both.
$show_category = get_field( 'show_category' );
$show_category = ( null === $show_category ) ? true : (bool) $show_category;
$show_excerpt = get_field( 'show_excerpt' );
$show_excerpt = ( null === $show_excerpt ) ? true : (bool) $show_excerpt;

$read_more = trim( (string) get_field( 'read_more_text' ) );
if ( '' === $read_more ) {
	$read_more = __( 'Read more', 'classic-city-core' );
}

$permalink = (string) get_permalink( $post_id );

// Featured image -> the {url, alt} shape the partial expects. The card's
// .sg-image-card__image class already carries the brand duotone via CSS.
$image    = array();
$thumb_id = get_post_thumbnail_id( $post_id );
if ( $thumb_id ) {
	$image = array(
		'url' => (string) get_the_post_thumbnail_url( $post_id, 'large' ),
		'alt' => (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ),
	);
}

// First category as the eyebrow / tag, when enabled.
$tag = '';
if ( $show_category ) {
	$cats = get_the_category( $post_id );
	if ( ! empty( $cats ) ) {
		$tag = (string) $cats[0]->name;
	}
}

$description = $show_excerpt ? trim( wp_strip_all_tags( get_the_excerpt( $post_id ) ) ) : '';

ccc_render_image_card(
	array(
		'image'              => $image,
		'image_aspect_ratio' => $aspect,
		'bg_color'           => 'panel',
		'tag'                => $tag,
		'title'              => (string) get_the_title( $post_id ),
		'title_url'          => $permalink,
		'description'        => $description,
		'footer_link_text'   => $read_more,
		'footer_link_url'    => $permalink,
	)
);
