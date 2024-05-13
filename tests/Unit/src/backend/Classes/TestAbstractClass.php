<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Exceptions\InvalidColumnException;
use Mockery;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers AnsPress\Classes\AbstractModel
 * @package Tests\Unit
 */
class TestAbstractClass extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		Functions\expect('current_time')->andReturn('2024-05-13 15:30:00');

		require_once PLUGIN_DIR . '/src/backend/Classes/Str.php';
		require_once PLUGIN_DIR . '/src/backend/Exceptions/InvalidColumnException.php';
		require_once PLUGIN_DIR . '/src/backend/Interfaces/ModelInterface.php';
		require_once PLUGIN_DIR . '/src/backend/Interfaces/ModelInterface.php';
		require_once PLUGIN_DIR . '/src/backend/Classes/AbstractModel.php';
	}

	protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

	public function testValidColumn() {
        $model = new class extends AbstractModel {
            protected $columns = ['id' => '%d', 'name' => '%s', 'created_at' => '%s', 'updated_at' => '%s'];
        };

        $this->assertTrue($model->isValidColumn('id'));
        $this->assertFalse($model->isValidColumn('invalid_column'));
    }

	public function testFillWithValidAttributes() {
		$class = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s'];
			protected $timestamps = true;
		};

        $model = Mockery::mock($class)->makePartial(); // Partial mock allows overriding methods.
        $model->shouldReceive('isValidColumn')->andReturn(true); // All columns are valid for this test.

        Functions\expect('esc_attr')->never();

        $attributes = [
            'id'         => 1,
            'name'       => 'Test Model',
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-02 12:00:00',
        ];

        $model->fill($attributes);

        $this->assertEquals(1, $model->getAttribute('id'));
        $this->assertEquals('Test Model', $model->getAttribute('name'));
        $this->assertEquals('2023-01-01 12:00:00', $model->getAttribute('created_at')); // Verify timestamp override.
        $this->assertEquals('2023-01-02 12:00:00', $model->getAttribute('updated_at')); // Verify timestamp override.
    }

	public function testTimestampsAndColumnSetting() {
		Functions\expect('esc_attr');

		$model = new class extends AbstractModel {
			protected $timestamps = true;
			protected $columns = ['id' => '%d', 'name' => '%s', 'created_at' => '%s', 'updated_at' => '%s'];
		};

		$model->fill(['id' => 1, 'name' => 'Test Model']);

		// Timestamps should be set automatically
		$this->assertEquals('2024-05-13 15:30:00', $model->created_at);
		$this->assertEquals('2024-05-13 15:30:00', $model->updated_at);

		// Column setting and validation
		$model->setAttribute('id', 10); // Valid column, should update
		$this->assertEquals(10, $model->id);

		$this->expectException(InvalidColumnException::class);
		$model->setAttribute('invalid_column', 'some value'); // Invalid column
	}

}
