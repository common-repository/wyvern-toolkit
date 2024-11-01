<?php
/**
 * Static class for handling Wyvern Toolkit data configuration.
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
 * Static class for handling Wyvern Toolkit data configuration.
 *
 * @since 1.0.4
 */
class DataManager {

	/**
	 * Returns wpdb object instance.
	 *
	 * @return \wpdb
	 */
	public static function get_wpdb() {
		global $wpdb;
		return $wpdb;
	}

	public static function get_table_column() {
		$table_column = array(
			'options'  => 'option_name',
			'postmeta' => 'meta_key',
		);

		return apply_filters( 'wyvern_toolkit_filter_data_manager_table_column', $table_column );
	}

	/**
	 * =========================
	 * Import related methods.
	 * =========================
	 */

	public static function import( $params ) {

		if ( empty( $params['tmp_name'] ) ) {
			return;
		}

		if ( ! is_uploaded_file( $params['tmp_name'] ) ) {
			return;
		}

		$json = @file_get_contents( $params['tmp_name'] );

		if ( ! $json ) {
			return;
		}

		$data = json_decode( $json, true );

		if ( empty( $data['Results'] ) ) {
			return;
		}

		$wpdb = self::get_wpdb();

		$prefix = $wpdb->prefix;

		$results = $data['Results'];

		if ( is_array( $results ) && ! empty( $results ) ) {
			foreach ( $results as $table => $result ) {

				switch ( $table ) {
					case 'options':
						if ( is_array( $result ) && ! empty( $result ) ) {
							foreach ( $result as $res ) {
								$sql = "REPLACE INTO {$prefix}{$table} (option_name, option_value) VALUES ( '{$res['option_name']}', '{$res['option_value']}' );";
								$wpdb->query( $sql );
							}
						}

					break;

					case 'postmeta':
						if ( is_array( $result ) && ! empty( $result ) ) {
							foreach ( $result as $res ) {
								$sql = "REPLACE INTO {$prefix}{$table} (post_id, meta_key, meta_value) VALUES ( {$res['post_id']}, '{$res['meta_key']}', '{$res['meta_value']}' );";
								$wpdb->query( $sql );
							}
						}
						break;

					default:
						break;
				}

			}
		}

	}

	/**
	 * =========================
	 * Export related methods.
	 * =========================
	 */

	public static function export() {

		$wpdb = self::get_wpdb();

		$prefix = $wpdb->prefix;

		$table_column = self::get_table_column();

		$results = array();

		if ( is_array( $table_column ) && ! empty( $table_column ) ) {
			foreach ( $table_column as $table => $column ) {
				$results[ $table ] = $wpdb->get_results( "SELECT * FROM `{$prefix}{$table}` WHERE (`{$column}` LIKE '%wyvern_toolkit%' );" );
			}
		}

		global $wp_version;

		$configurations = array(
			'WordPress' => array(
				'SSL'                => is_ssl(),
				'DBPrefix'           => $prefix,
				'MultiSite'          => is_multisite(),
				'PermalinkStructure' => get_option( 'permalink_structure' ),
				'HomeUrl'            => home_url(),
				'SiteUrl'            => site_url(),
				'Version'            => $wp_version,
				'Uploads'            => wp_upload_dir(),
				'Template'           => get_option( 'template' ),
				'Stylesheet'         => get_option( 'stylesheet' ),
				'ABSPATH'            => ABSPATH,
				'WP_CONTEND_DIR'     => WP_CONTENT_DIR,
				'WP_CONTENT_URL'     => WP_CONTENT_URL,
			),
			'WyvernToolkit' =>  array(
				'Version'   => WYVERN_TOOLKIT_VERSION,
				'StorePath' => WYVERN_TOOLKIT_STORE_PATH
			),
			'Results' => $results,
		);

		return apply_filters( 'wyvern_toolkit_filter_data_manager_export', $configurations );

	}

	/**
	 * =========================
	 * Reset related methods.
	 * =========================
	 */

	public static function reset() {
		$table_column = self::get_table_column();

		$wpdb = self::get_wpdb();

		$prefix = $wpdb->prefix;

		if ( is_array( $table_column ) && ! empty( $table_column ) ) {
			foreach ( $table_column as $table => $column ) {
				$wpdb->query( "DELETE from {$prefix}{$table} WHERE (`{$column}` LIKE '%wyvern_toolkit%' );" );
			}
		}

		return array();
	}
}
