<?php
/**
 * Subscriber service.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Subscriber;

use AnsPress\Classes\AbstractService;
use AnsPress\Modules\Subscriber\SubscriberModel;

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
	 * Get subascriber by subs_id.
	 *
	 * @param int $subs_id Subscriber ID.
	 * @return SubscriberModel|null
	 */
	public function getById( int $subs_id ): ?SubscriberModel {
		return SubscriberModel::find( $subs_id );
	}

	/**
	 * Get subscriber by user_id, event and ref_id.
	 *
	 * @param int    $user_id User ID.
	 * @param string $event Event type.
	 * @param int    $ref_id Reference ID.
	 * @return SubscriberModel[]|null
	 */
	public function getByUserAndEvent( int $user_id, string $event, int $ref_id ): ?array {
		global $wpdb;

		$table = SubscriberModel::getTableName();

		$sql = $wpdb->prepare( "SELECT * FROM $table WHERE subs_user_id = %d AND subs_event = %s AND subs_ref_id = %d", $user_id, $event, $ref_id ); // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery

		$rows = $wpdb->get_results( $sql, ARRAY_A ); // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( ! $rows ) {
			return array();
		}

		return SubscriberModel::hydrate( $rows );
	}
}
