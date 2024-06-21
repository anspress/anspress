<?php
/**
 * Media service.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Media;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Validator;
use AnsPress\Exceptions\ValidationException;
use WP_Post;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment service.
 *
 * @package AnsPress\Modules\Core
 */
class MediaService extends AbstractService {

	/**
	 * Upload media.
	 *
	 * @param array $data Media data.
	 * @return int Media ID.
	 * @throws ValidationException If validation fails.
	 */
	public function uploadMedia( $data ) {
		$validator = new Validator(
			$data,
			array(
				'file' => array(
					'type'     => 'file',
					'required' => true,
				),
			)
		);

		if ( ! $validator->validate() ) {
			throw new ValidationException( $validator->errors() ); // @codingStandardsIgnoreLine
		}

		$attachment_id = media_handle_upload( 'file', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			throw new ValidationException( $attachment_id->get_error_messages() ); // @codingStandardsIgnoreLine
		}

		return $attachment_id;
	}
}
