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
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DownloadResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\Util;

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

	/** @var IL10N */
	private $l10n;

	/** @var string */
	private $userId;

	public const FORMS_ARRAY_SEPARATOR =
		"\n";
	public const CONFIG_BOOL_TRUE =
		'true';
	public const CONFIG_BOOL_FALSE =
		'false';

	public const CONFIG_ADVANCED_ACCESS_CONTROL =
		'FormsEnableAdvancedAccessControl';
	public const CONFIG_CREATE_FORMS_GROUPS =
		'FormsCreateAllowed';
	public const CONFIG_VIEW_RESULTS_FORMS_GROUPS =
		'FormsViewResults';
	public const CONFIG_VIEW_PERSONAL_DATA_FORMS_GROUPS =
		'FormsViewPersonalData';
	public const CONFIG_SURVEY_UI_LOGOIMAGE =
		'FormsSurveyLogoImage';
	public const CONFIG_ENABLE_ACCESS_ALL =
		'FormsAccessAll';
	public const CONFIG_URL_TOS =
		'FormsUrlTos';
	public const CONFIG_URL_PP =
		'FormsUrlPP';

	public function __construct($AppName,
								IRequest $request,
								ILogger $logger,
								IUserManager $userManager,
								IGroupManager $groupManager,
								IConfig $config,
								IL10N $l10n,
								$UserId) {
		parent::__construct($AppName, $request);
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->logger = $logger;
		$this->l10n = $l10n;
		$this->userId = $UserId;
		$this->config = $config;
	}

	/**
	 * @NoAdminRequired
	 *
	 * Read the access for the current user as set in the settings
	 */
	public function getAccess(): DataResponse {
		if ($this->isAccessControlEnabled()) {
			$result = [
				'canCreate' => $this->canCreateForms(),
				'canViewSurveyResults' => $this->canViewResults(),
			];
		} else {
			$result = [
				'canCreate' => true,
				'canViewSurveyResults' => true,
			];
		}

		return new DataResponse($result);
	}

	/**
	 * @return bool True, if the current user can view the results
	 */
	public function canViewResults() : bool {
		return $this->checkListForAccess($this->getFormsViewResultsGroups());
	}

	/**
	 * @return bool True, if the current user can create a form
	 */
	public function canCreateForms() : bool {
		return $this->checkListForAccess($this->getFormsCreatorGroups());
	}

	/**
	 * @return bool True, if the current user can view survey user personal data
	 */
	public function canViewPersonalData() : bool {
		return $this->checkListForAccess($this->getFormsViewPersonalDataGroups());
	}

	/**
	 * @param array $accessList List of group IDS allowed
	 * @return bool True if the user has a group that is allowed
	 */
	private function checkListForAccess($accessList) : bool {
		$userGroups = $this->groupManager->getUserGroups(
			$this->userManager->get($this->userId));

		foreach ($userGroups as $userGroup)
			if (in_array($userGroup->getGID(), $accessList))
				return true;

		return false;
	}

	/**
	 * Admin only, this method handles the settings post route
	 */
	public function postSettings() {
		$configViewGroups = [];
		$configCreateGroups = [];
		$configPersonalDataGroups = [];
		$error = [];

		foreach ($_POST as $key => $item) {
			$this->checkFieldForGroup($configViewGroups, 'view-', $key, $error);
			$this->checkFieldForGroup($configCreateGroups, 'create-', $key, $error);
			$this->checkFieldForGroup($configPersonalDataGroups, 'personal-', $key, $error);
		}

		$this->setFormsCreatorGroups($configCreateGroups);
		$this->setFormsViewResultsGroups($configViewGroups);
		$this->setFormsViewPersonalDataGroups($configPersonalDataGroups);
		$this->setIsAccessControlEnabled(
			isset($_POST['enable-access']) && $_POST['enable-access'] === 'yes'
		);
		$this->setIsAccessToAllEnabled(
			isset($_POST['allow-all']) && $_POST['allow-all'] === 'yes'
		);
		$this->setPrivacyPolicyUrl($_POST['policy-pp']);
		$this->setTermsOfServiceUrl($_POST['policy-tos']);

		if (isset($_POST['uploadLogoData']) && strlen($_POST['uploadLogoData']) > 0)
			$this->setSurveyUiLogo($_POST['uploadLogoData']);

		if (count($error) === 0)
			return new DataResponse('Ok', Http::STATUS_OK);
		else
			return new DataResponse(implode("\n", $error), Http::STATUS_BAD_REQUEST);
	}

	private function checkFieldForGroup(array &$groupArray,
										string $prefix,
										string $field,
										array &$errorArray) {
		if (substr($field, 0, strlen($prefix)) === $prefix) {
			$group = str_replace('_', ' ', substr($field, strlen($prefix)));
			if ($this->groupManager->groupExists($group))
				$groupArray[] = $group;
			else
				$errorArray[] = $this->l10n->t('Invalid group name: %s', [$group]);
		}
	}

	private static function getImageData($data) {
		$items = [];
		$matches = preg_match('/^(data:)(image\/[^;]+);base64,(.*)/', $data, $items);
		if ($matches <= 0)
			return false;

		if ($items[1] !== 'data:')
			return false;

		$source = base64_decode($items[3]);
		if (!$source)
			return false;

		return [
			'source' => $source,
			'mime' => $items[2]
		];
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @return DataDownloadResponse|DataResponse
	 */
	public function getLogoImage() {
		$data = self::getImageData($this->getSurveyUiLogo());
		if (!$data) return new DataResponse('', Http::STATUS_NO_CONTENT);

		return new DataDownloadResponse(
			$data['source'],
			'logo',
			$data['mime']
		);
	}

	/**
	 * Display the settings page
	 */
	public function getForm() {
		\OC_Util::addScript($this->appName, 'admin');
		\OC_Util::addStyle($this->appName, 'admin');

		$createGroupList = [];
		$viewGroupList = [];
		$personalDataGroupList = [];
		$configViewGroups = $this->getFormsViewResultsGroups();
		$configCreateGroups = $this->getFormsCreatorGroups();
		$configPersonalDataGroups = $this->getFormsViewPersonalDataGroups();

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
			$personalDataGroupList[] = [
				'name' => $group->getDisplayName(),
				'id' => $group->getGID(),
				'selected' => in_array($id, $configPersonalDataGroups)
			];
		}

		$data = [
			'policy-tos' => $this->getTermsOfServiceUrl(),
			'policy-pp' => $this->getPrivacyPolicyUrl(),
			'enableAll' => $this->isAccessToAllEnabled(),
			'createGroups' => $createGroupList,
			'viewGroups' => $viewGroupList,
			'personalDataGroups' => $personalDataGroupList,
			'enableAccess' => $this->isAccessControlEnabled(),
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

	/**
	 * @return array List of the groups allowed to view personal data
	 */
	public function getFormsViewPersonalDataGroups()
	{
		return $this->getArray(self::CONFIG_VIEW_PERSONAL_DATA_FORMS_GROUPS);
	}

	/**
	 * @param array $groupList List of the groups allowed to view personal data
	 */
	public function setFormsViewPersonalDataGroups($groupList)
	{
		return $this->setArray(self::CONFIG_VIEW_PERSONAL_DATA_FORMS_GROUPS, $groupList);
	}

	/**
	 * @param array $groupList List of the groups allowed to create forms
	 */
	public function setFormsCreatorGroups($groupList)
	{
		return $this->setArray(self::CONFIG_CREATE_FORMS_GROUPS, $groupList);
	}

	/**
	 * @param array $groupList List of the groups allowed to view forms results
	 */
	public function setFormsViewResultsGroups($groupList)
	{
		return $this->setArray(self::CONFIG_VIEW_RESULTS_FORMS_GROUPS, $groupList);
	}

	/**
	 * @return bool True if the group-based access control should be considered
	 */
	public function isAccessControlEnabled()
	{
		return $this->getBoolVal(self::CONFIG_ADVANCED_ACCESS_CONTROL);
	}

	/**
	 * @param bool $enabled True if the group-based access control should be
	 * considered
	 */
	public function setIsAccessControlEnabled($enabled)
	{
		return $this->setBoolVal(self::CONFIG_ADVANCED_ACCESS_CONTROL,
			$enabled);
	}

	/**
	 * @return string Base64 coded image to insert into the source
	 */
	public function getSurveyUiLogo()
	{
		return $this->getVal(self::CONFIG_SURVEY_UI_LOGOIMAGE);
	}

	/**
	 * @return bool If the page has an image to insert into the source
	 */
	public function hasSurveyUiLogo()
	{
		return strlen($this->getVal(self::CONFIG_SURVEY_UI_LOGOIMAGE))>0;
	}

	/**
	 * @param string $image Base64 coded image to insert into the source
	 * considered
	 */
	public function setSurveyUiLogo($image)
	{
		return $this->setVal(self::CONFIG_SURVEY_UI_LOGOIMAGE,
			$image);
	}

	/**
	 * @return bool True if everyone can access every form
	 */
	public function isAccessToAllEnabled()
	{
		return $this->getBoolVal(self::CONFIG_ENABLE_ACCESS_ALL);
	}

	/**
	 * @param bool $enabled True if everyone can access every form
	 */
	public function setIsAccessToAllEnabled($enabled)
	{
		return $this->setBoolVal(self::CONFIG_ENABLE_ACCESS_ALL,
			$enabled);
	}

	/**
	 * @return string Privacy policy URL
	 */
	public function getPrivacyPolicyUrl()
	{
		return $this->getVal(self::CONFIG_URL_PP);
	}

	/**
	 * @param string $url  Privacy policy URL
	 */
	public function setPrivacyPolicyUrl($url)
	{
		return $this->setVal(self::CONFIG_URL_PP,
			$url);
	}

	/**
	 * @return string Terms of service URL
	 */
	public function getTermsOfServiceUrl()
	{
		return $this->getVal(self::CONFIG_URL_TOS);
	}

	/**
	 * @param string $url Terms of service URL
	 */
	public function setTermsOfServiceUrl($url)
	{
		return $this->setVal(self::CONFIG_URL_TOS,
			$url);
	}
}
