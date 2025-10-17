<?php

namespace Celysium\Elasticsearch;

use Illuminate\Pagination\LengthAwarePaginator;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\HttpClientException;
use Celysium\Elasticsearch\Traits\QueryBuilder;

class Elasticsearch
{
    use QueryBuilder;

    protected null $request = null;
    private Client $client;

    public function __construct()
    {
        $this->client = Connection::getClient();
        $this->request = app('request');
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
        if (isset($this->params['aggs'])) {
            return $this->responseAggregations($result);
        } else {
            return $this->response($result);
        }
    }

    public function pagination($size = 15): LengthAwarePaginator
    {
        $this->size($size);

        $page = $this->request->get('page', 1);
        $this->setPaginationParams($page);

        $params = $this->getParams();
        $result = $this->client->search($params);

        $total  = $this->total($result);
        $result = $this->response($result);
        $url    = $url ?? $this->request->url();

        return new LengthAwarePaginator($result, $total, $size, $page, ['path' => $url]);
    }
    public function setPaginationParams(int $page = 1): void
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
}
