<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestThemeFunctions extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		register_taxonomy( 'question_category', array( 'question' ) );
		register_taxonomy( 'question_tag', array( 'question' ) );
	}

	public function tear_down() {
		unregister_taxonomy( 'question_category' );
		unregister_taxonomy( 'question_tag' );
		parent::tear_down();
	}

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
		$id = $this->factory()->post->create(
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
		$id = $this->factory()->post->create(
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
		$id = $this->factory()->post->create(
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
		$id = $this->factory()->post->create(
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
		$child_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_parent'  => $id,
			)
		);
		$this->assertTrue( ap_have_parent_post( $child_id ) );
		$child_post_id = $this->factory()->post->create(
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
		$this->assertSame( '<a class="ap-btn-ask" href="' . esc_url( $link ) . '">Ask question</a>', ap_get_ask_btn() );
		ob_start();
		ap_ask_btn();
		$output = ob_get_clean();
		$this->assertSame( '<a class="ap-btn-ask" href="' . esc_url( $link ) . '">Ask question</a>', $output );

		// Test for filter addition.
		add_filter( 'ap_ask_btn_link', array( $this, 'askBtnLink' ) );
		$this->assertSame( '<a class="ap-btn-ask" href="' . home_url( '/ask' ) . '">Ask question</a>', ap_get_ask_btn() );
		ob_start();
		ap_ask_btn();
		$output = ob_get_clean();
		$this->assertSame( '<a class="ap-btn-ask" href="' . home_url( '/ask' ) . '">Ask question</a>', $output );

		// Test after filter remove.
		remove_filter( 'ap_ask_btn_link', array( $this, 'askBtnLink' ) );
		$this->assertSame( '<a class="ap-btn-ask" href="' . esc_url( $link ) . '">Ask question</a>', ap_get_ask_btn() );
		ob_start();
		ap_ask_btn();
		$output = ob_get_clean();
		$this->assertSame( '<a class="ap-btn-ask" href="' . esc_url( $link ) . '">Ask question</a>', $output );
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
		$this->assertIsArray( ap_assets() );

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
		$this->assertEquals( is_rtl(), wp_style_is( 'anspress-rtl', 'enqueued' ) );
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

	/**
	 * @covers ::is_ap_search
	 */
	public function testIsApSearch() {
		// Non ap search page test.
		$this->assertFalse( is_ap_search() );
		$this->go_to( '/?s=question' );
		$this->assertFalse( is_ap_search() );
		$this->go_to( '/ap_?s=question' );
		$this->assertFalse( is_ap_search() );

		// Real ap search page test.
		$id = $this->factory()->post->create(
			[
				'post_title' => 'Base Page',
				'post_type'  => 'page',
			]
		);
		$this->go_to( '?post_type=page&p=' . $id . '&ap_s=question' );
		$this->assertFalse( is_ap_search() );
		$this->go_to( '?post_type=page&p=' . $id . '&ap_s=answer' );
		$this->assertFalse( is_ap_search() );
		ap_opt( 'base_page', $id );
		$this->go_to( '?post_type=page&p=' . $id . '&ap_s=question' );
		$this->assertTrue( is_ap_search() );
		$this->go_to( '?post_type=page&p=' . $id . '&ap_s=answer' );
		$this->assertTrue( is_ap_search() );
	}

	/**
	 * @covers ::ap_current_page
	 */
	public function testAPCurrentPage() {
		// Normal test.
		$this->assertEmpty( ap_current_page() );

		// Test for the single question page.
		$id = $this->insert_answer();
		$this->go_to( '?post_type=question&p=' . $id->q );
		$this->assertEquals( 'question', ap_current_page() );
		$this->go_to( ap_get_short_link( [ 'ap_q' => $id->q ] ) );
		$this->assertEquals( 'shortlink', ap_current_page() );

		// Test for the single answer page.
		$this->go_to( ap_get_short_link( [ 'ap_a' => $id->a ] ) );
		$this->assertEquals( 'shortlink', ap_current_page() );

		// For the base page test.
		$id = $this->factory()->post->create(
			[
				'post_title' => 'Base Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'base_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'base', ap_current_page() );

		// For the ask page test.
		$id = $this->factory()->post->create(
			[
				'post_title' => 'Ask Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'ask_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'ask', ap_current_page() );

		// For the user page test.
		$id = $this->factory()->post->create(
			[
				'post_title' => 'User Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'user_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'user', ap_current_page() );

		// For the categories page test.
		$id = $this->factory()->post->create(
			[
				'post_title' => 'Categories Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'categories_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'categories', ap_current_page() );

		// For the tags page test.
		$id = $this->factory()->post->create(
			[
				'post_title' => 'Tags Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'tags_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'tags', ap_current_page() );

		// For the activities page test.
		$id = $this->factory()->post->create(
			[
				'post_title' => 'Activities Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'activities_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'activities', ap_current_page() );

		// Test for the single category archive page.
		$cid = $this->factory()->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$this->assertEquals( 'category', ap_current_page() );

		// Test for the single tag archive page.
		$cid = $this->factory()->term->create(
			array(
				'name'     => 'Question tag',
				'taxonomy' => 'question_tag',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_tag' );
		$this->go_to( '/?ap_page=tag&question_tag=' . $term->slug );
		$this->assertEquals( 'tag', ap_current_page() );

		// Test for 404 error page.
		$this->go_to( '/error-404' );
		$this->assertEquals( '', ap_current_page() );
	}

	/**
	 * @covers ::ap_register_page
	 */
	public function testAPRegisterPage() {
		// Test 1.
		$page_slug = 'test-page';
		$page_title = 'Test Page';
		$func = 'test_page';
		$show_in_menu = true;
		$is_private = true;

		// Call the function.
		ap_register_page( $page_slug, $page_title, $func, $show_in_menu, $is_private );

		// Access the pages property.
		$registered_page = anspress()->pages[ $page_slug ];

		// Test begins.
		$this->assertNotEmpty( $registered_page );
		$this->assertEquals( $page_title, $registered_page['title'] );
		$this->assertEquals( $func, $registered_page['func'] );
		$this->assertEquals( $show_in_menu, $registered_page['show_in_menu'] );
		$this->assertEquals( $is_private, $registered_page['private'] );

		// Test 2.
		$page_slug = 'test-page-1';
		$page_title = 'Test Page 1';
		$func = 'test_page_1';

		// Call the function.
		ap_register_page( $page_slug, $page_title, $func );

		// Access the pages property.
		$registered_page = anspress()->pages[ $page_slug ];

		// Test begins.
		$this->assertNotEmpty( $registered_page );
		$this->assertEquals( $page_title, $registered_page['title'] );
		$this->assertEquals( $func, $registered_page['func'] );
		$this->assertEquals( true, $registered_page['show_in_menu'] );
		$this->assertEquals( false, $registered_page['private'] );

		// Test 3.
		$page_slug = 'test-page-2';
		$page_title = 'Test Page 2';
		$func = 'test_page_2';
		$show_in_menu = false;
		$is_private = true;

		// Call the function.
		ap_register_page( $page_slug, $page_title, $func, $show_in_menu, $is_private );

		// Access the pages property.
		$registered_page = anspress()->pages[ $page_slug ];

		// Test begins.
		$this->assertNotEmpty( $registered_page );
		$this->assertEquals( $page_title, $registered_page['title'] );
		$this->assertEquals( $func, $registered_page['func'] );
		$this->assertEquals( $show_in_menu, $registered_page['show_in_menu'] );
		$this->assertEquals( $is_private, $registered_page['private'] );

		// Test 4.
		$page_slug = 'test-page-3';
		$page_title = 'Test Page 3';
		$func = function() {};
		$is_private = true;

		// Call the function.
		ap_register_page( $page_slug, $page_title, $func, '', $is_private );

		// Access the pages property.
		$registered_page = anspress()->pages[ $page_slug ];

		// Test begins.
		$this->assertNotEmpty( $registered_page );
		$this->assertEquals( $page_title, $registered_page['title'] );
		$this->assertEquals( $func, $registered_page['func'] );
		$this->assertEquals( false, $registered_page['show_in_menu'] );
		$this->assertEquals( $is_private, $registered_page['private'] );
	}

	/**
	 * @covers ::ap_page
	 */
	public function testAPPage() {
		// Test for valid page.
		anspress()->pages[ 'test-page' ] = [
			'title'        => 'Test Page',
			'func'         => function() {
				echo 'Test Page Content';
			},
		];

		// Fetch for the contents.
		ob_start();
		ap_page( 'test-page' );
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Test Page Content', $output );

		// Test for invalid page.
		ob_start();
		ap_page( 'invalid-page' );
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Error 404', $output );

		// Test for not adding any page value.
		ob_start();
		ap_page();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Ask question', $output );
		$this->assertStringContainsString( 'There are no questions matching your query or you do not have permission to read them.', $output );

		// Test for not adding any page value but question exists.
		$this->factory()->post->create( [
			'post_title'   => 'Question title',
			'post_content' => 'Question content',
			'post_type'    => 'question',
		] );
		ob_start();
		ap_page();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Ask question', $output );
		$this->assertStringContainsString( 'Question title', $output );
		$this->assertStringNotContainsString( 'There are no questions matching your query or you do not have permission to read them.', $output );
	}

	/**
	 * @covers ::ap_post_actions_buttons
	 */
	public function testAPPostActionsButtons() {
		// Test for user not logged in.
		ob_start();
		ap_post_actions_buttons();
		$output = ob_get_clean();
		$this->assertEmpty( $output );
		$this->assertEquals( '', $output );

		// Test for user logged in.
		// Test 1.
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$nonce = wp_create_nonce( 'post-actions-' . $id );
		ob_start();
		ap_post_actions_buttons();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<postActions class="ap-dropdown">', $output );
		$this->assertStringContainsString( '<button class="ap-btn apicon-gear ap-actions-handle ap-dropdown-toggle" ap="actiontoggle" apquery="', $output );
		$this->assertStringContainsString( '{&quot;post_id&quot;:' . $id . ',', $output );
		$this->assertStringContainsString( '&quot;nonce&quot;:&quot;' . $nonce . '&quot;}', $output );
		$this->assertStringContainsString( '"></button><ul class="ap-actions ap-dropdown-menu"></ul></postActions>', $output );

		// Test 2.
		$this->setRole( 'administrator' );
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$nonce = wp_create_nonce( 'post-actions-' . $id );
		ob_start();
		ap_post_actions_buttons();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<postActions class="ap-dropdown">', $output );
		$this->assertStringContainsString( '<button class="ap-btn apicon-gear ap-actions-handle ap-dropdown-toggle" ap="actiontoggle" apquery="', $output );
		$this->assertStringContainsString( '{&quot;post_id&quot;:' . $id . ',', $output );
		$this->assertStringContainsString( '&quot;nonce&quot;:&quot;' . $nonce . '&quot;}', $output );
		$this->assertStringContainsString( '"></button><ul class="ap-actions ap-dropdown-menu"></ul></postActions>', $output );
	}

	/**
	 * Filter ap_get_list_filters function.
	 */
	public function APListFilters( $filters ) {
		unset( $filters['order_by']['multiple'] );
		$filters['order_by']['test'] = 'AnsPress Question Answer Plugin';

		return $filters;
	}

	/**
	 * @covers ::ap_get_list_filters
	 */
	public function testAPGetListFilters() {
		// Test 1.
		set_query_var( 'ap_s', [] );
		$filters = ap_get_list_filters();

		// Test begins.
		$this->assertIsArray( $filters );
		$this->assertArrayHasKey( 'order_by', $filters );
		$this->assertArrayHasKey( 'title', $filters['order_by'] );
		$this->assertArrayHasKey( 'items', $filters['order_by'] );
		$this->assertArrayHasKey( 'multiple', $filters['order_by'] );
		$this->assertEquals( 'Order By', $filters['order_by']['title'] );
		$this->assertEmpty( $filters['order_by']['items'] );
		$this->assertFalse( $filters['order_by']['multiple'] );

		// Test 2.
		set_query_var( 'ap_s', 'search_query' );
		$filters = ap_get_list_filters();

		// Test begins.
		$this->assertIsArray( $filters );
		$this->assertArrayHasKey( 'order_by', $filters );
		$this->assertArrayHasKey( 'title', $filters['order_by'] );
		$this->assertArrayHasKey( 'items', $filters['order_by'] );
		$this->assertArrayHasKey( 'multiple', $filters['order_by'] );
		$this->assertEquals( 'Order By', $filters['order_by']['title'] );
		$this->assertEmpty( $filters['order_by']['items'] );
		$this->assertFalse( $filters['order_by']['multiple'] );

		// Test 3.
		// With filter applied.
		add_filter( 'ap_list_filters', [ $this, 'APListFilters' ] );
		set_query_var( 'ap_s', [] );
		$filters = ap_get_list_filters();
		$this->assertArrayNotHasKey( 'multiple', $filters['order_by'] );
		$this->assertArrayHasKey( 'test', $filters['order_by'] );
		$this->assertEquals( 'AnsPress Question Answer Plugin', $filters['order_by']['test'] );

		// With filter removed.
		remove_filter( 'ap_list_filters', [ $this, 'APListFilters' ] );
		set_query_var( 'ap_s', [] );
		$filters = ap_get_list_filters();
		$this->assertArrayHasKey( 'multiple', $filters['order_by'] );
		$this->assertArrayNotHasKey( 'test', $filters['order_by'] );
	}

	/**
	 * @covers ::ap_menu_obejct
	 */
	public function testAPMenuObejct() {
		// Test 1.
		anspress()->pages = [];
		$result = ap_menu_obejct();
		$this->assertEmpty( $result );

		// Test 2.
		anspress()->pages = [
			'ask'     => [
				'title'        => 'Ask Page',
				'func'         => 'ask_page_func',
				'show_in_menu' => true,
			],
			'questions' => [
				'title'        => 'Questions Page',
				'func'         => 'questions_page_func',
				'show_in_menu' => true,
			],
			'base' => [
				'title'        => 'Base Page',
				'func'         => 'base_page_func',
				'show_in_menu' => true,
			],
		];
		$result = ap_menu_obejct();
		$this->assertNotEmpty( $result );
		$this->assertCount( 3, $result );
		$this->assertEquals( 'Ask Page', $result[0]->title );
		$this->assertEquals( 'Questions Page', $result[1]->title );
		$this->assertEquals( 'Base Page', $result[2]->title );

		// Test 3.
		anspress()->pages = [
			'ask'     => [
				'title'        => 'Ask Page',
				'func'         => 'ask_page_func',
				'show_in_menu' => true,
			],
			'questions' => [
				'title'        => 'Questions Page',
				'func'         => 'questions_page_func',
				'show_in_menu' => false,
			],
			'base' => [
				'title'        => 'Base Page',
				'func'         => 'base_page_func',
				'show_in_menu' => false,
			],
		];
		$result = ap_menu_obejct();
		$this->assertNotEmpty( $result );
		$this->assertCount( 1, $result );
		$this->assertEquals( 'Ask Page', $result[0]->title );
	}

	/**
	 * @covers ::ap_display_answer_metas
	 */
	public function testAPResponceMessage() {
		$this->setExpectedDeprecated( 'ap_display_answer_metas' );
		ap_display_answer_metas();
		$this->assertNull( ap_display_answer_metas() );
	}

	/**
	 * @covers ::ap_theme_compat_reset_post
	 */
	public function testAPThemeCompatResetPost() {
		// Test without setting any post.
		global $wp_query;
		$this->assertNull( $wp_query->post );
		$this->assertEquals( false, anspress()->theme_compat->active );
		ap_theme_compat_reset_post();
		$this->assertEquals( true, anspress()->theme_compat->active );
		$this->assertNotNull( $wp_query->post );
		global $post;
		$this->assertEquals( $post, $wp_query->post );
		$post_args = [
			'ID'                    => -9999,
			'post_status'           => 'publish',
			'post_author'           => 0,
			'post_parent'           => 0,
			'post_type'             => 'page',
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_content_filtered' => '',
			'post_mime_type'        => '',
			'post_password'         => '',
			'post_name'             => '',
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'to_ping'               => '',
			'ping_status'           => '',
			'comment_status'        => 'closed',
			'comment_count'         => 0,
			'filter'                => 'raw',
		];
		foreach ( $post_args as $key => $value ) {
			$this->assertEquals( $value, $post->$key );
		}
		$query_args = [
			'is_404'     => false,
			'is_page'    => false,
			'is_single'  => false,
			'is_archive' => false,
			'is_tax'     => false,
		];
		foreach ( $query_args as $key => $value ) {
			$this->assertEquals( $value, $wp_query->$key );
		}

		// Test with setting post.
		anspress()->theme_compat->active = false;
		$post_id = $this->factory()->post->create();
		$post = get_post( $post_id );
		global $wp_query;
		$this->assertNotNull( $wp_query->post );
		$this->assertEquals( false, anspress()->theme_compat->active );
		$wp_query->post = $post;
		ap_theme_compat_reset_post();
		$this->assertEquals( true, anspress()->theme_compat->active );
		global $post;
		$this->assertEquals( $post_id, $post->ID );
		$this->assertEquals( $post, $wp_query->post );
		$post_args = [ 'ID', 'post_status', 'post_author', 'post_parent', 'post_type', 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_content_filtered', 'post_mime_type', 'post_password', 'post_name', 'guid', 'menu_order', 'pinged', 'to_ping', 'ping_status', 'comment_status', 'comment_count', 'filter' ];
		foreach ( $post_args as $key ) {
			$this->assertEquals( $post->$key, $wp_query->post->$key );
		}
		$query_args = [ 'is_404', 'is_page', 'is_single', 'is_archive', 'is_tax' ];
		foreach ( $query_args as $key ) {
			$this->assertEquals( false, $wp_query->$key );
		}
	}

	/**
	 * @covers ::ap_subscribe_btn
	 */
	public function testAPSubscribeBtn() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		$args = wp_json_encode(
			array(
				'__nonce' => wp_create_nonce( 'subscribe_' . $id ),
				'id'      => $id,
			)
		);

		// Test 1.
		// Test for return value.
		$result = ap_subscribe_btn( $id, false );
		$this->assertStringContainsString( 'Subscribe', $result );
		$this->assertStringContainsString( esc_js( $args ), $result );
		$this->assertEquals( '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small " apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">Subscribe</span><span class="apsubscribers-count">0</span></a>', $result );

		// Test for echo value.
		ob_start();
		ap_subscribe_btn( $id );
		$result = ob_get_clean();
		$this->assertStringContainsString( 'Subscribe', $result );
		$this->assertStringContainsString( esc_js( $args ), $result );
		$this->assertEquals( '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small " apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">Subscribe</span><span class="apsubscribers-count">0</span></a>', $result );

		// Test 2.
		// Test for return value.
		ap_new_subscriber( false, 'question', $id );
		$result = ap_subscribe_btn( $id, false );
		$this->assertStringContainsString( 'Unsubscribe', $result );
		$this->assertStringContainsString( esc_js( $args ), $result );
		$this->assertEquals( '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small active" apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">Unsubscribe</span><span class="apsubscribers-count">1</span></a>', $result );

		// Test for echo value.
		ob_start();
		ap_subscribe_btn( $id );
		$result = ob_get_clean();
		$this->assertStringContainsString( 'Unsubscribe', $result );
		$this->assertStringContainsString( esc_js( $args ), $result );
		$this->assertEquals( '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small active" apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">Unsubscribe</span><span class="apsubscribers-count">1</span></a>', $result );

		// Test 3.
		$id = $this->insert_question();
		$args = wp_json_encode(
			array(
				'__nonce' => wp_create_nonce( 'subscribe_' . $id ),
				'id'      => $id,
			)
		);
		$user_id1 = $this->factory()->user->create();
		$user_id2 = $this->factory()->user->create();
		$user_id3 = $this->factory()->user->create();
		ap_new_subscriber( $user_id1, 'question', $id );
		ap_new_subscriber( $user_id2, 'question', $id );
		ap_new_subscriber( $user_id3, 'question', $id );

		// Test for return value.
		$result = ap_subscribe_btn( $id, false );
		$this->assertStringContainsString( 'Subscribe', $result );
		$this->assertStringContainsString( esc_js( $args ), $result );
		$this->assertEquals( '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small " apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">Subscribe</span><span class="apsubscribers-count">3</span></a>', $result );

		// Test for echo value.
		ob_start();
		ap_subscribe_btn( $id );
		$result = ob_get_clean();
		$this->assertStringContainsString( 'Subscribe', $result );
		$this->assertStringContainsString( esc_js( $args ), $result );
		$this->assertEquals( '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small " apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">Subscribe</span><span class="apsubscribers-count">3</span></a>', $result );

		// Test 4.
		ap_new_subscriber( get_current_user_id(), 'question', $id );

		// Test for return value.
		$result = ap_subscribe_btn( $id, false );
		$this->assertStringContainsString( 'Unsubscribe', $result );
		$this->assertStringContainsString( esc_js( $args ), $result );
		$this->assertEquals( '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small active" apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">Unsubscribe</span><span class="apsubscribers-count">4</span></a>', $result );

		// Test for echo value.
		ob_start();
		ap_subscribe_btn( $id );
		$result = ob_get_clean();
		$this->assertStringContainsString( 'Unsubscribe', $result );
		$this->assertStringContainsString( esc_js( $args ), $result );
		$this->assertEquals( '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small active" apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">Unsubscribe</span><span class="apsubscribers-count">4</span></a>', $result );
	}

	/**
	 * @covers ::ap_featured_post_args
	 */
	public function testAPFeaturedPostArgs() {
		// Test passing question id.
		$id = $this->insert_question();

		// Test 1.
		$result = ap_featured_post_args( $id );
		$this->assertEmpty( $result );
		$this->assertIsArray( $result );

		// Test 2.
		$this->setRole( 'subscriber' );
		$result = ap_featured_post_args( $id );
		$this->assertEmpty( $result );
		$this->assertIsArray( $result );

		// Test 3.
		$this->setRole( 'administrator' );
		$result = ap_featured_post_args( $id );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$args = [
			'cb'     => 'toggle_featured',
			'active' => false,
			'query'  => array(
				'__nonce' => wp_create_nonce( 'set_featured_' . $id ),
				'post_id' => $id,
			),
			'title'  => 'Mark this question as featured',
			'label'  => 'Feature',
		];
		$this->assertEquals( $args, $result );

		// Test 4.
		ap_set_featured_question( $id );
		$result = ap_featured_post_args( $id );
		$args['active'] = true;
		$args['title'] = 'Unmark this question as featured';
		$args['label'] = 'Unfeature';
		$this->assertEquals( $args, $result );
		$this->logout();

		// Test widthout passing question id.
		$id = $this->insert_question();

		// Test 1.
		$this->go_to( '?post_type=question&p=' . $id );
		$result = ap_featured_post_args();
		$this->assertEmpty( $result );
		$this->assertIsArray( $result );

		// Test 2.
		$this->setRole( 'subscriber' );
		$this->go_to( '?post_type=question&p=' . $id );
		$result = ap_featured_post_args();
		$this->assertEmpty( $result );
		$this->assertIsArray( $result );

		// Test 3.
		$this->setRole( 'administrator' );
		$this->go_to( '?post_type=question&p=' . $id );
		$result = ap_featured_post_args();
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$args = [
			'cb'     => 'toggle_featured',
			'active' => false,
			'query'  => array(
				'__nonce' => wp_create_nonce( 'set_featured_' . $id ),
				'post_id' => $id,
			),
			'title'  => 'Mark this question as featured',
			'label'  => 'Feature',
		];
		$this->assertEquals( $args, $result );

		// Test 4.
		ap_set_featured_question( $id );
		$this->go_to( '?post_type=question&p=' . $id );
		$result = ap_featured_post_args();
		$args['active'] = true;
		$args['title'] = 'Unmark this question as featured';
		$args['label'] = 'Unfeature';
		$this->assertEquals( $args, $result );
	}

	/**
	 * @covers ::ap_post_status_btn_args
	 */
	public function testAPPostStatusBtnArgs() {
		// Test 1.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'trash' ] );
		$result = ap_post_status_btn_args( $question_id );
		$this->assertEmpty( $result );
		$this->assertIsArray( $result );

		// Test 2.
		$this->setRole( 'administrator' );
		$result = ap_post_status_btn_args( $question_id );
		$this->assertEmpty( $result );
		$this->assertIsArray( $result );

		// Test 3.
		$this->logout();
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );
		$result = ap_post_status_btn_args( $question_id );
		$this->assertNull( $result );

		// Test 4.
		$this->setRole( 'subscriber' );
		$result = ap_post_status_btn_args( $question_id );
		$this->assertNull( $result );

		// Test 5.
		$this->setRole( 'administrator' );
		$result = ap_post_status_btn_args( $question_id );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$expected = [
			[
				'cb'     => 'status',
				'active' => false,
				'query'  => array(
					'status'  => 'publish',
					'__nonce' => wp_create_nonce( 'change-status-publish-' . $question_id ),
					'post_id' => $question_id,
				),
				'label'  => 'Published',
			],
			[
				'cb'     => 'status',
				'active' => true,
				'query'  => array(
					'status'  => 'private_post',
					'__nonce' => wp_create_nonce( 'change-status-private_post-' . $question_id ),
					'post_id' => $question_id,
				),
				'label'  => 'Private',
			],
			[
				'cb'     => 'status',
				'active' => false,
				'query'  => array(
					'status'  => 'moderate',
					'__nonce' => wp_create_nonce( 'change-status-moderate-' . $question_id ),
					'post_id' => $question_id,
				),
				'label'  => 'Moderate',
			],
		];
		$this->assertEquals( $expected, $result );
		foreach ( $expected as $key => $value ) {
			$this->assertEquals( $value, $result[ $key ] );
		}

		// Test 6.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		$result = ap_post_status_btn_args( $question_id );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$expected = [
			[
				'cb'     => 'status',
				'active' => true,
				'query'  => array(
					'status'  => 'publish',
					'__nonce' => wp_create_nonce( 'change-status-publish-' . $question_id ),
					'post_id' => $question_id,
				),
				'label'  => 'Published',
			],
			[
				'cb'     => 'status',
				'active' => false,
				'query'  => array(
					'status'  => 'private_post',
					'__nonce' => wp_create_nonce( 'change-status-private_post-' . $question_id ),
					'post_id' => $question_id,
				),
				'label'  => 'Private',
			],
			[
				'cb'     => 'status',
				'active' => false,
				'query'  => array(
					'status'  => 'moderate',
					'__nonce' => wp_create_nonce( 'change-status-moderate-' . $question_id ),
					'post_id' => $question_id,
				),
				'label'  => 'Moderate',
			],
		];
		$this->assertEquals( $expected, $result );
		foreach ( $expected as $key => $value ) {
			$this->assertEquals( $value, $result[ $key ] );
		}

		// Test 7.
		if ( \is_multisite() ) {
			$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
			wp_set_current_user( $user_id );
			$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
			$result = ap_post_status_btn_args( $question_id );
			$this->assertNull( $result );

			// Test for super admin.
			grant_super_admin( $user_id );
			$result = ap_post_status_btn_args( $question_id );
			$this->assertNotEmpty( $result );
			$this->assertIsArray( $result );
			$expected = [
				[
					'cb'     => 'status',
					'active' => false,
					'query'  => array(
						'status'  => 'publish',
						'__nonce' => wp_create_nonce( 'change-status-publish-' . $question_id ),
						'post_id' => $question_id,
					),
					'label'  => 'Published',
				],
				[
					'cb'     => 'status',
					'active' => false,
					'query'  => array(
						'status'  => 'private_post',
						'__nonce' => wp_create_nonce( 'change-status-private_post-' . $question_id ),
						'post_id' => $question_id,
					),
					'label'  => 'Private',
				],
				[
					'cb'     => 'status',
					'active' => true,
					'query'  => array(
						'status'  => 'moderate',
						'__nonce' => wp_create_nonce( 'change-status-moderate-' . $question_id ),
						'post_id' => $question_id,
					),
					'label'  => 'Moderate',
				],
			];
			$this->assertEquals( $expected, $result );
			foreach ( $expected as $key => $value ) {
				$this->assertEquals( $value, $result[ $key ] );
			}
		} else {
			$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
			wp_set_current_user( $user_id );
			$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
			$result = ap_post_status_btn_args( $question_id );
			$this->assertNotEmpty( $result );
			$this->assertIsArray( $result );
			$expected = [
				[
					'cb'     => 'status',
					'active' => false,
					'query'  => array(
						'status'  => 'publish',
						'__nonce' => wp_create_nonce( 'change-status-publish-' . $question_id ),
						'post_id' => $question_id,
					),
					'label'  => 'Published',
				],
				[
					'cb'     => 'status',
					'active' => false,
					'query'  => array(
						'status'  => 'private_post',
						'__nonce' => wp_create_nonce( 'change-status-private_post-' . $question_id ),
						'post_id' => $question_id,
					),
					'label'  => 'Private',
				],
				[
					'cb'     => 'status',
					'active' => true,
					'query'  => array(
						'status'  => 'moderate',
						'__nonce' => wp_create_nonce( 'change-status-moderate-' . $question_id ),
						'post_id' => $question_id,
					),
					'label'  => 'Moderate',
				],
			];
			$this->assertEquals( $expected, $result );
			foreach ( $expected as $key => $value ) {
				$this->assertEquals( $value, $result[ $key ] );
			}
		}
	}

	/**
	 * @covers ::ap_select_answer_btn_html
	 */
	public function testAPSelectAnswerBtnHtml() {
		// Test 1.
		$id = $this->insert_answer();
		$result = ap_select_answer_btn_html( $id->a );
		$this->assertNull( $result );

		// Test 2.
		$this->setRole( 'subscriber' );
		$result = ap_select_answer_btn_html( $id->a );
		$this->assertNull( $result );

		// Test 3.
		$this->setRole( 'administrator' );
		$result = ap_select_answer_btn_html( $id->a );
		$this->assertNotEmpty( $result );
		$args = wp_json_encode(
			array(
				'answer_id' => $id->a,
				'__nonce'   => wp_create_nonce( 'select-answer-' . $id->a ),
			)
		);
		$this->assertEquals( '<a href="#" class="ap-btn-select ap-btn " ap="select_answer" apquery="' . esc_js( $args ) . '" title="Select this answer as best">Select</a>', $result );

		// Test 4.
		ap_set_selected_answer( $id->q, $id->a );
		$result = ap_select_answer_btn_html( $id->a );
		$this->assertNotEmpty( $result );
		$args = wp_json_encode(
			array(
				'answer_id' => $id->a,
				'__nonce'   => wp_create_nonce( 'select-answer-' . $id->a ),
			)
		);
		$this->assertEquals( '<a href="#" class="ap-btn-select ap-btn  active" ap="select_answer" apquery="' . esc_js( $args ) . '" title="Unselect this answer">Unselect</a>', $result );

		// Test 5.
		$ids = $this->factory()->post->create_many( 5, [ 'post_type' => 'answer', 'post_parent' => $id->q ] );
		ap_set_selected_answer( $id->q, $ids[2] );
		$result = ap_select_answer_btn_html( $ids[0] );
		$this->assertNotEmpty( $result );
		$args = wp_json_encode(
			array(
				'answer_id' => $ids[0],
				'__nonce'   => wp_create_nonce( 'select-answer-' . $ids[0] ),
			)
		);
		$this->assertEquals( '<a href="#" class="ap-btn-select ap-btn  hide" ap="select_answer" apquery="' . esc_js( $args ) . '" title="Select this answer as best">Select</a>', $result );

		// Test 6.
		$result = ap_select_answer_btn_html( $ids[1] );
		$this->assertNotEmpty( $result );
		$args = wp_json_encode(
			array(
				'answer_id' => $ids[1],
				'__nonce'   => wp_create_nonce( 'select-answer-' . $ids[1] ),
			)
		);
		$this->assertEquals( '<a href="#" class="ap-btn-select ap-btn  hide" ap="select_answer" apquery="' . esc_js( $args ) . '" title="Select this answer as best">Select</a>', $result );

		// Test 7.
		$result = ap_select_answer_btn_html( $ids[2] );
		$this->assertNotEmpty( $result );
		$args = wp_json_encode(
			array(
				'answer_id' => $ids[2],
				'__nonce'   => wp_create_nonce( 'select-answer-' . $ids[2] ),
			)
		);
		$this->assertEquals( '<a href="#" class="ap-btn-select ap-btn  active" ap="select_answer" apquery="' . esc_js( $args ) . '" title="Unselect this answer">Unselect</a>', $result );
	}

	/**
	 * @covers ::ap_localize_script
	 */
	public function testAPLocalizeScript() {
		ob_start();
		ap_localize_script();
		$output = ob_get_clean();

		// Test begins.
		$aplang = [
			'loading'                => 'Loading..',
			'sending'                => 'Sending request',
			'file_size_error'        => esc_attr( sprintf( 'File size is bigger than %s MB', round( ap_opt( 'max_upload_size' ) / ( 1024 * 1024 ), 2 ) ) ),
			'attached_max'           => 'You have already attached maximum numbers of allowed attachments',
			'commented'              => 'commented',
			'comment'                => 'Comment',
			'cancel'                 => 'Cancel',
			'update'                 => 'Update',
			'your_comment'           => 'Write your comment...',
			'notifications'          => 'Notifications',
			'mark_all_seen'          => 'Mark all as seen',
			'search'                 => 'Search',
			'no_permission_comments' => 'Sorry, you don\'t have permission to read comments.',
			'ajax_events'            => 'Are you sure you want to %s?',
			'ajax_error'             => [
				'snackbar' => [
					'success' => false,
					'message' => 'Something went wrong. Please try again.',
				],
				'modal'    => [
					'imageUpload',
				],
			],
		];
		$this->assertStringContainsString( '<script type="text/javascript">', $output );
		$this->assertStringContainsString( 'var ajaxurl = "' . esc_url( admin_url( 'admin-ajax.php' ) ) . '",', $output );
		$this->assertStringContainsString( 'ap_nonce = "' . esc_attr( wp_create_nonce( 'ap_ajax_nonce' ) ) . '",', $output );
		$this->assertStringContainsString( 'apTemplateUrl = "' . esc_url( ap_get_theme_url( 'js-template', false, false ) ) . '";', $output );
		$this->assertStringContainsString( 'apQuestionID = "' . (int) get_question_id() . '";', $output );
		$this->assertStringContainsString( 'aplang = ' . wp_json_encode( $aplang ) . ';', $output );
		$this->assertStringContainsString( 'disable_q_suggestion = "' . (bool) ap_opt( 'disable_q_suggestion' ) . '";', $output );
		$this->assertStringContainsString( '</script>', $output );
	}

	/**
	 * @covers ::ap_post_status
	 */
	public function testAPPostStatusPassingNoArgPassed() {
		// Test 1.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_post_status();
		$this->assertEquals( 'moderate', $result );

		// Test 2.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_post_status();
		$this->assertEquals( 'private_post', $result );

		// Test 3.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_post_status();
		$this->assertEquals( 'publish', $result );
	}

	/**
	 * @covers ::ap_post_status
	 */
	public function testAPPostStatusFalseArgPassed() {
		// Test 1.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_post_status( false );
		$this->assertEquals( 'moderate', $result );

		// Test 2.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_post_status( false );
		$this->assertEquals( 'private_post', $result );

		// Test 3.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_post_status( false );
		$this->assertEquals( 'publish', $result );
	}

	/**
	 * @covers ::ap_have_parent_post
	 */
	public function testAPHaveParentPostWithNoArgsShouldReturnFalse() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertFalse( ap_have_parent_post() );
	}

	/**
	 * @covers ::ap_have_parent_post
	 */
	public function testAPHaveParentPostWithNoArgsShouldReturnTrue() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$question_child_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_parent' => $question_id ] );
		$this->go_to( '?post_type=question&p=' . $question_child_id );
		$this->assertTrue( ap_have_parent_post() );
	}

	/**
	 * @covers ::ap_have_parent_post
	 */
	public function testAPHaveParentPostWithFalseArgShouldReturnFalse() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertFalse( ap_have_parent_post( false ) );
	}

	/**
	 * @covers ::ap_have_parent_post
	 */
	public function testAPHaveParentPostWithFalseArgShouldReturnTrue() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$question_child_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_parent' => $question_id ] );
		$this->go_to( '?post_type=question&p=' . $question_child_id );
		$this->assertTrue( ap_have_parent_post( false ) );
	}

	/**
	 * @covers ::ap_answers_tab
	 */
	public function testAPAnswersTab() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		ob_start();
		ap_answers_tab();
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">', $result );
		$this->assertStringContainsString( '<li class="active"><a href="' . esc_url( add_query_arg( array( 'order_by' => 'active' ), get_permalink() ) . '#answers-order' ) . '">Active</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'oldest' ), get_permalink() ) . '#answers-order' ) . '">Oldest</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'voted' ), get_permalink() ) . '#answers-order' ) . '">Voted</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'newest' ), get_permalink() ) . '#answers-order' ) . '">Newest</a></li>', $result );
	}

	/**
	 * @covers ::ap_answers_tab
	 */
	public function testAPAnswersTabWithCustomBase() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		ob_start();
		ap_answers_tab( 'https://example.com' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">', $result );
		$this->assertStringContainsString( '<li class="active"><a href="' . esc_url( add_query_arg( array( 'order_by' => 'active' ), 'https://example.com' ) . '#answers-order' ) . '">Active</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'oldest' ), 'https://example.com' ) . '#answers-order' ) . '">Oldest</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'voted' ), 'https://example.com' ) . '#answers-order' ) . '">Voted</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'newest' ), 'https://example.com' ) . '#answers-order' ) . '">Newest</a></li>', $result );
	}

	/**
	 * @covers ::ap_answers_tab
	 */
	public function testAPAnswersTabWithCustomOrder() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$_REQUEST['order_by'] = 'newest';
		ob_start();
		ap_answers_tab();
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'active' ), get_permalink() ) . '#answers-order' ) . '">Active</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'oldest' ), get_permalink() ) . '#answers-order' ) . '">Oldest</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'voted' ), get_permalink() ) . '#answers-order' ) . '">Voted</a></li>', $result );
		$this->assertStringContainsString( '<li class="active"><a href="' . esc_url( add_query_arg( array( 'order_by' => 'newest' ), get_permalink() ) . '#answers-order' ) . '">Newest</a></li>', $result );
	}

	/**
	 * @covers ::ap_answers_tab
	 */
	public function testAPAnswersTabWithCustomOrderAndCustomBase() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$_REQUEST['order_by'] = 'voted';
		ob_start();
		ap_answers_tab( 'https://example.com' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'active' ), 'https://example.com' ) . '#answers-order' ) . '">Active</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'oldest' ), 'https://example.com' ) . '#answers-order' ) . '">Oldest</a></li>', $result );
		$this->assertStringContainsString( '<li class="active"><a href="' . esc_url( add_query_arg( array( 'order_by' => 'voted' ), 'https://example.com' ) . '#answers-order' ) . '">Voted</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'newest' ), 'https://example.com' ) . '#answers-order' ) . '">Newest</a></li>', $result );
	}

	/**
	 * @covers ::ap_answers_tab
	 */
	public function testAPAnswersTabWithDisableVotingOnAnswerEnabled() {
		ap_opt( 'disable_voting_on_answer', true );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		ob_start();
		ap_answers_tab();
		$result = ob_get_clean();
		$this->assertStringContainsString( '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">', $result );
		$this->assertStringContainsString( '<li class="active"><a href="' . esc_url( add_query_arg( array( 'order_by' => 'active' ), get_permalink() ) . '#answers-order' ) . '">Active</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'oldest' ), get_permalink() ) . '#answers-order' ) . '">Oldest</a></li>', $result );
		$this->assertStringNotContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'voted' ), get_permalink() ) . '#answers-order' ) . '">Voted</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( add_query_arg( array( 'order_by' => 'newest' ), get_permalink() ) . '#answers-order' ) . '">Newest</a></li>', $result );
		ap_opt( 'disable_voting_on_answer', false );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForInvalidPostType() {
		$this->setRole( 'administrator' );
		$post_id = $this->factory()->post->create( [ 'post_type' => 'post' ] );
		$post_actions = ap_post_actions( $post_id );
		$this->assertEmpty( $post_actions );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForFeaturedLink() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'     => 'toggle_featured',
			'active' => false,
			'query'  => array(
				'__nonce' => wp_create_nonce( 'set_featured_' . $question_id ),
				'post_id' => $question_id,
			),
			'title'  => 'Mark this question as featured',
			'label'  => 'Feature',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserCanCloseQuestion() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'     => 'close',
			'icon'  => 'apicon-check',
			'query'  => array(
				'nonce' => wp_create_nonce( 'close_' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Close',
			'title'  => 'Close this question for new answer.',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserCanCloseQuestionButQuestionIsClosed() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		ap_toggle_close_question( $question_id );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'     => 'close',
			'icon'  => 'apicon-check',
			'query'  => array(
				'nonce' => wp_create_nonce( 'close_' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Open',
			'title'  => 'Open this question for new answers',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserCantCloseQuestion() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$not_expected = [
			'cb'     => 'close',
			'icon'  => 'apicon-check',
			'query'  => array(
				'nonce' => wp_create_nonce( 'close_' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Close',
			'title'  => 'Close this question for new answer.',
		];
		$this->assertFalse( in_array( $not_expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserCanEditQuestion() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'    => 'edit',
			'label' => 'Edit',
			'href'  => ap_post_edit_link( $question_id ),
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserCantEditQuestion() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_author' => 0 ] );
		$post_actions = ap_post_actions( $question_id );
		$not_expected = [
			'cb'    => 'edit',
			'label' => 'Edit',
			'href'  => ap_post_edit_link( $question_id ),
		];
		$this->assertFalse( in_array( $not_expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForFlagBtnArgs() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'     => 'flag',
			'icon'   => 'apicon-check',
			'query'  => array(
				'__nonce' => wp_create_nonce( 'flag_' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Flag',
			'title'  => 'Flag this question',
			'count'  => ap_get_post( $question_id )->flags,
			'active' => false,
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForAPPostStatusBtnArgs() {
		$this->setRole( 'administrator', true );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected_header_before = [
			'label'  => 'Status',
			'header' => true,
		];
		$this->assertTrue( in_array( $expected_header_before, $post_actions ) );
		$expected_publish = [
			'cb'     => 'status',
			'active' => false,
			'query'  => array(
				'status'  => 'publish',
				'__nonce' => wp_create_nonce( 'change-status-publish-' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Published',
		];
		$this->assertTrue( in_array( $expected_publish, $post_actions ) );
		$expected_private = [
			'cb'     => 'status',
			'active' => false,
			'query'  => array(
				'status'  => 'private_post',
				'__nonce' => wp_create_nonce( 'change-status-private_post-' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Private',
		];
		$this->assertTrue( in_array( $expected_private, $post_actions ) );
		$expected_moderate = [
			'cb'     => 'status',
			'active' => true,
			'query'  => array(
				'status'  => 'moderate',
				'__nonce' => wp_create_nonce( 'change-status-moderate-' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Moderate',
		];
		$this->assertTrue( in_array( $expected_moderate, $post_actions ) );
		$expected_header_after = [
			'header' => true,
		];
		$this->assertTrue( in_array( $expected_header_after, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForAPPostStatusBtnArgsWhoCantUpdateStatus() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate', 'post_author' => 0 ] );
		$post_actions = ap_post_actions( $question_id );
		$not_expected_header_before = [
			'label'  => 'Status',
			'header' => true,
		];
		$this->assertFalse( in_array( $not_expected_header_before, $post_actions ) );
		$not_expected_publish = [
			'cb'     => 'status',
			'active' => false,
			'query'  => array(
				'status'  => 'publish',
				'__nonce' => wp_create_nonce( 'change-status-publish-' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Published',
		];
		$this->assertFalse( in_array( $not_expected_publish, $post_actions ) );
		$not_expected_private = [
			'cb'     => 'status',
			'active' => false,
			'query'  => array(
				'status'  => 'private_post',
				'__nonce' => wp_create_nonce( 'change-status-private_post-' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Private',
		];
		$this->assertFalse( in_array( $not_expected_private, $post_actions ) );
		$not_expected_moderate = [
			'cb'     => 'status',
			'active' => true,
			'query'  => array(
				'status'  => 'moderate',
				'__nonce' => wp_create_nonce( 'change-status-moderate-' . $question_id ),
				'post_id' => $question_id,
			),
			'label'  => 'Moderate',
		];
		$this->assertFalse( in_array( $not_expected_moderate, $post_actions ) );
		$not_expected_header_after = [
			'header' => true,
		];
		$this->assertFalse( in_array( $not_expected_header_after, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCanDeleteQuestion() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'    => 'toggle_delete_post',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'trash_post_' . $question_id ),
			),
			'label' => 'Delete',
			'title' => 'Delete this question (can be restored again)',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCantDeleteQuestionButQuestionStatusIsTrash() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'trash' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'    => 'toggle_delete_post',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'trash_post_' . $question_id ),
			),
			'label' => 'Undelete',
			'title' => 'Restore this question',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCantDeleteQuestion() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$not_expected = [
			'cb'    => 'toggle_delete_post',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'trash_post_' . $question_id ),
			),
			'label' => 'Delete',
			'title' => 'Delete this %s (can be restored again)',
		];
		$this->assertFalse( in_array( $not_expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCanDeleteQuestionButHaveAnswersAndTrashingQuestionWithAnswerEnabled() {
		ap_opt( 'trashing_question_with_answer', true );
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$post_actions = ap_post_actions( $question_id );
		$not_expected = [
			'cb'    => 'toggle_delete_post',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'trash_post_' . $question_id ),
			),
			'label' => 'Delete',
			'title' => 'Delete this question (can be restored again)',
		];
		$this->assertFalse( in_array( $not_expected, $post_actions ) );
		ap_opt( 'trashing_question_with_answer', false );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCanDeleteQuestionButHaveAnswersAndTrashingQuestionWithAnswerDisabled() {
		ap_opt( 'trashing_question_with_answer', false );
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'    => 'toggle_delete_post',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'trash_post_' . $question_id ),
			),
			'label' => 'Delete',
			'title' => 'Delete this question (can be restored again)',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCanPermanentDeleteQuestion() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'    => 'delete_permanently',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'delete_post_' . $question_id ),
			),
			'label' => 'Delete Permanently',
			'title' => 'Delete question permanently (cannot be restored again)',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCantPermanentDeleteQuestion() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$not_expected = [
			'cb'    => 'delete_permanently',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'delete_post_' . $question_id ),
			),
			'label' => 'Delete Permanently',
			'title' => 'Delete question permanently (cannot be restored again)',
		];
		$this->assertFalse( in_array( $not_expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCantPermanentDeleteQuestionAndDeletingQuestionWithAnswerEnabled() {
		ap_opt( 'deleting_question_with_answer', true );
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$post_actions = ap_post_actions( $question_id );
		$not_expected = [
			'cb'    => 'delete_permanently',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'delete_post_' . $question_id ),
			),
			'label' => 'Delete Permanently',
			'title' => 'Delete question permanently (cannot be restored again)',
		];
		$this->assertFalse( in_array( $not_expected, $post_actions ) );
		ap_opt( 'deleting_question_with_answer', false );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCantPermanentDeleteQuestionAndDeletingQuestionWithAnswerDisabled() {
		ap_opt( 'deleting_question_with_answer', false );
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'    => 'delete_permanently',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'delete_post_' . $question_id ),
			),
			'label' => 'Delete Permanently',
			'title' => 'Delete question permanently (cannot be restored again)',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCanConvertQuestionToPost() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$expected = [
			'cb'    => 'convert_to_post',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'convert-post-' . $question_id ),
			),
			'label' => 'Convert to post',
			'title' => 'Convert this question to blog post',
		];
		$this->assertTrue( in_array( $expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCantConvertQuestionToPost() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post_actions = ap_post_actions( $question_id );
		$not_expected = [
			'cb'    => 'convert_to_post',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'convert-post-' . $question_id ),
			),
			'label' => 'Convert to post',
			'title' => 'Convert this question to blog post',
		];
		$this->assertFalse( in_array( $not_expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_post_actions
	 */
	public function testAPPostActionsForUserWhoCanConvertQuestionToPostButPostTypeIsInvalid() {
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'post' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$post_actions = ap_post_actions( $answer_id );
		$not_expected = [
			'cb'    => 'convert_to_post',
			'query' => array(
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'convert-post-' . $question_id ),
			),
			'label' => 'Convert to post',
			'title' => 'Convert this question to blog post',
		];
		$this->assertFalse( in_array( $not_expected, $post_actions ) );
	}

	/**
	 * @covers ::ap_current_page
	 */
	public function testAPCurrentPageForEditPage() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( ap_post_edit_link( $question_id ) );
		set_query_var( 'ap_page', 'edit' );
		$this->assertEquals( 'edit', ap_current_page() );
	}

	/**
	 * @covers ::ap_current_page
	 */
	public function testAPCurrentPageFor404Page() {
		$this->go_to( '/404-error-page' );
		global $wp_query;
		$wp_query->is_404 = true;
		$this->assertEquals( '', ap_current_page() );
	}

	/**
	 * @covers ::ap_current_page
	 */
	public function testAPCurrentPageForLookingForArg() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertTrue( ap_current_page( 'question' ) );
	}

	/**
	 * @covers ::ap_current_page
	 */
	public function testAPCurrentPageForLookingForArgShouldReturnFalse() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertFalse( ap_current_page( 'answer' ) );
	}

	/**
	 * @covers ::ap_current_page
	 */
	public function testAPCurrentPageForMainPages() {
		$user_page = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'user_page', $user_page );
		$this->go_to( '?post_type=page&p=' . $user_page );
		set_query_var( 'ap_page', 'user' );
		$this->assertEquals( 'user', ap_current_page() );
	}

	public static function APCurrentPage() {
		return 'test_query_var';
	}

	/**
	 * @covers ::ap_current_page
	 */
	public function testAPCurrentPageForAPCurrentPageFilter() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		add_filter( 'ap_current_page', [ $this, 'APCurrentPage' ] );
		$this->assertEquals( 'test_query_var', ap_current_page() );
		remove_filter( 'ap_current_page', [ $this, 'APCurrentPage' ] );
	}

	/**
	 * @covers ::ap_pagination
	 */
	public function testAPPaginationShouldReturnEmptyWithTotalPagesSetAsOne() {
		global $ap_max_num_pages, $ap_current;
		$ap_max_num_pages = 1;
		ob_start();
		ap_pagination();
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	/**
	 * @covers ::ap_pagination
	 */
	public function testAPPaginationWithGlobalVariables() {
		global $ap_max_num_pages, $ap_current;
		$ap_max_num_pages = 5;
		$ap_current = 2;
		ob_start();
		ap_pagination( true );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $result );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">2</span>', $result );
		$this->assertStringContainsString( '&#038;paged=1', $result );
		$this->assertStringContainsString( '&#038;paged=3', $result );
		$this->assertStringContainsString( '&#038;paged=4', $result );
		$this->assertStringContainsString( '&#038;paged=5', $result );
		$this->assertStringContainsString( '<a class="next page-numbers" rel="next"', $result );
		$this->assertStringContainsString( '<a class="prev page-numbers" rel="prev"', $result );
	}

	/**
	 * @covers ::ap_pagination
	 */
	public function testAPPaginationWithCustomCurrentAndTotalPages() {
		global $ap_max_num_pages, $ap_current;
		$ap_max_num_pages = null;
		set_query_var( 'paged', 2 );
		anspress()->questions->max_num_pages = 5;
		ob_start();
		ap_pagination();
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $result );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">2</span>', $result );
		$this->assertStringContainsString( '&#038;paged=1', $result );
		$this->assertStringContainsString( '&#038;paged=3', $result );
		$this->assertStringContainsString( '&#038;paged=4', $result );
		$this->assertStringContainsString( '&#038;paged=5', $result );
		$this->assertStringContainsString( '<a class="next page-numbers" rel="next"', $result );
		$this->assertStringContainsString( '<a class="prev page-numbers" rel="prev"', $result );
	}

	/**
	 * @covers ::ap_pagination
	 */
	public function testAPPaginationForFrontPage() {
		global $ap_max_num_pages, $ap_current;
		$ap_max_num_pages = 5;
		$this->go_to( home_url() );
		$_REQUEST['ap_paged'] = 2;
		ob_start();
		ap_pagination();
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $result );
		$this->assertStringContainsString( '<span aria-current="page" class="page-numbers current">2</span>', $result );
		$this->assertStringContainsString( '?paged=1', $result );
		$this->assertStringContainsString( '?paged=3', $result );
		$this->assertStringContainsString( '?paged=4', $result );
		$this->assertStringContainsString( '?paged=5', $result );
		$this->assertStringContainsString( '<a class="next page-numbers" rel="next"', $result );
		$this->assertStringContainsString( '<a class="prev page-numbers" rel="prev"', $result );
		unset( $_REQUEST['ap_paged'] );
	}
}
