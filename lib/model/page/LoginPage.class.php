<?php
/**
 * @author    Janek Ostendorf (ozzy) <ozzy2345de@gmail.com>
 * @copyright Copyright (c) 2013 Janek Ostendorf
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace skies\model\page;

use skies\model\Page;
use skies\model\template\Notification;
use skies\system\language\Language;
use skies\system\user\User;
use skies\util\LanguageUtil;
use skies\util\UserUtil;

/**
 * Login page
 */
class LoginPage extends Page {

	/**
	 * Prepare the output
	 *
	 * @return void
	 */
	public function prepare() {

		/*
		 * Check forms
		 */

		// Login
		if(isset($_POST['login'])) {

			$userId = UserUtil::usernameToID($_POST['username']);

			if($userId !== false) {

				$user = new User($userId);

				// Check password
				if($user->checkPassword($_POST['password'])) {

					if(\Skies::getSession()->login($user->getId(), (isset($_POST['longSession']) && $_POST['longSession'] != null)) !== false) {

						\Skies::updateUser();
						\Skies::getNotification()->add(Notification::SUCCESS, '{{system.page.login.login.success}}', ['userName' => $user->getName()]);

						if(isset($_GET['_1']) && $_GET['_1'] == 'refer') {

							$referTo = '';

							// Redirect back to the HTTP_REFERER if none is given
							if(!isset($_GET['_2'])) {
								$referTo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
							}
							else {

								// Build url
								$referTo = '/'.SUBDIR;

								$i = 2;

								while(isset($_GET['_'.$i])) {
									$referTo .= $_GET['_'.$i++].'/';
								}

							}

							\Skies::getNotification()->addSession(Notification::SUCCESS, '{{system.page.login.login.success}}', ['userName' => $user->getName()]);
							header('Location: '.$referTo);
							exit;
						}

					}
					else {

						\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.login.error.generic}}');

					}

				}
				else {

					\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.login.error.userPassword}}');

				}

			}
			else {

				\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.login.error.userPassword}}');

			}

		}

		// Logout
		if((isset($_GET['_1']) && $_GET['_1'] == 'logout') || isset($_GET['logout'])) {

			if(!\Skies::getUser()->isGuest()) {

				\Skies::getSession()->logout();
				\Skies::updateUser();
				\Skies::getNotification()->addSession(Notification::SUCCESS, '{{system.page.login.logout.success}}');
				header('Location: /'.SUBDIR);

			}
			else {

				\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.logout.error.guest}}');

			}

		}

		// Change email
		if(isset($_POST['changeMailSubmit'])) {

			// Everything set?
			if(isset($_POST['changeMail']) && isset($_POST['changeMailPassword'])) {

				// Check mail pattern
				if(UserUtil::checkMail($_POST['changeMail'])) {

					// Check password
					if(\Skies::getUser()->checkPassword($_POST['changeMailPassword'])) {

						// Everything's right, change the mail
						\Skies::getUser()->setMail($_POST['changeMail'], $_POST['changeMailPassword']);
						\Skies::getUser()->update();

						\Skies::getNotification()->add(Notification::SUCCESS, '{{system.page.login.changeMail.success}}', ['newMail' => \Skies::getUser()->getMail()]);

					}
					else {

						\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.changeMail.error.wrongPassword}}');

					}

				}
				else {

					\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.changeMail.error.mailPattern}}');

				}

			}
			else {

				\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.changeMail.error.missing}}');

			}

		}

		// Change Password
		if(isset($_POST['changePasswordSubmit'])) {

			// Check for passwords
			if(isset($_POST['changePassword1']) && isset($_POST['changePassword2'])) {

				if($_POST['changePassword1'] == $_POST['changePassword2']) {

					\Skies::getUser()->setPassword($_POST['changePassword1']);
					\Skies::updateUser();

					\Skies::getNotification()->add(Notification::SUCCESS, '{{system.page.login.changePassword.success}}');

				}
				else {

					\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.changePassword.error.mismatch}}');

				}

			}
			else {

				\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.changePassword.error.missing}}');

			}

		}

		// Choose language
		$availableLanguages = [];
		$languageIds = [];

		foreach(LanguageUtil::getAllLanguages() as $language) {
			$availableLanguages[] = $language->getTemplateArray();
			$languageIds[] = $language->getId();
		}

		if(isset($_POST['chooseLanguageSubmit'])) {

			// Is the language valid?
			if(in_array($_POST['chooseLanguage'], $languageIds)) {

				\Skies::getUser()->setData('language', $_POST['chooseLanguage']);
				\Skies::getUser()->update();

				// Some language vars are fetched before this is changed. Therefore there might be some text in the old language
				// To avoid this, we use this very ugly method called redirecting.
				// TODO: Look for a better solution
				header('Location: /'.SUBDIR.implode('/', $this->getPath()));
				exit;

			}
			else {

				\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.chooseLanguage.error.notExists}}');

			}

		}

		// Mail and username pattern
		\Skies::getTemplate()->assign([
			'loginPage' => [
				'mailPattern' => UserUtil::MAIL_PATTERN,
				'usernamePattern' => UserUtil::USERNAME_PATTERN,
				'availableLanguages' => $availableLanguages,
				'changeMail' => (isset($_POST['changeMail']) ? $_POST['changeMail'] : null)
			]
		]);

	}

	/**
	 * What's our style name?
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return 'loginPage.tpl';
	}

	/**
	 * Get the name of this page (short form for the URL)
	 *
	 * @return array
	 */
	public function getPath() {
		return ['login'];
	}

	/**
	 * Get the title of this page.
	 *
	 * @return string
	 */
	public function getTitle() {

		return \Skies::getLanguage()->get('system.page.login.title');

	}

	/**
	 * Get the name of the page
	 *
	 * @return string
	 */
	public function getName() {
		return 'login';
	}

}
