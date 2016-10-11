<?php

class ApajaxTest extends \Codeception\TestCase\WPAjaxTestCase
{
	public $current_post;
	public function setUp() {

		// before
		parent::setUp();

		$this->current_post = $this->factory->post->create( array( 'post_title' => 'Comment form loading', 'post_type' => 'question', 'post_status' => 'publish', 'post_content' => 'Donec nec nunc purus' ) );
	}

	public function tearDown() {

		// your tear down methods here
		// then
		parent::tearDown();
	}

	public function _set_post_data( $query ) {
		$args = wp_parse_args( $query );
		$_POST[ 'action' ] = 'ap_ajax';
		foreach ( $args as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	public function ap_ajax_success( $key = false, $return_json = false ) {
		preg_match( '#<div[^>]*>(.*?)</div>#', $this->_last_response, $match );

		if ( ! isset( $match[1] ) ) {
			return false;
		}

		$res = json_decode( $match[1] );

		if ( false !== $return_json ) {
			return $res;
		}

		if ( false !== $key ) {
			$this->assertObjectHasAttribute( $key, $res );
			if ( ! isset($res->$key ) ) {
				return false;
			}
			return $res->$key;
		}
	}

	public function triggerAjaxCapture() {
		try {
			$this->_handleAjax( 'ap_ajax' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->_last_response = $e->getMessage();
			codecept_debug( $e->getMessage() );
		}
	}

	public function test_vote_as_administrator() {
		// Become an administrator
		$this->_setRole( 'administrator' );
		$nonce = wp_create_nonce( 'vote_'.$this->current_post );

		$this->_set_post_data( 'ap_ajax_action=vote&type=up&post_id='.$this->current_post.'&__nonce='.$nonce.'' );
		add_action( 'ap_ajax_vote', array( 'AnsPress_Vote', 'vote' ) );
		$this->triggerAjaxCapture();

		// Ensure we found the right match
		$this->assertTrue( 'Thank you for voting.' == $this->ap_ajax_success( 'message' ) );
		$this->assertTrue( '1' == $this->ap_ajax_success( 'count' ) );
	}

	public function test_vote_as_subscriber( ) {
		$this->_setRole( 'subscriber' );
		$nonce = wp_create_nonce( 'vote_'.$this->current_post );
		$this->_set_post_data( 'ap_ajax_action=vote&type=up&post_id='.$this->current_post.'&__nonce='.$nonce.'' );
		add_action( 'ap_ajax_vote', array( 'AnsPress_Vote', 'vote' ) );
		$this->triggerAjaxCapture();
		$this->assertTrue( 'Thank you for voting.' == $this->ap_ajax_success( 'message' ) );
		$this->assertTrue( '1' == $this->ap_ajax_success( 'count' ) );
	}

	public function test_down_vote_as_subscriber( ) {
		$this->_setRole( 'ap_participant' );
		$nonce = wp_create_nonce( 'vote_'.$this->current_post );
		$this->_set_post_data( 'ap_ajax_action=vote&type=down&post_id='.$this->current_post.'&__nonce='.$nonce.'' );
		add_action( 'ap_ajax_vote', array( 'AnsPress_Vote', 'vote' ) );
		$this->triggerAjaxCapture();
		$this->assertTrue( 'Thank you for voting.' == $this->ap_ajax_success( 'message' ) );
		$this->assertTrue( '-1' == $this->ap_ajax_success( 'count' ) );
	}

	public function test_undo_vote_as_subscriber( ) {
		$this->_setRole( 'ap_participant' );
		$post = ap_get_post( $this->current_post );
		$counts = ap_add_post_vote( $this->current_post, $post->post_author , 'vote_up' );
		$nonce = wp_create_nonce( 'vote_'.$this->current_post );
		$this->_set_post_data( 'ap_ajax_action=vote&type=up&post_id='.$this->current_post.'&__nonce='.$nonce.'' );
		add_action( 'ap_ajax_vote', array( 'AnsPress_Vote', 'vote' ) );
		$this->triggerAjaxCapture();
		$this->assertTrue( 'Your vote has been removed.' == $this->ap_ajax_success( 'message' ) );
		$this->assertTrue( '1' == $this->ap_ajax_success( 'count' ) );
	}

	public function test_subscriber_vote_without_undo( ) {
		$this->_setRole( 'ap_participant' );
		$post = ap_get_post($this->current_post );
		$counts = ap_add_post_vote( $this->current_post, $post->post_author, 'vote_up' );
		$nonce = wp_create_nonce( 'vote_'.$this->current_post );
		$this->_set_post_data( 'ap_ajax_action=vote&type=down&post_id='.$this->current_post.'&__nonce='.$nonce.'' );
		add_action( 'ap_ajax_vote', array( 'AnsPress_Vote', 'vote' ) );
		$this->triggerAjaxCapture();
		$this->assertTrue( 'Undo your vote first.' == $this->ap_ajax_success( 'message' ) );
	}

	public function test_suggest_similar_questions( ) {
		// Become an administrator
		$this->_setRole( 'administrator' );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Very unique question', 'post_type' => 'question', 'post_status' => 'publish', 'post_content' => 'Very unique questionVery unique questionVery unique questionVery unique question' ) );

		$nonce = wp_create_nonce( 'ap_ajax_nonce' );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=Very+unique&__nonce='.$nonce.'' );
		add_action( 'ap_ajax_suggest_similar_questions', array( 'AnsPress_Ajax', 'suggest_similar_questions' ) );
		$this->triggerAjaxCapture();
		$json = $this->ap_ajax_success(false, true );
		$this->assertContains( 'Very unique question', strip_tags($json->html ) );
	}

	public function test_load_comment( ) {
		$this->_setRole( 'ap_participant' );
		$this->factory->comment->create_post_comments($this->current_post, 10 );
		$nonce = wp_create_nonce( 'comment_form_nonce' );
		$this->_set_post_data( 'ap_ajax_action=load_comments&args[]='.$this->current_post.'&__nonce='.$nonce.'' );
		add_action( 'ap_ajax_load_comments', array( 'AnsPress_Comment_Hooks', 'load_comments' ) );
		$this->triggerAjaxCapture();
		$response = $this->ap_ajax_success( false, true );
		// codecept_debug($response );
		$this->assertTrue( 'load_comment_form' == $response->action );
		$this->assertObjectHasAttribute( 'apData', $response );
		$this->assertObjectHasAttribute( 'template', $response );

		$this->assertObjectHasAttribute( 'current_user_avatar', $response->apData );
		$this->assertObjectHasAttribute( 'load_form', $response->apData );
		$this->assertObjectHasAttribute( 'load_form', $response->apData );

		$this->assertObjectHasAttribute( 'form', $response->apData );
		$this->assertObjectHasAttribute( 'key', $response->apData->form );
		$this->assertObjectHasAttribute( 'nonce', $response->apData->form );
		$this->assertObjectHasAttribute( 'post_id', $response->apData->form );

		$this->assertObjectHasAttribute( 'comments', $response->apData );

		$this->assertArrayHasKey( '0', $response->apData->comments );

		$atts = [ 'actions', 'approved', 'avatar', 'class', 'content', 'id', 'iso_date', 'time', 'user_link', 'user_name' ];
		foreach ( $response->apData->comments[0] as $k => $val ) {
			$this->assertTrue( in_array($k, $atts ) );
		}
	}

	function test_delete_comment() {
		$this->_setRole( 'ap_participant' );
		$comment_ID = $this->factory->comment->create(array(
		    'comment_post_ID' => $this->current_post,
		    'comment_content' => 'Aliquam at lectus felis, vel lacinia arcu',
		    'comment_type' => 'anspress',
		    'comment_parent' => 0,
		    'user_id' => get_current_user_id(),
		    'comment_author_IP' => '127.0.0.1',
		    'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
		    'comment_approved' => 1,
		));
		$nonce = wp_create_nonce( 'delete_comment' );
		$this->_set_post_data( 'ap_ajax_action=delete_comment&__nonce='.$nonce.'&comment_ID='.$comment_ID );
		add_action( 'ap_ajax_delete_comment', array( 'AnsPress_Comment_Hooks', 'delete_comment' ) );
		$this->triggerAjaxCapture();
		$response = $this->ap_ajax_success( false, true );
		// codecept_debug($response);
		$this->assertObjectHasAttribute('action', $response );
		$this->assertObjectHasAttribute('comment_ID', $response );
		$this->assertObjectHasAttribute('message', $response );
		$this->assertObjectHasAttribute('do', $response );
		$this->assertObjectHasAttribute('key', $response );
		$this->assertObjectHasAttribute('remove_if_exists', $response->do );
		$this->assertObjectHasAttribute('apData', $response );
		$this->assertObjectHasAttribute('current_user_avatar', $response->apData );
		$this->assertObjectHasAttribute('load_form', $response->apData );
		$this->assertObjectHasAttribute('form', $response->apData );
		$this->assertObjectHasAttribute( 'key', $response->apData->form );
		$this->assertObjectHasAttribute( 'nonce', $response->apData->form );
		$this->assertObjectHasAttribute( 'post_id', $response->apData->form );
	}

	function test_edit_comment_form() {
		$this->_setRole( 'ap_participant' );
		$comment_ID = $this->factory->comment->create(array(
		    'comment_post_ID' => $this->current_post,
		    'comment_content' => 'Aliquam at lectus felis, vel lacinia arcu',
		    'comment_type' => 'anspress',
		    'comment_parent' => 0,
		    'user_id' => get_current_user_id(),
		    'comment_author_IP' => '127.0.0.1',
		    'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
		    'comment_approved' => 1,
		));

		$nonce = wp_create_nonce( 'comment_form_nonce' );
		$this->_set_post_data( 'ap_ajax_action=edit_comment_form&__nonce='.$nonce.'&comment_ID='.$comment_ID );
		add_action( 'ap_ajax_edit_comment_form', array( 'AnsPress_Comment_Hooks', 'edit_comment_form' ) );
		$this->triggerAjaxCapture();
		$response = $this->ap_ajax_success( false, true );
		// codecept_debug($response);
		$this->assertObjectHasAttribute('action', $response );
		$this->assertObjectHasAttribute('template', $response );
		$this->assertObjectHasAttribute('key', $response );
		$this->assertObjectHasAttribute('apTemplate', $response );
		$this->assertObjectHasAttribute('apData', $response );
		$this->assertObjectHasAttribute('current_user_avatar', $response->apData );
		$this->assertObjectHasAttribute('load_form', $response->apData );
		$this->assertObjectHasAttribute('form', $response->apData );
		$this->assertObjectHasAttribute('nonce', $response->apData->form );
		$this->assertObjectHasAttribute('post_id', $response->apData->form );
		$this->assertObjectHasAttribute('key', $response->apData->form );
		$this->assertObjectHasAttribute('comment_ID', $response->apData->form );
		$this->assertObjectHasAttribute('content', $response->apData->form );
	}

	function test_submit_comment() {
		$this->_setRole( 'ap_participant' );

		$nonce = wp_create_nonce( $this->current_post . '_comment' );
		$content = 'Praesent%20lacus%20nisi%2C%20hendrerit%20ac%20dignissim%20a%2C%20accumsan%20at%20purus.';
		$this->_set_post_data( 'ap_ajax_action=submit_comment&__nonce='.$nonce.'&content='.$content.'&post_id='.$this->current_post );
		add_action( 'ap_ajax_submit_comment', array( 'AnsPress_Comment_Hooks', 'submit_comment' ) );
		$this->triggerAjaxCapture();
		$response = $this->ap_ajax_success( false, true );
		//codecept_debug($response );

		$this->assertObjectHasAttribute('action', $response);
		$this->assertObjectHasAttribute('comment_ID', $response);
		$this->assertObjectHasAttribute('comment_post_ID', $response);
		$this->assertObjectHasAttribute('comment_content', $response);
		$this->assertObjectHasAttribute('message', $response);
		$this->assertObjectHasAttribute('view', $response);
		$this->assertObjectHasAttribute('key', $response);
		$this->assertObjectHasAttribute('apData', $response);
		$this->assertObjectHasAttribute('current_user_avatar', $response->apData);
		$this->assertObjectHasAttribute('load_form', $response->apData);
		$this->assertObjectHasAttribute('form', $response->apData);
		$this->assertObjectHasAttribute('comments', $response->apData);
		$this->assertObjectHasAttribute('nonce', $response->apData->form);
		$this->assertObjectHasAttribute('post_id', $response->apData->form);
		$this->assertObjectHasAttribute('key', $response->apData->form);
	}

	function test_approve_comment() {
		$this->_setRole( 'administrator' );

		$nonce = wp_create_nonce( 'approve_comment_' . $this->current_post );
		$comment_ID = $this->factory->comment->create(array(
		    'comment_post_ID' => $this->current_post,
		    'comment_content' => 'Aliquam auctor diam ut urna lacinia faucibus. Vivamus et urna magna.',
		    'comment_type' => 'anspress',
		    'comment_parent' => 0,
		    'user_id' => get_current_user_id(),
		    'comment_author_IP' => '127.0.0.1',
		    'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
		    'comment_approved' => 0,
		));

		$this->_set_post_data( 'ap_ajax_action=approve_comment&__nonce='.$nonce.'&args[]='.$comment_ID );
		add_action( 'ap_ajax_approve_comment', array( 'AnsPress_Comment_Hooks', 'approve_comment' ) );
		$this->triggerAjaxCapture();
		$response = $this->ap_ajax_success( false, true );
		codecept_debug($response );
	}
}
