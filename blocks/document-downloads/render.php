<?php
/**
 * Document Downloads block render template.
 *
 * Markup contract: BLOCK_MARKUP_CONTRACT.md § Document Downloads.
 *
 * File-type icons are FA webfont glyphs composed via ccc_fa_icon_class() with
 * a fixed name per type (PDF/DOC/XLS/FILE all have named FA icons). The
 * site-wide FA style still applies.
 *
 * @package ClassicCityCore
 */

$documents = get_field( 'documents' );
if ( ! is_array( $documents ) || empty( $documents ) ) {
	return;
}

$icon_for_type = array(
	'PDF'  => 'fa-file-pdf',
	'DOC'  => 'fa-file-word',
	'XLS'  => 'fa-file-excel',
	'FILE' => 'fa-file',
);

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'sg-block-docs' ) );
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $documents as $doc ) :
		$type     = $doc['file_type'] ?? 'FILE';
		$title    = $doc['title'] ?? '';
		$body     = $doc['body'] ?? '';
		$file     = $doc['file'] ?? array();
		$file_url = ! empty( $file['url'] ) ? $file['url'] : '';
		if ( ! $file_url ) {
			continue;
		}
		$icon_slug  = $icon_for_type[ $type ] ?? 'fa-file';
		$icon_class = ccc_fa_icon_class( $icon_slug );
	?>
	<a class="sg-block-doc" href="<?php echo esc_url( $file_url ); ?>" download>
		<div class="sg-block-doc-type">
			<?php if ( $icon_class ) : ?>
			<i class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
			<?php endif; ?>
		</div>
		<div class="sg-block-doc-body">
			<?php if ( $title ) : ?><h5><?php echo esc_html( $title ); ?></h5><?php endif; ?>
			<?php if ( $body ) : ?>
			<p class="has-small-font-size"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
			<span class="sg-block-doc-link">
				<?php esc_html_e( 'Download', 'classic-city-core' ); ?>
				<span class="sg-block-doc-link-arrow">&rarr;</span>
			</span>
		</div>
	</a>
	<?php endforeach; ?>
</div>
