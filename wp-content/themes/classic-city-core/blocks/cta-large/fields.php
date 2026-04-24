<?php
/**
 * ACF field group for the Large CTA block.
 *
 * Background color/gradient comes from WP's native color picker
 * (supports.color.background|gradients). Only bg image + opacity are ACF fields.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_cta_large',
		'title'                 => __( 'Large CTA', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_cta_large_bg_image',
				'label'         => __( 'Background Image (optional)', 'classic-city-core' ),
				'name'          => 'bg_image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
			),
			array(
				'key'           => 'field_cta_large_bg_opacity',
				'label'         => __( 'Color Overlay Opacity', 'classic-city-core' ),
				'name'          => 'bg_opacity',
				'type'          => 'range',
				'instructions'  => __( 'Opacity of the brand color/gradient overlay on top of the background image (0–100).', 'classic-city-core' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 5,
				'default_value' => 80,
				'append'        => '%',
			),
			array(
				'key'           => 'field_cta_large_has_texture',
				'label'         => __( 'Apply Background Texture', 'classic-city-core' ),
				'name'          => 'has_texture',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 0,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/cta-large',
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
