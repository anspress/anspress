<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonAvatarGenerator extends TestCase {

	use Testcases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Anspress\Addons\Avatar\Generator' );
		$this->assertTrue( $class->hasProperty( 'name' ) && $class->getProperty( 'name' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'user_id' ) && $class->getProperty( 'user_id' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'filename' ) && $class->getProperty( 'filename' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'char_count' ) && $class->getProperty( 'char_count' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'text_color' ) && $class->getProperty( 'text_color' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'height' ) && $class->getProperty( 'height' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'width' ) && $class->getProperty( 'width' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'font_weight' ) && $class->getProperty( 'font_weight' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'font_size' ) && $class->getProperty( 'font_size' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'colors' ) && $class->getProperty( 'colors' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'filename' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'colors' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'avatar_exists' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'filepath' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'fileurl' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'generate' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'hex_to_rgb' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'image_center' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'image_gradientrect' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar\Generator', 'color_luminance' ) );
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::filename
	 */
	public function testFilename() {
		// Test 1.
		$user_id = $this->factory()->user->create();
		$generator = new \Anspress\Addons\Avatar\Generator( $user_id );
		$filename = $generator->filename();
		$expectedFilename = md5( $user_id );
		$this->assertEquals( $expectedFilename, $generator->filename );

		// Test 2.
		$generator = new \Anspress\Addons\Avatar\Generator( '' );
		$generator->name = 'anonymous';
		$filename = $generator->filename();
		$expectedFilename = md5( $generator->user_id );
		$this->assertEquals( $expectedFilename, $generator->filename );

		// Test 3.
		$user_id = $this->factory()->user->create();
		wp_set_current_user( $user_id );
		$generator = new \Anspress\Addons\Avatar\Generator( get_userdata( $user_id ) );
		$filename = $generator->filename();
		$expectedFilename = md5( $user_id );
		$this->assertEquals( $expectedFilename, $generator->filename );
		$this->logout();

		// Test 4.
		$user_id = $this->factory()->user->create();
		$id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( array( 'comment_post_ID' => $id, 'comment_type' => 'anspress', 'user_id' => $user_id ) );
		$comment = get_comment( $comment_id );
		$user = get_user_by( 'ID', $comment->user_id );
		$generator = new \Anspress\Addons\Avatar\Generator( $user );
		$filename = $generator->filename();
		$expectedFilename = md5( $comment->user_id );
		$this->assertEquals( $expectedFilename, $generator->filename );
	}

	public function Colors( $colors ) {
		$colors[] = '#FFFFFF';
		$colors[] = '#000000';
		$colors[] = '#FF0000';
		$colors[] = '#00FF00';
		$colors[] = '#0000FF';
		return $colors;
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::colors
	 */
	public function testColors() {
		$generator = new \Anspress\Addons\Avatar\Generator( '' );

		// Test begins.
		$colors = $generator->colors();

		// Test 1.
		$this->assertIsArray( $generator->colors );
		$this->assertNotEmpty( $generator->colors );
		$expected_colors = [ '#EA526F', '#FF0038', '#3C91E6', '#D64933', '#00A878', '#0A2472', '#736B92', '#FFAD05', '#DD9787', '#74D3AE', '#B9314F', '#878472', '#983628', '#E2AEDD', '#1B9AAA', '#FFC43D', '#4F3824', '#7A6F9B', '#376996', '#7B904B', '#613DC1' ];
		foreach ( $expected_colors as $color ) {
			$this->assertContains( $color, $generator->colors );
		}

		// Test 2.
		add_filter( 'ap_addon_avatar_colors', [ $this, 'Colors' ] );
		$colors = $generator->colors();
		$expected_colors = [ '#FFFFFF', '#000000', '#FF0000', '#00FF00', '#0000FF' ];
		foreach ( $expected_colors as $color ) {
			$this->assertContains( $color, $generator->colors );
		}
		remove_filter( 'ap_addon_avatar_colors', [ $this, 'Colors' ] );

		// Test 3.
		$colors = $generator->colors();
		foreach ( $expected_colors as $color ) {
			$this->assertNotContains( $color, $generator->colors );
		}
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::avatar_exists
	 */
	public function testAvatarExists() {
		$user_id = $this->factory()->user->create();
		$generator = new \Anspress\Addons\Avatar\Generator( $user_id );

		// Test for avatar not exists.
		$this->assertFalse( $generator->avatar_exists() );

		// Test for avatar exists.
		// Test 1.
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';
		$avatar_file = $avatar_dir . '/' . $generator->filename . '.jpg';
		wp_mkdir_p( $avatar_dir );
		touch( $avatar_file );
		$this->assertTrue( $generator->avatar_exists() );
		unlink( $avatar_file );
		rmdir( $avatar_dir );

		// Test 2.
		$user_id = $this->factory()->user->create();
		$generator = new \Anspress\Addons\Avatar\Generator( get_userdata( $user_id ) );
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';
		$avatar_file = $avatar_dir . '/' . $generator->filename . '.jpg';
		wp_mkdir_p( $avatar_dir );
		touch( $avatar_file );
		$this->assertTrue( $generator->avatar_exists() );
		unlink( $avatar_file );
		rmdir( $avatar_dir );
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::filepath
	 */
	public function testFilepath() {
		// Test 1.
		$user_id = $this->factory()->user->create();
		$generator = new \Anspress\Addons\Avatar\Generator( $user_id );
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';
		$avatar_file = $avatar_dir . '/' . $generator->filename . '.jpg';
		wp_mkdir_p( $avatar_dir );
		touch( $avatar_file );
		$this->assertEquals( $avatar_file, $generator->filepath() );
		unlink( $avatar_file );
		rmdir( $avatar_dir );

		// Test 2.
		$user_id = $this->factory()->user->create();
		$generator = new \Anspress\Addons\Avatar\Generator( get_userdata( $user_id ) );
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';
		$avatar_file = $avatar_dir . '/' . $generator->filename . '.jpg';
		wp_mkdir_p( $avatar_dir );
		touch( $avatar_file );
		$this->assertEquals( $avatar_file, $generator->filepath() );
		unlink( $avatar_file );
		rmdir( $avatar_dir );
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::fileurl
	 */
	public function testFileurl() {
		// Test 1.
		$user_id = $this->factory()->user->create();
		$generator = new \Anspress\Addons\Avatar\Generator( $user_id );
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['baseurl'] . '/ap_avatars';
		$avatar_file = $avatar_dir . '/' . $generator->filename . '.jpg';
		$this->assertEquals( $avatar_file, $generator->fileurl() );

		// Test 2.
		$user_id = $this->factory()->user->create();
		$generator = new \Anspress\Addons\Avatar\Generator( get_userdata( $user_id ) );
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['baseurl'] . '/ap_avatars';
		$avatar_file = $avatar_dir . '/' . $generator->filename . '.jpg';
		$this->assertEquals( $avatar_file, $generator->fileurl() );
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::hex_to_rgb
	 */
	public function testHEXtoRGB() {
		$generator = new \Anspress\Addons\Avatar\Generator( '' );

		// Make the hex_to_rgb method public.
		$method = new \ReflectionMethod( 'Anspress\Addons\Avatar\Generator', 'hex_to_rgb' );
		$method->setAccessible( true );

		// Test 1.
		$hex = 'invalid_hex_color';
		$this->assertFalse( $method->invoke( $generator, $hex ) );

		// Test 2.
		$hex = '#FFFFFF';
		$this->assertEquals( [ 'r' => 255, 'g' => 255, 'b' => 255 ], $method->invoke( $generator, $hex ) );

		// Test 3.
		$hex = '#FFF';
		$this->assertEquals( [ 'r' => 255, 'g' => 255, 'b' => 255 ], $method->invoke( $generator, $hex ) );

		// Test 4.
		$hex = '#000000';
		$this->assertEquals( [ 'r' => 0, 'g' => 0, 'b' => 0 ], $method->invoke( $generator, $hex ) );

		// Test 5.
		$hex = '#000';
		$this->assertEquals( [ 'r' => 0, 'g' => 0, 'b' => 0 ], $method->invoke( $generator, $hex ) );

		// Test 6.
		$hex = '#ABCDEF';
		$this->assertEquals( [ 'r' => 171, 'g' => 205, 'b' => 239 ], $method->invoke( $generator, $hex ) );

		// Test 7.
		$hex = '#ABC';
		$this->assertEquals( [ 'r' => 170, 'g' => 187, 'b' => 204 ], $method->invoke( $generator, $hex ) );

		// Test 8.
		$hex = 'FF0000';
		$this->assertEquals( [ 'r' => 255, 'g' => 0, 'b' => 0 ], $method->invoke( $generator, $hex ) );

		// Test 9.
		$hex = 'F00';
		$this->assertEquals( [ 'r' => 255, 'g' => 0, 'b' => 0 ], $method->invoke( $generator, $hex ) );

		// Test 10.
		$hex = '#';
		$this->assertFalse( $method->invoke( $generator, $hex ) );

		// Test 11.
		$hex = '#0000000';
		$this->assertFalse( $method->invoke( $generator, $hex ) );

		// Test 12.
		$hex = '#0000';
		$this->assertFalse( $method->invoke( $generator, $hex ) );
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::__construct
	 */
	public function testConstructorForEmptyArg() {
		$generator = new \Anspress\Addons\Avatar\Generator( '' );

		// Test.
		$this->assertEquals( 'anonymous', $generator->name );
		$this->assertEquals( 'anonymous', $generator->user_id );
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::__construct
	 */
	public function testConstructorForUserAsInteger() {
		$user_id = $this->factory()->user->create();
		$generator = new \Anspress\Addons\Avatar\Generator( $user_id );

		// Test.
		$this->assertEquals( get_userdata( $user_id )->display_name, $generator->name );
		$this->assertEquals( $user_id, $generator->user_id );
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::__construct
	 */
	public function testConstructorForUserAsInstanceOfWPComment() {
		$user_id = $this->factory()->user->create( [ 'display_name' => 'Test Comment User' ] );
		$id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( array( 'comment_post_ID' => $id, 'comment_type' => 'anspress', 'user_id' => $user_id ) );
		$comment = get_comment( $comment_id );
		$this->assertInstanceof( 'WP_Comment', $comment );
		$generator = new \Anspress\Addons\Avatar\Generator( $comment );

		// Test.
		$this->assertEquals( 'Test Comment User', $generator->name );
		$this->assertEquals( $comment->user_id, $generator->user_id );
	}

	/**
	 * @covers Anspress\Addons\Avatar\Generator::__construct
	 */
	public function testConstructorForUserAsInstanceOfWPUser() {
		$user_id = $this->factory()->user->create( [ 'display_name' => 'Test User' ] );
		$user = get_userdata( $user_id );
		$this->assertInstanceof( 'WP_User', $user );
		$generator = new \Anspress\Addons\Avatar\Generator( $user );

		// Test.
		$this->assertEquals( 'Test User', $generator->name );
		$this->assertEquals( $user->ID, $generator->user_id );
	}
}
