<?php

namespace Celysium\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class Connection
{
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

}
