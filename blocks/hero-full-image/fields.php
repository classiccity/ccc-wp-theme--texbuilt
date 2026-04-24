<?php
/**
 * ACF field group for the Hero: Full Image block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_hero_full_image',
		'title'                 => __( 'Hero: Full Image', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_hero_full_image',
				'label'         => __( 'Background Image', 'classic-city-core' ),
				'name'          => 'image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'required'      => 1,
			),
			array(
				'key'           => 'field_hero_full_video',
				'label'         => __( 'Background Video', 'classic-city-core' ),
				'name'          => 'video',
				'type'          => 'file',
				'return_format' => 'array',
				'mime_types'    => 'mp4,webm,mov,m4v,ogv',
				'instructions'  => __( 'Optional. If set, the video takes precedence over the image and plays muted/looped on load. The image is used as the video poster frame.', 'classic-city-core' ),
			),
			array(
				'key'          => 'field_hero_full_title_html',
				'label'        => __( 'Headline (HTML allowed)', 'classic-city-core' ),
				'name'         => 'title_html',
				'type'         => 'textarea',
				'instructions' => __( 'Wraps in an h1. Basic HTML like <span> and <em> is allowed for styling individual words.', 'classic-city-core' ),
				'rows'         => 2,
				'new_lines'    => '',
				'required'     => 1,
			),
			array(
				'key'           => 'field_hero_full_gradient_color',
				'label'         => __( 'Bottom Gradient Color', 'classic-city-core' ),
				'name'          => 'gradient_color',
				'type'          => 'select',
				'instructions'  => __( 'Color the soft fade at the bottom of the hero. Default fades to the page background.', 'classic-city-core' ),
				'choices'       => function_exists( 'ccc_palette_slug_choices' ) ? ccc_palette_slug_choices() : array( '' => '— Default —' ),
				'default_value' => '',
				'allow_null'    => 0,
				'ui'            => 1,
			),
			array(
				'key'           => 'field_hero_full_card_width',
				'label'         => __( 'Card Width', 'classic-city-core' ),
				'name'          => 'card_width',
				'type'          => 'button_group',
				'instructions'  => __( 'Width of the overlay content card. Narrow matches the theme\'s narrow size; Content matches the page content column.', 'classic-city-core' ),
				'choices'       => array(
					'narrow'  => __( 'Narrow', 'classic-city-core' ),
					'content' => __( 'Content', 'classic-city-core' ),
				),
				'default_value' => 'narrow',
				'layout'        => 'horizontal',
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/hero-full-image',
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
