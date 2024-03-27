<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormFieldTags extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Tags' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'sanitize_cb_args' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'get_options' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'field_markup' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Tags', 'unsafe_value' ) );
	}

	/**
	 * @covers \AnsPress\Form\Field\Tags::prepare
	 */
	public function testPrepare() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$valiate_cb = $reflection->getProperty( 'validate_cb' );
		$valiate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $valiate_cb->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'tags_field' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_array', 'array_max', 'array_min' ], $valiate_cb->getValue( $field ) );
		$expected = [
			'label'      => 'AnsPress Tags Field',
			'array_max'  => 3,
			'array_min'  => 2,
			'terms_args' => [ 'taxonomy' => 'question_tag', 'hide_empty' => false, 'fields' => 'id=>name' ],
			'options'    => 'terms',
			'js_options' => [
				'maxItems' => 3,
				'form'     => 'Sample Form',
				'id'       => 'SampleForm-sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
		];
		$this->assertEquals( $expected, $field->args );

		// Test 2.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'label'     => 'Test Label',
			'array_max' => 5,
			'array_min' => 2,
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$valiate_cb = $reflection->getProperty( 'validate_cb' );
		$valiate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $valiate_cb->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'tags_field' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_array', 'array_max', 'array_min' ], $valiate_cb->getValue( $field ) );
		$expected = [
			'label'      => 'Test Label',
			'array_max'  => 5,
			'array_min'  => 2,
			'terms_args' => [ 'taxonomy' => 'question_tag', 'hide_empty' => false, 'fields' => 'id=>name' ],
			'options'    => 'terms',
			'js_options' => [
				'maxItems' => 5,
				'form'     => 'Sample Form',
				'id'       => 'SampleForm-sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
		];
		$this->assertEquals( $expected, $field->args );

		// Test 3.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'array_max'  => 10,
			'array_min'  => 3,
			'options'    => 'posts',
			'js_options' => [],
			'sanitize'   => 'custom_sanitize_cb',
			'validate'   => 'custom_validate_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$valiate_cb = $reflection->getProperty( 'validate_cb' );
		$valiate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $valiate_cb->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'tags_field', 'custom_sanitize_cb' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_array', 'array_max', 'array_min', 'custom_validate_cb' ], $valiate_cb->getValue( $field ) );
		$expected = [
			'label'      => 'AnsPress Tags Field',
			'array_max'  => 10,
			'array_min'  => 3,
			'terms_args' => [ 'taxonomy' => 'question_tag', 'hide_empty' => false, 'fields' => 'id=>name' ],
			'options'    => 'posts',
			'js_options' => [
				'maxItems' => 10,
				'form'     => 'Sample Form',
				'id'       => 'SampleForm-sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
			'sanitize'   => 'custom_sanitize_cb',
			'validate'   => 'custom_validate_cb',
		];
		$this->assertEquals( $expected, $field->args );

		// Test 4.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'label'      => 'Test Label',
			'array_max'  => 10,
			'array_min'  => 3,
			'options'    => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'js_options' => [
				'maxItems' => 10,
				'form'     => 'Test Form',
				'id'       => 'test-form',
				'field'    => 'test-form',
				'nonce'    => wp_create_nonce( 'tags_Test Formtest-form' ),
				'create'   => false,
				'labelAdd' => 'New Tag',
			],
			'sanitize'   => 'custom_sanitize_cb',
			'validate'   => 'custom_validate_cb',
		] );
		$reflection = new \ReflectionClass( $field );
		$sanitize_cb = $reflection->getProperty( 'sanitize_cb' );
		$sanitize_cb->setAccessible( true );
		$valiate_cb = $reflection->getProperty( 'validate_cb' );
		$valiate_cb->setAccessible( true );
		// No need to invoke prepare() method as it is called in constructor.
		$this->assertIsArray( $sanitize_cb->getValue( $field ) );
		$this->assertIsArray( $valiate_cb->getValue( $field ) );
		$this->assertEquals( [ 'array_remove_empty', 'tags_field', 'custom_sanitize_cb' ], $sanitize_cb->getValue( $field ) );
		$this->assertEquals( [ 'is_array', 'array_max', 'array_min', 'custom_validate_cb' ], $valiate_cb->getValue( $field ) );
		$expected = [
			'label'      => 'Test Label',
			'array_max'  => 10,
			'array_min'  => 3,
			'terms_args' => [ 'taxonomy' => 'question_tag', 'hide_empty' => false, 'fields' => 'id=>name' ],
			'options'    => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
			'js_options' => [
				'maxItems' => 10,
				'form'     => 'Test Form',
				'id'       => 'test-form',
				'field'    => 'test-form',
				'nonce'    => wp_create_nonce( 'tags_Test Formtest-form' ),
				'create'   => false,
				'labelAdd' => 'New Tag',
			],
			'sanitize'   => 'custom_sanitize_cb',
			'validate'   => 'custom_validate_cb',
		];
		$this->assertEquals( $expected, $field->args );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::sanitize_cb_args
	 */
	public function testSanitizeCbArgs() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test', $field->args ], $result );

		// Test 2.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'label'     => 'Test Label',
			'array_max' => 5,
			'array_min' => 2,
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test_value', $field->args ], $result );

		// Test 3.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'array_max' => 5,
			'array_min' => 2,
			'options'   => 'terms',
			'js_options' => [],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, 'test_value' );
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test_value', $field->args ], $result );

		// Test 4.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'array_max' => 5,
			'array_min' => 2,
			'options'   => 'terms',
			'js_options' => [
				'maxItems' => 5,
				'form'     => 'Sample Form',
				'id'       => 'sample-form',
				'field'    => 'sample-form',
				'nonce'    => wp_create_nonce( 'tags_Sample Formsample-form' ),
				'create'   => false,
				'labelAdd' => 'Add',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'sanitize_cb_args' );
		$method->setAccessible( true );
		$result = $method->invoke( $field, [ 'new_value' ] );
		$this->assertIsArray( $result );
		$this->assertEquals( [ [ 'new_value' ], $field->args ], $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::unsafe_value
	 */
	public function testUnsafeValue() {
		// Test 1.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [] );
		$_REQUEST = [
			'Sample Form' => [
				'sample-form' => 'test1,test2',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test1', 'test2' ], $result );

		// Test 2.
		$field = new \AnsPress\Form\Field\Tags( 'Test Form', 'test-form', [] );
		$_REQUEST = [
			'Test Form' => [
				'test-form' => 'test1',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'test1' ], $result );

		// Test 3.
		$field = new \AnsPress\Form\Field\Tags( 'Test Form', 'test-form', [] );
		$_REQUEST = [
			'Test Form' => [
				'test-form' => '',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ '' ], $result );

		// Test 4.
		$field = new \AnsPress\Form\Field\Tags( 'Test Form', 'test-form', [] );
		$_REQUEST = [
			'Test Form' => [
				'test-form' => 'this is test, \\test value\\',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ 'this is test', ' test value' ], $result );

		// Test 5.
		$field = new \AnsPress\Form\Field\Tags( 'Test Form', 'test-form', [] );
		$_REQUEST = [
			'Test Form' => [
				'test-form' => '0,test value,valid tag id,\\valid tag name\\',
			],
		];
		$result = $field->unsafe_value();
		$this->assertIsArray( $result );
		$this->assertEquals( [ '0', 'test value', 'valid tag id', 'valid tag name' ], $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::get_options
	 */
	public function testGetOptionsShouldReturnEmptyArrayIfOptionsIsSetToAnArray() {
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
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
		$this->assertEmpty( $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::get_options
	 */
	public function testGetOptionsShouldReturnArrayListOfPostsIfPostsIsSetAsOption() {
		// Create some posts.
		$post_id_1 = $this->factory()->post->create( [ 'post_title' => 'Post Title 1' ] );
		$post_id_2 = $this->factory()->post->create( [ 'post_title' => 'Post Title 2' ] );
		$post_id_3 = $this->factory()->post->create( [ 'post_title' => 'Post Title 3' ] );
		$post_id_4 = $this->factory()->post->create( [ 'post_title' => 'Post Title 4' ] );
		$post_id_5 = $this->factory()->post->create( [ 'post_title' => 'Post Title 5' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options'    => 'posts',
			'posts_args' => [
				'post_type' => 'post',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$expected = [
			$post_id_1 => 'Post Title 1',
			$post_id_2 => 'Post Title 2',
			$post_id_3 => 'Post Title 3',
			$post_id_4 => 'Post Title 4',
			$post_id_5 => 'Post Title 5',
		];
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::get_options
	 */
	public function testGetOptionsShouldReturnArrayListOfPostsIfPostsIsSetAsOptionWithCustomPostsArgs() {
		// Create some posts.
		$post_id_1 = $this->factory()->post->create( [ 'post_title' => 'Post Title 1', 'post_type' => 'page' ] );
		$post_id_2 = $this->factory()->post->create( [ 'post_title' => 'Post Title 2', 'post_type' => 'page' ] );
		$post_id_3 = $this->factory()->post->create( [ 'post_title' => 'Post Title 3', 'post_type' => 'page' ] );
		$post_id_4 = $this->factory()->post->create( [ 'post_title' => 'Post Title 4', 'post_type' => 'page' ] );
		$post_id_5 = $this->factory()->post->create( [ 'post_title' => 'Post Title 5', 'post_type' => 'page' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options'    => 'posts',
			'posts_args' => [
				'post_type'      => 'page',
				'posts_per_page' => 3,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$expected = [
			$post_id_5 => 'Post Title 5',
			$post_id_4 => 'Post Title 4',
			$post_id_3 => 'Post Title 3',
		];
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::get_options
	 */
	public function testGetOptionsShouldReturnEmptyArrayIfValuesNotSet() {
		// Create some terms.
		$term_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 1' ] );
		$term_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 2' ] );
		$term_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 3' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options' => 'terms',
			'value'   => '',
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$this->assertEmpty( $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::get_options
	 */
	public function testGetOptionsShouldReturnArrayListOfTermsIfValueIsSet() {
		// Create some terms.
		$term_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 1', 'description' => 'Question Tag Description 1' ] );
		$term_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 2', 'description' => 'Question Tag Description 2' ] );
		$term_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 3', 'description' => 'Question Tag Description 3' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options' => 'terms',
			'value'   => [ $term_id_1, $term_id_2 ],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$expected = [
			[
				'term_id'     => $term_id_1,
				'name'        => 'Question Tag 1',
				'description' => 'Question Tag Description 1',
				'count'       => 0,
			],
			[
				'term_id'     => $term_id_2,
				'name'        => 'Question Tag 2',
				'description' => 'Question Tag Description 2',
				'count'       => 0,
			],
		];
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::get_options
	 */
	public function testGetOptionsShouldReturnArrayListOfTermsIfValueIsSetForOtherCustomTaxonomyAsWell() {
		// Create some terms.
		$term_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Question Category 1', 'description' => 'Question Tag Description 1' ] );
		$term_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Question Category 2', 'description' => 'Question Tag Description 2' ] );
		$term_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Question Category 3', 'description' => 'Question Tag Description 3' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options' => 'terms',
			'value'   => [ $term_id_1 ],
			'terms_args' => [
				'taxonomy' => 'question_category',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$result = $method->invoke( $field );
		$expected = [
			[
				'term_id'     => $term_id_1,
				'name'        => 'Question Category 1',
				'description' => 'Question Tag Description 1',
				'count'       => 0,
			],
		];
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::field_markup
	 */
	public function testFieldMarkupForOptionsAsAnArray() {
		// Set up the action hook callback.
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Tags', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options' => [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<input type="text" id="SampleForm-sample-form" data-type="tags" data-options="' . esc_js( wp_json_encode( $field->get( 'js_options' ) ) ) . '" class="ap-tags-input" autocomplete="off" aptagfield name="Sample Form[sample-form]" value="" />', $property->getValue( $field ) );
		$this->assertStringContainsString( '<script id="SampleForm-sample-form-options" type="application/json">[]</script>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::field_markup
	 */
	public function testFieldMarkupForOptionsAsTerms() {
		// Set up the action hook callback.
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Tags', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Create some terms.
		$term_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 1', 'description' => 'Question Tag Description 1' ] );
		$term_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 2', 'description' => 'Question Tag Description 2' ] );
		$term_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_tag', 'name' => 'Question Tag 3', 'description' => 'Question Tag Description 3' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options' => 'terms',
			'value'   => [ $term_id_1, $term_id_2 ],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<input type="text" id="SampleForm-sample-form" data-type="tags" data-options="' . esc_js( wp_json_encode( $field->get( 'js_options' ) ) ) . '" class="ap-tags-input" autocomplete="off" aptagfield name="Sample Form[sample-form]" value="' . implode( ',', [ $term_id_1, $term_id_2 ] ) . '" />', $property->getValue( $field ) );
		$this->assertStringContainsString( '<script id="SampleForm-sample-form-options" type="application/json">' . wp_json_encode( $method->invoke( $field ) ) . '</script>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::field_markup
	 */
	public function testFieldMarkupForOptionsAsTermsForQuestionCategoryTaxonomy() {
		// Set up the action hook callback.
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Tags', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Create some terms.
		$term_id_1 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Question Category 1', 'description' => 'Question Tag Description 1' ] );
		$term_id_2 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Question Category 2', 'description' => 'Question Tag Description 2' ] );
		$term_id_3 = $this->factory()->term->create( [ 'taxonomy' => 'question_category', 'name' => 'Question Category 3', 'description' => 'Question Tag Description 3' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options'    => 'terms',
			'value'      => [ $term_id_3 ],
			'terms_args' => [
				'taxonomy' => 'question_category',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<input type="text" id="SampleForm-sample-form" data-type="tags" data-options="' . esc_js( wp_json_encode( $field->get( 'js_options' ) ) ) . '" class="ap-tags-input" autocomplete="off" aptagfield name="Sample Form[sample-form]" value="' . implode( ',', [ $term_id_3 ] ) . '" />', $property->getValue( $field ) );
		$this->assertStringContainsString( '<script id="SampleForm-sample-form-options" type="application/json">' . wp_json_encode( $method->invoke( $field ) ) . '</script>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::field_markup
	 */
	public function testFieldMarkupForOptionsAsPosts() {
		// Set up the action hook callback.
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Tags', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Create some posts.
		$post_id_1 = $this->factory()->post->create( [ 'post_title' => 'Post Title 1' ] );
		$post_id_2 = $this->factory()->post->create( [ 'post_title' => 'Post Title 2' ] );
		$post_id_3 = $this->factory()->post->create( [ 'post_title' => 'Post Title 3' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options'    => 'posts',
			'value'      => [ $post_id_1, $post_id_2 ],
			'posts_args' => [
				'post_type' => 'post',
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<input type="text" id="SampleForm-sample-form" data-type="tags" data-options="' . esc_js( wp_json_encode( $field->get( 'js_options' ) ) ) . '" class="ap-tags-input" autocomplete="off" aptagfield name="Sample Form[sample-form]" value="' . implode( ',', [ $post_id_1, $post_id_2 ] ) . '" />', $property->getValue( $field ) );
		$this->assertStringContainsString( '<script id="SampleForm-sample-form-options" type="application/json">' . wp_json_encode( $method->invoke( $field ) ) . '</script>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}

	/**
	 * @covers AnsPress\Form\Field\Tags::field_markup
	 */
	public function testFieldMarkupForOptionsAsPostsForPagePostType() {
		// Set up the action hook callback.
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field\Tags', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$callback_triggered = true;
		} );

		// Create some posts.
		$post_id_1 = $this->factory()->post->create( [ 'post_title' => 'Post Title 1', 'post_type' => 'page' ] );
		$post_id_2 = $this->factory()->post->create( [ 'post_title' => 'Post Title 2', 'post_type' => 'page' ] );
		$post_id_3 = $this->factory()->post->create( [ 'post_title' => 'Post Title 3', 'post_type' => 'page' ] );

		// Test.
		$field = new \AnsPress\Form\Field\Tags( 'Sample Form', 'sample-form', [
			'options'    => 'posts',
			'value'      => [ $post_id_3 ],
			'posts_args' => [
				'post_type'      => 'page',
				'posts_per_page' => 3,
			],
		] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$method = $reflection->getMethod( 'get_options' );
		$method->setAccessible( true );
		$this->assertFalse( $callback_triggered );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertStringContainsString( '<input type="text" id="SampleForm-sample-form" data-type="tags" data-options="' . esc_js( wp_json_encode( $field->get( 'js_options' ) ) ) . '" class="ap-tags-input" autocomplete="off" aptagfield name="Sample Form[sample-form]" value="' . implode( ',', [ $post_id_3 ] ) . '" />', $property->getValue( $field ) );
		$this->assertStringContainsString( '<script id="SampleForm-sample-form-options" type="application/json">' . wp_json_encode( $method->invoke( $field ) ) . '</script>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_field_markup' ) > 0 );
	}
}
