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

use Exception;

class RandomHelper
{
	/**
	 * Create (crypto) random alphanumeric lower and uppercase string with the
	 * given byte len with (PHP 7) random_int — Generates cryptographically
	 * secure pseudo-random integers
	 *
	 * @param int $length Length to generate the string for
	 * @return string Generated random string
	 * @throws Exception
	 */
	public static function randomStr($length)
	{
		$keyspace =
			'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$str = '';
		$max = mb_strlen($keyspace, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) $str .= $keyspace[random_int(0, $max)];
		return $str;
	}
}
