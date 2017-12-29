<?php
/**
 * Tests tag addon.
 */
$I = new UiTester($scenario);
$I->wantTo('check tag addon');
$I->loginAsAdmin();
$I->haveManyTermsInDatabase(40, 'Q_Tag_{{n}}', 'question_tag');
$I->cli('cache delete addons anspress');
$I->cli('option delete anspress_addons');
$I->wait(2);
$I->amOnPage('/wp-admin/admin.php?page=anspress_addons');
$I->wantTo('Enable addon: Tag');
$I->click( 'Tag', '.ap-addons-list');
$I->click('Enable Add-on');
$I->waitForText("Disable Addon", 20);

$id = $I->havePostInDatabase( [ 'post_type' => 'page', 'post_title' => 'Ask', 'post_content' => '[anspress page="ask"]' ] );

$I->amOnPage( '?p=' . $id);
$I->seeElement('#form_question');
$I->seeElement('#form_question-tags-selectized');
$I->fillField(['css' => '#form_question-tags-selectized' ], 'tag');
$I->scrollTo('#form_question-tags-selectized');
$I->waitForElement('.selectize-dropdown-content div:nth-child(4)', 30);
$I->click('.selectize-dropdown-content div:nth-child(5)');
$I->click('.selectize-dropdown-content div:nth-child(8)');
$I->click('.selectize-dropdown-content div:nth-child(6)');
$I->fillField(['css' => '#form_question-tags-selectized' ], 'AwesomeTag');
$I->pressKey('#form_question-tags-selectized', ',');
$I->fillField([ 'name' => 'form_question[post_title]' ], 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.');
$I->fillTinyMceEditorById('form_question-post_content', 'Nulla vestibulum ultricies neque eu semper. Phasellus hendrerit ullamcorper est eget tincidunt.');
$I->click('//*[@id="form_question"]/button');
$I->waitForJS("return jQuery.active == 0;", 10);
$I->seeElement('#ap-single');
$I->see('AwesomeTag', '.question-tags');
$I->seeNumberOfElements('.question-tags a', 4);

