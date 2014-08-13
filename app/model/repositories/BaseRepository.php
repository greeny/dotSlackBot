<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\SlackBot\Model;

use LeanMapper\Repository;
use Nette\Utils\Paginator;

class BaseRepository extends Repository {
	public function find($id)
	{
		$row = $this->connection->select('*')
			->from($this->getTable())
			->where('id = %i', $id)
			->fetch();
		return ($row === false ? NULL : $this->createEntity($row));
	}

	public function findAll()
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->fetchAll()
		);
	}

	public function findByPage(Paginator $paginator)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->limit($paginator->itemsPerPage)
				->offset($paginator->offset)
				->fetchAll()
		);
	}

	public function findOrderedByPage(Paginator $paginator, $orderBy)
	{
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->limit($paginator->itemsPerPage)
				->offset($paginator->offset)
				->orderBy($orderBy)
				->fetchAll()
		);
	}

	public function countAll()
	{
		return $this->connection->select('COUNT(*) AS count')
			->from($this->getTable())
			->fetch()
			->count;
	}
}
