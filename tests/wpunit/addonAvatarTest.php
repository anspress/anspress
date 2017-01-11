<?php
/**
 * @group addons
 */
class AddonAvatarTest extends AnsPress_Tests
{
	

	private $upload_dir;
	
	public function setUp() {
		// before
		parent::setUp();

		$upload_dir = wp_upload_dir();
		$this->upload_dir = $upload_dir['basedir'] . '/ap_avatars';
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	/**
	 * Test to check if `pre_get_avatar_data` have `get_avatar` hook.
	 */
	public function test_pre_get_avatar_data_filter() {
		$this->assertNotFalse( has_filter( 'pre_get_avatar_data', [ 'AnsPress_Avatar_Hook', 'get_avatar' ] ) );
	}

	/**
	 * Checks avatar generation for comment object.
	 *
	 * @test
	 * @covers AnsPress_Avatar
	 * @uses   AnsPress_Avatar
	 */
	function test_comment_avatar() {

		// Generate avatar by comment object.
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

		$comment = get_comment( $comment_ID );

		$avatar = new AnsPress_Avatar( $comment );
		$avatar->generate();
		$this->assertFalse( ! $avatar->avatar_exists(), 'Unable to generate avatar using comment object' );

		unlink( $this->upload_dir . '/' . $avatar->filename . '.svg' );

		$this->assertFalse( $avatar->avatar_exists(), 'Unable to remove avatar' );
	}

	/**
	 * Checks avatar generation for user object.
	 *
	 * @test
	 * @covers AnsPress_Avatar
	 * @uses   AnsPress_Avatar
	 */
	function test_user_object_avatar() {
		// Generate avatar by comment object.
		$this->_setRole( 'ap_participant' );

		$user = get_user_by( 'id', get_current_user_id() );
		$avatar = new AnsPress_Avatar( $user );
		$avatar->generate();
		$this->assertFalse( ! $avatar->avatar_exists(), 'Unable to generate avatar using user object' );

		unlink( $this->upload_dir . '/' . $avatar->filename . '.svg' );

		$this->assertFalse( $avatar->avatar_exists(), 'Unable to remove avatar' );
	}

	/**
	 * Checks avatar generation for user id.
	 *
	 * @test
	 * @covers AnsPress_Avatar
	 * @uses   AnsPress_Avatar
	 */
	function test_user_id_avatar() {
		// Generate avatar by comment object.
		$this->_setRole( 'ap_participant' );

		$user_id = get_current_user_id();
		$avatar = new AnsPress_Avatar( $user_id );
		$avatar->generate();
		$this->assertFalse( ! $avatar->avatar_exists(), 'Unable to generate avatar using comment object' );

		unlink( $this->upload_dir . '/' . $avatar->filename . '.svg' );

		$this->assertFalse( $avatar->avatar_exists(), 'Unable to remove avatar' );
	}

	/**
	 * Checks avatar generation for anonymous.
	 *
	 * @test
	 * @covers AnsPress_Avatar
	 * @uses   AnsPress_Avatar
	 */
	function test_anonymous_avatar() {
		$user_id = get_current_user_id();
		$avatar = new AnsPress_Avatar( 'JohnDoe' );
		$avatar->generate();
		$this->assertFalse( ! $avatar->avatar_exists(), 'Unable to generate avatar for anonymous user' );

		unlink( $this->upload_dir . '/' . $avatar->filename . '.svg' );

		$this->assertFalse( $avatar->avatar_exists(), 'Unable to remove avatar' );
	}
}
