<?php
$I = new UiTester( $scenario );
$I->wantTo( 'test new comments' );
$id = $I->havePostInDatabase( [ 'post_type' => 'question' ] );
$I->cli( 'user create commenttester ct45645fre@local.com --role=ap_participant --user_pass=test' );
$I->cli( 'option update comment_moderation 0' );
$I->cli( 'option update comment_whitelist 0' );
$I->amOnPage( '/wp-login.php' );
$I->wait( 3 );
$I->submitForm(
	'#loginform', [
		'log' => 'commenttester',
		'pwd' => 'test',
	], '#wp-submit'
);
$I->amOnPage( '?p=' . $id );
$I->click( "[apid='$id'] .ap-btn-newcomment" );
$I->waitForElement( '#form_comment', 30 );
$I->makeScreenshot( 'new-comment' );
$comment_text = 'Suspendisse malesuada mattis eleifend. Phasellus sed dui massa, vitae dapibus augue. Nulla mauris sem, hendrerit sed fringilla a, facilisis vitae eros.';
$I->fillField( '#form_comment-content', $comment_text );
$I->click( '#form_comment [type=submit]' );
$I->waitForElementNotVisible( '#form_comment', 30 );
$I->waitForElementVisible( "#comments-$id apcomment:first-child", 30 );
$I->see( $comment_text, "#comments-$id apcomment" );
$I->scrollTo( "#comments-$id", 0, -100 );
$I->makeScreenshot( 'comment' );

$I->wantTo( 'Test comment edit' );
$I->click( "#comments-$id apcomment:first-child .ap-comment-actions a:first-child" );
$I->waitForElement( '#form_comment', 30 );
$I->makeScreenshot( 'edit-comment' );

$comment_text = 'Nam ut urna sit amet libero ultrices cursus. Integer vulputate nibh et diam sagittis in dictum mauris dapibus.';
$I->fillField( '#form_comment-content', $comment_text );
$I->click( '#form_comment [type=submit]' );
$I->waitForElementNotVisible( '#form_comment', 30 );
$I->waitForElementVisible( "#comments-$id apcomment:first-child", 30 );
$I->see( $comment_text, "#comments-$id apcomment" );
$I->scrollTo( "#comments-$id" );
$I->makeScreenshot( 'edited-comment' );

$I->wantTo( 'delete a comment' );
$comment_id = $I->haveCommentInDatabase( $id );
$I->amOnPage( '/wp-login.php' );
$I->wait( 3 );
$I->submitForm(
	'#loginform', [
		'log' => 'admin',
		'pwd' => 'admin',
	], '#wp-submit'
);
$I->amOnPage( '?p=' . $id );
$I->click( "#comment-$comment_id .ap-comment-actions a:nth-child(2)" );
$I->waitForText( 'Comment successfully deleted', 5 );
$I->waitForElementNotVisible( "#comment-$comment_id", 30 );

$I->wantTo( 'check comments loading' );
$opt                   = $I->grabOptionFromDatabase( 'anspress_opt' );
$opt['comment_number'] = 5;
$I->haveOptionInDatabase( 'anspress_opt', $opt );
$I->haveManyCommentsInDatabase( 20, $id );

$I->amOnPage( '?p=' . $id );
$I->scrollTo( "#comments-$id", 0, -100 );
$comment_count = $I->cliToArray( 'db query "SELECT count(*) as count FROM wp_comments where comment_post_ID=' . $id . '"' );
$I->seeNumberOfElements( "#comments-$id apcomment", 5 );
$I->click( "#comments-$id .ap-view-comments" );
$I->waitForJS( 'return jQuery.active == 0;', 10 );
$I->seeNumberOfElements( "#comments-$id apcomment", $comment_count[1] );
