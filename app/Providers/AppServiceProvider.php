<?php

namespace App\Providers;

use App\Models\CurrencyExchangeRate;
use App\Services\Currency\Contracts\BaseCurrency;
use App\Services\Currency\Contracts\CurrentCurrency;

use App\Services\Engine\OpenSearchEngine;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        resolve(EngineManager::class)->extend(config('scout.driver'), function () {
            return new OpenSearchEngine;
        });

        $this->app->singleton(BaseCurrency::class, function () {
            return CurrencyExchangeRate::fromCode('USD');
        });

        $this->app->singleton(CurrentCurrency::class, function () {
            return CurrencyExchangeRate::fromCode('USD');
        });

        Paginator::useBootstrapFour();
        if (Schema::hasTable('settings')) {
            $arrStripeSettings = getStripeSettings();
            if (isset($arrStripeSettings['stripe_key']) && isset($arrStripeSettings['stripe_secret']) && !empty($arrStripeSettings['stripe_key']) && !empty($arrStripeSettings['stripe_secret'])) {
                Config::set('stripe.STRIPE_KEY', $arrStripeSettings['stripe_key']);
                Config::set('stripe.STRIPE_SECRET', $arrStripeSettings['stripe_secret']);
            }

            if (isset($arrStripeSettings['min_amt']) && $arrStripeSettings['min_amt'] > 0) {
                Config::set('stripe.MIN_AMOUNT', $arrStripeSettings['min_amt']);
            }

            if (isset($arrStripeSettings['success_msg']) && !empty($arrStripeSettings['success_msg'])) {
                Config::set('stripe.SUCCESS_MSG', $arrStripeSettings['success_msg']);
            }

            if (isset($arrStripeSettings['error_msg']) && !empty($arrStripeSettings['error_msg'])) {
                Config::set('stripe.ERROR_MSG', $arrStripeSettings['success_msg']);
            }
        }
        Schema::defaultStringLength(191);
    }
}
