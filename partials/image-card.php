<?php
/**
 * Shared image-card partial.
 *
 * Renders the canonical "image-on-top with body + optional footer"
 * card used by image-card-grid and (eventually) other blocks. All
 * fields are optional — the card adapts to whatever's provided.
 *
 * Color treatment via WP palette helper classes:
 *   - Card bg:     has-{slug}-background-color  has-background — slug
 *                  comes from the caller's `bg_color` arg (per-instance,
 *                  no hardcoded default). If `bg_color` is empty, no
 *                  bg helper class is emitted at all (card is transparent).
 *   - Tag:         color owned by .sg-image-card__tag in blocks.css
 *                  (secondary), not a hardcoded class on the markup.
 *   - Footer link: color owned by .sg-image-card__footer-link a in
 *                  blocks.css (cta), not a hardcoded class on the markup.
 *
 * The child theme's palette decides what each slug renders as, so the
 * card adapts to every client without per-instance color picking.
 *
 * Markup contract:
 *   article.sg-image-card[.has-{slug}-background-color.has-background]?
 *     [style="--card-aspect-ratio: {ratio};"]
 *     .__media         (aspect-ratio-locked frame)
 *       img.__image    (object-fit: cover)
 *     .__body          (flex: 1 so footer anchors to bottom)
 *       .__heading-group (flex column, tight 10px gap between tag + title)
 *         p.__tag.is-style-eyebrow                (color via blocks.css)
 *           [a href]
 *         p.__title [a href]                 (large + bold via CSS, not a heading)
 *       p.__description.has-small-font-size
 *     .__divider                              (only when footer has content)
 *     .__footer                               (only when footer has content)
 *       .__footer-meta                        (only when footer_title or _subtitle set)
 *         p.__footer-title (default-size, bold)
 *         p.__footer-subtitle.has-small-font-size
 *       p.__footer-link
 *         a [arrow]                              (color via blocks.css)
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ccc_render_image_card' ) ) {
	/**
	 * Render a single image-card.
	 *
	 * @param array{
	 *   image?:              array|false,  // ACF image array or false
	 *   image_aspect_ratio?: string,        // CSS aspect-ratio value (e.g. '16/9', '1/1'). Default '16/9'.
	 *   bg_color?:           string,        // Palette slug for the card bg. Empty = no bg helper class emitted.
	 *   tag?:                string,
	 *   tag_url?:             string,
	 *   title?:              string,        // Always rendered as a <p> (large + bold via CSS). Not a heading.
	 *   title_url?:          string,
	 *   description?:        string,
	 *   footer_title?:       string,
	 *   footer_subtitle?:    string,
	 *   footer_link_text?:   string,
	 *   footer_link_url?:    string,
	 *   echo?:               bool,          // Default true.
	 * } $args
	 * @return string
	 */
	function ccc_render_image_card( $args = array() ) {
		$image            = $args['image']            ?? array();
		$ratio            = (string) ( $args['image_aspect_ratio'] ?? '16/9' );
		$bg_color         = sanitize_html_class( (string) ( $args['bg_color'] ?? '' ) );
		$tag              = (string) ( $args['tag']              ?? '' );
		$tag_url          = (string) ( $args['tag_url']          ?? '' );
		$title            = (string) ( $args['title']            ?? '' );
		$title_url        = (string) ( $args['title_url']        ?? '' );
		$description      = (string) ( $args['description']      ?? '' );
		$footer_title     = (string) ( $args['footer_title']     ?? '' );
		$footer_subtitle  = (string) ( $args['footer_subtitle']  ?? '' );
		$footer_link_text = (string) ( $args['footer_link_text'] ?? '' );
		$footer_link_url  = (string) ( $args['footer_link_url']  ?? '' );
		$echo             = (bool)   ( $args['echo']             ?? true );

		$image_url = is_array( $image ) ? ( $image['url'] ?? '' ) : '';
		$image_alt = is_array( $image ) ? ( $image['alt'] ?? '' ) : '';

		$has_body   = ( $tag !== '' || $title !== '' || $description !== '' );
		$has_footer = ( $footer_title !== '' || $footer_subtitle !== '' || $footer_link_url !== '' );

		// Wrap optionally-linked tag / title in their respective anchors so
		// the markup tree below stays clean and predictable.
		$tag_inner = $tag_url
			? sprintf( '<a href="%s">%s</a>', esc_url( $tag_url ), esc_html( $tag ) )
			: esc_html( $tag );
		$title_inner = $title_url
			? sprintf( '<a href="%s">%s</a>', esc_url( $title_url ), esc_html( $title ) )
			: esc_html( $title );

		$article_classes = array( 'sg-image-card' );
		if ( $bg_color !== '' ) {
			$article_classes[] = 'has-' . $bg_color . '-background-color';
			$article_classes[] = 'has-background';
		}

		ob_start();
		?>
		<article
			class="<?php echo esc_attr( implode( ' ', $article_classes ) ); ?>"
			style="--card-aspect-ratio: <?php echo esc_attr( $ratio ); ?>;"
		>
			<?php if ( $image_url ) : ?>
			<div class="sg-image-card__media">
				<img
					class="sg-image-card__image"
					src="<?php echo esc_url( $image_url ); ?>"
					alt="<?php echo esc_attr( $image_alt ); ?>"
					loading="lazy"
					decoding="async"
				/>
			</div>
			<?php endif; ?>

			<?php if ( $has_body ) : ?>
			<div class="sg-image-card__body">
				<?php
				/* Tag + title sit inside a flex-column wrapper with a
				   tight 10px gap so they read as one grouping above the
				   description — see __heading-group in blocks.css. The
				   title is a <p> (not a heading) because the card is a
				   marketing/UI element rather than a section landmark;
				   its visual weight is delivered by `font-size:
				   --wp--preset--font-size--large` + bold weight in CSS. */
				$has_heading_group = ( $tag !== '' || $title !== '' );
				?>
				<?php if ( $has_heading_group ) : ?>
				<div class="sg-image-card__heading-group">
					<?php if ( $tag !== '' ) : ?>
					<p class="sg-image-card__tag is-style-eyebrow"><?php
						echo $tag_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped above.
					?></p>
					<?php endif; ?>

					<?php if ( $title !== '' ) : ?>
					<p class="sg-image-card__title"><?php
						echo $title_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped above.
					?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<?php if ( $description !== '' ) : ?>
				<p class="sg-image-card__description has-small-font-size"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( $has_footer ) : ?>
			<div class="sg-image-card__divider" aria-hidden="true"></div>
			<div class="sg-image-card__footer">
				<?php if ( $footer_title !== '' || $footer_subtitle !== '' ) : ?>
				<div class="sg-image-card__footer-meta">
					<?php if ( $footer_title !== '' ) : ?>
					<p class="sg-image-card__footer-title"><?php echo esc_html( $footer_title ); ?></p>
					<?php endif; ?>
					<?php if ( $footer_subtitle !== '' ) : ?>
					<p class="sg-image-card__footer-subtitle has-small-font-size"><?php echo esc_html( $footer_subtitle ); ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<?php if ( $footer_link_url !== '' ) :
					/* Empty link_text defaults to "Learn more" so the link
					   isn't a bare arrow — gives screen readers something
					   to announce when authors leave it blank. */
					$link_text = $footer_link_text !== '' ? $footer_link_text : __( 'Learn more', 'classic-city-core' );
				?>
				<p class="sg-image-card__footer-link">
					<a href="<?php echo esc_url( $footer_link_url ); ?>">
						<?php echo esc_html( $link_text ); ?>
						<span class="sg-image-card__footer-link-arrow" aria-hidden="true">&rarr;</span>
					</a>
				</p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</article>
		<?php
		$html = (string) ob_get_clean();

		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped above.
		}
		return $html;
	}
}
