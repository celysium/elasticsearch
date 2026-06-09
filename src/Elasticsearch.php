<?php

namespace Celysium\Elasticsearch;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Elastic\Elasticsearch\Client;
use Celysium\Elasticsearch\Traits\Builder;
use Illuminate\Support\Collection;

class Elasticsearch
{
    use Builder;

    private static Client $client;

    public function __construct(array $attributes = [])
    {
        self::connection();
    }

    public static function connection(): Client
    {
        return self::$client = Connection::getClient();
    }

    public function count(): int
    {
        $params = $this->getParams();
        $response = self::$client->count($params);
        return (int) $response['count'];
    }

    public function search(): Collection
    {
        $params = $this->getParams();
        $response = self::$client->search($params);
        if (isset($this->params['body']['aggs'])) {
            return new Collection($response['aggregations']);
        } else {
            return $this->response($response);
        }
    }

    public function pagination($size = 15, $page = 1, array $options = []): LengthAwarePaginator
    {
        $this->size($size);

        $this->params['from'] = ($page - 1) * $size;

        $params = $this->getParams();
        $response = self::$client->search($params);

        $total  = $response['hits']['total']['value'];
        $response = $this->response($response);

        return new LengthAwarePaginator($response, $total, $size, $page, $options);
    }

    private function response($response): Collection
    {
        $data = [];
        foreach ($response['hits']['hits'] as $hit) {
            $data[] = array_merge(['id' => $hit['_id']],$hit['_source']);
        }
        return new Collection($data);
    }

    public function find(string $id, array $source = ['*']): ?Collection
    {
        $params = [
            'index' => $this->index,
            'id' => $id
        ];
        if($source != ['*']) {
            $params['_source'] = $source;
        }
        try {
            $response = self::$client->get($params);

            return new Collection(array_merge($response['_source'], ['id' => $id]));
        }
        catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return null;
            } else {
                throw $e;
            }
        }
    }

    public function create(array $attributes): Collection
    {
        $params = [
            'index' => $this->index,
            'body'  => $attributes
        ];
        if(isset($attributes['id'])) {
            $params['id'] = $attributes['id'];
        }
        $response = self::$client->index($params);

        $attributes['id'] = $response['_id'];

        return new Collection($attributes);
    }

    public function update(string $id, array $attributes): Collection
    {
        $response = self::$client->update([
            'index' => $this->index,
            'id'    => $id,
            'body'  => $attributes
        ]);

        $attributes['id'] = $response['_id'];

        return new Collection($attributes);
    }

    public function save(): Collection
    {
        if(isset($this->attributes['id'])) {
            return $this->update($this->attributes['id'], $this->attributes);
        }
        return $this->create($this->attributes);
    }

    public function delete(string $id): string
    {
        $response = self::$client->delete([
            'index' => $this->index,
            'id'    => $id,
        ]);

        return $response['_id'];
    }
}
