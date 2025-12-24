<?php

namespace Celysium\Elasticsearch\Exceptions;

use Exception;

class MissingFieldException extends Exception
{
    /**
     * Create a new missing attribute exception instance.
     *
     * @param  object  $model
     * @param  string  $key
     */
    public function __construct($model, $key)
    {
        parent::__construct(sprintf(
            'The attribute [%s] either does not exist or was not retrieved for model [%s].',
            $key, get_class($model)
        ));
    }
}