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
	 * The model's primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'subs_id';

	/**
	 * The model's table name.
	 *
	 * @var string
	 */
	protected $tableName = 'ap_subscribers';

	/**
	 * The model's columns.
	 *
	 * @var string[]
	 */
	protected $columns = array(
		'subs_id'      => '%d',
		'subs_user_id' => '%d',
		'subs_ref_id'  => '%d',
		'subs_event'   => '%s',
	);
}
