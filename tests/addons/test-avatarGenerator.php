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
}
