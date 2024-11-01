<?php
/**
 * Plugin Name: Wyvern Toolkit
 * Plugin URI: https://wyvernwp.github.io/plugins/wyvern-toolkit/
 * Description: Wyvern Toolkit is a fast, reliable, and affordable professional WordPress plugin that does everything you need to create and manage an amazing website. It comes with all the essentials so you do not have to install multiple plugins for each task.
 * Author: codewyvern
 * Author URI: https://codewyvern.com/
 * Version: 1.0.6
 * Text Domain: wyvern-toolkit
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WyvernToolkit
 */

/**
 *========================================================
 * * NOTE: This file is generated automatically.
 * * Please do not make any changes in this file directly.
 *========================================================
 */

use WyvernToolkit\WyvernToolkit;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wyvern Toolkit core file.
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_FILE', __FILE__ );

/**
 * Wyvern Toolkit version.
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_VERSION', ( get_file_data( WYVERN_TOOLKIT_FILE, array( 'Version' => 'Version' ) )['Version'] ) );

/**
 * Path to Wyvern Toolkit plugin folder.
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_PATH', trailingslashit( plugin_dir_path( WYVERN_TOOLKIT_FILE ) ) );

/**
 * URL to Wyvern Toolkit plugin folder.
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_URL', trailingslashit( plugin_dir_url( WYVERN_TOOLKIT_FILE ) ) );

/**
 * Path to Wyvern Toolkit modules folder.
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_MODULES_PATH', trailingslashit( WYVERN_TOOLKIT_PATH . 'modules' ) );

/**
 * URL to Wyvern Toolkit modules folder.
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_MODULES_URL', trailingslashit( WYVERN_TOOLKIT_URL . 'modules' ) );

$uploads_dir = wp_get_upload_dir();

/**
 * Path to wyvern-toolkit-store folder.
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_STORE_PATH', ! empty( $uploads_dir['basedir'] ) ? wp_normalize_path( trailingslashit( "{$uploads_dir['basedir']}/wyvern-toolkit-store" ) ) : ''  );

/**
 * Namespace for modules core.
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_MODULES_NAMESPACE', 'WyvernToolkit\Modules' );

/**
 * Database metakey prefix..
 *
 * @since 1.0.0
 */
define( 'WYVERN_TOOLKIT_METAKEY_PREFIX', 'wyvern_toolkit_' );

if ( ! defined( 'WYVERN_TOOLKIT_STATS_URL' ) ) {

	/**
	* Stats base url.
	*
	* @since 1.0.4
	*/
	define( 'WYVERN_TOOLKIT_STATS_URL', 'https://stats.codewyvern.com' );
}

/**
 * Load files.
 */
require_once WYVERN_TOOLKIT_PATH . 'includes/requires.php';

/**
 * Init Wyvern Toolkit.
 *
 * @return WyvernToolkit\WyvernToolkit
 */
function wyvern_toolkit() {
	return WyvernToolkit::instance();
}
wyvern_toolkit();
