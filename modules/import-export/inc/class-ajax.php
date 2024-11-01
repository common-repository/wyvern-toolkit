<?php
/**
 * Import/Export ajax handler.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit\Modules\ImportExport;

use WyvernToolkit\Helpers;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {

	public function __construct() {
		add_action( 'wp_ajax_wyvern_toolkit_import_export_importer', array( $this, 'import_ajax_handler' ) );
		add_action( 'wp_ajax_wyvern_toolkit_import_export_exporter', array( $this, 'export_ajax_handler' ) );
	}

	public function import_ajax_handler() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		check_ajax_referer( 'wyvernToolkit-ajax', '_nonce' );

		$import = new Import( $_FILES );

		new Import_Customizer( $import );
		new Import_Widgets( $import );
		new Import_Contents( $import );

		wp_send_json_success();
	}

	public function export_ajax_handler() {

		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}

		check_ajax_referer( 'wyvernToolkit-ajax', '_nonce' );

		$response = Helpers::get_ajax_response();

		$export = new Export( $response );

		new Export_Customizer( $export );
		new Export_Widgets( $export );
		new Export_Contents( $export );

		$export->zip();

		$download_url = $export->download_url();

		if ( $download_url ) {
			wp_send_json_success( $download_url );
		}

		wp_send_json_error();

	}
}

new Ajax();
