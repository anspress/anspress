<?php

namespace Tests\WP\backend\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractSchema;
use AnsPress\Classes\Plugin;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

class SampleSchema extends AbstractSchema {
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix . 'test_table';
	}

	public function getPrimaryKey(): string {
		return 'id';
	}

	public function getColumns(): array {
		return array(
			'id'        => '%d',
			'str_field' => '%s',
		);
	}
}

class SampleModel extends AbstractModel {
	protected static function createSchema(): AbstractSchema {
		return new SampleSchema();
	}
}



/**
 * @covers AnsPress\Classes\AbstractModel
 * @package Tests\WP
 */
class TestAbstractModel extends TestCase {
	public function setUp() : void {
		parent::setUp();
	}

	private function createTable() {
		global $wpdb;
		// Drop table if exists.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'test_table' );

		$wpdb->query( 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'test_table (
			id int(11) NOT NULL AUTO_INCREMENT,
			str_field varchar(255) NOT NULL,
			PRIMARY KEY  (id)
		)' );
	}

	public function testCreate() {
		$this->createTable();

		$model = new SampleModel();

		$result = $model->create( array(
			'str_field' => 'test',
		) );

		global $wpdb;

		$this->assertEquals(
			[
				[
					'id'        => 1,
					'str_field' => 'test',
				]
			],
			$wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'test_table', ARRAY_A )
		);

		$this->assertInstanceOf( SampleModel::class, $result );

	}

	public function testSaveOnNew() {
		$this->createTable();

		$model = new SampleModel();

		$model->setAttribute( 'str_field', 'test' );

		$result = $model->save();

		global $wpdb;

		$this->assertEquals(
			[
				[
					'id'        => '1',
					'str_field' => 'test',
				]
			],
			$wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'test_table', ARRAY_A )
		);

		$this->assertInstanceOf( SampleModel::class, $result );
	}

	public function testSaveOnExisting() {
		$this->createTable();

		global $wpdb;

		$wpdb->insert( $wpdb->prefix . 'test_table', array(
			'str_field' => 'test',
		) );

		$model = new SampleModel();

		$model->setAttribute( 'id', 1 );
		$model->setAttribute( 'str_field', 'test2' );

		$result = $model->save();

		$this->assertEquals(
			[
				[
					'id'        => '1',
					'str_field' => 'test2',
				]
			],
			$wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'test_table', ARRAY_A )
		);

		$this->assertInstanceOf( SampleModel::class, $result );
	}

	public function testDeleteSuccess() {
		$this->createTable();

		global $wpdb;

		$wpdb->insert( $wpdb->prefix . 'test_table', array(
			'str_field' => 'test',
		) );

		$model = new SampleModel();

		$model->setAttribute( 'id', 1 );

		$result = $model->delete();

		$this->assertEquals( 1, $result );

		$this->assertEquals(
			[],
			$wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'test_table', ARRAY_A )
		);
	}

	public function testDeleteFail() {
		$this->createTable();

		$model = new SampleModel();

		$model->setAttribute( 'id', 1 );

		$this->assertFalse( $model->delete() );
	}
}
