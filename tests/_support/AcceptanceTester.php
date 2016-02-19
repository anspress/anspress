<?php


/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
	use _generated\AcceptanceTesterActions;

	public $admin_username = 'admin';
	public $admin_password = 'admin';
	public $users = array( 'user1', 'user2', 'user3', 'user4' );
	public $questions = array( 'question1' => 'This is question 1' );
	public $answers = array(
		'answer1' => 'This is an interesting answer on question 1. Hope this will work.',
		'answer2' => 'This is an interesting question again for question 1. Hope this will work as well.',
	);
	public $comment = array(
		'comment1' => 'This is an awesome comment on question1. I like it. Nam hendrerit lacinia vulputate.',
		'comment2' => 'This is an awesome comment on answer1. I like it.',
		'comment3' => 'This is an awesome comment on answer1 again.. Maann..!! its gonna be super cool.',
	);

	/**
	 * Login a user by user login and password.
	 * @param string $name User login.
	 * @param string $password User password.
	 */
	public function login($name, $password) {
		$this->amOnPage('/wp-login.php' );
		$this->fillField('Username', $name );
		$this->fillField('Password', $password );
		$this->click('Log In' );
		//$this->see('Welcome to WordPress!' );
	}

	/**
	 * Login as an admin
	 */
	public function loginAsAdmin() {
		$this->login($this->admin_username, $this->admin_password );
	}

	/**
	 * Switch logged in user.
	 * @param string $name User login.
	 * @param string $password User password.
	 */
	public function switch_user( $name, $password ) {
		//$this->amOnPage('/wp-admin' );
		//$loutput_button = $I->executeJS('return jQuery(".wp-admin-bar-logout").length > 0');
		//concept_debug($loutput_button);
		/*if ( $loutput_button ) {
			$this->click( 'Log Out', '.ab-item' );
		}*/
		$this->login( $name, $password );
	}

	public function amOnPluginPage() {
		$this->amOnPage('/wp-admin/plugins.php' );
	}

	public function fillTinyMceEditorById($id, $content) {
		$this->fillTinyMceEditor('id', $id, $content );
	}

	public function fillTinyMceEditorByName($name, $content) {
		$this->fillTinyMceEditor('name', $name, $content );
	}

	private function fillTinyMceEditor($attribute, $value, $content) {
		$this->fillRteEditor(
			\Facebook\WebDriver\WebDriverBy::xpath(
				'//textarea[@' . $attribute . '=\'' . $value . '\']/../div[contains(@class, \'mce-tinymce\')]//iframe'
			),
			$content
		);
	}

	private function fillRteEditor($selector, $content) {
		$this->executeInSelenium(
			function (\Facebook\WebDriver\Remote\RemoteWebDriver $webDriver)
			use ($selector, $content) {
				$webDriver->switchTo()->frame(
					$webDriver->findElement($selector )
				);

				$webDriver->executeScript(
					'arguments[0].innerHTML = "' . addslashes($content ) . '"',
					[ $webDriver->findElement(\Facebook\WebDriver\WebDriverBy::tagName('body' ) ) ]
				);

				$webDriver->switchTo()->defaultContent();
			});
	}
}
