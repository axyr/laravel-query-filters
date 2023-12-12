<?php

namespace Axyr\QueryFilters\Tests;

use Axyr\QueryFilters\Tests\Dummy\Filters;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mockery;
use Orchestra\Testbench\TestCase;

class QueryFiltersTest extends TestCase
{
    public function testItCreatesAFilterFromArray(): void
    {
        $filters = ['a' => 'b', 'c' => 'd'];

        $dummyFilters = Filters::fromArray($filters);

        $this->assertEquals($filters, $dummyFilters->getFilters());
    }

    public function testItCreatesAFilterFromRequest(): void
    {
        $filters = ['a' => 'b', 'c' => 'd'];
        $request = new Request($filters);

        $dummyFilters = Filters::fromRequest($request);

        $this->assertEquals($filters, $dummyFilters->getFilters());
    }

    public function testItReplacesTheFilters(): void
    {
        $filters = ['a' => 'b', 'c' => 'd'];
        $newFilters = ['e' => 'f', 'g' => 'h'];

        $dummyFilters = Filters::fromArray($filters);

        $dummyFilters->setFilters($newFilters);

        $this->assertEquals($newFilters, $dummyFilters->getFilters());
    }

    public function testItAddsFilters(): void
    {
        $filters = ['a' => 'b', 'c' => 'd'];
        $newFilters = ['e' => 'f', 'g' => 'h'];

        $dummyFilters = Filters::fromArray($filters);

        $dummyFilters->addFilters($newFilters);

        $this->assertEquals($filters + $newFilters, $dummyFilters->getFilters());
    }

    public function testItAddsAFilter(): void
    {
        $dummyFilters = Filters::fromArray();

        $dummyFilters->addFilter('a', 'b');

        $this->assertEquals(['a' => 'b'], $dummyFilters->getFilters());
    }

    public function testItGetsAFilterValue(): void
    {
        $filters = ['a' => 'b', 'c' => 'd'];

        $dummyFilters = Filters::fromArray($filters);

        $this->assertEquals('b', $dummyFilters->getFilterValue('a'));
        $this->assertEquals('d', $dummyFilters->getFilterValue('c'));
        $this->assertEquals(null, $dummyFilters->getFilterValue('e'));
    }

    /**
     * @dataProvider appliesTheFiltersToTheQueryDataProvider
     */
    public function testItAppliesTheFiltersToTheQuery(array $filters, string $expected): void
    {
        $pdo = Mockery::mock('PDO');
        $queryBuilder = new QueryBuilder(new MySqlConnection($pdo));
        $eloquentBuilder = new EloquentBuilder($queryBuilder);

        Filters::fromArray($filters)->applyToQuery($eloquentBuilder);

        $this->assertSame($expected, Str::replaceArray('?', $queryBuilder->getBindings(), $queryBuilder->toSql()));
    }

    public static function appliesTheFiltersToTheQueryDataProvider(): array
    {
        return [
            'no filters' => [
                'filters' => [],
                'expected' => 'select *',
            ],
            'one filter' => [
                'filters' => ['attribute-a' => 'a'],
                'expected' => 'select * where `a` = a',
            ],
            'all filters' => [
                'filters' => ['attribute-a' => 'a', 'attribute-b' => 'b'],
                'expected' => 'select * where `a` = a and `b` = b',
            ],
            'ignore filter without method' => [
                'filters' => ['attribute-a' => 'a', 'attribute-b' => 'b', 'attribute-x' => 'x'],
                'expected' => 'select * where `a` = a and `b` = b',
            ],
            'valid boolean value' => [
                'filters' => ['attribute-c' => true],
                'expected' => 'select * where `c` = 1',
            ],
            'invalid boolean value' => [
                'filters' => ['attribute-c' => 'x'],
                'expected' => 'select *',
            ],
            'valid integer value' => [
                'filters' => ['attribute-d' => 10],
                'expected' => 'select * where `d` = 10',
            ],
            'invalid integer value' => [
                'filters' => ['attribute-d' => 100],
                'expected' => 'select *',
            ],
            'without method argument' => [
                'filters' => ['attribute-e' => null],
                'expected' => 'select * where `e` = 1',
            ],
        ];
    }
}
