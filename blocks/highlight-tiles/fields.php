<?php
/**
 * ACF field group for the Highlight Tiles block.
 *
 * Block-level: card_count (4 or 6 — drives the desktop grid: 2×2 or 3×2).
 * Aspect ratio is hardcoded in CSS — there is no editor control for it.
 *
 * Per card: headline (textarea so authors can hand-break lines, e.g.
 * "Backed by 40+ peer-reviewed studies / on our care model"), bg_color
 * (palette slug — used as both the surface color and the auto-paired
 * text color via the parent's pair-helpers), image_treatment
 * (Background = full-bleed cover with scrim; Inline = centered
 * illustration with a max-width so it doesn't bleed edge-to-edge),
 * and the image itself.
 *
 * Repeater min/max are clamped to 6 — the block layout only knows how
 * to render the first N (4 or 6) cards. Anything extra is ignored.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_highlight_tiles',
		'title'                 => __( 'Highlight Tiles', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_highlight_tiles_card_count',
				'label'         => __( 'Tile Count', 'classic-city-core' ),
				'name'          => 'card_count',
				'type'          => 'select',
				'choices'       => array(
					4 => __( '4 tiles (2 × 2)', 'classic-city-core' ),
					6 => __( '6 tiles (3 × 2)', 'classic-city-core' ),
				),
				'default_value' => 6,
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Desktop layout. 4 tiles drops the right column of the 6-tile arrangement; 6 tiles is the full 3 &times; 2 grid. Mobile stacks single-column either way.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '100' ),
			),

			array(
				'key'          => 'field_highlight_tiles_cards',
				'label'        => __( 'Tiles', 'classic-city-core' ),
				'name'         => 'cards',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 6,
				'layout'       => 'block',
				'button_label' => __( 'Add Tile', 'classic-city-core' ),
				'instructions' => __( 'The block renders the first 4 or 6 tiles depending on the layout above; any extras are ignored.', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'          => 'field_highlight_tiles_card_headline',
						'label'        => __( 'Headline', 'classic-city-core' ),
						'name'         => 'headline',
						'type'         => 'textarea',
						'rows'         => 3,
						'new_lines'    => '',
						'instructions' => __( 'Top-left of the tile. Author-controlled line breaks are preserved — hit Enter to break a line.', 'classic-city-core' ),
						'wrapper'      => array( 'width' => '50' ),
					),
					array(
						'key'           => 'field_highlight_tiles_card_bg_color',
						'label'         => __( 'Background Color', 'classic-city-core' ),
						'name'          => 'bg_color',
						'type'          => 'select',
						'choices'       => function_exists( 'ccc_palette_slug_choices' )
							? ccc_palette_slug_choices()
							: array( 'panel' => 'panel', 'primary' => 'primary' ),
						'default_value' => 'panel',
						'allow_null'    => 1,
						'ui'            => 1,
						'instructions'  => __( 'Palette slug applied to the tile via <code>has-{slug}-background-color</code>. Text color auto-pairs via the parent’s pair-helpers.', 'classic-city-core' ),
						'wrapper'       => array( 'width' => '25' ),
					),
					array(
						'key'           => 'field_highlight_tiles_card_image_treatment',
						'label'         => __( 'Image Treatment', 'classic-city-core' ),
						'name'          => 'image_treatment',
						'type'          => 'select',
						'choices'       => array(
							'background' => __( 'Background', 'classic-city-core' ),
							'inline'     => __( 'Inline', 'classic-city-core' ),
						),
						'default_value' => 'background',
						'allow_null'    => 0,
						'ui'            => 1,
						'instructions'  => __( '<strong>Background</strong>: image fills the tile, cover-cropped, scrim for legibility. <strong>Inline</strong>: image sits centered in the lower portion at constrained width (does NOT bleed edge-to-edge).', 'classic-city-core' ),
						'wrapper'       => array( 'width' => '25' ),
					),
					array(
						'key'           => 'field_highlight_tiles_card_image',
						'label'         => __( 'Image', 'classic-city-core' ),
						'name'          => 'image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'instructions'  => __( 'Optional. Leave empty for a text-only tile (just the headline on the bg color).', 'classic-city-core' ),
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/highlight-tiles',
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
