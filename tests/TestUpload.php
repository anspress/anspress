<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestUpload extends TestCase {

	use Testcases\Common;

	public function testHooks() {
		$this->assertEquals( 10, has_action( 'deleted_post', [ 'AnsPress_Uploader', 'deleted_attachment' ] ) );
		$this->assertEquals( 10, has_action( 'init', [ 'AnsPress_Uploader', 'create_single_schedule' ] ) );
		$this->assertEquals( 10, has_action( 'ap_delete_temp_attachments', [ 'AnsPress_Uploader', 'cron_delete_temp_attachments' ] ) );
		$this->assertEquals( 10, has_action( 'intermediate_image_sizes_advanced', [ 'AnsPress_Uploader', 'image_sizes_advanced' ] ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'delete_attachment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'deleted_attachment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'create_single_schedule' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'cron_delete_temp_attachments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'upload_modal' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'image_upload' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'image_sizes_advanced' ) );
	}

	public function allowedMimes( $mimes ) {
		$mimes['ico'] = 'image/x-icon';
		$mimes['pdf'] = 'application/pdf';
		unset( $mimes['gif'] );
		unset( $mimes['png'] );
		return $mimes;
	}

	public function testAPAllowedMimesWhenEmpty() {
		ap_opt( 'allowed_file_mime', '' );

		$this->assertTrue( empty( ap_allowed_mimes() ) );
	}

	public function testApAllowedMimesDefault() {
		$this->assertEquals([
			'jpeg|jpg' => 'image/jpeg',
			'png'  => 'image/png',
			'gif'  => 'image/gif',
		], ap_allowed_mimes() );
	}

	public function testApAllowedMimesCustomOption() {
		ap_opt( 'allowed_file_mime', "pdf=>application/pdf\nico=>image/x-icon" );

		$this->assertEquals([
			'ico'  => 'image/x-icon',
			'pdf'  => 'application/pdf',
		], ap_allowed_mimes() );
	}

	public function testApAllowedMimesCustomOptionWithInvalidData() {
		ap_opt( 'allowed_file_mime', "pdf" );

		$this->assertEquals([], ap_allowed_mimes() );
	}

	public function testApAllowedMimesCustomOptionWithInvalidDataMime() {
		ap_opt( 'allowed_file_mime', "pdf=>\nico\n" );

		$this->assertEquals([], ap_allowed_mimes() );
	}

	public function testApAllowedMimesCustomOptionWithExcessiveNewLines() {
		ap_opt( 'allowed_file_mime', "ico=>image/x-icon\n\n" );

		$this->assertEquals([
			'ico' => 'image/x-icon',
		], ap_allowed_mimes() );
	}

	/**
	 * @covers AnsPress_Uploader::create_single_schedule
	 */
	public function testCreateSingleSchedule() {
		// Test when it is not scheduled initially.
		wp_clear_scheduled_hook( 'ap_delete_temp_attachments' );
		$is_scheduled = wp_next_scheduled( 'ap_delete_temp_attachments' );
		$this->assertFalse( $is_scheduled );
		\AnsPress_Uploader::create_single_schedule();
		$is_scheduled = wp_next_scheduled( 'ap_delete_temp_attachments' );
		$this->assertNotFalse( $is_scheduled );

		// Test when it is already scheduled.
		wp_clear_scheduled_hook( 'ap_delete_temp_attachments' );
		\AnsPress_Uploader::create_single_schedule();
		$is_scheduled = wp_next_scheduled( 'ap_delete_temp_attachments' );
		$this->assertNotFalse( $is_scheduled );
		\AnsPress_Uploader::create_single_schedule();
		$is_scheduled = wp_next_scheduled( 'ap_delete_temp_attachments' );
		$this->assertNotFalse( $is_scheduled );
	}

	/**
	 * @covers AnsPress_Uploader::image_sizes_advanced
	 */
	public function testImageSizesAdvanced() {
		// Test for allowing AnsPress custom image size.
		global $ap_thumbnail_only;
		$ap_thumbnail_only = true;
		$expected = [
			'thumbnail' => [
				'width'  => 150,
				'height' => 150,
				'crop'   => true,
			],
		];
		$result = \AnsPress_Uploader::image_sizes_advanced( [ 'original' => [ 'width' => 800, 'height' => 600, 'crop' => false ] ] );
		$this->assertEquals( $expected, $result );

		// Test for not allowing AnsPress custom image size.
		$ap_thumbnail_only = false;
		$expected = [
			'medium' => [
				'width'  => 300,
				'height' => 300,
				'crop'   => true,
			],
		];
		$result = \AnsPress_Uploader::image_sizes_advanced( $expected );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers ::ap_count_users_temp_media
	 */
	public function testAPCountUsersTempMedia() {
		$user_id = $this->factory()->user->create();

		// Create some temporary attachments.
		$this->factory()->attachment->create_many( 5, [ 'post_author' => $user_id, 'post_status' => 'inherit', 'post_title' => '_ap_temp_media' ] );
		$result = ap_count_users_temp_media( $user_id );
		$this->assertEquals( 5, $result );

		// Create some permanent attachments.
		$this->factory()->attachment->create_many( 5, [ 'post_author' => $user_id, 'post_status' => 'publish', 'post_title' => '_non_temp_media' ] );
		$result = ap_count_users_temp_media( $user_id );
		$this->assertEquals( 5, $result );
	}

	/**
	 * @covers ::ap_update_user_temp_media_count
	 */
	public function testAPUpdateUserTempMediaCount() {
		// Test with passing user id.
		$user_id = $this->factory()->user->create();

		// Create some attachments.
		$this->assertEmpty( get_user_meta( $user_id, '_ap_temp_media', true ) );
		$this->factory()->attachment->create_many( 5, [ 'post_author' => $user_id, 'post_status' => 'inherit', 'post_title' => '_ap_temp_media' ] );
		$this->factory()->attachment->create_many( 5, [ 'post_author' => $user_id, 'post_status' => 'publish', 'post_title' => '_non_temp_media' ] );
		ap_update_user_temp_media_count( $user_id );
		$this->assertEquals( 5, get_user_meta( $user_id, '_ap_temp_media', true ) );

		// Test without passing user id.
		$user_id = $this->factory()->user->create();
		wp_set_current_user( $user_id );

		// Create some attachments.
		$this->assertEmpty( get_user_meta( $user_id, '_ap_temp_media', true ) );
		$this->factory()->attachment->create_many( 5, [ 'post_author' => $user_id, 'post_status' => 'inherit', 'post_title' => '_ap_temp_media' ] );
		$this->factory()->attachment->create_many( 5, [ 'post_author' => $user_id, 'post_status' => 'publish', 'post_title' => '_non_temp_media' ] );
		ap_update_user_temp_media_count();
		$this->assertEquals( 5, get_user_meta( $user_id, '_ap_temp_media', true ) );
	}

	/**
	 * @covers ::ap_user_can_upload_temp_media
	 */
	public function testAPUserCanUploadTempMedia() {
		// Test without passing user id.
		$user_id = $this->factory()->user->create();
		wp_set_current_user( $user_id );

		// Create some attachments.
		$this->factory()->attachment->create_many( 3, [ 'post_author' => $user_id, 'post_status' => 'inherit', 'post_title' => '_ap_temp_media' ] );
		ap_update_user_temp_media_count( $user_id );

		// Test 1.
		$this->assertTrue( ap_user_can_upload_temp_media() );

		// Test 2.
		ap_opt( 'uploads_per_post', 2 );
		$this->assertFalse( ap_user_can_upload_temp_media() );

		// Setting to default value.
		ap_opt( 'uploads_per_post', 4 );

		// Test with passing user id.
		$user_id = $this->factory()->user->create();

		// Create some attachments.
		$this->factory()->attachment->create_many( 3, [ 'post_author' => $user_id, 'post_status' => 'inherit', 'post_title' => '_ap_temp_media' ] );
		ap_update_user_temp_media_count( $user_id );

		// Test 1.
		$this->assertTrue( ap_user_can_upload_temp_media( $user_id ) );

		// Test 2.
		ap_opt( 'uploads_per_post', 2 );
		$this->assertFalse( ap_user_can_upload_temp_media( $user_id ) );

		// Setting to default value.
		ap_opt( 'uploads_per_post', 4 );
	}

	/**
	 * @covers ::ap_post_attach_pre_fetch
	 */
	public function testAPPostAttachPreFetch() {
		// Test for not passing attachment ids.
		ap_post_attach_pre_fetch( [] );
		$this->assertEmpty( wp_cache_get( 'posts', 'posts' ) );

		// Test for passing attachment ids for non logged in user.
		$attachment_ids = $this->factory()->attachment->create_many( 5 );
		ap_post_attach_pre_fetch( $attachment_ids );
		$this->assertEmpty( wp_cache_get( 'posts', 'posts' ) );

		// Test for passing attachment ids for logged in user.
		$this->setRole( 'subscriber' );
		$attachment_ids = $this->factory()->attachment->create_many( 5 );
		ap_post_attach_pre_fetch( $attachment_ids );
		foreach ( $attachment_ids as $attachment_id ) {
			$this->assertNotEmpty( wp_cache_get( $attachment_id, 'posts' ) );
		}
		$this->logout();
	}

	/**
	 * @covers ::ap_clear_unattached_media
	 */
	public function testAPClearUnattachedMedia() {
		// Test for not passing user id.
		$this->setRole( 'subscriber' );
		$attachment_ids = $this->factory()->attachment->create_many( 3, array(
			'post_author' => get_current_user_id(),
			'post_title' => '_ap_temp_media',
		) );

		// Before function is called.
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment = get_post( $attachment_id );
			$this->assertIsObject( $attachment );
		}

		// After function is called.
		ap_clear_unattached_media();
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment = get_post( $attachment_id );
			$this->assertNull( $attachment );
		}
		$this->logout();

		// Test for passing user id.
		$user_id = $this->factory()->user->create();
		$attachment_ids = $this->factory()->attachment->create_many( 3, array(
			'post_author' => $user_id,
			'post_title' => '_ap_temp_media',
		) );

		// Before function is called.
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment = get_post( $attachment_id );
			$this->assertIsObject( $attachment );
		}

		// After function is called.
		ap_clear_unattached_media( $user_id );
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment = get_post( $attachment_id );
			$this->assertNull( $attachment );
		}
	}

	/**
	 * @covers ::ap_delete_images_not_in_content
	 */
	public function testAPDeleteImagesNotInContent() {
		$id = $this->insert_answer();
		$uploads = wp_upload_dir();

		// Test begins.
		// Test directly calling the function.
		// Test 1.
		update_post_meta( $id->q, 'anspress-image', 'test-image.jpg' );
		$this->assertNotEmpty( get_post_meta( $id->q, 'anspress-image', true ) );
		ap_delete_images_not_in_content( $id->q );
		$this->assertEmpty( get_post_meta( $id->q, 'anspress-image', true ) );
		$this->assertFalse( file_exists( $uploads['basedir'] . '/anspress-uploads/test-image.jpg' ) );

		// Test 2.
		update_post_meta( $id->a, 'anspress-image', 'test-image.jpg' );
		$this->assertNotEmpty( get_post_meta( $id->a, 'anspress-image', true ) );
		ap_delete_images_not_in_content( $id->a );
		$this->assertEmpty( get_post_meta( $id->a, 'anspress-image', true ) );
		$this->assertFalse( file_exists( $uploads['basedir'] . '/anspress-uploads/test-image.jpg' ) );

		// Test by updating the question and answer.
		// Test 1.
		update_post_meta( $id->q, 'anspress-image', 'test-image.jpg' );
		$this->assertNotEmpty( get_post_meta( $id->q, 'anspress-image', true ) );
		wp_update_post( [ 'ID' => $id->q ] );
		$this->assertEmpty( get_post_meta( $id->q, 'anspress-image', true ) );
		$this->assertFalse( file_exists( $uploads['basedir'] . '/anspress-uploads/test-image.jpg' ) );

		// Test 2.
		update_post_meta( $id->a, 'anspress-image', 'test-image.jpg' );
		$this->assertNotEmpty( get_post_meta( $id->a, 'anspress-image', true ) );
		wp_update_post( [ 'ID' => $id->a ] );
		$this->assertEmpty( get_post_meta( $id->a, 'anspress-image', true ) );
		$this->assertFalse( file_exists( $uploads['basedir'] . '/anspress-uploads/test-image.jpg' ) );
	}

	/**
	 * @covers ::ap_set_media_post_parent
	 */
	public function testAPSetMediaPostParent() {
		$this->setRole( 'subscriber' );

		// Test for not passing user id.
		// Test 1.
		$attachment_id = $this->factory()->attachment->create();
		$post_id = $this->factory()->post->create();
		ap_set_media_post_parent( $attachment_id, $post_id );
		$this->assertEquals( $post_id, wp_get_post_parent_id( $attachment_id ) );

		// Test 2.
		$attachment_ids = $this->factory()->attachment->create_many( 5 );
		$post_id = $this->factory()->post->create();
		ap_set_media_post_parent( $attachment_ids, $post_id );
		foreach ( $attachment_ids as $attachment_id ) {
			$this->assertEquals( $post_id, wp_get_post_parent_id( $attachment_id ) );
		}

		// Test for passing user id.
		$user_id = $this->factory()->user->create();

		// Test 1.
		$attachment_id = $this->factory()->attachment->create( [ 'post_author' => $user_id ] );
		$post_id = $this->factory()->post->create();
		ap_set_media_post_parent( $attachment_id, $post_id, $user_id );
		$this->assertEquals( $post_id, wp_get_post_parent_id( $attachment_id ) );

		// Test 2.
		$attachment_ids = $this->factory()->attachment->create_many( 5, [ 'post_author' => $user_id ] );
		$post_id = $this->factory()->post->create();
		ap_set_media_post_parent( $attachment_ids, $post_id, $user_id );
		foreach ( $attachment_ids as $attachment_id ) {
			$this->assertEquals( $post_id, wp_get_post_parent_id( $attachment_id ) );
		}
	}

	/**
	 * @covers AnsPress_Uploader::cron_delete_temp_attachments
	 */
	public function testCronDeleteTempAttachments() {
		$this->assertEquals( 10, has_action( 'ap_delete_temp_attachments', [ 'AnsPress_Uploader', 'cron_delete_temp_attachments' ] ) );
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$attachment_ids = [
			$this->factory()->post->create( [
				'post_type' => 'attachment',
				'post_title' => '_ap_temp_media',
				'post_parent' => $question_id,
				'post_date' => date( 'Y-m-d H:i:s', strtotime( '-2 days' ) )
			] ),
			$this->factory()->post->create( [
				'post_type' => 'attachment',
				'post_title' => '_ap_temp_media',
				'post_parent' => $question_id,
				'post_date' => date( 'Y-m-d H:i:s', strtotime( '-2 days' ) )
			] ),
			$this->factory()->post->create( [
				'post_type' => 'attachment',
				'post_title' => '_ap_temp_media',
				'post_parent' => $question_id,
				'post_date' => date( 'Y-m-d H:i:s', strtotime( '-2 days' ) )
			] ),
		];

		// Test before the method is called.
		// Tests for attachments available.
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment = get_post( $attachment_id );
			$this->assertIsObject( $attachment );
		}

		// Test for question having attachments.
		ap_update_post_attach_ids( $question_id );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertTrue( ap_have_attach() );
		$this->go_to( '/' );

		// Test for user meta for temp media.
		ap_update_user_temp_media_count();
		$user_meta = get_user_meta( get_current_user_id(), '_ap_temp_media', true );
		$this->assertNotEmpty( $user_meta );

		// Call the method.
		\AnsPress_Uploader::cron_delete_temp_attachments();

		// Test after the method is called.
		// Tests for wp_delete_attachment function being called.
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment = get_post( $attachment_id );
			$this->assertNull( $attachment );
		}

		// Test for ap_update_post_attach_ids function being called.
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertFalse( ap_have_attach() );
		$this->go_to( '/' );

		// Test for ap_update_user_temp_media_count function being called.
		$user_meta = get_user_meta( get_current_user_id(), '_ap_temp_media', true );
		$this->assertEmpty( $user_meta );
	}

	/**
	 * @covers AnsPress_Uploader::deleted_attachment
	 */
	public function testDeletedAttachment() {
		$this->assertEquals( 10, has_action( 'deleted_post', [ 'AnsPress_Uploader', 'deleted_attachment' ] ) );
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$attachment_id = $this->factory()->post->create( [ 'post_type' => 'attachment', 'post_title' => '_ap_temp_media', 'post_parent' => $question_id ] );

		// Test before the method is called.
		// Test for attachment available.
		$attachment = get_post( $attachment_id );
		$this->assertIsObject( $attachment );

		// Test for user meta for temp media.
		ap_update_user_temp_media_count();
		$user_meta = get_user_meta( get_current_user_id(), '_ap_temp_media', true );
		$this->assertNotEmpty( $user_meta );

		// Test for question having attachments.
		ap_update_post_attach_ids( $question_id );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertTrue( ap_have_attach() );
		$this->go_to( '/' );

		// Call the method.
		// \AnsPress_Uploader::deleted_attachment( $attachment_id );
		// This method is triggered on 'deleted_post' action,
		// so we need to delete the attachment using wp_delete_attachment function.
		wp_delete_attachment( $attachment_id, true );

		// Test for attachment available.
		$attachment = get_post( $attachment_id );
		$this->assertNull( $attachment );

		// Test after the method is called.
		// Test for ap_update_user_temp_media_count function being called.
		$user_meta = get_user_meta( get_current_user_id(), '_ap_temp_media', true );
		$this->assertEmpty( $user_meta );

		// Test for ap_update_post_attach_ids function being called.
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertFalse( ap_have_attach() );
	}
}
