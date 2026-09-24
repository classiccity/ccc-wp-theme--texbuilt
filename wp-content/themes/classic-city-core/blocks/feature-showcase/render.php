<?php
/**
 * Feature Showcase block render template.
 *
 * Two stacked sections:
 *
 * 1. Top panel: solid bg (block-level native picker), centered title + WYSIWYG
 *    + figure-wrapped image. Padding is spacing--30 on three sides; bottom is
 *    flush so the image can bleed to the panel's lower edge.
 *
 * 2. Bottom pods: configurable column count, each pod renders icon, H3 title,
 *    description, and a WP outline-style button keyed to a single block-level
 *    palette slug.
 *
 * We strip the WP-auto-injected bg class off the block root so the colored
 * panel is the only thing painted — the icon-pod row stays transparent.
 *
 * @package ClassicCityCore
 */

$top_title       = get_field( 'top_title' );
$top_description = get_field( 'top_description' );
$top_image       = get_field( 'top_image' );
$columns         = (int) ( get_field( 'pod_columns' ) ?: 3 );
$button_slug     = sanitize_html_class( get_field( 'pod_button_color' ) ?: 'primary' ) ?: 'primary';
$pods            = get_field( 'pods' );

// Demo-page fallback: bundled neutral image (skyscrapers, gallery-3) so the
// /style-guide preview never shows whatever random picsum returns for a seed —
// the seed-based pull happened to land on a swimwear photo, which is the wrong
// vibe for a "feature-showcase hero" demo any client might see.
if ( ( ! is_array( $top_image ) || empty( $top_image['url'] ) ) && function_exists( 'ccc_is_demo_page' ) && ccc_is_demo_page() ) {
	$demo_url  = trailingslashit( get_template_directory_uri() ) . 'assets/demo/gallery-3.jpg';
	$top_image = array(
		'ID'     => 0,
		'url'    => $demo_url,
		'alt'    => 'Demo placeholder',
		'width'  => 1600,
		'height' => 1067,
	);
}

$columns = max( 2, min( 4, $columns ) );

$bg_slug = sanitize_html_class( $block['backgroundColor'] ?? ( $block['attrs']['backgroundColor'] ?? '' ) );

$top_classes = array( 'sg-block-feature-showcase__top' );
if ( $bg_slug ) {
	$top_classes[] = 'has-' . $bg_slug . '-background-color';
	$top_classes[] = 'has-background';
}

$wrapper_attrs = ccc_strip_bg_from_wrapper(
	get_block_wrapper_attributes(
		array(
			'class' => 'sg-block-feature-showcase',
		)
	)
);

$img_url = ! empty( $top_image['url'] ) ? $top_image['url'] : '';
$img_alt = ! empty( $top_image['alt'] ) ? $top_image['alt'] : '';
$img_w   = ! empty( $top_image['width'] ) ? (int) $top_image['width'] : 0;
$img_h   = ! empty( $top_image['height'] ) ? (int) $top_image['height'] : 0;
?>
<div <?php echo $wrapper_attrs; ?>>

	<div class="<?php echo esc_attr( implode( ' ', $top_classes ) ); ?>">
		<div class="sg-block-feature-showcase__top-content">
			<?php if ( $top_title ) : ?>
			<h2 class="sg-block-feature-showcase__title"><?php echo esc_html( $top_title ); ?></h2>
			<?php endif; ?>
			<?php if ( $top_description ) : ?>
			<div class="sg-block-feature-showcase__description"><?php echo wp_kses_post( $top_description ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $img_url ) : ?>
		<figure class="sg-block-feature-showcase__figure">
			<img
				src="<?php echo esc_url( $img_url ); ?>"
				alt="<?php echo esc_attr( $img_alt ); ?>"
				<?php if ( $img_w && $img_h ) : ?>width="<?php echo esc_attr( $img_w ); ?>" height="<?php echo esc_attr( $img_h ); ?>"<?php endif; ?>
				loading="lazy"
				decoding="async"
			/>
		</figure>
		<?php endif; ?>
	</div>

	<?php if ( is_array( $pods ) && ! empty( $pods ) ) : ?>
	<div class="sg-block-feature-showcase__pods" style="--block-columns: <?php echo esc_attr( $columns ); ?>">
		<?php foreach ( $pods as $pod ) :
			$icon_class  = ccc_fa_icon_class( $pod['icon_name'] ?? '' );
			$pod_title   = $pod['title'] ?? '';
			$pod_body    = $pod['body'] ?? '';
			$button_text = $pod['button_text'] ?? '';
			$button_url  = $pod['button_url'] ?? '';
		?>
		<div class="sg-block-feature-showcase__pod">
			<?php if ( $icon_class ) : ?>
			<i class="sg-block-feature-showcase__pod-icon <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
			<?php endif; ?>
			<?php if ( $pod_title ) : ?>
			<h3 class="sg-block-feature-showcase__pod-title"><?php echo esc_html( $pod_title ); ?></h3>
			<?php endif; ?>
			<?php if ( $pod_body ) : ?>
			<p class="sg-block-feature-showcase__pod-body has-small-font-size"><?php echo esc_html( $pod_body ); ?></p>
			<?php endif; ?>
			<?php if ( $button_text && $button_url ) : ?>
			<div class="wp-block-button is-style-outline sg-block-feature-showcase__pod-btn">
				<a
					class="wp-block-button__link wp-element-button has-text-color has-<?php echo esc_attr( $button_slug ); ?>-color"
					href="<?php echo esc_url( $button_url ); ?>"
				><?php echo esc_html( $button_text ); ?></a>
			</div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

</div>
