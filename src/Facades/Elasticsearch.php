<?php

namespace Celysium\Elasticsearch\Facades;

use Illuminate\Pagination\LengthAwarePaginator;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Client getClient()
 * @method static int count()
 * @method static array search()
 * @method static LengthAwarePaginator pagination($size = 15)
 *
 * @method static array getParams()
 * @method static string toJson()
 *
 * @method static self index(string $name)
 * @method static self param(string $key, mixed $value)
 * @method static self aggregation(array $aggregations)
 * @method static self raw(array $params)
 * @method static self sort(string $column, string $direction = 'asc', bool $isDate = false)
 * @method static self sortDesc(string $column, bool $isDate = false)
 * @method static self size($size = 0)
 * @method static self whereRange(string $field, $from = null, $to = null, $firstOperator = 'gte', $secondOperator = 'lte')
 * @method static self where(string $field, array $value)
 * @method static self whereNot(string $field, array $value)
 * @method static self whereIn(string $field, array $values)
 * @method static self whereNotIn(string $field, array $values)
 */
class Elasticsearch extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'elasticsearch';
    }
}
