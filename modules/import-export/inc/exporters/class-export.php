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

class Export extends Abstract_Archiver {

	public function __construct( $response ) {

		if ( is_array( $response ) && ! empty( $response ) ) {
			foreach ( $response as $property => $value ) {
				$this->$property = $value;
			}
		}

		parent::__construct();

		$this->write_config( 'stylesheet', get_stylesheet() );
		$this->write_config( 'template', get_template() );
		$this->write_config( 'charset', get_option( 'blog_charset' ) );

		$this->set_filepath( $this->get_config_path() );
	}

	public function set_filepath( $filepath ) {

		if ( ! $filepath ) {
			return;
		}

		if ( file_exists( $filepath ) && is_readable( $filepath ) ) {
			$this->files[] = $filepath;
		}
	}

}
