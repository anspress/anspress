<?php
/**
 * Vote model.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractSchema;
use AnsPress\Classes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscriber model.
 *
 * @package AnsPress
 *
 * @property int    $subs_id      Subscriber ID.
 * @property int    $subs_user_id User ID.
 * @property int    $subs_ref_id  Reference ID.
 * @property string $subs_event   Event.
 */
class VoteModel extends AbstractModel {
	/**
	 * Vote type.
	 */
	const VOTE = 'vote';

	/**
	 * Flag type.
	 */
	const FLAG = 'flag';

	/**
	 * Get the schema.
	 *
	 * @return AbstractSchema
	 */
	public static function createSchema(): AbstractSchema {
		return Plugin::get( VoteSchema::class );
	}

	/**
	 * Get the default value for the vote date column.
	 *
	 * @return string
	 */
	public function getVoteDateColumnDefaultValue() {
		return current_time( 'mysql' );
	}
}
