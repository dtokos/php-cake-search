<?php

namespace Tests\Unit\Engines\Compiled\SearchSources\Table;

use Artvys\Search\Cake\Engines\Compiled\SearchSources\Table\TableResultQueryBuilder;
use Artvys\Search\Engines\Compiled\SearchSources\Field\ResultQueryBuilder;
use Artvys\Search\SearchResult;
use Cake\Database\Connection;
use Cake\Database\Schema\TableSchema;
use Cake\ORM\Query;
use Cake\ORM\Table;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\Engines\Compiled\SearchSources\Table\TableResultQueryBuilderStub;

class TableResultQueryBuilderTest extends TestCase {
	public function testEquals(): void {
		$this->assertQuery(
			'foo = bar LIMIT 1',
			fn(TableResultQueryBuilder $b) => $b->equals('foo', 'bar'),
			1
		);
	}

	public function testContains(): void {
		$this->assertQuery(
			'foo like %bar% LIMIT 2',
			fn(TableResultQueryBuilder $b) => $b->contains('foo', 'bar'),
			2
		);
	}

	public function testStartsWith(): void {
		$this->assertQuery(
			'foo like bar% LIMIT 3',
			fn(TableResultQueryBuilder $b) => $b->startsWith('foo', 'bar'),
			3
		);
	}

	public function testEndsWith(): void {
		$this->assertQuery(
			'foo like %bar LIMIT 4',
			fn(TableResultQueryBuilder $b) => $b->endsWith('foo', 'bar'),
			4
		);
	}

	public function testAnd(): void {
		$this->assertQuery(
			'(foo = 1 AND bar = 2) LIMIT 5',
			fn(TableResultQueryBuilder $b) => $b->and(fn(ResultQueryBuilder $a) => $a
				->equals('foo', '1')
				->equals('bar', '2')
			),
			5
		);
	}

	public function testOr(): void {
		$this->assertQuery(
			'(foo = 1 OR bar = 2) LIMIT 6',
			fn(TableResultQueryBuilder $b) => $b->or(fn(ResultQueryBuilder $o) => $o
				->equals('foo', '1')
				->equals('bar', '2')
			),
			6
		);
	}

	public function testExample1(): void {
		$this->assertQuery(
			'((foo = 1 OR bar like %2%) AND baz like 3% AND qux like %4) LIMIT 7',
			fn(TableResultQueryBuilder $b) => $b->and(fn(ResultQueryBuilder $a) => $a
				->or(fn(ResultQueryBuilder $o) => $o
					->equals('foo', '1')
					->contains('bar', '2')
				)
				->startsWith('baz', '3')
				->endsWith('qux', '4')
			),
			7
		);
	}

	public function testExample2(): void {
		$this->assertQuery(
			'((foo = 1 AND bar like %2%) OR baz like 3% OR qux like %4) LIMIT 8',
			fn(TableResultQueryBuilder $b) => $b->or(fn(ResultQueryBuilder $o) => $o
				->and(fn(ResultQueryBuilder $a) => $a
					->equals('foo', '1')
					->contains('bar', '2')
				)
				->startsWith('baz', '3')
				->endsWith('qux', '4')
			),
			8
		);
	}

	/**
	 * @param string $expected
	 * @param callable(TableResultQueryBuilder): TableResultQueryBuilder $buildingBlock
	 * @param int $limit
	 * @return void
	 */
	private function assertQuery(string $expected, callable $buildingBlock, int $limit): void {
		$builder = $this->makeNewBuilder();
		$buildingBlock($builder);

		$this->assertSame($expected, $this->toSQL($builder->buildQuery($limit)));
	}

	private function makeNewBuilder(): TableResultQueryBuilderStub {
		return new TableResultQueryBuilderStub(
			$this->makeQuery(),
			fn() => SearchResult::make('', '', ''),
		);
	}

	private function makeQuery(): Query {
		$connection = new Connection(['driver' => 'mysql']);
		$table = new Table([
			'table' => 'table',
			'connection' => $connection,
			'schema' => new TableSchema('foo'),
		]);

		return new Query($connection, $table);
	}

	private function toSQL(Query $query): string {
		$sql = preg_replace('/^.*?WHERE */', '', $query->sql()) ?? '';
		$placeholders = [];
		$values = [];

		foreach ($query->getValueBinder()->bindings() as $placeholder => $binding) {
			$placeholders[] = $placeholder;
			$values[] = $binding['value'];
		}

		return str_replace($placeholders, $values, $sql);
	}
}
