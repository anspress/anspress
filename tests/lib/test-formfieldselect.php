<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldSelect extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Select' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Select', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Select', 'get_options' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Select', 'field_markup' ) );
	}

	/**
	 * @covers \AnsPress\Form\Field\Select::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'text_field' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Select Field', 'options' => [], 'terms_args' => [ 'taxonomy' => 'question_category', 'hide_empty' => false, 'fields' => 'id=>name' ] ], $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [ 'options' => 'terms' ] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'absint' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Select Field', 'options' => 'terms', 'terms_args' => [ 'taxonomy' => 'question_category', 'hide_empty' => false, 'fields' => 'id=>name' ] ], $field->args );

		// Test 3.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [ 'options' => 'posts' ] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'absint' ], $property->getValue( $field ) );
		$this->assertEquals( [ 'label' => 'AnsPress Select Field', 'options' => 'posts', 'terms_args' => [ 'taxonomy' => 'question_category', 'hide_empty' => false, 'fields' => 'id=>name' ] ], $field->args );

		// Test 4.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'label' => 'Sample Label',
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'text_field' ], $property->getValue( $field ) );
		$expected = [
			'label' => 'Sample Label',
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'terms_args' => [ 'taxonomy' => 'question_category', 'hide_empty' => false, 'fields' => 'id=>name' ]
		];
		$this->assertEquals( $expected, $field->args );

		// Test 5.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'label'      => 'Sample Label',
			'desc'       => 'Sample Description',
			'options'    => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'terms_args' => [],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'text_field' ], $property->getValue( $field ) );
		$expected = [
			'label' => 'Sample Label',
			'desc' => 'Sample Description',
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'terms_args' => []
		];
		$this->assertEquals( $expected, $field->args );

		// Test 6.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'label'      => 'Sample Label',
			'desc'       => 'Sample Description',
			'options'    => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'terms_args' => [
				'taxonomy' => 'sample_taxonomy',
			],
			'sanitize'   => 'custom_sanitize_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'text_field', 'custom_sanitize_cb' ], $property->getValue( $field ) );
		$expected = [
			'label'      => 'Sample Label',
			'desc'       => 'Sample Description',
			'options'    => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'terms_args' => [
				'taxonomy' => 'sample_taxonomy',
			],
			'sanitize'   => 'custom_sanitize_cb',
		];
		$this->assertEquals( $expected, $field->args );

		// Test 7.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'label'      => 'Sample Label',
			'desc'       => 'Sample Description',
			'options'    => 'posts',
			'posts_args' => [
				'post_type' => 'page',
				'showposts' => -1,
			],
			'terms_args' => [],
			'sanitize'   => 'custom_sanitize_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'sanitize_cb' );
		$property->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $property->getValue( $field ) );
		$this->assertEquals( [ 'absint', 'custom_sanitize_cb' ], $property->getValue( $field ) );
		$expected = [
			'label'      => 'Sample Label',
			'desc'       => 'Sample Description',
			'options'    => 'posts',
			'posts_args' => [
				'post_type' => 'page',
				'showposts' => -1,
			],
			'terms_args' => [],
			'sanitize'   => 'custom_sanitize_cb',
		];
		$this->assertEquals( $expected, $field->args );
	}

	/**
	 * @covers \AnsPress\Form\Field\Select::get_options
	 */
	public function testGetOptions() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$this->assertIsArray( $result );
		$expected = [
			'option1' => 'Option 1',
			'option2' => 'Option 2',
			'option3' => 'Option 3',
		];
		$this->assertEquals( $expected, $result );

		// Test 2.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'options' => 'terms',
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );

		// Test 3.
		$category_1 = $this->factory->term->create( [ 'name' => 'Category 1', 'taxonomy' => 'question_category' ] );
		$category_2 = $this->factory->term->create( [ 'name' => 'Category 2', 'taxonomy' => 'question_category' ] );
		$category_3 = $this->factory->term->create( [ 'name' => 'Category 3', 'taxonomy' => 'question_category' ] );
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'options' => 'terms',
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$this->assertIsArray( $result );
		$expected = [
			$category_1 => 'Category 1',
			$category_2 => 'Category 2',
			$category_3 => 'Category 3',
		];
		$this->assertEquals( $expected, $result );

		// Test 4.
		$tag_1 = $this->factory->term->create( [ 'name' => 'Tag 1', 'taxonomy' => 'question_tag' ] );
		$tag_2 = $this->factory->term->create( [ 'name' => 'Tag 2', 'taxonomy' => 'question_tag' ] );
		$tag_3 = $this->factory->term->create( [ 'name' => 'Tag 3', 'taxonomy' => 'question_tag' ] );
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'options'    => 'terms',
			'terms_args' => [
				'taxonomy'   => 'question_tag',
				'hide_empty' => false,
				'fields'     => 'id=>name',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$this->assertIsArray( $result );
		$expected = [
			$tag_1 => 'Tag 1',
			$tag_2 => 'Tag 2',
			$tag_3 => 'Tag 3',
		];
		$this->assertEquals( $expected, $result );

		// Test 5.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'options' => 'posts',
			'posts_args' => [
				'post_type' => 'page',
				'showposts' => -1,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );

		// Test 6.
		$page1 = $this->factory->post->create( [ 'post_type' => 'page', 'post_title' => 'Page 1' ] );
		$page2 = $this->factory->post->create( [ 'post_type' => 'page', 'post_title' => 'Page 2' ] );
		$page3 = $this->factory->post->create( [ 'post_type' => 'page', 'post_title' => 'Page 3' ] );
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'options' => 'posts',
			'posts_args' => [
				'post_type' => 'page',
				'showposts' => -1,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$this->assertIsArray( $result );
		$expected = [
			$page1 => 'Page 1',
			$page2 => 'Page 2',
			$page3 => 'Page 3',
		];
		$this->assertEquals( $expected, $result );

		// Test 7.
		$field = new \AnsPress\Form\Field\Select( 'Sample Form', 'sample-form', [
			'label'   => 'Sample Label',
			'desc'    => 'Sample Description',
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$this->assertIsArray( $result );
		$expected = [
			'option1' => 'Option 1',
			'option2' => 'Option 2',
		];
		$this->assertEquals( $expected, $result );
	}
}
