<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQAMeta extends TestCase {

	/**
	 * @covers ::ap_qameta_fields
	 */
	public function testAPQametaFields() {
		// Test for if the array key exists or not.
		$qameta_fields_array = array(
			'post_id',
			'selected',
			'selected_id',
			'comments',
			'answers',
			'ptype',
			'featured',
			'closed',
			'views',
			'votes_up',
			'votes_down',
			'subscribers',
			'flags',
			'terms',
			'attach',
			'activities',
			'fields',
			'roles',
			'last_updated',
			'is_new',
		);
		foreach ( $qameta_fields_array as $qameta_field_item ) {
			$this->assertArrayHasKey( $qameta_field_item, ap_qameta_fields() );
		}

		// Test for the qameta fields default value is matching or not.
		$qameta_fields = ap_qameta_fields();
		$this->assertEquals( '', $qameta_fields['post_id'] );
		$this->assertFalse( $qameta_fields['selected'] );
		$this->assertEquals( 0, $qameta_fields['selected_id'] );
		$this->assertEquals( 0, $qameta_fields['comments'] );
		$this->assertEquals( 0, $qameta_fields['answers'] );
		$this->assertEquals( 'question', $qameta_fields['ptype'] );
		$this->assertEquals( 0, $qameta_fields['featured'] );
		$this->assertEquals( 0, $qameta_fields['closed'] );
		$this->assertEquals( 0, $qameta_fields['views'] );
		$this->assertEquals( 0, $qameta_fields['votes_up'] );
		$this->assertEquals( 0, $qameta_fields['votes_down'] );
		$this->assertEquals( 0, $qameta_fields['subscribers'] );
		$this->assertEquals( 0, $qameta_fields['flags'] );
		$this->assertEquals( '', $qameta_fields['terms'] );
		$this->assertEquals( '', $qameta_fields['attach'] );
		$this->assertEquals( '', $qameta_fields['activities'] );
		$this->assertEquals( '', $qameta_fields['fields'] );
		$this->assertEquals( '', $qameta_fields['roles'] );
		$this->assertEquals( '', $qameta_fields['last_updated'] );
		$this->assertFalse( $qameta_fields['is_new'] );
	}
}
