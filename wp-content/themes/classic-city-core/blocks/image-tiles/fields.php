<?php
/**
 * ACF field group for the Image Tiles block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_image_tiles',
		'title'                 => __( 'Image Tiles', 'classic-city-core' ),
		'fields'                => array(
			// Layout controls — rendered in one row via wrapper.width=33/33/34.
			array(
				'key'           => 'field_image_tiles_desktop_columns',
				'label'         => __( 'Desktop Columns', 'classic-city-core' ),
				'name'          => 'desktop_columns',
				'type'          => 'select',
				'choices'       => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'default_value' => '4',
				'allow_null'    => 0,
				'ui'            => 1,
				'wrapper'       => array( 'width' => '33' ),
			),
			array(
				'key'           => 'field_image_tiles_desktop_carousel',
				'label'         => __( 'Desktop Carousel', 'classic-city-core' ),
				'name'          => 'desktop_carousel',
				'type'          => 'true_false',
				'instructions'  => __( 'On mobile the tiles scroll-snap horizontally. Enable this to keep that carousel on desktop too.', 'classic-city-core' ),
				'ui'            => 1,
				'default_value' => 0,
				'wrapper'       => array( 'width' => '33' ),
			),
			array(
				'key'           => 'field_image_tiles_aspect',
				'label'         => __( 'Tile Aspect Ratio', 'classic-city-core' ),
				'name'          => 'aspect_ratio',
				'type'          => 'button_group',
				'choices'       => array(
					'vertical'   => __( 'Vertical', 'classic-city-core' ),
					'horizontal' => __( 'Horizontal', 'classic-city-core' ),
				),
				'default_value' => 'vertical',
				'layout'        => 'horizontal',
				'wrapper'       => array( 'width' => '34' ),
			),
			array(
				'key'          => 'field_image_tiles_items',
				'label'        => __( 'Tiles', 'classic-city-core' ),
				'name'         => 'tiles',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 12,
				'layout'       => 'block',
				'button_label' => __( 'Add Tile', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'           => 'field_image_tiles_image',
						'label'         => __( 'Image', 'classic-city-core' ),
						'name'          => 'image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'required'      => 1,
						'wrapper'       => array( 'width' => '30' ),
					),
					array(
						'key'      => 'field_image_tiles_blurb',
						'label'    => __( 'Blurb', 'classic-city-core' ),
						'name'     => 'blurb',
						'type'     => 'textarea',
						'rows'     => 2,
						'required' => 1,
						'wrapper'  => array( 'width' => '40' ),
					),
					array(
						'key'     => 'field_image_tiles_link',
						'label'   => __( 'Link URL', 'classic-city-core' ),
						'name'    => 'link_url',
						'type'    => 'url',
						'wrapper' => array( 'width' => '30' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/image-tiles',
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
