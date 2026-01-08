<?php

namespace Celysium\Elasticsearch;

use Celysium\Elasticsearch\Traits\Builder;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class Connection
{
    use Builder;
    private static Client $client;

    public function __construct()
    {
        self::$client = self::getClient();
    }

    public static function getClient(): Client
	{
		$config = config('database.elasticsearch');

        if (!isset($config)) {
            throw new \LogicException('No elasticsearch config found!');
        }
		$hosts  = explode(',', $config['host'] ?? '');

		return ClientBuilder::create()
			->setHosts($hosts)
			->setBasicAuthentication($config['username'] ?? '', $config['password'] ?? '')
			->setSSLVerification($config['ssl_verification'] ?? false)
			->build();
	}

    public function __call(string $name, array $params)
    {
        return self::$client->$name(array_merge($this->params, $params));
    }

}
