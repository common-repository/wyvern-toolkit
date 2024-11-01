<?php
/**
 * Core class for Wyvern Toolkit plugin.
 *
 * @since 1.0.0
 * @package WyvernToolkit
 */

namespace WyvernToolkit;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WyvernToolkit\API\API;
use WyvernToolkit\Traits\Singleton;

/**
 * Core class for Wyvern Toolkit plugin,
 *
 * @since 1.0.0
 * @singleton
 */
final class WyvernToolkit {

	/**
	 * Make singleton class.
	 */
	use Singleton;

	/**
	 * Init class.
	 */
	private function __construct() {
		register_activation_hook( WYVERN_TOOLKIT_FILE, array( $this, 'on_activation' ) );
		register_deactivation_hook( WYVERN_TOOLKIT_FILE, array( $this, 'on_deactivation' ) );
		add_action( 'upgrader_process_complete', array( $this, 'on_update' ), 12, 2 );

		$this->init();
	}

	/**
	 * Scripts to run on Wyvern Toolkit activation,
	 *
	 * @return void
	 */
	public function on_activation() {
		Store::init();
		Stats::send();

		/**
		 * @since 1.0.5
		 */
		do_action( 'wyvern_toolkit_is_activated' );
	}

	/**
	 * Scripts to run on Wyvern Toolkit update/upgrade.
	 *
	 * @return void
	 * @since 1.0.5
	 */
	public function on_update( \WP_Upgrader $upgrader, $hook_extra ) {
		if ( ! empty( $hook_extra['action'] ) && 'update' !== $hook_extra['action'] ) {
			return;
		}

		if ( ! empty( $hook_extra['type'] ) && 'plugin' !== $hook_extra['type'] ) {
			return;
		}

		if ( ! empty( $upgrader->result['destination_name'] ) && pathinfo( WYVERN_TOOLKIT_FILE, PATHINFO_FILENAME ) !== $upgrader->result['destination_name'] ) {
			return;
		}

		Stats::send();

		do_action( 'wyvern_toolkit_is_updated' );

	}

	/**
	 * Scripts to run on Wyvern Toolkit deactivation,
	 *
	 * @return void
	 */
	public function on_deactivation() {
		Stats::unload();

		/**
		 * @since 1.0.5
		 */
		do_action( 'wyvern_toolkit_is_deactivated' );
	}

	/**
	 * Init.
	 *
	 * @return void
	 */
	protected function init() {
		new DownloadManager();
		AdminMenu::init();
		ModulesManager::instance();
		API::instance();
		Assets::instance();
		ActionLinks::init();
		Stats::init();
	}

}
