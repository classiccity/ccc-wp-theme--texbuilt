<?php
/**
 * ACF field group for the Text Callout block.
 *
 * Width is an ACF field rather than WP's native alignment toolbar so the four
 * options are a closed, named set (and so `narrow` can be the default — the
 * toolbar has no way to express "narrower than content"). block.json therefore
 * declares NO align support; render.php emits the align classes itself. One
 * control, one source of truth.
 *
 * The WYSIWYG intentionally uses the `basic` toolbar: bold, italic and links
 * are the whole point (a child theme styles `p > strong` for its own emphasis
 * treatment), while headings and lists would fight the block's purpose.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_text_callout',
		'title'                 => __( 'Text Callout', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_text_callout_width',
				'label'         => __( 'Width', 'classic-city-core' ),
				'name'          => 'width',
				'type'          => 'select',
				'choices'       => array(
					'narrow'  => __( 'Narrow', 'classic-city-core' ),
					'content' => __( 'Content', 'classic-city-core' ),
					'wide'    => __( 'Wide', 'classic-city-core' ),
					'full'    => __( 'Full', 'classic-city-core' ),
				),
				'default_value' => 'narrow',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Narrow is the default and usually the right one — a short statement reads best on a short measure. Content matches the page width; Wide and Full break out of it.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '40' ),
			),
			array(
				'key'          => 'field_text_callout_content',
				'label'        => __( 'Content', 'classic-city-core' ),
				'name'         => 'content',
				'type'         => 'wysiwyg',
				'tabs'         => 'visual',
				'toolbar'      => 'basic',
				'media_upload' => 0,
				'delay'        => 1,
				'required'     => 1,
				'instructions' => __( 'One or two sentences. Bold the two or three words that carry the idea — each child theme gives bold runs its own emphasis treatment.', 'classic-city-core' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/text-callout',
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
