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
		$cid = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_category',
			)
		);
		$qid = $this->factory()->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		wp_set_object_terms( $qid, array( $cid ), 'question_category' );
		$this->assertTrue( ap_question_have_category( $qid ) );
		$qid = $this->factory()->post->create(
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
		$tid = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_tag',
			)
		);
		$qid = $this->factory()->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		wp_set_object_terms( $qid, array( $tid ), 'question_tag' );
		$this->assertTrue( ap_question_have_tags( $qid ) );
		$qid = $this->factory()->post->create(
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
		$id = $this->factory()->post->create(
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
		$id = $this->factory()->post->create(
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
		$cid = $this->factory()->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_category' );
		$this->go_to( '/?ap_page=category&question_category=' . $term->slug );
		$this->assertTrue( is_question_category() );

		// Test without passing the name.
		$cid = $this->factory()->term->create(
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
		$cid = $this->factory()->term->create(
			array(
				'name'     => 'Question tag',
				'taxonomy' => 'question_tag',
			)
		);
		$term = get_term_by( 'id', $cid, 'question_tag' );
		$this->go_to( '/?ap_page=tag&question_tag=' . $term->slug );
		$this->assertTrue( is_question_tag() );

		// Test without passing the name.
		$cid = $this->factory()->term->create(
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
		$id = $this->factory()->post->create(
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
		$cid = $this->factory()->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $post->ID );
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
		$cid = $this->factory()->term->create(
			array(
				'name'     => 'New Question category',
				'taxonomy' => 'question_category',
			)
		);
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $post->ID );
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

	/**
	 * @covers ::ap_get_category_icon
	 * @covers ::ap_category_icon
	 */
	public function testCategoryIcon() {
		$cid = $this->factory()->term->create(
			[
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			]
		);
		$term_meta = [
			'icon' => 'apicon-star',
		];
		$term = get_term_by( 'id', $cid, 'question_category' );

		// Test begins.
		// Test for empty icon.
		// For ap_get_category_icon.
		$result = ap_get_category_icon( $term->term_id );
		$this->assertNull( $result );
		$this->assertEmpty( $result );
		$this->assertEquals( '', $result );

		// For ap_category_icon.
		ob_start();
		ap_category_icon( $term->term_id );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
		$this->assertEquals( '', $result );

		// Test for icon only.
		update_term_meta( $cid, 'ap_category', $term_meta );

		// For ap_get_category_icon.
		$result = ap_get_category_icon( $term->term_id );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'apicon-star', $result );
		$this->assertEquals( '<span class="ap-category-icon apicon-star"style=""></span>', $result );

		// For ap_category_icon.
		ob_start();
		ap_category_icon( $term->term_id );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'apicon-star', $result );
		$this->assertEquals( '<span class="ap-category-icon apicon-star"style=""></span>', $result );

		// Test for both icon and color option.
		$term_meta['color'] = '#000000';
		update_term_meta( $cid, 'ap_category', $term_meta );

		// For ap_get_category_icon.
		$result = ap_get_category_icon( $term->term_id );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'apicon-star', $result );
		$this->assertStringContainsString( 'background:#000000', $result );
		$this->assertEquals( '<span class="ap-category-icon apicon-star"style=" background:#000000;"></span>', $result );

		// For ap_category_icon.
		ob_start();
		ap_category_icon( $term->term_id );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'apicon-star', $result );
		$this->assertStringContainsString( 'background:#000000', $result );
		$this->assertEquals( '<span class="ap-category-icon apicon-star"style=" background:#000000;"></span>', $result );

		// Test passing custom attributes.
		// For ap_get_category_icon.
		$result = ap_get_category_icon( $term->term_id, ' id="custom-id"' );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'apicon-star', $result );
		$this->assertStringContainsString( 'background:#000000', $result );
		$this->assertEquals( '<span class="ap-category-icon apicon-star"style=" background:#000000;" id="custom-id"></span>', $result );

		// For ap_category_icon.
		ob_start();
		ap_category_icon( $term->term_id, ' id="custom-id"' );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'apicon-star', $result );
		$this->assertStringContainsString( 'background:#000000', $result );
		$this->assertEquals( '<span class="ap-category-icon apicon-star"style=" background:#000000;" id="custom-id"></span>', $result );

		// Test passing custom attributes but without any icon.
		update_term_meta( $cid, 'ap_category', [] );

		// For ap_get_category_icon.
		$result = ap_get_category_icon( $term->term_id, ' id="custom-id"' );
		$this->assertNull( $result );
		$this->assertEmpty( $result );
		$this->assertEquals( '', $result );

		// For ap_category_icon.
		ob_start();
		ap_category_icon( $term->term_id, ' id="custom-id"' );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
		$this->assertEquals( '', $result );
	}

	/**
	 * @covers ::ap_get_category_image
	 * @covers ::ap_category_image
	 */
	public function testCategoryImage() {
		$cid = $this->factory()->term->create(
			[
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			]
		);
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $post->ID );
		$term_meta = [
			'image' => [
				'id'  => $attachment_id,
				'url' => wp_get_attachment_url( $attachment_id ),
			]
		];
		$term = get_term_by( 'id', $cid, 'question_category' );

		// Test begins.
		// Test for empty image.
		// For ap_get_category_image.
		$result = ap_get_category_image( $term->term_id );
		$this->assertEquals( '<div class="ap-category-defimage" style="background:#333;height:32px;"></div>', $result );

		// For ap_category_image.
		ob_start();
		ap_category_image( $term->term_id );
		$result = ob_get_clean();
		$this->assertEquals( '<div class="ap-category-defimage" style="background:#333;height:32px;"></div>', $result );

		// Test for image only.
		update_term_meta( $cid, 'ap_category', $term_meta );

		// For ap_get_category_image.
		$result = ap_get_category_image( $term->term_id );
		$this->assertStringContainsString( wp_get_attachment_url( $attachment_id ), $result );
		$this->assertStringContainsString( 'class="attachment-900x32 size-900x32"', $result );

		// For ap_category_image.
		ob_start();
		ap_category_image( $term->term_id );
		$result = ob_get_clean();
		$this->assertStringContainsString( wp_get_attachment_url( $attachment_id ), $result );
		$this->assertStringContainsString( 'class="attachment-900x32 size-900x32"', $result );

		// Test for both image and color option.
		$term_meta['color'] = '#000000';
		update_term_meta( $cid, 'ap_category', $term_meta );

		// For ap_get_category_image.
		$result = ap_get_category_image( $term->term_id );
		$this->assertStringContainsString( wp_get_attachment_url( $attachment_id ), $result );
		$this->assertStringNotContainsString( 'background:#000000', $result );

		// For ap_category_image.
		ob_start();
		ap_category_image( $term->term_id );
		$result = ob_get_clean();
		$this->assertStringContainsString( wp_get_attachment_url( $attachment_id ), $result );
		$this->assertStringNotContainsString( 'background:#000000', $result );

		// Test for invalid attachment id.
		$term_meta['image'] = [
			'id'  => -1,
			'url' => '',
		];
		update_term_meta( $cid, 'ap_category', $term_meta );

		// For ap_get_category_image.
		$result = ap_get_category_image( $term->term_id );
		$this->assertEmpty( $result );
		$this->assertEquals( '', $result );

		// For ap_category_image.
		ob_start();
		ap_category_image( $term->term_id );
		$result = ob_get_clean();
		$this->assertEmpty( $result );
		$this->assertEquals( '', $result );

		// Test passing the height attribute and valid attachment id.
		$term_meta['image'] = [
			'id'  => $attachment_id,
			'url' => wp_get_attachment_url( $attachment_id ),
		];
		update_term_meta( $cid, 'ap_category', $term_meta );

		// For ap_get_category_image.
		$result = ap_get_category_image( $term->term_id, 100 );
		$this->assertStringContainsString( wp_get_attachment_url( $attachment_id ), $result );
		$this->assertStringContainsString( 'class="attachment-900x100 size-900x100"', $result );

		// For ap_category_image.
		ob_start();
		ap_category_image( $term->term_id, 100 );
		$result = ob_get_clean();
		$this->assertStringContainsString( wp_get_attachment_url( $attachment_id ), $result );
		$this->assertStringContainsString( 'class="attachment-900x100 size-900x100"', $result );

		// Test passing the height attribute and attachment id as 0.
		$term_meta['image'] = [
			'id'  => 0,
			'url' => '',
		];
		update_term_meta( $cid, 'ap_category', $term_meta );

		// For ap_get_category_image.
		$result = ap_get_category_image( $term->term_id, 100 );
		$this->assertEquals( '<div class="ap-category-defimage" style=" background:#000000;height:100px;"></div>', $result );

		// For ap_category_image.
		ob_start();
		ap_category_image( $term->term_id, 100 );
		$result = ob_get_clean();
		$this->assertEquals( '<div class="ap-category-defimage" style=" background:#000000;height:100px;"></div>', $result );
	}

	/**
	 * @covers ::ap_question_categories_html
	 */
	public function testAPQuestionCategoriesHTML() {
		$question_id = $this->factory()->post->create(
			[
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			]
		);

		// Test before assigning categories.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		// Test for return value.
		$result = ap_question_categories_html( [] );
		$this->assertNull( $result );

		// Test for echoed value.
		ob_start();
		ap_question_categories_html( [ 'echo' => true ] );
		$result = ob_get_clean();
		$this->assertEmpty( $result );

		// Test after assigning categories.
		$category_id_1 = $this->factory()->term->create(
			[
				'name'     => 'Question category 1',
				'taxonomy' => 'question_category',
			]
		);
		$category_id_2 = $this->factory()->term->create(
			[
				'name'     => 'Question category 2',
				'taxonomy' => 'question_category',
			]
		);
		$category_id_3 = $this->factory()->term->create(
			[
				'name'     => 'Question category 3',
				'taxonomy' => 'question_category',
			]
		);
		wp_set_object_terms( $question_id, [ $category_id_1, $category_id_2, $category_id_3 ], 'question_category' );
		$cat_1 = get_term_by( 'id', $category_id_1, 'question_category' );
		$cat_2 = get_term_by( 'id', $category_id_2, 'question_category' );
		$cat_3 = get_term_by( 'id', $category_id_3, 'question_category' );

		// Test for return value.
		// Test 1.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_categories_html( [] );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'Categories', $result );
		$this->assertStringContainsString( '<span class="question-categories">', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_1->term_id . '" href="' . esc_url( get_term_link( $category_id_1 ) ) . '" title="' . $cat_1->description . '">Question category 1</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_2->term_id . '" href="' . esc_url( get_term_link( $category_id_2 ) ) . '" title="' . $cat_2->description . '">Question category 2</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_3->term_id . '" href="' . esc_url( get_term_link( $category_id_3 ) ) . '" title="' . $cat_3->description . '">Question category 3</a>', $result );

		// Test 2.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_categories_html( [ 'list' => true, 'label' => 'Available Categories', 'class' => 'categories-lists', 'tag' => 'strong' ] );
		$this->assertNotEmpty( $result );
		$this->assertStringNotContainsString( 'Available Categories', $result );
		$this->assertStringContainsString( '<ul class="categories-lists">', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $category_id_1 ) ) . '" data-catid="' . $cat_1->term_id . '" title="' . $cat_1->description . '">Question category 1</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $category_id_2 ) ) . '" data-catid="' . $cat_2->term_id . '" title="' . $cat_2->description . '">Question category 2</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $category_id_3 ) ) . '" data-catid="' . $cat_3->term_id . '" title="' . $cat_3->description . '">Question category 3</a></li>', $result );

		// Test 3.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_categories_html( [ 'list' => false, 'label' => 'Available Categories', 'class' => 'categories-lists', 'tag' => 'strong' ] );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'Available Categories', $result );
		$this->assertStringContainsString( '<strong class="categories-lists">', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_1->term_id . '" href="' . esc_url( get_term_link( $category_id_1 ) ) . '" title="' . $cat_1->description . '">Question category 1</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_2->term_id . '" href="' . esc_url( get_term_link( $category_id_2 ) ) . '" title="' . $cat_2->description . '">Question category 2</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_3->term_id . '" href="' . esc_url( get_term_link( $category_id_3 ) ) . '" title="' . $cat_3->description . '">Question category 3</a>', $result );

		// Test for echoed value.
		// Test 1.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_question_categories_html( [ 'echo' => true ] );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'Categories', $result );
		$this->assertStringContainsString( '<span class="question-categories">', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_1->term_id . '" href="' . esc_url( get_term_link( $category_id_1 ) ) . '" title="' . $cat_1->description . '">Question category 1</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_2->term_id . '" href="' . esc_url( get_term_link( $category_id_2 ) ) . '" title="' . $cat_2->description . '">Question category 2</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_3->term_id . '" href="' . esc_url( get_term_link( $category_id_3 ) ) . '" title="' . $cat_3->description . '">Question category 3</a>', $result );

		// Test 2.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_question_categories_html( [ 'list' => true, 'label' => 'Available Categories', 'class' => 'categories-lists', 'tag' => 'strong', 'echo' => true ] );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringNotContainsString( 'Available Categories', $result );
		$this->assertStringContainsString( '<ul class="categories-lists">', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $category_id_1 ) ) . '" data-catid="' . $cat_1->term_id . '" title="' . $cat_1->description . '">Question category 1</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $category_id_2 ) ) . '" data-catid="' . $cat_2->term_id . '" title="' . $cat_2->description . '">Question category 2</a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $category_id_3 ) ) . '" data-catid="' . $cat_3->term_id . '" title="' . $cat_3->description . '">Question category 3</a></li>', $result );

		// Test 3.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_question_categories_html( [ 'list' => false, 'label' => 'Available Categories', 'class' => 'categories-lists', 'tag' => 'strong', 'echo' => true ] );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'Available Categories', $result );
		$this->assertStringContainsString( '<strong class="categories-lists">', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_1->term_id . '" href="' . esc_url( get_term_link( $category_id_1 ) ) . '" title="' . $cat_1->description . '">Question category 1</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_2->term_id . '" href="' . esc_url( get_term_link( $category_id_2 ) ) . '" title="' . $cat_2->description . '">Question category 2</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_3->term_id . '" href="' . esc_url( get_term_link( $category_id_3 ) ) . '" title="' . $cat_3->description . '">Question category 3</a>', $result );
	}

	/**
	 * @covers ::ap_question_tags_html
	 */
	public function testAPQuestionTagsHTML() {
		$question_id = $this->factory()->post->create(
			[
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			]
		);

		// Test before assigning tags.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		// Test for return value.
		$result = ap_question_tags_html( [] );
		$this->assertNull( $result );

		// Test for echoed value.
		ob_start();
		ap_question_tags_html( [ 'echo' => true ] );
		$result = ob_get_clean();
		$this->assertEmpty( $result );

		// Test after assigning tags.
		$tag_id_1 = $this->factory()->term->create(
			[
				'name'     => 'Question tag 1',
				'taxonomy' => 'question_tag',
			]
		);
		$tag_id_2 = $this->factory()->term->create(
			[
				'name'     => 'Question tag 2',
				'taxonomy' => 'question_tag',
			]
		);
		$tag_id_3 = $this->factory()->term->create(
			[
				'name'     => 'Question tag 3',
				'taxonomy' => 'question_tag',
			]
		);
		wp_set_object_terms( $question_id, [ $tag_id_1, $tag_id_2, $tag_id_3 ], 'question_tag' );
		$tag_1 = get_term_by( 'id', $tag_id_1, 'question_tag' );
		$tag_2 = get_term_by( 'id', $tag_id_2, 'question_tag' );
		$tag_3 = get_term_by( 'id', $tag_id_3, 'question_tag' );

		// Test for return value.
		// Test 1.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_tags_html( [] );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'Tagged', $result );
		$this->assertStringContainsString( '<span class="question-tags" itemprop="keywords">', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_1 ) ) . '" title="' . $tag_1->description . '">Question tag 1</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_2 ) ) . '" title="' . $tag_2->description . '">Question tag 2</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_3 ) ) . '" title="' . $tag_3->description . '">Question tag 3</a>', $result );

		// Test 2.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_tags_html( [ 'list' => true, 'label' => 'Available Tags', 'class' => 'tags-lists', 'tag' => 'strong' ] );
		$this->assertNotEmpty( $result );
		$this->assertStringNotContainsString( 'Available Tags', $result );
		$this->assertStringContainsString( '<ul class="tags-lists" itemprop="keywords">', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $tag_id_1 ) ) . '" title="' . $tag_1->description . '">Question tag 1 &times; <i class="tax-count">' . $tag_1->count . '</i></a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $tag_id_2 ) ) . '" title="' . $tag_2->description . '">Question tag 2 &times; <i class="tax-count">' . $tag_2->count . '</i></a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $tag_id_3 ) ) . '" title="' . $tag_3->description . '">Question tag 3 &times; <i class="tax-count">' . $tag_3->count . '</i></a></li>', $result );

		// Test 3.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_tags_html( [ 'list' => false, 'label' => 'Available Tags', 'class' => 'tags-lists', 'tag' => 'strong' ] );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'Available Tags', $result );
		$this->assertStringContainsString( '<strong class="tags-lists" itemprop="keywords">', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_1 ) ) . '" title="' . $tag_1->description . '">Question tag 1</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_2 ) ) . '" title="' . $tag_2->description . '">Question tag 2</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_3 ) ) . '" title="' . $tag_3->description . '">Question tag 3</a>', $result );

		// Test for echoed value.
		// Test 1.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_question_tags_html( [ 'echo' => true ] );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'Tagged', $result );
		$this->assertStringContainsString( '<span class="question-tags" itemprop="keywords">', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_1 ) ) . '" title="' . $tag_1->description . '">Question tag 1</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_2 ) ) . '" title="' . $tag_2->description . '">Question tag 2</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_3 ) ) . '" title="' . $tag_3->description . '">Question tag 3</a>', $result );

		// Test 2.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_question_tags_html( [ 'list' => true, 'label' => 'Available Tags', 'class' => 'tags-lists', 'tag' => 'strong', 'echo' => true ] );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringNotContainsString( 'Available Tags', $result );
		$this->assertStringContainsString( '<ul class="tags-lists" itemprop="keywords">', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $tag_id_1 ) ) . '" title="' . $tag_1->description . '">Question tag 1 &times; <i class="tax-count">' . $tag_1->count . '</i></a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $tag_id_2 ) ) . '" title="' . $tag_2->description . '">Question tag 2 &times; <i class="tax-count">' . $tag_2->count . '</i></a></li>', $result );
		$this->assertStringContainsString( '<li><a href="' . esc_url( get_term_link( $tag_id_3 ) ) . '" title="' . $tag_3->description . '">Question tag 3 &times; <i class="tax-count">' . $tag_3->count . '</i></a></li>', $result );

		// Test 3.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_question_tags_html( [ 'list' => false, 'label' => 'Available Tags', 'class' => 'tags-lists', 'tag' => 'strong', 'echo' => true ] );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'Available Tags', $result );
		$this->assertStringContainsString( '<strong class="tags-lists" itemprop="keywords">', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_1 ) ) . '" title="' . $tag_1->description . '">Question tag 1</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_2 ) ) . '" title="' . $tag_2->description . '">Question tag 2</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_3 ) ) . '" title="' . $tag_3->description . '">Question tag 3</a>', $result );
	}

	/**
	 * @covers ::ap_question_categories_html
	 */
	public function testAPQuestionCategoriesHTMLByPassingQuestionCategoryIdInsteadOfArray() {
		$question_id = $this->factory()->post->create(
			[
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			]
		);
		$category_id_1 = $this->factory()->term->create(
			[
				'name'     => 'Question category 1',
				'taxonomy' => 'question_category',
			]
		);
		$category_id_2 = $this->factory()->term->create(
			[
				'name'     => 'Question category 2',
				'taxonomy' => 'question_category',
			]
		);
		wp_set_object_terms( $question_id, [ $category_id_1, $category_id_2 ], 'question_category' );
		$cat_1 = get_term_by( 'id', $category_id_1, 'question_category' );
		$cat_2 = get_term_by( 'id', $category_id_2, 'question_category' );

		// Test.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_categories_html( $question_id );
		$this->assertStringContainsString( 'Categories', $result );
		$this->assertStringContainsString( '<span class="question-categories">', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_1->term_id . '" href="' . esc_url( get_term_link( $category_id_1 ) ) . '" title="' . $cat_1->description . '">Question category 1</a>', $result );
		$this->assertStringContainsString( '<a data-catid="' . $cat_2->term_id . '" href="' . esc_url( get_term_link( $category_id_2 ) ) . '" title="' . $cat_2->description . '">Question category 2</a>', $result );
	}

	/**
	 * @covers ::ap_question_tags_html
	 */
	public function testAPQuestionTagsHTMLByPassingQuestionTagIdInsteadOfArray() {
		$question_id = $this->factory()->post->create(
			[
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			]
		);
		$tag_id_1 = $this->factory()->term->create(
			[
				'name'     => 'Question tag 1',
				'taxonomy' => 'question_tag',
			]
		);
		$tag_id_2 = $this->factory()->term->create(
			[
				'name'     => 'Question tag 2',
				'taxonomy' => 'question_tag',
			]
		);
		wp_set_object_terms( $question_id, [ $tag_id_1, $tag_id_2 ], 'question_tag' );
		$tag_1 = get_term_by( 'id', $tag_id_1, 'question_tag' );
		$tag_2 = get_term_by( 'id', $tag_id_2, 'question_tag' );

		// Test.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_tags_html( $question_id );
		$this->assertStringContainsString( 'Tagged', $result );
		$this->assertStringContainsString( '<span class="question-tags" itemprop="keywords">', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_1 ) ) . '" title="' . $tag_1->description . '">Question tag 1</a>', $result );
		$this->assertStringContainsString( '<a href="' . esc_url( get_term_link( $tag_id_2 ) ) . '" title="' . $tag_2->description . '">Question tag 2</a>', $result );
	}

	/**
	 * @covers ::ap_question_have_category
	 */
	public function testAPQuestionHaveCategoryWithoutPassingPostID() {
		$question_id = $this->factory()->post->create(
			[
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			]
		);
		$category_id_1 = $this->factory()->term->create(
			[
				'name'     => 'Question category 1',
				'taxonomy' => 'question_category',
			]
		);

		// Before assigning category.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_have_category();
		$this->assertFalse( $result );

		// After assigning category.
		wp_set_object_terms( $question_id, [ $category_id_1 ], 'question_category' );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_have_category();
		$this->assertTrue( $result );
	}

	/**
	 * @covers ::ap_question_have_tags
	 */
	public function testAPQuestionHaveTagsWithoutPassingPostID() {
		$question_id = $this->factory()->post->create(
			[
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			]
		);
		$tag_id_1 = $this->factory()->term->create(
			[
				'name'     => 'Question tag 1',
				'taxonomy' => 'question_tag',
			]
		);

		// Before assigning tag.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_have_tags();
		$this->assertFalse( $result );

		// After assigning tag.
		wp_set_object_terms( $question_id, [ $tag_id_1 ], 'question_tag' );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = ap_question_have_tags();
		$this->assertTrue( $result );
	}
}
