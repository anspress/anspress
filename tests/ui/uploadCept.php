<?php
use \Codeception\Util\Locator;
$I = new UiTester( $scenario );
$I->wantTo( 'check image uploads' );
$ask_page = $I->havePageInDatabase( [ 'post_content' => '[anspress page="ask"]' ] );
$I->cli( 'user create uploadtester ct45632dfss3@local.com --role=ap_participant --user_pass=test' );
$I->amOnPage( '/wp-login.php' );
$I->wait( 2 );
$I->submitForm(
	'#loginform', [
		'log' => 'uploadtester',
		'pwd' => 'test',
	], '#wp-submit'
);
$I->amOnPage( '?p=' . $ask_page );
$I->fillField( [ 'name' => 'form_question[post_title]' ], 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.' );
$I->fillTinyMceEditorById( 'form_question-post_content', 'Nulla vestibulum ultricies neque eu semper. Phasellus hendrerit ullamcorper est eget tincidunt.' );

$I->wantTo( 'Check image upload' );
$I->click( '#form_question .ap-btn-insertimage' );
$I->waitForElementVisible( '#ap-modal-imageUpload', 10 );
$I->attachFile( 'input[name="form_image_upload-image"]', 'placeholder.png' );
$I->click( '#form_image_upload .ap-btn-submit' );
$I->waitForElementNotVisible( '#ap-modal-imageUpload', 10 );

$I->wantTo( 'Check image preset in editor or not' );
$content = $I->executeJS( 'tinymce.activeEditor.save(); return tinymce.activeEditor.getContent();' );
$I->assertContains( 'wp-content/uploads/anspress-temp', $content );
$I->waitForJS( 'return jQuery.active == 0;', 1 );
$I->scrollTo( '#form_question .ap-btn-submit', 0, -50 );
$I->click( '#form_question .ap-btn-submit' );
$I->makeScreenshot( 'uploadImage' );
$I->waitForJS( 'return jQuery.active == 0;', 10 );
$I->seeElement( '#ap-single' );
$I->seeElement( '.ap-q-content img' );
