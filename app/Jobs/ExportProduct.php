<?php

namespace App\Jobs;

use App\Models\Export;
use App\Models\Product;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Storage;

class ExportProduct implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    protected int $offset;
    protected Export $export;

    public $timeout = 10000;
    public $failOnTimeout = false;

    /**
     * Create a new job instance.
     *
     * @param $export
     * @param $offset
     */
    public function __construct($export, $offset)
    {
        $this->export = $export;
        $this->offset = $offset;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $file = fopen(Storage::disk('public')->path('export-tmp/' . $this->export->id . "/" . ($this->offset + 1) . '.csv'), 'a+');

            $offset = $this->offset * $this->export->chunk_size;

            $chunk = $this->export->chunk_size;

            $itChunk = min($chunk, 1000);

            $it = ceil($chunk / $itChunk);

            $headers = [
                'id' => 'id',
                'url' => 'URL',
                'part_number' => 'PartNumber',
                'manufacturer' => 'Manufacturer',
                'category' => 'Category',
                'subcategory' => 'Subcategory',
                'subsubcategory' => 'SubSubcategory',
                'short_description' => 'Description',
                'tags' => 'Tags',
                'price_per_quantity' => 'price_per_quantity',
                'description' => 'DetailDescription',
                'quantity' => 'QuantityAvailable',
                'datasheet' => 'Datasheet',
                'image' => 'Image',
                // 'product_detail_data' => 'Attribute',
            ];

            for ($i = 1; $i <= 55; $i++) {
                $headers['attribute_' . $i] = 'Attribute' . $i;
                $headers['value_' . $i] = 'Value' . $i;
            }

            fputcsv($file, array_values($headers));

            //DB::enableQueryLog();

            for ($i = 0; $i < $it; $i++) {

                $itChunk = ($i + 1) * $itChunk > $chunk ? ($chunk - ($i * $itChunk)) : $itChunk;

                $productCount = Product::with('productDetail');

                $conditions = $this->export->conditions;

                $filterManufacturer = null;
                $filterCategory = null;
                $filterSubCategory = null;
                $filterSubSubCategory = null;

                if($conditions && is_array($conditions)) {
                 //   $productCount = Product::has('productDetail');
                    if (isset($conditions['category_id']) && $conditions['category_id'] != "") {
                        $productCount = $productCount->where('category_id', $conditions['category_id']);
                        $filterCategory = getCategory($conditions['category_id']);
                    }
                    if (isset($conditions['subcategory_id']) && $conditions['subcategory_id'] != "") {
                        $productCount = $productCount->where('subcategory_id', $conditions['subcategory_id']);
                        $filterSubCategory = getCategory($conditions['subcategory_id']);;
                    }
                    if (isset($conditions['subsubcategory_id']) && $conditions['subsubcategory_id'] != "") {
                        $productCount = $productCount->where('subsubcategory_id', $conditions['subsubcategory_id']);
                        $filterSubSubCategory = getCategory($conditions['subsubcategory_id']);
                    }
                    if (isset($conditions['manufacturer_id']) && $conditions['manufacturer_id'] != "") {
                        $productCount = $productCount->where('manufacturer_id', $conditions['manufacturer_id']);
                        $filterManufacturer = getManufacturer($conditions['manufacturer_id']);
                    }
                }

                /** @var Product[] $productCount */
                $productCount = $productCount->offset($offset)
                    ->take($itChunk)
                    ->orderBy('products.id')
                    ->get();

                foreach ($productCount as $row) {

                    $manufacturer = $filterManufacturer ? $filterManufacturer : $row->cache_manufacturer;
                    $category = $filterCategory ? $filterCategory : $row->cache_category;
                    $subCategory = $filterSubCategory ? $filterSubCategory : $row->cache_sub_category;
                    $subSubCategory = $filterSubSubCategory ? $filterSubSubCategory : $row->cache_sub_sub_category;

                    $toArray = $row->toArray();
                    if($row->productDetail && is_array($row->productDetail->data) && count($row->productDetail->data) > 0) {
                        foreach($row->productDetail->data as $k => $v) {
                            $toArray[$k] = $v;
                        }
                    }

                    $toArray['manufacturer'] = $manufacturer ? $manufacturer->name : '';
                    $toArray['category']  = $category ? $category->name : '';
                    $toArray['subcategory']  = $subCategory ? $subCategory->name : '';
                    $toArray['subsubcategory']  = $subSubCategory ? $subSubCategory->name : '';
                    $toArray['url']  = $row->detail_url ?? "";
                    $toArray['image']  = $row->strict_image_full_url ? $row->strict_image_full_url : '';
                    $toArray['datasheet']  = $row->datasheet_full_url ? $row->datasheet_full_url : '';

                    $res = [];
                    foreach ($headers as $key => $value) {
                        $res[$key] = data_get($toArray, $key);
                    }
                    $res = array_filter($res, fn($item) => !is_array($item));

                    fputcsv($file, $res);
                }

                $offset += $itChunk;

            }

            fclose($file);

            $this->export->refresh();
            $this->export->update(['no_of_file_completed' => $this->export->no_of_file_completed +1 ]);
            // $this->export->update(['no_of_file_completed' => $this->offset +1 ]);

            $this->export->update([
                'expected_completed_at' =>
                    now()->addSeconds(($this->export->process_start_at->diffInSeconds(now())    / ($this->offset +1)) * $this->export->no_of_file)
            ]);

        } catch (\Exception $e) {
            \Log::info('ExportProduct job error : ' . $e->getMessage());
        }
    }


}
