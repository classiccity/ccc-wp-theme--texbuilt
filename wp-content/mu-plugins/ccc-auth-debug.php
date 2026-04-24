<?php
/**
 * Plugin Name: CCC — TEMPORARY Auth Debug
 * Description: Dumps $_SERVER auth-related keys to a JSON response at
 *              /?ccc-auth-debug=1. REMOVE after diagnosing.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( isset( $_GET['ccc-auth-debug'] ) ) {
	header( 'Content-Type: application/json' );
	$keys = array(
		'HTTP_AUTHORIZATION',
		'REDIRECT_HTTP_AUTHORIZATION',
		'PHP_AUTH_USER',
		'PHP_AUTH_PW',
		'REMOTE_USER',
		'REDIRECT_REMOTE_USER',
	);
	$out = array(
		'mu_plugin_loaded' => true,
		'server' => array(),
		'all_auth_like' => array(),
	);
	foreach ( $keys as $k ) {
		$out['server'][$k] = array_key_exists( $k, $_SERVER ) ? substr( (string) $_SERVER[$k], 0, 20 ) . '…' : null;
	}
	foreach ( $_SERVER as $k => $v ) {
		if ( stripos( $k, 'auth' ) !== false ) {
			$out['all_auth_like'][$k] = substr( (string) $v, 0, 20 ) . '…';
		}
	}
	echo json_encode( $out, JSON_PRETTY_PRINT );
	exit;
}
