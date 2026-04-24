<?php
/**
 * ACF field group for the Image Hero 50/50 block.
 *
 * Background color/gradient is NOT an ACF field — it comes from WP's native
 * color picker via `supports.color.background|gradients` in block.json.
 * render.php reads $attributes['backgroundColor'] / $attributes['gradient']
 * and composes the class onto `.sg-block-hero-bg-media` (not the block root).
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_image_hero_50_50',
		'title'                 => __( 'Image Hero 50/50', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_image_hero_image',
				'label'         => __( 'Image', 'classic-city-core' ),
				'name'          => 'image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'required'      => 1,
			),
			array(
				'key'           => 'field_image_hero_video',
				'label'         => __( 'Video', 'classic-city-core' ),
				'name'          => 'video',
				'type'          => 'file',
				'return_format' => 'array',
				'mime_types'    => 'mp4,webm,mov,m4v,ogv',
				'instructions'  => __( 'Optional. If set, the video takes precedence over the image and plays muted/looped on load. The image is used as the video poster frame.', 'classic-city-core' ),
			),
			array(
				'key'           => 'field_image_hero_image_side',
				'label'         => __( 'Media Side', 'classic-city-core' ),
				'name'          => 'image_side',
				'type'          => 'button_group',
				'instructions'  => __( 'Which side the image or video appears on at desktop. On mobile the media always renders above the content at 16:9.', 'classic-city-core' ),
				'choices'       => array(
					'left'  => __( 'Left', 'classic-city-core' ),
					'right' => __( 'Right', 'classic-city-core' ),
				),
				'default_value' => 'left',
				'layout'        => 'horizontal',
			),
			array(
				'key'           => 'field_image_hero_media_spacing',
				'label'         => __( 'Media Spacing', 'classic-city-core' ),
				'name'          => 'media_spacing',
				'type'          => 'true_false',
				'instructions'  => __( 'On: the media sits inside a color panel with padding and a drop shadow. Off: media fills its half edge-to-edge with no shadow.', 'classic-city-core' ),
				'ui'            => 1,
				'default_value' => 1,
			),
			array(
				'key'           => 'field_image_hero_full_height',
				'label'         => __( 'Full Viewport Height', 'classic-city-core' ),
				'name'          => 'full_height',
				'type'          => 'true_false',
				'instructions'  => __( 'On: block fills 100vh (media section becomes roughly square at most widths). Off: block sizes to its content and the content section gets a modest top/bottom pad.', 'classic-city-core' ),
				'ui'            => 1,
				'default_value' => 1,
			),
			array(
				'key'           => 'field_image_hero_has_texture',
				'label'         => __( 'Apply Background Texture', 'classic-city-core' ),
				'name'          => 'has_texture',
				'type'          => 'true_false',
				'instructions'  => __( 'Overlay the subtle line/dot texture on top of the color/gradient panel.', 'classic-city-core' ),
				'ui'            => 1,
				'default_value' => 0,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/image-hero-50-50',
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
