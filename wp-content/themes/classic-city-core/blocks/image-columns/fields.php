<?php
/**
 * ACF field group for the Image Columns block.
 *
 * Card background is a block-level native picker (supports.color in block.json)
 * — solid OR gradient. One choice applies to every card body.
 *
 * Button color is a block-level ACF select — one choice applies to every
 * card's CTA. Defaults to `cta`.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_image_columns',
		'title'                 => __( 'Image Columns', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_image_columns_desktop_columns',
				'label'         => __( 'Desktop Columns', 'classic-city-core' ),
				'name'          => 'desktop_columns',
				'type'          => 'select',
				'choices'       => array( 2 => '2', 3 => '3', 4 => '4', 5 => '5' ),
				'default_value' => 3,
				'wrapper'       => array( 'width' => '33' ),
			),
			array(
				'key'           => 'field_image_columns_aspect_ratio',
				'label'         => __( 'Image Aspect Ratio', 'classic-city-core' ),
				'name'          => 'aspect_ratio',
				'type'          => 'button_group',
				'choices'       => array(
					'horizontal' => __( 'Horizontal', 'classic-city-core' ),
					'square'     => __( 'Square', 'classic-city-core' ),
					'vertical'   => __( 'Vertical', 'classic-city-core' ),
				),
				'default_value' => 'horizontal',
				'allow_null'    => 0,
				'layout'        => 'horizontal',
				'instructions'  => __( 'Applied to every card image. Horizontal = 16:9, Square = 1:1, Vertical = 3:4.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '33' ),
			),
			array(
				'key'           => 'field_image_columns_cta_color',
				'label'         => __( 'Button Color', 'classic-city-core' ),
				'name'          => 'cta_color',
				'type'          => 'select',
				'choices'       => ccc_palette_slug_choices(),
				'default_value' => 'cta',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Applied to every card\'s CTA button.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '33' ),
			),
			array(
				'key'          => 'field_image_columns_items',
				'label'        => __( 'Columns', 'classic-city-core' ),
				'name'         => 'columns',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 12,
				'layout'       => 'block',
				'button_label' => __( 'Add Column', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'           => 'field_image_columns_image',
						'label'         => __( 'Image', 'classic-city-core' ),
						'name'          => 'image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'required'      => 1,
						'wrapper'       => array( 'width' => '30' ),
					),
					array(
						'key'      => 'field_image_columns_heading',
						'label'    => __( 'Heading', 'classic-city-core' ),
						'name'     => 'heading',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '70' ),
					),
					array(
						'key'      => 'field_image_columns_body',
						'label'    => __( 'Body', 'classic-city-core' ),
						'name'     => 'body',
						'type'     => 'textarea',
						'rows'     => 3,
						'required' => 1,
					),
					array(
						'key'      => 'field_image_columns_cta_text',
						'label'    => __( 'Button Label', 'classic-city-core' ),
						'name'     => 'cta_text',
						'type'     => 'text',
						'wrapper'  => array( 'width' => '40' ),
					),
					array(
						'key'     => 'field_image_columns_cta_url',
						'label'   => __( 'Button URL', 'classic-city-core' ),
						'name'    => 'cta_url',
						'type'    => 'url',
						'wrapper' => array( 'width' => '60' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/image-columns',
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
