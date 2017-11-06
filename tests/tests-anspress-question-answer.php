<?php
class Tests_AnsPress extends AnsPress_UnitTestCase
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

	public function test_anspress_instance() {
		$this->assertClassHasStaticAttribute( 'instance', 'AnsPress' );
	}

	/**
	 * @covers AnsPress::setup_constants
	 */
	public function test_constants() {
		$tests_dir = 'tests/';

		$path = str_replace( $tests_dir, '', plugin_dir_url( __FILE__ ) );
		$this->assertSame( ANSPRESS_URL, $path );

		$path = str_replace( $tests_dir, '', plugin_dir_path( __FILE__ ) );
		$path = substr( $path, 0, -1 );
		$this->assertSame( substr( ANSPRESS_DIR, 0, -1 ), $path );
		print_r(ANSPRESS_WIDGET_DIR);
		$path = str_replace( $tests_dir, '', plugin_dir_path( __FILE__ ) . 'widgets' . DIRECTORY_SEPARATOR );
		$this->assertSame( ANSPRESS_WIDGET_DIR, $path );

		$path = str_replace( $tests_dir, '', plugin_dir_path( __FILE__ ) . 'templates' );
		$this->assertSame( ANSPRESS_THEME_DIR, $path );

		$path = str_replace( $tests_dir, '', plugin_dir_url( __FILE__ ) . 'templates' );
		$this->assertSame( ANSPRESS_THEME_URL, $path );

		$this->assertSame( ANSPRESS_CACHE_DIR, WP_CONTENT_DIR . '/cache/anspress' );
		$this->assertSame( ANSPRESS_CACHE_TIME, HOUR_IN_SECONDS );

		$path = str_replace( $tests_dir, '', plugin_dir_path( __FILE__ ) . 'addons' );
		$this->assertSame( ANSPRESS_ADDONS_DIR, $path );
	}


}