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
		$this->assertEquals( 'Question title &lt; I will get removed I reside at last', ap_sanitize_unslash( 'Question \title < <span>I will get removed</span>     I reside at last' ) );
		$this->assertEquals( 'question', ap_sanitize_unslash( '', true, 'question' ) );
		$this->assertEquals( 'answer', ap_sanitize_unslash( '', false, 'answer' ) );
	}

	/**
	 * @covers ::ap_disable_question_suggestion
	 */
	public function testAPDisableQuestionSuggestion() {
		$this->assertFalse( ap_disable_question_suggestion() );

		add_filter( 'ap_disable_question_suggestion', '__return_true' );
		$this->assertTrue( ap_disable_question_suggestion() );

		add_filter( 'ap_disable_question_suggestion', '__return_false' );
		$this->assertFalse( ap_disable_question_suggestion() );
	}

	/**
	 * @covers ::ap_activity_short_title
	 */
	public function testAPActivityShortTitle() {
		$this->assertEquals( 'asked', ap_activity_short_title( 'new_question' ) );
		$this->assertEquals( 'approved', ap_activity_short_title( 'approved_question' ) );
		$this->assertEquals( 'approved', ap_activity_short_title( 'approved_answer' ) );
		$this->assertEquals( 'answered', ap_activity_short_title( 'new_answer' ) );
		$this->assertEquals( 'deleted answer', ap_activity_short_title( 'delete_answer' ) );
		$this->assertEquals( 'restored question', ap_activity_short_title( 'restore_question' ) );
		$this->assertEquals( 'restored answer', ap_activity_short_title( 'restore_answer' ) );
		$this->assertEquals( 'commented', ap_activity_short_title( 'new_comment' ) );
		$this->assertEquals( 'deleted comment', ap_activity_short_title( 'delete_comment' ) );
		$this->assertEquals( 'commented on answer', ap_activity_short_title( 'new_comment_answer' ) );
		$this->assertEquals( 'edited question', ap_activity_short_title( 'edit_question' ) );
		$this->assertEquals( 'edited answer', ap_activity_short_title( 'edit_answer' ) );
		$this->assertEquals( 'edited comment', ap_activity_short_title( 'edit_comment' ) );
		$this->assertEquals( 'edited comment on answer', ap_activity_short_title( 'edit_comment_answer' ) );
		$this->assertEquals( 'selected answer', ap_activity_short_title( 'answer_selected' ) );
		$this->assertEquals( 'unselected answer', ap_activity_short_title( 'answer_unselected' ) );
		$this->assertEquals( 'updated status', ap_activity_short_title( 'status_updated' ) );
		$this->assertEquals( 'selected as best answer', ap_activity_short_title( 'best_answer' ) );
		$this->assertEquals( 'unselected as best answer', ap_activity_short_title( 'unselected_best_answer' ) );
		$this->assertEquals( 'changed status', ap_activity_short_title( 'changed_status' ) );
	}

	/**
	 * @covers ::ap_activate_addon
	 * @covers ::ap_deactivate_addon
	 */
	public function testAPActivateAddon() {
		// For default addons activate behaviour.
		$this->assertArrayHasKey( 'categories.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'email.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'reputation.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'akismet.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'avatar.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'buddypress.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'notifications.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'profile.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'recaptcha.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'syntaxhighlighter.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'tags.php', get_option( 'anspress_addons' ) );

		// For addons activate behaviour test.
		ap_activate_addon( 'akismet.php' );
		ap_activate_addon( 'avatar.php' );
		ap_activate_addon( 'buddypress.php' );
		ap_activate_addon( 'notifications.php' );
		ap_activate_addon( 'profile.php' );
		ap_activate_addon( 'recaptcha.php' );
		ap_activate_addon( 'syntaxhighlighter.php' );
		ap_activate_addon( 'tags.php' );

		// Test begins.
		$this->assertArrayHasKey( 'categories.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'email.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'reputation.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'akismet.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'avatar.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'buddypress.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'notifications.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'profile.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'recaptcha.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'syntaxhighlighter.php', get_option( 'anspress_addons' ) );
		$this->assertArrayHasKey( 'tags.php', get_option( 'anspress_addons' ) );

		// For addons deactivate behaviour test.
		ap_deactivate_addon( 'categories.php' );
		ap_deactivate_addon( 'email.php' );
		ap_deactivate_addon( 'reputation.php' );
		ap_deactivate_addon( 'akismet.php' );
		ap_deactivate_addon( 'avatar.php' );
		ap_deactivate_addon( 'buddypress.php' );
		ap_deactivate_addon( 'notifications.php' );
		ap_deactivate_addon( 'profile.php' );
		ap_deactivate_addon( 'recaptcha.php' );
		ap_deactivate_addon( 'syntaxhighlighter.php' );
		ap_deactivate_addon( 'tags.php' );

		// Test begins.
		$this->assertArrayNotHasKey( 'categories.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'email.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'reputation.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'akismet.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'avatar.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'buddypress.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'notifications.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'profile.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'recaptcha.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'syntaxhighlighter.php', get_option( 'anspress_addons' ) );
		$this->assertArrayNotHasKey( 'tags.php', get_option( 'anspress_addons' ) );
	}

	/**
	 * @covers ::ap_main_pages
	 */
	public function testAPMainPages() {
		$this->assertArrayHasKey( 'base_page', ap_main_pages() );
		$this->assertArrayHasKey( 'ask_page', ap_main_pages() );
		$this->assertArrayHasKey( 'user_page', ap_main_pages() );
		$this->assertArrayHasKey( 'categories_page', ap_main_pages() );
		$this->assertArrayHasKey( 'tags_page', ap_main_pages() );
		$this->assertArrayHasKey( 'activities_page', ap_main_pages() );
	}

	/**
	 * @covers ::ap_main_pages_id
	 */
	public function testAPMainPagesID() {
		$main_pages = array_keys( ap_main_pages() );
		foreach ( $main_pages as $slug ) {
			$pages_id[ $slug ] = ap_opt( $slug );
		}
		$this->assertEquals( $pages_id, ap_main_pages_id() );
	}

	/**
	 * @covers ::ap_total_published_questions
	 */
	public function testAPTotalPublishedQuestions() {
		$this->assertEquals( 0, ap_total_published_questions() );
		$this->insert_question( 'First Question', 'First Content' );
		$this->assertEquals( 1, ap_total_published_questions() );

		$this->insert_question( 'Later Questions', 'Later Contents' );
		$this->insert_question( 'Later Questions', 'Later Contents' );
		$this->insert_question( 'Later Questions', 'Later Contents' );
		$this->insert_question( 'Later Questions', 'Later Contents' );
		$this->assertEquals( 5, ap_total_published_questions() );
	}

	/**
	 * @covers ::ap_total_posts_count
	 */
	public function testAPTotalQuestionCount() {
		// For question count test.
		$total_posts = ap_total_posts_count();
		$this->assertEquals( 0, $total_posts->publish );
		$this->assertEquals( 0, $total_posts->total );

		$this->insert_question( 'First Question', 'First Content' );
		$total_posts = ap_total_posts_count();
		$this->assertEquals( 1, $total_posts->publish );
		$this->assertEquals( 1, $total_posts->total );

		$this->insert_question( 'Later Questions', 'Later Contents' );
		$this->insert_question( 'Later Questions', 'Later Contents' );
		$this->insert_question( 'Later Questions', 'Later Contents' );
		$this->insert_question( 'Later Questions', 'Later Contents' );
		$total_posts = ap_total_posts_count();
		$this->assertEquals( 5, $total_posts->publish );
		$this->assertEquals( 5, $total_posts->total );
	}

	/**
	 * @covers ::ap_total_posts_count
	 */
	public function testAPTotalAnswerCount() {
		// For answer count test.
		$total_posts = ap_total_posts_count( 'answer' );
		$this->assertEquals( 0, $total_posts->publish );
		$this->assertEquals( 0, $total_posts->total );

		$this->insert_answer( 'First answer', 'First Content' );
		$total_posts = ap_total_posts_count( 'answer' );
		$this->assertEquals( 1, $total_posts->publish );
		$this->assertEquals( 1, $total_posts->total );

		$this->insert_answer( 'Later answers', 'Later Contents' );
		$this->insert_answer( 'Later answers', 'Later Contents' );
		$this->insert_answer( 'Later answers', 'Later Contents' );
		$this->insert_answer( 'Later answers', 'Later Contents' );
		$total_posts = ap_total_posts_count( 'answer' );
		$this->assertEquals( 5, $total_posts->publish );
		$this->assertEquals( 5, $total_posts->total );
	}

	/**
	 * @covers ::ap_total_posts_count
	 */
	public function testAPTotalQuestionAnswerCount() {
		// For question count test.
		$total_posts = ap_total_posts_count( '' );
		$this->assertEquals( 0, $total_posts->publish );
		$this->assertEquals( 0, $total_posts->total );

		$this->insert_answer( 'First answer', 'First Content' );
		$total_posts = ap_total_posts_count( '' );
		$this->assertEquals( 2, $total_posts->publish );
		$this->assertEquals( 2, $total_posts->total );

		$this->insert_answer( 'Later answers', 'Later Contents' );
		$this->insert_answer( 'Later answers', 'Later Contents' );
		$this->insert_answer( 'Later answers', 'Later Contents' );
		$this->insert_answer( 'Later answers', 'Later Contents' );
		$total_posts = ap_total_posts_count( '' );
		$this->assertEquals( 10, $total_posts->publish );
		$this->assertEquals( 10, $total_posts->total );
	}

	/**
	 * @covers ::ap_get_addons
	 */
	public function testAPGetAddons() {
		$this->assertArrayHasKey( 'categories.php', ap_get_addons() );
		$this->assertArrayHasKey( 'email.php', ap_get_addons() );
		$this->assertArrayHasKey( 'reputation.php', ap_get_addons() );
		$this->assertArrayHasKey( 'akismet.php', ap_get_addons() );
		$this->assertArrayHasKey( 'avatar.php', ap_get_addons() );
		$this->assertArrayHasKey( 'buddypress.php', ap_get_addons() );
		$this->assertArrayHasKey( 'notifications.php', ap_get_addons() );
		$this->assertArrayHasKey( 'profile.php', ap_get_addons() );
		$this->assertArrayHasKey( 'recaptcha.php', ap_get_addons() );
		$this->assertArrayHasKey( 'syntaxhighlighter.php', ap_get_addons() );
		$this->assertArrayHasKey( 'tags.php', ap_get_addons() );
	}

	/**
	 * @covers ::ap_get_addon
	 */
	public function testAPGetAddon() {
		$addon_arrays = array(
			'categories.php',
			'email.php',
			'reputation.php',
			'akismet.php',
			'avatar.php',
			'buddypress.php',
			'notifications.php',
			'profile.php',
			'recaptcha.php',
			'syntaxhighlighter.php',
			'tags.php',
		);

		foreach ( $addon_arrays as $addon ) {
			$get_addon = ap_get_addon( $addon );
			$this->assertEquals( $addon, $get_addon['id'] );
			$this->assertEquals( 'addon-' . str_replace( '.php', '', $addon ), $get_addon['class'] );

			// Test for key is available or not.
			$this->assertArrayHasKey( 'name', $get_addon );
			$this->assertArrayHasKey( 'description', $get_addon );
			$this->assertArrayHasKey( 'path', $get_addon );
			$this->assertArrayHasKey( 'pro', $get_addon );
			$this->assertArrayHasKey( 'active', $get_addon );
			$this->assertArrayHasKey( 'id', $get_addon );
			$this->assertArrayHasKey( 'class', $get_addon );
		}
	}

	/**
	 * @covers ::ap_in_array_r
	 */
	public function testAPInArrayR() {
		$this->assertTrue( ap_in_array_r( 'question', array( 'question', 'answer', 'comment' ) ) );
		$this->assertTrue( ap_in_array_r( 'answer', array( 'question', 'answer', 'comment' ) ) );
		$this->assertFalse( ap_in_array_r( 'comment', array( 'question', 'answer' ) ) );
	}

	/**
	 * @covers ::ap_truncate_chars
	 */
	public function testAPTruncateChars() {
		$this->assertEquals( 'Question title', ap_truncate_chars( '<h1>Question title</h1>' ) );
		$this->assertEquals( '', ap_truncate_chars( '<style>body{background-color:white;}</style>' ) );
		$this->assertEquals( 'Question title Answer', ap_truncate_chars( 'Question title	Answer' ) );
		$this->assertEquals( 'The quick brown fox jumps over the lazy dog', ap_truncate_chars( '<h1>The quick <span style="color: brown;">brown</span> fox jumps over the lazy dog</h1>' ) );
		$this->assertEquals( 'Question title...', ap_truncate_chars( 'Question title I\m', 14 ) );
		$this->assertEquals( 'Question title;;;', ap_truncate_chars( '<h1>Question title I\m</h1>', 14, ';;;' ) );
	}

	/**
	 * @covers ::ap_response_message
	 */
	public function testAPResponseMessage() {
		$this->assertEquals(
			[
				'type'    => 'success',
				'message' => 'Success',
			],
			ap_response_message( 'success' )
		);
		$this->assertEquals( 'Success', ap_response_message( 'success', true ) );

		$this->assertEquals(
			[
				'type'    => 'error',
				'message' => 'Something went wrong, last action failed.',
			],
			ap_response_message( 'something_wrong' )
		);
		$this->assertEquals( 'Something went wrong, last action failed.', ap_response_message( 'something_wrong', true ) );

		$this->assertEquals(
			[
				'type'    => 'success',
				'message' => 'Comment updated successfully.',
			],
			ap_response_message( 'comment_edit_success' )
		);
		$this->assertEquals( 'Comment updated successfully.', ap_response_message( 'comment_edit_success', true ) );

		$this->assertEquals(
			[
				'type'    => 'warning',
				'message' => 'You cannot vote on your own question or answer.',
			],
			ap_response_message( 'cannot_vote_own_post' )
		);
		$this->assertEquals( 'You cannot vote on your own question or answer.', ap_response_message( 'cannot_vote_own_post', true ) );

		$this->assertEquals(
			[
				'type'    => 'warning',
				'message' => 'You do not have permission to view private posts.',
			],
			ap_response_message( 'no_permission_to_view_private' )
		);
		$this->assertEquals( 'You do not have permission to view private posts.', ap_response_message( 'no_permission_to_view_private', true ) );

		$this->assertEquals(
			[
				'type'    => 'error',
				'message' => 'Please check captcha field and resubmit it again.',
			],
			ap_response_message( 'captcha_error' )
		);
		$this->assertEquals( 'Please check captcha field and resubmit it again.', ap_response_message( 'captcha_error', true ) );

		$this->assertEquals(
			[
				'type'    => 'success',
				'message' => 'Image uploaded successfully',
			],
			ap_response_message( 'post_image_uploaded' )
		);
		$this->assertEquals( 'Image uploaded successfully', ap_response_message( 'post_image_uploaded', true ) );

		$this->assertEquals(
			[
				'type'    => 'success',
				'message' => 'Answer has been deleted permanently',
			],
			ap_response_message( 'answer_deleted_permanently' )
		);
		$this->assertEquals( 'Answer has been deleted permanently', ap_response_message( 'answer_deleted_permanently', true ) );

		$this->assertEquals(
			[
				'type'    => 'warning',
				'message' => 'You have already attached maximum numbers of allowed uploads.',
			],
			ap_response_message( 'upload_limit_crossed' )
		);
		$this->assertEquals( 'You have already attached maximum numbers of allowed uploads.', ap_response_message( 'upload_limit_crossed', true ) );

		$this->assertEquals(
			[
				'type'    => 'success',
				'message' => 'Your profile has been updated successfully.',
			],
			ap_response_message( 'profile_updated_successfully' )
		);
		$this->assertEquals( 'Your profile has been updated successfully.', ap_response_message( 'profile_updated_successfully', true ) );

		$this->assertEquals(
			[
				'type'    => 'warning',
				'message' => 'Voting down is disabled.',
			],
			ap_response_message( 'voting_down_disabled' )
		);
		$this->assertEquals( 'Voting down is disabled.', ap_response_message( 'voting_down_disabled', true ) );

		$this->assertEquals(
			[
				'type'    => 'warning',
				'message' => 'You cannot vote on restricted posts',
			],
			ap_response_message( 'you_cannot_vote_on_restricted' )
		);
		$this->assertEquals( 'You cannot vote on restricted posts', ap_response_message( 'you_cannot_vote_on_restricted', true ) );
	}

	/**
	 * @covers ::ap_total_solved_questions
	 */
	public function testAPTotalSolvedQuestions() {
		$this->assertEquals( 0, ap_total_solved_questions() );
		$id = $this->insert_answer();
		ap_insert_qameta(
			$id->q,
			array(
				'selected_id'  => $id->a,
				'last_updated' => current_time( 'mysql' ),
				'closed'       => 1,
			)
		);
		$this->assertNotEquals( 0, ap_total_solved_questions() );
		$this->assertEquals( 1, ap_total_solved_questions() );

		$question = $this->insert_question();
		$this->assertEquals( 1, ap_total_solved_questions() );
		$answer = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Question content',
				'post_parent'  => $question,
			)
		);
		$this->assertEquals( 1, ap_total_solved_questions() );
		ap_insert_qameta(
			$question,
			array(
				'selected_id'  => $answer,
				'last_updated' => current_time( 'mysql' ),
				'closed'       => 1,
			)
		);
		$this->assertEquals( 2, ap_total_solved_questions() );
		ap_insert_qameta(
			$question,
			array(
				'selected_id'  => '',
				'last_updated' => current_time( 'mysql' ),
				'closed'       => 1,
			)
		);
		$this->assertEquals( 1, ap_total_solved_questions() );
	}

	/**
	 * @covers ::ap_questions_answer_ids
	 */
	public function testAPQuestionsAnswerIDs() {
		$question = $this->insert_question();
		$this->assertEquals( [], ap_questions_answer_ids( $question ) );
		$answer_arr = array(
			'post_title'   => 'Question title',
			'post_type'    => 'answer',
			'post_status'  => 'publish',
			'post_content' => 'Question content',
			'post_parent'  => $question,
		);
		$answer = $this->factory()->post->create( $answer_arr );
		$this->assertEquals( [ $answer ], ap_questions_answer_ids( $question ) );
		$answer1 = $this->factory()->post->create( $answer_arr );
		$this->assertEquals( [ $answer, $answer1 ], ap_questions_answer_ids( $question ) );
		$answer2 = $this->factory()->post->create( $answer_arr );
		$this->assertEquals( [ $answer, $answer1, $answer2 ], ap_questions_answer_ids( $question ) );
		wp_delete_post( $answer2 );
		$this->assertNotEquals( [ $answer, $answer1, $answer2 ], ap_questions_answer_ids( $question ) );
		$this->assertEquals( [ $answer, $answer1 ], ap_questions_answer_ids( $question ) );
	}

	/**
	 * @covers ::ap_search_array
	 */
	public function testAPSearchArray() {
		$this->assertEquals(
			[
				[
					'post_title'   => 'Question title',
					'post_content' => 'Question content',
				]
			],
			ap_search_array(
				array(
					'post_title'   => 'Question title',
					'post_content' => 'Question content',
				),
				'post_title',
				'Question title'
			)
		);
		$this->assertEquals(
			[
				[
					'post_title'   => 'Question title',
					'post_content' => 'Question content',
				]
			],
			ap_search_array(
				array(
					'question' => array(
						'post_title'   => 'Question title',
						'post_content' => 'Question content',
					),
					'answer'    => array(
						'post_title'   => 'Answer title',
						'post_content' => 'Answer content',
					),
				),
				'post_title',
				'Question title'
			)
		);
		$this->assertEquals(
			[
				[
					'post_title'   => 'Answer title',
					'post_content' => 'Answer content',
				]
			],
			ap_search_array(
				array(
					'question' => array(
						'post_title'   => 'Question title',
						'post_content' => 'Question content',
					),
					'answer'   => array(
						'post_title'   => 'Answer title',
						'post_content' => 'Answer content',
					),
				),
				'post_title',
				'Answer title'
			)
		);
		$this->assertEquals(
			[
				[
					'title'   => 'Question title',
					'content' => 'Question content',
				]
			],
			ap_search_array(
				array(
					'question' => array(
						'post_title'   => 'Question title',
						'post_content' => 'Question content',
					),
					'answer'   => array(
						'post_title'   => 'Answer title',
						'post_content' => 'Answer content',
					),
					'qa'       => array(
						array(
							'title'   => 'Question title',
							'content' => 'Question content',
						),
						array(
							'title'   => 'Answer title',
							'content' => 'Answer content',
						),
					)
				),
				'title',
				'Question title'
			)
		);
		$this->assertEquals(
			[
				[
					'title'   => 'Answer title',
					'content' => 'Answer content',
				]
			],
			ap_search_array(
				array(
					'question' => array(
						'post_title'   => 'Question title',
						'post_content' => 'Question content',
					),
					'answer'   => array(
						'post_title'   => 'Answer title',
						'post_content' => 'Answer content',
					),
					'qa'       => array(
						array(
							'title'   => 'Question title',
							'content' => 'Question content',
						),
						array(
							'title'   => 'Answer title',
							'content' => 'Answer content',
						),
					)
				),
				'title',
				'Answer title'
			)
		);
	}

	/**
	 * @covers ::ap_find_duplicate_post
	 */
	public function testAPFindSuplicatePost() {
		// Test for question post type.
		$question = $this->factory->post->create(
			array(
				'post_type'    => 'question',
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
			)
		);
		$post     = get_post( $question );
		$this->assertNotEmpty( ap_find_duplicate_post( $post->post_content ) );
		$this->assertEmpty( ap_find_duplicate_post( 'Question title' ) );
		$this->assertNotEmpty( ap_find_duplicate_post( 'Question content' ) );
		$this->assertIsInt( ap_find_duplicate_post( $post->post_content ) );
		$this->assertFalse( ap_find_duplicate_post( 'Question title' ) );

		// Test for answer post type.
		$answer = $this->factory->post->create(
			array(
				'post_type'    => 'answer',
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_parent'  => $question,
			)
		);
		$post     = get_post( $answer );
		$this->assertNotEmpty( ap_find_duplicate_post( $post->post_content, 'answer' ) );
		$this->assertEmpty( ap_find_duplicate_post( 'Answer title', 'answer' ) );
		$this->assertNotEmpty( ap_find_duplicate_post( 'Answer content', 'answer' ) );
		$this->assertIsInt( ap_find_duplicate_post( $post->post_content, 'answer' ) );
		$this->assertFalse( ap_find_duplicate_post( 'Answer title', 'answer' ) );

		// Test for question id pass.
		$id   = $this->insert_answer( 'Answer title', 'Answer content' );
		$post = get_post( $id->a );
		$this->assertNotEmpty( ap_find_duplicate_post( $post->post_content, 'answer', $id->q ) );
		$this->assertEmpty( ap_find_duplicate_post( 'Answer title', 'answer', $id->q ) );
		$this->assertNotEmpty( ap_find_duplicate_post( 'Answer content', 'answer', $id->q ) );
		$this->assertIsInt( ap_find_duplicate_post( $post->post_content, 'answer', $id->q ) );
		$this->assertFalse( ap_find_duplicate_post( 'Answer title', 'answer', $id->q ) );
	}

}
