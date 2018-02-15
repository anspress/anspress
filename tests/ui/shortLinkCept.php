<?php
$I = new UiTester( $scenario );
$I->wantTo( 'check if shortlink is working or not' );

$id = $I->havePostInDatabase( [ 'post_type' => 'question' ] );
$I->amOnPage( '?ap_page=shortlink&ap_q=' . $id );
$I->makeScreenshot( ap_screenshot_inc() . 'shortlink-question' );
$I->scrollTo( '#ap-single' );
$I->seeElement( '#ap-single' );
$I->seeElement( '[apid="' . $id . '"]' );

$id = $I->havePostInDatabase(
	[
		'post_type'   => 'answer',
		'post_parent' => $id,
	]
);

$I->amOnPage( '?ap_page=shortlink&ap_a=' . $id );
codecept_debug( $I->grabFullUrl() );
$I->makeScreenshot( ap_screenshot_inc() . 'shortlink-answer' );
$I->scrollTo( '#answers' );
$I->seeElement( '#answers' );
$I->seeElement( '[apid="' . $id . '"]' );
