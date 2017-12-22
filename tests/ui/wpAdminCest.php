<?php


class wpAdminCest
{
    public function _before(UiTester $I){
        // Activate.
        $I->loginAsAdmin();
        $I->amOnPluginsPage();
        $I->wantTo('Activate AnsPress plugin');
        $I->activatePlugin('anspress-question-answer');
        $I->wantTo('Check that there are no fatal error reported');
        $I->dontSeeElement('#message.error');
        $I->seePluginActivated('anspress-question-answer');
    }

    public function _after(UiTester $I){
    }

    // tests
    public function activate(UiTester $I){
        $I->wantTo('Check all default menu exists');
        $I->amOnPage('/wp-admin');
        $I->SeeElement('#adminmenu .toplevel_page_anspress');
        $I->see('AnsPress', '//*[@id="toplevel_page_anspress"]/a/div[3]');

        $I->wantTo('Check option tabs');
        $I->amOnPage('wp-admin/admin.php?page=anspress_options');
        $I->see('General', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
        $I->see('Posts & Comments', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
        $I->see('User Access Control', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
        $I->see('Tools', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
        $I->see('Reputations', [ 'css' => '.anspress-options .nav-tab-wrapper a' ] );
    }
}
