<?php
/**
 * @author    Janek Ostendorf (ozzy) <ozzy2345de@gmail.com>
 * @copyright Copyright (c) 2013 Janek Ostendorf
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
 
namespace skies\model\page;

/**
 * Login page
 */
use skies\model\Page;
use skies\model\template\Notification;
use skies\system\user\User;
use skies\util\UserUtil;

class LoginPage extends Page {

	/**
	 * Prepare the output
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

					if(\Skies::getSession()->login($user->getId()) !== false) {

						\Skies::updateUser();
						\Skies::getNotification()->add(Notification::SUCCESS, '{{system.page.login.login.success}}', ['userName' => $user->getName()]);

					}
					else {

						\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.error}}');

					}

				}
				else {

					\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.error.user-pw}}');

				}

			}
			else {

				\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.error.user-pw}}');

			}

		}

		// Logout
		if((isset($_GET['_1']) && $_GET['_1'] == 'logout') || isset($_GET['logout'])) {

			if(!\Skies::getUser()->isGuest()) {

				\Skies::getSession()->logout();
				\Skies::updateUser();
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

						\Skies::getNotification()->add(Notification::SUCCESS, '{{system.page.login.change.mail.success}}', ['newMail' => \Skies::getUser()->getMail()]);

					}
					else {

						\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.change.mail.error.wrong-password}}');

					}

				}
				else {

					\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.change.mail.error.mail-pattern}}');

				}

			}
			else {

				\Skies::getNotification()->add(Notification::ERROR, '{{system.page.login.change.mail.error.missing}}');

			}

		}


		// Mail and username pattern
		\Skies::getTemplate()->assign([
			'loginPage' => [
				'mailPattern' => UserUtil::MAIL_PATTERN,
				'usernamePattern' => UserUtil::USERNAME_PATTERN
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
	 * @return string
	 */
	public function getName() {
		return 'login';
	}

	/**
	 * Get the title of this page.
	 *
	 * @return string
	 */
	public function getTitle() {

		return \Skies::getLanguage()->get('system.page.login.title');

	}
}