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

    public function __construct()
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

    public function search(array $source = ['*']): Collection
    {
        if($source != ['*']) {
            $params['_source'] = $source;
        }

        $params = $this->getParams();
        $response = self::$client->search($params);

        if (isset($this->params['body']['aggs'])) {
            return new Collection($response['aggregations']);
        } else {
            return $this->format($response);
        }
    }

    public function paginate($size = null, $page = 1, array $source = ['*']): LengthAwarePaginator
    {
        $this->size($size ?? 15);

        $page = $page > 0 ?: 1;

        $this->params['from'] = ($page - 1) * $size;

        if($source != ['*']) {
            $params['_source'] = $source;
        }

        $params = $this->getParams();
        $response = self::$client->search($params);

        $total  = $response['hits']['total']['value'];
        $response = $this->format($response);

        return new LengthAwarePaginator($response, $total, $size, $page);
    }

    private function format($response): Collection
    {
        $data = [];
        foreach ($response['hits']['hits'] as $hit) {
            $data[] = (object) $hit['_source'];
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
            return $this->format($response);
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
        return $this->format($response);
    }

    public function update(string $id, array $attributes): Collection
    {
        $response = self::$client->update([
            'index' => $this->index,
            'id'    => $id,
            'body'  => $attributes
        ]);

        return $this->format($response);
    }

    public function save(array $attributes): Collection
    {
        if(isset($attributes['id'])) {
            return $this->update($attributes['id'], $attributes);
        }
        return $this->create($attributes);
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
