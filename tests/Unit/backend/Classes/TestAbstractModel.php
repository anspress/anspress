<?php
namespace Tests\Unit\Functions\src\backend\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractSchema;
use AnsPress\Classes\Container;
use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\DBException;
use AnsPress\Exceptions\InvalidColumnException;
use Mockery;
use Yoast\WPTestUtils\BrainMonkey\TestCase;
use Brain\Monkey\Functions;
use wpdb;

require_once PLUGIN_DIR . '/src/backend/autoloader.php';

class MockSchema extends AbstractSchema
{
	public function getTableName(): string
	{
		global $wpdb;

		return $wpdb->prefix . 'mock_table';
	}

	public function getPrimaryKey(): string {
		return 'id';
	}

	public function getColumns(): array {
		return [
			'id'         => '%d',
			'name'       => '%s',
			'email'      => '%s',
			'created_at' => '%s',
			'updated_at' => '%s'
		];
	}
}

class MockModelWithTrait extends AbstractModel
{

	public static function createSchema(): AbstractSchema
	{
		return Plugin::get(MockSchema::class);
	}
}

define( 'ARRAY_A', 'ARRAY_A');

/**
 * @covers AnsPress\Classes\AbstractModel
 * @package Tests\Unit
 */
class TestAbstractModel extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		Functions\expect('current_time')->andReturn('2024-05-13 15:30:00');

		Plugin::make(PLUGIN_DIR . '/anspress-question-answer.php', '5.0.0', 38, '8.1', '5.8.0', new Container);
	}

	protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

	private function setupDBMock() {
		global $wpdb;
		$wpdb = Mockery::mock(wpdb::class)->makePartial();
		$wpdb->ap_votes = 'wp_ap_votes';
		$wpdb->prefix = 'wp_';

		return $wpdb;
	}

	public function testValidColumn() {
        $model = new MockModelWithTrait();

        $this->assertTrue($model->getSchema()->isValidColumn('id'));
        $this->assertFalse($model->getSchema()->isValidColumn('invalid_column'));
    }

	public function testFill() {
		$class = new MockModelWithTrait();

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

		$model = new MockModelWithTrait();

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
		$model = new MockModelWithTrait();

		$model->fillInitial();

		$this->assertEquals(0, $model->getAttribute('id'));
		$this->assertEquals('', $model->getAttribute('name'));
		$this->assertEquals('2024-05-13 15:30:00', $model->getAttribute('created_at'));
		$this->assertEquals('2024-05-13 15:30:00', $model->getAttribute('updated_at'));
	}

	public function testSetAttribute() {
		$model = new MockModelWithTrait();

		$model->setAttribute('id', 1);
		$model->setAttribute('name', 'Test Model');

		$this->assertEquals(1, $model->getAttribute('id'));
		$this->assertEquals('Test Model', $model->getAttribute('name'));
	}

	public function testSetAttributeWithCustomMethod() {
		$model = new class extends MockModelWithTrait {
			public function setFloatValAttribute($value) {
				return 999.111;
			}
		};

		$model->setAttribute('float_val', '11.11');

		$this->assertEquals(999.111, $model->getAttribute('float_val'));
	}

	public function testSetAttributeDefaultValueOnlyOnNew() {
		$model = new class extends MockModelWithTrait {
			public function getNameColumnDefaultValue() {
				return 'UPDATED';
			}
		};

		$model->setIsNew(false); // Set model as not new.

		$this->assertEquals('UPDATED', $model->name);

		$model->setAttribute('id', 2);
		$model->setAttribute('name', 'Test Model 2');

		$this->assertEquals(2, $model->getAttribute('id'));
		$this->assertEquals('Test Model 2', $model->getAttribute('name'));
	}

	public function testGetAttributeWithoutMethod() {
		$model = new MockModelWithTrait();

		$model->setAttribute('id', 1);
		$model->setAttribute('name', 'Test Model');

		$this->assertEquals(1, $model->getAttribute('id'));
		$this->assertEquals('Test Model', $model->getAttribute('name'));
	}

	public function testGetAttributeWithMethod() {
		$model = new class extends MockModelWithTrait {
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

		$this->assertEquals(2, $model->getAttribute('id'));
		$this->assertEquals('TEST MODEL', $model->getAttribute('name'));
	}

	public function testGetColumnDefaultValueException() {
		$class = new MockModelWithTrait();

		// Mock esc_attr function.
		Functions\expect('esc_attr');

		$model = Mockery::mock($class)->makePartial();

		$model->shouldReceive('isValidColumn')->andReturn(false); // All columns are valid for this test.

		$this->expectException(InvalidColumnException::class);
		$model->getColumnDefaultValue('invalid_column');
	}

	public function testGetPrimaryKey() {
		$model = new MockModelWithTrait();

		$this->assertEquals('id', $model->getSchema()->getPrimaryKey());
	}

	public function testGetTableName() {
		$model = new MockModelWithTrait();

		// Mock wpdb global variable.
		global $wpdb;
		$wpdb = Mockery::mock(wpdb::class)->makePartial();
		$wpdb->prefix = 'wp_';

		$this->assertEquals('wp_mock_table', $model->getSchema()->getTableName());
	}

	public function testGetAttributes() {
		$model = new MockModelWithTrait;

		$this->assertEquals(['id' => 0, 'name' => '', 'email' => '', 'created_at' => '2024-05-13 15:30:00', 'updated_at' => '2024-05-13 15:30:00'], $model->getAttributes());
	}

	public function testGetFormatString() {
		$model = new MockModelWithTrait;

		$this->assertEquals('%d', $model->getSchema()->getFormatString('id'));
		$this->assertEquals('%s', $model->getSchema()->getFormatString('name'));
		$this->assertEquals('%s', $model->getSchema()->getFormatString('created_at'));
	}

	public function testGetOriginalValid() {
		$model = new MockModelWithTrait;

		$model->fill(['id' => 1, 'name' => 'Test Model']);

		$this->assertEquals(0, $model->getOriginal('id'));
		$this->assertEquals('', $model->getOriginal('name'));
	}

	public function testGetOriginalInvalid() {
		$model = new MockModelWithTrait;

		Functions\expect('esc_attr');

		$this->expectException(InvalidColumnException::class);
		$model->getOriginal('invalid_column');
	}

	public function testGetFormatStrings() {
		$model = new MockModelWithTrait;

		$this->assertEquals(['%d', '%s', '%s'], $model->getSchema()->getFormatStrings(['id', 'name', 'created_at']));
		$this->assertEquals(['%d', '%s'], $model->getSchema()->getFormatStrings(['id', 'name']));
	}

	public function testToArray() {
		$model = new MockModelWithTrait;

		$model->fill(['id' => 1, 'name' => 'Test Model', 'created_at' => '2023-01-01 12:00:00', 'updated_at' => '2023-01-02 12:00:00', 'email' => 'rah12@live.com']);

		$this->assertEquals(['id' => 1, 'name' => 'Test Model', 'created_at' => '2023-01-01 12:00:00', 'updated_at' => '2023-01-02 12:00:00', 'email' => 'rah12@live.com'], $model->toArray());
	}

	public function testToJson() {
		$model = new MockModelWithTrait;

		$model->fill(['id' => 1, 'name' => 'Test Model', 'created_at' => '2023-01-01 12:00:00', 'updated_at' => '2023-01-02 12:00:00']);

		$this->assertEquals('{"id":1,"name":"Test Model","email":"","created_at":"2023-01-01 12:00:00","updated_at":"2023-01-02 12:00:00"}', $model->toJson());
	}

	public function testGetterMethod() {
		$model = new MockModelWithTrait;

		$model->fill(['id' => 2, 'name' => 'TEST MODEL']);

		$this->assertEquals(2, $model->id);
		$this->assertEquals('TEST MODEL', $model->name);
	}

	public function testInvalidArgumentInGetterMethod() {
		$model = new MockModelWithTrait;

		// Mock esc_attr function.
		Functions\expect('esc_attr');

		$this->expectException(\InvalidArgumentException::class);
		$model->invalid_column;
	}

	public function testGetColumnFormat() {
		$model = new MockModelWithTrait;

		$this->assertEquals('%d', $model->getSchema()->getColumnFormat('id'));
		$this->assertEquals('%s', $model->getSchema()->getColumnFormat('name'));
	}

	public function testHydrate() {
		$model = new MockModelWithTrait;

		$rows = [
			['id' => 1, 'name' => 'Test Model 1'],
			['id' => 2, 'name' => 'Test Model 2'],
		];

		$models = $model::hydrate($rows);

		$this->assertCount(2, $models);
		$this->assertEquals(1, $models[0]->id);
		$this->assertEquals('Test Model 1', $models[0]->name);
		$this->assertEquals(2, $models[1]->id);
		$this->assertEquals('Test Model 2', $models[1]->name);
	}

	public function testFloatFormat() {

		$model = new class extends AbstractModel {
			public static function createSchema(): AbstractSchema
			{
				return new class extends AbstractSchema {
					public function getPrimaryKey(): string
					{
						return 'id';
					}

					public function getTableName(): string
					{
						global $wpdb;

						return $wpdb->prefix . 'mock_table';
					}

					public function getColumns(): array
					{
						return [
							'id'       => '%d',
							'float_val' => '%f',
						];
					}
				};
			}
		};

		$model->fill(['float_val' => '11.11']);

		$this->assertEquals(11.11, $model->float_val);
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
            ->with('SELECT * FROM mock_table WHERE id = 1', 'ARRAY_A')
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
			->with('SELECT * FROM mock_table WHERE id = 1', 'ARRAY_A')
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
			->with('SELECT * FROM mock_table WHERE id = 1', 'ARRAY_A')
			->andReturn(['id' => 1, 'name' => 'John Doe', 'email' => 'john@doe.com']);

		// Call the method under test
		$result = MockModelWithTrait::find(1);

		// Assertions
		$this->assertInstanceOf(MockModelWithTrait::class, $result); // Ensure a model is returned
		$this->assertEquals(1, $result->id);
		$this->assertEquals('John Doe', $result->name);
		$this->assertEquals('john@doe.com', $result->email);
	}

	public function testCreatePassed() {
		$wpdb = $this->setupDBMock();

		$wpdb->insert_id = 1;

		// Set up the expected query and result
		$wpdb->shouldReceive('insert')
			->with('wp_mock_table', ['name' => 'John Doe', 'email' => 'john@doe.com'], ['%s', '%s'])
			->andReturn(1);

		$wpdb->shouldReceive('prepare')
			->with("SELECT * FROM wp_mock_table WHERE id = %d", 1)
			->andReturn('SELECT * FROM wp_mock_table WHERE id = 1');

		$wpdb->shouldReceive('get_row')
			->with('SELECT * FROM wp_mock_table WHERE id = 1', 'ARRAY_A')
			->andReturn(['id' => 1, 'name' => 'John Doe', 'email' => 'john@doe.com']);

		// Functions\expect('do_action')
		// 	->with('anspress/model/failed_to_insert', 'mock_table', ['name' => 'John Doe', 'email' => 'john@doe.com'], 'Error message');

		// Call the method under test
		$result = MockModelWithTrait::create(['name' => 'John Doe', 'email' => 'john@doe.com']);

		// Assertions
		$this->assertInstanceOf(MockModelWithTrait::class, $result); // Ensure a model is returned
		$this->assertEquals(1, $result->id);
		$this->assertEquals('John Doe', $result->name);
		$this->assertEquals('john@doe.com', $result->email);
	}

	public function testCreateFailed() {
		$wpdb = $this->setupDBMock();

		$wpdb->insert_id = 1;
		$wpdb->last_error = 'Error message';

		// Set up the expected query and result
		$wpdb->shouldReceive('insert')
			->with('wp_mock_table', ['name' => 'John Doe', 'email' => 'john@doe.com'], ['%s', '%s'])
			->andReturn(false);

		$wpdb->shouldReceive('prepare')
			->with("SELECT * FROM wp_mock_table WHERE id = %d", 1)
			->andReturn('SELECT * FROM wp_mock_table WHERE id = 1');

		$wpdb->shouldReceive('get_row')
			->with('SELECT * FROM wp_mock_table WHERE id = 1', 'ARRAY_A')
			->andReturn(['id' => 1, 'name' => 'John Doe', 'email' => 'john@doe.com']);

		Functions\expect('do_action')
			->with('anspress/model/failed_to_insert', 'wp_mock_table', ['name' => 'John Doe', 'email' => 'john@doe.com'], 'Error message');

		Functions\expect('esc_html')
			->with('Error message')
			->andReturn('Error message');

		$this->expectException( DBException::class );

		// Call the method under test
		MockModelWithTrait::create(['name' => 'John Doe', 'email' => 'john@doe.com']);
	}

	public function testUpdateFailed() {
		$wpdb = $this->setupDBMock();

		$wpdb->last_error = 'Error message';

		// Set up the expected query and result
		$wpdb->shouldReceive('update')
			->with(
					'wp_mock_table',
					[
						"name"       => "John Doe",
						"email"      => "rah12@live.com",
						"created_at" => "2024-05-13 15:30:00",
						"updated_at" => "2024-05-13 15:30:00"
					],
					['id' => 1],
					['%s', '%s', '%s', '%s'],
					['%d']
				)
			->andReturn(0);

		$wpdb->shouldReceive('prepare')
			->with("SELECT * FROM wp_mock_table WHERE id = %d", 1)
			->andReturn('SELECT * FROM wp_mock_table WHERE id = 1');

		$wpdb->shouldReceive('get_row')
			->with('SELECT * FROM wp_mock_table WHERE id = 1', 'ARRAY_A')
			->andReturn(['id' => 1, 'name' => 'John Doe', 'email' => 'rah12@live.com']);

		// Expect do_action call.
		Functions\expect(
			'do_action',
			[
				'anspress/model/failed_to_update',
				'wp_mock_table',
				[
					'name'       => 'John Doe',
					'email'      => 'rah12@live.com',
					"created_at" => "2024-05-13 15:30:00",
					"updated_at" => "2024-05-13 15:30:00"
				],
				'Error message'
			]
		);

		Functions\expect('esc_html')
			->with('Error message')
			->andReturn('Error message');

		// Call the method under test
		$result = new MockModelWithTrait(['name' => 'John Doe', 'email' => 'rah12@live.com']);
		$result->fill(['id' => 1]); // Set the ID.

		// Assertions
		$this->assertNull($result->update());
	}

	public function testUpdatePassed() {
		$wpdb = $this->setupDBMock();

		// Set up the expected query and result
		$wpdb->shouldReceive('update')
			->with(
					'wp_mock_table',
					[
						"name"       => "John Doe",
						"email"      => "rah12@live.com",
						"created_at" => "2024-05-13 15:30:00",
						"updated_at" => "2024-05-13 15:30:00"
					],
					['id' => 1],
					['%s', '%s', '%s', '%s'],
					['%d']
				)
			->andReturn(1);

		$wpdb->shouldReceive('prepare')
			->with("SELECT * FROM wp_mock_table WHERE id = %d", 1)
			->andReturn('SELECT * FROM wp_mock_table WHERE id = 1');

		$wpdb->shouldReceive('get_row')
			->with('SELECT * FROM wp_mock_table WHERE id = 1', 'ARRAY_A')
			->andReturn(['id' => 1, 'name' => 'John Doe', 'email' => 'rah12@live.com']);

		// Expect do_action call.
		Functions\expect(
			'do_action',
			[
				'anspress/model/after_update',
				'wp_mock_table',
				[
					'name'       => 'John Doe',
					'email'      => 'rah12@live.com',
					"created_at" => "2024-05-13 15:30:00",
					"updated_at" => "2024-05-13 15:30:00"
				]
			]
		);

		// Call the method under test
		$result = new MockModelWithTrait(['name' => 'John Doe', 'email' => 'rah12@live.com']);
		$result->fill(['id' => 1]); // Set the ID.

		$result->update();

		// Assertions
		$this->assertInstanceOf(MockModelWithTrait::class, $result); // Ensure a model is returned
	}

	public function testDeleteFailed() {
		$wpdb = $this->setupDBMock();

		$wpdb->last_error = 'Error message';

		// Set up the expected query and result
		$wpdb->shouldReceive('delete')
			->with('wp_mock_table', ['id' => 1], ['%d'])
			->andReturn(0);

		// Expect do_action call.
		Functions\expect(
			'do_action',
			[
				'anspress/model/failed_to_delete',
				'wp_mock_table',
				['id' => 1],
				'Error message'
			]
		);

		Functions\expect('esc_html')
			->with('Error message')
			->andReturn('Error message');

		// Call the method under test
		$result = new MockModelWithTrait();
		$result->fill(['id' => 1]); // Set the ID.
		$result->setIsNew(false); // Set the model as not new.

		// Assertions
		$this->assertFalse($result->delete());
	}

	public function testDeletePassed() {
		$wpdb = $this->setupDBMock();

		// Set up the expected query and result
		$wpdb->shouldReceive('delete')
			->with('wp_mock_table', ['id' => 1], ['%d'])
			->andReturn(1);

		// Expect do_action call.
		Functions\expect(
			'do_action',
			[
				'anspress/model/after_delete',
				'wp_mock_table',
				['id' => 1]
			]
		);

		// Call the method under test
		$result = new MockModelWithTrait();
		$result->fill(['id' => 1]); // Set the ID.
		$result->setIsNew(false); // Set the model as not new.

		$result->delete();

		// Assertions
		$this->assertInstanceOf(MockModelWithTrait::class, $result); // Ensure a model is returned
	}

	public function testDeleteWhenNotExists() {
		$wpdb = $this->setupDBMock();

		// Set up the expected query and result
		$wpdb->shouldReceive('delete')
			->with('wp_mock_table', ['id' => 1], ['%d'])
			->andReturn(0);

		// Call the method under test
		$result = new MockModelWithTrait();
		$result->fill(['id' => 1]); // Set the ID.
		$result->setIsNew(true); // Set the model as not new.

		$this->assertFalse($result->delete());
	}
}
