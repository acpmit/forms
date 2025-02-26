<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2020 Jonas Rittershofer <jotoeri@users.noreply.github.com>
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

namespace OCA\Forms\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method integer getFormId()
 * @method void setFormId(integer $value)
 * @method string getPasswordhash()
 * @method void setPasswordhash(string $value)
 * @method string getRealname()
 * @method void setRealname(string $value)
 * @method bool getIsdeleted()
 * @method void setIsdeleted(bool $value)
 * @method string getAddress()
 * @method void setAddress(string $value)
 * @method string getEmail()
 * @method void setEmail(string $value)
 * @method string getConfirmcode()
 * @method void setConfirmcode(string $value)
 * @method string getPhone()
 * @method void setPhone(string $value)
 * @method integer getBornyear()
 * @method void setBornyear(integer $value)
 * @method integer getStatus()
 * @method void setStatus(integer $value)
 */
class SurveyUser extends Entity
{
	protected $passwordhash;
	protected $realname;
	protected $isdeleted;
	protected $address;
	protected $email;
	protected $confirmcode;
	protected $bornyear;
	protected $status;
	protected $phone;

	/**
	 * SurveyUser constructor.
	 */
	public function __construct()
	{
		$this->addType('realname', 'string');
		$this->addType('isdeleted', 'bool');
		$this->addType('address', 'string');
		$this->addType('email', 'string');
		$this->addType('confirmcode', 'string');
		$this->addType('bornyear', 'integer');
		$this->addType('passwordhash', 'string');
		$this->addType('status', 'integer');
		$this->addType('phone', 'string');
	}

	public function read(): array
	{
		return [
			'id' => $this->getId(),
			'passwordhash' => $this->getPasswordhash(),
			'realname' => $this->getRealname(),
			'isdeleted' => $this->getIsdeleted(),
			'address' => $this->getAddress(),
			'email' => $this->getEmail(),
			'confirmcode' => $this->getConfirmcode(),
			'bornyear' => $this->getBornyear(),
			'status' => $this->getStatus(),
			'phone' => $this->getPhone(),
		];
	}
}
