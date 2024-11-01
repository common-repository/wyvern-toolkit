<?php
/**
 * Abstract class for bootstrapping our module files.
 *
 * @package WyvernToolkit\Abstract
 */

namespace WyvernToolkit\Abstracts;

use WyvernToolkit\ModulesHelpers;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class for safe bootstrapping our modules informations and configuration.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class ModuleConfigs {

	/**
	 * Init class.
	 */
	final public function __construct() {

		$info = $this->set_info();

		if ( is_array( $info ) && ! empty( $info ) ) {
			foreach ( $info as $property => $value ) {
				$this->{$property} = $value;
			}
		}

		add_action( "wyvern_toolkit_on_{$this->module}_activation", array( $this, 'on_activation' ) );
		add_action( "wyvern_toolkit_on_{$this->module}_deactivation", array( $this, 'on_deactivation' ) );

	}

	/**
	 * Init module.
	 *
	 * @return void
	 */
	final public function init() {
		$this->includes();

		add_filter( 'wyvern_toolkit_filter_admin_localize', array( $this, 'admin_localized' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Include files set by `get_includes()` method.
	 *
	 * @return void
	 */
	final protected function includes() {

		$included = array_flip( get_included_files() );
		$path     = ModulesHelpers::get_module_dir_path( $this->module );
		$files    = $this->get_includes();

		if ( is_array( $files ) && ! empty( $files ) ) {
			foreach ( $files as $file ) {

				$filepath = $path . $file;

				if ( ! isset( $included[ $filepath ] ) ) {
					require_once $filepath;
				}
			}
		}
	}

	/**
	 * Set informations for current module.
	 *
	 * @return array
	 * @abstract
	 */
	protected function set_info() {
		_doing_it_wrong(
			__METHOD__,
			/* translators: %s is the method name. */
			sprintf( esc_html__( 'The %s is supposed to be override by child classes.', 'wyvern-toolkit' ), __METHOD__ ),
			'1.0.0'
		);

		return array();
	}

	/**
	 * Array of files to include of current module.
	 *
	 * @return void
	 * @abstract
	 */
	protected function get_includes() {}

	/**
	 * On current module activation.
	 *
	 * @return void
	 * @abstract
	 */
	public function on_activation() {}

	/**
	 * On current module deactivation.
	 *
	 * @return void
	 * @abstract
	 */
	public function on_deactivation() {}

	/**
	 * Styles and Scripts to load at frontend.
	 *
	 * @return void
	 * @abstract
	 */
	public function enqueue_scripts() {}

	/**
	 * Styles and Scripts to load at backend.
	 *
	 * @return void
	 * @abstract
	 */
	public function enqueue_admin_scripts() {}

	/**
	 * Data to localize in admin side.
	 *
	 * @param array
	 * @return array
	 */
	final public function admin_localized( $localized ) {

		$data = apply_filters(
			"wyvern_toolkit_filter_{$this->module}_admin_localize",
			$this->set_admin_localized( $localized )
		);

		if ( $data ) {
			$localized[ $this->module ] = $data;
		}

		return $localized;
	}

	/**
	 * Set data to localize in admin side.
	 *
	 * @param array
	 * @return array
	 */
	protected function set_admin_localized( $localized ) {
		return array();
	}


}
