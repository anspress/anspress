<?php

namespace AnsPress\Tests\WP;

use AnsPress\Classes\Plugin;
use AnsPress\Modules\Subscriber\SubscriberModel;
use AnsPress\Modules\Subscriber\SubscriberService;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestSubscribe extends TestCase {

	use Testcases\Common;

	public function setUp() : void {
		parent::setUp();

		global $wpdb;

		// Remove all data from subscribers table.
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'ap_subscribers' );
	}

	/**
	 * @covers ::ap_new_subscriber
	 * @covers ::ap_get_subscriber
	 */
	public function testNewSubscriber() {
		$subscriber = SubscriberModel::create(
			array(
				'subs_user_id' => 456,
				'subs_event'   => 'question',
				'subs_ref_id'  => 222,
			)
		);

		$this->assertEquals( 1, $subscriber->subs_id );
		$this->assertEquals( 456, $subscriber->subs_user_id );
		$this->assertEquals( 'question', $subscriber->subs_event );

		$subscriber = ap_get_subscriber( 456, 'question', 222 );
		$this->assertEquals( 456, $subscriber->subs_user_id );
		$this->assertEquals( 'question', $subscriber->subs_event );
		$this->assertEquals( 222, $subscriber->subs_ref_id );
	}



}
