<?php
/**
 * Swatch Explorer block render template.
 *
 * Markup contract:
 *   .sg-block-swatch-explorer.alignfull
 *     .sg-block-head.is-layout-flow                (shared head partial)
 *     [role="tablist"].__tabs                       (scenario tabs)
 *       button[role="tab"].__tab                    (icon + label)
 *     .__panels
 *       [role="tabpanel"].__panel                   (one per toggle)
 *         .__big-image                              (featured image — swaps on click)
 *           img.__big-image-el
 *         .__swatches
 *           button.__scroll-prev                    (left arrow)
 *           .__swatch-track                         (overflow-x: auto + scroll-snap)
 *             button.__swatch[data-big-image-url][data-big-image-alt]
 *               .__swatch-image                     (forced 1:1 square)
 *               span.__swatch-name
 *           button.__scroll-next                    (right arrow)
 *
 * Each toggle panel has its own swatch lineup. The first swatch's
 * big_image becomes the initial featured image; clicking another
 * swatch swaps it in via view.js.
 *
 * On /style-guide, swatches without uploaded images fall back to the
 * parent theme's bundled gallery photos so the block always renders.
 *
 * @package ClassicCityCore
 */

$toggles = get_field( 'toggles' );
if ( ! is_array( $toggles ) || empty( $toggles ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'sg-block-swatch-explorer',
) );

$instance_id = wp_unique_id( 'swex-' );

