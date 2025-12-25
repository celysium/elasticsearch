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
        $body = $this->params['body'] ?? [];
        unset($this->params['body']);
        unset($this->params['index']);
        return json_encode(array_merge($this->params, $body));
    }

    public function index(string $name): self
    {
        $this->index = $name;
        $this->params['index'] = $name;
        return $this;
    }

    public function param(string $key, mixed $value): self
    {
        Arr::set($this->params, $key, $value);
        return $this;
    }

    public function aggregation(array $aggregations): self
    {
        $this->params['body']['aggs'] = array_merge($this->params['aggs'], $aggregations);
        return $this;
    }

    public function raw(array $params): self
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function sort(string $column, string $direction = 'asc', bool $isDate = false): self
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

    public function sortDesc(string $column, bool $isDate = false): self
    {
       return $this->sort($column, 'desc', $isDate);
    }

    public function size($size = 0) : self
    {
        $this->params['size'] = $size;
        return $this;
    }

    public function whereRange(string $field, $from = null, $to = null, $firstOperator = 'gte', $secondOperator = 'lte'): self
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

    public function where(string $field, mixed $value): self
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

    public function whereNot(string $field, mixed $value): self
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

    public function whereIn(string $field, array $values): self
    {
        $this->params['body']['query']['bool']['must'][] = [
            "terms" => [
                $field => $values,
            ],
        ];

        return $this;
    }

    public function whereNotIn(string $field, array $values): self
    {
        $this->params['body']['query']['bool']['must_not'][] = [
            "terms" => [
                $field => $values,
            ],
        ];

        return $this;
    }
}
