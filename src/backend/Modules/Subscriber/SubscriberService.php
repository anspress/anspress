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
		return $this->isSubscribed( $questionId, 'question', $userId );
	}

	/**
	 * Check if user is subscribed to an answer.
	 *
	 * @param int      $answerId Answer id.
	 * @param null|int $userId User id.
	 * @return null|SubscriberModel
	 */
	public function isSubscribedToAnswer( int $answerId, ?int $userId = null ): ?SubscriberModel {
		$postParent = get_post_field( 'post_parent', $answerId );
		return $this->isSubscribed( $answerId, 'answer_' . $postParent, $userId );
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
		$question = get_post( $questionId );

		// Check if user has access to view question.
		Auth::checkAndThrow( 'question:view', array( 'question' => $question ) );

		$subscriber = $this->subscribe( $questionId, 'question', $userId );
		$count      = $this->getSubscriberCountByEventRef( 'question', $questionId );

		ap_insert_qameta( $questionId, array( 'subscribers' => $count ) );

		return $subscriber;
	}

	/**
	 * Subscribe to an answer.
	 *
	 * @param int      $answerId Answer ID.
	 * @param int|null $userId User ID.
	 * @return SubscriberModel
	 * @throws InvalidArgumentException If user is already subscribed to the answer.
	 */
	public function subscribeToAnswer( int $answerId, ?int $userId = null ): SubscriberModel {
		$postParent = get_post_field( 'post_parent', $answerId );
		$answer     = get_post( $answerId );

		// Check if user has access to view answer.
		Auth::currentUserCan( 'answer:view', array( 'answer' => $answer ) );

		$event      = 'answer_' . $postParent;
		$subscriber = $this->subscribe( $answerId, $event, $userId );

		$count = $this->getSubscriberCountByEventRef( $event, $answerId );

		ap_insert_qameta( $answerId, array( 'subscribers' => $count ) );

		return $subscriber;
	}

	/**
	 * Subscribe.
	 *
	 * @param int      $refId ID.
	 * @param string   $event Event.
	 * @param int|null $userId User ID.
	 * @return SubscriberModel
	 * @throws InvalidArgumentException If user is already subscribed.
	 */
	public function subscribe( int $refId, string $event, ?int $userId = null ): SubscriberModel {
		$userId = $userId ?? Auth::getID();

		if ( empty( $userId ) ) {
			throw new InvalidArgumentException( esc_attr__( 'User ID is required when creating a answer subscription.', 'anspress-question-answer' ) );
		}

		if ( $this->isSubscribed( $refId, $event, $userId ) ) {
			throw new InvalidArgumentException( esc_attr__( 'User is already subscribed.', 'anspress-question-answer' ) );
		}

		$data = array(
			'subs_user_id' => $userId,
			'subs_event'   => $event,
			'subs_ref_id'  => $refId,
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
	 * Check if user is subscribed.
	 *
	 * @param int      $refId Reference id.
	 * @param string   $event Event name.
	 * @param null|int $userId User id.
	 * @return null|SubscriberModel
	 */
	public function isSubscribed( int $refId, string $event, ?int $userId = null ): ?SubscriberModel {
		$userId = $userId ?? Auth::getID();

		if ( empty( $userId ) ) {
			return null;
		}

		$subscribers = $this->getSubscribers(
			array(
				'subs_user_id' => $userId,
				'subs_event'   => $event,
				'subs_ref_id'  => $refId,
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
