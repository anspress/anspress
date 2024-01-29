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

	/**
	 * @covers ::ap_allowed_mimes
	 */
	public function testAPAllowedMimes() {
		$this->assertArrayHasKey( 'jpg|jpeg', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'gif', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'png', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'doc|docx', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'xls', ap_allowed_mimes() );

		// Test for adding filter.
		add_filter( 'ap_allowed_mimes', [ $this, 'allowedMimes' ] );
		$this->assertArrayHasKey( 'ico', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'pdf', ap_allowed_mimes() );
		$this->assertArrayNotHasKey( 'gif', ap_allowed_mimes() );
		$this->assertArrayNotHasKey( 'png', ap_allowed_mimes() );
		remove_filter( 'ap_allowed_mimes', [ $this, 'allowedMimes' ] );
		$this->assertArrayNotHasKey( 'ico', ap_allowed_mimes() );
		$this->assertArrayNotHasKey( 'pdf', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'gif', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'png', ap_allowed_mimes() );
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
}
