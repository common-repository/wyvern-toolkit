<?php
/**
 * Handle import-export crons.
 * 
 * @package WyvernToolkit
 */

namespace WyvernToolkit\Modules\ImportExport\Functions;

use WyvernToolkit\Modules\ImportExport\Abstract_Archiver;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cleanup_temp_folder() {

	if ( wp_doing_ajax() ) {
		return;
	}

	$archiver = new Abstract_Archiver();
	$archiver->cleanup();
}
add_action( 'wp_scheduled_delete', __NAMESPACE__ . '\cleanup_temp_folder' );