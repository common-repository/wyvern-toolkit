<?php
/**
 * Import/Export contents importer.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit\Modules\ImportExport;

class Import_Contents {

	public function __construct( Import $import ) {
		if ( ! $import->has_file( 'contents' ) ) {
			return;
		}

		$this->import = $import;

		$this->import_contents();

	}

	protected function import_contents() {
		$file = $this->import->has_file( 'contents' );

		if ( ! class_exists( 'WP_Import' ) ) {
			require_once dirname( __FILE__ ) . '/content-importer/init.php';
		}

		/**
		 * Bufferring because WP_Import echos and dies progress during import which causes JSON error at client side.
		 */
		ob_start();
		$wp_import                    = new \WP_Import();
		$wp_import->fetch_attachments = true;
		$wp_import->import( $file );
		ob_end_clean();

	}
}
