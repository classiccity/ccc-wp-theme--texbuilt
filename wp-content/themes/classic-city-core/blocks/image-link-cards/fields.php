<?php
/**
 * ACF field group for the Image Links with Icons block.
 *
 * Block-level: eyebrow text + color, header text + color, columns.
 * Per-card (repeater): title, palette bg color, FontAwesome icon name,
 * link URL, image upload.
 *
 * Field widths on the editor side mirror the user's spec:
 *  - eyebrow_text 75% / eyebrow_color 25%
 *  - header_text  75% / header_color  25%
 *  - columns full-width below them.
 *  - Per-card: title 50% / bg_color 50%, icon_name 50% / link_url 50%,
 *    image full-width.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_image_link_cards',
		'title'                 => __( 'Image Links with Icons', 'classic-city-core' ),
		// Head fields (eyebrow + header, each with palette color dropdown
		// at 75/25 widths) come from the shared partial so any future
		// schema tweaks land in inc/block-head.php once. The block-
		// specific fields (columns + the cards repeater) merge in after.
		'fields'                => array_merge(
			ccc_block_head_fields( 'image_link_cards' ),
			array(
			array(
				'key'           => 'field_image_link_cards_columns',
				'label'         => __( 'Columns', 'classic-city-core' ),
				'name'          => 'columns',
				'type'          => 'select',
				'choices'       => array(
					1 => '1',
					2 => '2',
					3 => '3',
					4 => '4',
					5 => '5',
					6 => '6',
				),
				'default_value' => 4,
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Grid column count on desktop. Collapses on smaller viewports.', 'classic-city-core' ),
			),
			array(
				'key'          => 'field_image_link_cards_items',
				'label'        => __( 'Cards', 'classic-city-core' ),
				'name'         => 'cards',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 24,
				'layout'       => 'block',
				'button_label' => __( 'Add Card', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'      => 'field_image_link_cards_title',
						'label'    => __( 'Title', 'classic-city-core' ),
						'name'     => 'title',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '50' ),
					),
					array(
						'key'           => 'field_image_link_cards_bg_color',
						'label'         => __( 'Footer Background Color', 'classic-city-core' ),
						'name'          => 'bg_color',
						'type'          => 'select',
						'choices'       => function_exists( 'ccc_palette_slug_choices' ) ? ccc_palette_slug_choices() : array( '' => '— Default —' ),
						'default_value' => 'primary',
						'allow_null'    => 1,
						'ui'            => 1,
						'instructions'  => __( 'Applied via has-{slug}-background-color. Text + icon color auto-flip to the opposite via the parent theme&rsquo;s palette pair-helpers.', 'classic-city-core' ),
						'wrapper'       => array( 'width' => '50' ),
					),
					array(
						'key'          => 'field_image_link_cards_icon_name',
						'label'        => __( 'FontAwesome Icon Name', 'classic-city-core' ),
						'name'         => 'icon_name',
						'type'         => 'text',
						'instructions' => __( 'Just the icon name (e.g. <code>mountain</code>, <code>water</code>, <code>home</code>). The active FontAwesome style (set in theme.json under custom.icons.style) is applied automatically.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
					array(
						'key'      => 'field_image_link_cards_link_url',
						'label'    => __( 'Link URL', 'classic-city-core' ),
						'name'     => 'link_url',
						'type'     => 'url',
						'required' => 1,
						'wrapper'  => array( 'width' => '50' ),
					),
					array(
						'key'           => 'field_image_link_cards_image',
						'label'         => __( 'Image', 'classic-city-core' ),
						'name'          => 'image',
						'type'          => 'image',
						'required'      => 1,
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),
				),
			),
			) // close the inline `array(...)` of block-specific fields
		), // close `array_merge(...)`
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/image-link-cards',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
		'show_in_rest'          => 1,
	)
);
