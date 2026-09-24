<?php
/**
 * ACF field group for the Detail Cards block.
 *
 * Block-level controls layout: column count (1-4), background-image
 * opacity (per-card images), autoscroll toggle (infinite marquee),
 * mobile layout (stack vs horizontal scroll), and card aspect ratio.
 * Card background color is the Gutenberg native picker (block.json
 * `supports.color.background`) and gets propagated onto each card
 * rather than the wrapper — see render.php.
 *
 * Per-card sub-fields: stat (left), stat_title (right), description
 * (bottom), background image.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_detail_cards',
		'title'                 => __( 'Detail Cards', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_detail_cards_column_count',
				'label'         => __( 'Column Count', 'classic-city-core' ),
				'name'          => 'column_count',
				'type'          => 'select',
				'choices'       => array(
					1 => '1',
					2 => '2',
					3 => '3',
					4 => '4',
				),
				'default_value' => 3,
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'How many cards fit per row when autoscroll is off. When autoscroll is on, cards are a fixed ~33vw wide and overflow into the marquee regardless of this value.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_detail_cards_bg_opacity',
				'label'         => __( 'Background Image Opacity', 'classic-city-core' ),
				'name'          => 'bg_opacity',
				'type'          => 'number',
				'min'           => 0,
				'max'           => 100,
				'step'          => 5,
				'default_value' => 100,
				'append'        => '%',
				'instructions'  => __( 'Opacity of each card&rsquo;s background image (0&ndash;100). Lower values let the card&rsquo;s background color show through.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_detail_cards_autoscroll',
				'label'         => __( 'Autoscroll', 'classic-city-core' ),
				'name'          => 'autoscroll',
				'type'          => 'true_false',
				'ui'            => 1,
				'ui_on_text'    => __( 'On', 'classic-city-core' ),
				'ui_off_text'   => __( 'Off', 'classic-city-core' ),
				'default_value' => 0,
				'instructions'  => __( 'Infinite marquee row (same CSS pattern as the Logo Strip scroller).', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_detail_cards_mobile_layout',
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
				'instructions'  => __( 'How cards behave below the tablet breakpoint. Stacked = one card per row; Horizontal Scroll = swipeable row with scroll-snap. Autoscroll always disables itself on mobile.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_detail_cards_aspect_ratio',
				'label'         => __( 'Card Aspect Ratio', 'classic-city-core' ),
				'name'          => 'aspect_ratio',
				'type'          => 'select',
				'choices'       => array(
					'3/4'  => '3 : 4 (portrait)',
					'4/5'  => '4 : 5 (slight portrait)',
					'1/1'  => '1 : 1 (square)',
					'4/3'  => '4 : 3 (landscape)',
					'3/2'  => '3 : 2 (landscape)',
					'16/9' => '16 : 9 (landscape)',
				),
				'default_value' => '3/4',
				'allow_null'    => 0,
				'ui'            => 1,
				'wrapper'       => array( 'width' => '100' ),
			),

			array(
				'key'          => 'field_detail_cards_cards',
				'label'        => __( 'Cards', 'classic-city-core' ),
				'name'         => 'cards',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 24,
				'layout'       => 'block',
				'button_label' => __( 'Add Card', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'          => 'field_detail_cards_card_stat',
						'label'        => __( 'Stat', 'classic-city-core' ),
						'name'         => 'stat',
						'type'         => 'text',
						'instructions' => __( 'Top-left value, e.g. &ldquo;80&rdquo;, &ldquo;0.93&rdquo;, &ldquo;74&rdquo;. Renders at the &ldquo;large&rdquo; paragraph size, left-aligned.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
					array(
						'key'          => 'field_detail_cards_card_stat_title',
						'label'        => __( 'Stat Title', 'classic-city-core' ),
						'name'         => 'stat_title',
						'type'         => 'text',
						'instructions' => __( 'Top-right label, e.g. &ldquo;Vitamin D&rdquo;. Renders at the &ldquo;small&rdquo; paragraph size, right-aligned. Can include a unit on the second line.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
					array(
						'key'          => 'field_detail_cards_card_description',
						'label'        => __( 'Description', 'classic-city-core' ),
						'name'         => 'description',
						'type'         => 'textarea',
						'rows'         => 3,
						'instructions' => __( 'Bottom-anchored copy. Renders at the &ldquo;small&rdquo; paragraph size.', 'classic-city-core' ),
					),
					array(
						'key'           => 'field_detail_cards_card_bg_image',
						'label'         => __( 'Background Image', 'classic-city-core' ),
						'name'          => 'bg_image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/detail-cards',
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
