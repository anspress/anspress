<?php

class WidgetTest extends \Codeception\TestCase\WPTestCase
{

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
	 * Test if register widget hook is wokring.
	 */
	public function test_file_hooks() {
		$this->assertNotFalse( has_action( 'widgets_init', 'ap_widgets_positions' ) );
	}

	/**
	 * Test that the widgets are registered properly.
	 */
	public function test_register_widget() {
		do_action('widget_init');
		$widgets = array_keys( $GLOBALS['wp_widget_factory']->widgets );
		$this->assertContains( 'AP_Askform_Widget', $widgets );
		$this->assertContains( 'AnsPress_Breadcrumbs_Widget', $widgets );
		$this->assertContains( 'AP_followers_Widget', $widgets );
		$this->assertContains( 'AP_Questions_Widget', $widgets );
		$this->assertContains( 'AP_Related_questions', $widgets );
		$this->assertContains( 'AP_Search_Widget', $widgets );
		//$this->assertContains( 'ap_subscribe_widget', $widgets );
		$this->assertContains( 'AP_Users_Widget', $widgets );
		$this->assertContains( 'AnsPress_User_Notifications_Widget', $widgets );
		$this->assertContains( 'AP_User_Widget', $widgets );
	}

	/**
	 * Test that the cart widget exists with the right properties.
	 */
	public function test_askform_widget() {
		$widgets = $GLOBALS['wp_widget_factory']->widgets;
		$askform_widget = $widgets['AP_Askform_Widget'];
		$this->assertInstanceOf( 'AP_Askform_Widget', $askform_widget );
		$this->assertEquals( 'ap_askform_widget', $askform_widget->id_base );
		$this->assertEquals( '(AnsPress) Ask form', $askform_widget->name );
	}

}
