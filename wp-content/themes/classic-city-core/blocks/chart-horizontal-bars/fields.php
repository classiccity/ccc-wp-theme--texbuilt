<?php
/**
 * ACF field group for the Chart: Horizontal Bars block.
 *
 * The card background is set via WP's native color picker (supports.color in
 * block.json), not an ACF field — the block root IS the card, so WP's injected
 * helper class already lands in the right place and needs no re-targeting. The
 * card border is derived from that choice in render.php.
 *
 * `value` is a NUMBER field, unlike the text values in card-detail-rows: bar
 * widths are each value's share of the column total, so the values have to be
 * summable. Authors enter the real figure (205, 41, 23, 12) and never a
 * percentage — computing the proportion is the block's job.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_chart_horizontal_bars',
		'title'                 => __( 'Chart: Horizontal Bars', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'     => 'field_chart_horizontal_bars_title',
				'label'   => __( 'Title', 'classic-city-core' ),
				'name'    => 'title',
				'type'    => 'text',
				'wrapper' => array( 'width' => '100' ),
			),
			array(
				'key'          => 'field_chart_horizontal_bars_description',
				'label'        => __( 'Description', 'classic-city-core' ),
				'name'         => 'description',
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
				'delay'        => 1,
				'instructions' => __( 'Optional copy between the title and the chart. Renders at the small font size.', 'classic-city-core' ),
			),
			array(
				'key'          => 'field_chart_horizontal_bars_bars',
				'label'        => __( 'Bars', 'classic-city-core' ),
				'name'         => 'bars',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 20,
				'layout'       => 'table',
				'button_label' => __( 'Add Bar', 'classic-city-core' ),
				'instructions' => __( 'Bar lengths are calculated automatically as each value\'s share of the total across all rows — enter the real numbers, never percentages.', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'      => 'field_chart_horizontal_bars_label',
						'label'    => __( 'Label', 'classic-city-core' ),
						'name'     => 'label',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '55' ),
					),
					array(
						'key'          => 'field_chart_horizontal_bars_value',
						'label'        => __( 'Value', 'classic-city-core' ),
						'name'         => 'value',
						'type'         => 'number',
						'required'     => 1,
						'step'         => 'any',
						'instructions' => __( 'The raw figure. Shown at the end of the row exactly as entered (thousands separated).', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '25' ),
					),
					array(
						'key'           => 'field_chart_horizontal_bars_is_highlight',
						'label'         => __( 'Highlight?', 'classic-city-core' ),
						'name'          => 'is_highlight',
						'type'          => 'true_false',
						'default_value' => 0,
						'ui'            => 1,
						'instructions'  => __( 'Adds `is-highlighted` to the row, which paints its bar with the CTA color by default.', 'classic-city-core' ),
						'wrapper'       => array( 'width' => '20' ),
					),
				),
			),
			array(
				'key'          => 'field_chart_horizontal_bars_footnote',
				'label'        => __( 'Footnote', 'classic-city-core' ),
				'name'         => 'footnote',
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
				'delay'        => 1,
				'instructions' => __( 'Optional source or caveat line below the chart. Renders at the small font size.', 'classic-city-core' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/chart-horizontal-bars',
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
