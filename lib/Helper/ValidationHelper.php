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

namespace OCA\Forms\Helper;

class ValidationHelper
{
	/**
	 * Filter input for unicode alphanumeric characters
	 *
	 * @param string $input String to filter
	 * @param int $len Maximum length
	 * @return false|string Filtered string or false if there was no result
	 */
	public static function filterAlphaNumericUnicde($input,
													$len = 200)
	{
		return substr(preg_replace("/[^[:alnum:][:space:]]/u", '',
			$input), 0, $len);
	}

	/**
	 * Filter input for phone numbers
	 *
	 * @param string $input String to filter
	 * @param int $len Maximum length
	 * @return false|string Filtered string or false if there was no result
	 */
	public static function filterPhoneNumber($input,
													$len = 200)
	{
		return trim(substr(preg_replace('/[^\d^\s^+^\-]/', '',
			$input), 0, $len));
	}
}
