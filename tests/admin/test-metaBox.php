<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAPQuestionMetaBox extends TestCase {

	use Testcases\Common;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', 'add_meta_box' ) );
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', 'answers_meta_box_content' ) );
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', 'question_meta_box_content' ) );
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', 'flag_meta_box' ) );
	}

	/**
	 * @covers AP_Question_Meta_Box::__construct
	 */
	public function testConstruct() {
		$meta_box = new \AP_Question_Meta_Box();
		$this->assertEquals( 10, has_action( 'add_meta_boxes', [ $meta_box, 'add_meta_box' ] ) );
	}

	/**
	 * @covers AP_Question_Meta_Box::add_meta_box
	 */
	public function testadd_meta_box() {
		// Metabox for question post type has ap_get_answers_count which returns falls
		// if there is no question id in url so we test via visiting the question page.
		$id = $this->insert_answer();
		$this->go_to( '?post_type=question&p=' . $id->q );
		$meta_box = new \AP_Question_Meta_Box();

		// Test for question post type.
		$GLOBALS['wp_meta_boxes']['question']['normal']['high'] = [];
		$GLOBALS['wp_meta_boxes']['question']['side']['high'] = [];
		$GLOBALS['wp_meta_boxes']['answer']['side']['high'] = [];
		$meta_box->add_meta_box( 'question' );

		// Test on Answers meta box.
		$this->assertArrayHasKey( 'ap_answers_meta_box', $GLOBALS['wp_meta_boxes']['question']['normal']['high'] );
		$this->assertEquals( 'ap_answers_meta_box', $GLOBALS['wp_meta_boxes']['question']['normal']['high']['ap_answers_meta_box']['id'] );
		$this->assertEquals( ' 1 Answers', $GLOBALS['wp_meta_boxes']['question']['normal']['high']['ap_answers_meta_box']['title'] );
		$this->assertEquals( [ $meta_box, 'answers_meta_box_content' ], $GLOBALS['wp_meta_boxes']['question']['normal']['high']['ap_answers_meta_box']['callback'] );

		// Test on Question meta box.
		$this->assertArrayHasKey( 'ap_question_meta_box', $GLOBALS['wp_meta_boxes']['question']['side']['high'] );
		$this->assertEquals( 'ap_question_meta_box', $GLOBALS['wp_meta_boxes']['question']['side']['high']['ap_question_meta_box']['id'] );
		$this->assertEquals( 'Question', $GLOBALS['wp_meta_boxes']['question']['side']['high']['ap_question_meta_box']['title'] );
		$this->assertEquals( [ $meta_box, 'question_meta_box_content' ], $GLOBALS['wp_meta_boxes']['question']['side']['high']['ap_question_meta_box']['callback'] );

		// Test for answer post type.
		$GLOBALS['wp_meta_boxes']['question']['normal']['high'] = [];
		$GLOBALS['wp_meta_boxes']['question']['side']['high'] = [];
		$GLOBALS['wp_meta_boxes']['answer']['side']['high'] = [];
		$meta_box->add_meta_box( 'answer' );

		// Test on Answers meta box.
		$this->assertArrayNotHasKey( 'ap_answers_meta_box', $GLOBALS['wp_meta_boxes']['question']['normal']['high'] );

		// Test on Question meta box.
		$this->assertArrayHasKey( 'ap_question_meta_box', $GLOBALS['wp_meta_boxes']['answer']['side']['high'] );
		$this->assertEquals( 'ap_question_meta_box', $GLOBALS['wp_meta_boxes']['answer']['side']['high']['ap_question_meta_box']['id'] );
		$this->assertEquals( 'Question', $GLOBALS['wp_meta_boxes']['answer']['side']['high']['ap_question_meta_box']['title'] );
		$this->assertEquals( [ $meta_box, 'question_meta_box_content' ], $GLOBALS['wp_meta_boxes']['answer']['side']['high']['ap_question_meta_box']['callback'] );
	}

	/**
	 * @covers AP_Question_Meta_Box::flag_meta_box
	 */
	public function testFlagMetaBox() {
		$meta_box = new \AP_Question_Meta_Box();
		$id = $this->insert_answer();

		// Store the result of the method in a variable.
		ob_start();
		$meta_box->flag_meta_box( get_post( $id->q ) );
		$result = ob_get_clean();

		// Test begins.
		// Test 1.
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id->q ),
			'post_id'        => $id->q,
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">0</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );
		$this->assertStringContainsString( '<script type="text/javascript">', $result );
		$this->assertStringContainsString( '$(\'#ap-clear-flag\')', $result );
		$this->assertStringContainsString( '$.ajax', $result );

		// Test 2.
		ap_add_flag( $id->q );
		$user_id = $this->factory()->user->create();
		ap_add_flag( $id->q, $user_id );
		ap_update_flags_count( $id->q );
		ob_start();
		$meta_box->flag_meta_box( get_post( $id->q ) );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">2</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );
		$this->assertStringContainsString( '<script type="text/javascript">', $result );
		$this->assertStringContainsString( '$(\'#ap-clear-flag\')', $result );
		$this->assertStringContainsString( '$.ajax', $result );
	}

	/**
	 * @covers AP_Question_Meta_Box::answers_meta_box_content
	 */
	public function testAnswersMetaBoxContent() {
		$meta_box = new \AP_Question_Meta_Box();
		$id = $this->insert_question();

		// Store the result of the method in a variable.
		global $post;
		$post = get_post( $id );
		ob_start();
		$meta_box->answers_meta_box_content();
		$result = ob_get_clean();

		// Test begins.
		$this->assertStringContainsString( '<div id="answers-list" data-questionid="' . $id . '">', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id ) ) . '" class="button add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<script type="text/html" id="ap-answer-template">', $result );
		$this->assertStringContainsString( '<a href="#" class="ap-ansm-avatar">{{{avatar}}}</a>', $result );
		$this->assertStringContainsString( '<span class="post-status">{{status}}</span>', $result );
		$this->assertStringContainsString( '{{{activity}}}', $result );
		$this->assertStringContainsString( '<div class="ap-ansm-content">{{{content}}}</div>', $result );
		$this->assertStringContainsString( '<span><a href="{{{editLink}}}">Edit</a></span>', $result );
		$this->assertStringContainsString( '<span class="delete vim-d vim-destructive"> | <a href="{{{trashLink}}}">Trash</a></span>', $result );
	}

	/**
	 * @covers AP_Question_Meta_Box::question_meta_box_content
	 */
	public function testQuestionMetaBoxContent() {
		$meta_box = new \AP_Question_Meta_Box();
		$this->setRole( 'administrator' );
		$nonce = wp_create_nonce( 'admin_vote' );
		global $post;

		// Test begins.
		// Test 1.
		$id = $this->insert_question();
		$post = get_post( $id );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringContainsString( '<strong>0</strong> Answers', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>0</strong> Votes', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id ),
			'post_id'        => $id,
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">0</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 2.
		ap_add_flag( $id );
		$user_id = $this->factory()->user->create();
		ap_add_flag( $id, $user_id );
		ap_update_flags_count( $id );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringContainsString( '<strong>0</strong> Answers', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>0</strong> Votes', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id ),
			'post_id'        => $id,
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">2</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 3.
		$id = $this->insert_answer();
		$post = get_post( $id->q );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringContainsString( '<strong>1</strong> Answer', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id->q ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>0</strong> Votes', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id->q . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id->q . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id->q ),
			'post_id'        => $id->q,
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">0</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 4.
		ap_add_flag( $id->q );
		$user_id = $this->factory()->user->create();
		ap_add_flag( $id->q, $user_id );
		ap_update_flags_count( $id->q );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringContainsString( '<strong>1</strong> Answer', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id->q ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>0</strong> Votes', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id->q . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id->q . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id->q ),
			'post_id'        => $id->q,
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">2</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 5.
		$id = $this->insert_answer();
		$post = get_post( $id->a );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringNotContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringNotContainsString( '<strong>1</strong> Answer', $result );
		$this->assertStringNotContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id->q ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>0</strong> Votes', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id->a . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id->a . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id->a ),
			'post_id'        => $id->a,
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">0</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 6.
		ap_add_flag( $id->a );
		$user_id = $this->factory()->user->create();
		ap_add_flag( $id->a, $user_id );
		ap_update_flags_count( $id->a );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringNotContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringNotContainsString( '<strong>1</strong> Answer', $result );
		$this->assertStringNotContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id->q ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>0</strong> Votes', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id->a . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id->a . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id->a ),
			'post_id'        => $id->a,
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">2</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 7.
		$id = $this->insert_answers( [], [], 3 );
		$post = get_post( $id['question'] );
		ap_add_post_vote( $id['question'] );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringContainsString( '<strong>3</strong> Answers', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id['question'] ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>1</strong> Vote', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id['question'] . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id['question'] . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id['question'] ),
			'post_id'        => $id['question'],
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">0</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 8.
		ap_add_flag( $id['question'] );
		$user_id = $this->factory()->user->create();
		ap_add_flag( $id['question'], $user_id );
		ap_update_flags_count( $id['question'] );
		ap_add_post_vote( $id['question'], $user_id );
		$post = get_post( $id['question'] );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringContainsString( '<strong>3</strong> Answers', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id['question'] ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>2</strong> Votes', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id['question'] . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id['question'] . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id['question'] ),
			'post_id'        => $id['question'],
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">2</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 9.
		$id = $this->insert_answers( [], [], 3 );
		$post = get_post( $id['answers'][2] );
		ap_add_post_vote( $id['answers'][2] );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringNotContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringNotContainsString( '<strong>3</strong> Answers', $result );
		$this->assertStringNotContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id['question'] ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>1</strong> Vote', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id['answers'][2] . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id['answers'][2] . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id['answers'][2] ),
			'post_id'        => $id['answers'][2],
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">0</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );

		// Test 10.
		ap_add_flag( $id['answers'][2] );
		$user_id = $this->factory()->user->create();
		ap_add_flag( $id['answers'][2], $user_id );
		ap_update_flags_count( $id['answers'][2] );
		ap_add_post_vote( $id['answers'][2], $user_id );
		$post = get_post( $id['answers'][2] );
		ob_start();
		$meta_box->question_meta_box_content( $post );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul class="ap-meta-list">', $result );
		$this->assertStringNotContainsString( '<i class="apicon-answer"></i>', $result );
		$this->assertStringNotContainsString( '<strong>3</strong> Answers', $result );
		$this->assertStringNotContainsString( '<a href="' . esc_url( admin_url( 'post-new.php?post_type=answer&post_parent=' . $id['question'] ) ) . '" class="add-answer">Add an answer</a>', $result );
		$this->assertStringContainsString( '<i class="apicon-thumb-up"></i>', $result );
		$this->assertStringContainsString( '<strong>2</strong> Votes', $result );
		$this->assertStringContainsString( '<a id="ap-vote-down" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id['answers'][2] . '::down" data-cb="replaceText">', $result );
		$this->assertStringContainsString( '<a id="ap-vote-up" href="#" class="vote button button-small ap-ajax-btn" data-query="ap_admin_vote::' . $nonce . '::' . $id['answers'][2] . '::up" data-cb="replaceText">', $result );
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id['answers'][2] ),
			'post_id'        => $id['answers'][2],
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">2</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );
	}
}
