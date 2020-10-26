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
use OCA\Forms\Db\SurveyUserMapper;
use OCP\AppFramework\Db\DoesNotExistException;
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

	public function __construct(FormMapper $formMapper,
								SurveyUserMapper $surveyUserMapper,
								ILogger $logger) {
		$this->formMapper = $formMapper;
		$this->surveyUserMapper = $surveyUserMapper;
		$this->logger = $logger;
	}

	public function isSurveyUserLoggedIn() {
		return false;
	}

	public function getCurrentSurveyUser() {
		return false;
	}

	/**
	 * @param $loginToCheck
	 * @return bool
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function isUserNameAvailable($loginToCheck) {
		try {
			$this->surveyUserMapper->findByLogin($loginToCheck);
		} catch (DoesNotExistException $e) {
			return true;
		}

		return false;
	}
}
