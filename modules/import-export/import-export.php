<?php
/**
 * Import Export
 *
 * @package WyvernToolkit\Modules
 * @subpackage import-export
 */

use WyvernToolkit\ModulesHelpers;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrap this module configs.
 */
require_once ModulesHelpers::get_module_dir_path( 'import-export' ) . 'inc/class-import-export.php';
