<?php


/**
 * @group edd_cpt
 */
class Tests_Post_Types extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_question_post_type() {
		global $wp_post_types;
		$this->assertArrayHasKey( 'question', $wp_post_types );
	}

	public function test_questions_post_type_labels() {
		global $wp_post_types;
		$this->assertEquals( 'Question', $wp_post_types['question']->labels->name );
		
	}

}