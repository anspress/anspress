<?php

class EmailAddonTest extends \Codeception\TestCase\WPTestCase {

	use \AnsPress\Tests\Testcases\Common;

	public function setUp() {
		// before
		parent::setUp();
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	/**
	 * @covers AnsPress\Addons\Email::ap_after_new_question
	 */
	public function testAPAfterNewQuestion() {

		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_after_new_question', [ AnsPress\Addons\Email::init(), 'ap_after_new_question' ] ) );
		ap_opt( 'email_admin_emails', 'admin@xsdsdsd.local, admin2@aptext.local, admin@site.com' );
		codecept_debug( ap_opt( 'email_admin_emails' ) );

		$this->setRole( 'subscriber' );
		reset_phpmailer_instance();
		// Check if question created without author set current user as subscriber.
		$id = $this->insert_question( '', '', get_current_user_id() );

		// Run action so that ap_after_new_question hook can trigger.
		// do_action( 'ap_processed_new_question', $id, get_post( $id ) );
		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertEquals( 'admin@xsdsdsd.local', $mailer->get_recipient( 'to' )->address );
	}

	/**
	 * @covers AnsPress\Addons\Email::ap_after_new_answer
	 */
	private function testApAfterNewAnswer() {
		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ AnsPress\Addons\Email::init(), 'ap_after_new_answer' ] ) );
		$this->setRole( 'subscriber' );
		$q_user = get_current_user_id();

		ap_opt( 'email_admin_emails', 'admin@xsdsdsd.local' );

		// Check if question created without author set current user as subscriber.
		$question_id = $this->insert_question( '', '', $q_user );

		// Add a subscriber.
		$subs_user = wp_create_user( 'q_subscriber', 'q_ubscriber', 'q_subscriber@local.com' );
		ap_new_subscriber( $subs_user, 'question', $question_id );

		$this->setRole( 'subscriber' );
		reset_phpmailer_instance();
		$answer_id = $this->factory->post->create(
			array(
				'post_type'   => 'answer',
				'post_status' => 'publish',
				'post_parent' => $question_id,
				'post_author' => get_current_user_id(),
			)
		);

		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertEquals( get_user_by( 'id', $subs_user )->user_email, $mailer->get_recipient( 'bcc', 0, 1 )->address );
		$this->assertEquals( get_user_by( 'id', $subs_user )->user_email, $mailer->get_recipient( 'bcc' )->address );
		$this->assertEquals( 'admin@xsdsdsd.local', $mailer->get_recipient( 'to' )->address );
	}

}
