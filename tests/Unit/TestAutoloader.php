<?php

namespace Tests\Unit;

use Mockery;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;
use AnsPress\Core;

/**
 * @covers \AnsPress\Core\autoloader
 * @package Tests\Unit
 */
class TestAutoloader extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        require_once PLUGIN_DIR . '/src/autoloader.php';
    }

    public function testShouldReturnWhenNamespaceNotMatches()
	{
		Functions\expect('strpos')
			->times(1)
			->andReturn(false);

		$this->assertFalse(class_exists('AnsPress\Core\TestClass'));
	}

	public function testForFileExists()
	{
		Functions\expect('strpos')
			->times(1)
			->andReturn(true);

		Functions\expect('file_exists')
			->times(1)
			->with(PLUGIN_DIR . '/src/TestClass.php')
			->andReturn(false);

		Functions\expect('wp_sprintf')
			->times(1)
			->andReturn('Class AnsPress\Core\TestClass not found');

		Functions\expect('esc_html__')
			->once()
			->andReturn('Class %1$s not found');

		Functions\expect('esc_html')
			->times(1)
			->andReturn(PLUGIN_DIR . '/src/TestClass.php');

		$this->expectException(\Exception::class);

		$this->assertTrue(class_exists('AnsPress\Core\TestClass'));
	}
}
