<?php
/**
 * Check if AnsPress is activate
 * @var UITester
 */

$I = new UITester($scenario);
$I->loginAsAdmin();
$I->amOnPluginPage();
$I->wantTo('Check if AnsPress is active');
$I->seeElement('[data-slug="anspress-question-answer"] .deactivate');
//$I->makeScreenshot('admin' );
