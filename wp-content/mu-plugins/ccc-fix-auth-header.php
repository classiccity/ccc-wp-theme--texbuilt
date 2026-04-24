<?php
/**
 * Plugin Name: CCC — Fix REST API Authorization Header
 * Description: Restore the HTTP Authorization header before PHP sees it so
 *              WordPress Application Password auth works over the REST API.
 *              Many managed WP hosts (WP Engine, SiteGround, some others)
 *              strip or rename the header via their nginx→PHP-FPM setup,
 *              causing Basic Auth requests to fail with rest_not_logged_in.
 *              This mu-plugin patches $_SERVER back to the state WP core
 *              expects, regardless of which of the several known quirks
 *              applies on this host.
 * Version:     1.0.0
 * Author:      Classic City Core
 *
 * Ship this with every client repo that needs programmatic REST access
 * (Claude automation, external integrations, etc.). Harmless on hosts
 * that already forward the header correctly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Case 1: some PHP setups (nginx w/ certain fastcgi configs) expose the
// Authorization header as REDIRECT_HTTP_AUTHORIZATION instead of
// HTTP_AUTHORIZATION. Copy it across so WP core finds it in the usual spot.
if ( ! empty( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) && empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
	$_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

// Case 2: when the header IS present as HTTP_AUTHORIZATION but PHP_AUTH_USER
// / PHP_AUTH_PW are not populated (some CGI modes don't auto-decode the
// header), decode Basic Auth manually and populate the expected vars. WP
// core's Application Passwords module prefers PHP_AUTH_USER / PHP_AUTH_PW
// as a fast path before falling back to parsing HTTP_AUTHORIZATION.
if (
	! empty( $_SERVER['HTTP_AUTHORIZATION'] )
	&& empty( $_SERVER['PHP_AUTH_USER'] )
	&& stripos( $_SERVER['HTTP_AUTHORIZATION'], 'basic ' ) === 0
) {
	$decoded = base64_decode( substr( $_SERVER['HTTP_AUTHORIZATION'], 6 ), true );
	if ( is_string( $decoded ) && strpos( $decoded, ':' ) !== false ) {
		list( $user, $pass )        = explode( ':', $decoded, 2 );
		$_SERVER['PHP_AUTH_USER']   = $user;
		$_SERVER['PHP_AUTH_PW']     = $pass;
	}
}
