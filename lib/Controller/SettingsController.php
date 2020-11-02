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

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUserManager;

class SettingsController extends Controller
{
	/** @var IUserManager */
	private $userManager;

	/** @var IGroupManager */
	protected $groupManager;

	/** @var IConfig */
	protected $config;

	/** @var ILogger */
	private $logger;

	public const FORMS_ARRAY_SEPARATOR =
		"\n";
	public const CONFIG_BOOL_TRUE =
		'true';
	public const CONFIG_BOOL_FALSE =
		'false';

	public const CONFIG_CREATE_FORMS_GROUPS =
		'FormsCreateAllowed';
	public const CONFIG_VIEW_RESULTS_FORMS_GROUPS =
		'FormsViewResults';

	public function __construct($AppName,
								IRequest $request,
								ILogger $logger,
								IUserManager $userManager,
								IGroupManager $groupManager,
								IConfig $config,
								$UserId) {
		parent::__construct($AppName, $request);
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->logger = $logger;
		$this->userId = $UserId;
		$this->config = $config;
	}

	/**
	 * Admin only, this method handles the settings post route
	 */
	public function postSettings() {
		// TODO SAVE
		return $this->getForm();
	}

	/**
	 * Display the settings page
	 */
	public function getForm()
	{
		$createGroupList = [];
		$viewGroupList = [];

		$configViewGroups =
			$this->getFormsViewResultsGroups();
		$configCreateGroups =
			$this->getFormsCreatorGroups();

		foreach ($this->groupManager->search('') as $group) {
			$id = $group->getGID();
			$viewGroupList[] = [
				'name' => $group->getDisplayName(),
				'id' => $group->getGID(),
				'selected' => in_array($id, $configViewGroups)
			];
			$createGroupList[] = [
				'name' => $group->getDisplayName(),
				'id' => $group->getGID(),
				'selected' => in_array($id, $configCreateGroups)
			];
		}

		$data = [
			'createGroups' => $createGroupList,
			'viewGroups' => $viewGroupList
		];

		return new TemplateResponse(
			'forms',
			'settings',
			$data);
	}

	/**
	 * Retrieve a value from the NC app settings
	 *
	 * @param string $valName Name of a setting entry
	 * @return mixed Setting value retrieved
	 */
	private function getVal($valName) {
		return ($this->config->getAppValue($this->appName, $valName));
	}

	/**
	 * Retrieve a bool value from the NC app settings
	 *
	 * @param string $valName Name of a setting entry
	 * @return bool Setting value retrieved
	 */
	private function getBoolVal($valName) {
		return ($this->config->getAppValue($this->appName, $valName))
			=== self::CONFIG_BOOL_TRUE;
	}

	/**
	 * Store a value in the NC app settings
	 *
	 * @param string $valName Name of a setting entry
	 * @param mixed $value Item to store
	 */
	private function setVal($valName, $value) {
		$this->config->setAppValue($this->appName, $valName, $value);
	}

	/**
	 * Store a bool value in the NC app settings
	 *
	 * @param string $valName Name of a setting entry
	 * @param bool $value Item to store
	 */
	private function setBoolVal($valName, $value) {
		$this->config->setAppValue($this->appName, $valName, $value ?
			self::CONFIG_BOOL_TRUE : self::CONFIG_BOOL_FALSE);
	}

	/**
	 * General setting to array converter, retrieves an array form the NC app
	 * settings
	 *
	 * @param string $valName Name of a setting entry
	 * @return array Setting value retrieved
	 */
	private function getArray($valName) {
		$val = $this->getVal($valName);
		// We don't want to go rounds with the explode and filter if it will be
		// a completely empty array
		if ($val === null || $val === '') return [];

		$arr = (explode(self::FORMS_ARRAY_SEPARATOR,
			$val));

		if ($arr === null || !is_array($arr))
			return [];
		else
			return $arr;
	}

	/**
	 * General array to setting converter, this one stores an array in the NC
	 * app settings
	 *
	 * @param string $valName Name of a setting entry
	 * @param array $array array to save in the settings
	 */
	private function setArray($valName, $array) {
		$this->setVal($valName, implode(self::FORMS_ARRAY_SEPARATOR,
			$array));
	}

	/**
	 * @return array List of the groups allowed to create forms
	 */
	public function getFormsCreatorGroups()
	{
		return $this->getArray(self::CONFIG_CREATE_FORMS_GROUPS);
	}

	/**
	 * @return array List of the groups allowed to view forms results
	 */
	public function getFormsViewResultsGroups()
	{
		return $this->getArray(self::CONFIG_VIEW_RESULTS_FORMS_GROUPS);
	}
}
