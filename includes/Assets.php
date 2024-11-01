<?php
/**
 * Core class for handling assets.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit;

use WyvernToolkit\Traits\Singleton;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core class for handling assets.
 *
 * @since 1.0.0
 */
class Assets {

	use Singleton;

	/**
	 * Init class.
	 */
	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin' ) );
		add_action( 'wp_ajax_wyvern_toolkit_pointer_dismiss_notice', array( $this, 'dismiss_pointer_notice' ) );
	}

	/**
	 * Localized data to admin scripts.
	 *
	 * @return array
	 */
	public function admin_localize() {

		$codemirror_configs = array();

		$codemirror_configs['css'] = wp_enqueue_code_editor( array( 'type' => 'text/css'));
		$codemirror_configs['js']  = wp_enqueue_code_editor( array( 'type' => 'text/javascript'));

		return apply_filters(
			'wyvern_toolkit_filter_admin_localize',
			array(
				'version'           => WYVERN_TOOLKIT_VERSION,
				'adminURL'          => trailingslashit( admin_url() ),
				'ajaxURL'           => add_query_arg( '_nonce', wp_create_nonce( 'wyvernToolkit-ajax' ), admin_url( '/admin-ajax.php' ) ),
				'rootApiUrl'        => esc_url_raw( untrailingslashit( rest_url() ) ),
				'nonce'             => wp_create_nonce( 'wp_rest' ),
				'cmConfig'          => $codemirror_configs,
				'settings'          => SettingsManager::get(),
				'displayStatsModal' => is_null( Stats::is_allowed() ),
			)
		);
	}

	/**
	 * Returns dismissed notices array.
	 *
	 * @return array
	 * @since 1.0.4
	 */
	public function get_dismissed_notices() {
		return Helpers::get_option( 'dismissed_notices', array() );
	}

	/**
	 * Dismiss pointer notice.
	 *
	 * @return void
	 * @since 1.0.4
	 */
	public function dismiss_pointer_notice() {

		check_ajax_referer( 'wyvernToolkit-ajax', '_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Sorry, you are not allowed to dismiss notice.', 'wyvern-toolkit') );
		}

		$notices = $this->get_dismissed_notices();

		$data = Helpers::get_submitted_data( 'get' );

		$notice_name = ! empty( $data['notice_name'] ) ? $data['notice_name'] : '';

		if ( $notice_name ) {
			$notices[ $notice_name ] = true;
			Helpers::update_option( 'dismissed_notices', $notices );
		}

		wp_send_json_success();

	}

	/**
	 * Returns all WP pointers
	 *
	 * @return array
	 */
	public function get_pointers() {
		$pointers = array();

		$pointers['welcome'] = array(
			'target'  => '#toplevel_page_wyvern-toolkit',
			'edge'    => 'left',
			'align'   => 'right',
			'content' => 'Thank you for installing the <strong>Wyvern Toolkit</strong> plugin!'
		);

		return $pointers;
	}

	/**
	 * Styles and scripts for admin side.
	 *
	 * @return void
	 */
	public function admin( $hook ) {

		$scripts_bundle_dep = include WYVERN_TOOLKIT_PATH . 'build/admin/scripts.bundle.asset.php';

		$scripts_bundle_dep['dependencies'] = array( 'jquery' );

		$pointers = $this->get_pointers();

		$dismissed_notices = $this->get_dismissed_notices();

		if ( is_array( $dismissed_notices ) && ! empty( $dismissed_notices ) ) {
			foreach ( $dismissed_notices as $notice_name => $bool ) {
				if ( $bool && isset( $pointers[ $notice_name ] ) ) {
					unset( $pointers[ $notice_name ] );
				}
			}
		}

		if ( ! empty( $pointers ) && ( false === strpos( $hook, 'wyvern-toolkit' ) ) && current_user_can( 'administrator' ) ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_localize_script( 'wp-pointer', 'wyvern_toolkit_pointers', $pointers );
		}

		wp_register_script( 'wyvern-toolkit-script-bundle', WYVERN_TOOLKIT_URL . 'build/admin/scripts.bundle.js', $scripts_bundle_dep['dependencies'], $scripts_bundle_dep['version'], true );
		wp_enqueue_script( 'wyvern-toolkit-script-bundle' );

		wp_localize_script( 'wyvern-toolkit-script-bundle', 'wyvernToolkit', $this->admin_localize() );

		if ( false !== strpos( $hook, 'wyvern-toolkit' ) ) {
			$deps = include WYVERN_TOOLKIT_PATH . 'build/admin/index.asset.php';

			wp_enqueue_style( 'wyvern-toolkit-style', WYVERN_TOOLKIT_URL . 'build/admin/index.css' );

			wp_register_script( 'wyvern-toolkit-script', WYVERN_TOOLKIT_URL . 'build/admin/index.js', $deps['dependencies'], $deps['version'], true );
			wp_enqueue_script( 'wyvern-toolkit-script' );
		}

	}

}
