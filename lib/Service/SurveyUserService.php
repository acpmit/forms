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
use OCP\IL10N;
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

	/** @var IL10N */
	private $l10n;

	public const SURVEY_USER_SESSION_ID = 'FormsSurveyUserId';
	public const SURVEY_USER_DB_PREFIX = 'survey-user-';

	public function __construct(FormMapper $formMapper,
								SurveyUserMapper $surveyUserMapper,
								IL10N $l10n,
								ILogger $logger) {
		// We need the session if we want to use survey user login sessions
		if (session_status() == PHP_SESSION_NONE) session_start();
		$this->formMapper = $formMapper;
		$this->surveyUserMapper = $surveyUserMapper;
		$this->logger = $logger;
		$this->l10n = $l10n;
	}

	public function isSurveyUserLoggedIn() {
		$user = $this->getCurrentSurveyUserId();
		return $user !== null && ((int)$user) > 0;
	}

	public function logoutSurveyUser() {
		if (session_status() == PHP_SESSION_NONE) session_start();
		$_SESSION[self::SURVEY_USER_SESSION_ID] = null;
	}

	public function setCurrentSurveyUserId($userId) {
		if (session_status() == PHP_SESSION_NONE) session_start();
		// \OC::$server->getSession()->set(self::SURVEY_USER_SESSION_ID, $userId);
		$_SESSION[self::SURVEY_USER_SESSION_ID] = $userId;
	}

	public function getCurrentSurveyUserId() {
		if (session_status() == PHP_SESSION_NONE) session_start();
		// return \OC::$server->getSession()->get(self::SURVEY_USER_SESSION_ID);
		return $_SESSION[self::SURVEY_USER_SESSION_ID];
	}

	public function getCurrentSurveyUser() : ?SurveyUser {
		if (!$this->isSurveyUserLoggedIn())
			return null;

		try {
			$user = $this->surveyUserMapper->load(
				$this->getCurrentSurveyUserId());
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

	private const QUESTION_ID_ADDRESS = 2147483647;
	private const QUESTION_ID_BIRTHYEAR = 2147483646;
	private const QUESTION_ID_REALNAME = 2147483645;

	public function addAnswersForPersonalData(&$answersList, $submissionId) {
		$answers = [
			self::QUESTION_ID_ADDRESS => 'ADDR',
			self::QUESTION_ID_BIRTHYEAR => 'BY',
			self::QUESTION_ID_REALNAME => 'RN',
		];

		foreach ($answers as $key => $answer)
			$answersList[] = [
				'id' => $submissionId.'_'.$key,
				'submissionId' => $submissionId,
				'questionId' => $key,
				'text' => $answer
			];
	}

	public function addQuestionsForPersonalData(&$questions, $formId) {
		$newFields = [
			self::QUESTION_ID_ADDRESS => $this->l10n->t('Address'),
			self::QUESTION_ID_BIRTHYEAR => $this->l10n->t('Your birth year'),
			self::QUESTION_ID_REALNAME => $this->l10n->t('Real name'),
		];

		foreach ($questions as $question)
			$question['order'] += count($newFields);

		$order = 0;
		foreach ($newFields as $key => $newField) {
			array_unshift($questions, [
				'id' => (int)$key,
				'formId' => $formId,
				'order' => $order++,
				'type' => 'short',
				'mandatory' => false,
				'text' => $newField,
				'hideSummary' => true,
				'options' => []
			]);
		}

		return $questions;
	}
}
