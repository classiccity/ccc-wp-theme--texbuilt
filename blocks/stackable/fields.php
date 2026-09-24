<?php
/**
 * ACF field group for the Stackable block.
 *
 * Single field: sticky_offset — picks a spacing preset slug used as
 * the `top: var(--wp--preset--spacing--{slug})` value for the sticky
 * positioning. Default 40 matches the user's "default space below the
 * navbar" spec. Authors can override per-instance if the active
 * theme's fixed chrome (navbar, announcement bar) needs more
 * clearance.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_stackable',
		'title'                 => __( 'Stackable', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_stackable_sticky_offset',
				'label'         => __( 'Sticky Offset', 'classic-city-core' ),
				'name'          => 'sticky_offset',
				'type'          => 'select',
				'choices'       => array(
					'10'  => '10',
					'20'  => '20',
					'30'  => '30',
					'40'  => '40 (default)',
					'50'  => '50',
					'60'  => '60',
					'70'  => '70',
					'80'  => '80',
					'90'  => '90',
					'100' => '100',
				),
				'default_value' => '40',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Spacing-preset slug used as the sticky top offset. Renders as <code>top: var(--wp--preset--spacing--{slug})</code>. Bump this when stacking under a tall navbar or announcement bar so the block clears it.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/stackable',
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
