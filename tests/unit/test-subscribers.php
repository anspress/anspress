<?php
class UserTest extends \Codeception\TestCase\Test
{
    public function testValidation()
    {
        $this->assertTrue(ap_new_subscriber( 1, 111, 'q_all', 111, 0 ));           
    }
}