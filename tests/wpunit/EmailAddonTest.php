<?php

class EmailAddonTest extends \Codeception\TestCase\WPTestCase
{
  use \AnsPress\Tests\Testcases\Common;

    public function setUp()
    {
        // before
        parent::setUp();

        // Init addon.
        AnsPress_Email_Hooks::init();
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
        // Check hook exists.
        $this->assertEquals( 10, has_action( 'ap_after_new_question', [ 'AnsPress_Email_Hooks', 'ap_after_new_question' ] ) );
        ap_opt( 'email_admin_emails', 'admin@local.local, admin2@aptext.local, admin@site.com' );

        $this->setRole('subscriber');

		// Check if question created without author set current user as subscriber.
		$id = $this->insert_question('', '', get_current_user_id());

		// Run action so that ap_after_new_question hook can trigger.
        do_action( 'ap_processed_new_question', $id, get_post( $id ) );

        $mailer = tests_retrieve_phpmailer_instance();

        $this->assertEquals( 'admin@local.local', $mailer->get_recipient( 'to' )->address );
        $this->assertEquals( 'admin2@aptext.local', $mailer->get_recipient( 'bcc' )->address );
        $this->assertEquals( 'admin@site.com', $mailer->get_recipient( 'bcc', 0, 1 )->address );
    }

    /**
     * @covers AnsPress_Email_Hooks::ap_after_new_answer
     */
    private function testApAfterNewAnswer(){
        // Check hook exists.
        $this->assertEquals( 10, has_action( 'ap_after_new_answer', [ 'AnsPress_Email_Hooks', 'ap_after_new_answer' ] ) );
        $this->setRole('subscriber');
        $q_user = get_current_user_id();

        ap_opt( 'email_admin_emails', 'admin@local.local' );

		// Check if question created without author set current user as subscriber.
        $question_id = $this->insert_question('', '', $q_user);

        // Add a subscriber.
        $subs_user = wp_create_user( 'q_subscriber', 'q_ubscriber', 'q_subscriber@local.com' );
        ap_new_subscriber( $subs_user, 'question', $question_id);

        $this->setRole('subscriber');
        $answer_id = $this->factory->post->create( array( 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $question_id, 'post_author' => get_current_user_id() ) );

        // Run action so that ap_after_new_question hook can trigger.
        do_action( 'ap_processed_new_answer', $answer_id, get_post( $answer_id ) );

        $mailer = tests_retrieve_phpmailer_instance();
        codecept_debug([$mailer->get_recipient( 'bcc', 0, 1 ), $mailer->get_recipient( 'bcc' )]);
        $this->assertEquals( get_user_by('id', $subs_user)->user_email, $mailer->get_recipient( 'bcc', 0, 1 )->address );
        $this->assertEquals( get_user_by('id', $subs_user)->user_email, $mailer->get_recipient( 'bcc' )->address );
        $this->assertEquals( 'admin@local.local', $mailer->get_recipient( 'to' )->address );
    }

}