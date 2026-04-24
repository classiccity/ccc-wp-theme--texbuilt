<?php
/**
 * ACF validation tweaks.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow fragment-only values ("#", "#section-name", "#contact") in ACF
 * `url` fields.
 *
 * ACF validates URL fields via PHP's URL validators (FILTER_VALIDATE_URL /
 * wp_http_validate_url), which require a scheme or a path-starting slash.
 * A bare "#" is a valid fragment identifier per RFC 3986 but fails those
 * validators, so ACF blocks the save.
 *
 * We use "#" and "#name" all over the place in sample content / starter
 * links, so this filter whitelists them at the validate step. Anything
 * else falls through to ACF's default validation.
 */
add_filter( 'acf/validate_value/type=url', 'ccc_acf_allow_fragment_urls', 10, 4 );
function ccc_acf_allow_fragment_urls( $valid, $value, $field, $input_name ) {
	if ( $valid !== true ) {
		// ACF already flagged this value — see if it's a fragment identifier.
		$v = is_string( $value ) ? trim( $value ) : '';
		if ( $v === '#' || preg_match( '/^#[\w-]*$/', $v ) ) {
			return true;
		}
	}
	return $valid;
}
