<?php
/**
 * Import/Export customizer importer.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit\Modules\ImportExport;

use WyvernToolkit\Filesystem;

class Import_Customizer {

	public function __construct( Import $import ) {

		if ( ! $import->has_file( 'customizer' ) ) {
			return;
		}

		$this->import = $import;

		$this->import_customizer();
	}

	protected function import_customizer() {
		$customizer_file = $this->import->has_file( 'customizer' );

		$json  = Filesystem::init()->get_wp_fs()->get_contents( $customizer_file );
		$mods  = json_decode( $json, true );
		$theme = get_option( 'stylesheet' );

		return update_option( "theme_mods_$theme", $mods );
	}
}
