<?php
/**
 * ACF field group for the Logo Strip block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_logo_strip',
		'title'                 => __( 'Logo Strip', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'   => 'field_logo_strip_eyebrow',
				'label' => __( 'Eyebrow Text (optional)', 'classic-city-core' ),
				'name'  => 'eyebrow',
				'type'  => 'text',
			),
			array(
				'key'          => 'field_logo_strip_items',
				'label'        => __( 'Logos', 'classic-city-core' ),
				'name'         => 'logos',
				'type'         => 'repeater',
				'min'          => 2,
				'max'          => 20,
				'layout'       => 'table',
				'button_label' => __( 'Add Logo', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'           => 'field_logo_strip_image',
						'label'         => __( 'Logo', 'classic-city-core' ),
						'name'          => 'logo_image',
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
					'value'    => 'classic-city-core/logo-strip',
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
