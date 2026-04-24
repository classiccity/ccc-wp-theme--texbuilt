<?php
/**
 * ACF field group for the Feature Grid block.
 *
 * Icon convention: admins enter the bare FA name (e.g. `fa-star`). The style
 * (solid/regular/light/sharp-light) is resolved site-wide from the theme-level
 * icon-style setting — see render.php.
 *
 * Card background is set ONCE at the block level via WP's native color picker
 * (see supports.color in block.json). render.php applies the chosen slug to
 * every `.sg-block-feature` card, and the inverse-icon CSS rules (emitted per
 * slug by css-generator.ts) automatically flip the icon wrapper. Gradients
 * are disabled on purpose — the inverse-icon pattern requires a solid slug
 * with a corresponding `-opposite` color.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_feature_grid',
		'title'                 => __( 'Feature Grid', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_feature_grid_desktop_columns',
				'label'         => __( 'Desktop Columns', 'classic-city-core' ),
				'name'          => 'desktop_columns',
				'type'          => 'select',
				'choices'       => array( 2 => '2', 3 => '3', 4 => '4', 5 => '5' ),
				'default_value' => 3,
				'wrapper'       => array( 'width' => '40' ),
			),
			array(
				'key'           => 'field_feature_grid_items',
				'label'         => __( 'Features', 'classic-city-core' ),
				'name'          => 'features',
				'type'          => 'repeater',
				'min'           => 1,
				'max'           => 12,
				'layout'        => 'block',
				'button_label'  => __( 'Add Feature', 'classic-city-core' ),
				'sub_fields'    => array(
					array(
						'key'          => 'field_feature_grid_icon_name',
						'label'        => __( 'Icon Name', 'classic-city-core' ),
						'name'         => 'icon_name',
						'type'         => 'text',
						'instructions' => __( 'FontAwesome icon name, e.g. "fa-star", "fa-bolt". Style is set site-wide; just enter the bare name.', 'classic-city-core' ),
						'placeholder'  => 'fa-star',
						'required'     => 1,
						'wrapper'      => array( 'width' => '25' ),
					),
					array(
						'key'      => 'field_feature_grid_heading',
						'label'    => __( 'Heading', 'classic-city-core' ),
						'name'     => 'heading',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '35' ),
					),
					array(
						'key'      => 'field_feature_grid_body',
						'label'    => __( 'Body', 'classic-city-core' ),
						'name'     => 'body',
						'type'     => 'textarea',
						'rows'     => 3,
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
					'value'    => 'classic-city-core/feature-grid',
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
