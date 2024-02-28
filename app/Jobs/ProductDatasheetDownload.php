<?php

namespace App\Jobs;

use App\Models\Product;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Storage;
use Symfony\Component\Process\Process;

class ProductDatasheetDownload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 10000;
    public $failOnTimeout = false;

    /**
     * @throws GuzzleException
     */
    public function handle()
    {
        Log::info("PDF Querying................................" . now()->format('H:i:s'));

        $products = Product::whereNotNull('datasheet')
            ->whereNull('datasheet_url')
            ->take(10)->get();

        if (!$products->count()) {
             return;
        }

        Log::info("PDF Query Done...." . now()->format('H:i:s'));

        foreach ($products as $product) {
            downloadProductDatasheet($product);
        }

        dispatch(new ProductDatasheetDownload())->onQueue('asset-import');
    }
}
