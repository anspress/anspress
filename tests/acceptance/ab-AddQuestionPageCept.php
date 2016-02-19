<?php
/**
 * Add base page for AnsPress
 */

$I = new AcceptanceTester($scenario );
$I->wantTo('Add AnsPress base page' );
$I->loginAsAdmin();
$I->amOnPage('/wp-admin/post-new.php?post_type=page' );

if ( $I->seeInSource('Questions', 'CSS:#the-list .row-title' ) ) {
	$I->fillField([ 'name' => 'post_title' ] , 'Questions' );
	$I->fillTinyMceEditorById('content', '[anspress]' );
	$I->click('input#publish' );
	$I->amOnPage('/wp-admin/edit.php?post_type=page' );
	$I->seeInSource('Questions', 'CSS:#the-list .row-title' );
	$I->amOnPage('/questions/' );
	$I->seeInSource('Questions', 'CSS:h1' );
} else {
	$I->comment('AnsPress base page already exists.' );
}
$I->wantTo('Check AnsPress base page is selected');
$I->amOnPage('/questions/' );

if( !$I->seeElement('#anspress') ){
	$I->amOnPage('/wp-admin/admin.php?page=anspress_options' );
	$I->submitForm('#options_form',  array('anspress_opt[base_page]' => '3' ), 'Save options');
}
