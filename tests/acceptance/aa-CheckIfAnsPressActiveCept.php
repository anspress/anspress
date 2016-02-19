<?php
/**
 * Check if AnsPress is activate
 * @var AcceptanceTester
 */

$I = new AcceptanceTester($scenario);
$I->loginAsAdmin();

$I->wantTo('Check if AnsPress is installed');
$I->amOnPluginPage();
$I->seeInSource('AnsPress', 'CSS:tr#anspress .plugin-title>strong');
$I->wantTo('Check if AnsPress is active');
$I->seeElement('tr#anspress .plugin-title .deactivate');
