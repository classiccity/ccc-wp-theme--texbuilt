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
 * Relax ACF's URL-field validator so the placeholder/link forms we use
 * all over our content patterns pass without manual workarounds.
 *
 * ACF validates URL fields with PHP's URL validators
 * (FILTER_VALIDATE_URL / wp_http_validate_url), which require a scheme
 * AND a host. That's correct for absolute URLs but rejects four forms
 * that are perfectly valid for an anchor's `href`:
 *
 *   1. Fragment identifiers — `#`, `#section-name`
 *      (RFC 3986 fragment-only references, e.g. on-page anchors)
 *   2. Root-relative paths — `/`, `/about`, `/path/to/page?q=1#x`
 *      (RFC 3986 path-absolute references — same-site links)
 *   3. mailto: URIs — `mailto:hello@example.com`
 *   4. tel: URIs — `tel:+18005551234`
 *
 * Filter pattern: if ACF has already flagged the value as invalid,
 * we check whether it matches one of the above shapes and, if so,
 * approve it. Anything that doesn't match falls through to ACF's
 * existing verdict — we never APPROVE values ACF already accepted
 * (idempotent) and we never REJECT values ACF accepted (additive).
 *
 * Note: we deliberately reject protocol-relative URLs starting with
 * "//" — they're an attack-surface footgun in field content where the
 * scheme of the resulting link depends on the surrounding page. If
 * a content author needs one of those, they can paste a full URL.
 *
 * Priority: 20 (above ACF's own field-type validator, which registers
 * at priority 10). Without that bump our filter runs FIRST, sees the
 * initial `$valid === true`, passes through unchanged, and then ACF's
 * `acf_field_url::validate_value` fires next and rejects with "Value
 * must be a valid URL" — defeating the whole filter. Priority 20 puts
 * us after ACF so we can flip its verdict back to true on the shapes
 * we want to whitelist.
 */
add_filter( 'acf/validate_value/type=url', 'ccc_acf_allow_relative_urls', 20, 4 );
function ccc_acf_allow_relative_urls( $valid, $value, $field, $input_name ) {
	// Don't disturb values ACF already accepted.
	if ( $valid === true ) {
		return $valid;
	}

	$v = is_string( $value ) ? trim( $value ) : '';
	if ( $v === '' ) {
		return $valid;
	}

	// Fragment identifiers: "#" or "#anything-non-whitespace".
	if ( $v === '#' || preg_match( '/^#\S*$/', $v ) ) {
		return true;
	}

	// Root-relative paths: starts with "/" but NOT "//" (which would
	// be a protocol-relative URL — see header comment).
	if ( isset( $v[0] ) && $v[0] === '/' && ( ! isset( $v[1] ) || $v[1] !== '/' ) ) {
		return true;
	}

	// mailto: and tel: URI schemes (case-insensitive). We accept the
	// shape "mailto:something" / "tel:something" — full RFC 6068 /
	// RFC 3966 validation is the browser/mail client's job.
	if ( preg_match( '/^(mailto:|tel:)\S+$/i', $v ) ) {
		return true;
	}

	return $valid;
}
