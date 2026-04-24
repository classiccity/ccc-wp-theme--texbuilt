<?php
/**
 * Style Guide demo page — ensures a WP page with slug "style-guide" exists
 * so the FSE template `page-style-guide.html` has something to resolve to,
 * and adds an admin-bar shortcut to it for logged-in editors.
 *
 * Content strategy:
 * - The page's post_content is intentionally empty. All rendering happens via
 *   the FSE template, which embeds the `ccc/style-guide` pattern.
 * - The pattern itself looks for a child-theme override (slug
 *   `sg-{stylesheet}/style-guide-content`) and falls back to the canonical
 *   content in the parent theme.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CCC_Demo_Page {

	const SLUG = 'style-guide';

	public static function boot() {
		add_action( 'after_switch_theme', array( __CLASS__, 'ensure' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_link' ), 80 );
	}

	/**
	 * Make sure a published page with slug `style-guide` exists. Idempotent —
	 * safe to call on every theme activation or CLI scaffold. Only creates;
	 * never modifies an existing page's content.
	 *
	 * @return int The post ID of the page (existing or just-created).
	 */
	public static function ensure() {
		$existing = get_page_by_path( self::SLUG );
		if ( $existing && $existing->post_status !== 'trash' ) {
			return $existing->ID;
		}

		$post_id = wp_insert_post( array(
			'post_title'   => 'Style Guide',
			'post_name'    => self::SLUG,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		), true );

		if ( is_wp_error( $post_id ) ) {
			return 0;
		}
		return (int) $post_id;
	}

	/**
	 * "View Style Guide" node in the admin bar. Visible to anyone who can
	 * edit pages (matches the audience likely to care).
	 */
	public static function admin_bar_link( $wp_admin_bar ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		$wp_admin_bar->add_node( array(
			'id'    => 'ccc-style-guide',
			'title' => __( 'Style Guide', 'classic-city-core' ),
			'href'  => home_url( '/' . self::SLUG . '/' ),
			'meta'  => array(
				'title' => __( 'View the style-guide page for the active child theme', 'classic-city-core' ),
			),
		) );
	}
}

CCC_Demo_Page::boot();
