<?php
/**
 * Class for `wyvern-toolkit-store` folder and files.
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
 * Class for `wyvern-toolkit-store` folder and files.
 *
 * @since 1.0.0
 * @singleton
 */
class Store {

	use Singleton;

	const ROOT = WYVERN_TOOLKIT_STORE_PATH;

	/**
	 * Returns singleton traits instance for type definition
	 *
	 * @return self
	 */
	public static function init() {
		return self::instance();
	}

	public function __construct() {
		$this->create_root_folder();
		$this->create_default_files();
	}

	protected static function fs() {
		return Filesystem::init();
	}

	protected static function normalize_path( $path ) {

		$root = self::ROOT;
		$path = wp_normalize_path( $path );

		if ( ! pathinfo( $path, PATHINFO_EXTENSION )  ){
			// If we are here then it means $path is path of a folder.
			$path = trailingslashit( $path );
		}

		return wp_normalize_path( $root . str_replace( $root, '', $path ) );
	}

	public function get_path_to( $path ) {
		return self::normalize_path( $path );
	}

	/**
	 * Convert provided path related to Store.
	 *
	 * @param string $path
	 * @return string
	 * @since 1.0.4
	 */
	public function convert_path_to_relative( $path ) {
		$path = self::normalize_path( $path );
		return str_replace( self::ROOT, '', $path );
	}

	public function exists( $path = null ) {
		$path = self::normalize_path( $path );
		return file_exists( $path );
	}

	public function create_root_folder() {
		if ( $this->exists() ) {
			return self::ROOT;
		}

		if ( self::fs()->create_folder( self::ROOT ) ) {
			return self::ROOT;
		}
	}

	public function delete_root_folder() {
		if ( ! $this->exists() ) {
			return true;
		}

		return self::fs()->delete_folder( self::ROOT, true );
	}

	public function get_default_files() {
		return apply_filters(
			'wyvern_toolkit_filter_store_default_files',
			array(
				'index.php'  => array(
					'<?php',
					'// Silence is golden.',
				),
				'index.html' => '',
			)
		);
	}

	public function create_default_files( $folder = null ) {

		$default_files = $this->get_default_files();

		$this->create_folder( $folder );

		$htaccess_path = self::normalize_path( '.htaccess' );

		if ( $this->exists( $htaccess_path ) ) {

			/**
			 * Remove htaccess created by older version (Before v1.0.3).
			 */
			self::fs()->delete_file( $htaccess_path );
		}

		if ( is_array( $default_files ) && ! empty( $default_files ) ) {
			foreach ( $default_files as $file => $content ) {

				$_content = is_array( $content ) ? implode( PHP_EOL, $content ) : $content;

				$_file = $folder ? $folder . DIRECTORY_SEPARATOR . $file : $file;
				$_file = self::normalize_path( $_file );

				if ( $this->exists( $_file ) ) {
					continue;
				}

				self::fs()->writefile( $_file, $_content );
			}
		}
	}

	public function delete_cache( $folder ) {
		$path = self::normalize_path( $folder );
		delete_transient( 'dirsize_cache' );
		return Cache::delete( $path );
	}

	public function list_files( $folder, $cache_reset = false ) {
		$path = self::normalize_path( $folder );

		if ( $cache_reset ) {
			$this->delete_cache( $path );
		}

		$cache = Cache::get( $path );

		if ( $cache ) {
			return $cache;
		}

		$files = list_files( $path, 100, array_keys( $this->get_default_files() ) );
		Cache::set( $path, $files );

		return $files;
	}

	public function create_file( $filename, $content = '', $create_folder = true ) {
		if ( ! $this->exists() ) {
			return false;
		}

		$filename = self::normalize_path( $filename );

		if ( $this->exists( $filename ) ) {
			return true;
		}

		if ( $create_folder ) {
			$this->create_folder( $filename );
		}

		return self::fs()->writefile( $filename, $content );

	}

	public function delete_file( $filename ) {
		if ( ! $this->exists() ) {
			return false;
		}

		$filename = self::normalize_path( $filename );

		return self::fs()->delete_file( $filename );
	}

	public function create_folder( $path ) {

		$path = self::normalize_path( $path );

		if ( $this->exists( $path ) ) {
			return true;
		}

		$is_file = ! empty( pathinfo( $path, PATHINFO_EXTENSION ) );

		$path = $is_file ? dirname( $path ) : $path;

		if ( ! self::fs()->create_folder( $path ) ) {
			return false;
		}

		return true;
	}

	public function delete_folder( $path ) {

		if ( ! $this->exists( $path ) ) {
			return true;
		}

		$path = self::normalize_path( $path );
		return self::fs()->delete_folder( $path, true );
	}
}
