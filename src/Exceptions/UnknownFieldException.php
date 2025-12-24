<?php

namespace Celysium\Elasticsearch\Exceptions;

use Exception;

class UnknownFieldException extends Exception
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
            'The field [%s] either does not exist in field list model [%s].',
            $key, get_class($model)
        ));
    }
}