// Demo image fallback cycle for /style-guide.
$is_demo     = function_exists( 'ccc_is_demo_page' ) && ccc_is_demo_page();
$demo_images = $is_demo ? ccc_get_demo_gallery_images( 24 ) : array();
$demo_idx    = 0;
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php
	// Shared head partial — eyebrow + h2 + description.
	ccc_render_block_head( array(
		'eyebrow_text'  => get_field( 'eyebrow_text' ),
		'eyebrow_color' => get_field( 'eyebrow_color' ),
		'header_text'   => get_field( 'header_text' ),
		'header_color'  => get_field( 'header_color' ),
		'description'   => get_field( 'description' ),
	) );
	?>

	<div role="tablist" aria-label="<?php echo esc_attr( get_field( 'header_text' ) ?: __( 'Swatch scenarios', 'classic-city-core' ) ); ?>" class="sg-block-swatch-explorer__tabs">
		<?php foreach ( $toggles as $idx => $toggle ) :
			$label      = (string) ( $toggle['label'] ?? '' );
			$icon_class = ccc_fa_icon_class( $toggle['icon_name'] ?? '' );
			$is_active  = ( $idx === 0 );
			$tab_id     = $instance_id . '-tab-' . $idx;
			$panel_id   = $instance_id . '-panel-' . $idx;
		?>
		<button
			type="button"
			role="tab"
			id="<?php echo esc_attr( $tab_id ); ?>"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
			aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
			tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
			class="sg-block-swatch-explorer__tab<?php echo $is_active ? ' is-active' : ''; ?>"
		>
			<?php if ( $icon_class ) : ?>
			<i class="sg-block-swatch-explorer__tab-icon <?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
			<?php endif; ?>
			<span class="sg-block-swatch-explorer__tab-label"><?php echo esc_html( $label ); ?></span>
		</button>
		<?php endforeach; ?>
	</div>

	<div class="sg-block-swatch-explorer__panels">
		<?php foreach ( $toggles as $idx => $toggle ) :
			$is_active = ( $idx === 0 );
			$tab_id    = $instance_id . '-tab-' . $idx;
			$panel_id  = $instance_id . '-panel-' . $idx;
			$swatches  = is_array( $toggle['swatches'] ?? null ) ? $toggle['swatches'] : array();
			if ( empty( $swatches ) ) {
				continue;
			}

			// Resolve the initial featured image — first swatch's big_image,
			// with demo fallback on /style-guide.
			$first_big = $swatches[0]['big_image'] ?? array();
			if ( empty( $first_big['url'] ) && ! empty( $demo_images[ $demo_idx ] ) ) {
				$first_big = $demo_images[ $demo_idx ];
			}
			$initial_big_url = $first_big['url'] ?? '';
			$initial_big_alt = $first_big['alt'] ?? '';
		?>
		<div
			role="tabpanel"
			id="<?php echo esc_attr( $panel_id ); ?>"
			aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
			class="sg-block-swatch-explorer__panel<?php echo $is_active ? ' is-active' : ''; ?>"
			<?php if ( ! $is_active ) echo 'hidden'; ?>
		>
			<div class="sg-block-swatch-explorer__big-image">
				<?php if ( $initial_big_url ) : ?>
				<img
					class="sg-block-swatch-explorer__big-image-el"
					src="<?php echo esc_url( $initial_big_url ); ?>"
					alt="<?php echo esc_attr( $initial_big_alt ); ?>"
					loading="lazy"
					decoding="async"
				/>
				<?php endif; ?>
			</div>

			<div class="sg-block-swatch-explorer__swatches">
				<button
					type="button"
					class="sg-block-swatch-explorer__scroll-prev"
					aria-label="<?php esc_attr_e( 'Scroll swatches left', 'classic-city-core' ); ?>"
				>
					<i class="<?php echo esc_attr( ccc_fa_icon_class( 'arrow-left' ) ); ?>" aria-hidden="true"></i>
				</button>

				<div class="sg-block-swatch-explorer__swatch-track">
					<?php foreach ( $swatches as $s_idx => $swatch ) :
						$swatch_name  = (string) ( $swatch['swatch_name'] ?? '' );
						$swatch_image = $swatch['swatch_image'] ?? array();
						$big_image    = $swatch['big_image'] ?? array();

						// Demo fallbacks — cycle through bundled photos.
						if ( $is_demo ) {
							if ( empty( $swatch_image['url'] ) && ! empty( $demo_images[ $demo_idx % count( $demo_images ) ] ) ) {
								$swatch_image = $demo_images[ $demo_idx % count( $demo_images ) ];
							}
							if ( empty( $big_image['url'] ) && ! empty( $demo_images[ $demo_idx % count( $demo_images ) ] ) ) {
								$big_image = $demo_images[ $demo_idx % count( $demo_images ) ];
							}
							$demo_idx++;
						}

						$swatch_image_url = $swatch_image['url'] ?? '';
						$swatch_image_alt = $swatch_image['alt'] ?? $swatch_name;
						$big_image_url    = $big_image['url'] ?? '';
						$big_image_alt    = $big_image['alt'] ?? $swatch_name;
						$is_swatch_active = ( $s_idx === 0 );
					?>
					<button
						type="button"
						class="sg-block-swatch-explorer__swatch<?php echo $is_swatch_active ? ' is-active' : ''; ?>"
						aria-pressed="<?php echo $is_swatch_active ? 'true' : 'false'; ?>"
						data-big-image-url="<?php echo esc_url( $big_image_url ); ?>"
						data-big-image-alt="<?php echo esc_attr( $big_image_alt ); ?>"
					>
						<span
							class="sg-block-swatch-explorer__swatch-image"
							<?php if ( $swatch_image_url ) : ?>
							style="background-image: url('<?php echo esc_url( $swatch_image_url ); ?>');"
							<?php endif; ?>
							role="img"
							aria-label="<?php echo esc_attr( $swatch_image_alt ); ?>"
						></span>
						<span class="sg-block-swatch-explorer__swatch-name"><?php echo esc_html( $swatch_name ); ?></span>
					</button>
					<?php endforeach; ?>
				</div>

				<button
					type="button"
					class="sg-block-swatch-explorer__scroll-next"
					aria-label="<?php esc_attr_e( 'Scroll swatches right', 'classic-city-core' ); ?>"
				>
					<i class="<?php echo esc_attr( ccc_fa_icon_class( 'arrow-right' ) ); ?>" aria-hidden="true"></i>
				</button>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
