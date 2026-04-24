<?php
/**
 * ACF field group for the Hero block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_hero',
		'title'                 => __( 'Hero', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_hero_image',
				'label'         => __( 'Image', 'classic-city-core' ),
				'name'          => 'image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'required'      => 1,
			),
			array(
				'key'           => 'field_hero_image_side',
				'label'         => __( 'Image Side', 'classic-city-core' ),
				'name'          => 'image_side',
				'type'          => 'button_group',
				'instructions'  => __( 'Which side of the block the image appears on at desktop. On mobile the image is always below the content.', 'classic-city-core' ),
				'choices'       => array(
					'right' => __( 'Right', 'classic-city-core' ),
					'left'  => __( 'Left', 'classic-city-core' ),
				),
				'default_value' => 'right',
				'layout'        => 'horizontal',
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/hero',
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
