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

namespace OCA\Forms\Service;

use OCA\Forms\Db\FormMapper;
use OCA\Forms\Db\SurveyUser;
use OCA\Forms\Db\SurveyUserMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\IMapperException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\ILogger;

/**
 * Class SurveyUserService Handles the survey user registration and login
 * @package OCA\Forms\Service
 */
class SurveyUserService
{
	/** @var FormMapper */
	private $formMapper;

	/** @var SurveyUserMapper */
	private $surveyUserMapper;

	/** @var ILogger */
	private $logger;

	public const SURVEY_USER_SESSION_ID = 'FORMS_SURVEY_USER';

	public function __construct(FormMapper $formMapper,
								SurveyUserMapper $surveyUserMapper,
								ILogger $logger) {
		$this->formMapper = $formMapper;
		$this->surveyUserMapper = $surveyUserMapper;
		$this->logger = $logger;
	}

	public function isSurveyUserLoggedIn() {
		$user = \OC::$server->getSession()->get(self::SURVEY_USER_SESSION_ID);
		return $user === null || ((int)$user) === 0;
	}

	public function setCurrentSurveyUser($userId) {
		\OC::$server->getSession()->set(self::SURVEY_USER_SESSION_ID, $userId);
	}

	public function getCurrentSurveyUser() : ?SurveyUser {
		if (!$this->isSurveyUserLoggedIn())
			return null;

		try {
			$user = $this->surveyUserMapper->get(
				$_SESSION[self::SURVEY_USER_SESSION_ID]);
			return $user;
		} catch (IMapperException $e) {
			// TODO FORMSTODO log
			return  null;
		}
	}

	/**
	 * Check if the user name is available to register
	 *
	 * @param string $loginToCheck Username to check
	 * @return bool True if the username is not taken
	 */
	public function isUserNameAvailable($loginToCheck) {
		try {
			$this->surveyUserMapper->findByLogin($loginToCheck);
		} catch (DoesNotExistException $e) {
			return true;
		} catch (MultipleObjectsReturnedException $e) {
			return false;
		}

		return false;
	}

	/**
	 * Check if the email is available to register
	 *
	 * @param string $emailToCheck Email to check
	 * @return bool True if the email is not taken
	 */
	public function isEmailAvailable($emailToCheck) {
		try {
			$this->surveyUserMapper->findByEmail($emailToCheck);
		} catch (DoesNotExistException $e) {
			return true;
		} catch (MultipleObjectsReturnedException $e) {
			return false;
		}

		return false;
	}
}
