<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Require the api.php file.
require_once ANSPRESS_DIR . 'includes/api.php';

class TestAPI extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_API', 'register' ) );
		$this->assertTrue( method_exists( 'AnsPress_API', 'avatar' ) );
	}

	/**
	 * @covers AnsPress_API::avatar
	 */
	public function testAvatar() {
		// Test 1.
		$request = new \WP_REST_Request();
		$user_id = $this->factory()->user->create();
		$request->set_query_params( array(
			'id'   => $user_id,
			'size' => 50,
		) );
		$response = \AnsPress_API::avatar( $request );
		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertEquals( 200, $response->get_status() );

		// Test 2.
		$request->set_query_params( array() );
		$response = \AnsPress_API::avatar( $request );
		$this->assertInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 'wrongData', $response->get_error_code() );
	}
}
