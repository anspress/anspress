<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTaxo extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	public function set_up() {
		parent::set_up();
		register_taxonomy( 'question_category', array( 'question' ) );
		register_taxonomy( 'question_tag', array( 'question' ) );
	}

	public function tear_down() {
		unregister_taxonomy( 'question_category' );
		unregister_taxonomy( 'question_tag' );
		parent::tear_down();
	}

	/**
	 * @covers ::ap_question_have_category
	 */
	public function testAPQuestionHaveCategory() {
		$cid = $this->factory->term->create(
			array(
				'taxonomy' => 'question_category',
			)
		);
		$qid = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		wp_set_object_terms( $qid, array( $cid ), 'question_category' );
		$this->assertTrue( ap_question_have_category( $qid ) );
		$qid = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$this->assertFalse( ap_question_have_category( $qid ) );
	}

	/**
	 * @covers ::ap_question_have_tags
	 */
	public function testAPQuestionHaveTags() {
		$tid = $this->factory->term->create(
			array(
				'taxonomy' => 'question_tag',
			)
		);
		$qid = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		wp_set_object_terms( $qid, array( $tid ), 'question_tag' );
		$this->assertTrue( ap_question_have_tags( $qid ) );
		$qid = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$this->assertFalse( ap_question_have_tags( $qid ) );
	}

	/**
	 * @covers ::is_question_categories
	 */
	public function testISQuestionCategories() {
		$this->assertFalse( is_question_categories() );
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Categories page',
				'post_content' => 'Categories page',
				'post_type'    => 'page',
			)
		);
		ap_opt( 'categories_page', $id );
		$this->go_to( '/?post_type=page&p=' . $id );
		$this->assertTrue( is_question_categories() );
	}

	/**
	 * @covers ::is_question_tags
	 */
	public function testISQuestionTags() {
		$this->assertFalse( is_question_tags() );
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Tags page',
				'post_content' => 'Tags page',
				'post_type'    => 'page',
			)
		);
		ap_opt( 'tags_page', $id );
		$this->go_to( '/?post_type=page&p=' . $id );
		$this->assertTrue( is_question_tags() );
	}

	/**
	 * @covers ::ap_get_categories_slug
	 */
	public function testAPGetCategoriesSlug() {
		$this->assertEquals( 'categories', ap_get_categories_slug() );
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Categories page',
				'post_content' => 'Categories content',
				'post_name'    => 'categories111',
			)
		);
		ap_opt( 'categories_page', $id );
		ap_opt( 'categories_page_id', 'categories111' );
		$this->assertEquals( 'categories111', ap_get_categories_slug() );
	}

	public function categorySlug() {
		return 'imcategory';
	}

	/**
	 * @covers ::ap_get_category_slug
	 */
	public function testAPGetCategorySlug() {
		$this->assertEquals( 'category', ap_get_category_slug() );
		ap_opt( 'category_page_slug', 'cat' );
		$this->assertEquals( 'cat', ap_get_category_slug() );
		ap_opt( 'category_page_slug', '' );
		$this->assertEquals( 'category', ap_get_category_slug() );

		// Test for filter within same function.
		add_filter( 'ap_category_slug', [ $this, 'categorySlug' ] );
		$this->assertEquals( 'imcategory', ap_get_category_slug() );
		remove_filter( 'ap_category_slug', [ $this, 'categorySlug' ] );
		$this->assertEquals( 'category', ap_get_category_slug() );

		// Test for filter within the main function.
		add_filter( 'ap_page_slug_category', [ $this, 'categorySlug' ] );
		$this->assertEquals( 'imcategory', ap_get_category_slug() );
		remove_filter( 'ap_page_slug_category', [ $this, 'categorySlug' ] );
		$this->assertEquals( 'category', ap_get_category_slug() );
	}

	/**
	 * @covers ::ap_category_have_image
	 */
	public function testAPCategoryHaveImage() {
		// Test for image.
		$cid = $this->factory->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$post = $this->factory->post->create_and_get();
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $post->ID );
		$this->assertFalse( ap_category_have_image( $cid ) );
		update_term_meta(
			$cid,
			'ap_category',
			[
				'image' => [
					'id' => $attachment_id,
				]
			]
		);
		$this->assertTrue( ap_category_have_image( $cid ) );
		update_term_meta(
			$cid,
			'ap_category',
			[
				'image' => []
			]
		);
		$this->assertFalse( ap_category_have_image( $cid ) );

		// Test for image.
		$cid = $this->factory->term->create(
			array(
				'name'     => 'New Question category',
				'taxonomy' => 'question_category',
			)
		);
		$post = $this->factory->post->create_and_get();
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $post->ID );
		$this->assertFalse( ap_category_have_image( $cid ) );
		update_term_meta(
			$cid,
			'ap_category',
			[
				'image' => [
					'id' => $attachment_id,
				]
			]
		);
		$this->assertTrue( ap_category_have_image( $cid ) );
		update_term_meta(
			$cid,
			'ap_category',
			[
				'image' => []
			]
		);
		$this->assertFalse( ap_category_have_image( $cid ) );
	}
}
