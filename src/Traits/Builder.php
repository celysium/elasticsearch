<?php

namespace Celysium\Elasticsearch\Traits;

use Illuminate\Support\Arr;

trait Builder
{
    protected string $index;

    protected bool $trackTotalHits = true;

    private array $params = [];

    public static function query(): static
    {
        return new static();
    }

    public function getParams(): array
    {
        $this->params['track_total_hits'] = $this->trackTotalHits;
        return $this->params;
    }

    public function toJson(): string
    {
        $params = $this->params;
        $params = array_merge($params, $params['body']);
        unset($params['body']);
        unset($params['index']);
        return json_encode($params);
    }

    public function index(string $name): static
    {
        $this->params['index'] = $name;
        $this->index = $name;
        return $this;
    }

    public function param(string $key, mixed $value): static
    {
        Arr::set($this->params, $key, $value);
        return $this;
    }

    public function aggregation(array $aggregations): static
    {
        $this->params['body']['aggs'] = array_merge($this->params['aggs'], $aggregations);
        return $this;
    }

    public function raw(array $params): static
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function sort(string $column, string $direction = 'asc', bool $isDate = false): static
    {
        $this->params['sort'] = [
            $column => [
                'order' => $direction,
            ],

        ];
        if ($isDate) {
            $this->params['sort'][$column]['format']       = 'strict_date_optional_time_nanos';
            $this->params['sort'][$column]['numeric_type'] = 'date_nanos';
        }
        return $this;
    }

    public function sortDesc(string $column, bool $isDate = false): static
    {
       return $this->sort($column, 'desc', $isDate);
    }

    public function size($size = 0) : static
    {
        $this->params['size'] = $size;
        return $this;
    }

    public function whereRange(string $field, $from = null, $to = null, $firstOperator = 'gte', $secondOperator = 'lte'): static
    {
        if (!$from && !$to) {
            return $this;
        }

        $ranges = [
            $field => [],
        ];

        if ($from) {
            $ranges[$field][$firstOperator] = $from;
        }
        if ($to) {
            $ranges[$field][$secondOperator] = $to;
        }
        $this->params['body']['query']['bool']['must'][] = [
            'range' => [
                $ranges
            ]
        ];

        return $this;
    }

    public function where(string $field, mixed $value): static
    {
        $this->params['body']['query']['bool']['must'][] = [
            "term" => [
                $field => [
                    "value" => $value
                ],
            ],
        ];

        return $this;
    }

    public function whereNot(string $field, mixed $value): static
    {
        $this->params['body']['query']['bool']['must_not'][] = [
            "term" => [
                $field => [
                    "value" => $value
                ],
            ],
        ];

        return $this;
    }

    public function whereIn(string $field, array $values): static
    {
        $this->params['body']['query']['bool']['must'][] = [
            "terms" => [
                $field => $values,
            ],
        ];

        return $this;
    }

    public function whereNotIn(string $field, array $values): static
    {
        $this->params['body']['query']['bool']['must_not'][] = [
            "terms" => [
                $field => $values,
            ],
        ];

        return $this;
    }
}
