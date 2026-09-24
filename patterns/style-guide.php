<?php
/**
 * Title: Style Guide
 * Slug: ccc/style-guide
 * Inserter: no
 *
 * Canonical style-guide pattern. Renders every block archetype the parent
 * theme ships so each child theme gets a living, testable sample page.
 *
 * Per-client override: if the active CHILD theme registers a pattern with
 * slug `sg-{stylesheet}/style-guide-content`, we render that instead. That
 * registration happens automatically when a child theme drops its own
 * `patterns/style-guide.php` using that slug + client-specific content.
 *
 * Content source: ported 1:1 from the Next.js BlockSamplesSection defaults so
 * the sample page matches what the preview app has always rendered.
 *
 * @package ClassicCityCore
 */

// The active stylesheet slug already starts with `sg-` for our child themes,
// so the pattern slug is just `{active_theme}/style-guide-content`.
$active_theme = get_stylesheet();
$child_slug   = "{$active_theme}/style-guide-content";
$registry     = \WP_Block_Patterns_Registry::get_instance();

if ( $registry->is_registered( $child_slug ) ) {
	$child = $registry->get_registered( $child_slug );
	echo $child['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — trusted theme content.
	return;
}

// Helpers are guarded against redeclare because WP's pattern-registration
// pipeline includes this file once, and a child theme's seed-demo pattern
// includes this file again (to defer to canonical content). First include
// wins; subsequent includes reuse these definitions.

if ( ! function_exists( 'ccc_sg_acf_block' ) ) {
	/**
	 * Build an ACF block comment with the required `{"data":{field,_field,...}}`
	 * shape. Flat fields only — repeaters use ccc_sg_repeater_data().
	 */
	function ccc_sg_acf_block( $block_name, $fields, $keys, $extra_attrs = array(), $inner = '' ) {
		$data = array();
		foreach ( $fields as $k => $v ) {
			$data[ $k ]        = $v;
			$data[ '_' . $k ]  = $keys[ $k ] ?? '';
		}
		$attrs = array_merge( array( 'data' => $data ), $extra_attrs );
		$json  = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES );
		if ( $inner === '' ) {
			return "<!-- wp:{$block_name} {$json} /-->\n";
		}
		return "<!-- wp:{$block_name} {$json} -->\n{$inner}\n<!-- /wp:{$block_name} -->\n";
	}
}

if ( ! function_exists( 'ccc_sg_repeater_data' ) ) {
	/**
	 * Flatten a repeater's rows into ACF's `name_N_subfield` + `_name_N_subfield`
	 * key-value pairs, plus the parent counter `name`/`_name`.
	 */
	function ccc_sg_repeater_data( $repeater_name, $repeater_key, $rows, $sub_keys ) {
		$out = array(
			$repeater_name          => count( $rows ),
			'_' . $repeater_name    => $repeater_key,
		);
		foreach ( $rows as $i => $row ) {
			foreach ( $row as $sub_name => $sub_val ) {
				$flat_name              = "{$repeater_name}_{$i}_{$sub_name}";
				$out[ $flat_name ]      = $sub_val;
				$out[ '_' . $flat_name ] = $sub_keys[ $sub_name ] ?? '';
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'ccc_sg_inner_heading_body_cta' ) ) {
	/** heading + paragraph + CTA button group — typical Pattern A inner content. */
	function ccc_sg_inner_heading_body_cta( $headline, $body, $cta_label = 'Get started', $secondary_cta = '' ) {
		$h = '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">' . esc_html( $headline ) . '</h1><!-- /wp:heading -->';
		$p = '<!-- wp:paragraph --><p>' . esc_html( $body ) . '</p><!-- /wp:paragraph -->';
		$buttons_inner  = '<!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">' . esc_html( $cta_label ) . '</a></div><!-- /wp:button -->';
		if ( $secondary_cta ) {
			// textColor:primary gives the per-color hover rule a `has-primary-color`
			// handle so hover swaps text primary → primary-alt.
			$buttons_inner .= '<!-- wp:button {"textColor":"primary","className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-primary-color has-text-color wp-element-button">' . esc_html( $secondary_cta ) . '</a></div><!-- /wp:button -->';
		}
		$buttons = '<!-- wp:buttons --><div class="wp-block-buttons">' . $buttons_inner . '</div><!-- /wp:buttons -->';
		return $h . $p . $buttons;
	}
}

if ( ! function_exists( 'ccc_sg_block_descriptor' ) ) {
	/**
	 * Render a styled descriptor card immediately above a block on /style-guide.
	 *
	 * The card carries:
	 *  - The block's display name (pulled from its block.json `title`, or a
	 *    `$name_override` if supplied — useful for core/* blocks or for
	 *    surfacing a sample variant name).
	 *  - A "kind" pill: Custom (pure ACF block), Hybrid (Custom outer +
	 *    Gutenberg InnerBlocks), or Gutenberg (core block, possibly with a
	 *    custom block style).
	 *  - The block.json `description` (the short "what is this?" line).
	 *  - Author-supplied usage text (HTML allowed via wp_kses_post) that
	 *    spells out the key fields/settings and how to actually wire it up.
	 *
	 * All CSS lives in assets/blocks.css under `.sg-block-descriptor`.
	 * Rendering is intentionally a plain wp:group block so the editor
	 * preview matches the front end one-to-one.
	 *
	 * @param string $slug          Block slug. Bare slug (e.g. 'hero') resolves to
	 *                              classic-city-core/{slug}. Pass 'core/quote' or
	 *                              other vendor/* untouched.
	 * @param string $kind          One of 'Custom' | 'Hybrid' | 'Gutenberg'.
	 * @param string $usage_html    Settings & usage guidance. HTML allowed.
	 * @param string $name_override Optional display-name override.
	 */
	function ccc_sg_block_descriptor( $slug, $kind, $usage_html, $name_override = '' ) {
		// Resolve the block manifest to pull title + description from the
		// canonical source. For core blocks we just synthesize a reasonable
		// title since core blocks live in WP itself, not this theme.
		$title = $name_override;
		$desc  = '';

		if ( strpos( $slug, '/' ) === false ) {
			// Treat as a classic-city-core block.
			$manifest_path = trailingslashit( get_template_directory() ) . 'blocks/' . $slug . '/block.json';
			if ( file_exists( $manifest_path ) ) {
				$json = json_decode( file_get_contents( $manifest_path ), true );
				if ( is_array( $json ) ) {
					$title = $title ?: ( $json['title'] ?? $slug );
					$desc  = $json['description'] ?? '';
				}
			}
			$title = $title ?: ucwords( str_replace( '-', ' ', $slug ) );
		} else {
			// vendor/slug — typically core/*.
			$title = $title ?: ucwords( str_replace( array( '-', '/' ), array( ' ', ' / ' ), $slug ) );
		}

		$kind_lower = strtolower( $kind );
		$kind_class = 'sg-block-descriptor__kind--' . sanitize_html_class( $kind_lower );
		$wrap_class = 'sg-block-descriptor sg-block-descriptor--' . sanitize_html_class( $kind_lower );

		$html  = '<!-- wp:group {"align":"wide","className":"' . esc_attr( $wrap_class ) . '","layout":{"type":"constrained"}} -->';
		$html .= '<div class="wp-block-group alignwide ' . esc_attr( $wrap_class ) . '">';
		$html .= '<div class="sg-block-descriptor__head">';
		$html .= '<h3 class="sg-block-descriptor__name">' . esc_html( $title ) . '</h3>';
		$html .= '<span class="sg-block-descriptor__kind ' . esc_attr( $kind_class ) . '">' . esc_html( $kind ) . '</span>';
		$html .= '</div>';
		if ( $desc ) {
			$html .= '<p class="sg-block-descriptor__what">' . esc_html( $desc ) . '</p>';
		}
		$html .= '<div class="sg-block-descriptor__how">' . wp_kses_post( $usage_html ) . '</div>';
		$html .= '</div>';
		$html .= '<!-- /wp:group -->' . "\n";

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — internally escaped above.
	}
}

// ==========================================================================
// Canonical content — sourced from Next.js BlockSamplesSection.tsx defaults.
// ==========================================================================
?>

<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);">
	<!-- wp:heading {"level":1} -->
	<h1 class="wp-block-heading">Style Guide</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size">Living reference for every block and token this theme exposes. Use it to spot-check the brand in context and to demo layouts to clients.</p>
	<!-- /wp:paragraph -->
</section>
<!-- /wp:group -->

<!-- ────────────────────────────────────────────────────────────────── -->
<!-- Typography samples -->

<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);">
	<!-- wp:heading --><h2 class="wp-block-heading">Typography</h2><!-- /wp:heading -->
	<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">H1 Heading</h1><!-- /wp:heading -->
	<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">H2 Heading</h2><!-- /wp:heading -->
	<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">H3 Heading</h3><!-- /wp:heading -->
	<!-- wp:heading {"level":4} --><h4 class="wp-block-heading">H4 Heading</h4><!-- /wp:heading -->
	<!-- wp:heading {"level":5} --><h5 class="wp-block-heading">H5 Heading</h5><!-- /wp:heading -->
	<!-- wp:heading {"level":6} --><h6 class="wp-block-heading">H6 Heading</h6><!-- /wp:heading -->
	<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">Eyebrow kicker</p><!-- /wp:paragraph -->
	<!-- wp:paragraph --><p>Default body paragraph. Two sentences that introduce the business and make the reader want to learn more. Keep it direct, benefit-oriented, and human.</p><!-- /wp:paragraph -->
	<!-- wp:paragraph {"fontSize":"small"} --><p class="has-small-font-size">Small body — for captions, disclaimers, metadata.</p><!-- /wp:paragraph -->
</section>
<!-- /wp:group -->

<!-- ────────────────────────────────────────────────────────────────── -->
<!-- Buttons -->

