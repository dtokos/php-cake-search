<?php

namespace Tests\Stubs\Engines\Compiled\SearchSources\Table;

use Artvys\Search\Cake\Engines\Compiled\SearchSources\Table\TableResultQueryBuilder;
use Cake\ORM\Query;

class TableResultQueryBuilderStub extends TableResultQueryBuilder {
	public function buildQuery(int $limit): Query {
		return $this->query
			->where($this->groupedExpressions())
			->limit($limit);
	}
}
