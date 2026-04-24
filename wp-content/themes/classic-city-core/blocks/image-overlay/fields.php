<?php
/**
 * ACF field group for the Image + Content Overlay block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_image_overlay',
		'title'                 => __( 'Image + Content Overlay', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_overlay_image',
				'label'         => __( 'Image', 'classic-city-core' ),
				'name'          => 'image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'large',
				'required'      => 1,
			),
			array(
				'key'           => 'field_overlay_content_side',
				'label'         => __( 'Content Side', 'classic-city-core' ),
				'name'          => 'content_side',
				'type'          => 'button_group',
				'instructions'  => __( 'Which side the overlay content card sits on.', 'classic-city-core' ),
				'choices'       => array(
					'right' => __( 'Right', 'classic-city-core' ),
					'left'  => __( 'Left', 'classic-city-core' ),
				),
				'default_value' => 'right',
				'layout'        => 'horizontal',
			),
			array(
				'key'           => 'field_overlay_texture',
				'label'         => __( 'Card Texture', 'classic-city-core' ),
				'name'          => 'card_texture',
				'type'          => 'select',
				'instructions'  => __( 'Optional background texture applied to the content card.', 'classic-city-core' ),
				'choices'       => array(
					''                    => __( 'None', 'classic-city-core' ),
					'has-bg-texture'      => __( 'Line Texture', 'classic-city-core' ),
					'has-bg-texture-sand' => __( 'Sand Texture', 'classic-city-core' ),
				),
				'default_value' => '',
				'allow_null'    => 0,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/image-overlay',
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
