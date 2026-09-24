<?php
/**
 * ACF field group for the Card with Detail Rows block.
 *
 * Card background is set ONCE at the block level via WP's native color picker
 * (see supports.color in block.json) — there is deliberately no ACF color
 * field here. render.php strips WP's auto-injected background off the grid
 * root and re-applies it to every card, so the color lands on the cards and
 * not on the container behind them (same mechanism as feature-grid).
 *
 * Gradients are enabled: the generated gradient helper classes carry the
 * paired `-opposite` text color just like the solid ones do, so card text
 * stays readable either way. (feature-grid disables them only because its
 * inverse-icon rules need a solid slug — this block has no icon.)
 *
 * The detail rows are a NESTED repeater inside `cards`. `value` is a TEXT
 * field, not a number: real content includes things like "within 18 hours",
 * "5,000+", and "$75k–$150k".
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_card_detail_rows',
		'title'                 => __( 'Card with Detail Rows', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_card_detail_rows_desktop_columns',
				'label'         => __( 'Desktop Columns', 'classic-city-core' ),
				'name'          => 'desktop_columns',
				'type'          => 'select',
				'choices'       => array( 1 => '1', 2 => '2', 3 => '3', 4 => '4' ),
				'default_value' => 3,
				'allow_null'    => 0,
				'ui'            => 1,
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_card_detail_rows_mobile_layout',
				'label'         => __( 'Mobile Layout', 'classic-city-core' ),
				'name'          => 'mobile_layout',
				'type'          => 'select',
				'choices'       => array(
					'stack'  => __( 'Stacked', 'classic-city-core' ),
					'scroll' => __( 'Horizontal Scroll', 'classic-city-core' ),
				),
				'default_value' => 'stack',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'How cards behave below the tablet breakpoint. Stacked = one card per row; Horizontal Scroll = swipeable row where the next card peeks in at the right edge.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'          => 'field_card_detail_rows_cards',
				'label'        => __( 'Cards', 'classic-city-core' ),
				'name'         => 'cards',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 12,
				'layout'       => 'block',
				'button_label' => __( 'Add Card', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'          => 'field_card_detail_rows_eyebrow',
						'label'        => __( 'Eyebrow', 'classic-city-core' ),
						'name'         => 'eyebrow',
						'type'         => 'text',
						'instructions' => __( 'Small uppercase lead-in above the heading. Leave empty to omit.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '40' ),
					),
					array(
						'key'      => 'field_card_detail_rows_heading',
						'label'    => __( 'Heading', 'classic-city-core' ),
						'name'     => 'heading',
						'type'     => 'text',
						'wrapper'  => array( 'width' => '60' ),
					),

					/* ─── Nested repeater: detail rows ─── */
					array(
						'key'          => 'field_card_detail_rows_rows',
						'label'        => __( 'Detail Rows', 'classic-city-core' ),
						'name'         => 'rows',
						'type'         => 'repeater',
						'min'          => 0,
						'max'          => 10,
						'layout'       => 'table',
						'button_label' => __( 'Add Detail Row', 'classic-city-core' ),
						'instructions' => __( 'Label on the left, value on the right, separated by a hairline rule. Leave empty to omit the list entirely.', 'classic-city-core' ),
						'sub_fields'   => array(
							array(
								'key'      => 'field_card_detail_rows_row_label',
								'label'    => __( 'Label', 'classic-city-core' ),
								'name'     => 'label',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => array( 'width' => '65' ),
							),
							array(
								'key'          => 'field_card_detail_rows_row_value',
								'label'        => __( 'Value', 'classic-city-core' ),
								'name'         => 'value',
								'type'         => 'text',
								'required'     => 1,
								'instructions' => __( 'Free text, not just numbers — "19", "91%", "within 18 hours" and "$75k–$150k" are all valid.', 'classic-city-core' ),
								'wrapper'      => array( 'width' => '35' ),
							),
						),
					),

					array(
						'key'          => 'field_card_detail_rows_description',
						'label'        => __( 'Description', 'classic-city-core' ),
						'name'         => 'description',
						'type'         => 'wysiwyg',
						'tabs'         => 'visual',
						'toolbar'      => 'basic',
						'media_upload' => 0,
						'delay'        => 1,
					),
					array(
						'key'          => 'field_card_detail_rows_button_text',
						'label'        => __( 'Button Text', 'classic-city-core' ),
						'name'         => 'button_text',
						'type'         => 'text',
						'instructions' => __( 'Leave empty to omit the button. When present, it pins to the bottom edge of the card so buttons line up across the row.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '35' ),
					),
					array(
						'key'     => 'field_card_detail_rows_button_link',
						'label'   => __( 'Button Link', 'classic-city-core' ),
						'name'    => 'button_link',
						'type'    => 'url',
						'wrapper' => array( 'width' => '40' ),
					),
					array(
						'key'           => 'field_card_detail_rows_button_style',
						'label'         => __( 'Button Style', 'classic-city-core' ),
						'name'          => 'button_style',
						'type'          => 'select',
						'choices'       => array(
							'fill'    => __( 'Solid', 'classic-city-core' ),
							'outline' => __( 'Outline', 'classic-city-core' ),
							'text'    => __( 'Text', 'classic-city-core' ),
						),
						'default_value' => 'fill',
						'allow_null'    => 0,
						'ui'            => 1,
						'instructions'  => __( 'The standard core/button styles. Per content rule 6: page-level main CTA solid, a second button in the same row outline.', 'classic-city-core' ),
						'wrapper'       => array( 'width' => '25' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/card-detail-rows',
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
