<?php
/**
 * WP API endpoints.
 *
 * @link         https://anspress.io/anspress
 * @since        2.0.1
 * @author       Rahul Aryan <support@anspress.io>
 * @package      AnsPress
 * @subpackage   API
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_REST_API {
	protected $namespace = 'anspress/v1';

	public function __construct () {
		anspress()->add_action( 'rest_api_init', $this, 'register_routes' );


	}

	/**
	* Register the /wp-json/myplugin/v1/foo route
	*/
	public function register_routes() {
		register_rest_route( $this->namespace, 'answers/(?P<id>\d+)', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [ $this, 'get_answers' ],
			'args' => array(
					'id' => array(
						'validate_callback' => 'is_numeric'
					),
				),
		) );
	}

	/**
		* Generate results for the /wp-json/myplugin/v1/foo route.
		*
		* @param  WP_REST_Request $request Full details about the request.
		* @return WP_REST_Response|WP_Error The response for the request.
		*/
	public function get_answers( WP_REST_Request $request ) {
		print_r( $request );
		/*if ( empty( $posts ) ) {
			return new WP_Error( 'awesome_no_author', 'Invalid author', array( 'status' => 404 ) );
		}*/

		$data = array();

		return $data;
	}
 }

