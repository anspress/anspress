<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAskForm extends TestCase {

	use Testcases\Common;

	/**
	 * @covers AP_Form_Hooks::question_form
	 */
	public function testQuestionForm() {
		$this->logout();

		ap_opt( 'allow_private_posts', true );
		unset( anspress()->forms['question'] );
		anspress()->form_exists( 'question' );
		$form = anspress()->forms['question'];

		$this->assertInstanceOf( 'AnsPress\Form', $form );
		$this->assertArrayHasKey( 'fields', $form->args );
		$this->assertEquals( 'Submit Question', $form->args['submit_label'] );
		$this->assertEquals( 'input', $form->args['fields']['post_title']['type'] );
		$this->assertEquals( 'Title', $form->args['fields']['post_title']['label'] );
		$this->assertEquals( 'Question in one sentence', $form->args['fields']['post_title']['desc'] );
		$this->assertEquals(
			array(
				'autocomplete'   => 'off',
				'placeholder'    => 'Question title',
				'data-action'    => 'suggest_similar_questions',
				'data-loadclass' => 'q-title',
			), $form->args['fields']['post_title']['attr']
		);
		$this->assertEquals( ap_opt( 'minimum_qtitle_length' ), $form->args['fields']['post_title']['min_length'] );
		$this->assertEquals( 100, $form->args['fields']['post_title']['max_length'] );
		$this->assertEquals( 'required,min_string_length,max_string_length,badwords', $form->args['fields']['post_title']['validate'] );
		$this->assertEquals( 2, $form->args['fields']['post_title']['order'] );
		$this->assertEquals( 'editor', $form->args['fields']['post_content']['type'] );
		$this->assertEquals( 'Description', $form->args['fields']['post_content']['label'] );
		$this->assertEquals( ap_opt( 'minimum_question_length' ), $form->args['fields']['post_content']['min_length'] );
		$this->assertEquals( 'required,min_string_length,badwords', $form->args['fields']['post_content']['validate'] );
		$this->assertEquals(
			array(
				'quicktags' => ap_opt( 'question_text_editor' ) ? true : false,
			), $form->args['fields']['post_content']['editor_args']
		);

		$this->assertEquals( 'Is private?', $form->args['fields']['is_private']['label'] );
		$this->assertEquals( 'Only visible to admin and moderator.', $form->args['fields']['is_private']['desc'] );
		$this->assertEquals( 'checkbox', $form->args['fields']['is_private']['type'] );

		$this->assertEquals( 'Your Name', $form->args['fields']['anonymous_name']['label'] );
		$this->assertEquals(
			array(
				'placeholder' => __( 'Enter your name to display', 'anspress-question-answer' ),
			), $form->args['fields']['anonymous_name']['attr']
		);
		$this->assertEquals( 20, $form->args['fields']['anonymous_name']['order'] );
		$this->assertEquals( 'max_string_length,badwords', $form->args['fields']['anonymous_name']['validate'] );
		$this->assertEquals( 64, $form->args['fields']['anonymous_name']['max_length'] );
		$this->assertEquals( 'input', $form->args['fields']['post_id']['type'] );
		$this->assertEquals( 'hidden', $form->args['fields']['post_id']['subtype'] );
		$this->assertEquals( 'absint', $form->args['fields']['post_id']['sanitize'] );

		// Try for logged in users.
		$this->setRole( 'subscriber' );

		unset( anspress()->forms['question'] );
		anspress()->form_exists( 'question' );
		$form = anspress()->forms['question'];

		$this->assertArrayNotHasKey( 'anonymous_name', $form->args['fields'] );
	}

	/**
	 * Test question fields while editing.
	 *
	 * @covers AP_Form_Hooks::question_form
	 */
	public function testQuestionFormEditing() {
		$this->logout();

		ap_opt( 'allow_private_posts', true );

		$id = $this->factory()->post->create(
			array(
				'post_title'   => 'Mauris a velit id neque dignissim congue',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
				'post_content' => 'Sed cursus, diam sit amet',
			)
		);

		ap_insert_qameta( $id, [ 'fields' => [ 'anonymous_name' => 'Rahul' ] ] );
		$_REQUEST['id'] = $id;

		unset( anspress()->forms['question'] );
		anspress()->form_exists( 'question' );
		$form = anspress()->forms['question'];

		$this->assertEquals( true, $form->editing );
		$this->assertEquals( $id, $form->editing_id );
		$this->assertEquals( 'Update Question', $form->args['submit_label'] );

		$question = ap_get_post( $id );
		$this->assertEquals( 0, $question->post_author );
		$this->assertEquals( $id, $form->args['fields']['post_id']['value'] );
		$this->assertEquals( 'Mauris a velit id neque dignissim congue', $form->args['fields']['post_title']['value'] );
		$this->assertEquals( 'Sed cursus, diam sit amet', $form->args['fields']['post_content']['value'] );
		$this->assertEquals( true, $form->args['fields']['is_private']['value'] );
		$this->assertEquals( 'Rahul', $form->args['fields']['anonymous_name']['value'] );
	}

	/**
	 * Tests ap_ask_form() function.
	 *
	 * @covers ::ap_ask_form
	 */
	public function testApAskForm() {
		$_REQUEST = [];
		$_POST    = [];

		ap_opt( 'post_question_per', 'have_cap' );
		$this->setRole( 'ap_banned' );
		$id = $this->factory()->post->create(
			array(
				'post_title'   => 'Suspendisse aliqua',
				'post_type'    => 'question',
				'post_content' => 'Sed cursus, diam sit amet',
				'post_author'  => get_current_user_id(),
			)
		);

		ob_start();
		ap_ask_form();
		$form_html = ob_get_clean();

		$this->assertEquals( '<p>You do not have permission to ask a question.</p>', $form_html );

		ap_opt( 'post_question_per', 'anyone' );
		$_REQUEST['id'] = $id;
		ob_start();
		ap_ask_form();
		$form_html = ob_get_clean();
		$this->assertEquals( '<p>You cannot edit this question.</p>', $form_html );

		$this->setRole( 'administrator' );
		ob_start();
		ap_ask_form();
		$form_html = ob_get_clean();
		$this->assertStringContainsString( 'name="form_question[post_title]"', $form_html );
		$this->assertStringContainsString( 'name="form_question[post_content]"', $form_html );
	}
}
