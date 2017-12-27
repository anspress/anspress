<?php


class functionsCest
{
    public function _before(UiTester $I)
    {
    }

    public function _after(UiTester $I)
    {
    }

    // tests
    public function testApGetShortLink(UiTester $I)
    {
        $I->wantTo('check if shortlink is working or not');
        $I->loginAsAdmin();
        $I->wantTo('Change site permalink');
        $I->amOnPage('wp-admin/options-permalink.php');
        $I->selectOption('form [name=selection]', 'Post name');
        $I->click('//*[@id="submit"]');
        $id = $I->havePostInDatabase( [ 'post_type' => 'question', 'post_title' => 'Question {n}' ] );
        $I->amOnPage('?ap_page=shortlink&ap_q=' . $id);
        $I->makeScreenshot(ap_screenshot_inc() . 'shortlink-question');
        $I->scrollTo('#ap-single');
        $I->seeElement('#ap-single');
        $I->seeElement('[apid="'.$id.'"]');

        $id = $I->havePostInDatabase( [ 'post_type' => 'answer', 'post_parent' => $id ] );
        $I->amOnPage('?ap_page=shortlink&ap_a=' . $id);
        $I->makeScreenshot(ap_screenshot_inc() . 'shortlink-answer');
        $I->scrollTo('#answers');
        $I->seeElement('#answers');
        $I->seeElement('[apid="'.$id.'"]');
    }
}
