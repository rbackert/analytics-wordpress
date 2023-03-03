<?php
/**
 * Plugin Name: Analytics for WordPress — by Segment.io
 * Plugin URI: https://segment.io/plugins/wordpress
 * Description: The hassle-free way to integrate any analytics service into your WordPress site.
 * Version: 1.0.14
 * License: GPLv2
 * Author: Segment.io
 * Author URI: https://segment.io
 * Author Email: friends@segment.io
 *
 * @package analytics-wordpress
 */

/**
 * Main plugin file.
 */
class Segment_Analytics_WordPress {

	/**
	 * Slug used in page and menu names
	 */
	const SLUG = 'analytics';

	/**
	 * Current plugin version.
	 */
	const VERSION = '1.0.14';

	/**
	 * The singleton instance of Segment_Analytics_WordPress.
	 *
	 * @access private
	 * @var Segment_Analytics_WordPress
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * The singleton instance of Segment_Api_Js, for use in our class.
	 *
	 * @access private
	 * @var Segment_Api_Js
	 * @since 1.0.0
	 */
	private $analytics;

	/**
	 * The name of our options array.
	 *
	 * @access private
	 * @var string
	 * @since 1.0.0
	 */
	private $option = 'analytics_wordpress_options';

	/**
	 * The default values for our options array.
	 *
	 * Not used since 1.0.0, outside of activation hooks, with our move to the Settings API.
	 * See Segment_Analytics_WordPress::register_settings().
	 *
	 * @access public
	 * @var array
	 * @since 1.0.0
	 */
	public $defaults = array(

		// Your Segment.io API key that we'll use to initialize analytics.js.
		'api_key'                   => '',

		// Whether or not we should ignore users of above a certain permissions
		// level (eg. `11` ignores nobody and `8` ignores Administrators).
		'ignore_user_level'         => 11,

		// Whether or not we should track events for posts. This also includes
		// custom post types, for example a Product post type.
		'track_posts'               => 1,

		// Whether or not we should track events for pages. This includes the
		// Home page and things like the About page, Contact page, etc.
		'track_pages'               => 1,

		// Whether or not we should track custom events for archive pages like
		// the Category archive or the Author archive.
		'track_archives'            => 1,

		// Whether or not we should track custom events for comments.
		'track_comments'            => 1,

		// Whether or not we should use Intercom's Secure Mode.
		'use_intercom_secure_mode'  => '',

		// Whether or not we should track custom events for searches.
		'track_searches'            => 1,

		// Whether or not we should track custom events for users logging in.
		'track_logins'              => 1,

		// Whether or not we should track custom events for viewing the logged in page.
		'track_login_page'          => false,

		// Whether or not we should track custom events for the Search page.
		'exclude_custom_post_types' => array(),
	);

