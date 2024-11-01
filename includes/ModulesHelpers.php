<?php
/**
 * Class for handling modules.
 *
 * @since 1.0.0
 * @package WyvernToolkit
 */

namespace WyvernToolkit;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling modules.
 *
 * @since 1.0.0
 * @static
 */
class ModulesHelpers {

	/**
	 * Returns an array of all available modules from modules directory.
	 *
	 * @return array
	 */
	public static function get_modules() {

		$modules = Cache::get( 'modules' );

		if ( $modules ) {
			return $modules;
		}

		$modules = array();

		if ( ! function_exists( 'list_files' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}

		$files = list_files( WYVERN_TOOLKIT_MODULES_PATH, 1 );

		if ( ! $files ) {
			return $modules;
		}

		if ( is_array( $files ) && ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( ! is_dir( $file ) ) {
					continue;
				}

				$module = pathinfo( $file, PATHINFO_BASENAME );

				if ( ! $module ) {
					continue;
				}

				$module_slug = self::get_module_slug( $module );
				$module_file = self::get_module_file( $module );

				$modules[ $module ]['slug']      = $module_slug;
				$modules[ $module ]['dir']       = plugin_dir_path( $module_file );
				$modules[ $module ]['url']       = plugin_dir_url( $module_file );
				$modules[ $module ]['file']      = $module_file;
				$modules[ $module ]['module']    = $module;
				$modules[ $module ]['active']    = self::is_module_active( $module );
				$modules[ $module ]['config']    = self::get_module_config( $module );
				$modules[ $module ]['classname'] = self::get_module_classname( $module );

			}
		}

		$modules = apply_filters( 'wyvern_toolkit_filter_modules', $modules );

		Cache::set( 'modules', $modules );

		return $modules;
	}

	/**
	 * Returns Module Config.
	 *
	 * @param string $module Module key or basename.
	 */
	public static function get_module_config( $module ) {
		return Helpers::get_decoded_option( "{$module}_config" );
	}

	/**
	 * Returns module slug.
	 *
	 * @param string $module Module key or basename.
	 * @return string
	 */
	public static function get_module_slug( $module ) {
		return "{$module}/{$module}.php";
	}

	/**
	 * Returns full path to module main file.
	 *
	 * @param string $module Module key or basename.
	 * @return string
	 */
	public static function get_module_file( $module ) {
		return WYVERN_TOOLKIT_MODULES_PATH . self::get_module_slug( $module );
	}

	/**
	 * Returns module main classname.
	 *
	 * @param string $module Module key or basename.
	 * @return string
	 */
	public static function get_module_classname( $module ) {

		$module_file = self::get_module_file( $module );

		/**
		 * Lets parse classname from filename.
		 */
		$classname = str_replace( '-', '_', pathinfo( $module_file, PATHINFO_FILENAME ) );
		$classname = ucwords( $classname, '_' );

		return WYVERN_TOOLKIT_MODULES_NAMESPACE . "\\{$classname}";

	}

	/**
	 * Check if module exists.
	 *
	 * @param string $module Module key or basename.
	 * @return boolean
	 */
	public static function is_module_exists( $module ) {
		return file_exists( self::get_module_file( $module ) );
	}

	/**
	 * Checks if module is loaded or not.
	 *
	 * @param string $module $module Module key or basename.
	 * @return boolean
	 */
	public static function is_module_loaded( $module ) {
		return class_exists( self::get_module_classname( $module ) );
	}

	/**
	 * Check if module is active.
	 *
	 * @param string $module Module key or basename.
	 * @return boolean
	 */
	public static function is_module_active( $module ) {
		$active_modules = Helpers::get_decoded_option( 'active_modules', '{}' );
		return isset( $active_modules[ $module ] ) && ! ! $active_modules[ $module ];
	}

	/**
	 * Updates Module.
	 *
	 * @param string $module Module key or basename.
	 * @param mixed  $data Data to be updated.
	 */
	public static function update_module( $module, $data = null ) {

		$module = sanitize_text_field( wp_unslash( $module ) );

		if ( ! $module ) {
			return;
		}

		$schema = self::get_module_schema( $module );

		$active_modules = Helpers::get_decoded_option( 'active_modules' );

		if ( isset( $data['active'] ) && isset( $schema['properties']['active'] ) ) {

			$is_active = (bool) $data['active'];

			$active_modules[ $module ] = $is_active;

			Helpers::update_option( 'active_modules', $active_modules, true );

			if ( $is_active ) {
				do_action( "wyvern_toolkit_on_{$module}_activation" );
			} else {
				do_action( "wyvern_toolkit_on_{$module}_deactivation" );
			}

			/**
			 * Action hook for general purpose use, unlike activation and deactivation module specific hook.
			 */
			do_action( 'wyvern_toolkit_on_active_status_change', compact( 'is_active', 'module' ) );
		}

		if ( isset( $data['config'] ) && isset( $schema['properties']['config'] ) ) {
			$config_schema = $schema['properties']['config'];
			$config        = Helpers::get_decoded_option( $module . '_config', '{}' );
			if ( is_array( $config_schema ) && isset( $config_schema['properties'] ) ) {
				foreach ( $config_schema['properties'] as $key => $args ) {
					if ( isset( $data['config'][ $key ] ) ) {
						$value = $data['config'][ $key ];
						if ( isset( $args['sanitize_callback'] ) && \is_callable( $args['sanitize_callback'] ) ) {
							$value = call_user_func( $args['sanitize_callback'], $value );
						} else {
							$value = Helpers::sanitize_by_type( $value, isset( $args['type'] ) ? $args['type'] : 'string' );
						}
						$config[ $key ] = $value;
					}
				}
			}
			Helpers::update_option( $module . '_config', $config, true );
		}

	}

	/**
	 * Returns module config schema.
	 *
	 * @param string $module Module key or basename.
	 * @return array
	 */
	public static function get_module_schema( $module ) {
		$module_path = self::get_module_dir_path( $module );

		$schema_filepath = $module_path . 'schema.json';

		if ( ! file_exists( $schema_filepath ) ) {
			return array();
		}

		$json = file_get_contents( $schema_filepath );

		if ( ! $json ) {
			return array();
		}

		return json_decode( $json, true );
	}

	/**
	 * Returns path to module directory.
	 *
	 * @param string $module Module key or basename.
	 * @return string
	 */
	public static function get_module_dir_path( $module ) {
		$modules = self::get_modules();

		if ( empty( $modules[ $module ] ) ) {
			return;
		}

		return $modules[ $module ]['dir'];
	}

	/**
	 * Returns url to module directory.
	 *
	 * @param string $module Module key or basename.
	 * @return string
	 */
	public static function get_module_dir_url( $module ) {
		$modules = self::get_modules();

		if ( empty( $modules[ $module ] ) ) {
			return;
		}

		return $modules[ $module ]['url'];
	}

}
