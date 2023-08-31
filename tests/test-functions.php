<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFunctions extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	/**
	 * @covers ::ap_get_short_link
	 */
	public function testApGetShortLink() {
		$id    = $this->insert_question();
		$url   = ap_get_short_link( [ 'ap_q' => $id ] );
		$query = wp_parse_args( wp_parse_url( $url )['query'] );
		$this->assertEquals( 'shortlink', $query['ap_page'] );
		$this->go_to( ap_get_short_link( [ 'ap_q' => $id ] ) );
		$this->assertEquals( 'shortlink', get_query_var( 'ap_page' ) );
	}

	/**
	 * @covers ::ap_base_page_slug
	 */
	public function testApBasePageSlug() {
		$id = $this->factory->post->create(
			[
				'post_type' => 'page',
				'post_name' => 'qqqqslug',
			]
		);
		ap_opt( 'base_page', $id );
		$this->assertEquals( 'qqqqslug', ap_base_page_slug() );
	}

	/**
	 * @covers ::ap_base_page_link
	 */
	public function testApBasePageLink() {
		ap_opt( 'base_page', '' );
		$this->assertEquals( home_url( '/questions/' ), ap_base_page_link() );
		$id = $this->factory->post->create(
			[
				'post_type' => 'page',
				'post_name' => 'qqqqslugqq',
			]
		);
		ap_opt( 'base_page', $id );
		$this->assertEquals( get_permalink( $id ), ap_base_page_link() );
	}

	/**
	 * @covers ::ap_get_theme_location
	 */
	public function testApGetThemeLocation() {
		$file       = 'test.php';
		$template_f = get_template_directory() . '/anspress/';
		if ( ! file_exists( $template_f ) ) {
			mkdir( $template_f );
		}

		if ( file_exists( $template_f . $file ) ) {
			unlink( $template_f . $file );
		}

		$plugin_path = ABSPATH . 'testplugin';

		$this->assertEquals( ANSPRESS_THEME_DIR . '/' . $file, ap_get_theme_location( $file ) );
		$this->assertEquals( $plugin_path . '/templates/' . $file, ap_get_theme_location( $file, $plugin_path ) );

		file_put_contents( $template_f . $file, '<?php' );
		$this->assertEquals( $template_f . $file, ap_get_theme_location( $file ) );
	}

	/**
	 * @covers :: ap_get_theme_url
	 */
	public function testApGetThemeUrl() {
		$file = 'test.css';

		$template_f = get_template_directory() . '/anspress/';
		if ( ! file_exists( $template_f ) ) {
			mkdir( $template_f );
		}
		if ( file_exists( $template_f . $file ) ) {
			unlink( $template_f . $file );
		}

		$plugin_url = home_url( 'wp-content/plugins/testplugin/' );

		$this->assertEquals( ANSPRESS_URL . 'templates/' . $file, ap_get_theme_url( $file, false, false ) );
		$this->assertEquals( $plugin_url . 'templates/' . $file, ap_get_theme_url( $file, $plugin_url, false ) );

		file_put_contents( $template_f . $file, ' ' );
		$this->assertEquals( get_template_directory_uri() . '/anspress/' . $file, ap_get_theme_url( $file, false, false ) );
	}

	/**
	 * @covers ::is_question
	 */
	public function testIsQuestion() {
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertTrue( is_question() );
	}

	/**
	 * @covers ::is_ask
	 */
	public function testIsAsk() {
		$this->assertFalse( is_ask() );
		$id = $this->factory->post->create(
			[
				'post_type'  => 'page',
				'post_name'  => 'asksssd3432s',
				'post_title' => 'ask page',
			]
		);
		ap_opt( 'ask_page', $id );
		ap_opt( 'ask_page_id', 'asksssd3432s' );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertTrue( is_ask() );
	}

	/**
	 * @covers ::get_question_id
	 */
	public function testGetQuestionID() {
		$this->assertEquals( 0, get_question_id() );
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertEquals( $id, get_question_id() );
	}

	/**
	 * @covers ::ap_human_time
	 */
	public function testApHumanTime() {
		$this->assertEquals( '1 second ago', ap_human_time( current_time( 'mysql' ), false ) );
		$this->assertEquals( '1 second ago', ap_human_time( current_time( 'U' ) ) );
		$this->assertEquals( date_i18n( get_option( 'date_format' ), current_time( 'U' ) ), ap_human_time( current_time( 'U' ), true, 0 ) );
		$this->assertEquals( date_i18n( 'M Y', current_time( 'U' ) ), ap_human_time( current_time( 'U' ), true, 0, 'M Y' ) );
	}

	/**
	 * @covers ::ap_is_user_answered
	 */
	public function testApIsUserAnswered() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		$this->assertFalse( ap_is_user_answered( $id->q, get_current_user_id() ) );
		$id = $this->insert_answer( '', '', get_current_user_id() );
		$this->assertTrue( ap_is_user_answered( $id->q, get_current_user_id() ) );
		wp_delete_post( $id->a, true );
		$this->assertFalse( ap_is_user_answered( $id->q, get_current_user_id() ) );
		$id = $this->insert_answer( '', '', get_current_user_id() );
		wp_delete_post( $id->a );
		$this->assertFalse( ap_is_user_answered( $id->q, get_current_user_id() ) );
	}

	/**
	 * @covers ::ap_answers_link
	 */
	public function testApAnswersLink() {
		$id = $this->insert_answer();
		$this->assertEquals( get_permalink( $id->q ) . '#answers', ap_answers_link( $id->q ) );
	}

	/**
	 * @covers ::ap_post_edit_link
	 */
	public function testApPostEditLink() {
		$id    = $this->insert_answer();
		$nonce = wp_create_nonce( 'edit-post-' . $id->q );
		$this->assertEquals( ap_get_link_to( 'ask' ) . '?id=' . $id->q . '&__nonce=' . $nonce, ap_post_edit_link( $id->q ) );
		$nonce = wp_create_nonce( 'edit-post-' . $id->a );
		$this->assertEquals( ap_get_link_to( 'edit' ) . '?id=' . $id->a . '&__nonce=' . $nonce, ap_post_edit_link( $id->a ) );
	}

	/**
	 * @covers ::sanitize_comma_delimited
	 */
	public function testSanitizeCommaDelimited() {
		$string = '122, sfdsf<87, jhj,, 87&&,00000, &amp;';
		$this->assertEquals( '122,0,0,87,0,0', sanitize_comma_delimited( $string ) );

		$array = array(
			'<?pgp223',
			'shdshds((*8',
			'77878',
			'8 8',
			'&&&&***',
		);

		$this->assertEquals( '0,0,77878,8,0', sanitize_comma_delimited( $array ) );
		$this->assertEquals(
			'"dsfsdf??","function(){","sdsd","sdsdsds86789"', sanitize_comma_delimited(
				array(
					'dsfsdf<>??',
					'function(){',
					',,,""sdsd',
					'\'sdsdsds86789',
				), 'str'
			)
		);
	}

	/**
	 * @covers ::ap_form_allowed_tags
	 */
	public function testApFormAllowedTags() {
		$tags = ap_form_allowed_tags();
		$this->assertEquals(
			[
				'style' => array(
					'align' => true,
				),
				'title' => true,
			], $tags['p']
		);

		$this->assertEquals(
			[
				'style' => array(
					'align' => true,
				),
			], $tags['span']
		);

		$this->assertEquals(
			[
				'href'  => true,
				'title' => true,
			], $tags['a']
		);
		$this->assertEquals( [], $tags['br'] );
		$this->assertEquals( [], $tags['em'] );
		$this->assertEquals(
			[
				'style' => array(
					'align' => true,
				),
			], $tags['strong']
		);
		$this->assertEquals( [], $tags['pre'] );
		$this->assertEquals( [], $tags['code'] );
		$this->assertEquals( [], $tags['blockquote'] );
		$this->assertEquals(
			[
				'style' => array(
					'align' => true,
				),
				'src'   => true,
			], $tags['img']
		);
		$this->assertEquals( [], $tags['ul'] );
		$this->assertEquals( [], $tags['ol'] );
		$this->assertEquals( [], $tags['li'] );
		$this->assertEquals( [], $tags['del'] );
	}

	/**
	 * @covers ::ap_is_addon_active
	 */
	public function testIsAddonActive() {
		// Default addons enabled check on plugin activation.
		$this->assertTrue( ap_is_addon_active( 'categories.php' ) );
		$this->assertTrue( ap_is_addon_active( 'email.php' ) );
		$this->assertTrue( ap_is_addon_active( 'reputation.php' ) );

		// Check for other addons is not enabled.
		$this->assertFalse( ap_is_addon_active( 'akismet.php' ) );
		$this->assertFalse( ap_is_addon_active( 'avatar.php' ) );
		$this->assertFalse( ap_is_addon_active( 'buddypress.php' ) );
		$this->assertFalse( ap_is_addon_active( 'notifications.php' ) );
		$this->assertFalse( ap_is_addon_active( 'profile.php' ) );
		$this->assertFalse( ap_is_addon_active( 'recaptcha.php' ) );
		$this->assertFalse( ap_is_addon_active( 'syntaxhighlighter.php' ) );
		$this->assertFalse( ap_is_addon_active( 'tags.php' ) );

		// Check for addons enabled.
		ap_activate_addon( 'akismet.php' );
		ap_activate_addon( 'avatar.php' );
		ap_activate_addon( 'buddypress.php' );
		ap_activate_addon( 'notifications.php' );
		ap_activate_addon( 'profile.php' );
		ap_activate_addon( 'recaptcha.php' );
		ap_activate_addon( 'syntaxhighlighter.php' );
		ap_activate_addon( 'tags.php' );

		// Checks.
		$this->assertTrue( ap_is_addon_active( 'akismet.php' ) );
		$this->assertTrue( ap_is_addon_active( 'avatar.php' ) );
		$this->assertTrue( ap_is_addon_active( 'buddypress.php' ) );
		$this->assertTrue( ap_is_addon_active( 'notifications.php' ) );
		$this->assertTrue( ap_is_addon_active( 'profile.php' ) );
		$this->assertTrue( ap_is_addon_active( 'recaptcha.php' ) );
		$this->assertTrue( ap_is_addon_active( 'syntaxhighlighter.php' ) );
		$this->assertTrue( ap_is_addon_active( 'tags.php' ) );
	}

	/**
	 * @covers ::ap_is_cpt
	 */
	public function testAPIsCPT() {
		// Test for post post type.
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_content' => 'Post Content',
			)
		);
		$post = get_post( $post_id );
		$this->assertNotEquals( $post->post_type, ap_is_cpt( $post ) );

		// Test for page post type.
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Page title',
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => 'Page Content',
			)
		);
		$post = get_post( $page_id );
		$this->assertNotEquals( $post->post_type, ap_is_cpt( $post ) );

		// Test for question post type.
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$post = get_post( $question_id );
		$this->assertEquals( $post->post_type, ap_is_cpt( $post ) );

		// Test for answer post type.
		$answer_id = $this->insert_answer( '', '', get_current_user_id() );
		$post = get_post( $answer_id->a );
		$this->assertEquals( $post->post_type, ap_is_cpt( $post ) );
	}

	/**
	 * @covers ::ap_to_dot_notation
	 */
	public function testAPToDotNotation() {
		$this->assertEquals( 'question.answer', ap_to_dot_notation( 'question..answer..' ) );
		$this->assertEquals( 'question.answer', ap_to_dot_notation( 'question..answer' ) );
		$this->assertEquals( 'question.answer', ap_to_dot_notation( 'question..answer..........' ) );
		$this->assertEquals( 'question.answer.comment', ap_to_dot_notation( 'question..answer.comment' ) );
		$this->assertEquals( 'question.answer', ap_to_dot_notation( 'question[answer]' ) );
	}

	/**
	 * @covers ::ap_array_insert_after
	 */
	public function testAPArrayInsertAfter() {
		// Test for appending at last.
		$this->assertEquals(
			array(
				'question' => 'Question title',
				'answer'   => 'Answer title',
				'comment'  => 'Comment content',
			),
			ap_array_insert_after(
				array(
					'question' => 'Question title',
					'answer'   => 'Answer title',
				),
				'comment',
				array(
					'comment'  => 'Comment content',
				)
			)
		);

		// Test for appending at middle.
		$this->assertEquals(
			array(
				'question' => 'Question title',
				'answer'   => 'Answer title',
				'comment'  => 'Comment content',
			),
			ap_array_insert_after(
				array(
					'question' => 'Question title',
					'comment'  => 'Comment content',
				),
				'answer',
				array(
					'answer'   => 'Answer title',
				)
			)
		);
	}


	/**
	 * @covers ::ap_get_active_addons
	 */
	public function testAPGetActiveAddons() {
		// For default addon active check.
		$this->assertArrayHasKey( 'categories.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'email.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'reputation.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'akismet.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'avatar.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'buddypress.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'notifications.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'profile.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'recaptcha.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'syntaxhighlighter.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'tags.php', ap_get_active_addons() );

		// Addon activate and check.
		ap_activate_addon( 'akismet.php' );
		ap_activate_addon( 'avatar.php' );
		ap_activate_addon( 'buddypress.php' );
		ap_activate_addon( 'notifications.php' );
		ap_activate_addon( 'profile.php' );
		ap_activate_addon( 'recaptcha.php' );
		ap_activate_addon( 'syntaxhighlighter.php' );
		ap_activate_addon( 'tags.php' );

		// Default addon deactivate and check.
		ap_deactivate_addon( 'categories.php' );
		ap_deactivate_addon( 'email.php' );
		ap_deactivate_addon( 'reputation.php' );

		// Checks.
		$this->assertArrayNotHasKey( 'categories.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'email.php', ap_get_active_addons() );
		$this->assertArrayNotHasKey( 'reputation.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'akismet.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'avatar.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'buddypress.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'notifications.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'profile.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'recaptcha.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'syntaxhighlighter.php', ap_get_active_addons() );
		$this->assertArrayHasKey( 'tags.php', ap_get_active_addons() );
	}

	/**
	 * @covers ::ap_remove_stop_words
	 * @covers ::ap_remove_stop_words_post_name
	 */
	public function testAPRemoveStopWords() {
		$this->assertEquals( 'The quick brown fox jumps   lazy dog', ap_remove_stop_words( 'The quick brown fox jumps over the lazy dog' ) );
		$this->assertEquals( 'Top   world', ap_remove_stop_words( 'Top of the world' ) );
		$this->assertEquals( ' quick brown fox jumps    lazy dog', ap_remove_stop_words( 'a quick brown fox jumps over the very lazy dog' ) );

		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'The quick brown fox jumps over the lazy dog',
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_content' => 'Post Content',
				'post_name'    => 'the-quick-brown-fox-jumps-over-the-lazy-dog'
			)
		);
		$post    = get_post( $post_id );
		$this->assertEquals( 'the-quick-brown-fox-jumps-over-the-lazy-dog', ap_remove_stop_words_post_name( $post->post_name ) );
		ap_opt( 'keep_stop_words', false );
		$this->assertEquals( '-quick-brown-fox-jumps---lazy-dog', ap_remove_stop_words_post_name( $post->post_name ) );

		ap_opt( 'keep_stop_words', true );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Top of the world',
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_content' => 'Post Content',
				'post_name'    => 'top-of-the-world'
			)
		);
		$post    = get_post( $post_id );
		$this->assertEquals( 'top-of-the-world', ap_remove_stop_words_post_name( $post->post_name ) );
		ap_opt( 'keep_stop_words', false );
		$this->assertEquals( 'top---world', ap_remove_stop_words_post_name( $post->post_name ) );

		ap_opt( 'keep_stop_words', true );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'a quick brown fox jumps over the very lazy dog',
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_content' => 'Post Content',
				'post_name'    => 'a-quick-brown-fox-jumps-over-the-very-lazy-dog'
			)
		);
		$post    = get_post( $post_id );
		$this->assertEquals( 'a-quick-brown-fox-jumps-over-the-very-lazy-dog', ap_remove_stop_words_post_name( $post->post_name ) );
		ap_opt( 'keep_stop_words', false );
		$this->assertEquals( '-quick-brown-fox-jumps----lazy-dog', ap_remove_stop_words_post_name( $post->post_name ) );
	}

	/**
	 * @covers ::ap_short_num
	 */
	public function testAPIsShortNum() {
		$this->assertEquals( '5.00K', ap_short_num( '5000' ) );
		$this->assertEquals( '5.05K', ap_short_num( '5050' ) );
		$this->assertEquals( '5.005K', ap_short_num( '5005', 3 ) );
		$this->assertEquals( '5.00M', ap_short_num( '5000000' ) );
		$this->assertEquals( '5.05M', ap_short_num( '5050000' ) );
		$this->assertEquals( '5.005M', ap_short_num( '5005000', 3 ) );
		$this->assertEquals( '5.00B', ap_short_num( '5000000000' ) );
		$this->assertEquals( '5.05B', ap_short_num( '5050000000' ) );
		$this->assertEquals( '5.005B', ap_short_num( '5005000000', 3 ) );
	}

	/**
	 * @covers ::ap_highlight_words
	 */
	public function testAPHighlightWords() {
		$this->assertEquals( 'This is <span class="highlight_word">question</span> title', ap_highlight_words( 'This is question title', 'question' ) );
		$this->assertEquals( 'This is <span class="highlight_word">answer</span> title', ap_highlight_words( 'This is answer title', 'answer' ) );
	}

	/**
	 * @covers ::ap_trim_traling_space
	 * @covers ::ap_replace_square_bracket
	 */
	public function testAPTrimTralingSpace() {
		$this->assertEquals( 'Question title', ap_trim_traling_space( '   Question title    ' ) );
		$this->assertEquals( 'Answer title', ap_trim_traling_space( 'Answer title           ' ) );
		$this->assertEquals( '[Question title]', ap_trim_traling_space( '[Question title]' ) );
		$this->assertEquals( '[Answer title]', ap_trim_traling_space( '[Answer title]' ) );
	}

	/**
	 * @covers ::ap_sanitize_unslash
	 */
	public function testAPSanitizeUnslash() {
		$this->assertEquals( 'Questions', ap_sanitize_unslash( 'Question\s' ) );
		$this->assertEquals( 'Answers', ap_sanitize_unslash( 'Answer\s' ) );
	}
}
