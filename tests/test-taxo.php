<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTaxo extends TestCase {

	use Testcases\Common;

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
	 * @covers ::is_question_category
	 */
	public function testISQuestionCategory() {
		$this->assertFalse( is_question_category() );
		$cid = $this->factory->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$this->assertTrue( is_question_category() );

		// Test without passing the name.
		$cid = $this->factory->term->create(
			array(
				'taxonomy' => 'question_category',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$this->assertTrue( is_question_category() );

		// Non-existance category.
		$this->go_to( '/?ap_page=category&question_category=' . 'question' );
		$this->assertFalse( is_question_category() );
	}

	/**
	 * @covers ::is_question_tag
	 */
	public function testISQuestionTag() {
		$this->assertFalse( is_question_tag() );
		$cid = $this->factory->term->create(
			array(
				'name'     => 'Question tag',
				'taxonomy' => 'question_tag',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_tag' );
		$this->go_to( '/?ap_page=tag&question_tag=' . $term->slug );
		$this->assertTrue( is_question_tag() );

		// Test without passing the name.
		$cid = $this->factory->term->create(
			array(
				'taxonomy' => 'question_tag',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_tag' );
		$this->go_to( '/?ap_page=tag&question_tag=' . $term->slug );
		$this->assertTrue( is_question_tag() );

		// Non-existance tag.
		$this->go_to( '/?ap_page=tag&question_tag=' . 'question' );
		$this->assertFalse( is_question_tag() );
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

	public function tagSlug() {
		return 'imtag';
	}

	/**
	 * @covers ::ap_get_tag_slug
	 */
	public function testAPGetTagSlug() {
		$this->assertEquals( 'tag', ap_get_tag_slug() );
		ap_opt( 'tag_page_slug', '#' );
		$this->assertEquals( '#', ap_get_tag_slug() );
		ap_opt( 'tag_page_slug', '' );
		$this->assertEquals( 'tag', ap_get_tag_slug() );

		// Test for filter within same function.
		add_filter( 'ap_tag_slug', [ $this, 'tagSlug' ] );
		$this->assertEquals( 'imtag', ap_get_tag_slug() );
		remove_filter( 'ap_tag_slug', [ $this, 'tagSlug' ] );
		$this->assertEquals( 'tag', ap_get_tag_slug() );

		// Test for filter within the main function.
		add_filter( 'ap_page_slug_tag', [ $this, 'tagSlug' ] );
		$this->assertEquals( 'imtag', ap_get_tag_slug() );
		remove_filter( 'ap_page_slug_tag', [ $this, 'tagSlug' ] );
		$this->assertEquals( 'tag', ap_get_tag_slug() );
	}

	public function tagsSlug() {
		return 'imtags';
	}

	/**
	 * @covers ::ap_get_tags_slug
	 */
	public function testAPGetTagsSlug() {
		$this->assertEquals( 'tags', ap_get_tags_slug() );
		ap_opt( 'tags_page_slug', '#' );
		$this->assertEquals( '#', ap_get_tags_slug() );
		ap_opt( 'tags_page_slug', '' );
		$this->assertEquals( 'tags', ap_get_tags_slug() );

		// Test for filter within same function.
		add_filter( 'ap_tags_slug', [ $this, 'tagsSlug' ] );
		$this->assertEquals( 'imtags', ap_get_tags_slug() );
		remove_filter( 'ap_tags_slug', [ $this, 'tagsSlug' ] );
		$this->assertEquals( 'tags', ap_get_tags_slug() );

		// Test for filter within the main function.
		add_filter( 'ap_page_slug_tags', [ $this, 'tagsSlug' ] );
		$this->assertEquals( 'imtags', ap_get_tags_slug() );
		remove_filter( 'ap_page_slug_tags', [ $this, 'tagsSlug' ] );
		$this->assertEquals( 'tags', ap_get_tags_slug() );
	}
}
