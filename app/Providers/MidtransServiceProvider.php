<?php
// app/Providers/MidtransServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MidtransServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('midtrans', function ($app) {
            return new \Midtrans\Config();
        });
    }

    public function boot()
    {
        //
    }
}