	/**
	 * Retrieves the one true instance of Segment_Analytics_WordPress
	 *
	 * @since  1.0.0
	 * @return object Singleton instance of Segment_Analytics_WordPress
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Segment_Analytics_WordPress ) ) {

			self::$instance = new Segment_Analytics_WordPress();

			self::$instance->setup_constants();
			self::$instance->load_textdomain();
			self::$instance->admin_hooks();
			self::$instance->frontend_hooks();
			self::$instance->include_files();

			self::$instance->analytics = Segment_Api_Js::get_instance();
		}

		return self::$instance;
	}


	/**
	 * Sets up constants for file paths, folders, URLs and directory names related to the plugin.
	 *
	 * @since 1.0.0
	 */
	public function setup_constants() {

		// Set the core file path.
		define( 'SEG_FILE_PATH', dirname( __FILE__ ) );

		// Define the path to the plugin folder.
		define( 'SEG_DIR_NAME', basename( SEG_FILE_PATH ) );

		// Define the URL to the plugin folder.
		define( 'SEG_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
		define( 'SEG_URL', plugins_url( '', __FILE__ ) );

	}

	/**
	 * Returns Settings option name.
	 *
	 * @since  1.0.0
	 *
	 * @return string Settings option name
	 */
	public function get_option_name() {
		return $this->option;
	}

	/**
	 * Hooks into actions and filters that affect the administration areas.
	 *
	 * @since  1.0.0
	 */
	public function admin_hooks() {
		add_action( 'admin_menu', array( self::$instance, 'admin_menu' ) );
		add_filter( 'plugin_action_links', array( self::$instance, 'plugin_action_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( self::$instance, 'plugin_row_meta' ), 10, 2 );
		add_action( 'admin_init', array( self::$instance, 'register_settings' ) );
	}

	/**
	 * Includes core classes.
	 * Currently includes Segment_Cookie and eCommerce bootstrap.
	 *
	 * @uses  do_action() Allows other plugins to hook in before or after everything is bootstrapped.
	 *
	 * @since  1.0.0
	 */
	public function include_files() {

		do_action( 'segment_pre_include_files', self::$instance );

		include_once SEG_FILE_PATH . '/includes/class.segment-settings.php';
		include_once SEG_FILE_PATH . '/includes/class.segment-cookie.php';

		include_once SEG_FILE_PATH . '/includes/api/javascript.php';

		include_once SEG_FILE_PATH . '/integrations/ecommerce.php';
		include_once SEG_FILE_PATH . '/integrations/intercom.php';

		do_action( 'segment_include_files', self::$instance );
	}

	/**
	 * Hooks into actions and filters that affect the front-end.
	 * That is to say, this is where the magic happens.
	 *
	 * @since  1.0.0
	 */
	public function frontend_hooks() {

		add_action( 'wp_head', array( self::$instance, 'wp_head' ), 9 );
		add_action( 'admin_head', array( self::$instance, 'wp_head' ), 9 );
		add_action( 'login_head', array( self::$instance, 'wp_head' ), 9 );
		add_action( 'wp_footer', array( self::$instance, 'wp_footer' ), 9 );
		add_action( 'login_footer', array( self::$instance, 'wp_footer' ), 9 );
		add_action( 'admin_footer', array( self::$instance, 'wp_footer' ), 9 );
		add_action( 'wp_insert_comment', array( self::$instance, 'insert_comment' ), 9, 2 );
		add_action( 'wp_login', array( self::$instance, 'login_event' ), 9, 2 );
		add_action( 'user_register', array( self::$instance, 'user_register' ), 9 );
	}

	/**
	 * Returns array of settings.
	 *
	 * @since  1.0.4
	 */
	public function get_default_settings() {
		return apply_filters(
			'segment_default_settings',
			array(
				'general'  => array(
					'title'    => __( 'General', 'segment' ),
					'callback' => array( 'Segment_Settings', 'general_section_callback' ),
					'fields'   => array(
						array(
							'name'     => 'api_key',
							'title'    => __( 'Segment API Write Key', 'segment' ),
							'callback' => array( 'Segment_Settings', 'api_key_callback' ),
						),
					),
				),
				'advanced' => array(
					'title'    => __( 'Advanced Settings', 'segment' ),
					'callback' => array( 'Segment_Settings', 'advanced_section_callback' ),
					'fields'   => array(
						array(
							'name'     => 'ignore_user_level',
							'title'    => __( 'Users to Ignore', 'segment' ),
							'callback' => array( 'Segment_Settings', 'ignore_user_level_callback' ),
						),
						array(
							'name'     => 'track_posts',
							'title'    => __( 'Track Posts', 'segment' ),
							'callback' => array( 'Segment_Settings', 'track_posts_callback' ),
						),
						array(
							'name'     => 'exclude_post_types',
							'title'    => __( 'Exclude Post Types', 'segment' ),
							'callback' => array( 'Segment_Settings', 'exclude_custom_post_types' ),
						),
						array(
							'name'     => 'track_pages',
							'title'    => __( 'Track Pages', 'segment' ),
							'callback' => array( 'Segment_Settings', 'track_pages_callback' ),
						),
						array(
							'name'     => 'track_archives',
							'title'    => __( 'Track Archives', 'segment' ),
							'callback' => array( 'Segment_Settings', 'track_archives_callback' ),
						),
						array(
							'name'     => 'track_archives',
							'title'    => __( 'Track Archives', 'segment' ),
							'callback' => array( 'Segment_Settings', 'track_archives_callback' ),
						),
						array(
							'name'     => 'track_comments',
							'title'    => __( 'Track Comments', 'segment' ),
							'callback' => array( 'Segment_Settings', 'track_comments_callback' ),
						),
						array(
							'name'     => 'track_logins',
							'title'    => __( 'Track Logins', 'segment' ),
							'callback' => array( 'Segment_Settings', 'track_logins_callback' ),
						),
						array(
							'name'     => 'track_login_page',
							'title'    => __( 'Track Login Page Views', 'segment' ),
							'callback' => array( 'Segment_Settings', 'track_login_page_callback' ),
						),
						array(
							'name'     => 'track_searches',
							'title'    => __( 'Track Searches', 'segment' ),
							'callback' => array( 'Segment_Settings', 'track_search_callback' ),
						),
						array(
							'name'     => 'use_intercom_secure_mode',
							'title'    => __( 'Intercom API Secret', 'segment' ),
							'callback' => array( 'Segment_Settings', 'use_intercom_secure_mode' ),
						),
					),
				),

			)
		);
	}

	/**
	 * Registers our settings, fields and sections using the WordPress Settings API.
	 *
	 * Developers should use the `segment_default_settings` filter to add settings.
	 * They should also use the `segment_settings_core_validation` filter to validate
	 * any settings they add.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_settings() {

		$settings = $this->get_default_settings();

		register_setting( self::SLUG, $this->get_option_name(), array( 'Segment_Settings', 'core_validation' ) );

		foreach ( $settings as $section_name => $section ) {

			add_settings_section(
				$section_name,
				$section['title'],
				$section['callback'],
				self::SLUG
			);

			foreach ( $section['fields'] as $field ) {

				add_settings_field(
					$field['name'],
					$field['title'],
					$field['callback'],
					self::SLUG,
					$section_name
				);

			}
		}

	}

	/**
	 * Empty constructor, as we prefer to get_instance().
	 *
	 * @since 1.0.0
	 */
	public function __construct() {}

	/**
	 * Loads the properly localized PO/MO files
	 *
	 * @since  1.0.0
	 */
	public function load_textdomain() {
		// Set filter for plugin's languages directory.
		$segment_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$segment_lang_dir = apply_filters( 'segment_languages_directory', $segment_lang_dir );

		// Traditional WordPress plugin locale filter.
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$locale = apply_filters( 'plugin_locale', get_locale(), 'segment' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'segment', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $segment_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/segment/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/segment folder.
			load_textdomain( 'segment', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/analytics-wordpress/languages/ folder.
			load_textdomain( 'segment', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'segment', false, $segment_lang_dir );
		}
	}

	/**
	 * Outputs analytics javascript and analytics.identify() snippet in head for admin, login page and wp_head.
	 *
	 * @since 1.0.0
	 */
	public function wp_head() {

		// Figure out whether the user should be ignored or not.
		$ignore = false;

		$settings = $this->get_settings();
		$user     = wp_get_current_user();

		if ( $user->user_level >= $settings['ignore_user_level'] ) {
			$ignore = true;
		}

		// Render the snippet.
		self::$instance->analytics->initialize( $settings, $ignore );
	}

	/**
	 * Outputs analytics.track()/.page()/ snippet in head for admin, login page and wp_footer.
	 *
	 * @since 1.0.0
	 */
	public function wp_footer() {

		// Identify the user if the current user merits it.
		$identify = $this->get_current_user_identify();

		if ( $identify ) {

			if ( ! isset( $identify['options'] ) ) {
				$identify['options'] = array();
			}

			self::$instance->analytics->identify( $identify['user_id'], $identify['traits'], $identify['options'] );
		}

		// Track a custom page view event if the current page merits it.
		$track = $this->get_current_page_track();
		$page  = $this->get_current_page();

		if ( $track ) {
			$http_event = isset( $track['http_event'] ) ? $track['http_event'] : false;
			self::$instance->analytics->track( $track['event'], $track['properties'], array(), $http_event );
		}

		if ( $page ) {
			self::$instance->analytics->page( $page['page'], $page['properties'] );
		}
	}

	/**
	 * Uses Segment_Cookie::set_cookie() to notify Segment that a comment has been left.
	 *
	 * @param  int    $id      Comment ID. Unused.
	 * @param  object $comment WP_Comment object Unused.
	 *
	 * @since 1.0.0
	 */
	public function insert_comment( $id, $comment ) {

		Segment_Cookie::set_cookie( 'left_comment', md5( wp_json_encode( wp_get_current_commenter() ) ) );
	}

	/**
	 * Uses Segment_Cookie::set_cookie() to notify Segment that a user has logged in.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $login Username of logged in user.
	 * @param  WP_User $user  User object of logged in user.
	 */
	public function login_event( $login, $user ) {

		Segment_Cookie::set_cookie( 'logged_in', md5( wp_json_encode( $user ) ) );
	}

	/**
	 * Uses Segment_Cookie::set_cookie() to notify Segment that a user has signed up.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $user_id Username of new user.
	 */
	public function user_register( $user_id ) {

		Segment_Cookie::set_cookie( 'signed_up', wp_json_encode( $user_id ) );
	}

	/**
	 * Adds "Settings" link to plugin row.
	 *
	 * @param  array  $links Array of links on plugin action row.
	 * @param  string $file  Basename of file.
	 * @return array  $links Modified array of links on plugin action row.
	 */
	public function plugin_action_links( $links, $file ) {

		// Not for other plugins, silly. NOTE: This doesn't work properly when
		// the plugin for testing is a symlink!! If you change this, test it.
		// Note: Works fine as of 3.9, see @link: https://core.trac.wordpress.org/ticket/16953
		if ( plugin_basename( __FILE__ ) !== $file ) {
			return $links;
		}

		// Add settings link to the beginning of the row of links.
		$settings_link = sprintf( '<a href="options-general.php?page=' . self::SLUG . '">%s</a>', __( 'Settings', 'segment' ) );

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Adds Settings and Documentation links to plugin row meta.
	 *
	 * @since  1.0.0
	 *
	 * @param array  $plugin_meta An array of the plugin's metadata,
	 *                            including the version, author,
	 *                            author URI, and plugin URI.
	 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
	 *
	 * @return array        Modified array of plugin metadata.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		// Not for other plugins, silly. NOTE: This doesn't work properly when
		// the plugin for testing is a symlink!! If you change this, test it.
		// Note: Works fine as of 3.9, see @link: https://core.trac.wordpress.org/ticket/16953
		if ( plugin_basename( __FILE__ ) !== $plugin_file ) {
			return $plugin_meta;
		}

		// Add a settings and docs link to the end of the row of links row of links.
		$settings_link = sprintf( '<a href="options-general.php?page=' . self::SLUG . '">%s</a>', __( 'Settings', 'segment' ) );
		$docs_link     = sprintf( '<a href="https://segment.io/plugins/wordpress" target="_blank">%s</a>', __( 'Docs', 'segment' ) );

		array_push( $plugin_meta, $settings_link, $docs_link );

		return $plugin_meta;
	}

	/**
	 * Adds "Analytics" Menu item to admin area.
	 *
	 * @since  1.0.0
	 */
	public function admin_menu() {

		add_options_page(
			apply_filters( 'segment_admin_menu_page_title', __( 'Analytics', 'segment' ) ), // Page Title
			apply_filters( 'segment_admin_menu_menu_title', __( 'Analytics', 'segment' ) ), // Menu Title
			apply_filters( 'segment_admin_settings_capability', 'manage_options' ),  // Capability Required
			self::SLUG,                                                              // Menu Slug
			array( $this, 'admin_page' )                                             // Function
		);

	}

	/**
	 * The callback used to build out the admin settings area.
	 *
	 * @since 1.0.0
	 */
	public function admin_page() {

		// Make sure the user has the required permissions to view the settings.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you don\'t have the permissions to access this page.', 'segment' ) );
		}

		include_once SEG_FILE_PATH . '/templates/settings.php';
	}

	/**
	 * Retrieves settings array.
	 *
	 * @since  1.0.0
	 *
	 * @uses apply_filters() Applies 'segment_get_settings' filter to allow other developers to override.
	 *
	 * @return array Array of settings.
	 */
	public function get_settings() {
		return apply_filters( 'segment_get_settings', get_option( $this->option ), $this );
	}

	/**
	 * Updates settings array.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $settings Array of settings
	 * @uses   apply_filters() Applies 'segment_get_settings' filter to allow other developers to override.
	 *
	 * @deprecated Deprecated in 1.0.0
	 *
	 * @return array Array of settings.
	 */
	private function set_settings( $settings ) {
		return update_option( $this->option, $settings );
	}

	/**
	 * Based on the current user or commenter, see if we have enough information
	 * to record an `identify` call. Since commenters don't have IDs, we
	 * identify everyone by their email address.
	 *
	 * @since  1.0.0
	 *
	 * @return bool|array Returns false if there is no commenter or logged in user
	 *                    An array of the user ID and traits if there is an authenticated user.
	 */
	public function get_current_user_identify() {
		$settings = $this->get_settings();

		$user      = wp_get_current_user();
		$commenter = array_filter( wp_get_current_commenter() );
		$identify  = false;

		if ( is_user_logged_in() && $user ) {
			// We've got a logged-in user.
			// http://codex.wordpress.org/Function_Reference/wp_get_current_user
			$identify = array(
				'user_id' => $user->user_email,
				'traits'  => array(
					'username'  => $user->user_login,
					'email'     => $user->user_email,
					'firstName' => $user->user_firstname,
					'lastName'  => $user->user_lastname,
					'url'       => $user->user_url,
				),
			);
		} elseif ( $commenter ) {
			// We've got a commenter.
			// http://codex.wordpress.org/Function_Reference/wp_get_current_commenter
			$identify = array(
				'user_id' => $commenter['comment_author_email'],
				'traits'  => array(
					'email' => $commenter['comment_author_email'],
					'name'  => $commenter['comment_author'],
					'url'   => $commenter['comment_author_url'],
				),
			);
		}

		if ( $identify ) {
			// Clean out empty traits before sending it back.
			$identify['traits'] = array_filter( $identify['traits'] );
		}

		/**
		 * Allows developers to modify the entire $identify call.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'segment_get_current_user_identify', $identify, $settings, $this );
	}

	/**
	 * Used to track the current event.  Used for analytics.track().
	 *
	 * @since  1.0.0
	 *
	 * @return array Array containing the page being tracked along with any additional properties.
	 */
	private function get_current_page_track() {

		$settings = $this->get_settings();

		// Login Event
		// --------
		if ( $settings['track_logins'] ) {

			$user = wp_get_current_user();
			$hash = md5( wp_json_encode( $user ) );

			if ( Segment_Cookie::get_cookie( 'logged_in', $hash ) ) {

				$track = array(
					'event'      => __( 'Logged In', 'segment' ),
					'properties' => array(
						'username'  => $user->user_login,
						'email'     => $user->user_email,
						'name'      => $user->display_name,
						'firstName' => $user->user_firstname,
						'lastName'  => $user->user_lastname,
						'url'       => $user->user_url,
					),
					'http_event' => 'logged_in',
				);

			}
		}

		// Posts
		// -----
		if ( $settings['track_posts'] ) {
			// A post or a custom post. `is_single` also returns attachments, so
			// we filter those out. The event name is based on the post's type,
			// and is uppercased.
			if ( is_single() && ! is_attachment() ) {

				if ( ! self::is_excluded_post_type() ) {
					$categories = implode( ', ', wp_list_pluck( get_the_category( get_the_ID() ), 'name' ) );
					$track      = array(
						// translators: Post type
						'event'      => sprintf( __( 'Viewed %s', 'segment' ), ucfirst( get_post_type() ) ),
						'properties' => array(
							'title'    => single_post_title( '', false ),
							'category' => $categories,
						),
					);
				}
			}
		}

		// Pages
		// -----
		if ( $settings['track_pages'] ) {
			// The front page of their site, whether it's a page or a list of
			// recent blog entries. `is_home` only works if it's not a page,
			// that's why we don't use it.
			if ( is_front_page() ) {
				$track = array(
					'event' => __( 'Viewed Home Page', 'segment' ),
				);
			} elseif ( is_page() ) {
				// A normal WordPress page.
				$track = array(
					// translators: Page title
					'event' => sprintf( __( 'Viewed %s Page', 'segment' ), single_post_title( '', false ) ),
				);
			}
		}

		// Archives
		// --------
		if ( $settings['track_archives'] ) {
			if ( is_author() ) {
				// An author archive page. Check the `wp_title` docs to see how they
				// get the title of the page, cuz it's weird.
				// http://core.trac.wordpress.org/browser/tags/3.5.1/wp-includes/general-template.php#L0
				$author = get_queried_object();
				$track  = array(
					'event'      => __( 'Viewed Author Page', 'segment' ),
					'properties' => array(
						'author' => $author->display_name,
					),
				);
			} elseif ( is_tag() ) {
				// A tag archive page. Use `single_tag_title` to get the name.
				// http://codex.wordpress.org/Function_Reference/single_tag_title
				$track = array(
					'event'      => __( 'Viewed Tag Page', 'segment' ),
					'properties' => array(
						'	tag' => single_tag_title( '', false ),
					),
				);
			} elseif ( is_category() ) {
				// A category archive page. Use `single_cat_title` to get the name.
				// http://codex.wordpress.org/Function_Reference/single_cat_title
				$track = array(
					'event'      => __( 'Viewed Category Page', 'segment' ),
					'properties' => array(
						'category' => single_cat_title( '', false ),
					),
				);
			}
		}

		// Comments
		// --------
		if ( $settings['track_comments'] ) {

			$commenter = array_filter( wp_get_current_commenter() );

			if ( $commenter ) {
				$hash = md5( wp_json_encode( $commenter ) );

				if ( Segment_Cookie::get_cookie( 'left_comment', $hash ) ) {

					$track = array(
						'event'      => __( 'Commented', 'segment' ),
						'properties' => array(
							'commenter' => $commenter,
						),
						'http_event' => 'left_comment',
					);
				}
			}
		}

		// Login Page
		// --------
		if ( $settings['track_login_page'] ) {

			if ( did_action( 'login_init' ) ) {

				$track = array(
					'event' => __( 'Viewed Login Page', 'segment' ),
				);

			}
		}

		// Searches
		// --------
		if ( $settings['track_searches'] ) {
			// The search page.
			if ( is_search() ) {
				$track = array(
					'event'      => __( 'Viewed Search Page', 'segment' ),
					'properties' => array(
						'query' => get_query_var( 's' ),
					),
				);
			}
		}

		if ( Segment_Cookie::get_cookie( 'signed_up' ) ) {

			$user_id = json_decode( Segment_Cookie::get_cookie( 'signed_up' ) );
			$user    = get_user_by( 'id', $user_id );

			add_filter( 'segment_get_current_user_identify', array( self::$instance, 'new_user_identify' ) );

			$track = array(
				'event'      => __( 'User Signed Up', 'segment' ),
				'properties' => array(
					'username'  => $user->user_login,
					'email'     => $user->user_email,
					'name'      => $user->display_name,
					'firstName' => $user->user_firstname,
					'lastName'  => $user->user_lastname,
					'url'       => $user->user_url,
				),
				'http_event' => 'signed_up',
			);

		}

		// We don't have a page we want to track.
		if ( ! isset( $track ) ) {
			$track = false;
		}

		if ( $track ) {
			// All of these are checking for pages, and we don't want that to throw
			// off Google Analytics's bounce rate, so mark them `noninteraction`.
			$track['properties']['nonInteraction'] = true;

			// Clean out empty properties before sending it back.
			$track['properties'] = array_filter( $track['properties'] );
		}

		return apply_filters( 'segment_get_current_page_track', $track, $settings, $this );
	}

	/**
	 * Filters the .identify() call with the newly signed up user.
	 * This is helpful, as the user will often times not be authenticated after signing up.
	 *
	 * @since  1.0.0
	 *
	 * @param  mixed $identify   False if no user is found, array of traits and ID if a user is found.
	 * @return array $identify   Array of traits for newly signed up user.
	 */
	public function new_user_identify( $identify ) {

		if ( Segment_Cookie::get_cookie( 'signed_up' ) ) {

			$user_id = json_decode( Segment_Cookie::get_cookie( 'signed_up' ) );
			$user    = get_user_by( 'id', $user_id );

			$identify = array(
				'user_id' => $user->user_email,
				'traits'  => array(
					'username'  => $user->user_login,
					'email'     => $user->user_email,
					'firstName' => $user->user_firstname,
					'lastName'  => $user->user_lastname,
					'url'       => $user->user_url,
				),
			);
		}

		return $identify;
	}

	/**
	 * Used to track the current page.  Used for analytics.page().
	 * Unlike get_current_page_track(), we use this primarily as a pub-sub observer for other core events.
	 * This makes it much more manageable for other developers to hook and unhook from it as needed.
	 *
	 * @since  1.0.0
	 *
	 * @return array Array containing the page being tracked along with any additional properties.
	 */
	private function get_current_page() {

		$page = apply_filters( 'segment_get_current_page', false, $this->get_settings(), $this );

		if ( $page ) {
			$page['properties'] = is_array( $page['properties'] ) ? $page['properties'] : array();
			// All of these are checking for pages, and we don't want that to throw
			// off Google Analytics's bounce rate, so mark them `noninteraction`.
			$page['properties']['nonInteraction'] = true;

			// Clean out empty properties before sending it back.
			$page['properties'] = array_filter( $page['properties'] );
		}

		return $page;
	}

	/**
	 * Kept for backwards compatibility, as clean_array() used to be, essentially, a round-about to array_filter().
	 *
	 * @since  1.0.0
	 *
	 * @deprecated
	 *
	 * @param  array $array Array to clean.
	 * @return array        Filtered array.
	 */
	private function clean_array( $array ) {
		return array_filter( $array );
	}

	/**
	 * Used in our activation hook to set up our default settings.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public static function setup_settings() {

		$settings = get_option( self::get_instance()->get_option_name() );

		if ( ! empty( $settings ) ) {
			return;
		}

		update_option( self::get_instance()->get_option_name(), self::get_instance()->defaults );
	}

	/**
	 * Helper function, essentially a replica of stripslashes_deep, but for esc_js.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $value Handles arrays, strings and objects that we are trying to escape for JS.
	 * @return mixed  $value esc_js()'d value.
	 */
	public static function esc_js_deep( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map( array( __CLASS__, 'esc_js_deep' ), $value );
		} elseif ( is_object( $value ) ) {
			$vars = get_object_vars( $value );
			foreach ( $vars as $key => $data ) {
				$value->{$key} = self::esc_js_deep( $data );
			}
		} elseif ( is_string( $value ) ) {
			$value = esc_js( $value );
		}

		return $value;
	}

	/**
	 * Checks if current post type is excluded or not.
	 * Intended to be used on singular views.
	 *
	 * @since  1.0.0
	 *
	 * @return boolean Whether or not post type is excluded
	 */
	public static function is_excluded_post_type() {
		$settings = self::get_instance()->get_settings();

		$cpts = isset( $settings['exclude_custom_post_types'] ) ? $settings['exclude_custom_post_types'] : array();

		return in_array( get_post_type(), $cpts, true );
	}

}

register_activation_hook( __FILE__, array( 'Segment_Analytics_WordPress', 'setup_settings' ) );
add_action( 'plugins_loaded', 'Segment_Analytics_WordPress::get_instance' );
