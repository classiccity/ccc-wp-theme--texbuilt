<?php
/**
 * Product Feature Toggles block render template.
 *
 * Markup contract:
 *   .sg-block-product-feature-toggles            (root)
 *     .__head.is-layout-flow                     (eyebrow + h2; uses parent's
 *                                                 eyebrow→heading spacing rule)
 *     [role="tablist"].__tabs                    (horizontal toggle row)
 *       button[role="tab"].__tab                 (icon + label per toggle)
 *     .__divider                                 (thin horizontal line)
 *     .__panels                                  (the swap-content area)
 *       [role="tabpanel"].__tabpanel             (one per toggle; only one
 *                                                 visible at a time. Carries
 *                                                 --detail-color CSS var from
 *                                                 the per-toggle palette slug.)
 *         .sg-block-feature-detail               (← shared partial — the 2-col
 *                                                 image+caption / text+details
 *                                                 layout. See
 *                                                 partials/feature-detail-section.php.
 *                                                 Identical shape to the
 *                                                 standalone feature-detail block.)
 *
 * Interactivity in view.js (auto-enqueued via block.json viewScript). The
 * panels are rendered server-side with one having `is-active`; JS just
 * swaps which tabpanel/tab is active on click.
 *
 * On `/style-guide` (ccc_is_demo_page) any toggle missing an image falls
 * back to the bundled gallery images so the block always renders cleanly
 * in the demo context. Production usage stays untouched.
 *
 * @package ClassicCityCore
 */

$header_text = (string) ( get_field( 'header_text' ) ?? '' ); // kept for aria-label below
$toggles     = get_field( 'toggles' );

if ( ! is_array( $toggles ) || empty( $toggles ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array( 'class' => 'sg-block-product-feature-toggles' )
);

$instance_id = wp_unique_id( 'pft-' );

// On the demo page, fall back to bundled gallery images for any toggle
// that doesn't have its own image — keeps /style-guide visible without
// requiring uploads.
$is_demo     = function_exists( 'ccc_is_demo_page' ) && ccc_is_demo_page();
$demo_images = $is_demo ? ccc_get_demo_gallery_images( max( 1, count( $toggles ) ) ) : array();
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php
	// Shared block-head partial: eyebrow + h2 with palette colors,
	// centered alignment, and the spacing-safe `is-layout-flow` wrapper.
	// See partials/block-head.php.
	ccc_render_block_head( array(
		'eyebrow_text'  => get_field( 'eyebrow_text' ),
		'eyebrow_color' => get_field( 'eyebrow_color' ),
		'header_text'   => $header_text,
		'header_color'  => get_field( 'header_color' ),
	) );
	?>

	<div role="tablist" aria-label="<?php echo esc_attr( $header_text ?: __( 'Product features', 'classic-city-core' ) ); ?>" class="sg-block-product-feature-toggles__tabs">
		<?php foreach ( $toggles as $idx => $toggle ) :
			$label       = (string) ( $toggle['label'] ?? '' );
			$icon_class  = ccc_fa_icon_class( $toggle['icon_name'] ?? '' );
			$is_active   = ( $idx === 0 );
			$tab_id      = $instance_id . '-tab-' . $idx;
			$panel_id    = $instance_id . '-panel-' . $idx;
		?>
		<button
			type="button"
			role="tab"
			id="<?php echo esc_attr( $tab_id ); ?>"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
			aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
			tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
			class="sg-block-product-feature-toggles__tab<?php echo $is_active ? ' is-active' : ''; ?>"
		>
			<?php if ( $icon_class ) : ?>
			<i class="sg-block-product-feature-toggles__tab-icon <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
			<?php endif; ?>
			<span class="sg-block-product-feature-toggles__tab-label"><?php echo esc_html( $label ); ?></span>
		</button>
		<?php endforeach; ?>
	</div>

	<div class="sg-block-product-feature-toggles__divider" aria-hidden="true"></div>

	<div class="sg-block-product-feature-toggles__panels">
		<?php foreach ( $toggles as $idx => $toggle ) :
			$is_active     = ( $idx === 0 );
			$tab_id        = $instance_id . '-tab-' . $idx;
			$panel_id      = $instance_id . '-panel-' . $idx;

			/* Demo fallback for /style-guide. */
			$image         = $toggle['left_image'] ?? array();
			if ( empty( $image['url'] ) && ! empty( $demo_images[ $idx ] ) ) {
				$image = $demo_images[ $idx ];
			}

			$detail_color  = sanitize_html_class( $toggle['right_detail_color'] ?? 'secondary' );

			/* Drive the detail-row accent (left bar + icon tint) via a CSS
			   custom prop on the tabpanel wrapper — descendant
			   `.sg-block-feature-detail__detail` rules read it. */
			$panel_style = $detail_color
				? '--detail-color: var(--wp--preset--color--' . $detail_color . ');'
				: '';
		?>
		<div
			role="tabpanel"
			id="<?php echo esc_attr( $panel_id ); ?>"
			aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
			class="sg-block-product-feature-toggles__tabpanel<?php echo $is_active ? ' is-active' : ''; ?>"
			<?php if ( ! $is_active ) echo 'hidden'; ?>
			style="<?php echo esc_attr( $panel_style ); ?>"
		>
			<?php
			/* Inner panel content is the shared feature-detail partial —
			   identical shape to what the standalone feature-detail block
			   renders. See partials/feature-detail-section.php. */
			ccc_render_feature_detail_section( array(
				'image'          => $image,
				'image_subtitle' => $toggle['left_image_subtitle'] ?? '',
				'image_title'    => $toggle['left_image_title']    ?? '',
				'headline'       => $toggle['right_headline']      ?? '',
				'description'    => $toggle['right_description']   ?? '',
				'button_text'    => $toggle['right_button_text']   ?? '',
				'button_url'     => $toggle['right_button_url']    ?? '',
				'detail_rows'    => is_array( $toggle['right_details'] ?? null ) ? $toggle['right_details'] : array(),
			) );
			?>
		</div>
		<?php endforeach; ?>
	</div>

</div>
