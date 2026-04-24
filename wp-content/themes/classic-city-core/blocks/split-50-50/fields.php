<?php
/**
 * ACF field group for the Split 50/50 block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_split_50_50',
		'title'                 => __( 'Split 50/50', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_split_image',
				'label'         => __( 'Image', 'classic-city-core' ),
				'name'          => 'image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'required'      => 1,
			),
			array(
				'key'           => 'field_split_video',
				'label'         => __( 'Video', 'classic-city-core' ),
				'name'          => 'video',
				'type'          => 'file',
				'return_format' => 'array',
				'mime_types'    => 'mp4,webm,mov,m4v,ogv',
				'instructions'  => __( 'Optional. If set, the video takes precedence over the image and plays muted/looped on load. The image is used as the video poster frame.', 'classic-city-core' ),
			),
			array(
				'key'           => 'field_split_side',
				'label'         => __( 'Media Side', 'classic-city-core' ),
				'name'          => 'image_side',
				'type'          => 'button_group',
				'instructions'  => __( 'Which side the image or video appears on at desktop.', 'classic-city-core' ),
				'choices'       => array(
					'left'  => __( 'Left', 'classic-city-core' ),
					'right' => __( 'Right', 'classic-city-core' ),
				),
				'default_value' => 'left',
				'layout'        => 'horizontal',
			),
			array(
				'key'           => 'field_split_has_texture',
				'label'         => __( 'Apply Background Texture', 'classic-city-core' ),
				'name'          => 'has_texture',
				'type'          => 'true_false',
				'instructions'  => __( 'Overlay the subtle line/dot texture on the background.', 'classic-city-core' ),
				'ui'            => 1,
				'default_value' => 0,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/split-50-50',
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
