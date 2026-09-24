<?php
/**
 * Shared "feature detail" panel partial.
 *
 * One feature-presentation panel — 2-column layout:
 *
 *   ┌──────────── 50% ─────────────┬──────────── 50% ──────────────┐
 *   │  image (1:1) with overlapping  │  h3 headline                  │
 *   │  floating caption card         │  body description             │
 *   │  (subtitle + title)            │  optional CTA outline button  │
 *   │                                │  detail rows (icon + label    │
 *   │                                │  + description, colored bar)  │
 *   └────────────────────────────────┴───────────────────────────────┘
 *
 * Used by:
 *  - blocks/product-feature-toggles → renders one panel per toggle row
 *    inside a tabpanel wrapper (the tab UI lives in PFT itself).
 *  - blocks/feature-detail → renders one panel standalone, no tab UI.
 *
 * Markup contract (canonical, both blocks emit identical inner shape):
 *   .sg-block-feature-detail            (the 2-col grid root)
 *     .sg-block-feature-detail__left      (image + caption column)
 *       .sg-block-feature-detail__image
 *         img.sg-block-feature-detail__img
 *         .sg-block-feature-detail__caption
 *           p.sg-block-feature-detail__caption-subtitle
 *           p.sg-block-feature-detail__caption-title
 *     .sg-block-feature-detail__right     (text + details + cta column)
 *       h3.sg-block-feature-detail__headline
 *       p.sg-block-feature-detail__description
 *       ul.sg-block-feature-detail__details
 *         li.sg-block-feature-detail__detail
 *           span.sg-block-feature-detail__detail-icon
 *           .sg-block-feature-detail__detail-body
 *             p.sg-block-feature-detail__detail-label
 *             p.sg-block-feature-detail__detail-desc
 *       .sg-block-feature-detail__cta (wp-block-buttons)
 *         a.wp-block-button__link.is-style-outline
 *
 * Per-instance detail-color accent (left bar + icon tint on each
 * detail row) reads from the CSS custom property `--detail-color`. The
 * CALLER is responsible for setting that prop — usually on whatever
 * wrapper contains the partial. PFT sets it on its tabpanel; the
 * feature-detail standalone block sets it on its own wrapper.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ccc_render_feature_detail_section' ) ) {
	/**
	 * Render the feature-detail panel HTML.
	 *
	 * @param array{
	 *   image?:           array|int,
	 *   image_subtitle?:  string,
	 *   image_title?:     string,
	 *   headline?:        string,
	 *   description?:     string,
	 *   button_text?:     string,
	 *   button_url?:      string,
	 *   detail_rows?:     array,
	 *   echo?:            bool,
	 * } $args
	 * @return string Empty when nothing meaningful was rendered. When
	 *               echo=true (default), the HTML is also output directly.
	 */
	function ccc_render_feature_detail_section( $args = array() ) {
		/* Image can arrive as the ACF image-field array shape, as an
		   attachment ID (when a block author saved it that way), or as
		   an empty value. Normalize to the array shape so the same
		   templating works in all cases. */
		$image = $args['image'] ?? array();
		if ( is_numeric( $image ) ) {
			$image = wp_prepare_attachment_for_js( (int) $image );
		}
		if ( ! is_array( $image ) ) {
			$image = array();
		}

		$image_url     = (string) ( $image['url'] ?? '' );
		$image_alt     = (string) ( $image['alt'] ?? '' );
		$image_subt    = (string) ( $args['image_subtitle'] ?? '' );
		$image_title   = (string) ( $args['image_title']    ?? '' );

		$headline      = (string) ( $args['headline']    ?? '' );
		$description   = (string) ( $args['description'] ?? '' );
		$button_text   = (string) ( $args['button_text'] ?? '' );
		$button_url    = (string) ( $args['button_url']  ?? '' );

		$details       = isset( $args['detail_rows'] ) && is_array( $args['detail_rows'] )
			? $args['detail_rows']
			: array();

		$echo = (bool) ( $args['echo'] ?? true );

		/* If literally nothing is set, render nothing. */
		if (
			$image_url === '' && $headline === '' && $description === ''
			&& $button_text === '' && empty( $details )
		) {
			return '';
		}

		ob_start();
		?>
		<div class="sg-block-feature-detail">
			<div class="sg-block-feature-detail__left">
				<?php if ( $image_url ) : ?>
				<div class="sg-block-feature-detail__image">
					<img
						class="sg-block-feature-detail__img"
						src="<?php echo esc_url( $image_url ); ?>"
						alt="<?php echo esc_attr( $image_alt ); ?>"
						loading="lazy"
						decoding="async"
					/>
					<?php if ( $image_subt !== '' || $image_title !== '' ) : ?>
					<div class="sg-block-feature-detail__caption">
						<?php if ( $image_subt !== '' ) : ?>
						<p class="sg-block-feature-detail__caption-subtitle"><?php echo esc_html( $image_subt ); ?></p>
						<?php endif; ?>
						<?php if ( $image_title !== '' ) : ?>
						<p class="sg-block-feature-detail__caption-title"><?php echo esc_html( $image_title ); ?></p>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<div class="sg-block-feature-detail__right">
				<?php if ( $headline !== '' ) : ?>
				<h3 class="sg-block-feature-detail__headline"><?php echo esc_html( $headline ); ?></h3>
				<?php endif; ?>

				<?php if ( $description !== '' ) : ?>
				<p class="sg-block-feature-detail__description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $details ) ) : ?>
				<ul class="sg-block-feature-detail__details">
					<?php foreach ( $details as $detail ) :
						$d_label = (string) ( $detail['label']       ?? '' );
						$d_desc  = (string) ( $detail['description'] ?? '' );
						$d_icon  = function_exists( 'ccc_fa_icon_class' )
							? ccc_fa_icon_class( $detail['icon_name'] ?? '' )
							: '';
					?>
					<li class="sg-block-feature-detail__detail">
						<?php if ( $d_icon ) : ?>
						<span class="sg-block-feature-detail__detail-icon" aria-hidden="true">
							<i class="<?php echo esc_attr( $d_icon ); ?>"></i>
						</span>
						<?php endif; ?>
						<div class="sg-block-feature-detail__detail-body">
							<?php if ( $d_label !== '' ) : ?>
							<p class="sg-block-feature-detail__detail-label"><?php echo esc_html( $d_label ); ?></p>
							<?php endif; ?>
							<?php if ( $d_desc !== '' ) : ?>
							<p class="sg-block-feature-detail__detail-desc"><?php echo esc_html( $d_desc ); ?></p>
							<?php endif; ?>
						</div>
					</li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<?php if ( $button_url && $button_text ) : ?>
				<?php /* `has-text-color has-primary-color` so the outline
				   button's text + the trailing arrow inherit the primary
				   palette slot. is-style-outline only handles the border
				   via currentColor — text needs its own has-{slug}-color
				   helper to pick up the palette. */ ?>
				<div class="sg-block-feature-detail__cta wp-block-buttons">
					<div class="wp-block-button is-style-outline">
						<a class="wp-block-button__link wp-element-button has-text-color has-primary-color" href="<?php echo esc_url( $button_url ); ?>">
							<?php echo esc_html( $button_text ); ?>
							<span class="sg-block-feature-detail__cta-arrow" aria-hidden="true">&rarr;</span>
						</a>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$html = (string) ob_get_clean();

		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped above.
		}
		return $html;
	}
}
