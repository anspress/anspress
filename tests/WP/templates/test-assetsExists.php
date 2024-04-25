<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestTemplatesAssetsExists extends TestCase {

	use Testcases\Common;

	public function testAssetsExists() {
		// Font files.
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/calibri.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/DeliusSwashCaps.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/Glegoo-Bold.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/OpenSans.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/Pacifico.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/avatar-fonts/SIL Open Font License.txt' );

		// CSS files.
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts/anspress.eot' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts/anspress.svg' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts/anspress.ttf' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts/anspress.woff' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/editor.css' );
		// $this->assertFileExists( ANSPRESS_THEME_DIR . '/css/fonts.css' );
		// $this->assertFileExists( ANSPRESS_THEME_DIR . '/css/main.css' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/css/overrides.css' );
		// $this->assertFileExists( ANSPRESS_THEME_DIR . '/css/rtl.css' );

		// Image files.
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/images/cover.jpg' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/images/cover.psd' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/images/editor-buttons.png' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/images/hovercard.svg' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/images/loading.gif' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/images/loading.png' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/images/loading.svg' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/images/small_cover.jpg' );

		// JS files.
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/js/jquery.peity.min.js' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/js/theme.js' );

		// SCSS files.
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/activity.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/alert.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/ask.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/breadcrumbs.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/buddypress.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/buttons.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/category.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/comment.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/dropdown.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/embed-question.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/fonts.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/form.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/hover_card.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/list.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/main.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/mixins.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/modal.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/notifications.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/question.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/reputations.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/reset.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/responsive.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/rtl.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/snackbar.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/sticky.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/tag.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/tip.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/upload.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/user-posts.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/user.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/variables.scss' );
		$this->assertFileExists( ANSPRESS_THEME_DIR . '/scss/widget.scss' );
	}
}
