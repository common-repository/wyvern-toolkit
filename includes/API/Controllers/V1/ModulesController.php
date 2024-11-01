<?php
/**
 * Modules controller REST API class for listing modules.
 *
 * @package WyvernToolkit\API\Controllers\V1
 */

namespace WyvernToolkit\API\Controllers\V1;

use WyvernToolkit\ModulesManager;
use WyvernToolkit\Abstracts\REST_Controller;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modules controller REST API class for listing modules.
 *
 * @since 1.0.0
 */
class ModulesController extends REST_Controller {

	/**
	 * Modules manager class object.
	 *
	 * @var ModulesManager
	 */
	protected $modules_manager;

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	// protected $namespace;

	/**
	 * Rest base route.
	 *
	 * @var string
	 */
	protected $route = 'modules';

	/**
	 * Init controller.
	 *
	 * @param string $namespace Rest API Namespace.
	 * @param string $route Rest Base Route.
	 */
	public function __construct( $namespace, $route ) {
		parent::__construct();
		$this->modules_manager = ModulesManager::instance();
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		$this->register_rest_route(
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_modules' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			) 
		);

		$this->register_rest_route(
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Unique identifier for the module.' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					// 'args'                => $get_item_args,
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					// 'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
			),
			'(?P<id>[\w-]+)'
		);

		$this->register_rest_route(
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Unique identifier for the module.' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item_data' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					// 'args'                => $get_item_args,
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item_data' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					// 'args'                => $get_item_args,
				),
			),
			'(?P<id>[\w-]+)/data'
		);

	}

	/**
	 * List modules according to request.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function list_modules( $request ) {
		return $this->modules_manager->get_modules_data();
	}

	/**
	 * Get module according to request.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function get_item( $request ) {
		$id    = $request->get_param( 'id' );
		$items = $this->modules_manager->get_modules_data();

		if ( ! isset( $items[ $id ] ) ) {
			return new \WP_Error( 'MODULE_NOT_EXISTS', __( 'Module not exists or invalid module request' ) );
		}

		return $items[ $id ];

	}

	/**
	 * Get module's data according to request query.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function get_item_data( $request ) {
		$id    = $request->get_param( 'id' );
		$items = $this->modules_manager->get_modules_data();

		$query_data = $request->get_param( 'query' );

		if ( ! isset( $items[ $id ] ) ) {
			return new \WP_Error( 'MODULE_NOT_EXISTS', __( 'Module not exists or invalid module request' ) );
		}

		if ( ! empty( $query_data ) ) {
			return apply_filters( "wyvern_toolkit_filter_{$id}_api_query", array(), $request->get_query_params() );
		}

		return $items[ $id ];

	}

	/**
	 * Updated requested module data.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function update_item( $request ) {
		$module = $request->get_param( 'id' );

		$items = $this->modules_manager->get_modules_data();

		if ( ! $module || ! isset( $items[ $module ] ) ) {
			return new \WP_Error( 'MODULE_NOT_EXISTS', __( 'Module not exists or invalid module update request' ) );
		}

		$this->modules_manager->get_helpers()::update_module( $module, $request->get_params() );

		$this->modules_manager->reset_modules();

		return $this->modules_manager->get_modules_data();

	}

	/**
	 * Updated module's data according to request query.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function update_item_data( $request ) {
		$id    = $request->get_param( 'id' );
		$items = $this->modules_manager->get_modules_data();

		if ( ! isset( $items[ $id ] ) ) {
			return new \WP_Error( 'MODULE_NOT_EXISTS', __( 'Module not exists or invalid module request' ) );
		}

		return apply_filters( "wyvern_toolkit_filter_{$id}_api_update", array(), $request->get_params() );
	}

}
