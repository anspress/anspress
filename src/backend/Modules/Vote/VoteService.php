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
		if ( empty( $data['vote_user_id'] ) && Auth::isLoggedIn() ) {
			$data['vote_user_id'] = Auth::user()->ID;
		}

		$validator = new Validator(
			$data,
			array(
				'vote_user_id'  => 'required|numeric|exists:users,ID',
				'vote_rec_user' => 'numeric|exists:users,ID',
				'vote_type'     => 'required|string|max:120',
				'vote_ref_id'   => 'required|numeric',
				'vote_value'    => 'required',
			)
		);

		$validated = $validator->validated();

		$vote = new VoteModel();

		$vote->fill( $validated );

		$updated = $vote->save();

		return $updated;
	}
}
