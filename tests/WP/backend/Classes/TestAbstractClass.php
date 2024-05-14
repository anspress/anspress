<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Exceptions\InvalidColumnException;
use Mockery;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;
use wpdb;

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

	public function testFill() {
		$class = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s'];
			protected $timestamps = true;
		};

        $model = Mockery::mock($class)->makePartial(); // Partial mock allows overriding methods.
        $model->shouldReceive('isValidColumn')->andReturn(true); // All columns are valid for this test.

        Functions\expect('esc_attr');

		// Test with valid columns.
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

		// Test with invalid column.
		$this->expectException(InvalidColumnException::class);
		$model->fill(['invalid_column' => 'some value']);
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

		// Test custom values
        $model->fill(['created_at' => '2023-01-01 00:00:00', 'updated_at' => '2023-02-02 00:00:00']);
        $this->assertEquals('2023-01-01 00:00:00', $model->created_at);
        $this->assertEquals('2023-02-02 00:00:00', $model->updated_at);

		$this->expectException(InvalidColumnException::class);
		$model->setAttribute('invalid_column', 'some value'); // Invalid column
	}

	public function testFillInitial() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'created_at' => '%s', 'updated_at' => '%s'];
		};

		$model->fillInitial();

		$this->assertEquals(0, $model->getAttribute('id'));
		$this->assertEquals('', $model->getAttribute('name'));
		$this->assertEquals('2024-05-13 15:30:00', $model->getAttribute('created_at'));
		$this->assertEquals('2024-05-13 15:30:00', $model->getAttribute('updated_at'));
	}

	public function testSetAttribute() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'float_val' => '%f'];
		};

		$model->setAttribute('id', 1);
		$model->setAttribute('name', 'Test Model');
		$model->setAttribute('float_val', '11.11');

		$this->assertEquals(1, $model->getAttribute('id'));
		$this->assertEquals('Test Model', $model->getAttribute('name'));
		$this->assertEquals(11.11, $model->getAttribute('float_val'));
	}

	public function testSetAttributeWithCustomMethod() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'float_val' => '%f'];

			public function setFloatValAttribute($value) {
				return 999.111;
			}
		};

		$model->setAttribute('float_val', '11.11');

		$this->assertEquals(999.111, $model->getAttribute('float_val'));
	}

	public function testSetAttributeDefaultValueOnlyOnNew() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'float_val' => '%f'];

			public function getFloatValColumnDefaultValue() {
				return 1100.11;
			}
		};

		$model->setAttribute('float_val', null);

		$this->assertEquals(1100.11, $model->getAttribute('float_val'));

		$model->setIsNew(false); // Set model as not new.

		$model->setAttribute('id', 2);
		$model->setAttribute('name', 'Test Model 2');
		$model->setAttribute('float_val', '22.22');

		$this->assertEquals(2, $model->getAttribute('id'));
		$this->assertEquals('Test Model 2', $model->getAttribute('name'));
		$this->assertEquals(22.22, $model->getAttribute('float_val'));
	}

	public function testGetAttributeWithoutMethod() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'float_val' => '%f'];
		};

		$model->setAttribute('id', 1);
		$model->setAttribute('name', 'Test Model');
		$model->setAttribute('float_val', '11.11');

		$this->assertEquals(1, $model->getAttribute('id'));
		$this->assertEquals('Test Model', $model->getAttribute('name'));
		$this->assertEquals(11.11, $model->getAttribute('float_val'));
	}

	public function testGetAttributeWithMethod() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'float_val' => '%f'];

			public function getIdAttribute($value) {
				return $value + 1;
			}

			public function getNameAttribute($value) {
				return strtoupper($value);
			}

			public function getFloatValAttribute($value) {
				return $value * 2;
			}
		};

		$model->setAttribute('id', 1);
		$model->setAttribute('name', 'Test Model');
		$model->setAttribute('float_val', '11.11');

		$this->assertEquals(2, $model->getAttribute('id'));
		$this->assertEquals('TEST MODEL', $model->getAttribute('name'));
		$this->assertEquals(22.22, $model->getAttribute('float_val'));
	}

	public function testGetColumnDefaultValueException() {
		$class = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'float_val' => '%f'];
		};

		// Mock esc_attr function.
		Functions\expect('esc_attr');

		$model = Mockery::mock($class)->makePartial();

		$model->shouldReceive('isValidColumn')->andReturn(false); // All columns are valid for this test.

		$this->expectException(InvalidColumnException::class);
		$model->getColumnDefaultValue('invalid_column');
	}

	public function testGetPrimaryKey() {
		$model = new class extends AbstractModel {
			protected $primaryKey = 'id_custom';
		};

		$this->assertEquals('id_custom', $model->getPrimaryKey());
	}

	public function testGetTableName() {
		$model = new class extends AbstractModel {
			protected $tableName = 'custom_table';
		};

		// Mock wpdb global variable.
		global $wpdb;
		$wpdb = Mockery::mock(wpdb::class)->makePartial();
		$wpdb->prefix = 'wp_';

		$this->assertEquals('wp_custom_table', $model->getTableName());
	}

	public function testGetAttributes() {
		$model = new class extends AbstractModel {
			protected $attributes = ['id', 'name', 'created_at', 'updated_at'];
		};

		$this->assertEquals(['id', 'name', 'created_at', 'updated_at'], $model->getAttributes());
	}

	public function testGetFormatString() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'created_at' => '%s', 'float_val' => '%f'];
		};

		$this->assertEquals('%d', $model->getFormatString('id'));
		$this->assertEquals('%s', $model->getFormatString('name'));
		$this->assertEquals('%s', $model->getFormatString('created_at'));
		$this->assertEquals('%f', $model->getFormatString('float_val'));
	}

	public function testGetOriginalValid() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s'];
		};

		$model->fill(['id' => 1, 'name' => 'Test Model']);

		$this->assertEquals(0, $model->getOriginal('id'));
		$this->assertEquals('', $model->getOriginal('name'));
	}

	public function testGetOriginalInvalid() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s'];
		};

		Functions\expect('esc_attr');

		$this->expectException(InvalidColumnException::class);
		$model->getOriginal('invalid_column');
	}

	public function testGetFormatStrings() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'created_at' => '%s', 'float_val' => '%f'];
		};

		$this->assertEquals(['%d', '%s', '%s'], $model->getFormatStrings(['id', 'name', 'created_at']));
		$this->assertEquals(['%d', '%s'], $model->getFormatStrings(['id', 'name']));
	}

	public function testToArray() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'created_at' => '%s', 'updated_at' => '%s'];
		};

		$model->fill(['id' => 1, 'name' => 'Test Model', 'created_at' => '2023-01-01 12:00:00', 'updated_at' => '2023-01-02 12:00:00']);

		$this->assertEquals(['id' => 1, 'name' => 'Test Model', 'created_at' => '2023-01-01 12:00:00', 'updated_at' => '2023-01-02 12:00:00'], $model->toArray());
	}

	public function testToJson() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s', 'created_at' => '%s', 'updated_at' => '%s'];
		};

		$model->fill(['id' => 1, 'name' => 'Test Model', 'created_at' => '2023-01-01 12:00:00', 'updated_at' => '2023-01-02 12:00:00']);

		$this->assertEquals('{"id":1,"name":"Test Model","created_at":"2023-01-01 12:00:00","updated_at":"2023-01-02 12:00:00"}', $model->toJson());
	}

	public function testGetterMethod() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s'];
		};

		$model->fill(['id' => 2, 'name' => 'TEST MODEL']);

		$this->assertEquals(2, $model->id);
		$this->assertEquals('TEST MODEL', $model->name);
	}

	public function testInvalidArgumentInGetterMethod() {
		$model = new class extends AbstractModel {
			protected $columns = ['id' => '%d', 'name' => '%s'];
		};

		// Mock esc_attr function.
		Functions\expect('esc_attr');

		$this->expectException(\InvalidArgumentException::class);
		$model->invalid_column;
	}
}
