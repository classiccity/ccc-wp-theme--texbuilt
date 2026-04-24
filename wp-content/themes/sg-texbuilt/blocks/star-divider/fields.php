<?php
/**
 * ACF field group for the TexBuilt Star Divider block.
 *
 * @package SgTexbuilt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_texbuilt_star_divider',
		'title'                 => __( 'Star Divider', 'sg-texbuilt' ),
		'fields'                => array(
			array(
				'key'           => 'field_star_divider_color',
				'label'         => __( 'Star Color', 'sg-texbuilt' ),
				'name'          => 'color',
				'type'          => 'select',
				'instructions'  => __( 'Pick a palette color, or leave as Default to inherit from the surrounding text color.', 'sg-texbuilt' ),
				'choices'       => function_exists( 'ccc_palette_slug_choices' ) ? ccc_palette_slug_choices() : array( '' => '— Default —' ),
				'default_value' => '',
				'allow_null'    => 0,
				'ui'            => 1,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'sg-texbuilt/star-divider',
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
