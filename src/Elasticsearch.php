<?php

namespace Celysium\Elasticsearch;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\HttpClientException;
use Celysium\Elasticsearch\Traits\QueryBuilder;

class Elasticsearch
{
    use QueryBuilder;

    private Client $client;

    public function __construct()
    {
        $this->client = Connection::getClient();
    }

    public static function query(): self
    {
        return new self();
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function count(): int
    {
        $params = $this->getParams();
        $result = $this->client->count($params);
        if (isset($result['count'])) {
            return (int) $result['count'];
        }
        throw new HttpClientException();
    }

    public function search(): array
    {
        $params = $this->getParams();
        $result = $this->client->search($params);
        if (isset($this->params['body']['aggs'])) {
            return $this->responseAggregations($result);
        } else {
            return $this->response($result);
        }
    }

    public function pagination($size = 15, $page = 1, array $options = []): LengthAwarePaginator
    {
        $this->size($size);

        $this->setPaginationParams($page);

        $params = $this->getParams();
        $result = $this->client->search($params);

        $total  = $this->total($result);
        $result = $this->response($result);

        return new LengthAwarePaginator($result, $total, $size, $page, $options);
    }
    private function setPaginationParams(int $page): void
    {
        $this->params['from'] = ($page - 1) * $this->params['size'];
    }

    private function response($result): array
    {
        $data = [];
        foreach ($result['hits']['hits'] as $hit) {
            $data[] = $hit['_source'];
        }

        return $data;
    }

    private function responseAggregations($result): array
    {
        return $result['aggregations'];
    }

    private function total($result): array
    {
        return $result['hits']['total']['value'];
    }

    public function find(string $id, array $source = ['*'])
    {
        $params = [
            'index' => $this->index,
            'id' => $id
        ];
        if($source != ['*']) {
            $params['_source'] = $source;
        }
        try {
            $response = $this->client->get($params);

            return $response['_source'];
        }
        catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return null;
            } else {
                throw $e;
            }
        }
    }

    public function create(array $attributes): array
    {
        $params = [
            'index' => $this->index,
            'body'  => $attributes
        ];
        if(isset($attributes['id'])) {
            $params['id'] = $attributes['id'];
        }
        $response = $this->client->index($params);

        $attributes['id'] = $response['_id'];

        return $attributes;
    }

    public function update(string $id, array $attributes): array
    {
        $response = $this->client->update([
            'index' => $this->index,
            'id'    => $id,
            'body'  => $attributes
        ]);

        $attributes['id'] = $response['_id'];

        return $attributes;
    }

    public function delete(string $id): string
    {
        $response = $this->client->delete([
            'index' => $this->index,
            'id'    => $id,
        ]);

        return $response['_id'];
    }
}
