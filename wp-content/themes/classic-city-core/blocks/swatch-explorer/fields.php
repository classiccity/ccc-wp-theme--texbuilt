<?php
/**
 * ACF field group for the Swatch Explorer block.
 *
 * Head (eyebrow + h2 + description) comes from the shared block-head
 * partial. Block-specific fields are a toggles repeater (the scenario
 * row at the top — Morning / Overcast / Evening in the canonical
 * mockup) with a NESTED swatches sub-repeater per toggle.
 *
 * Each swatch carries:
 *   - swatch_image: thumbnail rendered as a forced 1:1 square
 *   - swatch_name:  label below the thumbnail
 *   - big_image:    the large image that swaps into the panel's
 *                   featured-image slot when this swatch is selected
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_swatch_explorer',
		'title'                 => __( 'Swatch Explorer', 'classic-city-core' ),
		/* Head fields (eyebrow + header + description) from the shared
		   partial. Block-specific fields merge in after. */
		'fields'                => array_merge(
			ccc_block_head_fields( 'swatch_explorer' ),
			array(

				/* ─── Toggles repeater (scenarios at the top) ─── */
				array(
					'key'          => 'field_swatch_explorer_toggles',
					'label'        => __( 'Toggles', 'classic-city-core' ),
					'name'         => 'toggles',
					'type'         => 'repeater',
					'min'          => 1,
					'max'          => 6,
					'layout'       => 'block',
					'button_label' => __( 'Add Toggle', 'classic-city-core' ),
					'instructions' => __( 'Each toggle is a scenario (time of day, lighting condition, product line) and contains its own swatch lineup.', 'classic-city-core' ),
					'sub_fields'   => array(
						array(
							'key'      => 'field_swatch_explorer_toggle_label',
							'label'    => __( 'Toggle Label', 'classic-city-core' ),
							'name'     => 'label',
							'type'     => 'text',
							'required' => 1,
							'wrapper'  => array( 'width' => '75' ),
						),
						array(
							'key'          => 'field_swatch_explorer_toggle_icon_name',
							'label'        => __( 'Toggle Icon', 'classic-city-core' ),
							'name'         => 'icon_name',
							'type'         => 'text',
							'instructions' => __( 'FontAwesome icon name only (e.g. <code>sun</code>, <code>cloud</code>, <code>moon</code>). The site&rsquo;s active FA style is applied automatically.', 'classic-city-core' ),
							'wrapper'      => array( 'width' => '25' ),
						),

						/* ─── Nested repeater: swatches ─── */
						array(
							'key'          => 'field_swatch_explorer_swatches',
							'label'        => __( 'Swatches', 'classic-city-core' ),
							'name'         => 'swatches',
							'type'         => 'repeater',
							'min'          => 1,
							'max'          => 24,
							'layout'       => 'block',
							'button_label' => __( 'Add Swatch', 'classic-city-core' ),
							'sub_fields'   => array(
								array(
									'key'           => 'field_swatch_explorer_swatch_image',
									'label'         => __( 'Swatch Image (square)', 'classic-city-core' ),
									'name'          => 'swatch_image',
									'type'          => 'image',
									'return_format' => 'array',
									'preview_size'  => 'thumbnail',
									'instructions'  => __( 'Rendered as a 1:1 square thumbnail in the swatch row.', 'classic-city-core' ),
									'wrapper'       => array( 'width' => '30' ),
								),
								array(
									'key'      => 'field_swatch_explorer_swatch_name',
									'label'    => __( 'Swatch Name', 'classic-city-core' ),
									'name'     => 'swatch_name',
									'type'     => 'text',
									'required' => 1,
									'wrapper'  => array( 'width' => '70' ),
								),
								array(
									'key'           => 'field_swatch_explorer_swatch_big_image',
									'label'         => __( 'Big Image', 'classic-city-core' ),
									'name'          => 'big_image',
									'type'          => 'image',
									'return_format' => 'array',
									'preview_size'  => 'large',
									'instructions'  => __( 'Large featured photo shown above the swatch row when this swatch is selected.', 'classic-city-core' ),
								),
							),
						),
					),
				),
			)
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/swatch-explorer',
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
