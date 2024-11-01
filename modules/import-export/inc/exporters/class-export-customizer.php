<?php
/**
 * Import/Export customizer exporter.
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

class Export_Customizer {

	public function __construct( Export $export ) {

		if ( ! isset( $export->exportCustomizer ) ) {
			return;
		}

		$this->export = $export;

		$filepath = $this->export_customizer();

		$export->set_filepath( $filepath );
	}

	protected function export_customizer() {

		$mods = get_theme_mods();

		$storage         = $this->export->get_storage_path();
		$customizer_path = "{$storage}/customizer.json";

		$exported = @file_put_contents( $customizer_path, wp_json_encode( $mods ) );

		return is_int( $exported ) && file_exists( $customizer_path ) ? $customizer_path : null;
	}

}
