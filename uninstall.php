<?php
/**
 * Uninstall
 *
 * @package analytics-wordpress
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'analytics_wordpress_options' );
