<?php
/**
 * ACF field group for the Link Pods block.
 *
 * Block-level: column count.
 * Per-pod (repeater): title, title tag, description (WYSIWYG), link text + URL,
 * optional background image, palette background color.
 *
 * The entire pod is rendered as an <a> in render.php so the whole card is
 * clickable. Background color uses the standard `has-{slug}-background-color`
 * helper class so opposite-text-color is automatic.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_link_pods',
		'title'                 => __( 'Link Pods', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_link_pods_columns',
				'label'         => __( 'Column Count', 'classic-city-core' ),
				'name'          => 'columns',
				'type'          => 'select',
				'choices'       => array( 1 => '1', 2 => '2', 3 => '3', 4 => '4' ),
				'default_value' => 3,
				'allow_null'    => 0,
				'ui'            => 1,
			),
			array(
				'key'          => 'field_link_pods_items',
				'label'        => __( 'Pods', 'classic-city-core' ),
				'name'         => 'pods',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 24,
				'layout'       => 'block',
				'button_label' => __( 'Add Pod', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'      => 'field_link_pods_title',
						'label'    => __( 'Title', 'classic-city-core' ),
						'name'     => 'title',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '70' ),
					),
					array(
						'key'           => 'field_link_pods_title_tag',
						'label'         => __( 'Title Tag', 'classic-city-core' ),
						'name'          => 'title_tag',
						'type'          => 'select',
						'choices'       => array(
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
						'key'          => 'field_link_pods_description',
						'label'        => __( 'Description', 'classic-city-core' ),
						'name'         => 'description',
						'type'         => 'wysiwyg',
						'tabs'         => 'visual',
						'toolbar'      => 'basic',
						'media_upload' => 0,
					),
					array(
						'key'      => 'field_link_pods_link_text',
						'label'    => __( 'Link Text', 'classic-city-core' ),
						'name'     => 'link_text',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '40' ),
					),
					array(
						'key'      => 'field_link_pods_link_url',
						'label'    => __( 'Link URL', 'classic-city-core' ),
						'name'     => 'link_url',
						'type'     => 'url',
						'required' => 1,
						'wrapper'  => array( 'width' => '60' ),
					),
					array(
						'key'           => 'field_link_pods_bg_image',
						'label'         => __( 'Background Image (optional)', 'classic-city-core' ),
						'name'          => 'bg_image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'wrapper'       => array( 'width' => '50' ),
					),
					array(
						'key'           => 'field_link_pods_bg_color',
						'label'         => __( 'Background Color', 'classic-city-core' ),
						'name'          => 'bg_color',
						'type'          => 'select',
						'choices'       => function_exists( 'ccc_palette_slug_choices' ) ? ccc_palette_slug_choices() : array( '' => '— Default —' ),
						'default_value' => '',
						'allow_null'    => 0,
						'ui'            => 1,
						'instructions'  => __( 'Applied via the standard has-{slug}-background-color helper. Text color flips automatically.', 'classic-city-core' ),
						'wrapper'       => array( 'width' => '50' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/link-pods',
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
