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
use Storage;
use Image;
use Symfony\Component\Process\Process;

class ProductImageDownload implements ShouldQueue
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
        \Log::info('IMG Querying................................' . now()->format('H:i:s'));

        $products = Product::whereNotNull('image')
            ->whereNull('image_url')
          //  ->inRandomOrder()
            ->take(10)->get();

        if (!$products->count()) {
            return;
        }

        \Log::info('IMG Query Done....' . now()->format('H:i:s'));

        foreach ($products as $product) {

            $fileName = time() . rand(111111, 999999);
            $oldImage = $fileName . '.png';
            $fileBasePath = Storage::disk('public')->path(config('constants.PRODUCT_IMAGE_PATH'));
            $filePath = Storage::disk('public')->path(config('constants.PRODUCT_IMAGE_PATH') . $oldImage);

            try {
                \Log::info('IMG Downloading....');

                $url = (str_starts_with($product->image, '//') ? 'https:' : '') . $product->image;
                $url = str_replace(' ', '%20', $url);
                $url = str_replace(';', '%3B', $url);
                $url = str_replace(',', '%2C', $url);
                $url = str_replace('_', '%5F', $url);
                if (str_starts_with($url, 'https://media')) {
                    $command = 'cd ' . $fileBasePath . ' && curl \'' . $url . '\' -H \'sec-ch-ua: "Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"\' -H \'Referer: https://www.digikey.com/\' -H \'sec-ch-ua-mobile: ?0\' -H \'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36\' -H \'sec-ch-ua-platform: "Linux"\' --compressed -o ' . $oldImage;
                } else {
                    $command = 'cd ' . $fileBasePath . ' && curl \'' . $url . '\' -H \'sec-ch-ua: "Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"\' -H \'sec-ch-ua-mobile: ?0\' -H \'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36\' -H \'sec-ch-ua-platform: "Linux"\' --compressed -o ' . $oldImage;

                }
                \Log::info('IMG CMD....' . $command);

                $process = Process::fromShellCommandline($command);
                $process->setTimeout(5000);
                $process->run();
                $imgFile = \Intervention\Image\Facades\Image::make($filePath);

                $imgFile->trim();
                if ($imgFile->width() > 250) {
                    $imgFile->resize(250, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }
                if ($imgFile->height() > 250) {
                    $imgFile->resize(null, 250, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                $canvas = Image::canvas(250, 250, '#ffffff');
                $canvas->insert($imgFile, 'center')->encode('webp');
                Storage::put(config('constants.PRODUCT_IMAGE_PATH') . $fileName . '.webp', $canvas->stream());
                Storage::disk('public')->delete(config('constants.PRODUCT_IMAGE_PATH') . $oldImage);
                $product->update(['image_url' => $fileName . '.webp']);
              //  \Log::info("IMG command " . $command);
                \Log::info('IMG finished for ....' . $product->id);

            } catch (\Exception $e) {
                \Log::info('IMG failed for ....' . $product->id);
                $product->update(['image_url' => 'FAILED']);
                if ($oldImage) {
                    Storage::disk('public')->delete(config('constants.PRODUCT_IMAGE_PATH') . $oldImage);
                }
            }
        }

        dispatch(new ProductImageDownload())->onQueue('asset-import');


    }
}
