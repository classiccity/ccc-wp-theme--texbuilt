<?php
/**
 * ACF field group for the Stat Comparison block.
 *
 * Surface color is set at the block level via WP's native color picker
 * (supports.color in block.json) — the block root IS the panel, so there is
 * deliberately no ACF color field. The border is derived from that choice in
 * render.php.
 *
 * `from_value` and `to_value` are TEXT, not numbers. The block never computes
 * anything from them — there is no bar to size and no delta to print — so
 * "14.3%", "5 min 15 sec" and "2 in 10" are all equally valid. (Contrast
 * `chart-horizontal-bars`, whose value MUST be numeric because bar widths are
 * derived from it.)
 *
 * There is no third value and no "% change" field on purpose: the two figures
 * ARE the comparison, and a computed delta printed beside them is one more
 * number to read and one more thing to fall out of sync. If the change needs
 * naming, name it in the heading.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_stat_comparison',
		'title'                 => __( 'Stat Comparison', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'          => 'field_stat_comparison_from_value',
				'label'        => __( 'From Value', 'classic-city-core' ),
				'name'         => 'from_value',
				'type'         => 'text',
				'required'     => 1,
				'instructions' => __( 'The "before" figure. Free text — "14.3%", "5 min 15 sec", "2 in 10". Keep it short: it renders at display scale and never wraps.', 'classic-city-core' ),
				'placeholder'  => '14.3%',
				'wrapper'      => array( 'width' => '50' ),
			),
			array(
				'key'          => 'field_stat_comparison_to_value',
				'label'        => __( 'To Value', 'classic-city-core' ),
				'name'         => 'to_value',
				'type'         => 'text',
				'required'     => 1,
				'instructions' => __( 'The "after" figure, in the SAME unit as the From Value — the arrow between them asserts they are comparable. This is the one the eye should land on; it takes the accent color by default.', 'classic-city-core' ),
				'placeholder'  => '79.1%',
				'wrapper'      => array( 'width' => '50' ),
			),
			array(
				'key'          => 'field_stat_comparison_heading',
				'label'        => __( 'Heading', 'classic-city-core' ),
				'name'         => 'heading',
				'type'         => 'text',
				'required'     => 1,
				'instructions' => __( 'What the figures measure — a full sentence is fine and usually better than a fragment. Renders as an h3, so the section h2 that introduces it lives outside the block (content rule 9).', 'classic-city-core' ),
			),
			array(
				'key'          => 'field_stat_comparison_body',
				'label'        => __( 'Body', 'classic-city-core' ),
				'name'         => 'body',
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
				'delay'        => 1,
				'instructions' => __( 'A short paragraph of context — and the honest place for the caveat ("early data, small sample"). Leave empty to run the block as figure + heading only.', 'classic-city-core' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/stat-comparison',
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
