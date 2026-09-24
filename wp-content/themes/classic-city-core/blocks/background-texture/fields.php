<?php
/**
 * ACF field group for the Background Texture block.
 *
 * Two fields:
 *   - texture_slug: select populated DYNAMICALLY from
 *     `ccc_get_registered_textures()` so the dropdown always tracks
 *     whatever the active child theme has declared under
 *     `settings.custom.textures` in theme.json.
 *   - arrangement: one of `bottom` / `top` / `middle`. Drives a
 *     modifier class on the block wrapper that the CSS uses to flip
 *     the absolute positioning + mask gradient direction.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_background_texture',
		'title'                 => __( 'Background Texture', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_background_texture_slug',
				'label'         => __( 'Texture', 'classic-city-core' ),
				'name'          => 'texture_slug',
				'type'          => 'select',
				/* Choices populated at runtime via the load_field filter
				   below — keeps the dropdown in sync with theme.json without
				   touching this file every time a child theme registers a
				   new texture. */
				'choices'       => array(),
				'default_value' => '',
				'allow_null'    => 1,
				'ui'            => 1,
				'instructions'  => __( 'Pick from textures registered in this child theme&rsquo;s <code>settings.custom.textures</code> in theme.json. If the list is empty, register one or more textures there first.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_background_texture_arrangement',
				'label'         => __( 'Arrangement', 'classic-city-core' ),
				'name'          => 'arrangement',
				'type'          => 'select',
				'choices'       => array(
					'bottom' => __( 'Bottom (texture rises up from the drop point)', 'classic-city-core' ),
					'top'    => __( 'Top (texture descends down from the drop point)', 'classic-city-core' ),
					'middle' => __( 'Middle (texture centers on the drop point, extends both ways)', 'classic-city-core' ),
				),
				'default_value' => 'bottom',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'How the texture is positioned relative to where this block sits in the document flow.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/background-texture',
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

/**
 * Populate the texture dropdown from the active theme's registered set.
 * Re-evaluated on every editor load so newly-registered textures appear
 * without theme rebuild.
 */
add_filter(
	'acf/load_field/key=field_background_texture_slug',
	function ( $field ) {
		if ( ! function_exists( 'ccc_get_registered_textures' ) ) {
			$field['choices'] = array( '' => '— Texture system unavailable —' );
			return $field;
		}
		$textures = ccc_get_registered_textures();
		$choices  = array();
		if ( empty( $textures ) ) {
			$choices[''] = '— No textures registered in theme.json —';
		} else {
			foreach ( $textures as $slug => $entry ) {
				$choices[ $slug ] = ucwords( str_replace( '-', ' ', $slug ) );
			}
		}
		$field['choices'] = $choices;
		return $field;
	}
);
