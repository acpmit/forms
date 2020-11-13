<?php
/**
 * @copyright Copyright (c) 2020 John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @author ACPM IT LTD <info@acpmit.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Forms\Controller;

use OC\OCS\Exception;
use OCA\Activity\Data;
use OCA\Forms\Db\Form;
use OCA\Forms\Db\FormMapper;
use OCA\Forms\Db\SubmissionMapper;
use OCA\Forms\Db\SurveyUser;
use OCA\Forms\Db\SurveyUserMapper;
use OCA\Forms\Helper\RandomHelper;
use OCA\Forms\Helper\ValidationHelper;
use OCA\Forms\Service\SurveyUserService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\IMapperException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DownloadResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\Template\SimpleMenuAction;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\Mail\IMailer;
use OCP\Util;

class SureveyUserController extends Controller {
	public const TEMPLATE_SURVEY_USER_LOGIN = 'surveyuserlogin';
	public const TEMPLATE_SURVEY_USER_LOGOUT = 'surveyuserlogout';
	public const TEMPLATE_SURVEY_USER_REGISTER = 'surveyuserregister';
	public const TEMPLATE_SURVEY_USER_RESET = 'surveyuserreset';

	public const DB_CODE_PREFIX_VALIDATE = 'validate:';
	public const DB_CODE_PREFIX_RESET = 'reset:';
	public const DB_USER_STATUS_ACTIVE = 0;
	public const DB_USER_STATUS_BANNED = 1;

	public const ACCESS_CODE_LEN = 100;

	private const EMAIL_TEMPLATE_VERIFY = 1;
	private const EMAIL_TEMPLATE_RESET_PASSWORD = 2;

	public const SUREVEY_USER_PASS_MIN_LEN = 8;

	protected $appName;

	/** @var IConfig */
	private $config;

	/** @var IL10N */
	private $l10n;

	/** @var ILogger */
	private $logger;

	/** @var SurveyUserService */
	private $surveyUserService;

	/** @var FormMapper */
	private $formMapper;

	/** @var SurveyUserMapper */
	private $surveyUserMapper;

	/** @var SettingsController */
	private $settingsController;

	/** @var IMailer */
	private $mailer;

	/** @var SubmissionMapper */
	private $submissionMapper;

	public function __construct(string $appName,
								IL10N $l10n,
								ILogger $logger,
								IConfig $config,
								FormMapper $formMapper,
								SurveyUserMapper $surveyUserMapper,
								SettingsController $settingsController,
								SurveyUserService $surveyUserService,
								SubmissionMapper $submissionMapper,
								IMailer $mailer,
								IRequest $request) {
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->mailer = $mailer;
		$this->formMapper = $formMapper;
		$this->config = $config;
		$this->surveyUserMapper = $surveyUserMapper;
		$this->settingsController = $settingsController;
		$this->surveyUserService = $surveyUserService;
		$this->logger = $logger;
		$this->appName = $appName;
		$this->submissionMapper = $submissionMapper;
	}

	/**
	 * Destroy the survey user session
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function logout() {
		$this->surveyUserService->logoutSurveyUser();
		return $this->setupPage(
			[],
			'',
			'',
			self::TEMPLATE_SURVEY_USER_LOGOUT);
	}

	/**
	 * Process the login details and/or show the login page for the survey users
	 * accordingly
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $id Form id to redirect to after the login
	 * @return Response Login page
	 */
	public function login($id, $su_email, $su_password): Response {
		if (!$su_email || !$su_password) {
			return $this->surveyUserLoginPage(
				$id,
				false,
				$this->l10n->t('Please fill in both the e-mail and password fields.')
			);
		}

		$success = false;

		try {
			$user = $this->surveyUserMapper->findByEmail($su_email);
			if ($user && password_verify($su_password, $user->getPasswordhash())) {
				if (substr($user->getConfirmcode(), 0, strlen(self::DB_CODE_PREFIX_VALIDATE)) === self::DB_CODE_PREFIX_VALIDATE) {
					// Validation required
					return $response = $this->surveyUserLoginPage(
						$id,
						false,
						$this->l10n->t('Please check your e-mail inbox and validate your e-mail address.')
					);
				} else if ($user->getStatus() !== 0) {
					// Banned
					return $response = $this->surveyUserLoginPage(
						$id,
						false,
						$this->l10n->t('Your account have been banned.')
					);
				} else {
					// Login ok
					$this->surveyUserService->setCurrentSurveyUserId($user->getId());
					$success = true;
				}
			}
		} catch (IMapperException $e) {
			// TODO TODOFORMS log
			// We are already at success = false
		}

		if ($success) {
			if ($id)
				$response = new RedirectResponse(\OC::$server->getURLGenerator()
					->linkToRoute('forms.page.goto_form', ['hash' => $id]));
			else
				$response = $this->surveyUserLoginPage(
					null,
					true,
					$this->l10n->t('Successful login')
				);
		} else {
			$response = $this->surveyUserLoginPage(
				$id,
				false,
				$this->l10n->t('Invalid e-mail or login.')
			);
		}

		return $response;
	}

	/**
	 * Show the login page for the survey users
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $id Form id to redirect to after the login
	 * @return Response Login page
	 */
	public function loginForm($id): Response {
		return $this->login($id, null, null);
	}

	/**
	 * Provide a form where the survey users can register
	 *
	 * @param string $id Form id to pass to the login after the registration
	 *
     * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse The template with the registration details
	 */
	public function register($id): Response {
		return $this->setupRegisterPage([
			'formid' => $id,
			'mode' => 'first'
		]);
	}

	/**
	 * Generate a code for the password recovery/activation
	 *
	 * @return string Access code
	 * @throws \Exception
	 */
	private static function getAccessCode(): string {
		return RandomHelper::randomStr(self::ACCESS_CODE_LEN);
	}

	/**
	 * Password verification email link
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param $code
	 */
	public function verifyEmail($code, $formid = '') {
		$code = self::DB_CODE_PREFIX_VALIDATE.
			ValidationHelper::filterAlphaNumericUnicde($code, self::ACCESS_CODE_LEN);
		$failed = true;

		try {
			$user = $this->surveyUserMapper->findByCode($code);
			if ($user !== null) {
				$user->setConfirmcode(null);
				$this->surveyUserMapper->update($user);
				$failed = false;
			}
		} catch (IMapperException $e) {
			// TODO TODOFORMS log
			// We are already at failed true
		}

		if ($failed)
			return $this->setupLoginPage([
				'formid' => $formid,
				'activationMessage' => $this->l10n->t(
					'Invalid activation code. Please try again or contact the site administration.')
			], $this->l10n->t(
				'Activation done.'));
		else
			return $this->setupLoginPage([
				'formid' => $formid,
				'activationMessage' => $this->l10n->t(
					'Thank you for activating your account. Please log in.')
			], $this->l10n->t(
				'Activation done.'));
	}

	/**
	 * Reset password email link (process the code we got in the link and do
	 * the actual reset)
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param $code
	 */
	public function resetPasswordPost($code,
									  $submitted = null,
									  $su_password = null,
									  $su_password2 = null) {
		$notFound = false;
		$resetError = null;
		$code = ValidationHelper::filterAlphaNumericUnicde($code, self::ACCESS_CODE_LEN);

		if ($su_password !== null && $su_password !== $su_password2)
			$resetError = $this->l10n->t(
				'The password and password verification fields don\'t match.');

		if ($su_password === '' && $su_password2 === ''&& $submitted === 'yes')
			$resetError = $this->l10n->t(
				'Please type in your new password.');

		if ($resetError === null
			&& $submitted === 'yes'
			&& strlen($su_password) < self::SUREVEY_USER_PASS_MIN_LEN) {
			$resetError = $this->l10n->t(
				'The password is too short. Please use at least %s characters.',
				self::SUREVEY_USER_PASS_MIN_LEN);
		}

		try {
			if ($resetError === null) {
				if ($submitted === 'yes') {
					$user = $this->surveyUserMapper->findByCode(self::DB_CODE_PREFIX_RESET.$code);
					if ($user !== null) {
						$user->setConfirmcode(null);
						$user->setPasswordhash(password_hash($su_password, PASSWORD_ARGON2I));
						$this->surveyUserMapper->update($user);
					} else {
						$notFound = true;
					}
				} else {
					return $this->setupResetPage([
						'code' => $code,
						'altTitle' => $this->l10n->t(
							'Please enter your new password.'),
						'showresetfields' => true
					]);
				}
			}
		} catch (IMapperException $e) {
			// TODO TODOFORMS log
			$notFound = true;
		}

		if ($notFound)
			$resetError = $this->l10n->t(
				'Invalid reset code, please check it and try again.');

		if ($resetError !== null)
			return $this->setupResetPage([
				'altTitle' => 'Error resetting your password.',
				'message' => $resetError,
				'code' => $code,
				'su_password' => $su_password,
				'su_password2' => $su_password2,
				'showresetfields' => true
			]);

		return $this->setupLoginPage([
			'activationMessage' => $this->l10n->t(
				'You password have been reset. Please log in.')
		], $this->l10n->t(
			'Reset done.'));
	}

	/**
	 * Reset password email link, GET page for the new password form
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param $code
	 */
	public function resetPassword($code) {
		return $this->resetPasswordPost($code);
	}

	/**
	 * Reset password form (display the form to enter the email address)
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function requireResetPassword() {
		return $this->setupResetPage([]);
	}

	/**
	 * Process the password reset form (send the link)
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function requireResetPasswordPost($su_email = null) {
		$code = self::getAccessCode();
		$su_email = filter_var($su_email, FILTER_VALIDATE_EMAIL);
		$message = $this->l10n->t(
			'The password reset instructions were sent, if there was a registered user with this e-mail. Please check your inbox.');

		try {
			$user = $this->surveyUserMapper->findByEmail($su_email);
			if ($user !== null) {
				// We don't want to give out clues about the email being registered or not
				// if this security concern won't be valid, we can give feedback on an
				// email already sent and being invalidated
				//$savedCode = $user->getConfirmcode();
				//if ($savedCode)
				//	$message .= "\n".$this->l10n->t();

				$user->setConfirmcode(self::DB_CODE_PREFIX_RESET.$code);
				$this->surveyUserMapper->update($user);

				// Send mail with the activation code
				$link = \OC::$server->getURLGenerator()
					->linkToRoute('forms.sureveyUser.resetPassword', ['code' => $code]);
				$this->sendMail(self::EMAIL_TEMPLATE_RESET_PASSWORD, $link, $su_email);
			}
			// TODO TODOFORMS log
		} catch (IMapperException $e) {
			// We won't give out clues about whether the email exits
			// TODO TODOFORMS log attempt
		}

		return $this->setupResetPage([
			'message' => $message,
			'email' => $su_email
		]);
	}

	/**
	 * Process the form where the survey users can register
	 *
	 * @param string $id Form id to pass to the login after the registration
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * TODO TODOFORM delete these
	 *
	 * @NoSameSiteCookieRequired
	 * @CORS
	 *
	 * @return PublicTemplateResponse The template with the registration details
	 */
	public function commitRegister($id,
								   $su_email,
								   $su_phone,
								   $su_password,
								   $su_password2,
								   $su_realname,
								   $su_address = null,
								   $su_pp = null,
								   $su_tos = null,
								   $su_born = null): TemplateResponse {
		$success = true;
		$message = '';
		$problems = [];

		if (!$su_realname || strlen($su_realname) <= 0) {
			$success = false;
			$problems[] = $this->l10n->t(
				'Please provide your real name.');
		}

		if (!$su_address || strlen($su_address) <= 0) {
			$success = false;
			$problems[] = $this->l10n->t(
				'Please provide your address.');
		}

		if (!$su_phone || strlen($su_phone) <= 0) {
			$success = false;
			$problems[] = $this->l10n->t(
				'Please provide your phone number.');
		}

		//if (!$su_born || $su_born < 1800) {
		//	$success = false;
		//	$problems[] = $this->l10n->t(
		//		'Please provide your date of birth.');
		//}

		if (!filter_var($su_email, FILTER_VALIDATE_EMAIL)) {
			$success = false;
			$problems[] = $this->l10n->t('Invalid e-mail format.');
		} else {
			$su_email = filter_var($su_email, FILTER_VALIDATE_EMAIL);
			if (!$this->surveyUserService->isEmailAvailable($su_email)) {
				$success = false;
				$problems[] = $this->l10n->t(
					'The e-mail address is in use. Please try to reset your password.');
			}
		}

		if ($su_password !== $su_password2) {
			$success = false;
			$problems[] = $this->l10n->t(
				'The password and password verification fields don\'t match.');
		}

		$docsUrlPrivacyPolicy = $this->settingsController->getPrivacyPolicyUrl();
		$docsUrlTermsOfUse = $this->settingsController->getTermsOfServiceUrl();
		$tosReq = ($docsUrlTermsOfUse !== null && strlen($docsUrlTermsOfUse) > 0);
		$ppReq = ($docsUrlPrivacyPolicy !== null && strlen($docsUrlPrivacyPolicy) > 0);

		if ($ppReq && $su_pp !== 'yes') {
			$success = false;
			$problems[] = $this->l10n->t(
				'You have to accept our privacy policy to register.');
		}

		if ($tosReq && $su_tos !== 'yes') {
			$success = false;
			$problems[] = $this->l10n->t(
				'You have to accept our terms of use to register.');
		}

		if (strlen($su_password) < self::SUREVEY_USER_PASS_MIN_LEN) {
			$success = false;
			$problems[] = $this->l10n->t(
				'The password is too short. Please use at least %s characters.',
				self::SUREVEY_USER_PASS_MIN_LEN);
		}

		if (!$success) {
			$message = $this->l10n->n(
				'There is a problem with the registration data, please correct it:',  // singular string
				'There are problems with the registration data, please correct them:',  // plural string
				count($problems)
			);
		} else {
			$newUser = new SurveyUser();
			$newUser->setRealname(ValidationHelper::filterAlphaNumericUnicde($su_realname, 255));
			$newUser->setAddress(ValidationHelper::filterAlphaNumericUnicde($su_address, 1000));
			$newUser->setPhone(ValidationHelper::filterPhoneNumber($su_phone, 50));
			$newUser->setEmail($su_email); // It is already filtered above
			$newUser->setPasswordhash(password_hash($su_password, PASSWORD_ARGON2I));
			$code = self::getAccessCode();
			$newUser->setConfirmcode(self::DB_CODE_PREFIX_VALIDATE.$code);

			$born = (int)$su_born;
			if ($born > 0)
				$newUser->setBornyear(ValidationHelper::filterAlphaNumericUnicde($born));

			try {
				$this->surveyUserMapper->insert($newUser);
				$message = $this->l10n->t('Successful registration.');

				// Send mail with the activation code
				$link = \OC::$server->getURLGenerator()
					->linkToRoute('forms.sureveyUser.verifyEmail', ['code' => $code, 'formid' => $id]);
				$this->sendMail(self::EMAIL_TEMPLATE_VERIFY, $link, $su_email);
			} catch (Exception $e) {
				// TODO TODOFORMS Log
				$message = $this->l10n->t(
					'Error occurred during the registration, please try again later.');
				$success = false;
			}
		}

		$data = [
			'formid' => $id,
			'mode' => 'return',
			'su_email' => $su_email,
			'su_pp' => $su_pp,
			'su_phone' => $su_phone,
			'su_tos' => $su_tos,
			'su_password' => $su_password,
			'su_password2' => $su_password2,
			'su_address' => $su_address,
			'su_born' => $su_born,
			'su_realname' => $su_realname,
			'success' => $success,
			'message' => $message,
			'problems' => $problems,
		];

		return $this->setupRegisterPage($data,
			$this->l10n->t('Please check your details'));
	}

	/**
	 * Display the login form
	 *
	 * @param string $formId Form ID to be passed, so we can redirect after the
	 * login process
	 * @param null $success True if the login was successful
	 * @param null $message Message to be displayed
	 * @return TemplateResponse Login form
	 */
	public function surveyUserLoginPage($formId = null,
										$success = null,
										$message = null) : TemplateResponse {
		return $this->setupLoginPage([
				'formid' => $formId,
				'message' => $message,
				'success' => $success
			],
			$formId
				? $this->l10n->t('To access the survey, please log in')
				: '');
	}

	/**
	 * Setup the template for the login form
	 *
	 * @param array $data Extra data to be passed
	 * @param string $subtitle Subtitle on the top left side
	 * @return TemplateResponse Login page template
	 */
	private function setupLoginPage($data,
									$subtitle = null) : TemplateResponse {
		return $this->setupPage(
			$data,
			$this->l10n->t('Survey user log in'),
			$subtitle,
			self::TEMPLATE_SURVEY_USER_LOGIN);
	}

	/**
	 * Prepare a registration page for the survey users
	 *
	 * @param array $data Data to be passed to the PHP template
	 * @param null|string $subtitle Left bottom NC title
	 * @return TemplateResponse Regsitration page
	 */
	private function setupRegisterPage($data,
									   $subtitle = null) : TemplateResponse {
		Util::addStyle($this->appName, 'survey');
		$data['pplink'] = $this->settingsController->getPrivacyPolicyUrl();
		$data['toslink'] = $this->settingsController->getTermsOfServiceUrl();

		return $this->setupPage(
			$data,
			$this->l10n->t('Survey user registration'),
			$subtitle,
			self::TEMPLATE_SURVEY_USER_REGISTER);
	}

	/**
	 * Prepare a password reset form page for the survey users
	 *
	 * @param array $data Data to be passed to the PHP template
	 * @return TemplateResponse Password reset page
	 */
	private function setupResetPage($data) : TemplateResponse {
		Util::addStyle($this->appName, 'survey');

		return $this->setupPage(
			$data,
			$this->l10n->t('Survey user registration'),
			$this->l10n->t('Password reset'),
			self::TEMPLATE_SURVEY_USER_RESET);
	}

	/**
	 * Add header actions for the logged in survey users
	 *
	 * @param TemplateResponse $response Add the menu actions to this response
	 * @return TemplateResponse The response we got easier caller syntax
	 */
	public function addSurveyUserMenu(TemplateResponse $response) : TemplateResponse {
		// Only provide menu for the users when they are logged in
		if (!$this->surveyUserService->isSurveyUserLoggedIn())
			return $response;

		$profileUrl = 'http://';
		$logoutUrl = $routeReset = \OC::$server->getURLGenerator()
			->linkToRoute('forms.sureveyUser.logout');
		$docsUrlPrivacyPolicy = $this->settingsController->getPrivacyPolicyUrl();
		$docsUrlTermsOfUse = $this->settingsController->getTermsOfServiceUrl();

		// $profile = new SimpleMenuAction('profile', $this->l10n->t('View profile'), 'icon-user', $profileUrl, 0);
		$menu = [new SimpleMenuAction('logout', $this->l10n->t('Log out'), 'icon-close', $logoutUrl, 0)];
		if ($docsUrlTermsOfUse !== null && strlen($docsUrlTermsOfUse) > 0)
			$menu[] = new SimpleMenuAction('tos', $this->l10n->t('Terms of use'), 'icon-info', $docsUrlTermsOfUse, 1);
		if ($docsUrlPrivacyPolicy !== null && strlen($docsUrlPrivacyPolicy) > 0)
			$menu[] = new SimpleMenuAction('ppolicy', $this->l10n->t('Privacy Policy'), 'icon-info', $docsUrlPrivacyPolicy, 2);

		$response->setHeaderActions($menu);
		return $response;
	}

	/**
	 * Create a general public page for the survey users
	 *
	 * @param array $data Data passed to the template
	 * @param string $title Left top title in NC
	 * @param string $subtitle Left bottom title in NC
	 * @param string $template PHP template name
	 * @return TemplateResponse The created response
	 */
	private function setupPage($data,
							   $title,
							   $subtitle,
							   $template) : TemplateResponse
	{
		Util::addStyle($this->appName, 'public');
		Util::addStyle($this->appName, 'survey');

		if ($this->settingsController->hasSurveyUiLogo())
			$data['logoImage'] = \OC::$server->getURLGenerator()
				->linkToRoute('forms.settings.getLogoImage');
		else
			$data['logoImage'] = false;

		if ($this->settingsController->hasSurveyUiBackground())
			$data['bgImage'] = \OC::$server->getURLGenerator()
				->linkToRoute('forms.settings.getBackgroundImage');
		else
			$data['logoImage'] = false;

		// TODO
		$template = new PublicTemplateResponse(
			$this->appName,
			$template,
			$data);

		$template->setHeaderTitle($title);
		$template->setHeaderDetails($subtitle === null
			? $this->l10n->t('Please enter your details')
			: $subtitle
		);

		return $this->addSurveyUserMenu($template);
	}

	private function sendMail(int $template, string $link, string $to) {
		// The user sets the site name in the theming app
		$serverName = $this->config->getAppValue('theming', 'name', 'Site');
		$message = $this->mailer->createMessage();
		$link = \OC::$server->getURLGenerator()->getAbsoluteURL($link);

		switch ($template) {
			case self::EMAIL_TEMPLATE_VERIFY:
				$mailId = 'forms.verify';
				$title = $this->l10n->t('Password verification for %1$s', [$serverName]);
				$heading = $this->l10n->t('Please confirm that this is your e-mail address');
				$body = $this->l10n->t('To confirm your e-mail address, please press the button below:');
				$textBody = $this->l10n->t('To confirm your e-mail address, please open this link in your browser: %1$s', [$link]);
				$button = $this->l10n->t('Confirm e-mail address');
				break;
			case self::EMAIL_TEMPLATE_RESET_PASSWORD:
				$mailId = 'forms.reset';
				$title = $this->l10n->t('Password reset for %1$s', [$serverName]);
				$heading = $this->l10n->t('Someone requested a password reset at %1$s for you.', [$serverName]);
				$body = $this->l10n->t('To change your password, please press the button below:');
				$textBody = $this->l10n->t('To change your password, please open this link in your browser: %1$s', [$link]);
				$button = $this->l10n->t('Change password');
				break;
		}

		$emailTemplate = $this->mailer->createEMailTemplate($mailId, [
			'link' => $link,]);
		$emailTemplate->setSubject($title);
		$emailTemplate->addHeader();
		$emailTemplate->addHeading($heading, false);
		$emailTemplate->addBodyText(htmlspecialchars($body), $textBody);
		$emailTemplate->addBodyButton($button, $link);

		$message->setTo([$to]);

		// The "From" contains the sharers name
		$message->setFrom([\OCP\Util::getDefaultEmailAddress($serverName) => $serverName]);

		$message->useTemplate($emailTemplate);
		$this->mailer->send($message);
	}

	/**
 	 * @NoAdminRequired
	 *
	 * Set the status on a survey user
	 *
	 * @param int $user Survey user id
	 * @param int $status New status
	 * @return DataResponse
	 */
	public function apiSetStatus(int $user, int $status): DataResponse {
		if ($this->settingsController->isAccessControlEnabled() &&
			!$this->settingsController->canViewPersonalData())
			return new DataResponse($this->l10n->t('Access denied'),
				Http::STATUS_UNAUTHORIZED);

		$user = (int)$user;
		$status = (int)$status;
		if ($user <= 0 || $status < 0 || $status >= 100)
			return new DataResponse($this->l10n->t('Invalid request'),
				Http::STATUS_BAD_REQUEST);

		$userObj = $this->surveyUserService->getSurveyUser($user);
		if ($userObj === null)
			return new DataResponse($this->l10n->t('Invalid request'),
				Http::STATUS_BAD_REQUEST);

		try {
			$userObj->setStatus((int)$status);
			$this->surveyUserMapper->update($userObj);

			// Toggle survey results
			$submissions = $this->submissionMapper->findByUser(SurveyUserService::SURVEY_USER_DB_PREFIX.$user);
			foreach ($submissions as $submission) {
				$submission->setStatus($status === 0 ? 0 : 1);
				$this->submissionMapper->update($submission);
			}

		} catch (IMapperException $e) {
			// TODO FORMSTODO log
			return new DataResponse($this->l10n->t('Internal error'),
				Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		return new DataResponse(['OK']);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return Http\DownloadResponse All survey users exported to a CVS file
	 */
	public function apiListExport(): Response {
		if ($this->settingsController->isAccessControlEnabled() &&
			!$this->settingsController->canViewPersonalData())
			return new DataResponse($this->l10n->t('Access denied'),
				Http::STATUS_UNAUTHORIZED);

		$csv = sprintf(
			'"%s","%s","%s","%s","%s"'."\n",
			$this->l10n->t('Id'),
			$this->l10n->t('Real name'),
			$this->l10n->t('Address'),
			$this->l10n->t('E-mail'),
			$this->l10n->t('Phone number')
			);

		foreach ($this->surveyUserMapper->listAll() as $user)
			$csv .= sprintf(
				'%s,"%s","%s","%s","%s"'."\n",
				$user->getId(),
				$user->getRealname(),
				$user->getAddress(),
				$user->getEmail(),
				$user->getPhone()
			);

		$download = new DataDownloadResponse(
			$csv,
			$this->l10n->t('survey_users.csv'),
			'text/csv'
		);

		return $download;
	}

	/**
	 * @NoAdminRequired
	 *
	 * Read the list of the currently registered survey users
	 *
	 * @param string $filter Filter to use listing the users
	 * @return DataResponse List of survey users
	 */
	public function apiList(string $filter, int $page): DataResponse {
		if ($this->settingsController->isAccessControlEnabled() &&
			!$this->settingsController->canViewPersonalData())
			return new DataResponse($this->l10n->t('Access denied'),
				Http::STATUS_UNAUTHORIZED);

		$limit = 100;

		$data = [
			'results' => [],
			'limit' => $limit,
			'page' => $page,
			'more' => false,
		];

		foreach ($this->surveyUserMapper->findAll($filter, $limit+1, $limit * $page) as $user) {
			$data['results'][] = [
				'id' => $user->getId(),
				'realname' => $user->getRealname(),
				'address' => $user->getAddress(),
				'email' => $user->getEmail(),
				'phone' => $user->getPhone(),
				'status' => $user->getStatus(),
			];
			if (count($data['results']) === $limit) {
				$data['more'] = true;
				break;
			}
		}

		return new DataResponse($data);
	}

}
