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
		$id = $this->factory->post->create(
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
		$id = $this->factory->post->create(
			[
				'post_title' => 'Base Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'base_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'base', ap_current_page() );

		// For the ask page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Ask Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'ask_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'ask', ap_current_page() );

		// For the user page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'User Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'user_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'user', ap_current_page() );

		// For the categories page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Categories Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'categories_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'categories', ap_current_page() );

		// For the tags page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Tags Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'tags_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'tags', ap_current_page() );

		// For the activities page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Activities Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'activities_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'activities', ap_current_page() );

		// Test for the single category archive page.
		$cid = $this->factory->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$this->assertEquals( 'category', ap_current_page() );

		// Test for the single tag archive page.
		$cid = $this->factory->term->create(
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
		$this->factory->post->create( [
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
	 * @covers ::ap_ajax_tinymce_assets
	 */
	public function testAPAjaxTinyMCEAssets() {
		ob_start();
		ap_ajax_tinymce_assets();
		$output = ob_get_clean();

		// Test for assertions.
		$this->assertStringContainsString( 'anspress-common-js', $output );
		$this->assertStringContainsString( 'anspress-question-js', $output );
		$this->assertStringContainsString( 'anspress-ask-js', $output );
		$this->assertStringContainsString( 'anspress-list-js', $output );
		$this->assertStringContainsString( 'anspress-notifications-js', $output );
		$this->assertStringContainsString( 'anspress-theme-js', $output );
		$this->assertMatchesRegularExpression( '/<script.*<\/script>/', $output );
	}
}
