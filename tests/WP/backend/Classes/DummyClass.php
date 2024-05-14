<?php
/**
 * A dummy calss for testing purpose.
 *
 * @since 5.0.0
 */

namespace AnsPress\Tests\Unit\src\backend\Classes;

use AnsPress\Interfaces\ServiceInterface;
use AnsPress\Interfaces\SingletonInterface;

/**
 * Dummy class.
 */
class DummyClass implements SingletonInterface {
	protected $sampleService;

	/**
	 * Constructor class.
	 *
	 * @return void
	 */
	public function __construct(SampleService $sampleService) {
		$this->sampleService = $sampleService;
	}

	public function __clone()
	{

	}

	public function __wakeup()
	{

	}

	/**
	 * Get the sample service.
	 *
	 * @return SampleService
	 */
	public function getSampleService(): SampleService {
		return $this->sampleService;
	}
}
