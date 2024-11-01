<?php
/**
 * Import/Export content exporter.
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

class Export_Contents {

	public function __construct( Export $export ) {

		if ( ! isset( $export->exportContents ) ) {
			return;
		}

		$this->export = $export;

		/** Load WordPress export API */
		require_once ABSPATH . 'wp-admin/includes/export.php';

		$export->set_filepath( $this->export_contents() );

	}

	protected function export_contents() {

		ob_start();

		export_wp(
			array(
				'content' => 'all',
			)
		);

		$contents = ob_get_contents();
		ob_end_clean();

		if ( ! $contents ) {
			return;
		}

		$storage       = $this->export->get_storage_path();
		$contents_path = "{$storage}/contents.xml";

		$exported = @file_put_contents( $contents_path, $contents );

		return is_int( $exported ) && file_exists( $contents_path ) ? $contents_path : null;

	}
}
