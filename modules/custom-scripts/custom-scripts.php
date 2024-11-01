<?php
/**
 * Custom Scripts
 *
 * @package WyvernToolkit\Modules
 * @subpackage custom-scripts
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
require_once ModulesHelpers::get_module_dir_path( 'custom-scripts' ) . 'inc/class-custom-scripts.php';
