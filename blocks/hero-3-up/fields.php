<?php
/**
 * ACF field group for the Hero 3 Up block.
 *
 * Field widths follow Chris's spec — Title/Eyebrow side-by-side at 50/50,
 * Description full-width, Button text/link 50/50, then aspect ratio + image.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_hero_3_up',
		'title'                 => __( 'Hero 3 Up', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'      => 'field_hero_3_up_title',
				'label'    => __( 'Title', 'classic-city-core' ),
				'name'     => 'title',
				'type'     => 'text',
				'wrapper'  => array( 'width' => '50' ),
				'required' => 1,
				'instructions' => __( 'Renders as an <code>&lt;h1&gt;</code>.', 'classic-city-core' ),
			),
			array(
				'key'      => 'field_hero_3_up_eyebrow',
				'label'    => __( 'Eyebrow', 'classic-city-core' ),
				'name'     => 'eyebrow',
				'type'     => 'text',
				'wrapper'  => array( 'width' => '50' ),
				'instructions' => __( 'Small caps lead-in above the title.', 'classic-city-core' ),
			),
			array(
				'key'   => 'field_hero_3_up_description',
				'label' => __( 'Description', 'classic-city-core' ),
				'name'  => 'description',
				'type'  => 'textarea',
				'rows'  => 4,
			),
			array(
				'key'     => 'field_hero_3_up_button_text',
				'label'   => __( 'Button Text', 'classic-city-core' ),
				'name'    => 'button_text',
				'type'    => 'text',
				'wrapper' => array( 'width' => '50' ),
			),
			array(
				'key'     => 'field_hero_3_up_button_url',
				'label'   => __( 'Button Link', 'classic-city-core' ),
				'name'    => 'button_url',
				'type'    => 'url',
				'wrapper' => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_hero_3_up_image_aspect_ratio',
				'label'         => __( 'Image Aspect Ratio', 'classic-city-core' ),
				'name'          => 'image_aspect_ratio',
				'type'          => 'select',
				'choices'       => array(
					'none' => __( 'None (use image\'s natural aspect)', 'classic-city-core' ),
					'16/9' => '16 : 9 (landscape)',
					'3/2'  => '3 : 2 (landscape)',
					'4/3'  => '4 : 3 (landscape)',
					'1/1'  => '1 : 1 (square)',
					'4/5'  => '4 : 5 (slight portrait)',
					'3/4'  => '3 : 4 (portrait)',
				),
				'default_value' => 'none',
				'allow_null'    => 0,
				'ui'            => 1,
			),
			array(
				'key'           => 'field_hero_3_up_image',
				'label'         => __( 'Image', 'classic-city-core' ),
				'name'          => 'image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'required'      => 1,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/hero-3-up',
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
