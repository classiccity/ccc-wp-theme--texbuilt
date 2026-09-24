<?php
/**
 * ACF field group for the Post Card block.
 *
 * Post Card is a thin, query-loop-only adapter over the shared image-card
 * partial: the data (image, title, category, excerpt, link) is pulled from
 * the current post, so the only authorable controls are presentation:
 *
 *  - image_aspect_ratio : default aspect ratio for the featured image
 *  - show_category      : toggle the category eyebrow on/off
 *  - show_excerpt       : toggle the excerpt on/off
 *  - read_more_text     : footer link label (always links to the post)
 *
 * Aspect-ratio choices mirror image-card-grid so the two blocks feel
 * identical to author.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'    => 'group_block_post_card',
		'title'  => __( 'Post Card', 'classic-city-core' ),
		'fields' => array(
			array(
				'key'           => 'field_post_card_image_aspect_ratio',
				'label'         => __( 'Image Aspect Ratio', 'classic-city-core' ),
				'name'          => 'image_aspect_ratio',
				'type'          => 'select',
				'choices'       => array(
					'16/9' => '16 : 9 (landscape)',
					'3/2'  => '3 : 2 (landscape)',
					'4/3'  => '4 : 3 (landscape)',
					'1/1'  => '1 : 1 (square)',
					'4/5'  => '4 : 5 (slight portrait)',
					'3/4'  => '3 : 4 (portrait)',
				),
				'default_value' => '16/9',
				'allow_null'    => 0,
				'ui'            => 1,
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_post_card_read_more_text',
				'label'         => __( 'Read More Text', 'classic-city-core' ),
				'name'          => 'read_more_text',
				'type'          => 'text',
				'default_value' => __( 'Read more', 'classic-city-core' ),
				'placeholder'   => __( 'Read more', 'classic-city-core' ),
				'instructions'  => __( 'Footer link label. Always links to the post.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_post_card_show_category',
				'label'         => __( 'Show category', 'classic-city-core' ),
				'name'          => 'show_category',
				'type'          => 'true_false',
				'default_value' => 1,
				'ui'            => 1,
				'instructions'  => __( 'Show the post&rsquo;s first category as the eyebrow above the title.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_post_card_show_excerpt',
				'label'         => __( 'Show excerpt', 'classic-city-core' ),
				'name'          => 'show_excerpt',
				'type'          => 'true_false',
				'default_value' => 1,
				'ui'            => 1,
				'instructions'  => __( 'Show the post excerpt as the card description.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/post-card',
				),
			),
		),
		'position'              => 'normal',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
		'show_in_rest'          => 1,
	)
);
