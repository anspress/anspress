<?php


class formHooksCest
{
    public function _before(UiTester $I)
    {
    }

    public function _after(UiTester $I)
    {
    }

    /**
     * Checks edit answer form.
     */
    public function testAnswerForm(UiTester $I)
    {
        $I->wantTo('check answer editing');
        $I->loginAsAdmin();
        $id = $I->havePostInDatabase( [ 'post_type' => 'question', 'post_title' => 'Question {n}' ] );
        $id = $I->havePostInDatabase( [ 'post_type' => 'answer', 'post_parent' => $id ] );
        $I->amOnPage('?p='.$id);
        $I->seeElement('#ap-single');
        $I->click('#post-' . $id .' [ap="actiontoggle"]');
        $I->waitForElement('#post-' . $id .' .ap-actions li', 20);
        $I->click('Edit', '#post-' . $id .' .ap-actions li');
        $I->seeElement('#form_answer');
        $I->fillTinyMceEditorById('form_answer-post_content', '######edited####answer######');
        $I->click('#form_answer button[type="submit"]');
        $I->waitForJS("return jQuery.active == 0;", 10);
        $I->seeElement('#ap-single');
        $I->see('######edited####answer######', '#post-' . $id .' .ap-answer-content');
    }
}
