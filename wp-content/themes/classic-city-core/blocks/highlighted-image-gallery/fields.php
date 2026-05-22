<?php
/**
 * ACF field group for the Highlighted Image Gallery block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_highlighted_image_gallery',
		'title'                 => __( 'Highlighted Image Gallery', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_highlighted_image_gallery_images',
				'label'         => __( 'Images', 'classic-city-core' ),
				'name'          => 'images',
				'type'          => 'gallery',
				'instructions'  => __( 'Upload as many images as you want. The first image renders as the large primary; the rest render as a thumbnail row. Drag to reorder.', 'classic-city-core' ),
				'required'      => 1,
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'insert'        => 'append',
				'library'       => 'all',
				'min'           => 1,
			),
			array(
				'key'           => 'field_highlighted_image_gallery_columns',
				'label'         => __( 'Thumbnail Columns', 'classic-city-core' ),
				'name'          => 'columns',
				'type'          => 'number',
				'instructions'  => __( 'Number of thumbnails per row. Default 5.', 'classic-city-core' ),
				'default_value' => 5,
				'min'           => 2,
				'max'           => 10,
				'step'          => 1,
				'append'        => __( 'cols', 'classic-city-core' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/highlighted-image-gallery',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
	)
);
