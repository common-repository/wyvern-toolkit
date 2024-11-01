<?php
/**
 * Custom Scripts module main class file.
 *
 * @package WyvernToolkit\Modules
 */

namespace WyvernToolkit\Modules;

use WyvernToolkit\Abstracts\ModuleConfigs;
use WyvernToolkit\Store;

use function WyvernToolkit\Modules\CustomScripts\Functions\get_script_filepath;
use function WyvernToolkit\Modules\CustomScripts\Functions\get_script_url;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for Custom Scripts module.
 *
 * @since 1.0.0
 */
class Custom_Scripts extends ModuleConfigs {

	/**
	 * @inheritDoc
	 */
	protected function set_info() {
		return array(
			'name'        => esc_html__( 'Custom Scripts', 'wyvern-toolkit' ),
			'version'     => '1.0.0',
			'module'      => 'custom-scripts',
			'description' => esc_html__( 'Custom Scripts module lets you to write custom CSS and JS for your site frontend.', 'wyvern-toolki' ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function on_activation() {
		if ( Store::init()->create_folder( $this->module ) ) {
			Store::init()->create_file( "{$this->module}/custom.js", '', false );
			Store::init()->create_file( "{$this->module}/custom.css", '', false );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function get_includes() {
		return array(
			'inc/functions.php'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function enqueue_scripts() {
		$css = get_script_url( 'css' );

		if ( $css ) {
			wp_enqueue_style( 'wyvern-toolkit-custom-scripts-css', $css, array(), filemtime( get_script_filepath( 'css' ) ) );
		}

		$js = get_script_url( 'js' );

		if ( $js ) {
			wp_enqueue_script( 'wyvern-toolkit-custom-scripts-js', $js, array(), filemtime( get_script_filepath( 'js' ) ), true );
		}
	}
}
