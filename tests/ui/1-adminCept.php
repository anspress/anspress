<?php
$I = new UiTester($scenario);

$I->loginAsAdmin();

// Activate.
$I->makeScreenshot('admin' );
$I->amOnPluginsPage();
$I->wantTo('Activate AnsPress plugin');
$I->activatePlugin('anspress-question-answer');
$I->makeScreenshot(ap_screenshot_inc() . 'activate-plugin' );

$I->wantTo('Check that there are no fatal error reported');
$I->dontSeeElement('#message.error');
$I->seePluginActivated('anspress-question-answer');
$I->makeScreenshot(ap_screenshot_inc() . 'plugin-lists' );

$I->wantTo('Check all default menu exists');
$I->amOnPage('/wp-admin');
$I->SeeElement('#adminmenu .toplevel_page_anspress');
$I->see('AnsPress', '//*[@id="toplevel_page_anspress"]/a/div[3]');

$I->wantTo('Check option tabs');
$I->amOnPage('wp-admin/admin.php?page=anspress_options');
$I->makeScreenshot(ap_screenshot_inc() . 'options' );
$I->see('General', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
$I->see('Posts & Comments', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
$I->see('User Access Control', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
$I->see('Tools', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
$I->see('Reputations', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );

$I->wantTo('Enable addons');
$I->amOnPage('/wp-admin/admin.php?page=anspress_addons');

$I->wantTo('Enable category addon');
$I->click('[data-id="free/category.php"]');
$I->click('.ap-addon-toggle');
$I->makeScreenshot(ap_screenshot_inc() .  'addons');
$I->waitForJS("return jQuery.active == 0;", 30);

$I->wantTo('Add question category');
$I->amOnPage('/wp-admin/edit-tags.php?taxonomy=question_category');
$I->makeScreenshot(ap_screenshot_inc() . 'categories');

$I->fillField(['name' => 'tag-name'], 'Sample Cat');
$I->fillField(['name' => 'description'], 'Nunc tristique molestie fringilla. In massa justo, pellentesque a porttitor sit amet, imperdiet sed nisl. ');
$I->fillField(['name' => 'ap_icon'], 'apicon-anspress');
$I->click('.button.wp-color-result');
$I->fillField(['name' => 'ap_color'], '#666666');
$I->click('//*[@id="submit"]');


$I->waitForJS("return jQuery.active == 0;", 30);
$I->see( 'Sample Cat', [ 'css' => '.wp-list-table tr .column-name' ] );

$I->wantTo('Change site permalink');
$I->amOnPage('wp-admin/options-permalink.php');
$I->selectOption('form [name=selection]', 'Post name');
$I->click('//*[@id="submit"]');
