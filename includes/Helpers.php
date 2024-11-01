<?php
/**
 * Static class for providing helper methods.
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
 * Wyvern Toolkit helper class.
 */
class Helpers {

	/**
	 * Check if user is on localhost.
	 *
	 * @return boolean
	 * @since 1.0.4
	 */
	public static function is_localhost() {

		if ( empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return false;
		}

		return in_array(
			$_SERVER['REMOTE_ADDR'],
			array(
				'127.0.0.1',
				'::1'
			)
		);
	}

	public static function convert_path_to_url( $path ) {

		if ( ! $path ) {
			return;
		}

		return str_replace(
			wp_normalize_path( untrailingslashit( ABSPATH ) ),
			site_url(),
			wp_normalize_path( $path )
		);

	}

	/**
	 * Pretty permalink independent way for getting rest api base url.
	 *
	 * @param string $base REST API Base to join in API url.
	 * @return string
	 */
	public static function get_rest_url( $base = '' ) {
		$url_end = get_option( 'permalink_structure' ) ? '/wp-json' : '/?rest_route=';

		return home_url( "{$url_end}/{$base}" );
	}

	/**
	 * Updates the value of an option that was already added.
	 *
	 * You do not need to serialize values. If the value needs to be serialized,
	 * then it will be serialized before it is inserted into the database.
	 * Remember, resources cannot be serialized or added as an option.
	 *
	 * If the option does not exist, it will be created.
	 * This function is designed to work with or without a logged-in user. In terms of security,
	 * plugin developers should check the current user's capabilities before updating any options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option   Name of the option to update without prefix. Expected to not be SQL-escaped.
	 * @param mixed  $value    Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 *
	 * @return bool True if the value was updated, false otherwise.
	 */
	public static function update_option( $option, $value, $encode = false ) {
		$value = $encode ? wp_json_encode( $value ) : $value;
		return update_option( WYVERN_TOOLKIT_METAKEY_PREFIX . $option, $value );
	}

	/**
	 * Returns ajax response.
	 *
	 * @return void
	 */
	public static function get_ajax_response() {

		$inputstream = file_get_contents( 'php://input' );
		$data_decode = (array) json_decode( $inputstream, true );

		return self::sanitize_array( $data_decode );

	}

	/**
	 * Returns submitted data.
	 *
	 * @param string $type
	 * @param boolean $sanitize
	 * @return void
	 */
	public static function get_submitted_data( $type = 'request', $sanitize = true ) {

		$data = array();

		switch ( $type ) {
			case 'post':
				$data = $_POST;
				break;
			case 'get':
				$data = $_GET;
				break;
			default:
				$data = $_REQUEST;
				break;
		}

		return $sanitize ? self::sanitize_array( $data ) : $data;
	}

	/**
	 * Retrieves an option value based on an option name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option  Name of the option to retrieve without prefix. Expected to not be SQL-escaped.
	 * @param mixed  $default Optional. Default value to return if the option does not exist.
	 * @return mixed Value of the option. A value of any type may be returned, including
	 *               scalar (string, boolean, float, integer), null, array, object.
	 *               Scalar and null values will be returned as strings as long as they originate
	 *               from a database stored option value. If there is no option in the database,
	 *               boolean `false` is returned.
	 */
	public static function get_option( $option, $default ) {
		return get_option( WYVERN_TOOLKIT_METAKEY_PREFIX . $option, $default );
	}

	public static function delete_option( $option ) {
		return delete_option( WYVERN_TOOLKIT_METAKEY_PREFIX . $option );
	}

	public static function get_decoded_option( $option, $default = '{}' ) {
		return json_decode( get_option( WYVERN_TOOLKIT_METAKEY_PREFIX . $option, $default ), true );
	}

	/**
	 * Sanitize value by type.
	 */
	public static function sanitize_by_type( $value, $type = 'string' ) {
		$sanitize_functions = array(
			'boolean' => 'boolval',
			'integer' => 'floatval',
			'string'  => 'sanitize_text_field',
		);

		if ( isset( $sanitize_functions[ $type ] ) ) {
			if ( is_scalar( $value ) ) {
				return \call_user_func( $sanitize_functions[ $type ], $value );
			} elseif ( is_array( $value ) ) {
				return array_map( 'sanitize_text_field', $value );
			}
		} else {
			return is_scalar( $value ) ? sanitize_text_field( $value ) : array_map( 'sanitize_text_field', $value );
		}
	}

	/**
	 * Recursively sanitize array data.
	 *
	 * @param array $data
	 * @return array
	 */
	public static function sanitize_array( array $data ) {
		foreach ( $data as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = self::sanitize_array( $value );
			} else {
				if ( is_int( $value ) ) {
					$value = (int) $value;
				} elseif ( is_string( $value ) ) {
					$value = sanitize_text_field( wp_unslash( $value ) );
				}
			}
		}

		return $data;
	}

}
