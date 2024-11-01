<?php
/**
 * Main export class for import export.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit\Modules\ImportExport;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Import extends Abstract_Archiver {

	public function __construct( $response ) {

		if ( is_array( $response ) && ! empty( $response ) ) {
			foreach ( $response as $property => $value ) {
				$this->$property = $value;
			}
		}

		parent::__construct();

		move_uploaded_file( $this->file['tmp_name'], $this->get_zippath() );

		$this->unzip();

		$this->set_files();

		$this->validate_config();

	}

	public function has_file( $file ) {
		return isset( $this->files[ $file ] ) ? $this->files[ $file ] : false;
	}

	public function set_zipname() {
		$this->zipname = $this->file['name'];
	}

	protected function set_files() {
		$files = list_files( $this->get_storage_path(), 1 );

		if ( is_array( $files ) && ! empty( $files ) ) {
			foreach ( $files as $file ) {
				$this->files[ pathinfo( $file, PATHINFO_FILENAME ) ] = $file;
			}
		}
	}

	protected function validate_config() {
		$configs = $this->read_config();

		/**
		 * Do not remove these variables. We are using these vars below as "$$variable".
		 */
		$stylesheet = get_stylesheet();
		$template   = get_template();

		if ( is_array( $configs ) && ! empty( $configs ) ) {
			foreach ( $configs as $variable => $config ) {

				if ( ! isset( $$variable ) ) {
					continue;
				}

				if ( $$variable !== $config ) {
					wp_send_json_error( "Config info {$variable} does not match with current website." );
				}
			}
		} else {
			wp_send_json_error( 'Unable to read config file.' );
		}

	}
}
