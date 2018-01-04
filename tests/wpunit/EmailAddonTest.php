<?php

class EmailAddonTest extends \Codeception\TestCase\WPTestCase
{
  use \AnsPress\Tests\Testcases\Common;

    public function setUp()
    {
        // before
        parent::setUp();

        ap_activate_addon('free/email.php');
    }


    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    /**
     * @covers AnsPress_Email_Hooks::ap_after_new_question
     */
    public function testAPAfterNewQuestion()
    {
        // Init addon.
        AnsPress_Email_Hooks::init();

        // Check hook exists.
        $this->assertEquals( 10, has_action( 'ap_after_new_question', [ 'AnsPress_Email_Hooks', 'ap_after_new_question' ] ) );
        ap_opt( 'email_admin_emails', 'admin@local.local, admin2@aptext.local, admin@site.com' );

        $this->setRole('subscriber');

		// Check if question created without author set current user as subscriber.
		$id = $this->insert_question('', '', get_current_user_id());

		// Run action so that ap_after_new_question hook can trigger.
        do_action( 'ap_processed_new_question', $id, get_post( $id ) );

        $mailer = tests_retrieve_phpmailer_instance();
        codecept_debug($mailer->get_recipient( 'to' )->address);
        $this->assertEquals( 'admin@local.local', $mailer->get_recipient( 'to' )->address );
        $this->assertEquals( 'admin2@aptext.local', $mailer->get_recipient( 'bcc' )->address );
        $this->assertEquals( 'admin@site.com', $mailer->get_recipient( 'bcc', 0, 1 )->address );
    }

}