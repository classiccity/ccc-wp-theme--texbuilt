<?php
/**
 * Hero 3 Up block render template.
 *
 * Desktop layout:
 *   ┌─ alignfull, root L/R padding via theme.json ────────────────┐
 *   │  ┌───────── 66% ─────────┐  ┌────── 33% ───────┐            │
 *   │  │ EYEBROW                │  │ Description       │  intro   │
 *   │  │ <h1>Title</h1>         │  │ Button            │  row     │
 *   │  └────────────────────────┘  └───────────────────┘          │
 *   │  ┌───────────────────────────────────────────────┐          │
 *   │  │                Hero image                     │ row 2    │
 *   │  └───────────────────────────────────────────────┘          │
 *   └─────────────────────────────────────────────────────────────┘
 *
 * Mobile (≤ 768px): intro row collapses to a single stacked column;
 * image stays below.
 *
 * `.sg-hero` is on the wrapper as the page-top marker (shared with every
 * other Hero variant — see CLAUDE.md). The variant-specific
 * `.sg-block-hero-3-up` carries the layout CSS.
 *
 * @package ClassicCityCore
 */

$title         = (string) get_field( 'title' );
$eyebrow       = (string) get_field( 'eyebrow' );
$description   = (string) get_field( 'description' );
$button_text   = (string) get_field( 'button_text' );
$button_url    = (string) get_field( 'button_url' );
$image_aspect  = (string) get_field( 'image_aspect_ratio' );
$image         = get_field( 'image' );
if ( function_exists( 'ccc_resolve_image_or_demo' ) ) {
	$image = ccc_resolve_image_or_demo( $image, 'hero-3-up', 1440, 600 );
}

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'sg-hero sg-block-hero-3-up',
) );

$image_url = ! empty( $image['url'] ) ? $image['url'] : '';
$image_alt = ! empty( $image['alt'] ) ? $image['alt'] : '';

$image_style = '';
if ( $image_aspect && $image_aspect !== 'none' ) {
	$image_style = ' style="aspect-ratio:' . esc_attr( $image_aspect ) . '"';
}
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="sg-block-hero-3-up__inner">
		<div class="sg-block-hero-3-up__intro">
			<div class="sg-block-hero-3-up__title-col">
				<?php if ( $eyebrow ) : ?>
				<p class="is-style-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $title ) : ?>
				<h1 class="wp-block-heading sg-block-hero-3-up__title"><?php echo esc_html( $title ); ?></h1>
				<?php endif; ?>
			</div>
			<div class="sg-block-hero-3-up__desc-col">
				<?php if ( $description ) : ?>
				<p class="sg-block-hero-3-up__description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<?php if ( $button_text && $button_url ) : ?>
				<div class="wp-block-buttons">
					<div class="wp-block-button">
						<a class="wp-block-button__link has-cta-background-color has-background wp-element-button" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $button_text ); ?></a>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $image_url ) : ?>
		<div class="sg-block-hero-3-up__image"<?php echo $image_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
		</div>
		<?php endif; ?>
	</div>
</div>
