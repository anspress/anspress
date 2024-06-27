<?php

namespace Tests\Unit\src\backend\Classes;

use AnsPress\Classes\Auth;
use AnsPress\Modules\Vote\VoteModel;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers AnsPress\Modules\Vote\VotePolicy
 * @package Tests\WP
 */
class TestVotePolicy extends TestCase {

	public function testViewBeforePass() {
		global $wpdb;

		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'manage_options' );

		$this->assertTrue( $policy->before( 'view', $user ) );
	}

	public function testViewBeforeFail() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$this->assertNull( $policy->before( 'view', $user ) );
	}

	public function testViewPass() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'vote:view');

		$vote = VoteModel::create( array(
			'vote_user_id' => $user->ID,
			'vote_post_id' => 1,
			'vote_type'    => 'vote',
			'vote_value'   => '-1'
		) );

		$this->assertTrue( $policy->check( 'view', $user, [ 'vote' => $vote ] ) );
	}

	public function testViewFail() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'vote:view');

		$vote = VoteModel::create( array(
			'vote_user_id' => $this->factory()->user->create(),
			'vote_post_id' => 1,
			'vote_type'    => 'vote',
			'vote_value'   => '-1'
		) );

		$this->assertFalse( $policy->check( 'view', $user, [ 'vote' => $vote ] ) );
	}

	public function testCreatePass() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'vote:create');

		$this->assertTrue( $policy->check( 'create', $user ) );
	}

	public function testCreateFail() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$this->assertFalse( $policy->check( 'create', $user ) );
	}

	public function testUpdatePass() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'vote:update');

		$vote = VoteModel::create( array(
			'vote_user_id' => $user->ID,
			'vote_post_id' => 1,
			'vote_type'    => 'vote',
			'vote_value'   => '-1'
		) );

		$this->assertTrue( $policy->check( 'update', $user, [ 'vote' => $vote ] ) );
	}

	public function testUpdateFail() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'vote:update');

		$vote = VoteModel::create( array(
			'vote_user_id' => $this->factory()->user->create(),
			'vote_post_id' => 1,
			'vote_type'    => 'vote',
			'vote_value'   => '-1'
		) );

		$this->assertFalse( $policy->check( 'update', $user, [ 'vote' => $vote ] ) );
	}

	public function testDeletePass() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'vote:delete');

		$vote = VoteModel::create( array(
			'vote_user_id' => $user->ID,
			'vote_post_id' => 1,
			'vote_type'    => 'vote',
			'vote_value'   => '-1'
		) );

		$this->assertTrue( $policy->check( 'delete', $user, [ 'vote' => $vote ] ) );
	}

	public function testDeleteFail() {
		$policy = new \AnsPress\Modules\Vote\VotePolicy('vote');

		$user = $this->factory()->user->create_and_get();

		$user->add_cap( 'vote:delete');

		$vote = VoteModel::create( array(
			'vote_user_id' => $this->factory()->user->create(),
			'vote_post_id' => 1,
			'vote_type'    => 'vote',
			'vote_value'   => '-1'
		) );

		$this->assertFalse( $policy->check( 'delete', $user, [ 'vote' => $vote ] ) );
	}
}
