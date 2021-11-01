<?php

namespace AnsPress\Tests\WPUnit;

class AnsPressTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();
		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	/**
	 * @covers AnsPress::instance
	 */
	public function testInstance() {
		$this->assertClassHasStaticAttribute( 'instance', 'AnsPress' );
	}

	/**
	 * @covers AnsPress::setup_constants
	 */
	public function testConstant() {
		$tests_dir  = 'tests/wpunit/';
		$plugin_dir = wp_normalize_path( ABSPATH . 'wp-content/plugins/anspress-question-answer/' );
		$plugin_url = home_url( 'wp-content/plugins/anspress-question-answer/' );

		$this->assertSame( ANSPRESS_URL, $plugin_url );
		$this->assertSame( ANSPRESS_DIR, $plugin_dir );

		$path = $plugin_dir . 'widgets/';
		$this->assertSame( ANSPRESS_WIDGET_DIR, $path );

		$path = $plugin_dir . 'templates';
		$this->assertSame( ANSPRESS_THEME_DIR, $path );

		$path = $plugin_url . 'templates';
		$this->assertSame( ANSPRESS_THEME_URL, $path );

		$this->assertSame( ANSPRESS_CACHE_DIR, WP_CONTENT_DIR . '/cache/anspress' );
		$this->assertSame( ANSPRESS_CACHE_TIME, HOUR_IN_SECONDS );

		$path = $plugin_dir . 'addons';
		$this->assertSame( ANSPRESS_ADDONS_DIR, $path );
	}

	/**
	 * @covers AnsPress::includes
	 */
	public function testInclude() {
		// Check main PHP file exists.
		$this->assertFileExists( ANSPRESS_DIR . 'anspress-question-answer.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'activate.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/class/class-singleton.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/class/roles-cap.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/class/class-activity-helper.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/class/class-activity.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/activity.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/common-pages.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/class-theme.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/class-form-hooks.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/options.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/functions.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/hooks.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/question-loop.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/answer-loop.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/qameta.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/qaquery.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/qaquery-hooks.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/post-types.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/post-status.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/votes.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/views.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/theme.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/shortcode-basepage.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/process-form.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/rewrite.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/deprecated.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/flag.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/shortcode-question.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/akismet.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/comments.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/upload.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/taxo.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/reputation.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/subscribers.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/class-query.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'includes/class/class-session.php' );

		$this->assertFileExists( ANSPRESS_DIR . 'ajax/comment-modal.php' );

		$this->assertFileExists( ANSPRESS_DIR . 'classes/ajax.php' );

		$this->assertFileExists( ANSPRESS_DIR . 'widgets/search.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'widgets/question_stats.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'widgets/questions.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'widgets/breadcrumbs.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'widgets/ask-form.php' );

		$this->assertFileExists( ANSPRESS_DIR . 'lib/class-anspress-upgrader.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/class-form.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-field.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-input.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-group.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-repeatable.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-checkbox.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-select.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-editor.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-upload.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-tags.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-radio.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/form/class-textarea.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/class-validate.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/class-anspress-cli.php' );
		$this->assertFileExists( ANSPRESS_DIR . 'lib/class-wp-async-task.php' );

		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/avatar/avatar.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/buddypress/buddypress.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/categories/categories.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/categories/widget.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/email/email.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/email/class-helper.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/notifications/notifications.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/notifications/functions.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/notifications/query.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/profile/profile.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/autoload.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/class-captcha.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/ReCaptcha.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/RequestMethod.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/RequestParameters.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/Response.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/RequestMethod/Curl.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/RequestMethod/CurlPost.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/RequestMethod/Post.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/RequestMethod/Socket.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/ReCaptcha/RequestMethod/SocketPost.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/reputation/reputation.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/tags/tags.php' );
		$this->assertFileExists( ANSPRESS_ADDONS_DIR . '/syntaxhighlighter/syntaxhighlighter.php' );

		$this->assertFileExists( ANSPRESS_DIR . 'readme.txt' );

		// Check template file exists.
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/answer-form.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/answer.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/answers.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/archive.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/ask.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/attachments.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/comment.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/content-answer.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/content-none.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/edit.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/functions.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/list-head.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/login-signup.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/not-found.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/question-list-item.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/question-list.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/search-form.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/single-question.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/activities/activities.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/activities/activity-ref-content.php' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/activities/activity.php' );

		$this->assertFileExists( ANSPRESS_DIR . '/assets/question.png' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/answer.png' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/ap-admin.scss' );
		$this->assertFileExists( ANSPRESS_DIR . '/assets/ap-admin.scss' );

		$this->assertFileExists( ANSPRESS_DIR . '/languages/anspress-question-answer.pot' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/ajax.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/includes/ajax-hooks.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/addons.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/dashboard.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/emails.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/licenses.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/options.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/recount.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/reputation-events.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/roles.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/select_question.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/sidebar.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/views/uninstall.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/anspress-admin.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/class-list-table-hooks.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/functions.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/license.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/meta-box.php' );
		$this->assertFileExists( ANSPRESS_DIR . '/admin/updater.php' );
	}

	/**
	 * Register a sample form.
	 */
	public function _registerForm() {
		return array(
			'fields' => array(
				'text_field' => array(
					'label' => 'A sample text field',
				),
			),
		);
	}

	/**
	 * @covers AnsPress::get_form
	 */
	public function testGetForm() {
		// Register form
		add_filter( 'ap_form_test', [ $this, '_registerForm' ] );

		// Prepare form.
		anspress()->get_form( 'test' )->prepare();

		// Find for field `text_field` and check instanceof.
		$this->assertInstanceOf( 'AnsPress\\Form\\Field', anspress()->get_form( 'test' )->find( 'text_field' ) );

		$this->assertSame( 'form_test', anspress()->get_form( 'test' )->form_name );

		// As get form is passed reference, verify it.
		$form                                     = anspress()->get_form( 'test' );
		anspress()->get_form( 'test' )->form_name = 'form_test_changed';

		$this->assertEquals( $form, anspress()->get_form( 'test' ) );
	}

	/**
	 * @covers AnsPress::form_exists
	 */
	public function testFormExists() {
		anspress()->forms['sample'] = new \AnsPress\Form(
			'form_sample', array(
				'fields' => array(
					'field_one' => array(
						'label' => 'Simple text field',
					),
				),
			)
		);

		$this->assertTrue( anspress()->form_exists( 'form_sample' ) );
		$this->assertTrue( anspress()->form_exists( 'sample' ) );
		$this->assertFalse( anspress()->form_exists( 'undefinedform' ) );
	}

	/**
	 * @covers AnsPress::ajax_hooks
	 */
	public function testAjaxHooks() {
		// Check if ajax hooks exists if not doing ajax.
		$this->assertFalse( has_action( 'wp_ajax_ap_delete_flag', [ 'AnsPress_Admin_Ajax', 'ap_delete_flag' ] ) );
		$this->assertFalse( has_action( 'ap_ajax_toggle_best_answer', [ 'AnsPress_Ajax', 'toggle_best_answer' ] ) );
	}

	/**
	 * @covers AnsPress::site_include
	 */
	public function testSiteInclude() {
		$this->assertNotEquals( false, has_action( 'registered_taxonomy', [ 'AnsPress_Hooks', 'add_ap_tables' ] ) );
		$this->assertInstanceOf( 'AnsPress\Activity_Helper', anspress()->activity );

		// $this->assertTrue( class_exists( 'AnsPress\Addons\Avatar' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Addons\Avatar\Generator' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Addons\BuddyPress' ) );
		$this->assertTrue( class_exists( 'AnsPress\Addons\Categories' ) );
		$this->assertTrue( class_exists( 'AnsPress\Widgets\Categories' ) );
		$this->assertTrue( class_exists( 'AnsPress\Addons\Email' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Addons\Notifications' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Notifications' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Addons\Profile' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Addons\Captcha' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Addons\Reputation' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Addons\Syntax_Highlighter' ) );
		// $this->assertTrue( class_exists( 'AnsPress\Addons\Tags' ) );
	}

	/**
	 * @covers anspress
	 */
	public function testAnsPress() {
		$this->assertInstanceOf( 'AnsPress', anspress() );
	}
}
