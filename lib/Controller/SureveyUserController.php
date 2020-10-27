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
use OCA\Forms\Db\Form;
use OCA\Forms\Db\FormMapper;
use OCA\Forms\Db\SurveyUser;
use OCA\Forms\Db\SurveyUserMapper;
use OCA\Forms\Helper\RandomHelper;
use OCA\Forms\Helper\ValidationHelper;
use OCA\Forms\Service\SurveyUserService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\IMapperException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;

class SureveyUserController extends Controller {
	public const TEMPLATE_SURVEY_USER_LOGIN = 'surveyuserlogin';
	public const TEMPLATE_SURVEY_USER_REGISTER = 'surveyuserregister';

	public const SUREVEY_USER_PASS_MIN_LEN = 8;

	protected $appName;

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

	public function __construct(string $appName,
								IL10N $l10n,
								ILogger $logger,
								FormMapper $formMapper,
								SurveyUserMapper $surveyUserMapper,
								SurveyUserService $surveyUserService,
								IRequest $request) {
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->formMapper = $formMapper;
		$this->surveyUserMapper = $surveyUserMapper;
		$this->surveyUserService = $surveyUserService;
		$this->logger = $logger;
		$this->appName = $appName;
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
				$this->surveyUserService->setCurrentSurveyUserId($user->getId());
				$success = true;
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
	public function register($id): PublicTemplateResponse {
		return $this->setupRegisterPage([
			'formid' => $id,
			'mode' => 'first'
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
								   $su_password,
								   $su_password2,
								   $su_realname,
								   $su_address,
								   $su_born): PublicTemplateResponse {

		$su_email = $_POST['su_email'];

		$success = true;
		$message = '';
		$problems = [];

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
			$newUser->setEmail($su_email); // It is already filtered above
			$newUser->setPasswordhash(password_hash($su_password, PASSWORD_ARGON2I));
			$newUser->setConfirmcode(RandomHelper::randomStr(100));

			// TODO TODOFORMS send mail

			$born = (int)$su_born;
			if ($born > 0)
				$newUser->setBornyear(ValidationHelper::filterAlphaNumericUnicde($born));

			try {
				$this->surveyUserMapper->insert($newUser);
				$message = $this->l10n->t('Successful registration.');
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

	public function surveyUserLoginPage($formId = null,
										$success = null,
										$message = null) : PublicTemplateResponse {
		return $this->setupLoginPage([
				'formid' => $formId,
				'message' => $message,
				'success' => $success
			],
			$formId
				? $this->l10n->t('To access the survey, please log in')
				: '');
	}

	private function setupLoginPage($data,
									$subtitle = null) : PublicTemplateResponse {
		return $this->setupPage(
			$data,
			$this->l10n->t('Survey user log in'),
			$subtitle,
			self::TEMPLATE_SURVEY_USER_LOGIN);
	}

	private function setupRegisterPage($data,
									   $subtitle = null) : PublicTemplateResponse {
		return $this->setupPage(
			$data,
			$this->l10n->t('Survey user registration'),
			$subtitle,
			self::TEMPLATE_SURVEY_USER_REGISTER);
	}

	private function setupPage($data,
							   $title,
							   $subtitle,
							   $template) : PublicTemplateResponse {
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
		return $template;
	}
}
