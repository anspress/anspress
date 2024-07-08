<?php
/**
 * Tag controller.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Tag;

use AnsPress\Classes\AbstractController;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Tag controller class.
 */
class TagController extends AbstractController {
	/**
	 * Get tags.
	 *
	 * @return \WP_REST_Response
	 */
	public function index(): WP_REST_Response {
		$perPage = 20;
		$page    = absint( $this->getParam( 'page', 1 ) );

		$tags = get_terms(
			array(
				'taxonomy'   => 'question_tag',
				'hide_empty' => false,
				'number'     => $perPage,
				'offset'     => ( $page - 1 ) * $perPage,
				'search'     => sanitize_text_field( $this->getParam( 'search' ) ),
			)
		);

		return $this->response( array( 'data' => $tags ) );
	}
}
