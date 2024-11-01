<?php
/**
 * Class for plugin action links pages.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle plugin action links for this plugin.
 *
 * @since 1.0.4
 */
class ActionLinks {

	/**
	 * Register action linkss.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'plugin_action_links_' . plugin_basename( WYVERN_TOOLKIT_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Create plugin action links.
	 *
	 * @return array
	 */
	public static function action_links( $actions ) {

		$actions['modules'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( network_admin_url( '/admin.php?page=wyvern-toolkit#/modules' ) ), esc_html__( 'Modules', 'wyvern-toolkit' ) );

		return $actions;
	}

}
