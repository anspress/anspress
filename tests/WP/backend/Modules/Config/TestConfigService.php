<?php

namespace Tests\Unit\src\backend\Classes;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Container;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use AnsPress\Modules\Config\ConfigService;

/**
 * @covers AnsPress\Modules\Config\ConfigService
 * @package Tests\WP
 */
class TestConfigService extends TestCase {
	public function setUp() : void{
		parent::setUp();

		delete_option(ConfigService::OPTION_NAME);
	}

	public function testGetterMethod() {
		$service = new ConfigService();

		$service->registerDefaults([
			'sample_option' => [
				'value' => 'sample_value',
				'type' => 'string'
			]
		]);

		// Test getter magic method.
		$this->assertEquals($service->sample_option, 'sample_value');
	}

	public function testGetMethod() {
		$service = new ConfigService();

		$service->registerDefaults([
			'sample_option' => [
				'value' => 'sample_value',
				'type' => 'string'
			],
			'sample_int_option' => [
				'value' => 10,
				'type' => 'integer'
			],
			'sample_double_option' => [
				'value' => 10.5,
				'type' => 'double'
			],
			'sample_bool_option' => [
				'value' => true,
				'type' => 'boolean'
			],
		]);

		$this->assertEquals($service->get('sample_option'), 'sample_value');
		$this->assertEquals($service->get('sample_int_option'), 10);
		$this->assertEquals($service->get('sample_double_option'), 10.5);
		$this->assertEquals($service->get('sample_bool_option'), true);

		// Test updated values.
		$service->set('sample_option', 'new_value_UPDATED');
		$service->set('sample_int_option', '10001');
		$service->set('sample_double_option', '10001.5');
		$service->set('sample_bool_option', false);

		$this->assertEquals($service->get('sample_option'), 'new_value_UPDATED');
		$this->assertEquals($service->get('sample_int_option'), 10001);
		$this->assertEquals($service->get('sample_double_option'), 10001.5);
		$this->assertEquals($service->get('sample_bool_option'), false);

	}

	public function testGetCheckForceTypeSetting() {
		$service = new ConfigService();

		$service->registerDefaults([
			'sample_option' => [
				'value' => 'sample_value',
				'type' => 'string'
			],
		]);

		// Test force type setting.
		$this->assertEquals($service->get('sample_option'), 'sample_value');

		// Update option directly.
		update_option(ConfigService::OPTION_NAME, ['sample_option' => 10.11]);

		$service->clearCache();

		// Test force type setting.
		$this->assertEquals($service->get('sample_option'), '10.11');
	}

	public function testSetInvalidArgument() {
		$service = new ConfigService();

		$this->expectException(\InvalidArgumentException::class);
		$service->set('invalid_option', 'sample_value');
	}

	public function testRegisterDefaultExceptionForValue() {
		$service = new ConfigService();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('sample_option: When registering options [value] and [type] must be present.');
		$service->registerDefaults([
			'sample_option' => [
			]
		]);
	}

	public function testRegisterDefaultExceptionForType() {
		$service = new ConfigService();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('sample_option: When registering options [value] and [type] must be present.');
		$service->registerDefaults([
			'sample_option' => [
				'value' => 'sample_value'
			]
		]);
	}

	public function testRegisterDefaultExceptionForValidTypes() {
		$service = new ConfigService();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('sample_option: Invalid type.');
		$service->registerDefaults([
			'sample_option' => [
				'value' => 'sample_value',
				'type' => 'invalid_type'
			]
		]);
	}

	public function testGetDefaultValueException() {
		$service = new ConfigService();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Option not registered.');
		$service->getDefault('invalid_option');
	}
}
