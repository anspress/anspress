<?php
/**
 * Subscriber model.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Subscriber;

use AnsPress\Classes\AbstractModel;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscriber model.
 *
 * @package AnsPress
 */
class SubscriberModel extends AbstractModel {
	/**
	 * Primary column key.
	 *
	 * @var string
	 */
	protected string $primaryKey = 'subs_id';

	/**
	 * Subscriber ID.
	 *
	 * @var int
	 */
	public int $subs_id;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public int $subs_user_id;

	/**
	 * Reference id.
	 *
	 * @var int
	 */
	public int $subs_ref_id;

	/**
	 * Event type.
	 *
	 * @var string
	 */
	public string $subs_event;
}
