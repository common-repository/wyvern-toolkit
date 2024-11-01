<?php
/**
 * Core class for handling user statistics.
 *
 * @since 1.0.4
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
 * Core class for handling user statistics.
 *
 * @since 1.0.4
 */
class Stats {

	/**
	 * If Wyvern Toolkit is being deactivated.
	 *
	 * @var boolean
	 */
	private static $is_unloading = false;

	/**
	 * Get data for sending.
	 *
	 * @return array
	 */
	public static function get_data() {

		wp_cache_flush();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		global $wp_version;

		$data = array(
			'id'             => md5( site_url() . get_option( 'admin_email' ) ),
			'timestamp'      => time(),
			'deactivated'    => self::$is_unloading,
			'wp_version'     => $wp_version,
			'php_version'    => phpversion(),
			'is_localhost'   => Helpers::is_localhost(),
			'server'         => ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A',
			'wyvern_toolkit' => array(
				'version'        => WYVERN_TOOLKIT_VERSION,
				'active_modules' => Helpers::get_decoded_option( 'active_modules' ),
			),
			'themes' => array(
				'template'   => get_option( 'template', '' ),
				'stylesheet' => get_option( 'stylesheet', '' ),
			),
			'plugins'        => array(
				'all_plugins' => get_plugins(),
				'active'      => array_values( get_option( 'active_plugins', array() ) ),
			),
		);

		return apply_filters( 'wyvern_toolkit_filter_stats_data', $data );
	}

	/**
	 * Send data to codewyvern.
	 *
	 * @return void
	 */
	public static function send() {
		wp_remote_post(
			WYVERN_TOOLKIT_STATS_URL,
			array(
				'sslverify' => false,
				'body'      => self::get_data()
			)
		);
	}

	/**
	 * Trigger on plugin deactivation.
	 *
	 * @return void
	 */
	public static function unload() {
		self::$is_unloading = true;
		self::send();
	}

	/**
	 * Whether or not stats allowed by the user.
	 *
	 * @return bool|null Returns null if no any values is set by the user.
	 */
	public static function is_allowed() {
		return Helpers::get_option( 'send_usage_data', null );
	}

	/**
	 * Save user consent for usage data.
	 *
	 * @return void
	 */
	public static function save_consent() {

		$submitted_data = Helpers::get_submitted_data( 'post' );

		if ( ! isset( $submitted_data['wyvern_toolkit_send_usage_data'] ) ) {
			return;
		}

		$send_usage_data = absint( 'true' === $submitted_data['wyvern_toolkit_send_usage_data'] );

		if ( $send_usage_data ) {
			self::send();
		}

		Helpers::update_option( 'send_usage_data', $send_usage_data );
	}

	/**
	 * Init stats class.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'save_consent' ) );

		if ( self::is_allowed() ) {
			add_action( 'wp_version_check', array( __CLASS__, 'send' ) );
		}

	}
}
