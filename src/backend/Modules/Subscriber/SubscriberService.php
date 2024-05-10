<?php
/**
 * Subscriber service.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Subscriber;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\SubscriberModel;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscriber service.
 *
 * @since 5.0.0
 */
class SubscriberService extends AbstractService {
	/**
	 * Get subscriber by user id and ref id.
	 *
	 * @param int $sub_id Subscriber ID.
	 * @return SubscriberModel|null
	 */
	public function getById( $sub_id ) {
		return SubscriberModel::find( $sub_id );
	}
}
