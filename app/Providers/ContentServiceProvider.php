<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ContentServiceProvider extends ServiceProvider
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
        View::composer(
            'layouts.frontend',
            function ($view) {

                $menus = [
                    'header' => [
                        [
                            'name' => 'Home',
                            'url' => '/',
                            'icon' => '',
                        ],
                        [
                            'name' => 'Products Index',
                            'url' => '/products.html',
                            'icon' => '',
                        ],
                        [
                            'name' => 'Manufacturer',
                            'url' => '/supplier.html',
                            'icon' => '',
                        ],
                        [
                            'name' => 'News',
                            'url' => '/news',
                            'icon' => '',
                        ],
                        [
                            'name' => 'RFQ',
                            'url' => '/request-quote',
                            'icon' => '',
                        ],
                        [
                            'name' => 'About Us',
                            'url' => '/about-us',
                            'icon' => '',
                            'child' => [
                                [
                                    'name' => 'Contact Us',
                                    'url' => '/request-quote',
                                    'icon' => '',
                                ],
                                [
                                    'name' => 'Privacy Policy',
                                    'url' => '/request-quote',
                                    'icon' => '',
                                ],
                                [
                                    'name' => 'Quality',
                                    'url' => '/request-quote',
                                    'icon' => '',
                                ],
                            ],
                        ],
                    ],
                ];
                $view->with(['menus' => $menus]);
            }
        );
    }
}
