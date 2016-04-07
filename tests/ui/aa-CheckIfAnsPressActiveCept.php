<?php
/**
 * Check if AnsPress is activate
 * @var UITester
 */

$I = new UITester($scenario);
$I->loginAsAdmin();

$I->wantTo('Check if AnsPress is installed');
$I->amOnPluginPage();
$I->seePluginActivated('anspress-question-answer');
$I->wantTo('Check if AnsPress is active');
$I->seeElement('tr#anspress .plugin-title .deactivate');
//$I->makeScreenshot('admin' );
