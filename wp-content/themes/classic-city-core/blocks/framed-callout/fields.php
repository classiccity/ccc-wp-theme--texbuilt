<?php
/**
 * ACF field group for the Framed Callout block.
 *
 * Three fields — the bg color/gradient comes from the Gutenberg
 * native picker (block.json `supports.color.background + gradients`)
 * and is applied to the Inner panel in render.php, not the outer
 * container.
 *
 *  - alignment   left | center  (drives horizontal alignment of the
 *                                 InnerBlocks stack inside the inner panel)
 *  - height      auto | 50..100 (numeric values are vh on Container's
 *                                 min-height; auto = content-driven)
 *  - bg_image    optional cover image inside the inner panel, behind
 *                                 the InnerBlocks content
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_framed_callout',
		'title'                 => __( 'Framed Callout', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_framed_callout_alignment',
				'label'         => __( 'Alignment', 'classic-city-core' ),
				'name'          => 'alignment',
				'type'          => 'select',
				'choices'       => array(
					'left'   => __( 'Left', 'classic-city-core' ),
					'center' => __( 'Center', 'classic-city-core' ),
				),
				'default_value' => 'center',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'How the inner content stack aligns horizontally. <strong>Left</strong> pins it to the left edge of the page&rsquo;s content-width; <strong>Center</strong> centers the narrow column (and button rows) inside the inner panel.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_framed_callout_height',
				'label'         => __( 'Height', 'classic-city-core' ),
				'name'          => 'height',
				'type'          => 'select',
				'choices'       => array(
					'auto' => __( 'Auto', 'classic-city-core' ),
					'50'   => '50vh',
					'60'   => '60vh',
					'70'   => '70vh',
					'80'   => '80vh',
					'90'   => '90vh',
					'100'  => '100vh',
				),
				'default_value' => 'auto',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( '<strong>Minimum</strong> height of the section. Numeric values are viewport-height (vh) and include the outer frame padding, so &ldquo;100vh&rdquo; means the whole section (frame + inner) is exactly one viewport tall.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_framed_callout_bg_image',
				'label'         => __( 'Background Image', 'classic-city-core' ),
				'name'          => 'bg_image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'instructions'  => __( 'Optional. Fills the inner panel behind the content (cover, center). Sits on top of the bg color/gradient picked from the block toolbar.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '100' ),
			),
			array(
				'key'               => 'field_framed_callout_bg_opacity',
				'label'             => __( 'Background Image Opacity', 'classic-city-core' ),
				'name'              => 'bg_opacity',
				'type'              => 'range',
				'default_value'     => 100,
				'min'               => 0,
				'max'               => 100,
				'step'              => 5,
				'append'            => '%',
				'instructions'      => __( 'Opacity of the background image over the bg color. Lower lets the color show through (e.g. a photo behind the brand color).', 'classic-city-core' ),
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_framed_callout_bg_image',
							'operator' => '!=empty',
						),
					),
				),
				'wrapper'           => array( 'width' => '100' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/framed-callout',
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
