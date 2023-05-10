<?php

namespace Artvys\Search\Cake\Engines\Compiled\SearchSources\Table;

use Artvys\Search\Engines\Compiled\SearchSources\Field\FieldSearchSource;
use Artvys\Search\Engines\Compiled\SearchSources\Field\ResultQueryBuilder;
use Artvys\Search\SearchResult;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;

/**
 * @template T as Entity
 */
abstract class TableSearchSource extends FieldSearchSource {
	abstract protected function table(): Table;

	protected function makeResultQueryBuilder(): ResultQueryBuilder {
		return new TableResultQueryBuilder(
			$this->buildNewTableQuery(),
			$this->makeResultMapper()
		);
	}

	protected function buildNewTableQuery(): Query {
		return $this->applyTableQueryScopes($this->newTableQuery());
	}

	protected function newTableQuery(): Query {
		return $this->table()->find();
	}

	protected function applyTableQueryScopes(Query $query): Query {
		return $query;
	}

	/**
	 * @return callable(T): SearchResult
	 */
	abstract protected function makeResultMapper(): callable;
}
