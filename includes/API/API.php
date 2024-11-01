<?php
/**
 * Main class for initializing our API.
 *
 * @package WyvernToolkit\API
 */

namespace WyvernToolkit\API;

use WyvernToolkit\Filesystem;
use WyvernToolkit\Traits\Singleton;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for initializing our API.
 *
 * @since 1.0.0
 */
class API {

	use Singleton;

	/**
	 * Full path to API directory.
	 */
	protected $api_dir;

	/**
	 * Controllers information from controllers directory.
	 *
	 * @var array
	 * @since 1.0.4
	 */
	protected $controllers_info = array();

	/**
	 * Array of registered REST API Controllers.
	 *
	 * @var array
	 */
	protected $controllers = array();

	/**
	 * Init class.
	 */
	public function __construct() {

		$this->api_dir = trailingslashit( dirname( __FILE__ ) );

		$this->set_controllers_info();

		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	/**
	 * Scan controllers directory and generate controllers related info accordingly.
	 *
	 * @return void
	 * @since 1.0.4
	 */
	protected function set_controllers_info() {

		$fs = Filesystem::init();

		$root = $this->api_dir . 'Controllers';
		$dirs = @scandir( $root ); // @phpcs:ignore

		if ( is_array( $dirs ) && ! empty( $dirs ) ) {
			foreach ( $dirs as $dir ) {
				if ( '.' === $dir || '..' === $dir ) {
					continue;
				}

				$namespace = __NAMESPACE__ . "\\Controllers\\{$dir}";

				$controller_path = $root . DIRECTORY_SEPARATOR . $dir;

				$files = $fs->list_files( $controller_path );

				$controllers = array();

				if ( is_array( $files ) && ! empty( $files ) ) {
					foreach ( $files as $index => $file ) {

						require_once $file;

						$pathinfo  = pathinfo( $file );
						$classname = "$namespace\\{$pathinfo['filename']}";

						if ( ! class_exists( $classname ) ) {
							continue;
						}

						$controllers[ $index ]['route']     = strtolower( str_replace( 'Controller', '', $pathinfo['filename'] ) );
						$controllers[ $index ]['classname'] = $classname;
						$controllers[ $index ]['file']      = $file;
					}
				}

				if ( $controllers ) {
					$this->controllers_info[ $dir ] = array(
						'version'     => strtolower( $dir ),
						'namespace'   => $namespace,
						'path'        => $controller_path,
						'controllers' => $controllers,
					);
				}

				$controllers = array();

			}
		}

	}

	/**
	 * Returns array of namespaces.
	 *
	 * @return array
	 * @since 1.0.4 Namespaces are generated automatically.
	 */
	protected function get_namespaces() {

		$namespaces = array();

		if ( is_array( $this->controllers_info ) && ! empty( $this->controllers_info ) ) {
			foreach ( $this->controllers_info as $controller_info ) {
				$version     = $controller_info['version'];
				$controllers = $controller_info['controllers'];

				if ( is_array( $controllers ) && ! empty( $controllers ) ) {
					foreach ( $controllers as $controller ) {
						$namespaces["wyvern-toolkit/{$version}"][ $controller['route'] ] = $controller['classname'];
					}
				}
			}
		}

		return apply_filters( 'wyvern_toolkit_filter_rest_api_namespaces', $namespaces );
	}

	/**
	 * Register REST API Routes.
	 *
	 * @return void
	 */
	public function register() {
		$namespaces = $this->get_namespaces();

		if ( is_array( $namespaces ) && ! empty( $namespaces ) ) {
			foreach ( $namespaces as $namespace => $controllers ) {
				if ( is_array( $controllers ) && ! empty( $controllers ) ) {
					foreach ( $controllers as $route => $controller_class ) {
						$this->controllers[ $namespace ][ $route ] = new $controller_class( $namespace, $route );
					}
				}
			}
		}
	}

}
