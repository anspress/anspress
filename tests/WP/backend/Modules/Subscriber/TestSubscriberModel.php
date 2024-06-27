<?php

namespace Tests\Unit\src\backend\Classes;

use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers AnsPress\Modules\Subscriber\SubscriberModel
 * @package Tests\WP
 */
class TestSubscriberModel extends TestCase {

	public function testSchema() {
		$model = new \AnsPress\Modules\Subscriber\SubscriberModel();
		$this->assertInstanceOf( \AnsPress\Modules\Subscriber\SubscriberSchema::class, $model->getSchema() );
	}


}
