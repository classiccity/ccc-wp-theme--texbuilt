<?php
/**
 * ACF field group for the Icon Feature Row block.
 *
 * Icon convention matches Feature Grid. Item background is a block-level
 * choice via WP's native color picker (supports.color in block.json) —
 * gradients are disabled because the inverse-icon pattern requires a solid
 * slug with a corresponding `-opposite` color.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_icon_feature_row',
		'title'                 => __( 'Icon Feature Row', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_icon_feature_row_desktop_columns',
				'label'         => __( 'Desktop Columns', 'classic-city-core' ),
				'name'          => 'desktop_columns',
				'type'          => 'select',
				'choices'       => array( 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6' ),
				'default_value' => 4,
				'wrapper'       => array( 'width' => '40' ),
			),
			array(
				'key'          => 'field_icon_feature_row_items',
				'label'        => __( 'Features', 'classic-city-core' ),
				'name'         => 'features',
				'type'         => 'repeater',
				'min'          => 2,
				'max'          => 8,
				'layout'       => 'block',
				'button_label' => __( 'Add Feature', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'          => 'field_icon_feature_row_icon',
						'label'        => __( 'Icon Name', 'classic-city-core' ),
						'name'         => 'icon_name',
						'type'         => 'text',
						'instructions' => __( 'FontAwesome icon name, e.g. "fa-star", "fa-bolt".', 'classic-city-core' ),
						'placeholder'  => 'fa-star',
						'required'     => 1,
						'wrapper'      => array( 'width' => '25' ),
					),
					array(
						'key'      => 'field_icon_feature_row_heading',
						'label'    => __( 'Heading', 'classic-city-core' ),
						'name'     => 'heading',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '35' ),
					),
					array(
						'key'      => 'field_icon_feature_row_body',
						'label'    => __( 'Body', 'classic-city-core' ),
						'name'     => 'body',
						'type'     => 'textarea',
						'rows'     => 2,
						'required' => 1,
						'wrapper'  => array( 'width' => '40' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/icon-feature-row',
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
