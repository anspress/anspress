<?php

class SampleTest extends WP_UnitTestCase {
	function test_sample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}
	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	function set_post( $key, $value ) {
		$_POST[$key] = $_REQUEST[$key] = addslashes( $value );
	}
	/**
	 *
	 *
	 * @SuppressWarnings(PHPMD.Superglobals)
	 */
	function unset_post( $key ) {
		unset( $_POST[$key], $_REQUEST[$key] );
	}
}
