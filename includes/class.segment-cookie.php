<?php
/**
 * Segment Cookie
 *
 * @package analytics-wordpress
 */

/**
 * A simple API, provided to do one simple task: Track server-side events, on the client-side.
 *
 * We have an interesting problem we attempt to solve with Segment_Cookie.  A few facts, please.
 *
 * 1) It's preferred to use the client-side JS API to phone home to Segment.io.
 * 2) We need a decent way to output this tracking script based on a server-side event.
 * 3) We can't check `did_action()` on this event, as often times, we've been redirected after the fact.
 *
 * Two great examples: `wp_login` and `wp_insert_comment`.
 */
class Segment_Cookie {

	/**
	 * Sets a cookie, both via `setcookie()` and with the $_COOKIE superglobal.  Prefixed with segment_.
	 * Defaults to one day, though in reality, could just be a matter of minutes.
	 *
	 * @param string $key   The name of the cookie that we're setting.
	 * @param string $value The value of the cookie.
	 *
	 * @since  1.0.0
	 */
	public static function set_cookie( $key, $value ) {
		setcookie( 'segment_' . $key . '_' . COOKIEHASH, $value, time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
		$_COOKIE[ 'segment_' . $key . '_' . COOKIEHASH ] = $value;
	}

	/**
	 * A handy utility function to get the cookie that has been set.
	 * Allows you to either get the value of the cookie, or confirm that it has been set and is equal to a specific value.
	 *
	 * @param  string   $key   Required name of cookie to check for.
	 * @param  string   $value Cookie value.
	 * @param callable $sanitize Sanitizer for returned cookie value. Default `sanitize_text_field` and `wp_unslash`.
	 *
	 * @since  1.0.0
	 *
	 * @return bool|string   Returns true if cookie exists, or if cookie exists and matches value.  False if it does not exist, or does not match value.  Return value if it exists and $value parameter is not set.
	 */
	public static function get_cookie( $key, $value = '', $sanitize = null ) {
		if ( ! is_callable( $sanitize ) ) {
			$sanitize = fn( $value ) => sanitize_text_field( wp_unslash( $value ) );
		}

		if ( ! empty( $value ) ) {
			return isset( $_COOKIE[ 'segment_' . $key . '_' . COOKIEHASH ] ) && $value === $_COOKIE[ 'segment_' . $key . '_' . COOKIEHASH ];
		} elseif ( isset( $_COOKIE[ 'segment_' . $key . '_' . COOKIEHASH ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			return call_user_func( $sanitize, $_COOKIE[ 'segment_' . $key . '_' . COOKIEHASH ] );
		} else {
			return false;
		}
	}

	/**
	 * Unsets cookie via AJAX call.
	 * Used specifically via AJAX, as it uses wp_send_json_success().
	 *
	 * @param  string $key Name of cookie.
	 *
	 * @since  1.0.0
	 */
	public static function unset_cookie( $key = '' ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$nonce = $_REQUEST['segment-nonce'] ?? null;

		if ( ! ( $nonce && wp_verify_nonce( wp_unslash( $nonce, 'segment' ), 'segment-unset_cookie' ) ) ) {
			wp_send_json_error( $key );
			return;
		}

		if ( isset( $_POST['key'] ) ) {
			$key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
		}

		setcookie( 'segment_' . $key . '_' . COOKIEHASH, '', time() - DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
		unset( $_COOKIE[ 'segment_' . $key . '_' . COOKIEHASH ] );

		wp_send_json_success( $key );
	}

}

add_action( 'wp_ajax_segment_unset_cookie', array( 'Segment_Cookie', 'unset_cookie' ) );
add_action( 'wp_ajax_nopriv_segment_unset_cookie', array( 'Segment_Cookie', 'unset_cookie' ) );
