<?php
/**
 * Stackable block render template.
 *
 * Markup contract:
 *   div.sg-block-stackable
 *     style="--stackable-offset: var(--wp--preset--spacing--{slug});"
 *     <InnerBlocks />
 *
 * The wrapper is `position: sticky` via CSS (see assets/blocks.css).
 * Multiple Stackables stacked back-to-back in the same scroll
 * container stick at the same top offset and visually cover each
 * other in DOM order as the user scrolls — that's the "stacking"
 * behavior the spec calls for.
 *
 * The inline CSS var seeds the per-instance offset so authors can
 * dial it up under taller navbars without per-block CSS.
 *
 * @package ClassicCityCore
 */

$offset_slug = (string) ( get_field( 'sticky_offset' ) ?: '40' );
// Whitelist against the registered spacing preset slugs so authors
// can't inject arbitrary values into the inline style.
$allowed_slugs = array( '10', '20', '30', '40', '50', '60', '70', '80', '90', '100' );
if ( ! in_array( $offset_slug, $allowed_slugs, true ) ) {
	$offset_slug = '40';
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'sg-block-stackable',
	'style' => '--stackable-offset: var(--wp--preset--spacing--' . $offset_slug . ');',
) );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<InnerBlocks />
</div>
