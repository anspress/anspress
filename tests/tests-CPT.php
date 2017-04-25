<?php
class ActivityTest extends AnsPress_UnitTestCase
{
	private $_question;
	private $_answer;
	private $_comment;

	public function setUp() {
		// before
		parent::setUp();
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	public function test_get_activity() {
		$this->assertObjectHasAttribute( 'id', $out );
		$this->assertObjectHasAttribute( 'user_id', $out );
		$this->assertObjectHasAttribute( 'type', $out );
		$this->assertObjectHasAttribute( 'parent_type', $out );
		$this->assertObjectHasAttribute( 'status', $out );
		$this->assertObjectHasAttribute( 'content', $out );
		$this->assertObjectHasAttribute( 'permalink', $out );
		$this->assertObjectHasAttribute( 'question_id', $out );
		$this->assertObjectHasAttribute( 'answer_id', $out );
		$this->assertObjectHasAttribute( 'item_id', $out );
		$this->assertObjectHasAttribute( 'term_ids', $out );
		$this->assertObjectHasAttribute( 'created', $out );
		$this->assertObjectHasAttribute( 'updated', $out );
	}
}