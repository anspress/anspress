<?php

namespace AnsPress\Tests\Testcases;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Common extends TestCase {

	/**
	 * Switches between user roles.
	 *
	 * E.g. administrator, editor, author, contributor, subscriber.
	 *
	 * @param string $role The role to set.
	 */
	public static function setRole( $role ) {
		$post    = $_POST;
		$user_id = self::factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	/**
	 * Clears login cookies, unsets the current user.
	 */
	public static function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array( AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE );
		foreach ( $cookies as $c ) {
			unset( $_COOKIE[ $c ] );
		}
	}

	public static function insert_question( $title = '', $content = '', $author = 0 ) {
		$title   = empty( $title ) ? 'Question title' : $title;
		$content = empty( $content ) ? 'Question content' : $content;

		return self::factory()->post->create(
			array(
				'post_title'   => $title,
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => $content,
				'post_author'  => $author,
			)
		);
	}

	public static function insert_answer( $title = '', $content = '', $author = 0 ) {
		$title   = empty( $title ) ? 'Question title' : $title;
		$content = empty( $content ) ? 'Question content' : $content;

		$ids      = [];
		$ids['q'] = self::insert_question();
		$ids['a'] = self::factory()->post->create(
			array(
				'post_title'   => $title,
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => $content,
				'post_author'  => $author,
				'post_parent'  => $ids['q'],
			)
		);

		return (object) $ids;
	}

	/**
	 * Insert question along with 10 answers.
	 *
	 * @param array $q_args
	 * @param array $a_args
	 * @return array
	 */
	public static function insert_answers( $q_args = [], $a_args = [], $answer_num = 1 ) {
		$ids = [
			'question' => 0,
			'answers'  => [],
		];

		$q_args = wp_parse_args(
			$q_args, array(
				'post_type'   => 'question',
				'post_status' => 'publish',
				'post_author' => 0,
			)
		);

		$ids['question'] = self::factory()->post->create( $q_args );

		$a_args = wp_parse_args(
			$a_args, array(
				'post_type'   => 'answer',
				'post_status' => 'publish',
				'post_parent' => $ids['question'],
				'post_author' => 0,
			)
		);

		$ids['answers'] = self::factory()->post->create_many( $answer_num, $a_args );

		return $ids;
	}
}
