<?php
/**
 * ACF field group for the Stats block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_stats',
		'title'                 => __( 'Stats', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_stats_desktop_columns',
				'label'         => __( 'Desktop Columns', 'classic-city-core' ),
				'name'          => 'desktop_columns',
				'type'          => 'select',
				'choices'       => array( 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6' ),
				'default_value' => 4,
				'instructions'  => __( 'Number of stat columns at desktop width.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '40' ),
			),
			array(
				'key'          => 'field_stats_items',
				'label'        => __( 'Stats', 'classic-city-core' ),
				'name'         => 'stats',
				'type'         => 'repeater',
				'min'          => 2,
				'max'          => 8,
				'layout'       => 'table',
				'button_label' => __( 'Add Stat', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'          => 'field_stats_value',
						'label'        => __( 'Value', 'classic-city-core' ),
						'name'         => 'value',
						'type'         => 'text',
						'instructions' => __( 'Big number or short phrase (e.g. "12k+", "98%", "24/7").', 'classic-city-core' ),
						'placeholder'  => '12k+',
						'required'     => 1,
					),
					array(
						'key'      => 'field_stats_label',
						'label'    => __( 'Label', 'classic-city-core' ),
						'name'     => 'label',
						'type'     => 'text',
						'required' => 1,
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/stats',
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
