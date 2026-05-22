<?php
/**
 * Link Pods block render template.
 *
 * Each pod is an <a>: the entire card is clickable. Background image (if set)
 * sits behind the content; background color uses the standard
 * `has-{slug}-background-color has-background` helper so opposite-text-color
 * helpers flip automatically. Padding uses spacing|20 token. Default border
 * radius from theme.json. Grid gap uses the theme's normal block-gap token.
 *
 * @package ClassicCityCore
 */

$pods    = get_field( 'pods' );
$columns = (int) ( get_field( 'columns' ) ?: 3 );

if ( ! is_array( $pods ) || empty( $pods ) ) {
	return;
}

$columns = max( 1, min( 4, $columns ) );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'sg-block-link-pods',
	'style' => '--block-columns: ' . $columns,
) );

$allowed_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6' );
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $pods as $idx => $pod ) :
		$title       = $pod['title'] ?? '';
		$title_tag   = in_array( $pod['title_tag'] ?? '', $allowed_tags, true ) ? $pod['title_tag'] : 'h3';
		$description = $pod['description'] ?? '';
		$link_text   = $pod['link_text'] ?? '';
		$link_url    = $pod['link_url'] ?? '';
		$bg_image    = $pod['bg_image'] ?? array();
		$bg_color    = sanitize_html_class( $pod['bg_color'] ?? '' );
		$bg_url      = ! empty( $bg_image['url'] ) ? $bg_image['url'] : '';

		// Skip pods missing the required link — keeps the whole-pod-as-anchor contract intact.
		if ( ! $link_url ) {
			continue;
		}

		$pod_classes = array( 'sg-block-link-pod' );
		if ( $bg_color ) {
			$pod_classes[] = 'has-' . $bg_color . '-background-color';
			$pod_classes[] = 'has-background';
		}
		if ( $bg_url ) {
			$pod_classes[] = 'has-bg-image';
		}

		$pod_style = $bg_url ? ' style="background-image: url(\'' . esc_url( $bg_url ) . '\')"' : '';
	?>
	<a class="<?php echo esc_attr( implode( ' ', $pod_classes ) ); ?>" href="<?php echo esc_url( $link_url ); ?>"<?php echo $pod_style; ?>>
		<div class="sg-block-link-pod-content">
			<?php if ( $title ) : ?>
			<<?php echo $title_tag; ?> class="sg-block-link-pod-title"><?php echo esc_html( $title ); ?></<?php echo $title_tag; ?>>
			<?php endif; ?>
			<?php if ( $description ) : ?>
			<div class="sg-block-link-pod-description"><?php echo wp_kses_post( $description ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $link_text ) : ?>
		<span class="sg-block-link-pod-link">
			<span class="sg-block-link-pod-link-text"><?php echo esc_html( $link_text ); ?></span>
			<i class="<?php echo esc_attr( ccc_fa_icon_class( 'fa-arrow-right' ) ); ?>" aria-hidden="true"></i>
		</span>
		<?php endif; ?>
	</a>
	<?php endforeach; ?>
</div>
