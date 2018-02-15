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
$I->click( '#mceu_10-button' );
$image_field = $I->executeJS( 'return jQuery(tinymce.activeEditor.getElement()).attr("id") + "-images[]"' );
codecept_debug( $image_field );
$I->attachFile( 'input[name="' . $image_field . '"]', 'placeholder.png' );
$content = $I->executeJS( 'tinymce.activeEditor.save(); return tinymce.activeEditor.getContent();' );
$I->assertContains( '{{apimage "placeholder.png"}}', $content );
$I->waitForJS( 'return jQuery.active == 0;', 1 );
$I->click( '//*[@id="form_question"]/button' );
$I->makeScreenshot( 'uploadImage' );
$I->waitForJS( 'return jQuery.active == 0;', 10 );
$I->seeElement( '#ap-single' );
$I->seeElement( '.ap-q-content img' );
