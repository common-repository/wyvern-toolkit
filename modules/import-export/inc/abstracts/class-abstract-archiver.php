<?php
/**
 * Archiver abstract class.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit\Modules\ImportExport;

use WyvernToolkit\Filesystem;
use WyvernToolkit\Helpers;
use WyvernToolkit\ModulesHelpers;
use WyvernToolkit\Store;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Abstract_Archiver {

	/**
	 * Archiver object
	 *
	 * @var \ZipArchive|\PclZip
	 */
	private $archiver;

	protected $zipname;

	protected $zippath;

	protected $temp_root;

	protected $storage_path;

	protected $config_path;

	protected $files = array();

	protected $config = array();

	public function __construct() {
		$this->setup_storage();
		$this->set_config_path();
		$this->set_zipname();
		$this->set_zippath();
		$this->set_archiver();
	}

	private function set_archiver() {

		if ( class_exists( 'ZipArchive' ) ) {
			$this->archiver = new \ZipArchive();
		} else {
			$this->archiver = new \PclZip( $this->get_zippath() );
		}
	}

	protected function get_archiver() {
		return $this->archiver;
	}

	protected function set_zipname() {
		$theme         = get_stylesheet();
		$this->zipname = "{$theme}-" . date( 'd-m-Y-H-i-s' ) . '.zip';
	}

	public function get_zipname() {
		return $this->zipname;
	}

	protected function set_zippath() {
		$zipname = $this->get_zipname();

		if ( ! $zipname ) {
			return;
		}

		$storage_path  = $this->get_storage_path();
		$this->zippath = $storage_path . DIRECTORY_SEPARATOR . $zipname;
	}

	public function get_zippath() {
		return wp_normalize_path( $this->zippath );
	}

	protected function setup_storage() {
		$temp_root = Store::init()->get_path_to( 'import-export/' );

		if ( ! is_dir( $temp_root ) ) {
			// Create ".storage".
			@mkdir( $temp_root, 0777, true );
		}

		$this->temp_root = $temp_root;

		// Create "index.php" file.
		@file_put_contents( "{$temp_root}/index.php", '<?php' );

		$folder             = uniqid( "{$temp_root}/" );
		$this->storage_path = @mkdir( $folder, 0777, true ) ? $folder : null;
	}

	public function get_storage_path() {
		return $this->storage_path;
	}

	public function set_config_path() {
		$storage_path      = $this->get_storage_path();
		$this->config_path = "{$storage_path}/config.json";
	}

	public function get_config_path() {
		return $this->config_path;
	}

	public function write_config( $key, $data ) {
		$config = $this->read_config();

		$file = $this->get_config_path();

		$config[ $key ] = $data;

		@file_put_contents( $file, wp_json_encode( $config ) );

		$this->config = $config;

	}

	public function read_config() {

		if ( $this->config ) {
			return $this->config;
		}

		$file = $this->get_config_path();

		$json = @file_get_contents( $file );

		$this->config = json_decode( $json, true );

		return is_array( $this->config ) ? $this->config : array();
	}

	public function unzip() {
		$fs      = Filesystem::init();
		$zipfile = $this->get_zippath();

		if ( ! is_wp_error( unzip_file( $zipfile, $this->get_storage_path() ) ) ) {
			$fs->delete_file( $zipfile );
			return true;
		}
	}

	public function zip() {

		$files   = $this->files;
		$zippath = $this->get_zippath();

		$this->archiver->open( $zippath, $this->archiver::CREATE );

		if ( is_array( $files ) && ! empty( $files ) ) {
			foreach ( $files as $file ) {
				$this->archiver->addFile( $file, pathinfo( $file, PATHINFO_BASENAME ) );
			}
		}

		$this->archiver->close();

		if ( file_exists( $zippath ) ) {
			return $zippath;
		}

	}

	public function download_url() {

		$zippath = $this->get_zippath();

		if ( ! file_exists( $zippath ) ) {
			return;
		}

		$url = Helpers::convert_path_to_url( $zippath );

		return esc_url_raw( $url );

	}

	/**
	 * Recursively clean and delete temp folder.
	 *
	 * @return void
	 */
	public function cleanup() {
		$fs = Filesystem::init();
		$fs->delete_folder( $this->temp_root, true );
	}
}
