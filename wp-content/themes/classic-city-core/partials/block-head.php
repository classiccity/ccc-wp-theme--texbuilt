<?php
/**
 * Shared block-head partial.
 *
 * Two helpers that every block with the canonical "eyebrow + h2" intro
 * section should use:
 *
 *  - `ccc_block_head_fields( $block_slug )` — returns the four ACF field
 *    definitions (eyebrow_text 75% / eyebrow_color 25% / header_text 75% /
 *    header_color 25%) ready to merge into a block's `fields[]` array.
 *    Field keys are prefixed by `field_{slug}_` so they stay unique per
 *    block; field NAMES (used by `get_field()` and stored in the post
 *    meta) stay canonical (`eyebrow_text`, etc.) so the render helper
 *    can read them without per-block plumbing.
 *
 *  - `ccc_render_block_head( $args )` — emits the canonical head HTML:
 *    a `.sg-block-head.is-layout-flow` wrapper containing an
 *    `.is-style-eyebrow.has-text-align-center` paragraph + a
 *    `.wp-block-heading.has-text-align-center` h2. Centralizes the
 *    eyebrow→heading spacing rule, the has-text-align-center wiring,
 *    and the per-element palette helper classes.
 *
 * Why this exists: image-link-cards and product-feature-toggles both
 * grew the same head pattern (~70 lines each in fields + render + CSS).
 * Future blocks would compound the duplication. One source of truth
 * means a single place to fix bugs like the eyebrow→h2 collapse trap.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ccc_block_head_fields' ) ) {
	/**
	 * Returns the four canonical block-head ACF field definitions.
	 *
	 * @param string $block_slug Block slug in snake_case (e.g.
	 *                           'image_link_cards'). Used to derive
	 *                           per-block unique field keys.
	 * @return array<int, array<string, mixed>> Four ACF field defs.
	 */
	function ccc_block_head_fields( $block_slug ) {
		$prefix = 'field_' . $block_slug . '_';
		$choices = function_exists( 'ccc_palette_slug_choices' )
			? ccc_palette_slug_choices()
			: array( '' => '— Default —' );

		return array(
			array(
				'key'     => $prefix . 'eyebrow_text',
				'label'   => __( 'Eyebrow Text', 'classic-city-core' ),
				'name'    => 'eyebrow_text',
				'type'    => 'text',
				'wrapper' => array( 'width' => '75' ),
			),
			array(
				'key'           => $prefix . 'eyebrow_color',
				'label'         => __( 'Eyebrow Color', 'classic-city-core' ),
				'name'          => 'eyebrow_color',
				'type'          => 'select',
				'choices'       => $choices,
				'default_value' => 'secondary',
				'allow_null'    => 1,
				'ui'            => 1,
				'instructions'  => __( 'Palette slug applied to the eyebrow paragraph via has-{slug}-color.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '25' ),
			),
			array(
				'key'     => $prefix . 'header_text',
				'label'   => __( 'Header Text', 'classic-city-core' ),
				'name'    => 'header_text',
				'type'    => 'text',
				'wrapper' => array( 'width' => '75' ),
			),
			array(
				'key'           => $prefix . 'header_color',
				'label'         => __( 'Header Color', 'classic-city-core' ),
				'name'          => 'header_color',
				'type'          => 'select',
				'choices'       => $choices,
				'default_value' => 'primary',
				'allow_null'    => 1,
				'ui'            => 1,
				'instructions'  => __( 'Palette slug applied to the H2 header via has-{slug}-color.', 'classic-city-core' ),
				'wrapper'       => array( 'width' => '25' ),
			),
			array(
				'key'         => $prefix . 'description',
				'label'       => __( 'Description', 'classic-city-core' ),
				'name'        => 'description',
				'type'        => 'textarea',
				'rows'        => 2,
				'instructions' => __( 'Optional supporting paragraph below the H2. Renders with has-small-font-size.', 'classic-city-core' ),
			),
		);
	}
}

if ( ! function_exists( 'ccc_render_block_head' ) ) {
	/**
	 * Renders the canonical block-head HTML (`<div.sg-block-head>` with
	 * an eyebrow paragraph + an h2). Returns nothing if both texts are
	 * empty. Echoes by default; pass `'echo' => false` to return.
	 *
	 * Markup contract:
	 *   .sg-block-head.is-layout-flow
	 *     p.is-style-eyebrow.has-text-align-center.sg-block-head__eyebrow
	 *       [.has-{slug}-color.has-text-color]
	 *     h2.wp-block-heading.has-text-align-center.sg-block-head__heading
	 *       [.has-{slug}-color.has-text-color]
	 *
	 * The `is-layout-flow` class is critical — it makes the parent
	 * theme's `.is-style-eyebrow + h2` adjacent-sibling spacing rule
	 * fire correctly (10px collapse rule with no flex-gap math).
	 *
	 * The `has-text-align-center` class on both children matches the
	 * convention WP uses for user-dropped centered blocks, so any
	 * theme.json or core rule keyed off it picks up the alignment.
	 *
	 * @param array{
	 *   eyebrow_text?:  string,
	 *   eyebrow_color?: string,
	 *   header_text?:   string,
	 *   header_color?:  string,
	 *   description?:   string,
	 *   echo?:          bool,
	 * } $args
	 * @return string Empty when nothing was rendered. When `echo=true`
	 *               (default), the HTML is also output directly.
	 */
	function ccc_render_block_head( $args = array() ) {
		$eyebrow_text  = (string) ( $args['eyebrow_text']  ?? '' );
		$eyebrow_color = sanitize_html_class( (string) ( $args['eyebrow_color'] ?? '' ) );
		$header_text   = (string) ( $args['header_text']   ?? '' );
		$header_color  = sanitize_html_class( (string) ( $args['header_color']  ?? '' ) );
		$description   = (string) ( $args['description']   ?? '' );
		$echo          = (bool) ( $args['echo'] ?? true );

		if ( $eyebrow_text === '' && $header_text === '' && $description === '' ) {
			return '';
		}

		$eyebrow_classes = array( 'is-style-eyebrow', 'has-text-align-center', 'sg-block-head__eyebrow' );
		if ( $eyebrow_color ) {
			$eyebrow_classes[] = 'has-' . $eyebrow_color . '-color';
			$eyebrow_classes[] = 'has-text-color';
		}

		$header_classes = array( 'wp-block-heading', 'has-text-align-center', 'sg-block-head__heading' );
		if ( $header_color ) {
			$header_classes[] = 'has-' . $header_color . '-color';
			$header_classes[] = 'has-text-color';
		}

		ob_start();
		?>
		<div class="sg-block-head is-layout-flow">
			<?php if ( $eyebrow_text !== '' ) : ?>
			<p class="<?php echo esc_attr( implode( ' ', $eyebrow_classes ) ); ?>"><?php echo esc_html( $eyebrow_text ); ?></p>
			<?php endif; ?>
			<?php if ( $header_text !== '' ) : ?>
			<h2 class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>"><?php echo esc_html( $header_text ); ?></h2>
			<?php endif; ?>
			<?php if ( $description !== '' ) : ?>
			<p class="sg-block-head__description has-small-font-size has-text-align-center"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		$html = (string) ob_get_clean();

		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped above.
		}
		return $html;
	}
}
