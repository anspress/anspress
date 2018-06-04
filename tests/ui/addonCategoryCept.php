<?php
/**
 * Tests tag addon.
 */
$I = new UiTester( $scenario );
$I->wantTo( 'check category addon' );
$I->loginAsAdmin();
$ids = $I->haveManyTermsInDatabase( 25, 'Q_category_{{n}}', 'question_category' );
$I->cli( 'cache delete addons anspress' );
$I->cli( 'option delete anspress_addons' );
$I->cli( "wp rewrite structure '/%postname%/'" );
$I->wait( 2 );
$I->amOnPage( '/wp-admin/admin.php?page=anspress_addons' );
$I->wantTo( 'Enable addon: category' );
$I->click( 'Enable Addon', '.addon-categories' );
$I->waitForText( 'Disable Addon', 20, '.addon-categories' );

$I->wantTo( 'check category creation in wp-admin' );
$I->amOnPage( '/wp-admin/edit-tags.php?taxonomy=question_category' );
$I->click( '.wp-color-result' );
$I->fillField( '#ap-category-color', '#000' );
$I->submitForm(
	'#addtag', array(
		'tag-name' => 'ApSampleCat',
		'ap_icon'  => 'apicon-star',
	)
);
$I->waitForText( 'ApSampleCat', 20 );
$I->seeInSource( '<span class="ap-category-icon apicon-star"' );

$I->wantTo( 'check categories page' );
$I->amOnPage( '/questions/categories/' );
$I->seeElement( '#ap-categories' );
$I->see( 'Q_category_1' );
$I->see( 'Q_category_15' );
$I->see( 'Q_category_19' );
$I->makeScreenshot( 'categories' );
$I->scrollTo( '.ap-pagination' );
$I->click( '.ap-pagination a:nth-child(2)' );
$I->see( '1', '.ap-pagination a:nth-child(2)' );

$I->wantTo( 'check single category page' );
$I->haveManyPostsInDatabase(
	20, array(
		'post_type' => 'question',
		'tax_input' => array(
			'question_category' => [ 'Q_category_20', 'Q_category_0' ],
		),
	)
);
$I->click( 'Q_category_20' );
$I->seeElement( '.ap-questions' );

// Rest addons.
$I->cli( 'cache delete addons anspress' );
$I->cli( 'option delete anspress_addons' );
