<?php

class SessionTest extends \Codeception\TestCase\WPTestCase
{

    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    /**
	 * @covers AnsPress\Session::init
	 */
	public function testInit() {
		$this->assertClassHasStaticAttribute( 'instance', 'AnsPress\Session' );
    }

    /**
     * @covers AnsPress\Session::set_answer
     *
     * @return void
     */
    public function testSetAnswer() {
        AnsPress\Session::init()->set_answer(2);
        $this->assertArraySubset( [2], AnsPress\Session::init()->get( 'answers' ) );
    }

}