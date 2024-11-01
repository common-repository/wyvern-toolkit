<?php
/**
 * Include the required files for Wyvern Toolkit.
 * 
 * @package WyvernToolkit
 */

function wyvern_tookit_require_files() {
	$files = array(
		'includes/Traits/Singleton.php',

		'includes/Abstracts/ModuleConfigs.php',
		'includes/Abstracts/RestController.php',

		'includes/Cache.php',
		'includes/Filesystem.php',
		'includes/Helpers.php',
		'includes/AdminMenu.php',
		'includes/ActionLinks.php',
		'includes/Store.php',
		'includes/ModulesHelpers.php',
		'includes/ModulesManager.php',
		'includes/DataManager.php',
		'includes/SettingsManager.php',
		'includes/StoreManager.php',
		'includes/DownloadManager.php',
		'includes/Assets.php',
		'includes/API/API.php',
		'includes/Stats.php',
		'includes/WyvernToolkit.php',
	);

	if ( is_array( $files ) && ! empty( $files ) ) {
		foreach ( $files as $file ) {
			require_once WYVERN_TOOLKIT_PATH . $file;
		}
	}
}
wyvern_tookit_require_files();
