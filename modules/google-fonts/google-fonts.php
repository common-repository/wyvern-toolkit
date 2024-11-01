<?php
/**
 * Import Export
 *
 * @package WyvernToolkit\Modules
 * @subpackage google-fonts
 */

use WyvernToolkit\ModulesHelpers;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WYVERN_TOOLKIT_MODULES_GOOGLE_FONTS_PATH', ModulesHelpers::get_module_dir_path( 'google-fonts' ) );

/**
 * Bootstrap this module configs.
 */
require_once WYVERN_TOOLKIT_MODULES_GOOGLE_FONTS_PATH . 'inc/class-google-fonts.php';
