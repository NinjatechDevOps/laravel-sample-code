<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FailedImportDataController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\ManufacturerController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\NewsCategoriesController;
use App\Http\Controllers\Admin\ContactInquiryController;
use App\Http\Controllers\Admin\PagesController;
use Illuminate\Support\Facades\Route;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->middleware(['auth']);

Route::group(['prefix' => 'admin'], function () {

    Auth::routes(['register' => false]);

    Route::middleware(['auth'])->group(function () {

        Route::group(['as' => 'admin.'], function () {

            Route::get('/', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');
            Route::post('/', [App\Http\Controllers\Admin\HomeController::class, 'index']);
            Route::get('/myprofile', [App\Http\Controllers\Admin\HomeController::class, 'myprofile'])->name('myprofile');
            Route::post('/myprofile', [App\Http\Controllers\Admin\HomeController::class, 'updateMyProfile']);
            Route::get('/changepassword', [App\Http\Controllers\Admin\HomeController::class, 'changepassword'])->name('changepassword');
            Route::post('/changepassword', [App\Http\Controllers\Admin\HomeController::class, 'updateMyPassword']);
            Route::resources([
                'orders' => OrderController::class,
            ]);

            # END : Routes for the Quotes module

           
            Route::get('/orders/{order}/pay', [App\Http\Controllers\Admin\OrderController::class, 'pay'])->name('orders.pay');
            Route::post("deductAmount", [App\Http\Controllers\Admin\OrderController::class, 'deductAmount'])->name('deductAmount');
            Route::post('orders/processOrder', [App\Http\Controllers\Admin\OrderController::class, 'processOrder'])->name('orders.processOrder');
            /* End */

            Route::post('/update-is-payable', [App\Http\Controllers\Admin\ProductController::class, 'updateIsPayable'])->name('products.updateIsPayable');
            Route::post('/update-is-all-payable', [App\Http\Controllers\Admin\ProductController::class, 'updateAllPayable'])->name('products.updateAllPayable');

            Route::get('/order-list', [App\Http\Controllers\Admin\OrderController::class, 'list'])->name('orders.list');
            Route::get('products/subcategories/{category_id?}', [App\Http\Controllers\Admin\ProductController::class, 'getSubCategories'])->name('products.subcategories');
            Route::get('product-qty-price-update', [App\Http\Controllers\Admin\ProductController::class, 'qtyPriceUpdatePage'])->name('products.qtyPriceUpdate');
            Route::post('product-qty-price-update', [App\Http\Controllers\Admin\ProductController::class, 'qtyPriceUpdate']);
            Route::post('products/deleteAll', [App\Http\Controllers\Admin\ProductController::class, 'deleteAll']);

        });
    });
});



