<?php
/**
 * ACF field group for the Product Feature Toggles block.
 *
 * Structure:
 *   - Block-level: eyebrow text + color, header text + color.
 *   - Main repeater: each row is a toggle/tab.
 *       - Icon row: label + FA icon name.
 *       - Left column: image, image subtitle, image title.
 *       - Right column: headline, description, button text/url,
 *         per-toggle detail color (palette slug — drives the colored
 *         left bar + icon tint on each detail row), and a NESTED
 *         repeater of detail rows.
 *   - Detail repeater (nested): label + description + FA icon name.
 *
 * Field-width strategy mirrors the spec from the user mockup:
 *   - Title-style fields (eyebrow, header, labels) sit at 75% with their
 *     paired color/icon dropdown at 25% on the right.
 *   - Full-width fields stand alone.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_product_feature_toggles',
		'title'                 => __( 'Product Feature Toggles', 'classic-city-core' ),
		/* Head fields (eyebrow + header, palette color dropdowns at 75/25)
		   come from the shared partial. Block-specific fields — the main
		   toggles repeater with its nested detail repeater — merge in
		   after. See inc/block-head.php. */
		'fields'                => array_merge(
			ccc_block_head_fields( 'product_feature_toggles' ),
			array(

			/* ─── Main repeater: toggles ─── */
			array(
				'key'          => 'field_product_feature_toggles_items',
				'label'        => __( 'Toggles', 'classic-city-core' ),
				'name'         => 'toggles',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 8,
				'layout'       => 'block',
				'button_label' => __( 'Add Toggle', 'classic-city-core' ),
				'sub_fields'   => array(
					/* Icon row */
					array(
						'key'      => 'field_product_feature_toggles_label',
						'label'    => __( 'Toggle Label', 'classic-city-core' ),
						'name'     => 'label',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '75' ),
					),
					array(
						'key'         => 'field_product_feature_toggles_icon_name',
						'label'       => __( 'Toggle Icon', 'classic-city-core' ),
						'name'        => 'icon_name',
						'type'        => 'text',
						'instructions' => __( 'FontAwesome icon name only (e.g. <code>house</code>, <code>mountain</code>). The site&rsquo;s active FA style is applied automatically.', 'classic-city-core' ),
						'wrapper'     => array( 'width' => '25' ),
					),

					/* Left column */
					array(
						'key'           => 'field_product_feature_toggles_left_image',
						'label'         => __( 'Image', 'classic-city-core' ),
						'name'          => 'left_image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),
					array(
						'key'     => 'field_product_feature_toggles_left_image_subtitle',
						'label'   => __( 'Image Subtitle', 'classic-city-core' ),
						'name'    => 'left_image_subtitle',
						'type'    => 'text',
						'instructions' => __( 'Small caps text above the image title in the floating caption card (e.g. &ldquo;FOR SUBURBAN HOMES&rdquo;).', 'classic-city-core' ),
					),
					array(
						'key'     => 'field_product_feature_toggles_left_image_title',
						'label'   => __( 'Image Title', 'classic-city-core' ),
						'name'    => 'left_image_title',
						'type'    => 'text',
						'instructions' => __( 'Bold heading in the floating caption card (e.g. &ldquo;Genesis Collection&rdquo;).', 'classic-city-core' ),
					),

					/* Right column */
					array(
						'key'     => 'field_product_feature_toggles_right_headline',
						'label'   => __( 'Headline', 'classic-city-core' ),
						'name'    => 'right_headline',
						'type'    => 'text',
					),
					array(
						'key'     => 'field_product_feature_toggles_right_description',
						'label'   => __( 'Description', 'classic-city-core' ),
						'name'    => 'right_description',
						'type'    => 'textarea',
						'rows'    => 3,
					),
					array(
						'key'     => 'field_product_feature_toggles_right_button_text',
						'label'   => __( 'Button Text', 'classic-city-core' ),
						'name'    => 'right_button_text',
						'type'    => 'text',
						'default_value' => 'View Product',
						'wrapper' => array( 'width' => '50' ),
					),
					array(
						'key'     => 'field_product_feature_toggles_right_button_url',
						'label'   => __( 'Button URL', 'classic-city-core' ),
						'name'    => 'right_button_url',
						'type'    => 'url',
						'wrapper' => array( 'width' => '50' ),
					),
					array(
						'key'           => 'field_product_feature_toggles_right_detail_color',
						'label'         => __( 'Detail Row Accent Color', 'classic-city-core' ),
						'name'          => 'right_detail_color',
						'type'          => 'select',
						'choices'       => function_exists( 'ccc_palette_slug_choices' ) ? ccc_palette_slug_choices() : array( '' => '— Default —' ),
						'default_value' => 'secondary',
						'allow_null'    => 0,
						'ui'            => 1,
						'instructions'  => __( 'Palette slug used for the colored left bar AND icon tint on every detail row inside this toggle.', 'classic-city-core' ),
					),

					/* ─── Nested repeater: detail rows ─── */
					array(
						'key'          => 'field_product_feature_toggles_right_details',
						'label'        => __( 'Detail Rows', 'classic-city-core' ),
						'name'         => 'right_details',
						'type'         => 'repeater',
						'min'          => 0,
						'max'          => 8,
						'layout'       => 'block',
						'button_label' => __( 'Add Detail Row', 'classic-city-core' ),
						'sub_fields'   => array(
							array(
								'key'      => 'field_product_feature_toggles_detail_label',
								'label'    => __( 'Label', 'classic-city-core' ),
								'name'     => 'label',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => array( 'width' => '50' ),
							),
							array(
								'key'         => 'field_product_feature_toggles_detail_icon_name',
								'label'       => __( 'Icon', 'classic-city-core' ),
								'name'        => 'icon_name',
								'type'        => 'text',
								'instructions' => __( 'FontAwesome icon name only.', 'classic-city-core' ),
								'wrapper'     => array( 'width' => '50' ),
							),
							array(
								'key'   => 'field_product_feature_toggles_detail_description',
								'label' => __( 'Description', 'classic-city-core' ),
								'name'  => 'description',
								'type'  => 'textarea',
								'rows'  => 2,
							),
						),
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
					'value'    => 'classic-city-core/product-feature-toggles',
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
