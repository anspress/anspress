<?php
/**
 * AnsPress ask form
 */

$I = new UITester($scenario );
$I->wantTo('Check AnsPress actions links' );
$I->loginAs('user1', 'user1' );
$I->amOnPage( '/questions/ask/' );
$I->seeElement('#ask_form' );

$I->wantTo('Submit new question' );
$I->fillField([ 'name' => 'title' ], 'action-test' );
$I->fillTinyMceEditorById('description', 'Fusce iaculis condimentum nisi, nec commodo eros molestie at. Nullam libero erat, sollicitudin eu condimentum sit amet, rhoncus ut lacus. Integer vulputate nibh et diam sagittis in dictum mauris dapibus. ' );

$I->click('#ask_form .ap-btn-submit' );
$I->waitForJS( 'return jQuery.active == 0;',60 );

$I->wantTo('Check if actions dropdown showing' );
$I->amOnPage( '/questions/question/action-test/' );
$I->click( '.ap-q-cells .more-actions.ap-dropdown-toggle' );
$I->waitForJS( 'return jQuery.active == 0;',60 );
$I->seeElement('.ap-q-cells .ap-post-action .ap-dropdown-menu');

