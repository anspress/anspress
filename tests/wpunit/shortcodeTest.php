<?php

class shortcodeTest extends \Codeception\TestCase\WPTestCase
{
	use AnsPress\Tests\Testcases\Common;
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
	 * @covers AnsPress_BasePage_Shortcode::get_instance
	 */
	public function testGetInstance() {
		$this->assertClassHasStaticAttribute( 'instance', 'AnsPress_BasePage_Shortcode' );
		$this->assertTrue( shortcode_exists( 'anspress' ), 'anspress shortcode not registered.' );
	}

	/**
	 * @covers AnsPress_BasePage_Shortcode::anspress_sc
	 */
	public function testAnspressSc() {
		$this->go_to( home_url() );
		global $ap_shortcode_loaded;
		$this->assertNotEquals( true, $ap_shortcode_loaded );
		// Make sure shortcode does not echo anything.
		ob_start( );
		$content = do_shortcode('[anspress]');
		$output = ob_get_clean();
		$this->assertEquals( '', $output );
		$this->assertFalse( empty( $content ) );
	}

	/**
	 * @covers AnsPress_Common_Pages::base_page
	 */
	public function testBasePage() {
		$this->go_to( home_url() );

		$question_id = $this->factory->post->create( array( 'post_title' => 'Sample question', 'post_type' => 'question', 'post_status' => 'publish', 'post_content' => 'test content' ) );

		add_filter( 'the_content', [ $this, 'the_content' ] );
		$content = do_shortcode('[anspress page="base"]');
		remove_filter( 'the_content', [ $this, 'the_content' ] );

		$this->assertContains( 'id="anspress"', $content );
		$this->assertContains( 'id="ap-lists"', $content );
		$this->assertContains( 'class="ap-list-head', $content );
		$this->assertContains( 'Sample question', $content );
		$this->assertNotContains( 'AnsPress shortcode cannot be nested.', $content );
	}

	public function the_content( $content ) {
		$content = '[anspress]';
		return $content;
	}

	/**
	 * @covers AnsPress_Common_Pages::question_page
	 */
	public function testQuestionPage() {
		$question_id = $this->factory->post->create( array( 'post_title' => 'supersamplequestion1', 'post_type' => 'question', 'post_status' => 'publish', 'post_content' => 'Cras tempor eleifend essds98d9s8d9s' ) );

		$this->go_to_question( $question_id );
		$this->assertTrue(is_single());
		$this->assertTrue(is_singular('question'));

		add_filter( 'the_content', [ $this, 'the_content' ] );
		$content = do_shortcode('[anspress]');
		remove_filter( 'the_content', [ $this, 'the_content' ] );

		$this->assertContains( 'id="anspress"', $content );
		$this->assertContains( 'AnsPress shortcode cannot be nested.', $content, 'AnsPress shortcode is nesting somewhere' );
		$content = do_shortcode('[anspress]');
		$this->assertNotContains( 'AnsPress shortcode cannot be nested.', $content );
		$this->assertContains( 'id="ap-single"', $content );
		$this->assertContains( 'class="ap-question-meta', $content );
		$this->assertContains( 'class="ap-question-meta', $content );
		$this->assertContains( 'apid="' . $question_id . '"', $content );
		$this->assertContains( 'Cras tempor eleifend essds98d9s8d9s', $content );

		$this->assertEquals( 'Cras tempor eleifend essds98d9s8d9s', get_post()->post_content );
		$this->assertEquals( 'supersamplequestion1', get_post()->post_title );
		$this->assertEquals( $question_id, get_the_ID() );

		global $wp_query;
		$this->assertEquals( 1, $wp_query->found_posts );

		$i = 0;
		while($wp_query->have_posts()) {
			$wp_query->the_post();
			$this->assertNotEquals( 1, $i );
			$loop_content = do_shortcode('[anspress page="base"]');

			$this->assertNotEquals( '[anspress]', $loop_content );
			$this->assertEquals( $question_id, get_the_ID() );
			$this->assertEquals( 'supersamplequestion1', get_the_title() );
			$i++;
		}
	}

	/**
	 * @covers AnsPress_Common_Pages::base_page
	 */
	public function testAskPage() {
		$question_id = $this->factory->post->create( array( 'post_title' => 'Sample question', 'post_type' => 'question', 'post_status' => 'publish', 'post_content' => 'test content' ) );

		add_filter( 'the_content', [ $this, 'the_content' ] );
		$content = do_shortcode('[anspress page="base"]');
		remove_filter( 'the_content', [ $this, 'the_content' ] );

		$this->assertContains( 'id="anspress"', $content );
		$this->assertContains( 'id="ap-lists"', $content );
		$this->assertContains( 'class="ap-list-head', $content );
		$this->assertContains( 'Sample question', $content );
		$this->assertNotContains( 'AnsPress shortcode cannot be nested.', $content );
	}

}