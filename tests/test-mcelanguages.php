<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Require the mce-languages.php file.
require_once ANSPRESS_DIR . 'includes/mce-languages.php';

class TestMCELanguages extends TestCase {

	/**
	 * @covers ::ap_tinymce_translations
	 */
	public function testapTinyMCETranslations() {
		// Call the ap_tinymce_translations() function.
		$result = ap_tinymce_translations();

		// Test if the result is a non-empty string.
		$this->assertIsString( $result );
		$this->assertNotEmpty( $result );

		// Test if the returned string contains the expected translations.
		$expected_translations = array(
			'i18n_insert_image'         => 'Insert media',
			'i18n_insert_media'         => 'Insert Media (AnsPress)',
			'i18n_close'                => 'Close',
			'i18n_select_file'          => 'Select File',
			'i18n_browse_from_computer' => 'Browse from computer',
			'i18n_image_title'          => 'Image title',
			'i18n_media_preview'        => 'Media preview',
			'i18n_insert_code'          => 'Insert code',
			'i18n_insert_codes'         => 'Insert codes (AnsPress)',
			'i18n_insert'               => 'Insert',
			'i18n_inline'               => 'Inline?',
			'i18n_insert_your_code'     => 'Insert your code.',
		);

		foreach ( $expected_translations as $key => $translation ) {
			$this->assertStringContainsString( $key, $result );
			$this->assertStringContainsString( $translation, $result );
		}
	}
}
