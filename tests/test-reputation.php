<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestReputation extends TestCase {

	use \Anspress\Tests\Testcases\Common;

	/**
	 * @covers ::ap_insert_reputation
	 */
	public function testAPInsertReputation() {
		$id = $this->insert_answer();

		// Test begins.
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_insert_reputation( '', $id->q ) );
		$this->assertFalse( ap_insert_reputation( 'ask', $id->q, 0 ) );
		$this->assertIsInt( ap_insert_reputation( 'ask', $id->q ) );
		$this->assertIsInt( ap_insert_reputation( 'answer', $id->a ) );
		$this->assertIsInt( ap_insert_reputation( 'select_answer', $id->a ) );
		$this->assertIsInt( ap_insert_reputation( 'best_answer', $id->a ) );

		// Test for constant.
		define( 'AP_DISABLE_INSERT_REP', true );
		$this->assertNull( ap_insert_reputation( 'ask', $id->q ) );
	}
}
