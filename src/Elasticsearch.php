<?php

namespace Celysium\Elasticsearch;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Elastic\Elasticsearch\Client;
use Celysium\Elasticsearch\Traits\Attribute;
use Celysium\Elasticsearch\Traits\Builder;

class Elasticsearch
{
    use Builder, Attribute;

    private Client $client;

    public function __construct(array $attributes = [])
    {
        $this->client = Connection::getClient();
        $this->fill($attributes);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function count(): int
    {
        $params = $this->getParams();
        $result = $this->client->count($params);
        return (int) $result['count'];
    }

    public function search(): array
    {
        $params = $this->getParams();
        $result = $this->client->search($params);
        if (isset($this->params['body']['aggs'])) {
            return $result['aggregations'];
        } else {
            return $this->response($result);
        }
    }

    public function pagination($size = 15, $page = 1, array $options = []): LengthAwarePaginator
    {
        $this->size($size);

        $this->params['from'] = ($page - 1) * $size;

        $params = $this->getParams();
        $result = $this->client->search($params);

        $total  = $result['hits']['total']['value'];
        $result = $this->response($result);

        return new LengthAwarePaginator($result, $total, $size, $page, $options);
    }

    private function response($result): array
    {
        $data = [];
        foreach ($result['hits']['hits'] as $index => $hit) {
            $data[$index] = $hit['_source'];
            $data[$index]['id'] = $hit['_id'];
        }

        return $data;
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

            return array_merge($response['_source'], [$response['_id']]);
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
        $this->throwMissingAttributes($attributes);
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
        $this->throwUnknownFields($attributes);
        $response = $this->client->update([
            'index' => $this->index,
            'id'    => $id,
            'body'  => $attributes
        ]);

        $attributes['id'] = $response['_id'];

        return $attributes;
    }

    public function save(): array
    {
        if(isset($this->attributes['id'])) {
            return $this->update($this->attributes['id'], $this->attributes);
        }
        return $this->create($this->attributes);
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
