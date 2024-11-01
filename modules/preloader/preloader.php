<?php
/**
 * Backend Finder
 *
 * @package WyvernToolkit\Modules
 * @subpackage preloader
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
require_once ModulesHelpers::get_module_dir_path( 'preloader' ) . 'inc/class-preloader.php';
