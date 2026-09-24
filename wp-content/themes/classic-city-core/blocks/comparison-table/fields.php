<?php
/**
 * ACF field group for the Comparison Table block.
 *
 * Surface color is set at the block level via WP's native color picker
 * (supports.color in block.json). The border is derived from that choice in
 * render.php.
 *
 * SIDE LABELS ARE BLOCK-LEVEL, NOT PER ROW. "trialport" and "peer average"
 * repeat identically on every metric; making them sub-fields would mean typing
 * the same two strings six times and inviting a typo on row four that reads as
 * a different comparator. One pair, declared once.
 *
 * VALUES ARE TEXT, and there is deliberately NO numeric field beside them. Real
 * content here is "13 min 09 sec" and "17.0" in the same table, which no number
 * field can hold. Bar lengths are parsed out of the text in render.php — see
 * the header there for the parse rule and the same-unit guard that suppresses
 * bars rather than drawing a wrong one. A separate numeric "bar width" field
 * would be a second source of truth that silently drifts from the figure
 * printed next to it, which is exactly the failure `chart-horizontal-bars`
 * avoids by computing its widths too.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_comparison_table',
		'title'                 => __( 'Comparison Table', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'     => 'field_comparison_table_title',
				'label'   => __( 'Title', 'classic-city-core' ),
				'name'    => 'title',
				'type'    => 'text',
				'wrapper' => array( 'width' => '100' ),
			),
			array(
				'key'          => 'field_comparison_table_description',
				'label'        => __( 'Description', 'classic-city-core' ),
				'name'         => 'description',
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
				'delay'        => 1,
				'instructions' => __( 'Optional copy between the title and the first metric. Renders at the small font size.', 'classic-city-core' ),
			),
			array(
				'key'           => 'field_comparison_table_our_label',
				'label'         => __( 'Our Label', 'classic-city-core' ),
				'name'          => 'our_label',
				'type'          => 'text',
				'default_value' => 'us',
				'instructions'  => __( 'Name of the side being argued for — the brand, the product. Repeats on every row, so keep it to one or two words.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_comparison_table_their_label',
				'label'         => __( 'Their Label', 'classic-city-core' ),
				'name'          => 'their_label',
				'type'          => 'text',
				'default_value' => 'peer average',
				'instructions'  => __( 'Name of the comparator. "peer average" or "industry benchmark" is more defensible than naming a competitor — say what it is, and cite it in the footnote.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'          => 'field_comparison_table_rows',
				'label'        => __( 'Metrics', 'classic-city-core' ),
				'name'         => 'rows',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 10,
				'layout'       => 'block',
				'button_label' => __( 'Add Metric', 'classic-city-core' ),
				'instructions' => __( 'One row per metric, each drawn as its own pair of bars. Bar lengths are worked out from the two values — enter real figures, never percentages of each other.', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'          => 'field_comparison_table_row_label',
						'label'        => __( 'Metric', 'classic-city-core' ),
						'name'         => 'label',
						'type'         => 'text',
						'required'     => 1,
						'instructions' => __( 'What is being measured — "Average visit duration". Not a claim; the claim is the block title.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '100' ),
					),
					array(
						'key'          => 'field_comparison_table_row_our_value',
						'label'        => __( 'Our Value', 'classic-city-core' ),
						'name'         => 'our_value',
						'type'         => 'text',
						'required'     => 1,
						'instructions' => __( 'Printed exactly as typed. "13 min 09 sec", "17.0", "46".', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
					array(
						'key'          => 'field_comparison_table_row_their_value',
						'label'        => __( 'Their Value', 'classic-city-core' ),
						'name'         => 'their_value',
						'type'         => 'text',
						'required'     => 1,
						'instructions' => __( 'Same unit and same shape as Our Value — "5 min 15 sec" against "13 min 09 sec", not "5.25 minutes". When the two do not match, the row prints its figures with NO bars rather than drawing a misleading one. Keep ranges and caveats out of here and put them in the footnote.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
				),
			),
			array(
				'key'          => 'field_comparison_table_footnote',
				'label'        => __( 'Footnote', 'classic-city-core' ),
				'name'         => 'footnote',
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
				'delay'        => 1,
				'instructions' => __( 'Where the comparator numbers came from, and how firm they are. A benchmark claim without a source is the weakest thing on the page — this field should almost always be filled.', 'classic-city-core' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/comparison-table',
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
