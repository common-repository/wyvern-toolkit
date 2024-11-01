<?php
/**
 * Class for plugin admin menu pages.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle admin menu for this plugin.
 *
 * @since 1.0.0
 */
class AdminMenu {

	/**
	 * Create and register admin menus.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function init() {
		$hook = is_multisite() ? 'network_admin_menu' : 'admin_menu';
		add_action( $hook, array( __CLASS__, 'register' ) );
	}

	/**
	 * Register admin menu and sub menus.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function register() {
		self::register_menus();
		self::register_submenus();
	}

	/**
	 * Register admin menus.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected static function register_menus() {
		$admin_menus = self::get_menus();

		if ( is_array( $admin_menus ) && ! empty( $admin_menus ) ) {
			foreach ( $admin_menus as $menu_slug => $admin_menu ) {

				add_menu_page(
					! empty( $admin_menu['page_title'] ) ? $admin_menu['page_title'] : '',
					! empty( $admin_menu['menu_title'] ) ? $admin_menu['menu_title'] : '',
					! empty( $admin_menu['capability'] ) ? $admin_menu['capability'] : '',
					$menu_slug,
					! empty( $admin_menu['function'] ) ? $admin_menu['function'] : function() {
						?>
						<div id="wyvern-toolkit"></div>
						<?php
					},
					! empty( $admin_menu['icon_url'] ) ? $admin_menu['icon_url'] : '',
					! empty( $admin_menu['position'] ) ? $admin_menu['position'] : null
				);

			}
		}
	}

	/**
	 * Register submenus.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected static function register_submenus() {
		$submenus = self::get_submenus();

		if ( is_array( $submenus ) && ! empty( $submenus ) ) {
			foreach ( $submenus as $slug => $submenu ) {
				$menu_slug = "wyvern-toolkit#/{$slug}";

				add_submenu_page(
					! empty( $submenu['parent_slug'] ) ? $submenu['parent_slug'] : 'wyvern-toolkit',
					! empty( $submenu['page_title'] ) ? $submenu['page_title'] : '',
					! empty( $submenu['menu_title'] ) ? $submenu['menu_title'] : '',
					! empty( $submenu['capability'] ) ? $submenu['capability'] : 'manage_options',
					$menu_slug,
					! empty( $submenu['function'] ) ? $submenu['function'] : function() {
						?>
						<div class="wrap" id="wyvern-toolkit"></div>
						<?php
					},
					! empty( $submenu['position'] ) ? $submenu['position'] : null
				);

			}
		}

		remove_submenu_page( 'wyvern-toolkit', 'wyvern-toolkit' );
	}

	/**
	 * Return an array of menus arguments.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected static function get_menus() {
		$menus = array(
			'wyvern-toolkit' => array(
				'page_title' => __( 'Wyvern Toolkit', 'wyvern-toolkit' ),
				'menu_title' => __( 'Wyvern Toolkit', 'wyvern-toolkit' ),
				'capability' => 'manage_options',
				'function'   => '',
				'icon_url'   => 'data:image/svg+xml;base64,' . base64_encode( @file_get_contents( WYVERN_TOOLKIT_URL . 'logo.svg' ) ), // @phpcs:ignore
				'position'   => null,
			),
		);

		return apply_filters( 'wyvern_toolkit_filter_admin_menus', $menus );
	}

	/**
	 * Returns an array of submenus arguments.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected static function get_submenus() {
		$submenus = array(
			'modules' => array(
				'parent_slug' => '',
				'page_title'  => __( 'Modules', 'wyvern-toolkit' ),
				'menu_title'  => __( 'Modules', 'wyvern-toolkit' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
			'settings' => array(
				'parent_slug' => '',
				'page_title'  => __( 'Settings', 'wyvern-toolkit' ),
				'menu_title'  => __( 'Settings', 'wyvern-toolkit' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
		);

		return apply_filters( 'wyvern_toolkit_filter_admin_submenus', $submenus );
	}

}
