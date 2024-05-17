<?php
namespace Tests\Unit\src\backend\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Traits\FindableTrait;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;
use Mockery;
use wpdb;

class MockModelWithTrait extends AbstractModel
{
    use FindableTrait;

    protected static $primaryKey = 'id';
    protected static $columns = ['id' => '%d', 'name' => '%s', 'email' => '%s'];
}

define( 'ARRAY_A', 'ARRAY_A' );

/**
 * @covers AnsPress\Traits\FindableTrait
 * @package Tests\Unit
 */
class TestFindableTrait extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		Functions\expect('current_time')->andReturn('2024-05-13 15:30:00');

		require_once PLUGIN_DIR . '/src/backend/Classes/Str.php';
		require_once PLUGIN_DIR . '/src/backend/Exceptions/InvalidColumnException.php';
		require_once PLUGIN_DIR . '/src/backend/Interfaces/ModelInterface.php';
		require_once PLUGIN_DIR . '/src/backend/Interfaces/ModelInterface.php';
		require_once PLUGIN_DIR . '/src/backend/Classes/AbstractModel.php';
	}

	private function setupDBMock() {
		global $wpdb;
		$wpdb = Mockery::mock(wpdb::class)->makePartial();
		$wpdb->ap_votes = 'wp_ap_votes';
		$wpdb->prefix = 'wp_';

		return $wpdb;
	}

	public function testFindByPrimaryKeySuccess()
    {
		// Mock esc_attr.
		// Functions\expect('esc_attr');

        $wpdb = $this->setupDBMock();

        // Set up the expected query and result
        $wpdb->shouldReceive('prepare')
            // ->with("SELECT * FROM mock_table WHERE id = %d", 1)
            ->andReturn('SELECT * FROM mock_table WHERE id = 1');

        $wpdb->shouldReceive('get_row')
            ->with('SELECT * FROM mock_table WHERE id = 1', ARRAY_A)
            ->andReturn(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']);

        // Create an instance of your mock model
        $model = new MockModelWithTrait();

        // Call the method under test
        $result = $model->findByPrimaryKey(1);

        // Assertions
        $this->assertInstanceOf(MockModelWithTrait::class, $result); // Ensure a model is returned
        $this->assertEquals(1, $result->id);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john@example.com', $result->email);
    }

	public function testFindByPrimaryKeyFailure()
	{
		$wpdb = $this->setupDBMock();

		// Set up the expected query and result
		$wpdb->shouldReceive('prepare')
			->andReturn('SELECT * FROM mock_table WHERE id = 1');

		$wpdb->shouldReceive('get_row')
			->with('SELECT * FROM mock_table WHERE id = 1', ARRAY_A)
			->andReturn(null);

		// Create an instance of your mock model
		$model = new MockModelWithTrait();

		// Call the method under test
		$result = $model->findByPrimaryKey(1);

		// Assertions
		$this->assertNull($result); // Ensure null is returned
	}

	public function testFindMethod() {
		$wpdb = $this->setupDBMock();

		// Set up the expected query and result
		$wpdb->shouldReceive('prepare')
			->andReturn('SELECT * FROM mock_table WHERE id = 1');

		$wpdb->shouldReceive('get_row')
			->with('SELECT * FROM mock_table WHERE id = 1', ARRAY_A)
			->andReturn(['id' => 1, 'name' => 'John Doe', 'email' => 'john@doe.com']);

		// Call the method under test
		$result = MockModelWithTrait::find(1);

		// Assertions
		$this->assertInstanceOf(MockModelWithTrait::class, $result); // Ensure a model is returned
		$this->assertEquals(1, $result->id);
		$this->assertEquals('John Doe', $result->name);
		$this->assertEquals('john@doe.com', $result->email);
	}
}
