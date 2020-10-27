<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2020 Jonas Rittershofer <jotoeri@users.noreply.github.com>
 *
 * @author Jonas Rittershofer <jotoeri@users.noreply.github.com>
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

namespace OCA\Forms\Migration;

use Closure;
use Doctrine\DBAL\Types\Type;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Class Version020005Date20201019161000 Add user table for the survey users
 * @package OCA\Forms\Migration
 */
class Version020005Date20201019161000 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('forms_surveyusers')) {
			$table = $schema->createTable('forms_surveyusers');
			$table->addColumn('id', TYPE::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 11,
				'unsigned' => true
			]);
			$table->addColumn('passwordhash', TYPE::STRING, [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('realname', TYPE::STRING, [
				'notnull' => false,
				'length' => 255
			]);
			$table->addColumn('isdeleted', TYPE::BOOLEAN, [
				'notnull' => true,
				'length' => 1,
				'default' => 0
			]);
			$table->addColumn('address', TYPE::TEXT, [
				'notnull' => false
			]);
			$table->addColumn('email', TYPE::STRING, [
				'notnull' => true,
				'length' => 200
			]);
			$table->addColumn('confirmcode', TYPE::STRING, [
				'notnull' => false,
				'length' => 200
			]);
			$table->addColumn('bornyear', TYPE::INTEGER, [
				'notnull' => false
			]);

			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['email', 'isdeleted'], 'forms_su_email');
			$table->addIndex(['confirmcode'], 'forms_su_confirmcode');
		}

		return $schema;
	}
}
