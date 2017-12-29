<?php
$I = new UiTester($scenario);
$I->wantTo('test base page rendering');
$I->haveManyPostsInDatabase(20, array(
  'post_type' => 'question'
));
$I->amOnPage('/questions/');
$I->seeElement('#ap-lists');
$I->seeNumberOfElements('.ap-questions-item', 20);
$I->makeScreenshot('questions');

$I->wantTo('Check base page in front page');
$id = $I->havePageInDatabase(['post_content' => '[anspress]']);
$I->cli('option update show_on_front page');
$I->cli('option update page_on_front ' . $id);
$I->amOnPage('/');
$I->seeElement('#ap-lists');
$I->seeNumberOfElements('.ap-questions-item', 20);
$I->makeScreenshot('questions');
$I->see('2', '.ap-pagination a:nth-child(2)');
$I->click('.ap-pagination a:nth-child(2)');
$I->seeElement('#ap-lists');
$I->see('1', '.ap-pagination a:nth-child(2)');
$I->dontHaveOptionInDatabase('show_on_front');
$I->dontHaveOptionInDatabase('page_on_front');