<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<section class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);">
	<!-- wp:heading --><h2 class="wp-block-heading">Buttons</h2><!-- /wp:heading -->
	<!-- wp:buttons --><div class="wp-block-buttons">
		<!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Primary CTA</a></div><!-- /wp:button -->
		<!-- wp:button {"backgroundColor":"primary"} --><div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button">Secondary</a></div><!-- /wp:button -->
		<!-- wp:button {"textColor":"primary","className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-primary-color has-text-color wp-element-button">Outline</a></div><!-- /wp:button -->
	</div><!-- /wp:buttons -->
</section>
<!-- /wp:group -->

<!-- ────────────────────────────────────────────────────────────────── -->
<!-- HERO (Pattern A — InnerBlocks + ACF image/image_side) -->

<?php
ccc_sg_block_descriptor(
	'hero',
	'Hybrid',
	'Pick <code>image_side</code> (left or right). Author the headline, body, and buttons as Inner Blocks on the content side. Mobile stacks image below content.'
);
echo ccc_sg_acf_block(
	'classic-city-core/hero',
	array( 'image' => 0, 'image_side' => 'right' ),
	array( 'image' => 'field_hero_image', 'image_side' => 'field_hero_image_side' ),
	array(),
	ccc_sg_inner_heading_body_cta(
		'A confident headline that sets the tone',
		'Two sentences that introduce the business and make the reader want to learn more. Keep it direct, benefit-oriented, and human.',
		'Get started',
		'Learn more'
	)
);

// ──────────────────────────────────────────────────────────────────
// HERO: FULL IMAGE (Pattern A)
ccc_sg_block_descriptor(
	'hero-full-image',
	'Hybrid',
	'Set a background <code>image</code> (or video URL) and <code>title_html</code> — wrap emphasized words in <code>&lt;strong&gt;</code>. Inner Blocks (paragraph + buttons) render inside a centered card over the image with an auto-generated gradient overlay.'
);
echo ccc_sg_acf_block(
	'classic-city-core/hero-full-image',
	array( 'image' => 0, 'title_html' => 'We build what your community <strong>counts on</strong>' ),
	array( 'image' => 'field_hero_full_image', 'title_html' => 'field_hero_full_title_html' ),
	array( 'align' => 'full' ),
	'<!-- wp:paragraph --><p>A short supporting paragraph that reinforces the headline and gives the reader enough context to keep scrolling. Two or three sentences is plenty.</p><!-- /wp:paragraph -->'
	. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Get started</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
);

// ──────────────────────────────────────────────────────────────────
// HERO: GRADIENT (Pattern A — has-{slug}-bg-color on parent +
// dimmed image + directional palette gradient + InnerBlocks content)
ccc_sg_block_descriptor(
	'hero-gradient',
	'Hybrid',
	'Set <code>bg_color</code> (palette slug — drives both the parent&rsquo;s <code>has-{slug}-background-color</code> for the text-color cascade AND the directional gradient overlay color) and <code>bg_image</code>. The image renders at 70% opacity so the palette color shows through. Content stack is Inner Blocks — typical: eyebrow paragraph + h1 + body paragraph + CTA button. Gradient direction is locked top-right (transparent) → bottom-left (palette slug) so text in the bottom-left reads cleanly.'
);
echo ccc_sg_acf_block(
	'classic-city-core/hero-gradient',
	array( 'bg_color' => 'primary', 'bg_image' => 0 ),
	array( 'bg_color' => 'field_hero_gradient_bg_color', 'bg_image' => 'field_hero_gradient_bg_image' ),
	array( 'align' => 'full' ),
	'<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">Built for the way you live</p><!-- /wp:paragraph -->'
	. '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Decking that fits your home.</h1><!-- /wp:heading -->'
	. '<!-- wp:paragraph --><p>Whether you&rsquo;re on the coast, in a historic district, up in the mountains, or in a quiet suburb, there&rsquo;s a Veranda collection built to match. Find yours below.</p><!-- /wp:paragraph -->'
	. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Find your deck</a></div><!-- /wp:button --><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button">Request samples</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
);

// ──────────────────────────────────────────────────────────────────
// IMAGE HERO 50/50 (Pattern A + native bg color/gradient)
ccc_sg_block_descriptor(
	'image-hero-50-50',
	'Hybrid',
	'Set the image. Pick the content-side palette via the block&rsquo;s native <code>backgroundColor</code> or <code>gradient</code> color panel. Inner Blocks (heading + paragraph + buttons) sit on the colored half. Mobile stacks.'
);
echo ccc_sg_acf_block(
	'classic-city-core/image-hero-50-50',
	array( 'image' => 0, 'image_side' => 'left', 'has_texture' => 1 ),
	array( 'image' => 'field_image_hero_image', 'image_side' => 'field_image_hero_image_side', 'has_texture' => 'field_image_hero_has_texture' ),
	array( 'align' => 'full', 'gradient' => 'secondary' ),
	'<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">New collection</p><!-- /wp:paragraph -->'
	. ccc_sg_inner_heading_body_cta(
		'Built to outlast the elements',
		'Composite decking that stands up to salt, sun, and seasons of foot traffic — backed by a ten-year warranty and zero upkeep.',
		'Get a quote'
	)
);

// ──────────────────────────────────────────────────────────────────
// HERO 3 UP (Pattern B — fully field-driven; no Inner Blocks)
ccc_sg_block_descriptor(
	'hero-3-up',
	'Custom',
	'Page hero with a 2/3 + 1/3 intro row (eyebrow + h1 on the left; description + button on the right) and a full-width hero image below. All fields: <code>title</code>, <code>eyebrow</code>, <code>description</code>, <code>button_text</code>, <code>button_url</code>, <code>image_aspect_ratio</code> (6 ratios + <code>none</code>), <code>image</code>. Wrapper carries <code>.sg-hero</code> (shared page-hero marker, no CSS attached — see CLAUDE.md) plus the variant-specific <code>.sg-block-hero-3-up</code>. Mobile stacks the intro row to a single column.'
);
echo ccc_sg_acf_block(
	'classic-city-core/hero-3-up',
	array(
		'title'              => 'Built to last where everything else fails.',
		'eyebrow'            => 'MARINE GRADE',
		'description'        => 'Lumberock is engineered for full immersion — saltwater, freeze-thaw, UV, marine organisms. The kind of conditions that destroy lesser composites and rot every species of wood.',
		'button_text'        => 'Request sample box',
		'button_url'         => '#samples',
		'image_aspect_ratio' => '16/9',
		'image'              => 0,
	),
	array(
		'title'              => 'field_hero_3_up_title',
		'eyebrow'            => 'field_hero_3_up_eyebrow',
		'description'        => 'field_hero_3_up_description',
		'button_text'        => 'field_hero_3_up_button_text',
		'button_url'         => 'field_hero_3_up_button_url',
		'image_aspect_ratio' => 'field_hero_3_up_image_aspect_ratio',
		'image'              => 'field_hero_3_up_image',
	),
	array( 'align' => 'full' )
);

// ──────────────────────────────────────────────────────────────────
// FEATURE DETAIL (Pattern B — wraps the shared partial)
ccc_sg_block_descriptor(
	'feature-detail',
	'Custom',
	'Standalone single-panel version of one Product Feature Toggles tab. Wraps the shared <code>partials/feature-detail-section.php</code> partial — identical inner shape to one PFT tab&rsquo;s content panel. Authors get a 2-column layout: image with floating caption card on one side, headline + description + detail rows + optional CTA button on the other. No block-head (eyebrow/heading above the panel) — wrap the block in a <code>wp:group</code> with your own section header if you want one. Per-instance accent color drives the colored left bar + icon tint on each detail row via the <code>detail_color</code> palette slug.'
);
echo ccc_sg_acf_block(
	'classic-city-core/feature-detail',
	array(
		'image'           => 0,
		'image_subtitle'  => 'WHAT MARINE GRADE MEANS',
		'image_title'     => 'Built to Last',
		'headline'        => 'Engineered for full immersion',
		'description'     => 'Composite decking that stays structurally sound and visually identical through decades of salt, sun, freeze-thaw, and marine organisms. Built specifically for the conditions other materials lose to.',
		'button_text'     => 'See the science',
		'button_url'      => '#science',
		'detail_color'    => 'secondary',
		'detail_rows'     => 4,
		'detail_rows_0_label'       => 'Water & Moisture',
		'detail_rows_0_icon_name'   => 'droplet',
		'detail_rows_0_description' => 'Stands up to warping, rotting, and swelling under continuous water exposure.',
		'detail_rows_1_label'       => 'UV Exposure',
		'detail_rows_1_icon_name'   => 'sun',
		'detail_rows_1_description' => 'Resists fading and degradation from direct sun across seasons.',
		'detail_rows_2_label'       => 'Harsh Weather',
		'detail_rows_2_icon_name'   => 'cloud-rain',
		'detail_rows_2_description' => 'Withstands heavy rain, saltwater, freeze-thaw cycles, and extreme temperatures.',
		'detail_rows_3_label'       => 'Marine Organisms',
		'detail_rows_3_icon_name'   => 'shield-check',
		'detail_rows_3_description' => 'Impervious to mold, mildew, and insect damage.',
	),
	array(
		'image'           => 'field_feature_detail_image',
		'image_subtitle'  => 'field_feature_detail_image_subtitle',
		'image_title'     => 'field_feature_detail_image_title',
		'headline'        => 'field_feature_detail_headline',
		'description'     => 'field_feature_detail_description',
		'button_text'     => 'field_feature_detail_button_text',
		'button_url'      => 'field_feature_detail_button_url',
		'detail_color'    => 'field_feature_detail_detail_color',
		'detail_rows'     => 'field_feature_detail_rows',
		'detail_rows_0_label'       => 'field_feature_detail_row_label',
		'detail_rows_0_icon_name'   => 'field_feature_detail_row_icon_name',
		'detail_rows_0_description' => 'field_feature_detail_row_description',
		'detail_rows_1_label'       => 'field_feature_detail_row_label',
		'detail_rows_1_icon_name'   => 'field_feature_detail_row_icon_name',
		'detail_rows_1_description' => 'field_feature_detail_row_description',
		'detail_rows_2_label'       => 'field_feature_detail_row_label',
		'detail_rows_2_icon_name'   => 'field_feature_detail_row_icon_name',
		'detail_rows_2_description' => 'field_feature_detail_row_description',
		'detail_rows_3_label'       => 'field_feature_detail_row_label',
		'detail_rows_3_icon_name'   => 'field_feature_detail_row_icon_name',
		'detail_rows_3_description' => 'field_feature_detail_row_description',
	),
	array( 'align' => 'wide' )
);

// ──────────────────────────────────────────────────────────────────
// IMAGE COLUMNS (Pattern B — repeater of 3 cards)
ccc_sg_block_descriptor(
	'image-columns',
	'Custom',
	'Configure 1–N cards in the <code>cards</code> repeater. Each card has an Image, Title, Body, optional Link, and per-card <code>image_aspect_ratio</code>. Renders as a responsive grid.'
);
$image_columns_data = array_merge(
	array(
		'desktop_columns' => 3,
		'_desktop_columns' => 'field_image_columns_desktop_columns',
		'cta_color'       => 'cta',
		'_cta_color'      => 'field_image_columns_cta_color',
	),
	ccc_sg_repeater_data(
		'columns',
		'field_image_columns_items',
		array(
			array( 'image' => 0, 'heading' => 'Precision engineering', 'body' => 'Built for studios that need repeatable, pixel-accurate results on every run.', 'cta_text' => 'Learn more', 'cta_url' => '#' ),
			array( 'image' => 0, 'heading' => 'Production ready',       'body' => 'Configurable workflows that scale from one-off prototypes to full production batches.', 'cta_text' => 'Learn more', 'cta_url' => '#' ),
			array( 'image' => 0, 'heading' => 'Supported by experts',   'body' => 'Direct access to a team that knows your equipment and your workflow inside out.', 'cta_text' => 'Learn more', 'cta_url' => '#' ),
		),
		array(
			'image'    => 'field_image_columns_image',
			'heading'  => 'field_image_columns_heading',
			'body'     => 'field_image_columns_body',
			'cta_text' => 'field_image_columns_cta_text',
			'cta_url'  => 'field_image_columns_cta_url',
		)
	)
);
echo '<!-- wp:classic-city-core/image-columns ' . wp_json_encode( array( 'data' => $image_columns_data ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// STATS (Pattern B)
ccc_sg_block_descriptor(
	'stats',
	'Custom',
	'Wrapper takes a palette <code>gradient</code> (slug like <code>secondary</code>). Add <code>stats</code> rows with number + label + optional body. Renders 3-up or 4-up by viewport.'
);
$stats_data = array_merge(
	array( 'desktop_columns' => 4, '_desktop_columns' => 'field_stats_desktop_columns' ),
	ccc_sg_repeater_data(
		'stats',
		'field_stats_items',
		array(
			array( 'value' => '12k+', 'label' => 'Projects shipped' ),
			array( 'value' => '98%',  'label' => 'Customer satisfaction' ),
			array( 'value' => '24/7', 'label' => 'Expert support' ),
			array( 'value' => '15yr', 'label' => 'Industry experience' ),
		),
		array( 'value' => 'field_stats_value', 'label' => 'field_stats_label' )
	)
);
echo '<!-- wp:classic-city-core/stats ' . wp_json_encode( array( 'data' => $stats_data, 'gradient' => 'secondary' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// STATS — is-style-pods
// Same block, same fields, different treatment. Sampled at SIX stats to show
// the reason the style exists: the auto-fit grid reflows by pod width, so a
// count that no column number divides cleanly still fills its rows (rule 19,
// solved by the layout instead of by the editor). The style strips the block's
// own card, so the sample sits inside an `ink` group — that band is what
// satisfies rule 17, and it's what the translucent currentColor pods are
// mixing against.
ccc_sg_block_descriptor(
	'stats',
	'Custom',
	'Block style <code>is-style-pods</code> — translucent bordered pods on an auto-fit grid (<code>--ccc-stats-pod-min</code>, default 10rem), values alternating between two accent slots (<code>--ccc-stats-accent-a</code> / <code>-b</code>, default <code>cta</code> and <code>currentColor</code>). Pod fill and border are <code>currentColor</code> mixes, so the one rule works on a dark band and a light one. The style strips the block&rsquo;s own padding and shadow: the band belongs to the SECTION, which is why the sample below is wrapped in an <code>ink</code> group. Reach for it at five or six figures, and when the labels run to a short sentence rather than two words.',
	'Stats — Pods'
);
$stats_pods_data = array_merge(
	array( 'desktop_columns' => 3, '_desktop_columns' => 'field_stats_desktop_columns' ),
	ccc_sg_repeater_data(
		'stats',
		'field_stats_items',
		array(
			array( 'value' => '22',      'label' => 'applications, generated within 135 days' ),
			array( 'value' => '183%',    'label' => 'of the annual expectation — 22 against 12 anticipated over 12 months' ),
			array( 'value' => '83%',     'label' => 'more applications than anticipated — ten above the original expectation' ),
			array( 'value' => '73%',     'label' => 'of people who completed the self-reflection tools went on to apply' ),
			array( 'value' => '281',     'label' => 'visitors from 79 locations — broad international reach' ),
			array( 'value' => '~2 days', 'label' => 'response time after an application reached the study site' ),
		),
		array( 'value' => 'field_stats_value', 'label' => 'field_stats_label' )
	)
);
// `is-style-section` carries the radius + shadow (content rule 5 — never inline
// them); the padding has to be a real inline style because core/group is a
// static block and does not re-serialize spacing attributes on the front end
// (content rule 8's gotcha).
echo '<!-- wp:group {"backgroundColor":"ink","align":"wide","className":"is-style-section","layout":{"type":"constrained"}} -->' . "\n";
echo '<div class="wp-block-group alignwide is-style-section has-ink-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);">' . "\n";
echo '<!-- wp:classic-city-core/stats ' . wp_json_encode( array( 'data' => $stats_pods_data, 'className' => 'is-style-pods' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";
echo '</div>' . "\n" . '<!-- /wp:group -->' . "\n";

// ──────────────────────────────────────────────────────────────────
// STATS — is-style-strip
// The quiet variant: no band of its own, so it can follow a colored section
// without breaking rule 15. Four cells, four columns — the count and the
// column number match (rule 19).
ccc_sg_block_descriptor(
	'stats',
	'Custom',
	'Block style <code>is-style-strip</code> — hairline-separated cells with no gaps: one rule between neighbors (<code>--ccc-stats-cell-rule-color</code>, a <code>currentColor</code> mix) and one border around the set, clipped to the radius. No accent cycle, no shadow, no background of its own, which is what makes it safe directly under another colored band (rule 15). Below the phone breakpoint the cells stack and the hairline moves from the inline edge to the block edge.',
	'Stats — Strip'
);
$stats_strip_data = array_merge(
	array( 'desktop_columns' => 4, '_desktop_columns' => 'field_stats_desktop_columns' ),
	ccc_sg_repeater_data(
		'stats',
		'field_stats_items',
		array(
			array( 'value' => '80%',         'label' => 'of clinical trials miss enrollment timelines' ),
			array( 'value' => '~30%',        'label' => 'of screened candidates fail on eligibility, industry-wide' ),
			array( 'value' => '$75k–$150k',  'label' => 'the cost of a single late-phase dropout' ),
			array( 'value' => 'Under 2 min', 'label' => 'average visit on trial-finder platforms — the journey ends before the decision' ),
		),
		array( 'value' => 'field_stats_value', 'label' => 'field_stats_label' )
	)
);
echo '<!-- wp:classic-city-core/stats ' . wp_json_encode( array( 'data' => $stats_strip_data, 'className' => 'is-style-strip', 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// CARD WITH DETAIL ROWS
ccc_sg_block_descriptor(
	'card-detail-rows',
	'Custom',
	'Each <code>cards</code> row holds an eyebrow, heading, a nested <code>rows</code> repeater of label/value pairs, a WYSIWYG <code>description</code>, and an optional button (<code>fill</code> / <code>outline</code> / <code>text</code>) that pins to the card bottom. The block-level color picker paints each CARD, not the grid; the card border defaults to that color&rsquo;s palette partner &mdash; here <code>panel</code>, so the border resolves from the default token since <code>panel</code> is unpaired. Sampled below at 3 columns with a mixed set: full card, no-rows card, no-button card.'
);
$cdr_cards = array(
	array(
		'eyebrow'      => 'Case study 2 · from application to enrollment',
		'heading'      => 'Quality holds at every step.',
		'rows'         => array(
			array( 'label' => 'Applications',                        'value' => '19' ),
			array( 'label' => 'Screened at site (79% of applications)', 'value' => '15' ),
			array( 'label' => 'Enrolled to date (53% of screened)',   'value' => '8' ),
		),
		'description'  => '<p>Fast, high-quality flow within weeks of one community going live. Average self-reported likelihood of completing: <strong>91%</strong>.</p>',
		'button_text'  => 'Read the study',
		'button_link'  => '#',
		'button_style' => 'fill',
	),
	array(
		'eyebrow'      => 'Speed and trust',
		'heading'      => 'Live in a morning, first result by nightfall.',
		'rows'         => array(
			array( 'label' => 'Time to go live',      'value' => '30 minutes' ),
			array( 'label' => 'First application',    'value' => 'within 18 hours' ),
			array( 'label' => 'Countries searching',  'value' => '26' ),
			array( 'label' => 'Searches this year',   'value' => '5,000+' ),
		),
		'description'  => '<p>Engagement was entirely organic from day one &mdash; no campaign, no amplification.</p>',
		'button_text'  => 'See how it works',
		'button_link'  => '#',
		'button_style' => 'outline',
	),
	array(
		'eyebrow'      => 'The cost of deciding late',
		'heading'      => 'Every language, no noise.',
		'rows'         => array(),
		'description'  => '<p>Embedded in a global, multi-language community, translated study pages reached people in 46 languages. The hub was simply placed where people already are, and existing interest became action.</p><p>No campaign. No amplification. No social posts.</p>',
		'button_text'  => 'Read the full story',
		'button_link'  => '#',
		'button_style' => 'text',
	),
);
$cdr_card_keys = array(
	'eyebrow'      => 'field_card_detail_rows_eyebrow',
	'heading'      => 'field_card_detail_rows_heading',
	'description'  => 'field_card_detail_rows_description',
	'button_text'  => 'field_card_detail_rows_button_text',
	'button_link'  => 'field_card_detail_rows_button_link',
	'button_style' => 'field_card_detail_rows_button_style',
);
$cdr_row_keys = array(
	'label' => 'field_card_detail_rows_row_label',
	'value' => 'field_card_detail_rows_row_value',
);
$cdr_data = array(
	'desktop_columns'  => 3,
	'_desktop_columns' => 'field_card_detail_rows_desktop_columns',
	'mobile_layout'    => 'stack',
	'_mobile_layout'   => 'field_card_detail_rows_mobile_layout',
	'cards'            => count( $cdr_cards ),
	'_cards'           => 'field_card_detail_rows_cards',
);
// Nested repeater — ccc_sg_repeater_data() only flattens one level, so the
// inner `rows` are flattened by hand (same shape as the PFT sample below).
foreach ( $cdr_cards as $cdr_i => $cdr_card ) {
	foreach ( $cdr_card as $sub_name => $sub_val ) {
		if ( 'rows' === $sub_name ) {
			continue;
		}
		$flat                      = "cards_{$cdr_i}_{$sub_name}";
		$cdr_data[ $flat ]         = $sub_val;
		$cdr_data[ '_' . $flat ]   = $cdr_card_keys[ $sub_name ] ?? '';
	}
	$cdr_data[ "cards_{$cdr_i}_rows" ]  = count( $cdr_card['rows'] );
	$cdr_data[ "_cards_{$cdr_i}_rows" ] = 'field_card_detail_rows_rows';
	foreach ( $cdr_card['rows'] as $cdr_j => $cdr_row ) {
		foreach ( $cdr_row as $r_name => $r_val ) {
			$flat                    = "cards_{$cdr_i}_rows_{$cdr_j}_{$r_name}";
			$cdr_data[ $flat ]       = $r_val;
			$cdr_data[ '_' . $flat ] = $cdr_row_keys[ $r_name ] ?? '';
		}
	}
}
echo '<!-- wp:classic-city-core/card-detail-rows ' . wp_json_encode( array( 'data' => $cdr_data, 'backgroundColor' => 'panel', 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// TEXT CALLOUT
ccc_sg_block_descriptor(
	'text-callout',
	'Custom',
	'Two fields: <code>width</code> (<code>narrow</code> default / <code>content</code> / <code>wide</code> / <code>full</code>) and <code>content</code> (WYSIWYG, basic toolbar). Outputs a bare <code>&lt;div&gt;</code> around the paragraph &mdash; no card, no background. The parent sets size, measure and spacing only; each CHILD theme styles <code>p &gt; strong</code> for its own emphasis treatment and uses the div for <code>::before</code>/<code>::after</code> flourishes, so the same block reads differently per site. Reserves <code>spacing--60</code> above itself AND above the next block (override with <code>--ccc-tc-space</code>). Sampled at <code>narrow</code>; bold runs below show the active child theme&rsquo;s treatment.'
);
echo '<!-- wp:classic-city-core/text-callout ' . wp_json_encode( array(
	'data' => array(
		'width'    => 'narrow',
		'_width'   => 'field_text_callout_width',
		'content'  => '<p>We never treat an audience as a number to convert. The tools exist so people can <strong>step away</strong> from what is not right for them as confidently as they <strong>step toward</strong> what is.</p>',
		'_content' => 'field_text_callout_content',
	),
), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// CHART: HORIZONTAL BARS
ccc_sg_block_descriptor(
	'chart-horizontal-bars',
	'Custom',
	'Fields: <code>title</code>, <code>description</code> (WYSIWYG), a <code>bars</code> repeater of <code>label</code> / <code>value</code> / <code>is_highlight</code>, and a <code>footnote</code> (WYSIWYG). Bar widths are computed server-side as each value&rsquo;s share of the total &mdash; enter real figures, never percentages, and no percentage is printed. A flagged row gets <code>.is-highlighted</code>, which paints its bar CTA by default; repaint via <code>--ccc-chb-fill</code> rather than by out-specifying selectors. Sampled on <code>primary</code>, so the card border auto-derives to <code>primary-alt</code> &mdash; compare with Card with Detail Rows above, sampled on the unpaired <code>panel</code>, which falls back to the default border token.'
);
$chb_bars = array(
	array( 'label' => 'United States',  'value' => 205, 'is_highlight' => 1 ),
	array( 'label' => 'United Kingdom', 'value' => 41,  'is_highlight' => 0 ),
	array( 'label' => 'Europe',         'value' => 23,  'is_highlight' => 0 ),
	array( 'label' => 'Australia',      'value' => 12,  'is_highlight' => 0 ),
);
$chb_data = array_merge(
	array(
		'title'        => 'Global reach across 79 locations',
		'_title'       => 'field_chart_horizontal_bars_title',
		'description'  => '<p>Where the 281 visitors to this study&rsquo;s hub came from, over the full support period.</p>',
		'_description' => 'field_chart_horizontal_bars_description',
		'footnote'     => '<p>Directional figures from a single study; totals exclude sessions with no resolved location.</p>',
		'_footnote'    => 'field_chart_horizontal_bars_footnote',
	),
	ccc_sg_repeater_data(
		'bars',
		'field_chart_horizontal_bars_bars',
		$chb_bars,
		array(
			'label'        => 'field_chart_horizontal_bars_label',
			'value'        => 'field_chart_horizontal_bars_value',
			'is_highlight' => 'field_chart_horizontal_bars_is_highlight',
		)
	)
);
echo '<!-- wp:classic-city-core/chart-horizontal-bars ' . wp_json_encode( array( 'data' => $chb_data, 'backgroundColor' => 'primary', 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// COMPARISON TABLE
// Sampled with the third row's caveat ("46, in a range of 22–56") moved into
// the footnote on purpose: leaving it in the value field would make the two
// sides' unit residues differ, and the block would drop that row's bars rather
// than draw a comparison it can't verify. The sample is the block's own rule,
// followed.
ccc_sg_block_descriptor(
	'comparison-table',
	'Custom',
	'Fields: <code>title</code>, <code>description</code> (WYSIWYG), <code>our_label</code> / <code>their_label</code> (declared once, repeated on every row), a <code>rows</code> repeater of <code>label</code> / <code>our_value</code> / <code>their_value</code>, and a <code>footnote</code>. Bar lengths are parsed out of the TEXT values and scaled per ROW &mdash; the larger of the pair fills its track &mdash; so metrics in different units share a card without pretending they add up. Two guards suppress the bars rather than draw a wrong one: both sides must reduce to the same non-numeric residue, and the larger must be positive. The &ldquo;ours&rdquo; bar takes <code>cta</code>; retune via <code>--ccc-ct-fill</code> per row and <code>--ccc-ct-fill-muted</code>, not by out-specifying selectors. Compare with Chart: Horizontal Bars above, which sizes bars as a share of ONE total.'
);
$ct_rows = array(
	array( 'label' => 'Average visit duration', 'our_value' => '13 min 09 sec', 'their_value' => '5 min 15 sec' ),
	array( 'label' => 'Pages per visit',        'our_value' => '17.0',          'their_value' => '6.7' ),
	array( 'label' => 'Domain Authority',       'our_value' => '46',            'their_value' => '40' ),
);
$ct_data = array_merge(
	array(
		'title'         => 'Engagement runs deeper than the peer average.',
		'_title'        => 'field_comparison_table_title',
		'description'   => '<p>When trial information lives inside trusted environments, people read, compare, and consider &mdash; not skim and leave.</p>',
		'_description'  => 'field_comparison_table_description',
		'our_label'     => 'trialport',
		'_our_label'    => 'field_comparison_table_our_label',
		'their_label'   => 'peer average',
		'_their_label'  => 'field_comparison_table_their_label',
		'footnote'      => '<p>Competitor review, June 2026, across 13 trial-search platforms; third-party metrics are directional. Domain Authority across the reviewed set ranged from 22 to 56.</p>',
		'_footnote'     => 'field_comparison_table_footnote',
	),
	ccc_sg_repeater_data(
		'rows',
		'field_comparison_table_rows',
		$ct_rows,
		array(
			'label'       => 'field_comparison_table_row_label',
			'our_value'   => 'field_comparison_table_row_our_value',
			'their_value' => 'field_comparison_table_row_their_value',
		)
	)
);
echo '<!-- wp:classic-city-core/comparison-table ' . wp_json_encode( array( 'data' => $ct_data, 'backgroundColor' => 'panel', 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// PROOF BAND
// Sampled on `ink` — the band is the page's one high-contrast proof beat, and
// `ink` is unpaired in the canvas/panel/ink model, so the border falls back to
// the default token (contrast with Chart: Horizontal Bars on `primary`, which
// resolves to a `primary-alt` border). Three stats, so all three accent slots
// appear in one sample.
ccc_sg_block_descriptor(
	'proof-band',
	'Custom',
	'Fields: <code>kicker</code>, a <code>stats</code> repeater (2&ndash;4) of <code>value</code> / <code>label</code>, <code>quote</code> + <code>quote_attribution</code>, and <code>link_text</code> / <code>link_url</code>. Stat values cycle through three accent slots in author order (<code>--ccc-pb-accent-1/2/3</code>; slot 1 defaults to <code>cta</code>) &mdash; the cycle lives in CSS <code>:nth-child</code>, not in the markup, so a child theme changes the rhythm by redefining three variables. The quote is a real <code>&lt;blockquote&gt;</code> + <code>&lt;cite&gt;</code> per content rule 23, and the quotation marks are added by the block. The link renders as a standard theme button; set <code>--ccc-pb-link-radius: 999px</code> for the pill treatment.'
);
$pb_stats = array(
	array( 'value' => '183%',    'label' => 'of the full-year application expectation, delivered within 37% of the planned support period.' ),
	array( 'value' => '73%',     'label' => 'of people who completed the self-reflection tools went on to apply.' ),
	array( 'value' => '~2 days', 'label' => 'from application to site response — momentum that protects engagement.' ),
);
$pb_data = array_merge(
	array(
		'kicker'             => 'From one recent study',
		'_kicker'            => 'field_proof_band_kicker',
		'quote'              => 'Patient quality has been very strong.',
		'_quote'             => 'field_proof_band_quote',
		'quote_attribution'  => 'research site feedback, US pain study',
		'_quote_attribution' => 'field_proof_band_quote_attribution',
		'link_text'          => 'See the case studies',
		'_link_text'         => 'field_proof_band_link_text',
		'link_url'           => '#',
		'_link_url'          => 'field_proof_band_link_url',
	),
	ccc_sg_repeater_data(
		'stats',
		'field_proof_band_stats',
		$pb_stats,
		array(
			'value' => 'field_proof_band_stat_value',
			'label' => 'field_proof_band_stat_label',
		)
	)
);
echo '<!-- wp:classic-city-core/proof-band ' . wp_json_encode( array( 'data' => $pb_data, 'backgroundColor' => 'ink', 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// STAT COMPARISON
ccc_sg_block_descriptor(
	'stat-comparison',
	'Custom',
	'Four fields: <code>from_value</code>, <code>to_value</code> (both TEXT &mdash; nothing is computed from them), <code>heading</code> (h3) and <code>body</code> (WYSIWYG). The arrow is <code>aria-hidden</code> and the figure carries <code>role="img"</code> with an <code>aria-label</code> of &ldquo;{from} to {to}&rdquo;, because a bare &ldquo;&rarr;&rdquo; is announced inconsistently. Departing from the source mockup, the parent paints the <em>after</em> figure with <code>cta</code> and steps the <em>before</em> back to a <code>currentColor</code> mix, so the direction of the change reads without knowing the palette &mdash; swap them with <code>--ccc-sc-from-color</code> / <code>--ccc-sc-to-color</code>. Sampled on <code>panel</code>.'
);
echo '<!-- wp:classic-city-core/stat-comparison ' . wp_json_encode( array(
	'data'            => array(
		'from_value'  => '14.3%',
		'_from_value' => 'field_stat_comparison_from_value',
		'to_value'    => '79.1%',
		'_to_value'   => 'field_stat_comparison_to_value',
		'heading'     => 'The share of people who go on to apply for a trial, before and since the launch of readifit.',
		'_heading'    => 'field_stat_comparison_heading',
		'body'        => '<p>When people can see how a study fits their health and their life, they are far better able to decide whether the next step is right for them. Early data, small sample &mdash; and a clear direction.</p>',
		'_body'       => 'field_stat_comparison_body',
	),
	'backgroundColor' => 'panel',
	'align'           => 'wide',
), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// FEATURE GRID (Pattern B — icons)
ccc_sg_block_descriptor(
	'feature-grid',
	'Custom',
	'Each <code>features</code> row holds a FontAwesome <code>icon_name</code>, heading, and body. Columns auto-adjust by viewport. Icon family inherits from <code>settings.custom.icons.style</code> in theme.json.'
);
$feature_grid_data = array_merge(
	array( 'desktop_columns' => 3, '_desktop_columns' => 'field_feature_grid_desktop_columns' ),
	ccc_sg_repeater_data(
		'features',
		'field_feature_grid_items',
		array(
			array( 'icon_name' => 'bolt',         'heading' => 'Built for precision',      'body' => 'Engineered from the ground up to deliver repeatable, pixel-accurate results.' ),
			array( 'icon_name' => 'chart-line',   'heading' => 'Ready to scale',           'body' => 'Grows with your workflow — from a single project to full production runs.' ),
			array( 'icon_name' => 'shield-check', 'heading' => 'Rock-solid reliability',   'body' => 'Proven in the field, tested against the edge cases you actually encounter.' ),
			array( 'icon_name' => 'headset',      'heading' => 'Fully supported',          'body' => 'A dedicated team answers fast and knows your setup inside and out.' ),
			array( 'icon_name' => 'hammer',       'heading' => 'Designed to last',         'body' => 'Quality materials and thoughtful details mean less maintenance over time.' ),
			array( 'icon_name' => 'medal',        'heading' => 'Backed by experience',     'body' => 'Built by people who have solved this problem a hundred times before.' ),
		),
		array(
			'icon_name' => 'field_feature_grid_icon_name',
			'heading'   => 'field_feature_grid_heading',
			'body'      => 'field_feature_grid_body',
		)
	)
);
echo '<!-- wp:classic-city-core/feature-grid ' . wp_json_encode( array( 'data' => $feature_grid_data ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// SPLIT 50/50 (Pattern A)
ccc_sg_block_descriptor(
	'split-50-50',
	'Hybrid',
	'Image on one side, Inner Blocks on the other. Fields: <code>image</code> (or <code>video</code>), <code>image_side</code> (left / right), <code>has_texture</code>. The block&rsquo;s native <code>backgroundColor</code> / <code>gradient</code> picker paints the CONTENT side only — image side keeps its image. Default content-side bg is white. Stacks on mobile.'
);
echo ccc_sg_acf_block(
	'classic-city-core/split-50-50',
	array( 'image' => 0, 'image_side' => 'left', 'has_texture' => 1 ),
	array( 'image' => 'field_split_image', 'image_side' => 'field_split_side', 'has_texture' => 'field_split_has_texture' ),
	array( 'backgroundColor' => 'secondary' ),
	'<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">Trusted by teams everywhere</p><!-- /wp:paragraph -->'
	. '<!-- wp:heading --><h2 class="wp-block-heading">A heading that anchors the section</h2><!-- /wp:heading -->'
	. '<!-- wp:paragraph --><p>A short supporting paragraph introduces the idea and gives the reader enough context to keep reading. Two or three sentences is plenty. Keep it focused on a single benefit, then let the list below do the heavy lifting.</p><!-- /wp:paragraph -->'
	. '<!-- wp:list --><ul><!-- wp:list-item --><li>Clear benefit or feature one</li><!-- /wp:list-item --><!-- wp:list-item --><li>Clear benefit or feature two</li><!-- /wp:list-item --><!-- wp:list-item --><li>Clear benefit or feature three</li><!-- /wp:list-item --><!-- wp:list-item --><li>Clear benefit or feature four</li><!-- /wp:list-item --></ul><!-- /wp:list -->'
	. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Get started</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
);

// ──────────────────────────────────────────────────────────────────
// LOGO STRIP (Pattern B)
ccc_sg_block_descriptor(
	'logo-strip',
	'Custom',
	'Optional <code>eyebrow</code> line over the row. <code>logos</code> repeater holds image-only rows. <code>layout</code> toggles between Grid (5-col desktop, 2-col mobile) and Marquee Scroller (continuous loop).'
);
$logo_data = array_merge(
	array( 'eyebrow' => 'Trusted by teams everywhere', '_eyebrow' => 'field_logo_strip_eyebrow' ),
	ccc_sg_repeater_data(
		'logos',
		'field_logo_strip_items',
		array(
			array( 'logo_image' => 0 ),
			array( 'logo_image' => 0 ),
			array( 'logo_image' => 0 ),
			array( 'logo_image' => 0 ),
			array( 'logo_image' => 0 ),
			array( 'logo_image' => 0 ),
		),
		array( 'logo_image' => 'field_logo_strip_image' )
	)
);
echo '<!-- wp:classic-city-core/logo-strip ' . wp_json_encode( array( 'data' => $logo_data ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// LARGE TESTIMONIAL (core/quote w/ is-style-quote)
ccc_sg_block_descriptor(
	'core/quote',
	'Gutenberg',
	'Standard WordPress core Quote block with the registered &ldquo;Quote&rdquo; block style applied (<code>is-style-quote</code>). The style gives the oversized pull-quote treatment with rounded corners and accent border. Use for hero-sized single testimonials.',
	'Large Testimonial (core/quote)'
);
?>
<!-- wp:quote {"className":"is-style-quote"} -->
<blockquote class="wp-block-quote is-style-quote">
	<!-- wp:paragraph --><p>Working with this team fundamentally changed how we ship. Everything moved faster, the quality jumped overnight, and our customers noticed the difference within the first week.</p><!-- /wp:paragraph -->
	<cite><strong>Sam Okafor</strong><br>VP of Operations, Lumen Labs</cite>
</blockquote>
<!-- /wp:quote -->

<?php
// ──────────────────────────────────────────────────────────────────
// TESTIMONIAL CARDS (Pattern C — pulls from CPT; seed with empty array so
// render.php falls through to its "no testimonials yet" state. Users populate
// by creating Testimonial CPT entries and re-selecting.)
ccc_sg_block_descriptor(
	'testimonial-cards',
	'Custom',
	'Pulls testimonials from the Testimonial CPT — go to <code>Testimonials</code> in admin to author entries, then select them in the block&rsquo;s <code>testimonials</code> field. <code>desktop_columns</code> 1–4; <code>mobile_layout</code> is <code>column-count</code> (stacks vertically) or <code>horizontal-scroll</code> (swipeable carousel). Renders empty until at least one CPT entry is selected.'
);
echo '<!-- wp:classic-city-core/testimonial-cards ' . wp_json_encode( array(
	'data' => array(
		'testimonials'     => '',
		'_testimonials'    => 'field_testimonial_cards_posts',
		'desktop_columns'  => 3,
		'_desktop_columns' => 'field_testimonial_cards_desktop_columns',
		'mobile_layout'    => 'column-count',
		'_mobile_layout'   => 'field_testimonial_cards_mobile_layout',
	),
), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// IMAGE TILES (Pattern B)
ccc_sg_block_descriptor(
	'image-tiles',
	'Custom',
	'Each <code>tiles</code> repeater row has an Image, Blurb (overlays on hover), and optional Link URL. Renders 4-up by default, responsive down.'
);
$tiles_data = ccc_sg_repeater_data(
	'tiles',
	'field_image_tiles_items',
	array(
		array( 'image' => 0, 'blurb' => 'Built to last a lifetime', 'link_url' => '#' ),
		array( 'image' => 0, 'blurb' => 'Crafted by hand',          'link_url' => '#' ),
		array( 'image' => 0, 'blurb' => 'Ready for the elements',   'link_url' => '#' ),
		array( 'image' => 0, 'blurb' => 'Made in America',          'link_url' => '#' ),
	),
	array( 'image' => 'field_image_tiles_image', 'blurb' => 'field_image_tiles_blurb', 'link_url' => 'field_image_tiles_link' )
);
echo '<!-- wp:classic-city-core/image-tiles ' . wp_json_encode( array( 'data' => $tiles_data ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// DOCUMENT DOWNLOADS (Pattern B)
ccc_sg_block_descriptor(
	'document-downloads',
	'Custom',
	'<code>documents</code> repeater: each row has <code>file_type</code> (PDF/DOC/XLS/FILE), title, file upload, and body description. Renders as a card grid with the file-type badge prominent and a download link out.'
);
$docs_data = ccc_sg_repeater_data(
	'documents',
	'field_document_downloads_items',
	array(
		array( 'file_type' => 'PDF',  'title' => 'Installation Guide', 'file' => 0, 'body' => 'Step-by-step instructions for first-time installations, including tools and substrate prep.' ),
		array( 'file_type' => 'DOC',  'title' => 'Warranty Terms',     'file' => 0, 'body' => 'Full ten-year warranty coverage, claim process, and exclusions.' ),
		array( 'file_type' => 'XLS',  'title' => 'Material Calculator','file' => 0, 'body' => 'Estimate board feet, fasteners, and joist spacing for any deck footprint.' ),
		array( 'file_type' => 'PDF',  'title' => 'Color Chart',        'file' => 0, 'body' => 'Printable swatch reference covering all twelve standard finishes.' ),
		array( 'file_type' => 'PDF',  'title' => 'Product Spec Sheet', 'file' => 0, 'body' => 'Dimensions, load ratings, and compliance certifications for every profile.' ),
		array( 'file_type' => 'FILE', 'title' => 'CAD Drawings',       'file' => 0, 'body' => 'DWG files for railing, stair, and border detail integrations.' ),
	),
	array( 'file_type' => 'field_document_downloads_type', 'title' => 'field_document_downloads_title', 'file' => 'field_document_downloads_file', 'body' => 'field_document_downloads_body' )
);
echo '<!-- wp:classic-city-core/document-downloads ' . wp_json_encode( array( 'data' => $docs_data ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// ICON FEATURE ROW (Pattern B)
ccc_sg_block_descriptor(
	'icon-feature-row',
	'Custom',
	'Pick <code>desktop_columns</code> (2/3/4). Each <code>features</code> row has a FontAwesome <code>icon_name</code>, heading, and body. Mobile collapses to one column.'
);
$icon_row_data = array_merge(
	array( 'desktop_columns' => 4, '_desktop_columns' => 'field_icon_feature_row_desktop_columns' ),
	ccc_sg_repeater_data(
		'features',
		'field_icon_feature_row_items',
		array(
			array( 'icon_name' => 'shield-check', 'heading' => 'Unmatched durability', 'body' => 'Resistant to wear, weather, and time.' ),
			array( 'icon_name' => 'wrench',       'heading' => 'Low maintenance',      'body' => 'No staining, painting, or sealing — ever.' ),
			array( 'icon_name' => 'ship',         'heading' => 'Marine grade',         'body' => 'Withstands the harshest marine conditions.' ),
			array( 'icon_name' => 'palette',      'heading' => 'Color that lasts',     'body' => 'Fade-resistant pigments baked through.' ),
		),
		array( 'icon_name' => 'field_icon_feature_row_icon', 'heading' => 'field_icon_feature_row_heading', 'body' => 'field_icon_feature_row_body' )
	)
);
echo '<!-- wp:classic-city-core/icon-feature-row ' . wp_json_encode( array( 'data' => $icon_row_data ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// PROCESS STEPS (Pattern B)
ccc_sg_block_descriptor(
	'process-steps',
	'Custom',
	'Numbered horizontal flow. Pick <code>number_bg</code> palette slug for the step-number chip. <code>desktop_columns</code> 2–5. Each <code>steps</code> row has heading + body; the number auto-increments.'
);
$process_data = array_merge(
	array( 'desktop_columns' => 4, '_desktop_columns' => 'field_process_steps_desktop_columns', 'number_bg' => 'primary', '_number_bg' => 'field_process_steps_number_bg' ),
	ccc_sg_repeater_data(
		'steps',
		'field_process_steps_items',
		array(
			array( 'heading' => 'Plan & design',      'body' => 'We scope the project, confirm fit, and map the build.' ),
			array( 'heading' => 'Gather materials',   'body' => 'Everything arrives on-site, spec-matched and ready.' ),
			array( 'heading' => 'Build with care',    'body' => 'Craftsmanship you can see in every joint and edge.' ),
			array( 'heading' => 'Final walk-through', 'body' => 'We hand off only when the work meets our standard.' ),
		),
		array( 'heading' => 'field_process_steps_heading', 'body' => 'field_process_steps_body' )
	)
);
echo '<!-- wp:classic-city-core/process-steps ' . wp_json_encode( array( 'data' => $process_data ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// LINK PODS (Pattern B — repeater of clickable cards)
ccc_sg_block_descriptor(
	'link-pods',
	'Custom',
	'<code>columns</code> 1–4. Each <code>pods</code> row: title, title_tag (h2–h6), description (WYSIWYG), link_text + link_url (both required), optional bg_image, palette <code>bg_color</code>. The entire card renders as an <code>&lt;a&gt;</code> — whole-card click target.'
);
$link_pods_data = array_merge(
	array( 'columns' => 4, '_columns' => 'field_link_pods_columns' ),
	ccc_sg_repeater_data(
		'pods',
		'field_link_pods_items',
		array(
			array( 'title' => 'Our story',        'title_tag' => 'h3', 'description' => 'Where the company started and what keeps us building.', 'link_text' => 'Learn more',        'link_url' => '#',  'bg_image' => 0, 'bg_color' => 'primary' ),
			array( 'title' => 'Browse profiles', 'title_tag' => 'h3', 'description' => 'See every board, color, and finish in one place.',           'link_text' => 'See the range',      'link_url' => '#',  'bg_image' => 0, 'bg_color' => 'secondary' ),
			array( 'title' => 'Find a dealer',   'title_tag' => 'h3', 'description' => 'Locate the nearest authorized retailer or installer.',     'link_text' => 'Search by zip',     'link_url' => '#',  'bg_image' => 0, 'bg_color' => 'dark' ),
			array( 'title' => 'Get a quote',     'title_tag' => 'h3', 'description' => 'Tell us about your project and we will respond within a day.', 'link_text' => 'Start your project', 'link_url' => '#',  'bg_image' => 0, 'bg_color' => 'cta' ),
		),
		array(
			'title'       => 'field_link_pods_title',
			'title_tag'   => 'field_link_pods_title_tag',
			'description' => 'field_link_pods_description',
			'link_text'   => 'field_link_pods_link_text',
			'link_url'    => 'field_link_pods_link_url',
			'bg_image'    => 'field_link_pods_bg_image',
			'bg_color'    => 'field_link_pods_bg_color',
		)
	)
);
echo '<!-- wp:classic-city-core/link-pods ' . wp_json_encode( array( 'data' => $link_pods_data, 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// IMAGE LINKS WITH ICONS (Pattern B — repeater of image cards with FA-icon footer bars)
ccc_sg_block_descriptor(
	'image-link-cards',
	'Custom',
	'Block-level <code>eyebrow_text</code> + <code>eyebrow_color</code> and <code>header_text</code> + <code>header_color</code> lead the grid. <code>columns</code> 1–6 (default 4). Each <code>cards</code> row: title, palette <code>bg_color</code> for the footer bar, FontAwesome <code>icon_name</code> (active style from <code>custom.icons.style</code> wraps automatically), required <code>link_url</code> (whole card becomes the anchor), required <code>image</code>. Footer text + icon colors auto-flip via the parent&rsquo;s palette pair-helpers. Image aspect locked to 1:1; uploaded images cover-crop.'
);
$image_link_cards_data = array_merge(
	array(
		'eyebrow_text'    => 'Choose your journey',
		'_eyebrow_text'   => 'field_image_link_cards_eyebrow_text',
		'eyebrow_color'   => 'secondary',
		'_eyebrow_color'  => 'field_image_link_cards_eyebrow_color',
		'header_text'     => 'Built for every backyard',
		'_header_text'    => 'field_image_link_cards_header_text',
		'header_color'    => 'primary',
		'_header_color'   => 'field_image_link_cards_header_color',
		'columns'         => 4,
		'_columns'        => 'field_image_link_cards_columns',
	),
	ccc_sg_repeater_data(
		'cards',
		'field_image_link_cards_items',
		array(
			array( 'title' => 'Mountains', 'bg_color' => 'primary',   'icon_name' => 'mountain', 'link_url' => '#', 'image' => 0 ),
			array( 'title' => 'Coastal',   'bg_color' => 'secondary', 'icon_name' => 'water',    'link_url' => '#', 'image' => 0 ),
			array( 'title' => 'Suburbs',   'bg_color' => 'primary',   'icon_name' => 'house',    'link_url' => '#', 'image' => 0 ),
			array( 'title' => 'Historic',  'bg_color' => 'secondary', 'icon_name' => 'landmark', 'link_url' => '#', 'image' => 0 ),
		),
		array(
			'title'     => 'field_image_link_cards_title',
			'bg_color'  => 'field_image_link_cards_bg_color',
			'icon_name' => 'field_image_link_cards_icon_name',
			'link_url'  => 'field_image_link_cards_link_url',
			'image'     => 'field_image_link_cards_image',
		)
	)
);
echo '<!-- wp:classic-city-core/image-link-cards ' . wp_json_encode( array( 'data' => $image_link_cards_data, 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// PRODUCT FEATURE TOGGLES (Pattern B + nested repeater — first block in
// the parent theme with a nested ACF repeater; demo data hand-flattened
// since ccc_sg_repeater_data() is single-level only.)
ccc_sg_block_descriptor(
	'product-feature-toggles',
	'Custom',
	'Tabbed feature switcher. Block-level <code>eyebrow_text</code> / <code>header_text</code> with their own palette color dropdowns at 75/25 width. Main <code>toggles</code> repeater: each toggle has an icon row (label + FA <code>icon_name</code>), a left column (image + image subtitle + image title for the floating caption), and a right column (headline + description + button text/url + <code>right_detail_color</code> palette slug + a NESTED <code>right_details</code> repeater of label/icon/description rows). The detail color drives the left bar + icon tint of every detail row in that toggle. Interactivity is keyboard-navigable (ArrowLeft / ArrowRight / Home / End).'
);

$pft_toggles = array(
	array(
		'label'                => 'Suburbs',
		'icon_name'            => 'house',
		'left_image'           => 0,
		'left_image_subtitle'  => 'For suburban homes',
		'left_image_title'     => 'Genesis Collection',
		'right_headline'       => 'Built for the way you live',
		'right_description'    => 'Lorem ipsum dolor sit amet consectetur. Et sapien lacus vitae quis a sed. Ultrices et non dolor justo pulvinar.',
		'right_button_text'    => 'View Product',
		'right_button_url'     => '#',
		'right_detail_color'   => 'secondary',
		'right_details'        => array(
			array( 'label' => 'Moisture handling',   'icon_name' => 'droplet', 'description' => 'Built to shrug off the everyday wear of yards, sprinklers, and seasons.' ),
			array( 'label' => 'Family-safe surface', 'icon_name' => 'user',    'description' => 'Splinter-free and stable underfoot for kids, pets, and bare feet.' ),
			array( 'label' => 'Color stability',     'icon_name' => 'palette', 'description' => 'Holds its tone year after year — no fading, no repainting.' ),
			array( 'label' => 'Built to last',       'icon_name' => 'clock',   'description' => 'Engineered to outlast the deck you replaced — and the one before it.' ),
		),
	),
	array(
		'label'                => 'Historic',
		'icon_name'            => 'building-columns',
		'left_image'           => 0,
		'left_image_subtitle'  => 'For historic properties',
		'left_image_title'     => 'Heritage Collection',
		'right_headline'       => 'Period-correct without the upkeep',
		'right_description'    => 'Lorem ipsum dolor sit amet consectetur. Et sapien lacus vitae quis a sed. Ultrices et non dolor justo pulvinar.',
		'right_button_text'    => 'View Product',
		'right_button_url'     => '#',
		'right_detail_color'   => 'tertiary',
		'right_details'        => array(
			array( 'label' => 'Time-worn finish',  'icon_name' => 'wand-magic-sparkles',   'description' => 'Tones that read aged on day one — no weathering wait.' ),
			array( 'label' => 'Hidden fasteners', 'icon_name' => 'screwdriver-wrench',    'description' => 'No exposed screws breaking the visual line.' ),
			array( 'label' => 'Code-compatible',  'icon_name' => 'shield-check',          'description' => 'Approved by every historic district we have served.' ),
			array( 'label' => 'Reversible install','icon_name' => 'rotate-left',          'description' => 'Lift the boards without harming the original substructure.' ),
		),
	),
	array(
		'label'                => 'Coastal',
		'icon_name'            => 'water',
		'left_image'           => 0,
		'left_image_subtitle'  => 'For waterfront properties',
		'left_image_title'     => 'Marina Collection',
		'right_headline'       => 'Marine-grade by design',
		'right_description'    => 'Lorem ipsum dolor sit amet consectetur. Et sapien lacus vitae quis a sed. Ultrices et non dolor justo pulvinar.',
		'right_button_text'    => 'View Product',
		'right_button_url'     => '#',
		'right_detail_color'   => 'secondary-alt',
		'right_details'        => array(
			array( 'label' => 'Salt resistance', 'icon_name' => 'flask',       'description' => 'Built to outlast salt spray, tide, and time.' ),
			array( 'label' => 'UV stable',       'icon_name' => 'sun',         'description' => 'Color holds through years of full-sun exposure.' ),
			array( 'label' => 'Slip resistance', 'icon_name' => 'shoe-prints', 'description' => 'Textured surface that grips wet bare feet.' ),
			array( 'label' => 'Composite core',  'icon_name' => 'cube',        'description' => 'No rot, no rust, no replacement.' ),
		),
	),
	array(
		'label'                => 'Mountains',
		'icon_name'            => 'mountain',
		'left_image'           => 0,
		'left_image_subtitle'  => 'For alpine properties',
		'left_image_title'     => 'Summit Collection',
		'right_headline'       => 'Engineered for the freeze-thaw cycle',
		'right_description'    => 'Lorem ipsum dolor sit amet consectetur. Et sapien lacus vitae quis a sed. Ultrices et non dolor justo pulvinar.',
		'right_button_text'    => 'View Product',
		'right_button_url'     => '#',
		'right_detail_color'   => 'primary',
		'right_details'        => array(
			array( 'label' => 'Freeze-thaw stable', 'icon_name' => 'snowflake',        'description' => 'Dimensional stability through repeated deep-cold cycles.' ),
			array( 'label' => 'Snow load rated',    'icon_name' => 'gauge-high',       'description' => 'Engineered substructure handles full powder accumulation.' ),
			array( 'label' => 'Low maintenance',    'icon_name' => 'broom',            'description' => 'Shovel-safe surface; no sealing required season to season.' ),
			array( 'label' => 'Long span',          'icon_name' => 'ruler-horizontal', 'description' => 'Wider joist spacing without sag — fewer footings.' ),
		),
	),
);

$pft_toggle_keys = array(
	'label'               => 'field_product_feature_toggles_label',
	'icon_name'           => 'field_product_feature_toggles_icon_name',
	'left_image'          => 'field_product_feature_toggles_left_image',
	'left_image_subtitle' => 'field_product_feature_toggles_left_image_subtitle',
	'left_image_title'    => 'field_product_feature_toggles_left_image_title',
	'right_headline'      => 'field_product_feature_toggles_right_headline',
	'right_description'   => 'field_product_feature_toggles_right_description',
	'right_button_text'   => 'field_product_feature_toggles_right_button_text',
	'right_button_url'    => 'field_product_feature_toggles_right_button_url',
	'right_detail_color'  => 'field_product_feature_toggles_right_detail_color',
);
$pft_detail_keys = array(
	'label'       => 'field_product_feature_toggles_detail_label',
	'icon_name'   => 'field_product_feature_toggles_detail_icon_name',
	'description' => 'field_product_feature_toggles_detail_description',
);

// Hand-flatten the toggles array into ACF's expected `name_N_subfield` /
// `_name_N_subfield` shape, including the nested right_details repeater
// per toggle (which gets its own count + flat row-keyed sub-entries).
$pft_data = array(
	'eyebrow_text'   => 'Lorem ipsum',
	'_eyebrow_text'  => 'field_product_feature_toggles_eyebrow_text',
	'eyebrow_color'  => 'secondary',
	'_eyebrow_color' => 'field_product_feature_toggles_eyebrow_color',
	'header_text'    => 'Built for the way you live',
	'_header_text'   => 'field_product_feature_toggles_header_text',
	'header_color'   => 'primary',
	'_header_color'  => 'field_product_feature_toggles_header_color',
	'toggles'        => count( $pft_toggles ),
	'_toggles'       => 'field_product_feature_toggles_items',
);
foreach ( $pft_toggles as $pft_i => $pft_toggle ) {
	foreach ( $pft_toggle as $sub_name => $sub_val ) {
		if ( $sub_name === 'right_details' ) {
			continue;
		}
		$flat = "toggles_{$pft_i}_{$sub_name}";
		$pft_data[ $flat ]        = $sub_val;
		$pft_data[ '_' . $flat ]  = $pft_toggle_keys[ $sub_name ] ?? '';
	}
	$pft_details = $pft_toggle['right_details'];
	$pft_data[ "toggles_{$pft_i}_right_details" ]   = count( $pft_details );
	$pft_data[ "_toggles_{$pft_i}_right_details" ]  = 'field_product_feature_toggles_right_details';
	foreach ( $pft_details as $pft_j => $pft_detail ) {
		foreach ( $pft_detail as $d_name => $d_val ) {
			$flat = "toggles_{$pft_i}_right_details_{$pft_j}_{$d_name}";
			$pft_data[ $flat ]        = $d_val;
			$pft_data[ '_' . $flat ]  = $pft_detail_keys[ $d_name ] ?? '';
		}
	}
}
echo '<!-- wp:classic-city-core/product-feature-toggles ' . wp_json_encode( array( 'data' => $pft_data, 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// SWATCH EXPLORER (Pattern B + nested repeater — head partial + tabs
// + scrollable swatch row that swaps a featured "big image").
ccc_sg_block_descriptor(
	'swatch-explorer',
	'Custom',
	'Full-width swatch showcase. Head uses the shared partial (eyebrow + h2 + optional <code>description</code>). Block-specific: a <code>toggles</code> repeater for the scenario row (label + FA <code>icon_name</code> — e.g. Morning/Overcast/Evening) with a NESTED <code>swatches</code> repeater per toggle. Each swatch has a square <code>swatch_image</code> thumbnail, a <code>swatch_name</code> label, and a <code>big_image</code> that swaps into the panel&rsquo;s featured slot when clicked. Tabs are keyboard-navigable (ArrowLeft/Right, Home/End). Swatch row is horizontally scroll-snapped with arrow buttons; --swatch-width shrinks at the tablet + mobile breakpoints so fewer swatches fit naturally without per-breakpoint count rules. Block forces <code>align: full</code> only and uses the global root padding for side spacing.'
);

$swex_toggles = array(
	array(
		'label'     => 'Morning',
		'icon_name' => 'sun',
		'swatches'  => array(
			array( 'swatch_name' => 'Gray',           'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Putty',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Anchor Gray',    'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Weathered Wood', 'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Red Wood',       'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Green',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'White',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Harborwood',     'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Cedar',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Tan',            'swatch_image' => 0, 'big_image' => 0 ),
		),
	),
	array(
		'label'     => 'Overcast',
		'icon_name' => 'cloud',
		'swatches'  => array(
			array( 'swatch_name' => 'Gray',           'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Putty',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Anchor Gray',    'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Weathered Wood', 'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Red Wood',       'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Green',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'White',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Harborwood',     'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Cedar',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Tan',            'swatch_image' => 0, 'big_image' => 0 ),
		),
	),
	array(
		'label'     => 'Evening',
		'icon_name' => 'moon',
		'swatches'  => array(
			array( 'swatch_name' => 'Gray',           'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Putty',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Anchor Gray',    'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Weathered Wood', 'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Red Wood',       'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Green',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'White',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Harborwood',     'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Cedar',          'swatch_image' => 0, 'big_image' => 0 ),
			array( 'swatch_name' => 'Tan',            'swatch_image' => 0, 'big_image' => 0 ),
		),
	),
);

$swex_toggle_keys = array(
	'label'     => 'field_swatch_explorer_toggle_label',
	'icon_name' => 'field_swatch_explorer_toggle_icon_name',
);
$swex_swatch_keys = array(
	'swatch_image' => 'field_swatch_explorer_swatch_image',
	'swatch_name'  => 'field_swatch_explorer_swatch_name',
	'big_image'    => 'field_swatch_explorer_swatch_big_image',
);

// Hand-flatten the toggles + nested swatches into ACF's expected
// shape, same pattern as product-feature-toggles above.
$swex_data = array(
	'eyebrow_text'   => '12 colors available',
	'_eyebrow_text'  => 'field_swatch_explorer_eyebrow_text',
	'eyebrow_color'  => 'secondary',
	'_eyebrow_color' => 'field_swatch_explorer_eyebrow_color',
	'header_text'    => 'Find your perfect match',
	'_header_text'   => 'field_swatch_explorer_header_text',
	'header_color'   => 'primary',
	'_header_color'  => 'field_swatch_explorer_header_color',
	'description'    => 'Three homeowners. Three different homes. One brand they didn\'t have to think twice about.',
	'_description'   => 'field_swatch_explorer_description',
	'toggles'        => count( $swex_toggles ),
	'_toggles'       => 'field_swatch_explorer_toggles',
);
foreach ( $swex_toggles as $swex_i => $swex_toggle ) {
	foreach ( $swex_toggle as $sub_name => $sub_val ) {
		if ( $sub_name === 'swatches' ) {
			continue;
		}
		$flat = "toggles_{$swex_i}_{$sub_name}";
		$swex_data[ $flat ]       = $sub_val;
		$swex_data[ '_' . $flat ] = $swex_toggle_keys[ $sub_name ] ?? '';
	}
	$swex_swatches = $swex_toggle['swatches'];
	$swex_data[ "toggles_{$swex_i}_swatches" ]   = count( $swex_swatches );
	$swex_data[ "_toggles_{$swex_i}_swatches" ]  = 'field_swatch_explorer_swatches';
	foreach ( $swex_swatches as $swex_j => $swex_swatch ) {
		foreach ( $swex_swatch as $s_name => $s_val ) {
			$flat = "toggles_{$swex_i}_swatches_{$swex_j}_{$s_name}";
			$swex_data[ $flat ]       = $s_val;
			$swex_data[ '_' . $flat ] = $swex_swatch_keys[ $s_name ] ?? '';
		}
	}
}
echo '<!-- wp:classic-city-core/swatch-explorer ' . wp_json_encode( array( 'data' => $swex_data, 'align' => 'full' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// IMAGE CARD GRID (Pattern B — uses the shared image-card partial).
ccc_sg_block_descriptor(
	'image-card-grid',
	'Custom',
	'Block-level: <code>column_count</code> (1-4), <code>mobile_layout</code> (<code>stack</code> or <code>scroll</code> — horizontal scroller with snap), and <code>image_aspect_ratio</code> (six common ratios). Each card in the <code>cards</code> repeater has a Title, Tag (eyebrow above title), Image, Description, and Button text/URL. Cards render via the shared <code>ccc_render_image_card()</code> partial — colors hardcoded via palette helpers (card bg = light, tag = secondary, footer link = cta) so the visual style stays consistent across blocks that use it.'
);

$icg_cards_data = ccc_sg_repeater_data(
	'cards',
	'field_image_card_grid_cards',
	array(
		array(
			'title'           => '"Five years on the water, no warping, no fading. We stopped thinking about the deck a long time ago."',
			'tag'             => 'Coastal Collection',
			'image'           => 0,
			'description'     => '',
			'footer_title'    => 'Marisol &amp; Theo Aguilar',
			'footer_subtitle' => 'Tybee Island, GA',
			'button_text'     => 'See how we did it',
			'button_link'     => '#',
		),
		array(
			'title'           => '"The first summer we hosted, the kids never wanted to go inside. We&rsquo;ve had that same feeling every year since."',
			'tag'             => 'Suburban Collection',
			'image'           => 0,
			'description'     => '',
			'footer_title'    => 'The Brennan Family',
			'footer_subtitle' => 'Decatur, GA',
			'button_text'     => 'See how we did it',
			'button_link'     => '#',
		),
		array(
			'title'           => '"It looks like it has always been part of the house — the highest compliment we could ask for."',
			'tag'             => 'Heritage Collection',
			'image'           => 0,
			'description'     => '',
			'footer_title'    => '',
			'footer_subtitle' => '',
			'button_text'     => 'See how we did it',
			'button_link'     => '#',
		),
		array(
			'title'           => '"Through three winters at altitude, not a single board has shifted. The neighbors keep asking what we did differently."',
			'tag'             => 'Summit Collection',
			'image'           => 0,
			'description'     => '',
			'footer_title'    => '',
			'footer_subtitle' => '',
			'button_text'     => 'See how we did it',
			'button_link'     => '#',
		),
	),
	array(
		'title'           => 'field_image_card_grid_card_title',
		'tag'             => 'field_image_card_grid_card_tag',
		'image'           => 'field_image_card_grid_card_image',
		'description'     => 'field_image_card_grid_card_description',
		'footer_title'    => 'field_image_card_grid_card_footer_title',
		'footer_subtitle' => 'field_image_card_grid_card_footer_subtitle',
		'button_text'     => 'field_image_card_grid_card_button_text',
		'button_link'     => 'field_image_card_grid_card_button_link',
	)
);

$icg_data = array_merge(
	array(
		'column_count'        => 4,
		'_column_count'       => 'field_image_card_grid_column_count',
		'mobile_layout'       => 'scroll',
		'_mobile_layout'      => 'field_image_card_grid_mobile_layout',
		'image_aspect_ratio'  => '1/1',
		'_image_aspect_ratio' => 'field_image_card_grid_image_aspect_ratio',
	),
	$icg_cards_data
);
echo '<!-- wp:classic-city-core/image-card-grid ' . wp_json_encode( array( 'data' => $icg_data, 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// POST CARD — descriptor only, no live sample. block.json restricts it
// via "ancestor": ["core/post-template"], so it can only render inside a
// Query Loop's Post Template and would output nothing dropped here.
ccc_sg_block_descriptor(
	'post-card',
	'Custom',
	'No live sample on this page: the block is restricted (via <code>ancestor</code>) to <code>core/post-template</code>, so it only renders inside a Query Loop. To use it, add a Query Loop block and place Post Card inside its Post Template — it pulls the current post&rsquo;s featured image, category, title, excerpt, and read-more link automatically via the shared <code>ccc_render_image_card()</code> partial. No fields to configure.'
);

// ──────────────────────────────────────────────────────────────────
// DETAIL CARDS — image-backgrounded data cards. Each card pairs a stat
// (top-left) with its label (top-right) over a background image, with
// a description anchored at the bottom. Card bg color comes from the
// native Gutenberg picker applied per-card; the picked color drives a
// top/bottom scrim gradient so text reads cleanly on any image.
ccc_sg_block_descriptor(
	'detail-cards',
	'Custom',
	'Block-level: <code>column_count</code> (1-4, used when autoscroll is off), <code>bg_opacity</code> (background-image opacity 0&ndash;100), <code>autoscroll</code> (infinite marquee with fixed ~33vw cards), <code>mobile_layout</code> (<code>stack</code> or <code>scroll</code>), and <code>aspect_ratio</code> (six common ratios). Each card has a Stat (top-left, large), Stat Title (top-right, small), Description (bottom, small), and Background Image. Card background color uses the native Gutenberg picker but is applied to each card (not the wrapper) and drives a top/bottom scrim gradient for legibility.'
);

$dc_cards_data = ccc_sg_repeater_data(
	'cards',
	'field_detail_cards_cards',
	array(
		array(
			'stat'        => '80',
			'stat_title'  => "Vitamin D\nnmol/L",
			'description' => 'Regulates your immune system, mood, and bones. Essential in early development.',
			'bg_image'    => 0,
		),
		array(
			'stat'        => '0.93',
			'stat_title'  => "Magnesium\nmmol/L",
			'description' => 'Helps regulate your nervous system, supports better sleep, and reduces muscle tension.',
			'bg_image'    => 0,
		),
		array(
			'stat'        => '74',
			'stat_title'  => "Ferritin\nng/mL",
			'description' => 'Reflects how much usable iron your body has stored — affecting energy, concentration, and thyroid function.',
			'bg_image'    => 0,
		),
		array(
			'stat'        => '5.2',
			'stat_title'  => "HbA1c\n%",
			'description' => 'A three-month average of your blood sugar — a leading indicator for metabolic health and energy stability.',
			'bg_image'    => 0,
		),
		array(
			'stat'        => '110',
			'stat_title'  => "HDL\nmg/dL",
			'description' => 'The cholesterol that clears arteries. Higher values track with cardiovascular resilience and longevity.',
			'bg_image'    => 0,
		),
		array(
			'stat'        => '7.4',
			'stat_title'  => "Sleep\nhrs/night",
			'description' => 'Average nightly sleep, weighted across the past 30 days. Recovery, focus, and memory are downstream of this number.',
			'bg_image'    => 0,
		),
	),
	array(
		'stat'        => 'field_detail_cards_card_stat',
		'stat_title'  => 'field_detail_cards_card_stat_title',
		'description' => 'field_detail_cards_card_description',
		'bg_image'    => 'field_detail_cards_card_bg_image',
	)
);

$dc_data = array_merge(
	array(
		'column_count'   => 3,
		'_column_count'  => 'field_detail_cards_column_count',
		'bg_opacity'     => 70,
		'_bg_opacity'    => 'field_detail_cards_bg_opacity',
		'autoscroll'     => 0,
		'_autoscroll'    => 'field_detail_cards_autoscroll',
		'mobile_layout'  => 'scroll',
		'_mobile_layout' => 'field_detail_cards_mobile_layout',
		'aspect_ratio'   => '3/4',
		'_aspect_ratio'  => 'field_detail_cards_aspect_ratio',
	),
	$dc_cards_data
);
echo '<!-- wp:classic-city-core/detail-cards ' . wp_json_encode( array( 'data' => $dc_data, 'align' => 'wide', 'backgroundColor' => 'primary' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// HIGHLIGHT TILES — marketing tile grid. Each tile pairs a headline
// with a per-card palette color and an optional image (Background =
// full-bleed cover with scrim; Inline = constrained-width illustration
// centered in the lower portion). Layout is 4 (2×2) or 6 (3×2).
ccc_sg_block_descriptor(
	'highlight-tiles',
	'Custom',
	'Block-level: <code>card_count</code> (4 or 6 — drives 2&times;2 or 3&times;2 grid; mobile stacks single-column). Per tile: <code>headline</code> (textarea, line breaks preserved), <code>bg_color</code> (palette slug — auto-pairs text color), <code>image_treatment</code> (<code>background</code> full-bleed with scrim, or <code>inline</code> centered constrained-width), and an optional <code>image</code>. Aspect ratio is hardcoded (5:3) — no editor control.'
);

$ht_cards_data = ccc_sg_repeater_data(
	'cards',
	'field_highlight_tiles_cards',
	array(
		array(
			'headline'        => 'The largest virtual care network, supporting 28 million lives worldwide',
			'bg_color'        => 'primary',
			'image_treatment' => 'background',
			'image'           => 0,
		),
		array(
			'headline'        => '30+ provider specialties',
			'bg_color'        => 'secondary',
			'image_treatment' => 'inline',
			'image'           => 0,
		),
		array(
			'headline'        => 'Global care, coverage, and reimbursement across 175+ countries',
			'bg_color'        => 'secondary-alt',
			'image_treatment' => 'inline',
			'image'           => 0,
		),
		array(
			'headline'        => 'Your trusted partner in high-quality, 24/7/365 healthcare',
			'bg_color'        => 'primary',
			'image_treatment' => 'inline',
			'image'           => 0,
		),
		array(
			'headline'        => 'Backed by 40+ peer-reviewed studies on our care model',
			'bg_color'        => 'tertiary',
			'image_treatment' => 'inline',
			'image'           => 0,
		),
		array(
			'headline'        => 'Milliman-validated outcomes, proven by employer and health plan claims-based studies',
			'bg_color'        => 'primary',
			'image_treatment' => 'background',
			'image'           => 0,
		),
	),
	array(
		'headline'        => 'field_highlight_tiles_card_headline',
		'bg_color'        => 'field_highlight_tiles_card_bg_color',
		'image_treatment' => 'field_highlight_tiles_card_image_treatment',
		'image'           => 'field_highlight_tiles_card_image',
	)
);

$ht_data = array_merge(
	array(
		'card_count'  => 6,
		'_card_count' => 'field_highlight_tiles_card_count',
	),
	$ht_cards_data
);
echo '<!-- wp:classic-city-core/highlight-tiles ' . wp_json_encode( array( 'data' => $ht_data, 'align' => 'wide' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// FRAMED CALLOUT — hero / CTA hybrid. Full-width with a uniform
// padding frame around an inner panel that carries the bg color +
// optional image. InnerBlocks default to eyebrow + h1 + large p + CTA.
ccc_sg_block_descriptor(
	'framed-callout',
	'Hybrid',
	'Block-level: <code>alignment</code> (left | center), <code>height</code> (auto | 50/60/70/80/90/100 vh), <code>bg_image</code> (optional). Background color and gradient come from the Gutenberg native picker in the toolbar and are applied to the inner panel (not the outer frame) so the page bg shows through the frame. InnerBlocks seed with an eyebrow paragraph, an h1, a large paragraph, and a CTA-colored button.'
);

$fc_inner_html =
	'<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">Hero eyebrow</p><!-- /wp:paragraph -->'
	. '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">A headline that earns the room</h1><!-- /wp:heading -->'
	. '<!-- wp:paragraph {"fontSize":"large"} --><p class="has-large-font-size">A short, generous-sized paragraph supporting the headline above. Two lines is plenty.</p><!-- /wp:paragraph -->'
	. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Get started</a></div><!-- /wp:button --></div><!-- /wp:buttons -->';

$fc_data = array(
	'alignment'   => 'center',
	'_alignment'  => 'field_framed_callout_alignment',
	'height'      => '70',
	'_height'     => 'field_framed_callout_height',
	'bg_image'    => 0,
	'_bg_image'   => 'field_framed_callout_bg_image',
);

echo '<!-- wp:classic-city-core/framed-callout ' . wp_json_encode( array( 'data' => $fc_data, 'align' => 'full', 'backgroundColor' => 'primary' ), JSON_UNESCAPED_SLASHES ) . ' -->' . "\n"
	. $fc_inner_html
	. '<!-- /wp:classic-city-core/framed-callout -->' . "\n";

// ──────────────────────────────────────────────────────────────────
// IMAGE OVERLAY (Pattern A, left image) + again content-left
ccc_sg_block_descriptor(
	'image-overlay',
	'Hybrid',
	'Landscape image with a floating content card that overhangs the image edge. Set <code>image</code>, <code>content_side</code> (left/right), and <code>card_texture</code> (default/sand/none). Inner Blocks render in the card — typically heading + paragraph + button.'
);
echo ccc_sg_acf_block(
	'classic-city-core/image-overlay',
	array( 'image' => 0, 'content_side' => 'right', 'card_texture' => 'sand' ),
	array( 'image' => 'field_overlay_image', 'content_side' => 'field_overlay_content_side', 'card_texture' => 'field_overlay_texture' ),
	array( 'align' => 'full' ),
	ccc_sg_inner_heading_body_cta(
		'Ready for a new deck?',
		'A landscape hero image anchors the page, while a content card floats over the edge to draw the eye into your call-to-action.',
		'Get a quote'
	)
);

echo ccc_sg_acf_block(
	'classic-city-core/image-overlay',
	array( 'image' => 0, 'content_side' => 'left', 'card_texture' => 'default' ),
	array( 'image' => 'field_overlay_image', 'content_side' => 'field_overlay_content_side', 'card_texture' => 'field_overlay_texture' ),
	array( 'align' => 'full' ),
	ccc_sg_inner_heading_body_cta(
		'Same block, other side',
		'A single "content_side" field swaps the image and content columns without any other markup changes.',
		'Learn more'
	)
);

// ──────────────────────────────────────────────────────────────────
// IMAGE PORTFOLIO GALLERY (Pattern B)
ccc_sg_block_descriptor(
	'portfolio-gallery',
	'Custom',
	'<code>items</code> repeater: each row has Image, Title, optional Caption. Clicking a tile opens a lightbox with title + caption display. Renders as a dense responsive grid.'
);
$portfolio_titles = array( 'Coastal Retreat', 'Modern Kitchen', 'Oak & Stone', 'Garden Pavilion', 'Urban Loft', 'Craftsman Porch', 'Lakeside Deck', 'Studio Workshop', 'Terraced Garden', 'Minimal Interior', 'Outdoor Living', 'Cedar Finish' );
$portfolio_rows = array_map(
	function ( $title ) { return array( 'image' => 0, 'title' => $title, 'caption' => '' ); },
	$portfolio_titles
);
$portfolio_data = ccc_sg_repeater_data(
	'items',
	'field_portfolio_items',
	$portfolio_rows,
	array( 'image' => 'field_portfolio_image', 'title' => 'field_portfolio_title', 'caption' => 'field_portfolio_caption' )
);
echo '<!-- wp:classic-city-core/portfolio-gallery ' . wp_json_encode( array( 'data' => $portfolio_data ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// CENTER CONTENT (Pattern A)
ccc_sg_block_descriptor(
	'center-content',
	'Hybrid',
	'Centered copy block with an overhang <code>image</code> at the top. Inner Blocks (heading + paragraph + buttons) render centered below the image. Optional background texture inherited from theme.json.'
);
echo ccc_sg_acf_block(
	'classic-city-core/center-content',
	array( 'image' => 0 ),
	array( 'image' => 'field_center_content_image' ),
	array( 'align' => 'full' ),
	'<!-- wp:heading --><h2 class="wp-block-heading">Built for the way you live</h2><!-- /wp:heading -->'
	. '<!-- wp:paragraph --><p>The kind of quality you feel the first time you touch it and the hundredth time you walk across it. Ten-year warranty, zero upkeep, and color that doesn\'t quit.</p><!-- /wp:paragraph -->'
	. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Learn more</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
);

// ──────────────────────────────────────────────────────────────────
// THIN CTA (Pattern A-without-inner, field-driven)
ccc_sg_block_descriptor(
	'cta-thin',
	'Custom',
	'Fully field-driven — no Inner Blocks. Set <code>headline</code>, <code>headline_level</code> (h2–h6), <code>subtext</code>, <code>button_label</code>, <code>button_url</code>, optional <code>bg_image</code> with <code>bg_opacity</code> (0–100) and <code>has_texture</code>. Apply palette via the block&rsquo;s native <code>backgroundColor</code>.'
);
echo ccc_sg_acf_block(
	'classic-city-core/cta-thin',
	array(
		'headline'     => 'Here is the main headline to draw attention',
		'subtext'      => 'Some extra text to drive home the point',
		'button_label' => 'Get in Touch',
		'button_url'   => '/style-guide',
		'bg_image'     => 0,
		'bg_opacity'   => 80,
		'has_texture'  => 1,
	),
	array(
		'headline'     => 'field_cta_thin_headline',
		'subtext'      => 'field_cta_thin_subtext',
		'button_label' => 'field_cta_thin_button_label',
		'button_url'   => 'field_cta_thin_button_url',
		'bg_image'     => 'field_cta_thin_bg_image',
		'bg_opacity'   => 'field_cta_thin_bg_opacity',
		'has_texture'  => 'field_cta_thin_has_texture',
	),
	array( 'align' => 'full', 'backgroundColor' => 'primary' )
);

// ──────────────────────────────────────────────────────────────────
// LARGE CTA (Pattern A — InnerBlocks + ACF bg)
ccc_sg_block_descriptor(
	'cta-large',
	'Hybrid',
	'Set ACF fields (<code>bg_image</code>, <code>bg_opacity</code>, <code>has_texture</code>) and choose palette <code>backgroundColor</code> or <code>gradient</code> from the block&rsquo;s native color panel. Inner Blocks (heading + paragraph + button) render over the band.'
);
echo ccc_sg_acf_block(
	'classic-city-core/cta-large',
	array( 'bg_image' => 0, 'bg_opacity' => 80, 'has_texture' => 1 ),
	array( 'bg_image' => 'field_cta_large_bg_image', 'bg_opacity' => 'field_cta_large_bg_opacity', 'has_texture' => 'field_cta_large_has_texture' ),
	array( 'gradient' => 'cta' ),
	'<!-- wp:heading --><h2 class="wp-block-heading">Ready to bring this to life?</h2><!-- /wp:heading -->'
	. '<!-- wp:paragraph --><p>Talk to us about your next project and find out how fast we can move from kickoff to launch. We\'ll walk you through scope, timeline, and pricing in a single call.</p><!-- /wp:paragraph -->'
	. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Schedule a call</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
);

// ──────────────────────────────────────────────────────────────────
// IMAGE WALL (Pattern B)
ccc_sg_block_descriptor(
	'image-wall',
	'Custom',
	'<code>images</code> repeater holds up to 24+ image-only rows. Auto-tiles to a dense mosaic; aligns full-width for edge-to-edge presentation. Best with a mix of landscape and portrait aspect ratios.'
);
$wall_rows = array_fill( 0, 24, array( 'image' => 0 ) );
$wall_data = ccc_sg_repeater_data(
	'images',
	'field_image_wall_images',
	$wall_rows,
	array( 'image' => 'field_image_wall_image' )
);
echo '<!-- wp:classic-city-core/image-wall ' . wp_json_encode( array( 'data' => $wall_data, 'align' => 'full' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// HIGHLIGHTED IMAGE GALLERY (Pattern C — gallery field; on /style-guide the
// render.php now falls back to bundled demo images so the block renders
// without requiring an upload. Real production usage overrides via the
// images gallery field.)
ccc_sg_block_descriptor(
	'highlighted-image-gallery',
	'Custom',
	'<code>images</code> gallery field: the first image renders large, the rest become clickable thumbnails below. <code>columns</code> controls the thumb-row count (default 5; range 2–10). Selecting a thumb swaps it into the primary slot. On <code>/style-guide</code> the block falls back to bundled defaults so it always renders.'
);
echo '<!-- wp:classic-city-core/highlighted-image-gallery ' . wp_json_encode( array(
	'data' => array(
		'images'   => '',
		'_images'  => 'field_highlighted_image_gallery_images',
		'columns'  => 5,
		'_columns' => 'field_highlighted_image_gallery_columns',
	),
	'align' => 'wide',
), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// BACKGROUND TEXTURE (Pattern B — decorative drop-in)
ccc_sg_block_descriptor(
	'background-texture',
	'Custom',
	'Drop-in decorative block. Pick a texture from the dropdown (populated from <code>settings.custom.textures</code> in this child theme&rsquo;s theme.json — register textures there to populate the list) and an <code>arrangement</code> (<code>bottom</code> rises up from the drop point, <code>top</code> descends down, <code>middle</code> centers and extends both ways). The block itself has effectively no height; a positioned pseudo carries the texture at 50vh reach with mask-faded edges. Stacking is <code>z-index: -1</code> so it sits behind sibling content — visible where adjacent content has no opaque background. Renders only when the active theme has registered textures (otherwise the slug doesn&rsquo;t resolve and the block bails silently).'
);
echo '<!-- wp:classic-city-core/background-texture ' . wp_json_encode( array(
	'data' => array(
		'texture_slug'    => 'wood',
		'_texture_slug'   => 'field_background_texture_slug',
		'arrangement'     => 'bottom',
		'_arrangement'    => 'field_background_texture_arrangement',
	),
	'align' => 'full',
), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// STACKABLE (Pattern A — InnerBlocks; position: sticky children)
// One Stackable wrapper with three split-50-50 children. Each child
// sticks at the offset and the next one covers it as the user scrolls;
// the wrapper bounds the sticky range so all three release together
// when the section ends. (See the dual-pattern :has() rules in
// assets/blocks.css — single-child wrappers fall back to "wrapper
// itself is sticky" so legacy content keeps working.)
ccc_sg_block_descriptor(
	'stackable',
	'Hybrid',
	'<code>position: sticky</code> container for a scroll-stack effect. Canonical use: drop ONE Stackable, then nest N children inside it (commonly Split 50/50s). Each child sticks at the top offset as the user scrolls past, later children cover earlier ones in DOM order, all release together when the wrapper scrolls off. Single field — <code>sticky_offset</code> (spacing-slug select, default <code>40</code>). Adjust under taller navbars. Wrapper has no visual styling; the children bring their own bg / layout. Legacy single-child Stackables (one Stackable per row, multiple as siblings) still work — :has()-detected at the CSS level. Common gotcha: position: sticky only sticks within its nearest scrolling ancestor — if any container higher up uses <code>overflow: hidden/auto</code> or <code>transform</code>, the Stackable will stick to that instead of the viewport.'
);

$stackable_splits = array(
	array(
		'image_side' => 'left',
		'bg'         => 'secondary',
		'eyebrow'    => 'Brand awareness',
		'heading'    => 'Get on the radar.',
		'body'       => 'Reach the right buyers before they\'re shopping. These videos turn cold attention into warm intent so your sales team starts from a position of strength.',
	),
	array(
		'image_side' => 'right',
		'bg'         => 'primary',
		'eyebrow'    => 'Prospect conversion',
		'heading'    => 'Win more deals.',
		'body'       => 'Your prospects are stuck in the pipeline and your team is answering the same questions on every call. These videos build that proof into the process &mdash; lifting win rates, shortening deal cycles, and growing average deal size.',
	),
	array(
		'image_side' => 'left',
		'bg'         => 'secondary-alt',
		'eyebrow'    => 'Customer retention',
		'heading'    => 'Keep them coming back.',
		'body'       => 'Existing customers are the easiest growth lever you have. These videos make the next yes obvious &mdash; renewals, upsells, and the testimonials that fuel the next round of awareness.',
	),
);

$stackable_inner = '';
foreach ( $stackable_splits as $sx ) {
	$stackable_inner .= ccc_sg_acf_block(
		'classic-city-core/split-50-50',
		array( 'image' => 0, 'image_side' => $sx['image_side'], 'has_texture' => 0 ),
		array( 'image' => 'field_split_image', 'image_side' => 'field_split_side', 'has_texture' => 'field_split_has_texture' ),
		array( 'align' => 'wide', 'backgroundColor' => $sx['bg'] ),
		'<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">' . esc_html( $sx['eyebrow'] ) . '</p><!-- /wp:paragraph -->'
		. '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $sx['heading'] ) . '</h2><!-- /wp:heading -->'
		. '<!-- wp:paragraph --><p>' . wp_kses_post( $sx['body'] ) . '</p><!-- /wp:paragraph -->'
		. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Learn more</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
	);
}

echo ccc_sg_acf_block(
	'classic-city-core/stackable',
	array( 'sticky_offset' => '40' ),
	array( 'sticky_offset' => 'field_stackable_sticky_offset' ),
	array( 'align' => 'wide' ),
	$stackable_inner
);

// ──────────────────────────────────────────────────────────────────
// FEATURE SHOWCASE (Pattern B — colored panel + icon pod row)
ccc_sg_block_descriptor(
	'feature-showcase',
	'Custom',
	'Two stacked sections. <strong>Top panel:</strong> native bg picker (no gradients), centered <code>top_title</code> (H2), <code>top_description</code> (WYSIWYG), and a single <code>top_image</code> rendered inside a <code>&lt;figure&gt;</code> flush to the panel&rsquo;s bottom edge. <strong>Bottom pods:</strong> configurable <code>pod_columns</code> (2/3/4, default 3). Each pod has an icon, H3 title, description, and an outlined WP button — <code>pod_button_color</code> picks the palette slug for every pod&rsquo;s button text + border.'
);
$feature_showcase_data = array_merge(
	array(
		'top_title'         => 'Easy, secure, identity-based access to anything',
		'_top_title'        => 'field_feature_showcase_top_title',
		'top_description'   => '<p>Deploy quickly and enable Zero Trust access to any resource on your network. From CI/CD runners across multi-cloud environments to SaaS tools and infrastructure — connect it all, seamlessly.</p>',
		'_top_description'  => 'field_feature_showcase_top_description',
		'top_image'         => 0,
		'_top_image'        => 'field_feature_showcase_top_image',
		'pod_columns'       => 3,
		'_pod_columns'      => 'field_feature_showcase_pod_columns',
		'pod_button_color'  => 'primary',
		'_pod_button_color' => 'field_feature_showcase_pod_button_color',
	),
	ccc_sg_repeater_data(
		'pods',
		'field_feature_showcase_pods',
		array(
			array( 'icon_name' => 'bolt',         'title' => 'Installation takes minutes', 'body' => 'Get started in minutes. Integrations apply policies to your teams at scale.', 'button_text' => 'Get started', 'button_url' => '#' ),
			array( 'icon_name' => 'shuffle',      'title' => 'Switching is easy',          'body' => 'Cross-platform and infrastructure agnostic to give the flexibility you need.', 'button_text' => 'See how',    'button_url' => '#' ),
			array( 'icon_name' => 'network-wired','title' => 'Bridge hybrid environments', 'body' => 'Across clouds or on-prem, a mesh overlay network connects it all.',            'button_text' => 'Learn more', 'button_url' => '#' ),
		),
		array(
			'icon_name'   => 'field_feature_showcase_pod_icon',
			'title'       => 'field_feature_showcase_pod_title',
			'body'        => 'field_feature_showcase_pod_body',
			'button_text' => 'field_feature_showcase_pod_button_text',
			'button_url'  => 'field_feature_showcase_pod_button_url',
		)
	)
);
echo '<!-- wp:classic-city-core/feature-showcase ' . wp_json_encode( array( 'data' => $feature_showcase_data, 'backgroundColor' => 'primary' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";
