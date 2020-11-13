<?php

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

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class SurveyUserMapper extends QBMapper {

	/**
	 * SubmissionMapper constructor.
	 * @param IDBConnection $db
	 */
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'forms_surveyusers', SurveyUser::class);
	}

	/**
	 * @param int $id Load a user with a specific ID
	 * @return \OCP\AppFramework\Db\Entity
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function load(int $id) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id',
					$qb->createNamedParameter($id))
			);

		return $this->findEntity($qb);
	}

	/**
	 * Find a user by e-mail address. (Either deleted or active)
	 *
	 * @param string $email
	 * @return SurveyUser|\OCP\AppFramework\Db\Entity
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 */
	public function findByEmail(string $email)
	{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('email', $qb->createNamedParameter($email, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	/**
	 * Find a user by activation/reset code (Either deleted or active)
	 *
	 * @param string $email
	 * @return SurveyUser|\OCP\AppFramework\Db\Entity
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 */
	public function findByCode(string $code)
	{
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('confirmcode', $qb->createNamedParameter($code, IQueryBuilder::PARAM_STR))
			);

		return $this->findEntity($qb);
	}

	/**
	 * @return SurveyUser[]
	 */
	public function findAll($filter = '', $limit = 100, $offset = 0): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		if ($filter !== '' && $filter !== null)
			$qb->where($qb->expr()->iLike('realname', $qb->createNamedParameter("%$filter%")))
				->orWhere($qb->expr()->iLike('email', $qb->createNamedParameter("%$filter%")));

		$qb->orderBy('realname', 'ASC')
			->setFirstResult($offset)
			->setMaxResults($limit);

		return $this->findEntities($qb);
	}

	/**
	 * @return SurveyUser[] List for CSV export
	 */
	public function listAll(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
//		->expr()->eq('status', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT))
			->orderBy('realname', 'ASC');

		return $this->findEntities($qb);
	}
}
