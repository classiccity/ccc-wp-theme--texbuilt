<?php
/**
 * ACF field group for the Hero — Gradient block.
 *
 * Four fields — everything else is InnerBlocks:
 *   - bg_color: palette slug applied as `has-{slug}-background-color` on
 *     the parent. Two purposes: (1) seeds the directional gradient overlay
 *     via an inline CSS var so the gradient matches the slug; (2) the
 *     parent's per-palette pair-helpers cascade text colors inside the
 *     hero to the slug's opposite automatically (heading + body read
 *     correctly without per-element color picking).
 *   - bg_image: uploaded image, dimmed to 70% opacity at render time so
 *     the palette background-color shows through ~30% everywhere.
 *   - content_width: 'content' (default — fills the standard 1200px
 *     content area) or 'narrow' (constrains to var(--wp--custom--layout
 *     --narrow-size) for shorter, more readable hero line lengths).
 *     Renders as a `content-width-narrow` modifier class.
 *   - content_alignment: 'left' (default) or 'center'. Renders as a
 *     `content-align-center` modifier class. Centers both the inner
 *     flex children AND inline text + button rows.
 *
 * The content stack — eyebrow paragraph, h1, body paragraph, button(s) —
 * is InnerBlocks. Authors compose it with native WP blocks; no field
 * plumbing for typography or copy. The is-style-eyebrow block style on
 * core/paragraph (registered in inc/block-styles.php) handles the
 * eyebrow treatment.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_hero_gradient',
		'title'                 => __( 'Hero — Gradient', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'           => 'field_hero_gradient_bg_color',
				'label'         => __( 'Background Color', 'classic-city-core' ),
				'name'          => 'bg_color',
				'type'          => 'select',
				'choices'       => function_exists( 'ccc_palette_slug_choices' ) ? ccc_palette_slug_choices() : array( '' => '— Default —' ),
				'default_value' => 'primary',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Palette slug applied via <code>has-{slug}-background-color</code> on the parent. Drives both the directional gradient overlay AND the text color cascade for content inside the hero (via the parent theme&rsquo;s palette pair-helpers).', 'classic-city-core' ),
			),
			array(
				'key'           => 'field_hero_gradient_bg_image',
				'label'         => __( 'Background Image', 'classic-city-core' ),
				'name'          => 'bg_image',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'large',
				'instructions'  => __( 'Background photo. Rendered at 70% opacity so the selected palette color shows through.', 'classic-city-core' ),
			),
			array(
				'key'           => 'field_hero_gradient_content_width',
				'label'         => __( 'Content Width', 'classic-city-core' ),
				'name'          => 'content_width',
				'type'          => 'select',
				'choices'       => array(
					'content' => __( 'Content size (default — fills the standard 1200px content area)', 'classic-city-core' ),
					'narrow'  => __( 'Narrow size (constrained for shorter line lengths)', 'classic-city-core' ),
				),
				'default_value' => 'content',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'Max-width of the inner content stack. <code>Narrow</code> caps it at <code>var(--wp--custom--layout--narrow-size)</code> so hero copy reads at a comfortable line length.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
			array(
				'key'           => 'field_hero_gradient_content_alignment',
				'label'         => __( 'Content Alignment', 'classic-city-core' ),
				'name'          => 'content_alignment',
				'type'          => 'select',
				'choices'       => array(
					'left'   => __( 'Left (default)', 'classic-city-core' ),
					'center' => __( 'Center', 'classic-city-core' ),
				),
				'default_value' => 'left',
				'allow_null'    => 0,
				'ui'            => 1,
				'instructions'  => __( 'How the content stack aligns horizontally inside the hero. <code>Center</code> centers both the wrapper itself (within whichever <code>Content Width</code> is chosen) AND text/button rows inside it.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '50' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/hero-gradient',
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
