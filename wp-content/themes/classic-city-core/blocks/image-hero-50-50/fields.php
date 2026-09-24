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
				'key'           => 'field_image_hero_image_align',
				'label'         => __( 'Image Alignment', 'classic-city-core' ),
				'name'          => 'image_align',
				'type'          => 'select',
				'instructions'  => __( 'Which part of the photograph survives the crop. `cover` discards whatever does not fit the panel; this picks which end it keeps. Center is the browser default and what every block rendered before this field existed.', 'classic-city-core' ),
				'choices'       => array(
					'top-left'      => __( 'Top Left', 'classic-city-core' ),
					'top-center'    => __( 'Top Center', 'classic-city-core' ),
					'top-right'     => __( 'Top Right', 'classic-city-core' ),
					'center-left'   => __( 'Middle Left', 'classic-city-core' ),
					'center-center' => __( 'Middle Center', 'classic-city-core' ),
					'center-right'  => __( 'Middle Right', 'classic-city-core' ),
					'bottom-left'   => __( 'Bottom Left', 'classic-city-core' ),
					'bottom-center' => __( 'Bottom Center', 'classic-city-core' ),
					'bottom-right'  => __( 'Bottom Right', 'classic-city-core' ),
				),
				'default_value' => 'center-center',
				'allow_null'    => 0,
				'ui'            => 1,
			),
			array(
				'key'               => 'field_image_hero_desktop_ratio',
				'label'             => __( 'Desktop Image Ratio', 'classic-city-core' ),
				'name'              => 'desktop_image_ratio',
				'type'              => 'select',
				'instructions'      => __( 'Shape of the media panel at desktop. "Hug text" is the historic behavior: the panel takes whatever height the text column happens to be, so a short intro paragraph produces a very shallow band. Any fixed ratio pins the panel instead and centers it beside the text.', 'classic-city-core' ),
				'choices'           => array(
					'hug'  => __( 'Hug text (default)', 'classic-city-core' ),
					'16-9' => __( '16:9', 'classic-city-core' ),
					'3-2'  => __( '3:2', 'classic-city-core' ),
					'4-3'  => __( '4:3', 'classic-city-core' ),
					'5-4'  => __( '5:4', 'classic-city-core' ),
					'1-1'  => __( '1:1 Square', 'classic-city-core' ),
				),
				'default_value'     => 'hug',
				'allow_null'        => 0,
				'ui'                => 1,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_image_hero_full_height',
							'operator' => '!=',
							'value'    => '1',
						),
					),
				),
			),
			array(
				'key'               => 'field_image_hero_content_space',
				'label'             => __( 'Content Vertical Space', 'classic-city-core' ),
				'name'              => 'content_space',
				'type'              => 'select',
				'instructions'      => __( 'Top/bottom breathing room on the text column at desktop. When Desktop Image Ratio is "Hug text" this also sets the height of the image, because the text column is what the panel measures itself against. Medium reproduces the padding every block had before this field existed.', 'classic-city-core' ),
				'choices'           => array(
					'hug'    => __( 'Hug (none)', 'classic-city-core' ),
					'small'  => __( 'Small', 'classic-city-core' ),
					'medium' => __( 'Medium (default)', 'classic-city-core' ),
					'large'  => __( 'Large', 'classic-city-core' ),
				),
				'default_value'     => 'medium',
				'allow_null'        => 0,
				'ui'                => 1,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_image_hero_full_height',
							'operator' => '!=',
							'value'    => '1',
						),
					),
				),
			),
			array(
				'key'           => 'field_image_hero_mobile_ratio',
				'label'         => __( 'Mobile Image Ratio', 'classic-city-core' ),
				'name'          => 'mobile_image_ratio',
				'type'          => 'select',
				'instructions'  => __( 'Under 768px the layout always stacks to image-above-content, so none of the desktop settings apply and this is the only thing that shapes the image. 16:9 is what every block rendered before this field existed; 5:4 suits photographs of people, which lose their heads in a 16:9 band.', 'classic-city-core' ),
				'choices'       => array(
					'16-9' => __( '16:9 (default)', 'classic-city-core' ),
					'3-2'  => __( '3:2', 'classic-city-core' ),
					'4-3'  => __( '4:3', 'classic-city-core' ),
					'5-4'  => __( '5:4', 'classic-city-core' ),
					'1-1'  => __( '1:1 Square', 'classic-city-core' ),
				),
				'default_value' => '16-9',
				'allow_null'    => 0,
				'ui'            => 1,
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
