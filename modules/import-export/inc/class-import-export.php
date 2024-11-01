<?php
/**
 * Main class for Import/Export module.
 *
 * @package WyvernToolkit\Modules
 */

namespace WyvernToolkit\Modules;

use WyvernToolkit\Abstracts\ModuleConfigs;
use WyvernToolkit\Store;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for Import/Export module.
 *
 * @since 1.0.0
 */
class Import_Export extends ModuleConfigs {

	/**
	 * @inheritDoc
	 */
	protected function set_info() {
		return array(
			'name'        => esc_html__( 'Import Export', 'wyvern-toolkit' ),
			'version'     => '1.0.0',
			'module'      => 'import-export',
			'description' => esc_html__( 'Import export demo and contents.', 'wyvern-toolkit' ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function on_activation() {
		Store::init()->create_folder( $this->module );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_includes() {
		return array(
			'inc/abstracts/class-abstract-archiver.php',

			'inc/exporters/class-export-customizer.php',
			'inc/exporters/class-export-widgets.php',
			'inc/exporters/class-export-contents.php',
			'inc/exporters/class-export.php',

			'inc/importers/class-import-customizer.php',
			'inc/importers/class-import-widgets.php',
			'inc/importers/class-import-contents.php',
			'inc/importers/class-import.php',

			'inc/class-ajax.php',

			'inc/functions.php',
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function set_admin_localized( $localized ) {

		/**
		 * Filter for listing theme demos.
		 *
		 * Structure:
		 * =====================================================================
		 *
		 * array(
		 *	array(
		 *		'label'     => 'Demo One',
		 *		'thumbnail' => 'https://via.placeholder.com/600/92c952',
		 *		'demo_url'  => 'http://url-to-demo-page.com', // This can be link to buy now page for premium packages.
		 *		'package'   => '', // Do not provide package url for premium demos.
		 *	),
		 *	array(
		 *		'label'     => 'Demo Two',
		 *		'thumbnail' => 'https://via.placeholder.com/600/92c952',
		 *		'demo_url'  => 'http://url-to-demo-page.com',
		 *		'package'   => 'http://direct-url-to-package.com/package.zip',
		 *	 ),
		 * );
		 *
		 * =====================================================================
		 *
		 * @since 1.0.5
		 */
		$demos = apply_filters( 'wyvern_toolkit_filter_import_export_demos', array() );

		return array(
			'demos' => $demos,
		);
	}

}
