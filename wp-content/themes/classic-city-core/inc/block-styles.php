<?php
/**
 * Core block style registrations.
 *
 * These mirror the styles the Style Guide preview uses (per the original
 * Next.js→WP conversion plan, doc since retired). Visual CSS lives in
 * assets/blocks.css — this file only registers the slug ↔ label mapping
 * so the editor sidebar exposes them in the "Styles" panel.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ccc_register_block_styles() {

	// core/paragraph — eyebrow. Small caps lead-in used above headings in
	// hero + split blocks.
	register_block_style(
		'core/paragraph',
		array(
			'name'  => 'eyebrow',
			'label' => __( 'Eyebrow', 'classic-city-core' ),
		)
	);

	// core/quote — brand-colored icon glyph above the quote text (CSS rule
	// lives in assets/blocks.css under .is-style-quote).
	register_block_style(
		'core/quote',
		array(
			'name'  => 'quote',
			'label' => __( 'Quote', 'classic-city-core' ),
		)
	);

	// core/button — text (link-style) button. WP core ships only Fill and
	// Outline; this is the third rung of the button hierarchy, for tertiary
	// actions that shouldn't carry a button's visual weight. CSS rule lives in
	// assets/blocks.css under .is-style-text.
	register_block_style(
		'core/button',
		array(
			'name'  => 'text',
			'label' => __( 'Text', 'classic-city-core' ),
		)
	);

	// core/group + core/paragraph + core/heading — switch a run of content to
	// the ACCENT font role. The third role exists so a child theme can give
	// pull-quotes, letters, or highlighted runs a voice distinct from heading
	// and body; without a way to apply it, the role is a variable nothing reads.
	// CSS lives in assets/blocks.css under .is-style-accent-font.
	foreach ( array( 'core/group', 'core/paragraph', 'core/heading' ) as $ccc_accent_target ) {
		register_block_style(
			$ccc_accent_target,
			array(
				'name'  => 'accent-font',
				'label' => __( 'Accent Font', 'classic-city-core' ),
			)
		);
	}

	// core/group — background texture overlays.
	register_block_style(
		'core/group',
		array(
			'name'  => 'bg-texture',
			'label' => __( 'BG Texture', 'classic-city-core' ),
		)
	);

	register_block_style(
		'core/group',
		array(
			'name'  => 'bg-texture-sand',
			'label' => __( 'BG Texture (Sand)', 'classic-city-core' ),
		)
	);

	// core/group — "Section Panel": inset, rounded, shadowed full-width
	// section container. Set the panel color with the group's Background
	// control (which also pairs the text color). The visual rule lives in
	// blocks.css under `.wp-block-group.is-style-section`. Keeps page markup
	// free of inline container styling.
	register_block_style(
		'core/group',
		array(
			'name'  => 'section',
			'label' => __( 'Section Panel', 'classic-city-core' ),
		)
	);

	// core/image — drop-shadow style. Authors flip this on from the
	// editor sidebar's "Styles" panel; the visual rule (using the
	// theme's --wp--preset--shadow--md token) lives in blocks.css.
	register_block_style(
		'core/image',
		array(
			'name'  => 'shadow',
			'label' => __( 'Drop Shadow', 'classic-city-core' ),
		)
	);

	// classic-city-core/stats — two alternative treatments of the same
	// value+label repeater. Both are BAND styles: they strip the default card
	// (padding + shadow) off the stats root, because in both cases the
	// treatment lives on the individual cells and the band behind them belongs
	// to the section (content rule 17's "give it a background" is satisfied by
	// the section, not by the block's own shadow). CSS lives in blocks.css
	// under `.sg-block-stats.is-style-pods` / `.is-style-strip`.
	//
	//   pods  — translucent bordered pods on an auto-fit grid, sized by
	//           `--ccc-stats-pod-min` rather than a fixed column count, so 6
	//           figures reflow instead of ragging (rule 19 without asking the
	//           author to do the arithmetic). Values alternate between two
	//           accent slots.
	//   strip — hairline-separated cells, no gaps: one rule between neighbors
	//           and a border around the set. The quietest way to put four
	//           context figures under a heading without a second colored band
	//           (rule 15).
	register_block_style(
		'classic-city-core/stats',
		array(
			'name'  => 'pods',
			'label' => __( 'Pods', 'classic-city-core' ),
		)
	);

	register_block_style(
		'classic-city-core/stats',
		array(
			'name'  => 'strip',
			'label' => __( 'Strip', 'classic-city-core' ),
		)
	);

	// "Flush top" + "Overlap up" + "Underlap" — section-gap utilities.
	// Registered on the section containers (group/columns/cover) + every CCC
	// custom block so they're one click in the Styles panel (and usable as
	// plain `is-style-flush-top` / `is-style-overlap-up` / `is-style-underlap`
	// classes anywhere, including header/footer template parts). The CSS lives
	// in blocks.css; the overlap distance is the --wp--custom--overlap--* token,
	// shared by overlap-up and underlap so they stay in lockstep.
	//   flush-top  = two sections back-to-back (zero gap).
	//   overlap-up = pull this section up OVER the one above it (this on top).
	//   underlap   = pull this section up UNDER the one above it, lifting that
	//                previous sibling on top so it overlaps down onto this one
	//                (give the element above enough bottom padding).
	$gap_util_blocks = array_merge(
		array( 'core/group', 'core/columns', 'core/cover' ),
		array_map(
			static function ( $dir ) {
				return 'classic-city-core/' . basename( $dir );
			},
			glob( get_template_directory() . '/blocks/*', GLOB_ONLYDIR ) ?: array()
		)
	);
	foreach ( $gap_util_blocks as $gap_block ) {
		register_block_style( $gap_block, array( 'name' => 'flush-top',  'label' => __( 'Flush top', 'classic-city-core' ) ) );
		register_block_style( $gap_block, array( 'name' => 'overlap-up', 'label' => __( 'Overlap up', 'classic-city-core' ) ) );
		register_block_style( $gap_block, array( 'name' => 'underlap',   'label' => __( 'Underlap', 'classic-city-core' ) ) );
	}
}
add_action( 'init', 'ccc_register_block_styles' );
