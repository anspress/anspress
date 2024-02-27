<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFunctions extends TestCase {

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
		$id = $this->insert_answer();
		$this->assertTrue( true );
		if ( ! \is_multisite() ) {
			$nonce = wp_create_nonce( 'edit-post-' . $id->q );
			$this->assertEquals( ap_get_link_to( 'ask' ) . '?id=' . $id->q . '&__nonce=' . $nonce, ap_post_edit_link( $id->q ) );
		}
		if ( ! \is_multisite() ) {
			$nonce = wp_create_nonce( 'edit-post-' . $id->a );
			$this->assertEquals( ap_get_link_to( 'edit' ) . '?id=' . $id->a . '&__nonce=' . $nonce, ap_post_edit_link( $id->a ) );
		}
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
	 * Filter allowed tags.
	 *
	 * @param string[] $allowed_tags
	 */
	public function allowedTags( $allowed_tags ) {
		$allowed_tags['div'] = [];
		$allowed_tags['section'] = [
			'class' => true,
		];
		$allowed_tags['aside'] = [
			'id'    => true,
			'class' => true,
		];

		return $allowed_tags;
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

		// Test on adding custom tags via filter.
		// Before filter being applied.
		$tags = ap_form_allowed_tags();
		$this->assertFalse( isset( $tags['div'] ) );
		$this->assertFalse( isset( $tags['section'] ) );
		$this->assertFalse( isset( $tags['aside'] ) );

		// After filter applied.
		add_filter( 'ap_allowed_tags', [ $this, 'allowedTags' ] );
		$tags = ap_form_allowed_tags();
		$this->assertTrue( isset( $tags['div'] ) );
		$this->assertTrue( isset( $tags['section'] ) );
		$this->assertTrue( isset( $tags['aside'] ) );
		$this->assertEquals( [], $tags['div'] );
		$this->assertEquals( [ 'class' => true ], $tags['section'] );
		$this->assertEquals( [ 'id' => true, 'class' => true ], $tags['aside'] );

		// After filter removed.
		remove_filter( 'ap_allowed_tags', [ $this, 'allowedTags' ] );
		$tags = ap_form_allowed_tags();
		$this->assertFalse( isset( $tags['div'] ) );
		$this->assertFalse( isset( $tags['section'] ) );
		$this->assertFalse( isset( $tags['aside'] ) );
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
		$this->assertEquals( '', ap_to_dot_notation( '' ) );
		$this->assertEquals( '', ap_to_dot_notation( false ) );
		$this->assertEquals( '', ap_to_dot_notation( '...' ) );
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

		// Additional tests.
		$this->assertEquals( '', ap_sanitize_unslash( '' ) );
		$this->assertEquals( 'Question\s', ap_sanitize_unslash( '', false, 'Question\s' ) );
		$this->assertEquals( 'Question \title < <span>I will get removed</span>     I reside at last', ap_sanitize_unslash( '', false, 'Question \title < <span>I will get removed</span>     I reside at last' ) );
		$this->assertEquals( 'question', ap_sanitize_unslash( '', false, 'question' ) );

		// Test for superglobals.
		$_REQUEST['question'] = 'Question \title < <span>I will get removed</span>     I reside at last';
		$this->assertEquals( 'Question title &lt; I will get removed I reside at last', ap_sanitize_unslash( 'question', 'r' ) );
		$this->assertEquals( 'Question title &lt; I will get removed I reside at last', ap_sanitize_unslash( 'question', 'request' ) );
		$_POST['question'] = 'Question \title < <span>I will get removed</span>     I reside at last';
		$this->assertEquals( 'Question title &lt; I will get removed I reside at last', ap_sanitize_unslash( 'question', 'p' ) );
		$this->assertEquals( 'Question title &lt; I will get removed I reside at last', ap_sanitize_unslash( 'question', 'post' ) );
		$_GET['question'] = 'Question \title < <span>I will get removed</span>     I reside at last';
		$this->assertEquals( 'Question title &lt; I will get removed I reside at last', ap_sanitize_unslash( 'question', 'g' ) );
		$this->assertEquals( 'Question title &lt; I will get removed I reside at last', ap_sanitize_unslash( 'question', 'get' ) );
		unset( $_REQUEST['question'] );
		unset( $_POST['question'] );
		unset( $_GET['question'] );

		// Test for arrays.
		$arr = [];
		$this->assertEquals( '', ap_sanitize_unslash( $arr ) );
		$arr = [];
		$this->assertEquals( 'question', ap_sanitize_unslash( $arr, false, 'question' ) );
		$arr = [];
		$this->assertEquals( 'Question\s', ap_sanitize_unslash( $arr, false, 'Question\s' ) );
		$arr = [
			'Question\s',
			'Answer\s',
			'Question \title < <span>I will get removed</span>     I reside at last',
			'question',
			'answer',
		];
		$this->assertEquals(
			[
				'Questions',
				'Answers',
				'Question title &lt; I will get removed I reside at last',
				'question',
				'answer',
			],
			ap_sanitize_unslash( $arr )
		);
		$arr = [ 'Question \title < <span>I will get removed</span>     I reside at last' ];
		$this->assertEquals( [ 'Question title &lt; I will get removed I reside at last' ], ap_sanitize_unslash( $arr ) );
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

		// Test for inner array contents availability provided by ap_main_pages function.
		foreach ( ap_main_pages() as $main_page ) {
			$this->assertArrayHasKey( 'label', $main_page );
			$this->assertArrayHasKey( 'desc', $main_page );
			$this->assertArrayHasKey( 'post_title', $main_page );
			$this->assertArrayHasKey( 'post_name', $main_page );
		}
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

	/**
	 * @covers ::ap_sort_array_by_order
	 */
	public function testAPSortArrayByOrder() {
		$this->assertEquals(
			[
				'post' => [
					'title' => 'Post title',
					'order' => 1,
				],
				'page' => [
					'title' => 'Page title',
					'order' => 3,
				],
				'comment' => [
					'title' => 'Comment title',
					'order' => 5,
				],
			],
			ap_sort_array_by_order(
				[
					'post' => [
						'title' => 'Post title',
					],
					'page' => [
						'title' => 'Page title',
					],
					'comment' => [
						'title' => 'Comment title',
					],
				],
			)
		);
		$this->assertEquals(
			[
				'post' => [
					'title' => 'Post title',
					'order' => 2,
				],
				'page' => [
					'title' => 'Page title',
					'order' => 3,
				],
				'comment' => [
					'title' => 'Comment title',
					'order' => 5,
				],
			],
			ap_sort_array_by_order(
				[
					'post' => [
						'title' => 'Post title',
						'order' => 2,
					],
					'page' => [
						'title' => 'Page title',
					],
					'comment' => [
						'title' => 'Comment title',
					],
				],
			)
		);
		$this->assertEquals(
			[
				'comment' => [
					'title' => 'Comment title',
					'order' => 1,
				],
				'post' => [
					'title' => 'Post title',
					'order' => 2,
				],
				'page' => [
					'title' => 'Page title',
					'order' => 3,
				],
			],
			ap_sort_array_by_order(
				[
					'post' => [
						'title' => 'Post title',
						'order' => 2,
					],
					'page' => [
						'title' => 'Page title',
					],
					'comment' => [
						'title' => 'Comment title',
						'order' => 1,
					],
				],
			)
		);
		$this->assertEquals(
			[
				'comment' => [
					'title' => 'Comment title',
					'order' => 1,
				],
				'post' => [
					'title' => 'Post title',
					'order' => 1,
				],
				'page' => [
					'title' => 'Page title',
					'order' => 3,
				],
			],
			ap_sort_array_by_order(
				[
					'post' => [
						'title' => 'Post title',
					],
					'page' => [
						'title' => 'Page title',
					],
					'comment' => [
						'title' => 'Comment title',
						'order' => 1,
					],
				],
			)
		);
		$this->assertEquals(
			[
				'page' => [
					'title' => 'Page title',
					'order' => 1,
				],
				'post' => [
					'title' => 'Post title',
					'order' => 1,
				],
				'comment' => [
					'title' => 'Comment title',
					'order' => 5,
				],
			],
			ap_sort_array_by_order(
				[
					'post' => [
						'title' => 'Post title',
					],
					'page' => [
						'title' => 'Page title',
						'order' => 1,
					],
					'comment' => [
						'title' => 'Comment title',
					],
				],
			)
		);
	}

	/**
	 * @covers ::ap_meta_array_map
	 */
	public function testAPMetaArrayMap() {
		$this->assertEquals( 'q', ap_meta_array_map( 'question' ) );
		$this->assertEquals( 'a', ap_meta_array_map( 'answer' ) );
		$this->assertEquals( 'question', ap_meta_array_map( array( 'question' ) ) );
		$this->assertEquals( 'question', ap_meta_array_map( array( 'question', 'answer' ) ) );
		$this->assertEquals( 'answer', ap_meta_array_map( array( 'answer', 'question', 'comment' ) ) );
	}

	/**
	 * @covers ::is_anspress
	 */
	public function testIsAnsPress() {
		// Normal test.
		$this->assertFalse( is_anspress() );

		// Test for the single question page.
		$id = $this->insert_answer();
		$this->go_to( '?post_type=question&p=' . $id->q );
		$this->assertTrue( is_anspress() );
		$this->go_to( ap_get_short_link( [ 'ap_q' => $id->q ] ) );
		$this->assertTrue( is_anspress() );
		$this->go_to( '/' );
		$this->assertFalse( is_anspress() );

		// For the single answer page.
		$this->go_to( ap_get_short_link( [ 'ap_a' => $id->a ] ) );
		$this->assertTrue( is_anspress() );
		$this->go_to( '/' );
		$this->assertFalse( is_anspress() );

		// For the base page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Base Page',
				'post_type'  => 'page',
			]
		);
		$this->assertFalse( is_anspress() );
		ap_opt( 'base_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertTrue( is_anspress() );
		ap_opt( 'base_page', '' );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertFalse( is_anspress() );

		// For the ask page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Ask Page',
				'post_type'  => 'page',
			]
		);
		$this->assertFalse( is_anspress() );
		ap_opt( 'ask_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertTrue( is_anspress() );
		ap_opt( 'ask_page', '' );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertFalse( is_anspress() );

		// For the user page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'User Page',
				'post_type'  => 'page',
			]
		);
		$this->assertFalse( is_anspress() );
		ap_opt( 'user_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertTrue( is_anspress() );
		ap_opt( 'user_page', '' );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertFalse( is_anspress() );

		// For the categories page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Categories Page',
				'post_type'  => 'page',
			]
		);
		$this->assertFalse( is_anspress() );
		ap_opt( 'categories_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertTrue( is_anspress() );
		ap_opt( 'categories_page', '' );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertFalse( is_anspress() );

		// For the tags page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Tags Page',
				'post_type'  => 'page',
			]
		);
		$this->assertFalse( is_anspress() );
		ap_opt( 'tags_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertTrue( is_anspress() );
		ap_opt( 'tags_page', '' );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertFalse( is_anspress() );

		// For the activities page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Activities Page',
				'post_type'  => 'page',
			]
		);
		$this->assertFalse( is_anspress() );
		ap_opt( 'activities_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertTrue( is_anspress() );
		ap_opt( 'activities_page', '' );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertFalse( is_anspress() );

		// Test for the single category archive page.
		$cid = $this->factory->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_category' );
		$this->assertFalse( is_anspress() );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$this->assertTrue( is_anspress() );
		$this->go_to( '/' );
		$this->assertFalse( is_anspress() );

		// Test for the single tag archive page.
		$tid = $this->factory->term->create(
			array(
				'name'     => 'Question tag',
				'taxonomy' => 'question_tag',
			)
		);
		$term = get_term_by( 'id', $tid, 'question_tag' );
		$this->assertFalse( is_anspress() );
		$this->go_to( '/?ap_page=tag&question_tag=' . $term->slug );
		$this->assertTrue( is_anspress() );
		$this->go_to( '/' );
		$this->assertFalse( is_anspress() );

		// Test for the search page.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Base Page',
				'post_type'  => 'page',
			]
		);
		$this->assertFalse( is_anspress() );
		ap_opt( 'base_page', $id );
		$this->go_to( '?post_type=page&p=' . $id . '&ap_s=question' );
		$this->assertTrue( is_anspress() );
		$this->go_to( '?post_type=page&p=' . $id . '&ap_s=answer' );
		$this->assertTrue( is_anspress() );
		$this->go_to( '?ap_s=question' );
		$this->assertFalse( is_anspress() );
		$this->go_to( '?ap_s=answer' );
		$this->assertFalse( is_anspress() );
		$this->go_to( '/' );
		$this->assertFalse( is_anspress() );

		// Test for the static front page question page.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Base Page',
				'post_type'  => 'page',
			]
		);
		$this->assertFalse( is_anspress() );
		ap_opt( 'base_page', $id );
		update_option( 'page_on_front', $id );
		update_option( 'show_on_front', 'page' );
		$this->assertFalse( is_anspress() );
		$this->go_to( '/' );
		$this->assertTrue( is_anspress() );
	}

	/**
	 * @covers ::ap_canonical_url
	 */
	public function testap_canonical_url() {
		// For the single question page test.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertEquals( esc_url( get_permalink( $id ) ), ap_canonical_url() );
		$id = $this->insert_answer();
		$this->go_to( '?post_type=question&p=' . $id->q );
		$this->assertEquals( esc_url( get_permalink( $id->q ) ), ap_canonical_url() );

		// For the base page test.
		$id = $this->factory->post->create(
			[
				'post_title' => 'Base Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'base_page', $id );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( esc_url( get_permalink( $id ) ), ap_canonical_url() );
	}

	/**
	 * @covers ::ap_verify_default_nonce
	 */
	public function testAPVerifyDefaultNonce() {
		// Test for invalid nonce.
		$this->assertFalse( ap_verify_default_nonce() );
		$_REQUEST['ap_ajax_nonce'] = 'anspress-tests';
		$this->assertFalse( ap_verify_default_nonce() );
		$_REQUEST['ap_ajax_nonce'] = wp_create_nonce( 'anspress-tests' );
		$this->assertFalse( ap_verify_default_nonce() );
		$_REQUEST['ap_ajax_nonce'] = wp_create_nonce( '__nonce' );
		$this->assertFalse( ap_verify_default_nonce() );
		$_POST['ap_ajax_nonce'] = wp_create_nonce( 'ap_ajax_nonce' );
		$this->assertFalse( ap_verify_default_nonce() );

		// Test for valid nonce.
		$_REQUEST['ap_ajax_nonce'] = wp_create_nonce( 'ap_ajax_nonce' );
		$this->assertIsInt( ap_verify_default_nonce() );
		unset( $_POST['ap_ajax_nonce'] );
		unset( $_REQUEST['ap_ajax_nonce'] );
	}

	/**
	 * @covers ::ap_isset_post_value
	 */
	public function testAPIssetPostValue() {
		// If passing non existing value.
		$this->assertEquals( 'question', ap_isset_post_value( 'invalidVal', 'question' ) );
		$this->assertEquals( 'answer', ap_isset_post_value( 'defaultVal', 'answer' ) );
		$_POST['question'] = 'I\'m question';
		$this->assertEquals( '', ap_isset_post_value( 'question' ) );
		$_POST['answer'] = 'I\'m answer';
		$this->assertEquals( '', ap_isset_post_value( 'answer' ) );

		// Passing the correct valie.
		$_REQUEST['question'] = 'I\'m question';
		$this->assertEquals( 'I\'m question', ap_isset_post_value( 'question' ) );
		$_REQUEST['answer'] = 'I\'m answer';
		$this->assertEquals( 'I\'m answer', ap_isset_post_value( 'answer' ) );
		$_REQUEST['question'] = '\\\\\\I\'m question';
		$this->assertEquals( '\I\'m question', ap_isset_post_value( 'question' ) );
		$_REQUEST['answer'] = '\\\\\\I\'m answer';
		$this->assertEquals( '\I\'m answer', ap_isset_post_value( 'answer' ) );
		$_REQUEST['question'] = '\\\\\I\'m question///';
		$this->assertEquals( '\\I\'m question///', ap_isset_post_value( 'question' ) );
		$_REQUEST['answer'] = '\\\\\I\'m answer///';
		$this->assertEquals( '\\I\'m answer///', ap_isset_post_value( 'answer' ) );

		// Rest super global variables.
		unset( $_POST['question'] );
		unset( $_POST['answer'] );
		unset( $_REQUEST['question'] );
		unset( $_REQUEST['answer'] );
	}

	/**
	 * @covers ::ap_whitelist_array
	 */
	public function testAPWhitelistArray() {
		// Test 1.
		$arr1 = [ 'c' ];
		$arr2 = [ 'q' => 'question', 'a' => 'answer' ];
		$this->assertEmpty( ap_whitelist_array( $arr1, $arr2 ) );

		// Test 2.
		$arr1 = [ 'q' ];
		$arr2 = [ 'q' => 'question', 'a' => 'answer' ];
		$this->assertEquals( [ 'q' => 'question' ], ap_whitelist_array( $arr1, $arr2 ) );

		// Test 3.
		$arr1 = [ 'a', 'c' ];
		$arr2 = [ 'q' => 'question', 'a' => 'answer', 'c' => 'comment' ];
		$this->assertEquals( [ 'a' => 'answer', 'c' => 'comment' ], ap_whitelist_array( $arr1, $arr2 ) );

		// Test 4.
		$arr1 = [ 'question', 'comment' ];
		$arr2 = [ 'question' => 11, 'comment' => 101, 'answer' => 15 ];
		$this->assertEquals( [ 'question' => 11, 'comment' => 101 ], ap_whitelist_array( $arr1, $arr2 ) );

		// Test 5.
		$arr1 = [ 'comment' ];
		$arr2 = [ 'question' => 11, 'comment' => 101, 'answer' => 15 ];
		$this->assertEquals( [ 'comment' => 101 ], ap_whitelist_array( $arr1, $arr2 ) );
	}

	/**
	 * @covers ::ap_addon_activation_hook
	 */
	public function testAPAddonActivationHook() {
		global $ap_addons_activation;
		$ap_addons_activation = array();

		// Test for akismet activation check.
		ap_addon_activation_hook( 'akismet.php', 'akismet_activated' );
		$this->assertArrayHasKey( 'akismet.php', $ap_addons_activation );
		$this->assertEquals( 'akismet_activated', $ap_addons_activation['akismet.php'] );

		// Test for avatar activation check.
		ap_addon_activation_hook( 'avatar.php', 'avatar_activated' );
		$this->assertArrayHasKey( 'avatar.php', $ap_addons_activation );
		$this->assertEquals( 'avatar_activated', $ap_addons_activation['avatar.php'] );

		// Test for buddypress activation check.
		ap_addon_activation_hook( 'buddypress.php', 'buddypress_activated' );
		$this->assertArrayHasKey( 'buddypress.php', $ap_addons_activation );
		$this->assertEquals( 'buddypress_activated', $ap_addons_activation['buddypress.php'] );

		// Test for categories activation check.
		ap_addon_activation_hook( 'categories.php', 'categories_activated' );
		$this->assertArrayHasKey( 'categories.php', $ap_addons_activation );
		$this->assertEquals( 'categories_activated', $ap_addons_activation['categories.php'] );

		// Test for email activation check.
		ap_addon_activation_hook( 'email.php', 'email_activated' );
		$this->assertArrayHasKey( 'email.php', $ap_addons_activation );
		$this->assertEquals( 'email_activated', $ap_addons_activation['email.php'] );

		// Test for notifications activation check.
		ap_addon_activation_hook( 'notifications.php', 'notifications_activated' );
		$this->assertArrayHasKey( 'notifications.php', $ap_addons_activation );
		$this->assertEquals( 'notifications_activated', $ap_addons_activation['notifications.php'] );

		// Test for profile activation check.
		ap_addon_activation_hook( 'profile.php', 'profile_activated' );
		$this->assertArrayHasKey( 'profile.php', $ap_addons_activation );
		$this->assertEquals( 'profile_activated', $ap_addons_activation['profile.php'] );

		// Test for recaptcha activation check.
		ap_addon_activation_hook( 'recaptcha.php', 'recaptcha_activated' );
		$this->assertArrayHasKey( 'recaptcha.php', $ap_addons_activation );
		$this->assertEquals( 'recaptcha_activated', $ap_addons_activation['recaptcha.php'] );

		// Test for reputation activation check.
		ap_addon_activation_hook( 'reputation.php', 'reputation_activated' );
		$this->assertArrayHasKey( 'reputation.php', $ap_addons_activation );
		$this->assertEquals( 'reputation_activated', $ap_addons_activation['reputation.php'] );

		// Test for syntaxhighlighter activation check.
		ap_addon_activation_hook( 'syntaxhighlighter.php', 'syntaxhighlighter_activated' );
		$this->assertArrayHasKey( 'syntaxhighlighter.php', $ap_addons_activation );
		$this->assertEquals( 'syntaxhighlighter_activated', $ap_addons_activation['syntaxhighlighter.php'] );

		// Test for tags activation check.
		ap_addon_activation_hook( 'tags.php', 'tags_activated' );
		$this->assertArrayHasKey( 'tags.php', $ap_addons_activation );
		$this->assertEquals( 'tags_activated', $ap_addons_activation['tags.php'] );
	}

	/**
	 * @covers ::ap_append_table_names
	 */
	public function testAPAppendTableNames() {
		global $wpdb;

		// Call the function.
		ap_append_table_names();

		// Test case for directly checking if the table exists.
		$this->assertTrue( isset( $wpdb->ap_qameta ) );
		$this->assertTrue( isset( $wpdb->ap_votes ) );
		$this->assertTrue( isset( $wpdb->ap_views ) );
		$this->assertTrue( isset( $wpdb->ap_reputations ) );
		$this->assertTrue( isset( $wpdb->ap_subscribers ) );
		$this->assertTrue( isset( $wpdb->ap_activity ) );
		$this->assertTrue( isset( $wpdb->ap_reputation_events ) );

		// Test case for checking if the table exists after appending the table names.
		$original_prefix = $wpdb->prefix;

		// Expected table names.
		$exp_qameta = $original_prefix . 'ap_qameta';
		$exp_votes = $original_prefix . 'ap_votes';
		$exp_views = $original_prefix . 'ap_views';
		$exp_reputations = $original_prefix . 'ap_reputations';
		$exp_subscribers = $original_prefix . 'ap_subscribers';
		$exp_activity = $original_prefix . 'ap_activity';
		$exp_reputation_events = $original_prefix . 'ap_reputation_events';

		// Assert the table names.
		$this->assertEquals( $exp_qameta, $wpdb->ap_qameta );
		$this->assertEquals( $exp_votes, $wpdb->ap_votes );
		$this->assertEquals( $exp_views, $wpdb->ap_views );
		$this->assertEquals( $exp_reputations, $wpdb->ap_reputations );
		$this->assertEquals( $exp_subscribers, $wpdb->ap_subscribers );
		$this->assertEquals( $exp_activity, $wpdb->ap_activity );
		$this->assertEquals( $exp_reputation_events, $wpdb->ap_reputation_events );

		// Restore the original prefix.
		$wpdb->prefix = $original_prefix;
	}

	/**
	 * @covers ::ap_active_user_page
	 */
	public function testAPActiveUserPage() {
		// Test if user page is not set.
		$this->assertEquals( 'about', ap_active_user_page() );

		// Create a page and set it to user page.
		$id = $this->factory->post->create(
			[
				'post_title' => 'User Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'user_page', $id );

		// Go to the users page and run the required tests.
		// By not setting any query vars.
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 'about', ap_active_user_page() );
		// By setting the query vars.
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', 'profile' );
		$this->assertEquals( 'profile', ap_active_user_page() );
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', 'notifications' );
		$this->assertEquals( 'notifications', ap_active_user_page() );
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', 'edit' );
		$this->assertEquals( 'edit', ap_active_user_page() );
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', '' );
		$this->assertEquals( 'about', ap_active_user_page() );
	}

	/**
	 * @covers ::ap_current_user_id
	 */
	public function testAPCurrentUserID() {
		// Test for not visting the user page.
		$this->assertEquals( 0, ap_current_user_id() );

		// Create a page and set it to user page.
		$id = $this->factory->post->create(
			[
				'post_title' => 'User Page',
				'post_type'  => 'page',
			]
		);
		ap_opt( 'user_page', $id );

		// Test for visiting the user page.
		// By not setting any query vars.
		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );
		$this->go_to( '?post_type=page&p=' . $id );
		$this->assertEquals( 0, ap_current_user_id() );
		$this->assertNotEquals( $user->ID, ap_current_user_id() );

		// By setting the query vars.
		// Test 1.
		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', 'profile' );
		global $wp_query;
		$wp_query->queried_object = $user;
		$this->assertNotEquals( 0, ap_current_user_id() );
		$this->assertEquals( $user->ID, ap_current_user_id() );

		// Test 2.
		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', 'notifications' );
		global $wp_query;
		$wp_query->queried_object = $user;
		$this->assertNotEquals( 0, ap_current_user_id() );
		$this->assertEquals( $user->ID, ap_current_user_id() );

		// Test 3.
		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', 'edit' );
		global $wp_query;
		$wp_query->queried_object = $user;
		$this->assertNotEquals( 0, ap_current_user_id() );
		$this->assertEquals( $user->ID, ap_current_user_id() );

		// Test 4.
		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', '' );
		global $wp_query;
		$wp_query->queried_object = $user;
		$this->assertNotEquals( 0, ap_current_user_id() );
		$this->assertEquals( $user->ID, ap_current_user_id() );

		// Test 5.
		$user = $this->factory->user->create_and_get();
		wp_set_current_user( $user->ID );
		$this->go_to( '?post_type=page&p=' . $id );
		set_query_var( 'user_page', 'about' );
		global $wp_query;
		$wp_query->queried_object = $user;
		$this->assertNotEquals( 0, ap_current_user_id() );
		$this->assertEquals( $user->ID, ap_current_user_id() );
	}

	/**
	 * @covers ::ap_get_current_timestamp
	 */
	public function testAPGetCurrentTimestamp() {
		// Test for exact timestamp with no timezone change.
		$this->assertEquals( current_time( 'timestamp' ), ap_get_current_timestamp() );

		// Test for timestamp with timezone change.
		update_option( 'timezone_string', 'America/New_York' );
		$this->assertNotEquals( time(), ap_get_current_timestamp() ); // Returns current timestamp without GMT offset.
		$this->assertEquals( current_time( 'timestamp' ), ap_get_current_timestamp() ); // Returns current timestamp with GMT offset.
		update_option( 'timezone_string', 'Europe/Prague' );
		$this->assertNotEquals( time(), ap_get_current_timestamp() ); // Returns current timestamp without GMT offset.
		$this->assertEquals( current_time( 'timestamp' ), ap_get_current_timestamp() ); // Returns current timestamp with GMT offset.
		update_option( 'timezone_string', 'Asia/Kolkata' );
		$this->assertNotEquals( time(), ap_get_current_timestamp() ); // Returns current timestamp without GMT offset.
		$this->assertEquals( current_time( 'timestamp' ), ap_get_current_timestamp() ); // Returns current timestamp with GMT offset.

		// Reset to original timezone.
		update_option( 'timezone_string', 'UTC' );
	}

	/**
	 * @covers ::ap_set_in_array
	 */
	public function testAPSetInArray() {
		// Setting value with a simple array.
		$arr = [];
		ap_set_in_array( $arr, 'key', 'value' );
		$this->assertEquals( 'value', $arr['key'] );

		// Setting value in a nested array using path.
		$arr = [ 'nested' => [ 'key' => 'value' ] ];
		ap_set_in_array( $arr, 'nested.key', 'new_value' );
		$this->assertEquals( 'new_value', $arr['nested']['key'] );

		// Setting value in a nested array using array path.
		$arr = [ 'nested' => [ 'key' => 'value' ] ];
		$path = [ 'nested', 'key' ];
		ap_set_in_array( $arr, $path, 'new_value' );
		$this->assertEquals( 'new_value', $arr['nested']['key'] );

		// Merging an array.
		$arr = [ 'merge' => [ 'key' => 'value' ] ];
		$merge_arr = [ 'new_key' => 'new_value' ];
		ap_set_in_array( $arr, 'merge', $merge_arr, true );
		$this->assertEquals( [ 'key' => 'value', 'new_key' => 'new_value' ], $arr['merge'] );

		// Setting value in an empty array.
		$arr = [];
		ap_set_in_array( $arr, '', 'new_value' );
		$this->assertEquals( [ '' => 'new_value' ], $arr );
	}

	/**
	 * @covers ::ap_rand
	 */
	public function testAPRand() {
		// Test with a range of 1 to 5 and a weight of 2.
		srand( 1234 );
		$result = ap_rand( 1, 5, 2 );
		$this->assertGreaterThanOrEqual( 1, $result );
		$this->assertLessThanOrEqual( 5, $result );

		// Test with a range of 10 to 20 and a weight of 3.
		srand( 5678 );
		$result = ap_rand( 10, 20, 3 );
		$this->assertGreaterThanOrEqual( 10, $result );
		$this->assertLessThanOrEqual( 20, $result );

		// Test with a range of 5 to 10 and a weight of 1.
		srand( 4321 );
		$result = ap_rand( 5, 10, 1 );
		$this->assertGreaterThanOrEqual( 5, $result );
		$this->assertLessThanOrEqual( 10, $result );

		// Test with a range of 111 to 999 and a weight of 11.
		srand( 8765 );
		$result = ap_rand( 111, 999, 11 );
		$this->assertGreaterThanOrEqual( 111, $result );
		$this->assertLessThanOrEqual( 999, $result );
	}

	/**
	 * @covers ::ap_parse_search_string
	 */
	public function testAPParseSearchString() {
		// For no search string.
		$result = ap_parse_search_string( 'tag:tag1,tag2 category:category1 author:user1' );
		$exp_result = [
			'tag'      => [ 'tag1', 'tag2' ],
			'category' => [ 'category1' ],
			'author'   => [ 'user1' ],
			'q'        => '',
		];
		$this->assertEquals( $exp_result, $result );

		// For empty search.
		$result = ap_parse_search_string( '' );
		$exp_result = [ 'q' => '' ];
		$this->assertEquals( $exp_result, $result );

		// For invalid search,
		$result = ap_parse_search_string( 'invalid_search_string' );
		$exp_result = [ 'q' => 'invalid_search_string' ];
		$this->assertEquals( $exp_result, $result );

		// For search string with empty values.
		$result = ap_parse_search_string( 'tag: category: author: ' );
		$exp_result = [ 'q' => '' ];
		$this->assertEquals( $exp_result, $result );

		// For search string with only tag value.
		$result = ap_parse_search_string( 'tag:tag1,tag2,tag3' );
		$exp_result = [
			'tag' => [ 'tag1', 'tag2', 'tag3' ],
			'q'   => '',
		];
		$this->assertEquals( $exp_result, $result );

		// For search string with only category value.
		$result = ap_parse_search_string( 'category:category1,category2,category3' );
		$exp_result = [
			'category' => [ 'category1', 'category2', 'category3' ],
			'q'        => '',
		];
		$this->assertEquals( $exp_result, $result );

		// For search string with only author value.
		$result = ap_parse_search_string( 'author:author1,author2,author3' );
		$exp_result = [
			'author' => [ 'author1', 'author2', 'author3' ],
			'q'      => '',
		];
		$this->assertEquals( $exp_result, $result );

		// For random searches.
		$result = ap_parse_search_string( 'tag:tag1,tag2 author:' );
		$exp_result = [
			'tag' => [ 'tag1', 'tag2' ],
			'q'   => '',
		];
		$this->assertEquals( $exp_result, $result );
		$result = ap_parse_search_string( 'author:author1, tag: category:' );
		$exp_result = [
			'author' => [ 'author1' ],
			'q'      => '',
		];
		$this->assertEquals( $exp_result, $result );
		$result = ap_parse_search_string( 'author: tag: category: date:2022 id=10,11 p=10,11' );
		$exp_result = [
			'date' => [ '2022' ],
			'q'    => 'id=10,11 p=10,11',
		];
		$this->assertEquals( $exp_result, $result );
	}

	/**
	 * @covers ::ap_question_title_with_solved_prefix
	 */
	public function testAPQuestionTitleWithSolvedPrefix() {
		// Test for question with no answer.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$question_title = get_the_title( $id ) . ' ';
		$this->assertEquals( $question_title, ap_question_title_with_solved_prefix() );

		// Test for question with answer.
		$id = $this->insert_answer();
		$this->go_to( '?post_type=question&p=' . $id->q );
		$question_title = get_the_title( $id->q ) . ' ';
		$this->assertEquals( $question_title, ap_question_title_with_solved_prefix() );

		// Test for question with answer marked as selected.
		$id = $this->insert_answer();
		// Set as selected answer.
		ap_set_selected_answer( $id->q, $id->a );
		$this->go_to( '?post_type=question&p=' . $id->q );
		$question_title = '[Solved] ' . get_the_title( $id->q ) . ' ';
		$this->assertEquals( $question_title, ap_question_title_with_solved_prefix() );

		// Test when the prefix option is disabled.
		ap_opt( 'show_solved_prefix', false );
		$this->go_to( '?post_type=question&p=' . $id->q );
		$question_title = get_the_title( $id->q );
		$this->assertEquals( $question_title, ap_question_title_with_solved_prefix() );

		// Re-test when the prefix option is enabled.
		ap_opt( 'show_solved_prefix', true );
		$this->go_to( '?post_type=question&p=' . $id->q );
		$question_title = '[Solved] ' . get_the_title( $id->q ) . ' ';
		$this->assertEquals( $question_title, ap_question_title_with_solved_prefix() );
	}

	/**
	 * @covers ::ap_create_base_page
	 */
	public function testAPCreateBasePage() {
		// Call the AnsPress base page create function.
		ap_create_base_page();

		// Check if the pages were created as expected.
		$this->assertTrue( $this->basePagesCreated() );
	}

	public function basePagesCreated() {
		// Check if pages were created as expected
		$pages = ap_main_pages();

		foreach ( $pages as $slug => $page ) {
			$created_page = get_post( ap_opt( $slug ) );

			// Test on pages.
			$this->assertInstanceOf( 'WP_Post', $created_page );
			$this->assertEquals( 'publish', $created_page->post_status );
			$this->assertEquals( '[anspress]', $created_page->post_content );
			$this->assertEquals( 'closed', $created_page->comment_status );

			// Additional test for child pages.
			if ( 'base_page' !== $slug ) {
				$this->assertEquals( ap_opt( 'base_page' ), $created_page->post_parent );
			}
		}

		return true;
	}

	/**
	 * @covers ::ap_current_page_url
	 */
	public function testAPCurrentPageURL() {
		$id = $this->insert_question();

		// Test for no custom permalink structure.
		update_option( 'permalink_structure', '' );
		$this->go_to( '/?post_type=question&p=' . $id );
		$args = array( 'param1' => 'value1', 'param2' => 'value2' );
		$this->assertStringContainsString( 'param1=value1', ap_current_page_url( $args ) );
		$this->assertStringContainsString( 'param2=value2', ap_current_page_url( $args ) );
		$this->assertStringContainsString( '&param1=value1&param2=value2', ap_current_page_url( $args ) );

		// Test for custom permalink structure being set.
		update_option( 'permalink_structure', '/%postname%/' );
		$this->go_to( '/?post_type=question&p=' . $id );
		$args = array( 'param1' => 'value1', 'param2' => 'value2' );
		$this->assertStringContainsString( 'param1/value1', ap_current_page_url( $args ) );
		$this->assertStringContainsString( 'param2/value2', ap_current_page_url( $args ) );
		$this->assertStringContainsString( 'questions/question/question-title/param1/value1/param2/value2/', ap_current_page_url( $args ) );
	}

	/**
	 * @covers ::ap_is_ajax
	 */
	public function testAPIsAjax() {
		// DOING_AJAX is already defined so can't test with false value on it
		// since changing constant value on PHP is not possible.
		// Should return false since we're not passing any values.
		$this->assertFalse( ap_is_ajax() );

		// Should return false since we're passing wrong value.
		$_REQUEST['ap_action'] = 'new_question';
		$this->assertFalse( ap_is_ajax() );
		unset( $_REQUEST['ap_action'] );

		// Should return true since we're passing value as intended.
		$_REQUEST['ap_ajax_action'] = 'new_question';
		$this->assertTrue( ap_is_ajax() );
		unset( $_REQUEST['ap_ajax_action'] );
	}

	/**
	 * Filter ap_ajax_responce.
	 */
	public function apAjaxResponse( $results ) {
		$results['additional_data'] = 'AP Ajax Response Additional Data';
		$results['other_additional_data'] = 'Other AP Ajax Response Additional Data';

		return $results;
	}

	/**
	 * @covers ::ap_ajax_responce
	 */
	public function testap_ajax_responce() {
		// Test for passing string, which should contain message key.
		$result = ap_ajax_responce( 'question_answer' );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'message', $result );
		$this->assertArrayHasKey( 'ap_responce', $result );

		// Test for passing empty array, which should not contain message key.
		$result = ap_ajax_responce( [] );
		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'message', $result );
		$this->assertArrayHasKey( 'ap_responce', $result );

		// Test for passing array with message datas, should contain snackbar and success keys as well.
		$result = ap_ajax_responce( [ 'message' => 'success' ] );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'message', $result );
		$this->assertArrayHasKey( 'ap_responce', $result );
		$this->assertArrayHasKey( 'snackbar', $result );
		$this->assertArrayHasKey( 'success', $result );

		// Test should contain the response message datas.
		// Test 1.
		$result = ap_ajax_responce( [ 'message' => 'success' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( 'success', $result['message'] );
		$this->assertEquals( true, $result['ap_responce'] );
		$this->assertEquals( 'Success', $result['snackbar']['message'] );
		$this->assertEquals( 'success', $result['snackbar']['message_type'] );
		$this->assertEquals( true, $result['success'] );

		// Test 2.
		$result = ap_ajax_responce( [ 'message' => 'something_wrong' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( 'something_wrong', $result['message'] );
		$this->assertEquals( true, $result['ap_responce'] );
		$this->assertEquals( 'Something went wrong, last action failed.', $result['snackbar']['message'] );
		$this->assertEquals( 'error', $result['snackbar']['message_type'] );
		$this->assertEquals( false, $result['success'] );

		// Test 3.
		$result = ap_ajax_responce( [ 'message' => 'cannot_vote_own_post' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( 'cannot_vote_own_post', $result['message'] );
		$this->assertEquals( true, $result['ap_responce'] );
		$this->assertEquals( 'You cannot vote on your own question or answer.', $result['snackbar']['message'] );
		$this->assertEquals( 'warning', $result['snackbar']['message_type'] );
		$this->assertEquals( true, $result['success'] );

		// Test for additional data pass by filtering the ap_ajax_responce filter.
		// Test before filter being applied.
		$result = ap_ajax_responce( [] );
		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'additional_data', $result );
		$this->assertArrayNotHasKey( 'other_additional_data', $result );

		// Test after filter being applied.
		add_filter( 'ap_ajax_responce', [ $this, 'apAjaxResponse' ] );
		$result = ap_ajax_responce( [] );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'additional_data', $result );
		$this->assertArrayHasKey( 'other_additional_data', $result );
		$this->assertEquals( 'AP Ajax Response Additional Data', $result['additional_data'] );
		$this->assertEquals( 'Other AP Ajax Response Additional Data', $result['other_additional_data'] );

		// Test after filter being removed.
		remove_filter( 'ap_ajax_responce', [ $this, 'apAjaxResponse' ] );
		$result = ap_ajax_responce( [] );
		$this->assertIsArray( $result );
		$this->assertArrayNotHasKey( 'additional_data', $result );
		$this->assertArrayNotHasKey( 'other_additional_data', $result );
	}

	/**
	 * @covers ::ap_get_sort
	 */
	public function testAPGetSort() {
		// Test via request.
		$_REQUEST['ap_sort'] = 'date';
		$this->assertEquals( 'date', ap_get_sort() );
		unset( $_REQUEST['ap_sort'] );

		// Test via post.
		$_POST['ap_sort'] = 'date';
		$this->assertEquals( null, ap_get_sort() );
		unset( $_POST['ap_sort'] );

		// Test via get.
		$_GET['ap_sort'] = 'date';
		$this->assertEquals( null, ap_get_sort() );
		unset( $_GET['ap_sort'] );

		// Test via request but having invalid value.
		$_REQUEST['ap_sort_data'] = 'date';
		$this->assertEquals( null, ap_get_sort() );
		unset( $_REQUEST['ap_sort_data'] );
	}

	/**
	 * @covers ::ap_is_profile_menu
	 */
	public function testAPIsProfileMenu() {
		// Setup WordPress menu items.
		$menu_name = 'AnsPress Menu';
		$menu_id = wp_create_nav_menu( $menu_name );
		// Add home menu item.
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'   => 'Home',
				'menu-item-url'     => home_url( '/' ),
				'menu-item-status'  => 'publish',
			)
		);
		// Add contact menu item.
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'   => 'Contact',
				'menu-item-url'     => home_url( '/contact' ),
				'menu-item-status'  => 'publish',
			)
		);
		// Add user profile page item.
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'   => 'User Profile',
				'menu-item-url'     => home_url( '/user-profile' ),
				'menu-item-status'  => 'publish',
				'menu-item-classes' => 'anspress-page-profile',
			)
		);
		// Add user profile page about item.
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'   => 'User Profile',
				'menu-item-url'     => home_url( '/user-profile#about' ),
				'menu-item-status'  => 'publish',
				'menu-item-classes' => 'anspress-page-profile',
			)
		);
		// Add about item.
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'   => 'About',
				'menu-item-url'     => home_url( '/about' ),
				'menu-item-status'  => 'publish',
				'menu-item-classes' => '',
			)
		);
		$menu = wp_get_nav_menu_items( $menu_name );

		// Test case begins.
		// Get the individual menu items data.
		$menu_item_0 = $menu[ 0 ];
		$this->assertFalse( ap_is_profile_menu( $menu_item_0 ) );
		$menu_item_1 = $menu[ 1 ];
		$this->assertFalse( ap_is_profile_menu( $menu_item_1 ) );
		$menu_item_2 = $menu[ 2 ];
		$this->assertTrue( ap_is_profile_menu( $menu_item_2 ) );
		$menu_item_3 = $menu[ 3 ];
		$this->assertTrue( ap_is_profile_menu( $menu_item_3 ) );
		$menu_item_4 = $menu[ 4 ];
		$this->assertFalse( ap_is_profile_menu( $menu_item_4 ) );
	}

	/**
	 * Filter ap_list_filters
	 */
	public function apListFilters() {
		return [];
	}

	/**
	 * @covers ::ap_get_current_list_filters
	 */
	public function testAPGetCurrentListFilters() {
		// Test 1.
		$result = ap_get_current_list_filters( 'order_by' );
		$this->assertEquals( 'active', $result );

		// After changing the order_by data.
		ap_opt( 'question_order_by', 'newest' );
		$result = ap_get_current_list_filters( 'order_by' );
		$this->assertEquals( 'newest', $result );
		ap_opt( 'question_order_by', 'voted' );
		$result = ap_get_current_list_filters( 'order_by' );
		$this->assertEquals( 'voted', $result );

		// Test 2.
		ap_opt( 'question_order_by', 'active' );
		$result = ap_get_current_list_filters();
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'order_by', $result );
		$this->assertEquals( [ 'order_by' => 'active' ], $result );

		// Test 3.
		// Before applying the filter.
		// Case 1.
		$result = ap_get_current_list_filters();
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'order_by' => 'active' ], $result );
		$this->assertArrayHasKey( 'order_by', $result );

		// Case 2.
		$result = ap_get_current_list_filters( 'order_by' );
		$this->assertIsString( $result );
		$this->assertEquals( 'active', $result );

		// After applying the filter with empty array.
		add_filter( 'ap_list_filters', [ $this, 'apListFilters' ] );
		// Case 1.
		$result = ap_get_current_list_filters();
		$this->assertIsArray( $result );
		$this->assertEquals( [], $result );
		$this->assertArrayNotHasKey( 'order_by', $result );

		// Case 2.
		$result = ap_get_current_list_filters( 'order_by' );
		$this->assertEmpty( $result );
		$this->assertEquals( false, $result );

		// After removing the filter with empty array.
		remove_filter( 'ap_list_filters', [ $this, 'apListFilters' ] );
		// Case 1.
		$result = ap_get_current_list_filters();
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'order_by' => 'active' ], $result );
		$this->assertArrayHasKey( 'order_by', $result );

		// Case 2.
		$result = ap_get_current_list_filters( 'order_by' );
		$this->assertIsString( $result );
		$this->assertEquals( 'active', $result );
	}

	/**
	 * @covers ::ap_post_author_pre_fetch
	 */
	public function testAPPostAuthorPreFetch() {
		// Create some users.
		$user_1 = $this->factory->user->create( [ 'user_login' => 'anspress' ] );
		$user_2 = $this->factory->user->create( [ 'user_login' => 'question' ] );
		$user_3 = $this->factory->user->create( [ 'user_login' => 'webmaster' ] );

		// Call the required function.
		ap_post_author_pre_fetch( [ $user_1, $user_2, $user_3 ] );

		// Test begins for checking the user cache is updated or not.
		// Test 1.
		$user1_cache = wp_cache_get( $user_1, 'users', 'users' );
		$this->assertNotEmpty( $user1_cache );
		$this->assertEquals( $user_1, $user1_cache->ID );

		// Test 2.
		$user2_cache = wp_cache_get( $user_2, 'users', 'users' );
		$this->assertNotEmpty( $user2_cache );
		$this->assertEquals( $user_2, $user2_cache->ID );

		// Test 3.
		$user3_cache = wp_cache_get( $user_3, 'users', 'users' );
		$this->assertNotEmpty( $user3_cache );
		$this->assertEquals( $user_3, $user3_cache->ID );

		// Test begins for checking the meta cache is updated or not.
		// Test 1.
		$user1_meta_cache = wp_cache_get( $user_1, 'user_meta' );
		$this->assertNotEmpty( $user1_meta_cache );
		$this->assertEquals( 'anspress', $user1_meta_cache['nickname'][0] );

		// Test 2.
		$user2_meta_cache = wp_cache_get( $user_2, 'user_meta' );
		$this->assertNotEmpty( $user2_meta_cache );
		$this->assertEquals( 'question', $user2_meta_cache['nickname'][0] );

		// Test 3.
		$user3_meta_cache = wp_cache_get( $user_3, 'user_meta' );
		$this->assertNotEmpty( $user3_meta_cache );
		$this->assertEquals( 'webmaster', $user3_meta_cache['nickname'][0] );
	}

	/**
	 * @covers ::ap_get_addon_image
	 */
	public function testAPGetAddonImage() {
		// Test 1.
		ob_start();
		$image_url = ap_get_addon_image( 'categories.php' );
		ob_end_clean();
		$this->assertNotEmpty( $image_url );
		$this->assertStringContainsString( 'addons/categories/image.png', $image_url );

		// Test 2.
		ob_start();
		$image_url = ap_get_addon_image( 'tags.php' );
		ob_end_clean();
		$this->assertNotEmpty( $image_url );
		$this->assertStringContainsString( 'addons/tags/image.png', $image_url );

		// Test 3.
		ob_start();
		$image_url = ap_get_addon_image( 'invalid-addon.php' );
		ob_end_clean();
		$this->assertEmpty( $image_url );
		$this->assertNull( $image_url );
	}

	/**
	 * @covers ::ap_addon_has_options
	 */
	public function testAPAddonHasOptions() {
		// Should return false since categories addon form does not exists.
		$this->assertFalse( ap_addon_has_options( 'categories.php' ) );

		// Should return false since the required categories addon form still does not exists.
		anspress()->forms['categories'] = new \AnsPress\Form(
			'categories_form', array(
				'fields' => array(
					'field_one' => array(
						'label' => 'Simple text field',
					),
				),
			)
		);
		$this->assertFalse( ap_addon_has_options( 'categories.php' ) );

		// Should return true since the required categories addon form exists since it's created.
		anspress()->forms['addon-categories'] = new \AnsPress\Form(
			'categories_form', array(
				'fields' => array(
					'field_one' => array(
						'label' => 'Simple text field',
					),
				),
			)
		);
		$this->assertTrue( ap_addon_has_options( 'categories.php' ) );
	}

	/**
	 * @covers ::ap_trigger_qa_update_hook
	 */
	public function testAPTriggerQAUpdateHook() {
		// Test on other than the question and answer post type.
		$post_id = $this->factory->post->create( [ 'post_type' => 'page' ] );
		ap_trigger_qa_update_hook( get_post( $post_id ), 'update' );
		$this->assertFalse( did_action( 'ap_after_update_question' ) > 0 );

		// Create question/answer.
		$id = $this->insert_answer();

		// Test on question post type.
		ap_trigger_qa_update_hook( get_post( $id->q ), 'update' );
		$this->assertTrue( did_action( 'ap_after_update_question' ) > 0 );

		// Test on answer post type.
		ap_trigger_qa_update_hook( get_post( $id->a ), 'update' );
		$this->assertTrue( did_action( 'ap_after_update_answer' ) > 0 );
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
