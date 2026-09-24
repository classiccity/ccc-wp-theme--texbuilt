<?php
/**
 * ACF field group for the Feature Showcase block.
 *
 * Top panel background is set via WP's native color picker (supports.color in
 * block.json) — the slug is applied to the inner `.sg-block-feature-showcase__top`
 * element only, not the block root, so the icon pods below stay transparent.
 *
 * Bottom pod button color is a block-level ACF select — one slug applies to
 * every pod's outline button.
 *
 * Icon convention matches Feature Grid / Icon Feature Row: admins enter the
 * bare FA name (e.g. `fa-bolt`); the style is resolved site-wide from
 * settings.custom.icons.style in theme.json.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_feature_showcase',
		'title'                 => __( 'Feature Showcase', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'      => 'field_feature_showcase_top_title',
				'label'    => __( 'Title', 'classic-city-core' ),
				'name'     => 'top_title',
				'type'     => 'text',
				'required' => 1,
			),
			array(
				'key'          => 'field_feature_showcase_top_description',
				'label'        => __( 'Description', 'classic-city-core' ),
				'name'         => 'top_description',
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
			),
			array(
				'key'           => 'field_feature_showcase_top_image',
				'label'         => __( 'Image', 'classic-city-core' ),
				'name'          => 'top_image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'instructions'  => __( 'Sits beneath the title/description, flush to the bottom of the colored panel.', 'classic-city-core' ),
			),
			array(
				'key'           => 'field_feature_showcase_pod_columns',
				'label'         => __( 'Pod Columns', 'classic-city-core' ),
				'name'          => 'pod_columns',
				'type'          => 'select',
				'choices'       => array( 2 => '2', 3 => '3', 4 => '4' ),
				'default_value' => 3,
				'allow_null'    => 0,
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_feature_showcase_pod_button_color',
				'label'         => __( 'Pod Button Color', 'classic-city-core' ),
				'name'          => 'pod_button_color',
				'type'          => 'select',
				'choices'       => function_exists( 'ccc_palette_slug_choices' ) ? ccc_palette_slug_choices() : array( '' => '— Default —' ),
				'default_value' => 'primary',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Outlined button — slug drives the border + text color on every pod\'s button.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'          => 'field_feature_showcase_pods',
				'label'        => __( 'Pods', 'classic-city-core' ),
				'name'         => 'pods',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 8,
				'layout'       => 'block',
				'button_label' => __( 'Add Pod', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'          => 'field_feature_showcase_pod_icon',
						'label'        => __( 'Icon Name', 'classic-city-core' ),
						'name'         => 'icon_name',
						'type'         => 'text',
						'instructions' => __( 'FontAwesome icon name, e.g. "fa-bolt". Style is set site-wide; just enter the bare name.', 'classic-city-core' ),
						'placeholder'  => 'fa-bolt',
						'required'     => 1,
						'wrapper'      => array( 'width' => '25' ),
					),
					array(
						'key'      => 'field_feature_showcase_pod_title',
						'label'    => __( 'Title', 'classic-city-core' ),
						'name'     => 'title',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '75' ),
					),
					array(
						'key'      => 'field_feature_showcase_pod_body',
						'label'    => __( 'Description', 'classic-city-core' ),
						'name'     => 'body',
						'type'     => 'textarea',
						'rows'     => 3,
						'required' => 1,
					),
					array(
						'key'     => 'field_feature_showcase_pod_button_text',
						'label'   => __( 'Button Text', 'classic-city-core' ),
						'name'    => 'button_text',
						'type'    => 'text',
						'wrapper' => array( 'width' => '40' ),
					),
					array(
						'key'     => 'field_feature_showcase_pod_button_url',
						'label'   => __( 'Button URL', 'classic-city-core' ),
						'name'    => 'button_url',
						'type'    => 'url',
						'wrapper' => array( 'width' => '60' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/feature-showcase',
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
