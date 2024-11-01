<?php
/**
 * Settings controller REST API class for settings page.
 *
 * @package WyvernToolkit\API\Controllers\V1
 */

namespace WyvernToolkit\API\Controllers\V1;

use WyvernToolkit\StoreManager;
use WyvernToolkit\Abstracts\REST_Controller;
use WyvernToolkit\DataManager;
use WyvernToolkit\SettingsManager;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings controller REST API class for settings page.
 *
 * @since 1.0.4
 */
class AdminController extends REST_Controller {

    /**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wyvern-toolkit-admin/docs';

	/**
	 * Store manager class instance.
	 *
	 * @var \WyvernToolkit\StoreManager
	 */
	protected $store_manager;

	/**
	 * Rest base route.
	 *
	 * @var string
	 */
	protected $route = "settings";

	/**
	 * Init controller.
	 *
	 * @param string $namespace Rest API Namespace.
	 * @param string $route Rest Base Route.
	 */
	public function __construct() {
		parent::__construct();
		$this->store_manager = new StoreManager();
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		$this->register_rest_route( array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
		) );

		$this->register_rest_route( array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the settings tab.' ),
					'type'        => 'string',
				),
			),
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
			),
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
			),
		), '(?P<id>[\w-]+)' );

	}

	/**
	 * Get data according to request.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function get_item( $request ) {
		$id     = $request->get_param( 'id' );
		$params = $request->get_json_params();

		switch ( $id ) {
			case 'store-manager':
				$path = ! empty( $params['path'] ) ? $params['path'] : '';
				return $this->store_manager->list( $path );

			default:
				return SettingsManager::get();
		}

	}

	/**
	 * Updated requested data.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function update_item( $request ) {
		$id     = $request->get_param( 'id' );
		$params = $request->get_json_params();

		switch ( $id ) {
			case 'store-manager':
				$path = ! empty( $params['path'] ) ? $params['path'] : '';
				return $this->store_manager->list( $path );

			case 'wyvern-toolkit-import':
				$files = $request->get_file_params();

				if ( ! empty( $files['file'] ) ) {
					DataManager::import( $files['file'] );
				}

				return SettingsManager::get();

			default:
				if ( ! empty( $params['key'] ) ) {
					SettingsManager::save( $params['key'], $params['data'] );
				}

				return SettingsManager::get();
		}

	}

	/**
	 * Delete requested data.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function delete_item( $request ) {
		$id     = $request->get_param( 'id' );
		$params = $request->get_json_params();

		switch ( $id ) {
			case 'store-manager':
				$path = ! empty( $params['path'] ) ? $params['path'] : '';
				return $this->store_manager->delete( $path );

			case 'wyvern-toolkit-reset':
			return DataManager::reset();

			default:
				return array();
		}

	}

}
