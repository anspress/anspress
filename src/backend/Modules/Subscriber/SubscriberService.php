<?php
/**
 * Subscriber service.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Subscriber;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Validator;
use AnsPress\Modules\Subscriber\SubscriberModel;
use Exception;
use InvalidArgumentException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
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
		$data['subs_user_id'] = get_current_user_id();

		$validator = new Validator(
			$data,
			array(
				'subs_user_id' => 'required|numeric|exists:users,ID',
				'subs_event'   => 'required|string',
				'subs_ref_id'  => 'required|numeric',
			)
		);

		$validated = $validator->validated();

		$subscriber = new SubscriberModel();

		$subscriber->fill( $validated );

		$updated = $subscriber->save();

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

	/**
	 * Get subscribers.
	 *
	 * @param array $where Where clauses.
	 * @param array $args Arguments.
	 * @return SubscriberModel[]|null
	 */
	public function getSubscribers( array $where = array(), array $args = array() ): ?array {
		global $wpdb;

		$where = wp_parse_args(
			$where,
			array(
				'subs_event'   => '',
				'subs_ref_id'  => '',
				'subs_user_id' => '',
			)
		);

		$args = wp_parse_args(
			$args,
			array(
				'limit'  => 10,
				'offset' => 0,
			)
		);

		$where = wp_array_slice_assoc( $where, array( 'subs_event', 'subs_ref_id', 'subs_user_id' ) );

		$table = SubscriberModel::getSchema()->getTableName();

		$sql = "SELECT * FROM $table WHERE 1=1";

		foreach ( $where as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			$sql .= $wpdb->prepare( " AND $key = %s", $value ); // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery
		}

		$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $args['limit'], $args['offset'] ); // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery

		return SubscriberModel::findMany( $sql );
	}

	/**
	 * Check if user is subscribed to a question.
	 *
	 * @param int      $questionId Question id.
	 * @param int|null $userId User id.
	 * @return SubscriberModel|null
	 */
	public function isSubscribedToQuestion( int $questionId, ?int $userId = null ): ?SubscriberModel {
		$userId = $userId ?? Auth::getID();

		if ( empty( $userId ) ) {
			return null;
		}

		$subscribers = $this->getSubscribers(
			array(
				'subs_user_id' => $userId,
				'subs_event'   => 'question',
				'subs_ref_id'  => $questionId,
			),
			array(
				'limit' => 1,
			)
		);

		if ( empty( $subscribers ) ) {
			return null;
		}

		return $subscribers[0];
	}

	/**
	 * Subscribe to a question.
	 *
	 * @param int      $questionId Question ID.
	 * @param int|null $userId User ID.
	 * @return SubscriberModel
	 * @throws InvalidArgumentException If user is already subscribed to the question.
	 */
	public function subscribeToQuestion( int $questionId, ?int $userId = null ): SubscriberModel {
		$userId = $userId ?? Auth::getID();

		if ( empty( $userId ) ) {
			throw new InvalidArgumentException( esc_attr__( 'User ID is required when creating a question subscription.', 'anspress-question-answer' ) );
		}

		if ( $this->isSubscribedToQuestion( $questionId, $userId ) ) {
			throw new InvalidArgumentException( esc_attr__( 'User is already subscribed to this question.', 'anspress-question-answer' ) );
		}

		$data = array(
			'subs_user_id' => $userId,
			'subs_event'   => 'question',
			'subs_ref_id'  => $questionId,
		);

		return $this->create( $data );
	}

	/**
	 * Get the count of subscribers by event and ref id.
	 *
	 * @param string $event Question ID.
	 * @param int    $refId Ref ID.
	 * @return bool
	 */
	public function getSubscriberCountByEventRef( string $event, int $refId ): int {
		global $wpdb;

		$table = SubscriberModel::getSchema()->getTableName();

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE subs_event = %s AND subs_ref_id = %d", $event, $refId ); // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery

		return (int) $wpdb->get_var( $sql ); // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Get question subscribers.
	 *
	 * @param int   $questionId Question ID.
	 * @param array $args Arguments.
	 * @return SubscriberModel[]|null
	 */
	public function getQuestionSubscribers( int $questionId, array $args = array() ): ?array {
		$where = array(
			'subs_event'  => 'question',
			'subs_ref_id' => $questionId,
		);

		$args = wp_parse_args(
			$args,
			array(
				'limit' => 15,
			)
		);

		return $this->getSubscribers( $where, $args );
	}
}
