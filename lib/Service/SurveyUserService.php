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
use OCA\Forms\Helper\RandomHelper;
use OCA\Forms\Service\Exceptions\EmailExistsException;
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
	public const SURVEY_MANUAL_DB_PREFIX = 'manual:';

	public const SURVEY_USER_STATUS_ACTIVE = 0;
	public const SURVEY_USER_STATUS_BANNED = 1;
	public const SURVEY_USER_STATUS_DATA_RETRACTED = 2;

	private const QUESTION_ID_REALNAME = 2147483645;
	private const QUESTION_ID_ADDRESS = 2147483644;
	private const QUESTION_ID_PHONE = 2147483643;
	private const QUESTION_ID_EMAIL = 2147483642;

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

	/**
	 * @return bool True if there is a logged in survey user session
	 */
	public function isSurveyUserLoggedIn() {
		$user = $this->getCurrentSurveyUserId();
		return $user !== null && ((int)$user) > 0;
	}

	/**
	 * Log out survey user session
	 */
	public function logoutSurveyUser() {
		if (session_status() == PHP_SESSION_NONE) session_start();
		$_SESSION[self::SURVEY_USER_SESSION_ID] = null;
	}

	/**
	 * @param $userId int Set the current logged in survey user id
	 */
	public function setCurrentSurveyUserId($userId) {
		if (session_status() == PHP_SESSION_NONE) session_start();
		// \OC::$server->getSession()->set(self::SURVEY_USER_SESSION_ID, $userId);
		$_SESSION[self::SURVEY_USER_SESSION_ID] = $userId;
	}

	/**
	 * @return mixed Returns the current logged in surevy user id
	 */
	public function getCurrentSurveyUserId() {
		if (session_status() == PHP_SESSION_NONE) session_start();
		// return \OC::$server->getSession()->get(self::SURVEY_USER_SESSION_ID);
		return $_SESSION[self::SURVEY_USER_SESSION_ID];
	}

	/**
	 * @return SurveyUser|null Returns the current logged in survey user
	 */
	public function getCurrentSurveyUser() : ?SurveyUser {
		if (!$this->isSurveyUserLoggedIn())
			return null;

		return $this->getSurveyUser($this->getCurrentSurveyUserId());
	}

	/**
	 * Retrieves a survey user with error handling
	 *
	 * @param $userId int Get this survey user
	 * @return SurveyUser|null The user or null if there was an error
	 */
	public function getSurveyUser(int $userId) : ?SurveyUser {
		try {
			/** @var SurveyUser $user */
			$user = $this->surveyUserMapper->load($userId);
			return $user;
		} catch (IMapperException $e) {
			$this->logger->error('Error reading survey user: '.$userId, [$e]);
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
			// Not an error
			return true;
		} catch (MultipleObjectsReturnedException $e) {
			// Not an error
			return false;
		}

		return false;
	}

	/**
	 * Creates a hash from the e-mail address that will be used to anonymize
	 * the user upon delete
	 *
	 * @param $email string Email to be hashed
	 */
	public static function getEmailHash($email) {
		return hash('sha256', $email.'-deleted-forms-survey-user');
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
			// Not an error, the email was not registered in plain text. We
			// check for e-mail hashes for deleted users
			try {
				$this->surveyUserMapper->findByEmail(self::getEmailHash($emailToCheck));
			} catch (DoesNotExistException $e) {
				// Not an error, there were no deleted email with this hash
				return true;
			} catch (MultipleObjectsReturnedException $e) {
				// Not an error
				return false;
			}
		} catch (MultipleObjectsReturnedException $e) {
			// Not an error
			return false;
		}

		return false;
	}

	/**
	 * Adds answers to the "virtual" user personal data questions. These
	 * answers will be filled from the registered user database.
	 *
	 * @param $answersList array of the answers for a submission
	 * @param $submission array submission "object"
	 * @return array the $answersList parameter for easier syntax
	 */
	public function addAnswersForPersonalData(&$answersList, $submission) {
		$userId = $submission['userId'];
		if (substr($userId, 0, strlen(self::SURVEY_USER_DB_PREFIX)) !==
			self::SURVEY_USER_DB_PREFIX)
			// This is not a survey user submission, we can't fill in the
			// personal data answers
			return $answersList;

		$userId = (int)substr($userId, strlen(self::SURVEY_USER_DB_PREFIX));
		if ($userId === 0) return $answersList;

		try {
			/** @var SurveyUser $user */
			$user = $this->surveyUserMapper->load($userId);
			$answers = [
				self::QUESTION_ID_ADDRESS => $user->getAddress(),
				self::QUESTION_ID_PHONE => $user->getPhone(),
				self::QUESTION_ID_REALNAME => $user->getRealname(),
				self::QUESTION_ID_EMAIL => $user->getEmail(),
			];
		} catch (IMapperException $e) {
			$retracted = $this->l10n->t('Data not found or retracted');
			$answers = [
				self::QUESTION_ID_ADDRESS => $retracted,
				self::QUESTION_ID_EMAIL => $retracted,
				self::QUESTION_ID_PHONE => $retracted,
				self::QUESTION_ID_REALNAME => $retracted,
			];
		}

		foreach ($answers as $key => $answer)
			$answersList[] = [
				'id' => $submission['id'].'_'.$key,
				'submissionId' => $submission['id'],
				'questionId' => $key,
				'text' => $answer
			];

		return $answersList;
	}

	/**
	 * Adds the "virtual" questions for the registated user's personal data
	 * (address, real name, date of birth) so it can be filled in when the user
	 * has access to these data
	 *
	 * @param $questions array of Question objects
	 * @param $formId int ID for the form
	 * @return array the $questions parameter for easier syntax
	 */
	public function addQuestionsForPersonalData(&$questions, $formId) {
		$newFields = [
			self::QUESTION_ID_EMAIL => $this->l10n->t('E-mail'),
			self::QUESTION_ID_PHONE => $this->l10n->t('Phone number'),
			self::QUESTION_ID_ADDRESS => $this->l10n->t('Address'),
			self::QUESTION_ID_REALNAME => $this->l10n->t('Real name'),
		];

		foreach ($questions as $question) {
			$question['order'] += count($newFields);
			if ($question['id'] === self::QUESTION_ID_EMAIL)
				return $questions;
		}

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

	public function isManualSurveySubmission(array $submission) {
		$userId = $submission['userId'];
		if (substr($userId, 0, strlen(self::SURVEY_USER_DB_PREFIX)) !== self::SURVEY_USER_DB_PREFIX)
			return false;

		$userId = (int)substr($userId, strlen(self::SURVEY_USER_DB_PREFIX));
		if ($userId === 0) return false;

		$user = $this->getSurveyUser($userId);
		$email = $user->getEmail();
		return (substr($email, 0, strlen(self::SURVEY_MANUAL_DB_PREFIX)) === self::SURVEY_MANUAL_DB_PREFIX);
	}

	/**
	 * The nextcloud user enters a paper based survey that contain answers
	 * for personal details. We create a survey user based on these answers
	 *
	 * @param array $answers
	 * @return int The survey user ID
	 * @throws EmailExistsException If the email was registered manually already
	 */
	public function getSurveyUserForManualSubmission(array $answers)
	{
		$email = self::checkAnswer($answers, self::QUESTION_ID_EMAIL);
		if ($email != '-') {
			// Check if there was a submission with this email
			$email = self::SURVEY_MANUAL_DB_PREFIX . $email;
			try {
				// TODO FORMSTODO check for email without prefix
				// TODO FORMSTODO check for submission?
				$user = $this->surveyUserMapper->findByEmail($email);
				throw new EmailExistsException();
			} catch (DoesNotExistException $e) {
				// We should not find a result
			}
		} else {
			$email = self::SURVEY_MANUAL_DB_PREFIX.RandomHelper::randomStr(20);
		}

		try {
			$surveyUser = new SurveyUser();
			$surveyUser->setEmail($email);
			$surveyUser->setRealname(self::checkAnswer($answers, self::QUESTION_ID_REALNAME));
			$surveyUser->setAddress(self::checkAnswer($answers, self::QUESTION_ID_ADDRESS));
			$surveyUser->setPhone(self::checkAnswer($answers, self::QUESTION_ID_PHONE));
			$surveyUser->setPasswordhash(password_hash(RandomHelper::randomStr(50), PASSWORD_ARGON2I));
			$surveyUser = $this->surveyUserMapper->insert($surveyUser);
			return $surveyUser->getId();
		} catch (IMapperException $e) {
			$this->logger->error('Error creating survey user', [$e]);
			// TODO TODOFORMS throw
			return  null;
		}
	}

	private static function checkAnswer($array, $index) {
		return (isset($array[$index]) && is_array($array[$index]) && count($array[$index]) >= 1)
			? $array[$index][0]
			: '-';
	}

	/**
	 * Returns true, if this is a virtual question to hold personal data
	 * during manual input
	 *
	 * @param $questionId int Question id to check
	 * @return bool True if it's a virtual question
	 */
	public static function isPersonalDataQuestion(int $questionId) {
		return $questionId === self::QUESTION_ID_PHONE ||
			$questionId === self::QUESTION_ID_EMAIL ||
			$questionId === self::QUESTION_ID_ADDRESS ||
			$questionId === self::QUESTION_ID_REALNAME;
	}
}
