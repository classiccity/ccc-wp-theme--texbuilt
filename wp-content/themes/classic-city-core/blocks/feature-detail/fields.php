<?php
/**
 * ACF field group for the Feature Detail block.
 *
 * Fresh field-key namespace (`field_feature_detail_*`) — no inheritance
 * from product-feature-toggles. The two blocks share render output via
 * partials/feature-detail-section.php; field storage stays independent.
 *
 * Field admin layout (50/50 rows where it makes sense):
 *   Image (50%)              | Detail Accent Color (50%)
 *   Image Subtitle (50%)     | Image Title (50%)
 *   Headline (full)
 *   Description (full)
 *   Button Text (50%)        | Button URL (50%)
 *   Detail Rows (repeater, full)
 *
 * No block-head fields (eyebrow/heading-above-the-panel) — per Chris's
 * spec, authors wrap the block in a wp:group with their own section
 * header if they want one. Keeps the block focused on one job.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_feature_detail',
		'title'                 => __( 'Feature Detail', 'classic-city-core' ),
		'fields'                => array(

			/* ─── Image side ─────────────────────────────────────── */
			array(
				'key'           => 'field_feature_detail_image',
				'label'         => __( 'Image', 'classic-city-core' ),
				'name'          => 'image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'wrapper'       => array( 'width' => '50' ),
				'required'      => 1,
			),
			array(
				'key'           => 'field_feature_detail_detail_color',
				'label'         => __( 'Detail Row Accent Color', 'classic-city-core' ),
				'name'          => 'detail_color',
				'type'          => 'select',
				'choices'       => function_exists( 'ccc_palette_slug_choices' )
					? ccc_palette_slug_choices()
					: array( '' => '— Default —' ),
				'default_value' => 'secondary',
				'allow_null'    => 0,
				'ui'            => 1,
				'wrapper'       => array( 'width' => '50' ),
				'instructions'  => __( 'Palette slug used for the colored left bar AND icon tint on every detail row in this block.', 'classic-city-core' ),
			),
			array(
				'key'          => 'field_feature_detail_image_subtitle',
				'label'        => __( 'Image Caption Subtitle', 'classic-city-core' ),
				'name'         => 'image_subtitle',
				'type'         => 'text',
				'wrapper'      => array( 'width' => '50' ),
				'instructions' => __( 'Small caps text above the caption title in the floating card (e.g. &ldquo;FOR WATERFRONT&rdquo;).', 'classic-city-core' ),
			),
			array(
				'key'          => 'field_feature_detail_image_title',
				'label'        => __( 'Image Caption Title', 'classic-city-core' ),
				'name'         => 'image_title',
				'type'         => 'text',
				'wrapper'      => array( 'width' => '50' ),
				'instructions' => __( 'Bold heading in the floating caption card (e.g. &ldquo;Coastal Collection&rdquo;).', 'classic-city-core' ),
			),

			/* ─── Content side ───────────────────────────────────── */
			array(
				'key'   => 'field_feature_detail_headline',
				'label' => __( 'Headline', 'classic-city-core' ),
				'name'  => 'headline',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_feature_detail_description',
				'label' => __( 'Description', 'classic-city-core' ),
				'name'  => 'description',
				'type'  => 'textarea',
				'rows'  => 3,
			),
			array(
				'key'           => 'field_feature_detail_button_text',
				'label'         => __( 'Button Text', 'classic-city-core' ),
				'name'          => 'button_text',
				'type'          => 'text',
				'default_value' => '',
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'     => 'field_feature_detail_button_url',
				'label'   => __( 'Button URL', 'classic-city-core' ),
				'name'    => 'button_url',
				'type'    => 'url',
				'wrapper' => array( 'width' => '50' ),
			),

			/* ─── Detail rows repeater ───────────────────────────── */
			array(
				'key'          => 'field_feature_detail_rows',
				'label'        => __( 'Detail Rows', 'classic-city-core' ),
				'name'         => 'detail_rows',
				'type'         => 'repeater',
				'min'          => 0,
				'max'          => 8,
				'layout'       => 'block',
				'button_label' => __( 'Add Detail Row', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'      => 'field_feature_detail_row_label',
						'label'    => __( 'Label', 'classic-city-core' ),
						'name'     => 'label',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '50' ),
					),
					array(
						'key'          => 'field_feature_detail_row_icon_name',
						'label'        => __( 'Icon', 'classic-city-core' ),
						'name'         => 'icon_name',
						'type'         => 'text',
						'instructions' => __( 'FontAwesome icon name only (e.g. <code>droplet</code>, <code>sun</code>).', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
					array(
						'key'   => 'field_feature_detail_row_description',
						'label' => __( 'Description', 'classic-city-core' ),
						'name'  => 'description',
						'type'  => 'textarea',
						'rows'  => 2,
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/feature-detail',
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
