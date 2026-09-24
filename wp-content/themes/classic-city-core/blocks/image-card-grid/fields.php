<?php
/**
 * ACF field group for the Image Card Grid block.
 *
 * Block-level: column_count (1-4), mobile_layout (stack / scroll),
 * image_aspect_ratio (dropdown of common ratios).
 *
 * Per-card sub-fields map to what the image-card partial supports —
 * title, tag, image, description, optional footer_title +
 * footer_subtitle (e.g., a person's name + location for testimonial
 * cards), and button text/url.
 *
 * Field widths per the user spec:
 *  - title 40% / tag 40% / image 20% (image is just an upload chip)
 *  - description 100%
 *  - footer_title 50% / footer_subtitle 50%
 *  - button_text 50% / button_link 50%
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_image_card_grid',
		'title'                 => __( 'Image Card Grid', 'classic-city-core' ),
		'fields'                => array(
			/* Block-level layout controls */
			array(
				'key'           => 'field_image_card_grid_column_count',
				'label'         => __( 'Column Count', 'classic-city-core' ),
				'name'          => 'column_count',
				'type'          => 'select',
				'choices'       => array(
					1 => '1',
					2 => '2',
					3 => '3',
					4 => '4',
				),
				'default_value' => 3,
				'allow_null'    => 0,
				'ui'            => 1,
				'wrapper'       => array( 'width' => '34' ),
			),
			array(
				'key'           => 'field_image_card_grid_mobile_layout',
				'label'         => __( 'Mobile Layout', 'classic-city-core' ),
				'name'          => 'mobile_layout',
				'type'          => 'select',
				'choices'       => array(
					'stack'  => __( 'Stack', 'classic-city-core' ),
					'scroll' => __( 'Horizontal Scroll', 'classic-city-core' ),
				),
				'default_value' => 'stack',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'How the grid behaves on viewports below the tablet breakpoint.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '33' ),
			),
			array(
				'key'           => 'field_image_card_grid_image_aspect_ratio',
				'label'         => __( 'Image Aspect Ratio', 'classic-city-core' ),
				'name'          => 'image_aspect_ratio',
				'type'          => 'select',
				'choices'       => array(
					'16/9' => '16 : 9 (landscape)',
					'3/2'  => '3 : 2 (landscape)',
					'4/3'  => '4 : 3 (landscape)',
					'1/1'  => '1 : 1 (square)',
					'4/5'  => '4 : 5 (slight portrait)',
					'3/4'  => '3 : 4 (portrait)',
				),
				'default_value' => '16/9',
				'allow_null'    => 0,
				'ui'            => 1,
				'wrapper'       => array( 'width' => '33' ),
			),
			array(
				'key'           => 'field_image_card_grid_card_bg_color',
				'label'         => __( 'Card Background', 'classic-city-core' ),
				'name'          => 'card_bg_color',
				'type'          => 'select',
				'choices'       => function_exists( 'ccc_palette_slug_choices' )
					? ccc_palette_slug_choices()
					: array( 'panel' => 'panel', 'canvas' => 'canvas' ),
				'default_value' => 'panel',
				'allow_null'    => 1,
				'ui'            => 1,
				'instructions'  => __( 'Palette slug applied to every card via <code>has-{slug}-background-color</code>. Empty = no background helper class (card stays transparent).', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '100' ),
			),

			/* Cards repeater */
			array(
				'key'          => 'field_image_card_grid_cards',
				'label'        => __( 'Cards', 'classic-city-core' ),
				'name'         => 'cards',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 24,
				'layout'       => 'block',
				'button_label' => __( 'Add Card', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'     => 'field_image_card_grid_card_title',
						'label'   => __( 'Title', 'classic-city-core' ),
						'name'    => 'title',
						'type'    => 'text',
						'wrapper' => array( 'width' => '40' ),
					),
					array(
						'key'         => 'field_image_card_grid_card_tag',
						'label'       => __( 'Tag', 'classic-city-core' ),
						'name'        => 'tag',
						'type'        => 'text',
						'instructions' => __( 'Small caps eyebrow above the title (e.g. &ldquo;Coastal Collection&rdquo;).', 'classic-city-core' ),
						'wrapper'     => array( 'width' => '40' ),
					),
					array(
						'key'           => 'field_image_card_grid_card_image',
						'label'         => __( 'Image', 'classic-city-core' ),
						'name'          => 'image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'wrapper'       => array( 'width' => '20' ),
					),
					array(
						'key'     => 'field_image_card_grid_card_description',
						'label'   => __( 'Description', 'classic-city-core' ),
						'name'    => 'description',
						'type'    => 'textarea',
						'rows'    => 3,
					),
					array(
						'key'          => 'field_image_card_grid_card_footer_title',
						'label'        => __( 'Footer Title', 'classic-city-core' ),
						'name'         => 'footer_title',
						'type'         => 'text',
						'instructions' => __( 'Optional footer line (e.g., a person&rsquo;s name). Renders below the description, above the CTA button.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
					array(
						'key'          => 'field_image_card_grid_card_footer_subtitle',
						'label'        => __( 'Footer Subtitle', 'classic-city-core' ),
						'name'         => 'footer_subtitle',
						'type'         => 'text',
						'instructions' => __( 'Optional second footer line (e.g., a location or company).', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
					array(
						'key'     => 'field_image_card_grid_card_button_text',
						'label'   => __( 'Button Text', 'classic-city-core' ),
						'name'    => 'button_text',
						'type'    => 'text',
						'wrapper' => array( 'width' => '50' ),
					),
					array(
						'key'     => 'field_image_card_grid_card_button_link',
						'label'   => __( 'Button Link', 'classic-city-core' ),
						'name'    => 'button_link',
						'type'    => 'url',
						'wrapper' => array( 'width' => '50' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/image-card-grid',
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
