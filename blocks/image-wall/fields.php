<?php
/**
 * ACF field group for the Image Wall block.
 *
 * Images are split between the two rows by index: even indices go to the top
 * row, odd go to the bottom. Admins can reorder via drag.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_image_wall',
		'title'                 => __( 'Image Wall', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'          => 'field_image_wall_images',
				'label'        => __( 'Images', 'classic-city-core' ),
				'name'         => 'images',
				'type'         => 'repeater',
				'min'          => 4,
				'max'          => 24,
				'layout'       => 'table',
				'instructions' => __( 'Images alternate between the two scrolling rows. First → top row, second → bottom row, third → top, etc.', 'classic-city-core' ),
				'button_label' => __( 'Add Image', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'           => 'field_image_wall_image',
						'label'         => __( 'Image', 'classic-city-core' ),
						'name'          => 'image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'thumbnail',
						'required'      => 1,
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/image-wall',
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
