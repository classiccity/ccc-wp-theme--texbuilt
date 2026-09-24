<?php
/**
 * Text Callout block render template.
 *
 * Field keys: docs/BLOCKS.md (generated registry). This render.php is the canonical markup contract.
 *
 * Markup contract — deliberately the smallest thing that works:
 *   div.sg-block-text-callout.is-width-{narrow|content|wide|full}
 *     p            (from the WYSIWYG; `wpautop` supplies the tag)
 *
 * No inner wrapper, no card, no background. The bare div IS the extension
 * point: a child theme styles `p > strong` to give bold runs its own emphasis
 * treatment (its display font, a brand color, a gradient text-clip), and hangs
 * ::before / ::after off the div for quote marks, rules, or glyphs. Because
 * none of that lives here, the same block reads completely differently on
 * every site without a fork.
 *
 * WIDTH maps to WP's own layout classes rather than to bare max-widths, because
 * a block inside `is-layout-constrained` cannot exceed the container's content
 * size on max-width alone — `wide` and `full` have to opt out via `alignwide` /
 * `alignfull` for the layout CSS to release them. `narrow` and `content` sit
 * inside the content measure and are capped in CSS instead. block.json declares
 * no align support on purpose: this field is the only width control, so the
 * editor can't offer a second one that disagrees with it.
 *
 * @package ClassicCityCore
 */

$width   = get_field( 'width' ) ?: 'narrow';
$content = get_field( 'content' );

if ( empty( $content ) ) {
	return;
}

// Allow-list the width against schema drift (a value saved before an option
// was renamed would otherwise emit a junk class).
if ( ! in_array( $width, array( 'narrow', 'content', 'wide', 'full' ), true ) ) {
	$width = 'narrow';
}

$classes = array( 'sg-block-text-callout', 'is-width-' . $width );

// Only wide/full need to escape the content measure; see the note above.
if ( 'wide' === $width ) {
	$classes[] = 'alignwide';
} elseif ( 'full' === $width ) {
	$classes[] = 'alignfull';
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by get_block_wrapper_attributes(). ?>>
	<?php echo wp_kses_post( $content ); ?>
</div>
