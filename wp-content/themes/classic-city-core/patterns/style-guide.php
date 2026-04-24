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
echo ccc_sg_acf_block(
	'classic-city-core/hero-full-image',
	array( 'image' => 0, 'title_html' => 'We build what your community <strong>counts on</strong>' ),
	array( 'image' => 'field_hero_full_image', 'title_html' => 'field_hero_full_title_html' ),
	array( 'align' => 'full' ),
	'<!-- wp:paragraph --><p>A short supporting paragraph that reinforces the headline and gives the reader enough context to keep scrolling. Two or three sentences is plenty.</p><!-- /wp:paragraph -->'
	. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Get started</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
);

// ──────────────────────────────────────────────────────────────────
// IMAGE HERO 50/50 (Pattern A + native bg color/gradient)
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
// IMAGE COLUMNS (Pattern B — repeater of 3 cards)
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
// FEATURE GRID (Pattern B — icons)
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
echo ccc_sg_acf_block(
	'classic-city-core/split-50-50',
	array( 'image' => 0, 'image_side' => 'left', 'has_texture' => 1 ),
	array( 'image' => 'field_split_image', 'image_side' => 'field_split_side', 'has_texture' => 'field_split_has_texture' ),
	array(),
	'<!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">Trusted by teams everywhere</p><!-- /wp:paragraph -->'
	. '<!-- wp:heading --><h2 class="wp-block-heading">A heading that anchors the section</h2><!-- /wp:heading -->'
	. '<!-- wp:paragraph --><p>A short supporting paragraph introduces the idea and gives the reader enough context to keep reading. Two or three sentences is plenty. Keep it focused on a single benefit, then let the list below do the heavy lifting.</p><!-- /wp:paragraph -->'
	. '<!-- wp:list --><ul><!-- wp:list-item --><li>Clear benefit or feature one</li><!-- /wp:list-item --><!-- wp:list-item --><li>Clear benefit or feature two</li><!-- /wp:list-item --><!-- wp:list-item --><li>Clear benefit or feature three</li><!-- /wp:list-item --><!-- wp:list-item --><li>Clear benefit or feature four</li><!-- /wp:list-item --></ul><!-- /wp:list -->'
	. '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button">Get started</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'
);

// ──────────────────────────────────────────────────────────────────
// LOGO STRIP (Pattern B)
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
echo '<!-- wp:classic-city-core/testimonial-cards ' . wp_json_encode( array(
	'data' => array(
		'testimonials'     => '',
		'_testimonials'    => 'field_testimonial_cards_posts',
		'desktop_columns'  => 3,
		'_desktop_columns' => 'field_testimonial_cards_desktop_columns',
		'mobile_layout'    => 'stack',
		'_mobile_layout'   => 'field_testimonial_cards_mobile_layout',
	),
), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";

// ──────────────────────────────────────────────────────────────────
// IMAGE TILES (Pattern B)
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
// IMAGE OVERLAY (Pattern A, left image) + again content-left
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
echo ccc_sg_acf_block(
	'classic-city-core/cta-thin',
	array(
		'headline'     => 'Here is the main headline to draw attention',
		'subtext'      => 'Some extra text to drive home the point',
		'button_label' => 'Get in Touch',
		'button_url'   => '#',
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
$wall_rows = array_fill( 0, 24, array( 'image' => 0 ) );
$wall_data = ccc_sg_repeater_data(
	'images',
	'field_image_wall_images',
	$wall_rows,
	array( 'image' => 'field_image_wall_image' )
);
echo '<!-- wp:classic-city-core/image-wall ' . wp_json_encode( array( 'data' => $wall_data, 'align' => 'full' ), JSON_UNESCAPED_SLASHES ) . ' /-->' . "\n";
