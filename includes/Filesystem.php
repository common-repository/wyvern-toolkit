<?php
/**
 * Wyvern Toolkit Filesystem class.
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
 * Wyvern Toolkit Filesystem class.
 *
 * @since 1.0.0
 * @singleton
 */
class Filesystem {

	use Singleton;

	/**
	 * WordPress filesystem instance.
	 *
	 * @var \WP_Filesystem_Base|\WP_Filesystem_Direct
	 */
	protected $wp_filesystem;

	/**
	 * Returns singleton traits instance for type definition
	 *
	 * @return self
	 */
	public static function init() {
		return self::instance();
	}

	/**
	 * Init class.
	 */
	public function __construct() {
		global $wp_filesystem;

		if ( is_null( $wp_filesystem ) ) {
			require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		/**
		 * WordPress filesystem instance.
		 *
		 * @var \WP_Filesystem_Base|\WP_Filesystem_Direct
		 */
		$this->wp_filesystem = $wp_filesystem;

	}

	/**
	 * Returns WordPress filesystem instance.
	 *
	 * @return \WP_Filesystem_Base|\WP_Filesystem_Direct
	 */
	public function get_wp_fs() {
		return $this->wp_filesystem;
	}

	/**
	 * Trims off the path before /wp-content from provided path.
	 *
	 * @param string $fullpath
	 * @return string
	 * @since 1.0.4
	 */
	public function homepath_trimmer( $fullpath ) {
		return str_replace( ABSPATH, '', $fullpath );
	}

	/**
	 * Does the opposite of `WyvernToolkit\Filesystem::homepath_trimmer`.
	 *
	 * @param string $path
	 * @return string
	 * @since 1.0.4
	 */
	public function homepath_join( $path ) {
		return wp_normalize_path( ABSPATH . $path );
	}

	/**
	 * Recursive directory creation based on full path.
	 * Wrapper for `wp_mkdir_p`.
	 *
	 * Will attempt to set permissions on folders.
	 *
	 * @param string $target Full path to attempt to create.
	 * @return bool Whether the path was created. True if path already exists.
	 */
	public function create_folder( $path ) {
		return wp_mkdir_p( $path );
	}

	/**
	 * Deletes a directory.
	 * Wrapper for `WP_Filesystem_Direct::rmdir`.
	 *
	 * @param string $path      Path to directory.
	 * @param bool   $recursive Optional. Whether to recursively remove files/directories.
	 *                          Default false.
	 * @return bool True on success, false on failure.
	 */
	public function delete_folder( $path, $recursive = false ) {
		return $this->get_wp_fs()->rmdir( $path, $recursive );
	}

	/**
	 * Writes content to the file. If file doesn't exits, it creates file then writes the content to it.
	 *
	 * @param string $file    Remote path to the file where to write the data.
	 * @param string $content The data to write.
	 * @param string $append  If set to true, the content will be appended to the end of the file else whole content will be replaced.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function writefile( $file, $content, $append = false ) {
		// @phpcs:disable
		if ( $append ) {
			$resource = @fopen( $file, 'a' );
			$write    = @fwrite( $resource, $content );
			@fclose( $resource );
			$resource = false;
			return is_int( $write );
		}
		// @phpcs:enable

		return $this->get_wp_fs()->put_contents( $file, $content );
	}

	/**
	 * Deletes a file.
	 *
	 * @param string $file Full path to file.
	 * @return bool
	 */
	public function delete_file( $file ) {
		return $this->get_wp_fs()->delete( $file, false, 'f' );
	}

	/**
	 * Wrapper method for `list_files`.
	 *
	 * @param string   $folder     Optional. Full path to folder. Default empty.
	 * @param int      $levels     Optional. Levels of folders to follow, Default 100 (PHP Loop limit).
	 * @param string[] $exclusions Optional. List of folders and files to skip.
	 * @return string[]|false Array of files on success, false on failure.
	 *
	 * @since 1.0.5
	 */
	public function list_files( $folder = '', $levels = 100, $exclusions = array() ) {
		return list_files( $folder, $levels, $exclusions );
	}

}
