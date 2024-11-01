<?php
/**
 * Abstract class for API.
 *
 * @package WyvernToolkit\Abstract
 */

namespace WyvernToolkit\Abstracts;

/**
 * 
 */
abstract class REST_Controller extends \WP_REST_Controller {

    /**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wyvern-toolkit/v1';

    /**
	 * Init controller.
	 *
	 * @param string $namespace Rest API Namespace.
	 * @param string $route Rest Base Route.
	 */
	public function __construct() {
        $this->register_routes();
	}


    /**
     * Register Routes.
     */
    public function register_rest_route( $args, $base = '' ) {
        $route =  $this->route;
        if ( ! empty( $base ) ) {
            $route .= "/{$base}";
        }
        register_rest_route( $this->namespace, $route, $args );
    }


    /**
     * 
     * 
     * @TODO: Update this ASAP. Override this if required.
     */
    public function get_items_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update options.' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
    }

    /**
     * 
     * @TODO: Update this ASAP. Override this if required.
     */
    public function get_item_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update options.' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
    }

    /**
     * 
     */
    public function update_item_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update options.' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
    }
}