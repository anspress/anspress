<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesAttachments extends TestCase {

	use Testcases\Common;

	public function testAttachments() {
		$icons = array(
			'image/jpeg'               => 'file-image-o',
			'image/png'                => 'file-image-o',
			'image/jpg'                => 'file-image-o',
			'image/gif'                => 'file-image-o',
			'application/msword'       => 'file-word-o',
			'application/vnd.ms-excel' => 'file-excel-o',
			'application/pdf'          => 'file-pdf-o',
		);
		$question_id   = $this->insert_question();
		$attachment_id = $this->factory()->attachment->create_upload_object( dirname( __DIR__ ) . '/assets/img/anspress-hero.png', $question_id );
		ap_update_post_attach_ids( $question_id );
		$this->go_to( '/?post_type=question&p=' . $question_id );

		// Test 1.
		ob_start();
		ap_get_template_part( 'attachments' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-attachments">', $result );
		$this->assertStringContainsString( '<h3>Attachments</h3>', $result );
		$this->assertStringContainsString( 'Download file', $result );
		$this->assertStringContainsString( esc_url( wp_get_attachment_url( $attachment_id ) ), $result );
		$this->assertStringContainsString( 'apicon-file-image-o', $result );
		$this->assertStringContainsString( basename( get_attached_file( $attachment_id ) ), $result );

		// Test 2.
		$question_id = $this->insert_question();
		$attachment_ids = [
			$this->factory()->attachment->create_upload_object( dirname( __DIR__ ) . '/assets/files/anspress.pdf', $question_id ),
			$this->factory()->attachment->create_upload_object( dirname( __DIR__ ) . '/assets/img/question.png', $question_id ),
			$this->factory()->attachment->create_upload_object( dirname( __DIR__ ) . '/assets/img/answer.png', $question_id ),
		];
		ap_update_post_attach_ids( $question_id );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		ap_get_template_part( 'attachments' );
		$result = ob_get_clean();
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment = get_post( $attachment_id );
			$attachment_url = esc_url( wp_get_attachment_url( $attachment_id ) );
			$attachment_file = basename( get_attached_file( $attachment_id ) );
			$expected_icon = isset( $icons[ $attachment->post_mime_type ] ) ? $icons[ $attachment->post_mime_type ] : 'file-archive-o';
			$expected_output_1 = '<a class="ap-attachment" href="' . $attachment_url . '" target="_blank" title="Download file">';
			$expected_output_2 = '<i class="apicon-' . $expected_icon . '"></i>';
			$expected_output_3 = '<span>' . $attachment_file . '</span>';

			// Tests.
			$this->assertStringContainsString( '<div class="ap-attachments">', $result );
			$this->assertStringContainsString( '<h3>Attachments</h3>', $result );
			$this->assertStringContainsString( $expected_output_1, $result );
			$this->assertStringContainsString( $expected_output_2, $result );
			$this->assertStringContainsString( $expected_output_3, $result );
		}
	}

	public function testNoAttachments() {
		$question_id = $this->insert_question();
		$this->go_to( '/?post_type=question&p=' . $question_id );

		// Test.
		ob_start();
		ap_get_template_part( 'attachments' );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-attachments">', $result );
		$this->assertStringContainsString( '<h3>Attachments</h3>', $result );
		$this->assertStringNotContainsString( 'Download file', $result );
	}
}
