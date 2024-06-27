<?php

namespace Tests\Unit\src\backend\Classes;

use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers AnsPress\Modules\Subscriber\SubscriberSchema
 * @package Tests\WP
 */
class TestSubscriberSchema extends TestCase {

	public function testGetTableName() {
		$schema = new \AnsPress\Modules\Subscriber\SubscriberSchema();
		$this->assertEquals( $schema->getTableName(), 'wptests_ap_subscribers' );
	}

	public function testGetPrimaryKey() {
		$schema = new \AnsPress\Modules\Subscriber\SubscriberSchema();
		$this->assertEquals( $schema->getPrimaryKey(), 'subs_id' );
	}

	public function testGetColumns() {
		$schema = new \AnsPress\Modules\Subscriber\SubscriberSchema();
		$this->assertEquals( $schema->getColumns(), array(
			'subs_id'      => '%d',
			'subs_user_id' => '%d',
			'subs_ref_id'  => '%d',
			'subs_event'   => '%s',
		) );
	}


}
