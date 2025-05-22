<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FreteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('frete', function ($app) {
            return new \App\Services\FreteService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
