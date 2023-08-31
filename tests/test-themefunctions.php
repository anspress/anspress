<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestThemeFunctions extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	public function addFilter() {
		return 'Question title';
	}

	/**
	 * @covers ::ap_page_title
	 */
	public function testAPPageTitle() {
		$this->assertEquals( '', ap_page_title() );

		// Filter apply test,
		add_filter( 'ap_page_title', array( $this, 'addFilter' ) );
		$this->assertNotEquals( '', ap_page_title() );
		$this->assertEquals( 'Question title', ap_page_title() );

		// Filter remove test,
		remove_filter( 'ap_page_title', array( $this, 'addFilter' ) );
		$this->assertNotEquals( 'Question title', ap_page_title() );
		$this->assertEquals( '', ap_page_title() );
	}

}
