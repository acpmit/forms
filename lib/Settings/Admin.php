<?php
/**
 * @copyright Copyright (c) 2020 John Molakvoæ (skjnldsv) <skjnldsv@protonmail.com>
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

namespace OCA\Forms\Settings;

use OC\Group\Group;
use OCA\Forms\Controller\SettingsController;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;

class Admin implements \OCP\Settings\ISettings
{
	/** @var SettingsController */
	private $settingsController;

	/** @var IL10N */
	private $l;

	/** @var ILogger */
	private $logger;

	/** @var IGroupManager */
	private $groupManager;

	public function __construct(SettingsController $settingsController,
								IL10N $l,
								IGroupManager $groupManager,
								ILogger $logger) {
		$this->settingsController = $settingsController;
		$this->l = $l;
		$this->groupManager = $groupManager;
		$this->logger = $logger;
	}

    /**
     * @inheritDoc
     */
    public function getForm()
    {
		return $this->settingsController->getForm();
    }

    /**
     * @inheritDoc
     */
    public function getSection()
    {
        // TODO: Implement getSection() method.
		return 'forms';
    }

    /**
     * @inheritDoc
     */
    public function getPriority()
    {
        // TODO: Implement getPriority() method.
		return 90;
    }
}
