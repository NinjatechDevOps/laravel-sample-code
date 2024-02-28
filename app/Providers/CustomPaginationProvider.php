<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class CustomPaginationProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(
            \Illuminate\Pagination\LengthAwarePaginator::class,
            \App\Extensions\CustomLengthAwarePaginator::class
        );

        Paginator::currentPathResolver(function () {
            $path = parse_url(request()->url(), PHP_URL_PATH);
            return preg_replace('/\/page-\d+/', '', $path);
        });

        Paginator::currentPageResolver(function ($pageName) {
            $currentPage = request()->route($pageName);
            if (is_numeric($currentPage)) {
                return $currentPage;
            }
            return 1;
        });
    }
}
