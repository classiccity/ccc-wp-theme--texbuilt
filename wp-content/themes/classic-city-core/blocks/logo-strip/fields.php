<?php
/**
 * ACF field group for the Logo Strip block.
 *
 * Two layouts: static grid (default — wraps to multiple rows) and marquee
 * scroller (single row, CSS-animated infinite loop). Scroller-only settings
 * (speed, direction, pause-on-hover) are conditional on layout === 'scroller'.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_logo_strip',
		'title'                 => __( 'Logo Strip', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'   => 'field_logo_strip_eyebrow',
				'label' => __( 'Eyebrow Text (optional)', 'classic-city-core' ),
				'name'  => 'eyebrow',
				'type'  => 'text',
			),
			array(
				'key'           => 'field_logo_strip_layout',
				'label'         => __( 'Layout', 'classic-city-core' ),
				'name'          => 'layout',
				'type'          => 'button_group',
				'choices'       => array(
					'grid'     => __( 'Static Grid', 'classic-city-core' ),
					'scroller' => __( 'Marquee Scroller', 'classic-city-core' ),
				),
				'default_value' => 'grid',
				'allow_null'    => 0,
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'               => 'field_logo_strip_scroll_speed',
				'label'             => __( 'Scroll Speed', 'classic-city-core' ),
				'name'              => 'scroll_speed',
				'type'              => 'select',
				'choices'           => array(
					'slow'   => __( 'Slow (60s loop)', 'classic-city-core' ),
					'medium' => __( 'Medium (40s loop)', 'classic-city-core' ),
					'fast'   => __( 'Fast (25s loop)', 'classic-city-core' ),
				),
				'default_value'     => 'medium',
				'allow_null'        => 0,
				'ui'                => 1,
				'wrapper'           => array( 'width' => '25' ),
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_logo_strip_layout',
							'operator' => '==',
							'value'    => 'scroller',
						),
					),
				),
			),
			array(
				'key'               => 'field_logo_strip_scroll_direction',
				'label'             => __( 'Direction', 'classic-city-core' ),
				'name'              => 'scroll_direction',
				'type'              => 'button_group',
				'choices'           => array(
					'left'  => __( '← Left', 'classic-city-core' ),
					'right' => __( 'Right →', 'classic-city-core' ),
				),
				'default_value'     => 'left',
				'allow_null'        => 0,
				'wrapper'           => array( 'width' => '25' ),
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_logo_strip_layout',
							'operator' => '==',
							'value'    => 'scroller',
						),
					),
				),
			),
			array(
				'key'               => 'field_logo_strip_pause_on_hover',
				'label'             => __( 'Pause on Hover', 'classic-city-core' ),
				'name'              => 'pause_on_hover',
				'type'              => 'true_false',
				'ui'                => 1,
				'default_value'     => 1,
				'instructions'      => __( 'Honors prefers-reduced-motion regardless of this setting.', 'classic-city-core' ),
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_logo_strip_layout',
							'operator' => '==',
							'value'    => 'scroller',
						),
					),
				),
			),
			array(
				'key'          => 'field_logo_strip_items',
				'label'        => __( 'Logos', 'classic-city-core' ),
				'name'         => 'logos',
				'type'         => 'repeater',
				'min'          => 2,
				'max'          => 20,
				'layout'       => 'table',
				'button_label' => __( 'Add Logo', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'           => 'field_logo_strip_image',
						'label'         => __( 'Logo', 'classic-city-core' ),
						'name'          => 'logo_image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'thumbnail',
						'required'      => 1,
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/logo-strip',
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
