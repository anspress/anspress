<?php

class AddonsTest extends \Codeception\TestCase\WPTestCase
{
	public function setUp() {
		// before
		parent::setUp();
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	/**
	 * Checks if default addons exists. Also checks if `ap_get_addons` is returning
	 * valid addon files only.
	 * 
	 *
	 * @test
	 * @covers ap_get_addons
	 * @uses   ap_get_addons
	 */
	function test_ap_get_addons() {
		$addons = ap_get_addons();
		$default_addons = [ 'free/avatar.php', 'free/bad-words.php', 'free/buddypress.php', 'free/category.php', 'free/email.php', 'free/recaptcha.php', 'free/reputation.php', 'free/tag.php' ];

		foreach( (array) $default_addons as $filename ) {
			$this->assertArrayHasKey( $filename, $addons, 'Default add-on ' . $filename . ' does not exists!' );
		}

		// Check if pro folder exists.
		if ( file_exists( ANSPRESS_ADDONS_DIR . DS . 'pro' ) ) {
			$this->assertFalse( true, 'Pro folder exists!' );
		}

		// Check if function is returning valid addons.
		wp_cache_delete( 'addons', 'anspress' );

		if ( file_put_contents( ANSPRESS_ADDONS_DIR . DS . 'free/test.php', '<?php' ) === false ) {
		    $this->assertFalse( true, 'Unable to add sample addon' );
		}
		
		$this->assertFalse( array_key_exists( 'free/test.php', ap_get_addons() ), 'Addon without meta tag should not be included' );
	}

	/**
	 * Checks if `ap_get_active_addons` is returning proper activated
	 * addon file names. 
	 *
	 * @test
	 * @covers ap_get_active_addons
	 * @uses   ap_get_active_addons
	 */
	function test_ap_get_active_addons() {
		// Activate an addon.
		update_option( 'anspress_addons', [ 'free/reputation.php' => true ] );
		$this->assertArrayHasKey( 'free/reputation.php', ap_get_active_addons(), 'Unable to activate an addon' );

		// Deactivate an addon.
		update_option( 'anspress_addons', [] );
		$this->assertFalse( array_key_exists( 'free/reputation.php', ap_get_active_addons() ), 'Unable to deactivate an addon' );
	}
}
