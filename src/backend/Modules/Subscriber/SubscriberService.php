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
use Exception;

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
	 * Create a new subscriber.
	 *
	 * @param array $data Subscriber data.
	 * @return null|SubscriberModel  Subscriber model.
	 */
	public function create( array $data ): ?SubscriberModel {
		// Add current user id if not set.
		if ( empty( $data['subs_user_id'] ) ) {
			$data['subs_user_id'] = get_current_user_id();
		}

		$subscriber = new SubscriberModel();

		$subscriber->fill( $data );

		$updated = $subscriber->save();

		if ( ! $updated ) {
			return null;
		}

		return $updated;
	}

	/**
	 * Update subscriber.
	 *
	 * @param int   $subsId Subscriber ID.
	 * @param array $data Subscriber data.
	 * @return null|SubscriberModel
	 * @throws Exception If subscriber not found.
	 */
	public function update( int $subsId, array $data ): ?SubscriberModel {
		$subscriber = SubscriberModel::find( $subsId );

		if ( ! $subscriber ) {
			throw new Exception( esc_attr__( 'Subscriber not found.', 'anspress-question-answer' ) );
		}

		$subscriber->fill( $data );

		if ( ! $subscriber->save() ) {
			return null;
		}

		return $subscriber;
	}

	/**
	 * Get subascriber by subs_id.
	 *
	 * @param int $subsId Subscriber ID.
	 * @return SubscriberModel|null
	 */
	public function getById( int $subsId ): ?SubscriberModel {
		return SubscriberModel::find( $subsId );
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

		$table = SubscriberModel::getSchema()->getTableName();

		$sql = $wpdb->prepare( "SELECT * FROM $table WHERE subs_user_id = %d AND subs_event = %s AND subs_ref_id = %d", $user_id, $event, $ref_id ); // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery

		return SubscriberModel::findMany( $sql );
	}

	/**
	 * Destroy subscriber.
	 *
	 * @param int $subsId  Subscriber ID.
	 * @return bool True if subscriber deleted successfully.
	 * @throws Exception If subscriber not found.
	 */
	public function destroy( int $subsId ): bool {
		$subscriber = SubscriberModel::find( $subsId );

		if ( ! $subscriber ) {
			throw new Exception( esc_attr__( 'Subscriber not found.', 'anspress-question-answer' ) );
		}

		return $subscriber->delete();
	}
}
