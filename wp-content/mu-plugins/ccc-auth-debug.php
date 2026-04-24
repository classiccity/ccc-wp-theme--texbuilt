<?php
/**
 * Plugin Name: CCC — TEMPORARY Auth Debug
 * Description: Dumps $_SERVER auth-related keys to a JSON response at
 *              /?ccc-auth-debug=1. REMOVE after diagnosing.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( isset( $_GET['ccc-auth-debug'] ) ) {
	// Defer until WP is booted enough that the app-password class exists.
	add_action( 'init', function () {
		header( 'Content-Type: application/json' );
		$out = array(
			'mu_plugin_loaded' => true,
			'php_auth_user'    => isset( $_SERVER['PHP_AUTH_USER'] ) ? $_SERVER['PHP_AUTH_USER'] : null,
			'php_auth_pw_len'  => isset( $_SERVER['PHP_AUTH_PW'] )   ? strlen( $_SERVER['PHP_AUTH_PW'] ) : 0,
			'app_passwords_available' => class_exists( 'WP_Application_Passwords' ),
			'app_passwords_enabled'   => function_exists( 'wp_is_application_passwords_available' )
				? wp_is_application_passwords_available()
				: null,
			'user_lookup'      => null,
			'all_users'        => array(),
			'app_password_validation' => null,
		);

		// Look up the user provided in Basic Auth.
		if ( ! empty( $_SERVER['PHP_AUTH_USER'] ) ) {
			$user = get_user_by( 'login', $_SERVER['PHP_AUTH_USER'] );
			if ( ! $user ) {
				$user = get_user_by( 'email', $_SERVER['PHP_AUTH_USER'] );
			}
			$out['user_lookup'] = $user ? array(
				'found'    => true,
				'id'       => $user->ID,
				'login'    => $user->user_login,
				'email'    => $user->user_email,
				'roles'    => $user->roles,
			) : array( 'found' => false );

			// Try validating the app password directly.
			if ( $user && class_exists( 'WP_Application_Passwords' ) ) {
				$result = wp_authenticate_application_password( null, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );
				$out['app_password_validation'] = is_wp_error( $result )
					? array( 'error' => true, 'code' => $result->get_error_code(), 'message' => $result->get_error_message() )
					: ( is_a( $result, 'WP_User' ) ? array( 'success' => true, 'id' => $result->ID ) : array( 'returned' => gettype( $result ) ) );
			}
		}

		// List all users so we know what accounts exist.
		$users = get_users( array( 'fields' => array( 'ID', 'user_login', 'user_email' ), 'number' => 20 ) );
		foreach ( $users as $u ) {
			$out['all_users'][] = array( 'id' => $u->ID, 'login' => $u->user_login, 'email' => $u->user_email );
		}

		echo json_encode( $out, JSON_PRETTY_PRINT );
		exit;
	}, 1 );
}
