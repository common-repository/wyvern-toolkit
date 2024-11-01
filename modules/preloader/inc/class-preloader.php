<?php
/**
 * Preloader module main class file.
 *
 * @package WyvernToolkit\Modules
 */

namespace WyvernToolkit\Modules;

use WyvernToolkit\Abstracts\ModuleConfigs;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for Preloader module.
 *
 * @since 1.0.0
 */
class Preloader extends ModuleConfigs {

	/**
	 * Set configs for current module.
	 *
	 * @return array
	 */
	protected function set_info() {
		return array(
			'name'        => esc_html__( 'Preloader', 'wyvern-toolkit' ),
			'version'     => '1.0.0',
			'module'      => 'preloader',
			'description' => esc_html__( 'Animated preloader during page change.', 'wyvern-toolkit' ),
		);
	}

	/**
	 * Gets files to be included.
	 */
	protected function get_includes() {
		return [
			'inc/functions.php'
		];
	}

}
