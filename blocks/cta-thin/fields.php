<?php
/**
 * ACF field group for the Thin CTA block.
 *
 * Thin CTA is fully field-driven (no InnerBlocks). The horizontal layout
 * depends on having exactly two siblings — a copy column and a button —
 * which InnerBlocks can't guarantee. Keeping it simple: headline + subtext
 * + button label + button URL.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_cta_thin',
		'title'                 => __( 'Thin CTA', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'      => 'field_cta_thin_headline',
				'label'    => __( 'Headline', 'classic-city-core' ),
				'name'     => 'headline',
				'type'     => 'text',
				'required' => 1,
				'wrapper'  => array( 'width' => '70' ),
			),
			array(
				'key'           => 'field_cta_thin_headline_level',
				'label'         => __( 'Headline Tag', 'classic-city-core' ),
				'name'          => 'headline_level',
				'type'          => 'select',
				'instructions'  => __( 'Semantic tag for the headline. Bold Paragraph keeps the existing visual treatment (large + bold).', 'classic-city-core' ),
				'choices'       => array(
					'p'  => __( 'Bold Paragraph', 'classic-city-core' ),
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				),
				'default_value' => 'h3',
				'allow_null'    => 0,
				'ui'            => 1,
				'wrapper'       => array( 'width' => '30' ),
			),
			array(
				'key'   => 'field_cta_thin_subtext',
				'label' => __( 'Subtext (optional)', 'classic-city-core' ),
				'name'  => 'subtext',
				'type'  => 'text',
			),
			array(
				'key'      => 'field_cta_thin_button_label',
				'label'    => __( 'Button Label', 'classic-city-core' ),
				'name'     => 'button_label',
				'type'     => 'text',
				'required' => 1,
				'wrapper'  => array( 'width' => '40' ),
			),
			array(
				'key'      => 'field_cta_thin_button_url',
				'label'    => __( 'Button URL', 'classic-city-core' ),
				'name'     => 'button_url',
				'type'     => 'url',
				'required' => 1,
				'wrapper'  => array( 'width' => '60' ),
			),
			array(
				'key'           => 'field_cta_thin_bg_image',
				'label'         => __( 'Background Image (optional)', 'classic-city-core' ),
				'name'          => 'bg_image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
			),
			array(
				'key'           => 'field_cta_thin_bg_opacity',
				'label'         => __( 'Color Overlay Opacity', 'classic-city-core' ),
				'name'          => 'bg_opacity',
				'type'          => 'range',
				'instructions'  => __( 'Opacity of the brand color/gradient overlay on top of the background image (0–100).', 'classic-city-core' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 5,
				'default_value' => 80,
				'append'        => '%',
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_cta_thin_has_texture',
				'label'         => __( 'Apply Background Texture', 'classic-city-core' ),
				'name'          => 'has_texture',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 0,
				'wrapper'       => array( 'width' => '50' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/cta-thin',
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
