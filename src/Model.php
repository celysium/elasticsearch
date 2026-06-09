<?php

namespace Celysium\Elasticsearch;

use Celysium\Elasticsearch\Traits\Attribute;

class Model extends Elasticsearch
{
    use Attribute;

    public function __construct(array $attributes = [])
    {
        self::connection();

        $this->fill($attributes);
    }
}