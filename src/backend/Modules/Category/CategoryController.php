<?php
/**
 * Category controller.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Category;

use AnsPress\Classes\AbstractController;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Category controller class.
 */
class CategoryController extends AbstractController {
	/**
	 * Get categories.
	 *
	 * @return \WP_REST_Response
	 */
	public function index(): WP_REST_Response {
		$perPage = 20;
		$page    = absint( $this->getParam( 'page', 1 ) );

		$categories = get_terms(
			array(
				'taxonomy'   => 'question_category',
				'hide_empty' => false,
				'number'     => $perPage,
				'offset'     => ( $page - 1 ) * $perPage,
				'search'     => sanitize_text_field( $this->getParam( 'search' ) ),
			)
		);

		return $this->response( array( 'data' => $categories ) );
	}
}
