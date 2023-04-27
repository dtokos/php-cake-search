<?php

namespace Artvys\Search\Cake\Engines\Compiled\SearchSources\Table;

use Artvys\Search\Engines\Compiled\SearchSources\Field\ResultQueryBuilder;
use Artvys\Search\SearchResult;
use Cake\ORM\Query;

class TableResultQueryBuilder implements ResultQueryBuilder {
	protected readonly Query $query;
	/** @var callable(): SearchResult */
	protected $resultMapper;
	protected bool $groupUsingAnd = true;
	/** @var array<array-key, array<array-key, mixed>> */
	protected array $expressions = [];

	public function __construct(Query $query, callable $resultMapper) {
		$this->query = $query;
		$this->resultMapper = $resultMapper;
	}

	/**
	 * @inheritDoc
	 */
	public function and(callable $buildingBlock): static {
		$builder = $this->subBuilder(true);
		$buildingBlock($builder);
		$this->expressions[] = $builder->groupedExpressions();

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function or(callable $buildingBlock): static {
		$builder = $this->subBuilder(false);
		$buildingBlock($builder);
		$this->expressions[] = $builder->groupedExpressions();

		return $this;
	}

	public function equals(string $field, string $token): static {
		$this->expressions[] = [$field .' =' => $token];
		return $this;
	}

	public function contains(string $field, string $token): static {
		$this->expressions[] = [$field .' LIKE' => '%'. $token .'%'];
		return $this;
	}

	public function startsWith(string $field, string $token): static {
		$this->expressions[] = [$field .' LIKE' => $token .'%'];
		return $this;
	}

	public function endsWith(string $field, string $token): static {
		$this->expressions[] = [$field .' LIKE' => '%'. $token];
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function results(int $limit): array {
		return $this->query
			->where($this->groupedExpressions())
			->limit($limit)
			->all()
			->map($this->resultMapper)
			->toArray();
	}

	protected function subBuilder(bool $groupUsingAnd): TableResultQueryBuilder {
		$builder = new self($this->query, $this->resultMapper);
		$builder->groupUsingAnd = $groupUsingAnd;

		return $builder;
	}

	/**
	 * @return array<string, array<array-key, mixed>>
	 */
	protected function groupedExpressions(): array {
		$operator = $this->groupUsingAnd ? 'AND' : 'OR';
		return [$operator => $this->expressions];
	}
}
