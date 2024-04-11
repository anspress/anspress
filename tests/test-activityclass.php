<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestActivityClass extends TestCase {

	use Testcases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Activity' );
		$this->assertTrue( $class->hasProperty( 'verbs' ) && $class->getProperty( 'verbs' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'per_page' ) && $class->getProperty( 'per_page' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'in_group' ) && $class->getProperty( 'in_group' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Activity', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'query' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'prefetch' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'prefetch_posts' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'prefetch_actors' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'prefetch_comments' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'have_group' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'group_start' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'group_end' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'have_group_items' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'count_group' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'has_action' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_the_verb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'the_verb' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_avatar' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_user_id' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_the_date' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_the_icon' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'the_icon' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'the_ref_content' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'when' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'the_when' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'get_q_id' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity', 'more_button' ) );
	}

	/**
	 * @covers AnsPress\Activity::__construct
	 */
	public function testConstructor() {
		$activity = new \AnsPress\Activity();
		$this->assertInstanceOf( 'AnsPress\Activity', $activity );
	}

	/**
	 * @covers AnsPress\Activity::__construct
	 */
	public function testConstructorWithoutArgs() {
		$activity = new \AnsPress\Activity();
		$this->assertInstanceOf( 'AnsPress\Activity', $activity );

		// Tests.
		$this->assertEquals( 1, $activity->paged );
		$this->assertEquals( 0, $activity->offset );
		$this->assertEquals( 30, $activity->args['number'] );
		$this->assertEquals( 0, $activity->args['offset'] );
		$this->assertEquals( 'activity_date', $activity->args['orderby'] );
		$this->assertEquals( 'DESC', $activity->args['order'] );
		$this->assertEquals( [ 'administrator' ], $activity->args['exclude_roles'] );
		$this->assertEquals( 30, $activity->per_page );
	}

	/**
	 * @covers AnsPress\Activity::__construct
	 */
	public function testConstructorWithArgs() {
		$user_id = $this->factory()->user->create();
		$activity = new \AnsPress\Activity( [
			'number'        => 12,
			'offset'        => 2,
			'orderby'       => 'activity_q_id',
			'order'         => 'ASC',
			'exclude_roles' => [],
			'paged'         => 2,
			'user_id'       => $user_id,
		] );
		$this->assertInstanceOf( 'AnsPress\Activity', $activity );

		// Tests.
		$this->assertEquals( 2, $activity->paged );
		$this->assertEquals( 30, $activity->offset );
		$this->assertEquals( 12, $activity->args['number'] );
		$this->assertEquals( 2, $activity->args['offset'] );
		$this->assertEquals( 'activity_q_id', $activity->args['orderby'] );
		$this->assertEquals( 'ASC', $activity->args['order'] );
		$this->assertEquals( [], $activity->args['exclude_roles'] );
		$this->assertEquals( 2, $activity->args['paged'] );
		$this->assertEquals( $user_id, $activity->args['user_id'] );
		$this->assertEquals( 12, $activity->per_page );
	}

	/**
	 * @covers AnsPress\Activity::__construct
	 */
	public function testConstructorWithOrderByArgAsInvalid() {
		$activity = new \AnsPress\Activity( [ 'orderby' => 'invalid_order_by' ] );
		$this->assertInstanceOf( 'AnsPress\Activity', $activity );
		$this->assertEquals( 'activity_date', $activity->args['orderby'] );
	}

	/**
	 * @covers AnsPress\Activity::group_end
	 */
	public function testGroupEnd() {
		$activity = new \AnsPress\Activity();
		$activity->in_group = true;

		// Test.
		$activity->group_end();
		$this->assertFalse( $activity->in_group );
	}

	/**
	 * @covers AnsPress\Activity::when
	 */
	public function testWhenForJustUnder30Minutes() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-10 minutes' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-20 minutes' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-25 minutes' ) ),
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activities->the_object();
			$this->assertEquals( 'Just now', $activities->when( $activities->object ) );
		}
	}

	/**
	 * @covers AnsPress\Activity::when
	 */
	public function testWhenForJustUnder1Day() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-45 minutes' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-11 hours' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-23 hours' ) ),
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activities->the_object();
			$this->assertEquals( 'Today', $activities->when( $activities->object ) );
		}
	}

	/**
	 * @covers AnsPress\Activity::when
	 */
	public function testWhenForJustUnder2Days() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-25 hours' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-36 hours' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-47 hours' ) ),
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activities->the_object();
			$this->assertEquals( 'Yesterday', $activities->when( $activities->object ) );
		}
	}

	/**
	 * @covers AnsPress\Activity::when
	 */
	public function testWhenForBelow1Year() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-3 days 1 hour' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-1 week' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-1 month' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][2],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-11 months 30 days' ) ),
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activities->the_object();
			$this->assertEquals( date_i18n( 'M', strtotime( $activity->date ) ), $activities->when( $activities->object ) );
		}
	}

	/**
	 * @covers AnsPress\Activity::get_q_id
	 */
	public function testGetQIdForSingleQuestion() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activities->the_object();
			$this->assertEquals( $ids['question'], $activities->get_q_id() );
		}
	}

	/**
	 * @covers AnsPress\Activity::get_q_id
	 */
	public function testGetQIdForMultipleQuestions() {
		$this->setRole( 'subscriber' );
		$id_1 = $this->insert_answer();
		$id_2 = $this->insert_answer();
		$id_3 = $this->insert_answer();

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id_1->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id_2->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $id_3->q,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id_1->q,
				'a_id'   => $id_1->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id_2->q,
				'a_id'   => $id_2->a,
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $id_3->q,
				'a_id'   => $id_3->a,
			]
		);

		// Test begins.
		$activities = new \AnsPress\Activity();

		// Test 1.
		$activities->objects[0];
		$activities->the_object();
		$this->assertEquals( $id_1->q, $activities->get_q_id() );

		// Test 2.
		$activities->objects[1];
		$activities->the_object();
		$this->assertEquals( $id_2->q, $activities->get_q_id() );

		// Test 3.
		$activities->objects[2];
		$activities->the_object();
		$this->assertEquals( $id_3->q, $activities->get_q_id() );

		// Test 4.
		$activities->objects[3];
		$activities->the_object();
		$this->assertEquals( $id_1->q, $activities->get_q_id() );

		// Test 5.
		$activities->objects[4];
		$activities->the_object();
		$this->assertEquals( $id_2->q, $activities->get_q_id() );

		// Test 6.
		$activities->objects[5];
		$activities->the_object();
		$this->assertEquals( $id_3->q, $activities->get_q_id() );
	}

	/**
	 * @covers AnsPress\Activity::has_action
	 */
	public function testHasActionShouldReturnTrueIfActionExists() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activities->the_object();
			$this->assertTrue( $activities->has_action() );
		}
	}

	/**
	 * @covers AnsPress\Activity::has_action
	 */
	public function testHasActionShouldReturnFalseIfActionDoesNotExist() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity which should not be valid.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
			]
		);

		// Test.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activity->action = '';
			$activities->the_object();
			$this->assertFalse( $activities->has_action() );
		}
	}

	/**
	 * @covers AnsPress\Activity::get_the_verb
	 */
	public function testGetTheVerbReturnVerbContents() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
			]
		);

		// Test begins.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );

		// Test 1.
		$activities->objects[0];
		$activities->the_object();
		$this->assertEquals( 'Asked question', $activities->get_the_verb() );

		// Test 2.
		$activities->objects[1];
		$activities->the_object();
		$this->assertEquals( 'Answered question', $activities->get_the_verb() );

		// Test 3.
		$activities->objects[2];
		$activities->the_object();
		$this->assertEquals( 'Answered question', $activities->get_the_verb() );
	}

	/**
	 * @covers AnsPress\Activity::get_the_verb
	 */
	public function testGetTheVerbReturnNullIfDoesNotHaveAction() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity which should not be valid.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
			]
		);

		// Test begins.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activity->action = '';
			$activities->the_object();
			$this->assertNull( $activities->get_the_verb() );
		}
	}

	/**
	 * @covers AnsPress\Activity::get_the_verb
	 */
	public function testGetTheVerbReturnNullIfActionVerbIsEmpty() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity which should not be valid.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
			]
		);

		// Test begins.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );
		foreach ( $activities->objects as $activity ) {
			$activity->action['verb'] = '';
			$activities->the_object();
			$this->assertNull( $activities->get_the_verb() );
		}
	}

	/**
	 * @covers AnsPress\Activity::the_verb
	 */
	public function testTheVerb() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
			]
		);

		// Test begins.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );

		// Test 1.
		$activities->objects[0];
		$activities->the_object();
		ob_start();
		$activities->the_verb();
		$output = ob_get_clean();
		$this->assertEquals( 'Asked question', $output );

		// Test 2.
		$activities->objects[1];
		$activities->the_object();
		ob_start();
		$activities->the_verb();
		$output = ob_get_clean();
		$this->assertEquals( 'Answered question', $output );

		// Test 3.
		$activities->objects[2];
		$activities->the_object();
		ob_start();
		$activities->the_verb();
		$output = ob_get_clean();
		$this->assertEquals( 'Answered question', $output );
	}

	/**
	 * @covers AnsPress\Activity::get_the_date
	 */
	public function testGetTheDate() {
		$this->setRole( 'subscriber' );
		$ids = $this->insert_answers( [], [], 3 );

		// Add some user activity.
		ap_activity_add(
			[
				'action' => 'new_q',
				'q_id'   => $ids['question'],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-10 minutes' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][0],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-20 minutes' ) ),
			]
		);
		ap_activity_add(
			[
				'action' => 'new_a',
				'q_id'   => $ids['question'],
				'a_id'   => $ids['answers'][1],
				'date'   => date( 'Y-m-d H:i:s', strtotime( '-25 minutes' ) ),
			]
		);

		// Test begins.
		$activities = new \AnsPress\Activity( [ 'q_id' => $ids['question'] ] );

		// Test 1.
		$activities->objects[0];
		$activities->the_object();
		$this->assertEquals( date( 'Y-m-d H:i:s', strtotime( '-10 minutes' ) ), $activities->get_the_date() );

		// Test 2.
		$activities->objects[1];
		$activities->the_object();
		$this->assertEquals( date( 'Y-m-d H:i:s', strtotime( '-20 minutes' ) ), $activities->get_the_date() );

		// Test 3.
		$activities->objects[2];
		$activities->the_object();
		$this->assertEquals( date( 'Y-m-d H:i:s', strtotime( '-25 minutes' ) ), $activities->get_the_date() );
	}
}
