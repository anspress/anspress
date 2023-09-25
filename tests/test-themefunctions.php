<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestThemeFunctions extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	public function pageTitle() {
		return 'Question title';
	}

	public function askBtnLink() {
		return home_url( '/ask' );
	}

	public function tagPageSlug() {
		return 'imtag';
	}

	public function categoryPageSlug() {
		return 'imcategory';
	}

	public function questionPageSlug() {
		return 'imquestion';
	}

	/**
	 * @covers ::ap_page_title
	 */
	public function testAPPageTitle() {
		$this->assertEquals( '', ap_page_title() );

		// Filter apply test,
		add_filter( 'ap_page_title', array( $this, 'pageTitle' ) );
		$this->assertNotEquals( '', ap_page_title() );
		$this->assertEquals( 'Question title', ap_page_title() );

		// Filter remove test,
		remove_filter( 'ap_page_title', array( $this, 'pageTitle' ) );
		$this->assertNotEquals( 'Question title', ap_page_title() );
		$this->assertEquals( '', ap_page_title() );
	}

	/**
	 * @covers ::ap_post_status
	 */
	public function testAPPostStatus() {
		$id = $this->insert_question();
		$this->assertEquals( 'publish', ap_post_status( $id ) );

		// Check for private_post post status.
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_status'  => 'private_post',
				'post_type'    => 'question',
			)
		);
		$post = get_post( $id );
		$this->assertEquals( 'private_post', ap_post_status( $id ) );

		// Check for moderate post status.
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_status'  => 'moderate',
				'post_type'    => 'question',
			)
		);
		$post = get_post( $id );
		$this->assertEquals( 'moderate', ap_post_status( $id ) );
	}

	/**
	 * @covers ::is_private_post
	 */
	public function testPrivatePost() {
		$id = $this->insert_question();
		$this->assertFalse( is_private_post( $id ) );

		// Check for private_post post status.
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_status'  => 'private_post',
				'post_type'    => 'question',
			)
		);
		$post = get_post( $id );
		$this->assertTrue( is_private_post( $id ) );
	}

	/**
	 * @covers ::is_post_waiting_moderation
	 */
	public function testModeratePost() {
		$id = $this->insert_question();
		$this->assertFalse( is_post_waiting_moderation( $id ) );

		// Check for moderate post status.
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_status'  => 'moderate',
				'post_type'    => 'question',
			)
		);
		$post = get_post( $id );
		$this->assertTrue( is_post_waiting_moderation( $id ) );
	}

	/**
	 * @covers ::is_post_closed
	 */
	public function testIsPostClosed() {
		$id = $this->insert_question();
		$this->assertFalse( is_post_closed( $id ) );

		// Check for question open.
		ap_insert_qameta(
			$id,
			array(
				'selected_id'  => '',
				'last_updated' => current_time( 'mysql' ),
				'closed'       => 0,
			)
		);
		$this->assertFalse( is_post_closed( $id ) );

		// Check for question close.
		ap_insert_qameta(
			$id,
			array(
				'selected_id'  => '',
				'last_updated' => current_time( 'mysql' ),
				'closed'       => 1,
			)
		);
		$this->assertTrue( is_post_closed( $id ) );
	}

	/**
	 * @covers ::ap_have_parent_post
	 */
	public function testAPHaveParentPost() {
		$id = $this->insert_question();
		$this->assertFalse( ap_have_parent_post( $id ) );
		$child_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_parent'  => $id,
			)
		);
		$this->assertTrue( ap_have_parent_post( $child_id ) );
		$child_post_id = $this->factory->post->create(
			array(
				'post_parent'  => $id,
			)
		);
		$this->assertFalse( ap_have_parent_post( $child_post_id ) );
		$id = $this->insert_answer();
		$this->assertFalse( ap_have_parent_post( $id->a ) );
	}

	/**
	 * @covers ::ap_get_ask_btn
	 * @covers ::ap_ask_btn
	 */
	public function testAPAskBtn() {
		$link = ap_get_link_to( 'ask' );
		$this->assertSame( '<a class="ap-btn-ask" href="' . $link . '">Ask question</a>', ap_get_ask_btn() );
		ob_start();
		ap_ask_btn();
		$output = ob_get_clean();
		$this->assertSame( '<a class="ap-btn-ask" href="' . $link . '">Ask question</a>', $output );

		// Test for filter addition.
		add_filter( 'ap_ask_btn_link', array( $this, 'askBtnLink' ) );
		$this->assertSame( '<a class="ap-btn-ask" href="' . home_url( '/ask' ) . '">Ask question</a>', ap_get_ask_btn() );
		ob_start();
		ap_ask_btn();
		$output = ob_get_clean();
		$this->assertSame( '<a class="ap-btn-ask" href="' . home_url( '/ask' ) . '">Ask question</a>', $output );

		// Test after filter remove.
		remove_filter( 'ap_ask_btn_link', array( $this, 'askBtnLink' ) );
		$this->assertSame( '<a class="ap-btn-ask" href="' . $link . '">Ask question</a>', ap_get_ask_btn() );
		ob_start();
		ap_ask_btn();
		$output = ob_get_clean();
		$this->assertSame( '<a class="ap-btn-ask" href="' . $link . '">Ask question</a>', $output );
	}

	/**
	 * @covers ::ap_assets
	 * @covers ::ap_enqueue_scripts
	 */
	public function testApAssets() {
		// Required hook for testing style and script register/enqueue.
		ob_start();
		do_action( 'wp_enqueue_scripts' );
		ob_end_clean();

		// Test on the ap_assets function.
		// Test for register scripts.
		$this->assertTrue( wp_script_is( 'selectize', 'registered' ) );
		$this->assertTrue( wp_script_is( 'anspress-common', 'registered' ) );
		$this->assertTrue( wp_script_is( 'anspress-question', 'registered' ) );
		$this->assertTrue( wp_script_is( 'anspress-ask', 'registered' ) );
		$this->assertTrue( wp_script_is( 'anspress-list', 'registered' ) );
		$this->assertTrue( wp_script_is( 'anspress-notifications', 'registered' ) );
		$this->assertTrue( wp_script_is( 'anspress-theme', 'registered' ) );

		// Test for register style.
		$this->assertTrue( wp_style_is( 'anspress-fonts', 'registered' ) );
		$this->assertTrue( wp_style_is( 'anspress-main', 'registered' ) );
		$this->assertTrue( wp_style_is( 'anspress-rtl', 'registered' ) );

		// Test on the ap_enqueue_scripts function.
		$this->assertTrue( wp_script_is( 'anspress-theme' ) );
		$this->assertTrue( wp_style_is( 'anspress-main' ) );
	}

	/**
	 * @covers ::ap_get_page_slug
	 */
	public function testAPGetPageSlug() {
		// Default slug test.
		$this->assertEquals( 'tag', ap_get_page_slug( 'tag' ) );
		$this->assertEquals( 'category', ap_get_page_slug( 'category' ) );
		$this->assertEquals( 'question', ap_get_page_slug( 'question' ) );

		// Filter test for slug.
		add_filter( 'ap_page_slug_tag', [ $this, 'tagPageSlug' ], 11 );
		add_filter( 'ap_page_slug_category', [ $this, 'categoryPageSlug' ], 11 );
		add_filter( 'ap_page_slug_question', [ $this, 'questionPageSlug' ], 11 );
		$this->assertNotEquals( 'tag', ap_get_page_slug( 'tag' ) );
		$this->assertNotEquals( 'category', ap_get_page_slug( 'category' ) );
		$this->assertNotEquals( 'question', ap_get_page_slug( 'question' ) );
		$this->assertEquals( 'imtag', ap_get_page_slug( 'tag' ) );
		$this->assertEquals( 'imcategory', ap_get_page_slug( 'category' ) );
		$this->assertEquals( 'imquestion', ap_get_page_slug( 'question' ) );
		remove_filter( 'ap_page_slug_tag', [ $this, 'tagPageSlug' ], 11 );
		remove_filter( 'ap_page_slug_category', [ $this, 'categoryPageSlug' ], 11 );
		remove_filter( 'ap_page_slug_question', [ $this, 'questionPageSlug' ], 11 );
		$this->assertEquals( 'tag', ap_get_page_slug( 'tag' ) );
		$this->assertEquals( 'category', ap_get_page_slug( 'category' ) );
		$this->assertEquals( 'question', ap_get_page_slug( 'question' ) );
		$this->assertNotEquals( 'imtag', ap_get_page_slug( 'tag' ) );
		$this->assertNotEquals( 'imcategory', ap_get_page_slug( 'category' ) );
		$this->assertNotEquals( 'imquestion', ap_get_page_slug( 'question' ) );

		// Modified slug test.
		$slugs = [
			'tag'      => 't',
			'category' => 'cat',
			'question' => 'q',
		];
		foreach ( $slugs as $slug => $new_slug ) {
			ap_opt( $slug . '_page_slug', $new_slug );
		}
		$this->assertEquals( 't', ap_get_page_slug( 'tag' ) );
		$this->assertEquals( 'cat', ap_get_page_slug( 'category' ) );
		$this->assertEquals( 'q', ap_get_page_slug( 'question' ) );

		// Filter test for slug for modified option.
		add_filter( 'ap_page_slug_t', [ $this, 'tagPageSlug' ], 11 );
		add_filter( 'ap_page_slug_cat', [ $this, 'categoryPageSlug' ], 11 );
		add_filter( 'ap_page_slug_q', [ $this, 'questionPageSlug' ], 11 );
		$this->assertNotEquals( 't', ap_get_page_slug( 'tag' ) );
		$this->assertNotEquals( 'cat', ap_get_page_slug( 'category' ) );
		$this->assertNotEquals( 'q', ap_get_page_slug( 'question' ) );
		$this->assertEquals( 'imtag', ap_get_page_slug( 'tag' ) );
		$this->assertEquals( 'imcategory', ap_get_page_slug( 'category' ) );
		$this->assertEquals( 'imquestion', ap_get_page_slug( 'question' ) );
		remove_filter( 'ap_page_slug_t', [ $this, 'tagPageSlug' ], 11 );
		remove_filter( 'ap_page_slug_cat', [ $this, 'categoryPageSlug' ], 11 );
		remove_filter( 'ap_page_slug_q', [ $this, 'questionPageSlug' ], 11 );
		$this->assertEquals( 't', ap_get_page_slug( 'tag' ) );
		$this->assertEquals( 'cat', ap_get_page_slug( 'category' ) );
		$this->assertEquals( 'q', ap_get_page_slug( 'question' ) );
		$this->assertNotEquals( 'imtag', ap_get_page_slug( 'tag' ) );
		$this->assertNotEquals( 'imcategory', ap_get_page_slug( 'category' ) );
		$this->assertNotEquals( 'imquestion', ap_get_page_slug( 'question' ) );

		// Resetting to default test.
		$this->assertNotEquals( 'tag', ap_get_page_slug( 'tag' ) );
		$this->assertNotEquals( 'category', ap_get_page_slug( 'category' ) );
		$this->assertNotEquals( 'question', ap_get_page_slug( 'question' ) );
		$slugs = [
			'tag'      => 'tag',
			'category' => 'category',
			'question' => 'question',
		];
		foreach ( $slugs as $slug => $new_slug ) {
			ap_opt( $slug . '_page_slug', $new_slug );
		}
		$this->assertEquals( 'tag', ap_get_page_slug( 'tag' ) );
		$this->assertEquals( 'category', ap_get_page_slug( 'category' ) );
		$this->assertEquals( 'question', ap_get_page_slug( 'question' ) );
	}

}
