<?php
/**
 * ACF field group for the Process Steps block.
 *
 * Card background is a block-level native picker (solid OR gradient). One
 * choice applies to every step card.
 *
 * Number circle color is a block-level ACF select. One choice applies to
 * every step's number badge — the circle bg = picked slug, the number glyph
 * = that slug's opposite (combined-helper pattern, expressed via inline
 * `--step-number-bg` / `--step-number-color` custom props since ::before
 * can't carry classes).
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_process_steps',
		'title'                 => __( 'Process Steps', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_process_steps_desktop_columns',
				'label'         => __( 'Desktop Columns', 'classic-city-core' ),
				'name'          => 'desktop_columns',
				'type'          => 'select',
				'choices'       => array( 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6' ),
				'default_value' => 5,
				'wrapper'       => array( 'width' => '40' ),
			),
			array(
				'key'           => 'field_process_steps_number_bg',
				'label'         => __( 'Number Circle Color', 'classic-city-core' ),
				'name'          => 'number_bg',
				'type'          => 'select',
				'choices'       => ccc_palette_slug_choices(),
				'default_value' => '',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Color for the number badge on every step. Circle bg = the picked color; number glyph = its opposite. Leave blank to use the default (primary).', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '40' ),
			),
			array(
				'key'          => 'field_process_steps_items',
				'label'        => __( 'Steps', 'classic-city-core' ),
				'name'         => 'steps',
				'type'         => 'repeater',
				'min'          => 2,
				'max'          => 10,
				'layout'       => 'block',
				'button_label' => __( 'Add Step', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'      => 'field_process_steps_heading',
						'label'    => __( 'Heading', 'classic-city-core' ),
						'name'     => 'heading',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '40' ),
					),
					array(
						'key'      => 'field_process_steps_body',
						'label'    => __( 'Body', 'classic-city-core' ),
						'name'     => 'body',
						'type'     => 'textarea',
						'rows'     => 2,
						'required' => 1,
						'wrapper'  => array( 'width' => '60' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/process-steps',
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
