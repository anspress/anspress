<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAvatarGenerator extends TestCase {

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
}
