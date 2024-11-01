<?php
/**
 * Class for handling modules.
 *
 * @since 1.0.0
 * @package WyvernToolkit
 */

namespace WyvernToolkit;

use WyvernToolkit\Traits\Singleton;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling modules.
 * It merges and caches ModulesData and ModuleConfigs.
 *
 * @since 1.0.0
 * @singleton
 */
class ModulesManager {

	use Singleton;

	/**
	 * Modules helpers class object.
	 *
	 * @var \WyvernToolkit\ModulesHelpers
	 */
	protected $helpers;

	/**
	 * Modules configs array with key.
	 *
	 * @var \WyvernToolkit\Abstracts\ModuleConfigs[]
	 */
	protected $module_configs = array();

	/**
	 * Modules information.
	 *
	 * @var array
	 */
	protected $modules_data = array();

	/**
	 * Init class.
	 */
	private function __construct() {
		$this->helpers = new ModulesHelpers();
		$this->load_modules();
		$this->init_modules();
	}

	/**
	 * Modules helpers class object.
	 *
	 * @return \WyvernToolkit\ModulesHelpers
	 */
	public function get_helpers() {
		return $this->helpers;
	}

	/**
	 * Resets modules cache and regenerates data.
	 *
	 * @return void
	 */
	public function reset_modules() {
		Cache::delete( 'modules' );
		self::__construct();
	}

	/**
	 * Returns modules data with config informations.
	 *
	 * @return array
	 */
	public function get_modules_data() {
		return apply_filters( 'wyvern_toolkit_filter_modules_data', $this->modules_data );
	}

	/**
	 * Load module files and module class for configs.
	 *
	 * @return void
	 */
	protected function load_modules() {
		$modules = $this->helpers::get_modules();

		ksort( $modules ); // Sort modules by key

		if ( is_array( $modules ) && ! empty( $modules ) ) {
			foreach ( $modules as $module => $module_data ) {

				require_once $module_data['file'];

				/**
				 * Init modules main class.
				 *
				 * @var \WyvernToolkit\Abstracts\ModuleConfigs
				 */
				$classname = $module_data['classname'];

				$module_config = new $classname();

				$this->modules_data[ $module ]   = array_merge( (array) $module_config, $module_data );
				$this->module_configs[ $module ] = $module_config;
			}
		}
	}

	/**
	 * Init modules conditionally.
	 *
	 * @return void
	 */
	protected function init_modules() {
		if ( is_array( $this->module_configs ) && ! empty( $this->module_configs ) ) {
			foreach ( $this->module_configs as $module => $module_config ) {

				if ( ! $this->helpers::is_module_active( $module ) ) {
					continue;
				}

				$module_config->init();
			}
		}
	}

}
