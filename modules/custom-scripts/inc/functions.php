<?php
/**
 * Custom scripts module functions and definitions.
 */

namespace WyvernToolkit\Modules\CustomScripts\Functions;

use WyvernToolkit\Filesystem;
use WyvernToolkit\Helpers;
use WyvernToolkit\Store;

function get_script_filepath( $type ) {

	static $refetch = 0;

	$files = Store::init()->list_files( 'custom-scripts', $refetch );

	/**
	 * If we have files.
	 */
	if ( is_array( $files ) && ! empty( $files ) ) {
		foreach ( $files as $file ) {

			$ext = pathinfo( $file, PATHINFO_EXTENSION );

			if ( $ext === $type ) {
				return $file;
			}
		}
	}

	/**
	 * We do not have files, create files and do single recursion.
	 */
	$module = 'custom-scripts';

	if ( Store::init()->create_folder( $module ) ) {
		Store::init()->create_file( "{$module}/custom.js", '', false );
		Store::init()->create_file( "{$module}/custom.css", '', false );
	}

	if ( ! $refetch ) {
		return get_script_filepath( $type );
	}

	$refetch++;
}

function get_script_url( $type ) {
	$path = get_script_filepath( $type );

	if ( $path && file_exists( $path ) ) {
		return Helpers::convert_path_to_url( $path );
	}
}

function save_to_file( $content, $type ) {
	$file = get_script_filepath( $type );

	if ( ! $file ) {
		return false;
	}

	/**
	 * We might need to allocate large memory for large content.
	 * But hopefully not this much.
	 */
	wp_raise_memory_limit( 'admin' );

	$fs = Filesystem::init();
	$fs->writefile( $file, $content );
}

function get_script_content( $type ) {
	$file = get_script_filepath( $type );

	if ( ! $file ) {
		return;
	}

	return Filesystem::init()->get_wp_fs()->get_contents( $file );
}

function save_scripts( $data, $params ) {

	if ( empty( $params['config'] ) ) {
		return $data;
	}

	$config = $params['config'];

	if ( isset( $config['customCSS'] ) ) {
		save_to_file( $config['customCSS'], 'css' );
	}

	if ( isset( $config['customJS'] ) ) {
		save_to_file( $config['customJS'], 'js' );
	}

	return $data;
}
add_filter( 'wyvern_toolkit_filter_custom-scripts_api_update', __NAMESPACE__ . '\save_scripts', 12, 2 );

function get_scripts() {
	$css = get_script_content( 'css' );
	$js  = get_script_content( 'js' );

	return array(
		'customCSS' => $css,
		'customJS'  => $js,
	);
}
add_filter( 'wyvern_toolkit_filter_custom-scripts_api_query', __NAMESPACE__ . '\get_scripts' );
