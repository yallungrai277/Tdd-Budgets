<?php

namespace App\Providers;

use App\Payment\FakePayment;
use App\Payment\PaymentContract;
use App\Payment\StripePayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningUnitTests()) {
            $this->app->bind(PaymentContract::class, function ($app) {
                return new FakePayment();
            });
        } else {
            $this->app->bind(PaymentContract::class, function ($app) {
                return new StripePayment();
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::shouldBeStrict(!app()->environment('production'));
    }
}