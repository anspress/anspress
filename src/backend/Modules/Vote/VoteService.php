<?php
/**
 * Vote service.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Validator;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vote service.
 *
 * @since 5.0.0
 */
class VoteService extends AbstractService {
	/**
	 * Create a new vote.
	 *
	 * @param array $data Vote data.
	 * @return null|VoteModel  Vote model.
	 */
	public function create( array $data ): ?VoteModel {
		// Check if user can create vote.
		Auth::checkAndThrow( 'create', new VoteModel() );

		$data['vote_user_id'] = get_current_user_id();

		$validator = new Validator(
			$data,
			array(
				'vote_user_id' => 'required|numeric|exists:users,ID',
				'vote_type'    => 'required|string|max:120',
				'vote_ref_id'  => 'required|numeric',
				'vote_value'   => 'required|numeric|min:-1|max:1',
			)
		);

		$validated = $validator->validated();

		$vote = new VoteModel();

		$vote->fill( $validated );

		$updated = $vote->save();

		return $updated;
	}
}
