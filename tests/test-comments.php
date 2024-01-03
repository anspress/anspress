<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestComments extends TestCase {

	public function testClassProperties() {
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'the_comments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'load_comments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'comments_template_query_args' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'approve_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'comment_link' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'preprocess_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'comments_template' ) );
	}
}
