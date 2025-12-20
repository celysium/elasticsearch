<?php
return [
    'elasticsearch' => [
        'host'             => env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
        'username'         => env('ELASTICSEARCH_USERNAME'),
        'password'         => env('ELASTICSEARCH_PASSWORD'),
        'ssl_verification' => env('ELASTICSEARCH_SSL_VERIFICATION', false),
    ],
];