<?php
/**
 * Javascript API.
 *
 * @package analytics-wordpress
 */

/**
 * Implement Segment via JavaScript
 */
class Segment_Api_Js {

	/**
	 * The singleton instance of Segment_Api_Js.
	 *
	 * @access private
	 * @var Segment_Api_Js
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Retrieves the one true instance of Segment_Api_Js
	 * Also sets up constants and includes deprecated files.
	 *
	 * @since  1.0.0
	 * @return object Singleton instance of Segment_Api_Js
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new Segment_Api_Js();

		}

		return self::$instance;
	}

	/**
	 * Render the Segment.io Javascript snippet.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $settings Settings options array.
	 * @param  bool  $ignore   Whether or not to ignore the call and avoid outputting the API key snippet.
	 */
	public static function initialize( $settings, $ignore = false ) {

		if ( ! isset( $settings['api_key'] ) || '' === $settings['api_key'] ) {
			return;
		}

		include_once SEG_FILE_PATH . '/templates/snippet.php';

	}

	/**
	 * Render a Javascript `identify` call
	 *
	 * @since  1.0.0
	 *
	 * @param  int|string $user_id Current User ID.
	 *                             Generated via get_current_user_id() if logged in, anonymous user ID if not.
	 * @param  array      $traits  Array of traits to pass to Segment.
	 * @param  array      $options Array of options to pass to Segment.
	 */
	public static function identify( $user_id, $traits = array(), $options = array() ) {

		// Set the proper `library` option so we know where the API calls come from.
		$options['library'] = 'analytics-wordpress';

		include_once SEG_FILE_PATH . '/templates/identify.php';
	}

	/**
	 * Render a Javascript `track` call
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $event       The name of the event to pass to Segment.
	 * @param  array   $properties  An array of properties to pass to Segment.
	 * @param  array   $options     An array of options to pass to Segment.
	 * @param  boolean $http_event  Whether or not the event is occurring over HTTP, as opposed to on page load.
	 *                              This is helpful to track events that occur between page loads, like commenting.
	 */
	public static function track( $event, $properties = array(), $options = array(), $http_event = false ) {

		// Set the proper `library` option so we know where the API calls come from.
		$options['library'] = 'analytics-wordpress';

		include_once SEG_FILE_PATH . '/templates/track.php';
	}

	/**
	 * Render a Javascript `page` call
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $category    Category (or name) of event.
	 * @param  string  $name        Optional, but if set, category must be set as well.
	 * @param  array   $properties  An array of properties to pass to Segment.
	 * @param  array   $options     An array of options to pass to Segment.
	 * @param  boolean $http_event  Whether or not the event is occurring over HTTP, as opposed to on page load.
	 *                              This is helpful to track events that occur between page loads, like commenting.
	 */
	public static function page( $category = '', $name = '', $properties = array(), $options = array(), $http_event = false ) {

		// Set the proper `library` option so we know where the API calls come from.
		$options['library'] = 'analytics-wordpress';

		include_once SEG_FILE_PATH . '/templates/page.php';

	}

	/**
	 * Creates an alias between an anonymous ID and a newly created user ID.
	 * Primarily used for MixPanel.
	 *
	 * @since  1.0.0
	 *
	 * @param  int|string $from    The anonymous ID that we're aliasing from.
	 * @param  int|string $to      The newly created User ID we are aliasing to.
	 * @param  string     $context Optional context parameter to be passed to Segment.
	 */
	public static function alias( $from, $to, $context = '' ) {

		include_once SEG_FILE_PATH . '/templates/alias.php';
	}

}
