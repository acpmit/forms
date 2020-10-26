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

use OCA\Forms\Service\SurveyUserService;
use OCP\AppFramework\Controller;
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

	public function __construct(string $appName,
								IL10N $l10n,
								ILogger $logger,
								SurveyUserService $surveyUserService,
								IRequest $request) {
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->surveyUserService = $surveyUserService;
		$this->logger = $logger;
		$this->appName = $appName;
	}

	public function login(): Response {
		//https://docs.nextcloud.com/server/15/developer_manual/app/requests/controllers.html
		//$response->addCookie('foo', 'bar');
	}

	/**
	 * Provide a form where the survey users can register
	 *
     * @PublicPage
	 * @NoCSRFRequired
	 * @NoSameSiteCookieRequired
	 * @CORS
	 *
	 * @return TemplateResponse The template with the registration details
	 */
	public function register(): PublicTemplateResponse {
		return $this->setupRegisterPage([
			'mode' => 'first'
		]);
	}

	/**
	 * Process the form where the survey users can register
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 * @NoSameSiteCookieRequired
	 * @CORS
	 *
	 * @return PublicTemplateResponse The template with the registration details
	 */
	public function commitRegister($su_email,
								   $su_login,
								   $su_password,
								   $su_password2,
								   $su_address,
								   $su_born): PublicTemplateResponse {

		$su_email = $_POST['su_email'];

		$success = true;
		$message = '';
		$problems = [];

		if (!filter_var($su_email, FILTER_VALIDATE_EMAIL)) {
			$success = false;
			$problems[] = $this->l10n->t('Invalid e-mail format.');
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

		if (strlen($su_login) < 3) {
			$success = false;
			$problems[] = $this->l10n->t(
				'The login name is too short. Please use at least three characters.');
		} else if (!$this->surveyUserService->isUserNameAvailable($su_login)) {
			$success = false;
			$problems[] = $this->l10n->t(
				'The login is in use. Please select a different one.');
		}

		if (!$success)
			$message = $this->l10n->n(
				'There is a problem with the registration data, please correct it:',  // singular string
				'There are problems with the registration data, please correct them:',  // plural string
				count($problems)
			);

		$data = [
			'mode' => 'return',
			'su_email' => $su_email,
			'su_login' => $su_login,
			'su_password' => $su_password,
			'su_password2' => $su_password2,
			'su_address' => $su_address,
			'su_born' => $su_born,
			'success' => $success,
			'message' => $message,
			'problems' => $problems,
		];

		return $this->setupRegisterPage($data,
			$this->l10n->t('Please check your details'));
	}

	private function setupRegisterPage($data,
									   $subtitle = null) : PublicTemplateResponse {
		// TODO
		$template = new PublicTemplateResponse(
			$this->appName,
			self::TEMPLATE_SURVEY_USER_REGISTER,
			$data);
		$template->setHeaderTitle($this->l10n->t('Survey user registration'));
		$template->setHeaderDetails($subtitle === null
			? $this->l10n->t('Please enter your details')
			: $subtitle
		);
		return $template;
	}
}
