<?php
/**
 * Class for handling Wyvern Toolkit settings.
 *
 * @package WyvernToolkit
 * @since 1.0.4
 */

namespace WyvernToolkit;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling Wyvern Toolkit settings.
 *
 * @since 1.0.4
 */
class SettingsManager {

	const OPTION_KEY = 'settings';

	public static function save( $key, $data = null ) {
		$settings = self::get();

		$settings[ $key ] = $data;

		return Helpers::update_option( self::OPTION_KEY, $settings, true );
	}

	public static function get() {
		$settings = Helpers::get_decoded_option( self::OPTION_KEY );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return $settings;
	}

	public static function reset() {}

}
