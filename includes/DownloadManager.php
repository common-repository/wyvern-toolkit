<?php
/**
 * Class for handling file downloads.
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

class DownloadManager {

	protected $request;

	public function __construct() {

		$this->request = Helpers::get_submitted_data();

		add_action( 'init', array( $this, 'init' ) );
	}

	protected function verified() {

		if ( empty( $this->request['page'] ) ) {
			return false;
		}

		if ( 'wyvern-toolkit' !== $this->request['page'] ) {
			return false;
		}

		if ( empty( $this->request['key'] ) ) {
			return false;
		}

		if ( empty( $this->request['_nonce'] ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $this->request['_nonce'], 'wp_rest' ) ) {
			return false;
		}

		return true;
	}

	public function init() {

		if ( ! $this->verified() ) {
			return;
		}

		$key      = $this->request['key'];
		$filepath = ! empty( $this->request['path'] ) ? Store::init()->get_path_to( $this->request['path'] ) : '';
		$filename = ! empty( $this->request['filename'] ) ? $this->request['filename'] : wp_basename( $filepath );

		set_time_limit( 0 );
		ini_set( 'memory_limit', '-1' );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Pragma: public' );
		ob_clean();
		ob_end_flush();

		/**
		 * Action hook to print out content for file that is being downloaded.
		 * Modules can use this hook to echo the content to downloading files.
		 * Make sure you exit the code inside hook if your using predefined key.
		 *
		 * @since 1.0.4
		 */
		do_action( "wyvern_toolkit_download_manager_{$key}_content", $this->request );

		switch ( $key ) {
			case 'wyvern-toolkit-configuration':
				echo wp_json_encode( DataManager::export() ); // @phpcs:ignore
				break;

			default:
				readfile( $filepath );
				break;
		}

		exit;
	}

}
