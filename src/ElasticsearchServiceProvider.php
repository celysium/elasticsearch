<?php

namespace Celysium\Elasticsearch;

use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/elasticsearch.php',
            'database.elasticsearch'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

}