<?php
/**
 * ACF field group for the Image Portfolio Gallery block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_portfolio_gallery',
		'title'                 => __( 'Image Portfolio Gallery', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'          => 'field_portfolio_items',
				'label'        => __( 'Portfolio Items', 'classic-city-core' ),
				'name'         => 'items',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 48,
				'layout'       => 'block',
				'button_label' => __( 'Add Item', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'           => 'field_portfolio_image',
						'label'         => __( 'Image', 'classic-city-core' ),
						'name'          => 'image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'required'      => 1,
						'wrapper'       => array( 'width' => '30' ),
					),
					array(
						'key'      => 'field_portfolio_title',
						'label'    => __( 'Title', 'classic-city-core' ),
						'name'     => 'title',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '35' ),
					),
					array(
						'key'     => 'field_portfolio_caption',
						'label'   => __( 'Caption', 'classic-city-core' ),
						'name'    => 'caption',
						'type'    => 'textarea',
						'rows'    => 2,
						'wrapper' => array( 'width' => '35' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/portfolio-gallery',
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
