<?php

namespace JsonToDatabase\Reader;

use Illuminate\Support\ServiceProvider;
use JsonToDatabase\Reader\Factory\ReaderFactory;

class ReaderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ReaderFactory::class, function()
        {
            return new ReaderFactory(base_path());
        });
    }
}
