<?php
/**
 * Tests email templates options.
 */
// $I = new UiTester($scenario);
// $I->loginAsAdmin();
// $I->cli('cache delete addons anspress');
// $I->cli('option delete anspress_addons');
// $I->wait(2);
// $I->amOnPage('/wp-admin/admin.php?page=anspress_addons');
// $I->wantTo('Enable addon: Tag');
// $I->click( 'Tag', '.ap-addons-list');
// $I->click('Enable Add-on');
// $I->waitForText("Disable Addon", 20);
// $I->wantTo('check email templates tabs');
// $I->amOnPage('/wp-admin/admin.php?page=anspress_options');
// $I->click('Email Templates');
// $I->see('More options:');
// $I->see('Select template:');
// $I->see('Edit template:');
// $I->see('Email subject');
// $I->see('Email body');
// $I->see('Allowed tags');
// $I->wantTo('check if form get reloaded on changing template');
// $I->selectOption('select[name=email_templates]', 'Edit Question');
// $I->waitForJS("return jQuery.active == 0;", 10);
// $I->seeInField('form input[name=ap_form_name]', 'form_email_template');
// $I->seeInField('form input[name=template]', 'edit_question');
// $I->seeInField('form_email_template[subject]', 'A question is edited by {editor}');
// $I->seeInField('form_email_template[body]', '<div class="ap-email-event">A question is edited by <b class="user-name">{editor}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{question_link}">{question_title}</a></h1><div class="ap-email-content">{question_content}</div></div>');
// $I->fillField('form_email_template[subject]', 'Hello subject');
// $I->fillTinyMceEditorById('form_email_template-body', 'Hello Lorem');
// $I->click('#form_email_template button[type=submit]');
// $I->waitForJS("return jQuery.active == 0;", 10);
// $I->seeInField('form_email_template[subject]', 'Hello subject');
// $I->seeInField('form_email_template[body]', 'Hello Lorem');
