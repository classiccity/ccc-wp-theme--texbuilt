<?php
/**
 * ACF field group for the Testimonial Cards block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_testimonial_cards',
		'title'                 => __( 'Testimonial Cards', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_testimonial_cards_posts',
				'label'         => __( 'Testimonials', 'classic-city-core' ),
				'name'          => 'testimonials',
				'type'          => 'post_object',
				'post_type'     => array( 'testimonial' ),
				'multiple'      => 1,
				'return_format' => 'id',
				'ui'            => 1,
				'allow_null'    => 0,
				'instructions'  => __( 'Pick one or more testimonials to display. Order of selection is the order shown.', 'classic-city-core' ),
				'required'      => 1,
			),
			array(
				'key'           => 'field_testimonial_cards_desktop_columns',
				'label'         => __( 'Desktop Columns', 'classic-city-core' ),
				'name'          => 'desktop_columns',
				'type'          => 'select',
				'choices'       => array(
					1 => '1',
					2 => '2',
					3 => '3',
					4 => '4',
				),
				'default_value' => 3,
				'instructions'  => __( 'How many cards per row on desktop.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_testimonial_cards_mobile_layout',
				'label'         => __( 'Mobile Layout', 'classic-city-core' ),
				'name'          => 'mobile_layout',
				'type'          => 'select',
				'choices'       => array(
					'column-count'      => __( 'Column Count (stacks cards vertically)', 'classic-city-core' ),
					'horizontal-scroll' => __( 'Horizontal Scroll (swipeable carousel)', 'classic-city-core' ),
				),
				'default_value' => 'column-count',
				'instructions'  => __( 'How cards arrange on phones.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/testimonial-cards',
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
