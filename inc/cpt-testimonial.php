<?php
/**
 * Testimonials CPT.
 *
 * Matches Phase 3 spec in WORDPRESS_CONVERSION_PLAN.md:
 *  - Post title → person's name
 *  - Featured image → headshot
 *  - ACF fields → Company Name (text), Job Title (text), Quote (textarea)
 *  - Gutenberg disabled (`show_in_rest` stays true so the admin UI works but
 *    we don't enable `editor` in `supports`)
 *
 * ACF field group is defined separately — this file only registers the
 * post type.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ccc_register_testimonial_cpt() {
	$labels = array(
		'name'                  => _x( 'Testimonials',  'post type general name', 'classic-city-core' ),
		'singular_name'         => _x( 'Testimonial',   'post type singular name', 'classic-city-core' ),
		'menu_name'             => _x( 'Testimonials',  'admin menu',               'classic-city-core' ),
		'name_admin_bar'        => _x( 'Testimonial',   'add new on admin bar',     'classic-city-core' ),
		'add_new'               => __( 'Add New',                                   'classic-city-core' ),
		'add_new_item'          => __( 'Add New Testimonial',                       'classic-city-core' ),
		'new_item'              => __( 'New Testimonial',                           'classic-city-core' ),
		'edit_item'             => __( 'Edit Testimonial',                          'classic-city-core' ),
		'view_item'             => __( 'View Testimonial',                          'classic-city-core' ),
		'all_items'             => __( 'All Testimonials',                          'classic-city-core' ),
		'search_items'          => __( 'Search Testimonials',                       'classic-city-core' ),
		'not_found'             => __( 'No testimonials found.',                    'classic-city-core' ),
		'not_found_in_trash'    => __( 'No testimonials found in Trash.',           'classic-city-core' ),
		'featured_image'        => __( 'Headshot',                                  'classic-city-core' ),
		'set_featured_image'    => __( 'Set headshot',                              'classic-city-core' ),
		'remove_featured_image' => __( 'Remove headshot',                           'classic-city-core' ),
		'use_featured_image'    => __( 'Use as headshot',                           'classic-city-core' ),
	);

	$args = array(
		'labels'             => $labels,
		'description'        => __( 'Client testimonial — person + quote. Headshot via featured image; company/title/quote via ACF fields.', 'classic-city-core' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'menu_position'      => 25,
		'menu_icon'          => 'dashicons-format-quote',
		'capability_type'    => 'post',
		'has_archive'        => false,
		'rewrite'            => false,
		// Intentionally omit 'editor' — Gutenberg is disabled per plan decision.
		'supports'           => array( 'title', 'thumbnail' ),
	);

	register_post_type( 'testimonial', $args );
}
add_action( 'init', 'ccc_register_testimonial_cpt' );
