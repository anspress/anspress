<?php
/**
 * Subscriber policy class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Subscriber;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractPolicy;
use WP_User;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscriber policy class.
 */
class SubscriberPolicy extends AbstractPolicy {
	/**
	 * Determine if the given user can view the specified model.
	 *
	 * @param WP_User       $user The current user attempting the action.
	 * @param AbstractModel $model The model instance being viewed.
	 * @return bool True if the user is authorized to view the model, false otherwise.
	 */
	public function view( WP_User $user, AbstractModel $model ): bool {
		return true;
	}
}
