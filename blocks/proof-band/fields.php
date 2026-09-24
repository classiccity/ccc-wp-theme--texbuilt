<?php
/**
 * ACF field group for the Proof Band block.
 *
 * The band's surface color is set at the block level via WP's native color
 * picker (supports.color in block.json), not an ACF field — the block root IS
 * the band, so WP's injected helper class already lands in the right place and
 * carries the paired `-opposite` text color with it. The border is derived from
 * that choice in render.php.
 *
 * `value` is a TEXT field, not a number: the band's real content mixes "183%",
 * "73%" and "~2 days" in the same row, and nothing here is summed or charted
 * (that's `chart-horizontal-bars`' job, which is why ITS value is numeric).
 *
 * The stats repeater is capped at 4 on purpose. The band's job is a beat, not a
 * dashboard — past four figures the accent cycle repeats and the quote below
 * stops reading as the payoff. If content needs more, it wants `stats`
 * (is-style-pods) instead.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_proof_band',
		'title'                 => __( 'Proof Band', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'          => 'field_proof_band_kicker',
				'label'        => __( 'Kicker', 'classic-city-core' ),
				'name'         => 'kicker',
				'type'         => 'text',
				'instructions' => __( 'Small uppercase lead-in that names the source of the proof — "From one recent study". Leave empty to omit. This is a label, not the section heading: per content rule 9 the h2 that introduces the band belongs OUTSIDE it.', 'classic-city-core' ),
				'wrapper'      => array( 'width' => '100' ),
			),
			array(
				'key'          => 'field_proof_band_stats',
				'label'        => __( 'Stats', 'classic-city-core' ),
				'name'         => 'stats',
				'type'         => 'repeater',
				'min'          => 2,
				'max'          => 4,
				'layout'       => 'table',
				'button_label' => __( 'Add Stat', 'classic-city-core' ),
				'instructions' => __( 'Two to four figures. Values cycle through three accent slots in order (1st, 4th → accent 1; 2nd → accent 2; 3rd → accent 3), so the order you enter them is the order they are colored.', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'          => 'field_proof_band_stat_value',
						'label'        => __( 'Value', 'classic-city-core' ),
						'name'         => 'value',
						'type'         => 'text',
						'required'     => 1,
						'instructions' => __( 'Free text, not just numbers — "183%", "73%" and "~2 days" are all valid. Keep it short; it renders at heading scale and never wraps.', 'classic-city-core' ),
						'placeholder'  => '183%',
						'wrapper'      => array( 'width' => '30' ),
					),
					array(
						'key'          => 'field_proof_band_stat_label',
						'label'        => __( 'Label', 'classic-city-core' ),
						'name'         => 'label',
						'type'         => 'text',
						'required'     => 1,
						'instructions' => __( 'One sentence of context under the figure — this is a longer caption than the `stats` block takes.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '70' ),
					),
				),
			),
			array(
				'key'          => 'field_proof_band_quote',
				'label'        => __( 'Quote', 'classic-city-core' ),
				'name'         => 'quote',
				'type'         => 'textarea',
				'rows'         => 3,
				'new_lines'    => '',
				'instructions' => __( 'Words somebody actually said (content rule 23 — this renders as a real <blockquote>). Quotation marks are added by the block; do not type them. Leave empty to omit the quote and its attribution.', 'classic-city-core' ),
			),
			array(
				'key'          => 'field_proof_band_quote_attribution',
				'label'        => __( 'Quote Attribution', 'classic-city-core' ),
				'name'         => 'quote_attribution',
				'type'         => 'text',
				'instructions' => __( 'Who said it — "research site feedback, US pain study". Renders as a <cite>; ignored when Quote is empty.', 'classic-city-core' ),
				'wrapper'      => array( 'width' => '100' ),
			),
			array(
				'key'          => 'field_proof_band_link_text',
				'label'        => __( 'Link Text', 'classic-city-core' ),
				'name'         => 'link_text',
				'type'         => 'text',
				'instructions' => __( 'Leave either this or the URL empty to omit the link. One link only — the band closes a beat, it is not a menu.', 'classic-city-core' ),
				'wrapper'      => array( 'width' => '50' ),
			),
			array(
				'key'     => 'field_proof_band_link_url',
				'label'   => __( 'Link URL', 'classic-city-core' ),
				'name'    => 'link_url',
				'type'    => 'url',
				'wrapper' => array( 'width' => '50' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/proof-band',
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
