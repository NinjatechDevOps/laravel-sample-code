<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\FailedImportData;
use App\Models\ImportBatch;
use App\Models\Manufacturer;
use App\Models\Product;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Storage;
use Str;

class ImportProduct implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    protected string $productData;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 10000;
    public $failOnTimeout = false;

    protected int $importBatchId;

    protected ImportBatch $importBatch;

    protected $index = 1;

    protected $keyField = null;

    /**
     * Create a new job instance.
     *
     * @param int $importBatchId
     * @param int $index
     */
    public function __construct($productData, $importBatchId, $index, $keyField)
    {
        $this->productData = $productData;
        $this->importBatchId = $importBatchId;
        $this->keyField = $keyField;
        $this->index = $index;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $filename = $this->productData = Storage::disk('local')->path($this->productData);

        $this->importBatch = ImportBatch::findOrFail($this->importBatchId);
        if (! file_exists($filename) || ! is_readable($filename)) {
            return;
        }

        if (($handle = fopen($filename, 'r')) !== false) {
            $i = 0;
            while (($productData = fgetcsv($handle, 0, ',')) !== false) {
                $productData = array_map('utf8_encode', $productData); //added

                $i++;

                try {
                    if ($i == 1 && $this->index == 1) {
                        continue;
                    }

                    $data = [];
                    if (! $productData[$this->keyField['product']['part_number']]) {
                        $this->failedToInsert(
                            $productData,
                            (($this->index - 1) * 50) + $i,
                            'Missing part_number'
                        );

                        continue;
                    }

                    if (!$productData[$this->keyField['manufacturer']]) {
                        $this->failedToInsert(
                            $productData,
                            (($this->index - 1) * 50) + $i + 2,
                            'Missing manufacturer'
                        );
                        continue;
                    }
                    if (!$productData[$this->keyField['category']]) {
                        $this->failedToInsert(
                            $productData,
                            (($this->index - 1) * 50) + $i + 2,
                            'Missing category'
                        );
                        continue;
                    }
                    if (!$productData[$this->keyField['subcategory']]) {
                        $this->failedToInsert(
                            $productData,
                            (($this->index - 1) * 50) + $i + 2,
                            'Missing subcategory'
                        );
                        continue;
                    }

                    foreach ($this->keyField['product'] as $k => $val) {
                        if (!is_null($val)) {
                            if (array_key_exists($val, $productData)) {
                                $data[$k] = $productData[$val] && $productData[$val] != ''
                                    ? iconv('UTF-8', 'ASCII//IGNORE',  $productData[$val])
                                    : null;
                                if($k == 'quantity' && !$productData[$val]){
                                    $data[$k] = rand(5000,10000);
                                }
                            }
                        }
                    }

                    $product_detail_data = ['data' => []];

                    foreach ($this->keyField['product_detail'] as $k => $val) {
                        if (!is_null($val)) {
                            if (is_array($val)) {
                                foreach ($val as $key => $value) {
                                    if ($value) {
                                        if (array_key_exists($value, $productData)) {
                                            $product_detail_data[$k][$key] =
                                                $productData[$value]
                                                && $productData[$value] != ''
                                                    ? $productData[$value]
                                                    : null;
                                        }
                                    }
                                }
                            } elseif (array_key_exists($val, $productData)) {
                                $product_detail_data[$k] =
                                    $productData[$val]
                                    && $productData[$val] != ''
                                        ? $productData[$val]
                                        : null;
                            }
                        }
                    }

                    $data['row_number'] = (($this->index - 1) * 500) + $i;
                    $data['import_batch_id'] = $this->importBatchId;

                    if ($productData[$this->keyField['manufacturer']]) {
                        $manufacturer = Manufacturer::where(
                            'slug',
                            Str::slug($productData[$this->keyField['manufacturer']])
                        )->first();
                        if (! $manufacturer) {
                            $manufacturer = Manufacturer::create(
                                [
                                    'import_batch_id' => $this->importBatchId,
                                    'name' => $productData[$this->keyField['manufacturer']],
                                    'full_name' => $productData[$this->keyField['manufacturer']],
                                ]
                            );
                        }
                        $data['manufacturer_id'] = $manufacturer->id;
                    }
                    if ($productData[$this->keyField['category']]) {
                        $category = Category::where(
                            'slug',
                            Str::slug($productData[$this->keyField['category']])
                        )->whereNull('parent_id')->first();
                        if (! $category) {
                            $category = Category::create(
                                [
                                    'name' => $productData[$this->keyField['category']],
                                    'import_batch_id' => $this->importBatchId,
                                ]
                            );
                        }
                        $data['category_id'] = $category->id;

                        if ($productData[$this->keyField['subcategory']]) {
                            $subcategory = Category::where(
                                'name',
                                $productData[$this->keyField['subcategory']]
                            )->where('parent_id', $category->id)->first();
                            if (! $subcategory) {
                                $subcategory = Category::create([
                                    'name' => $productData[$this->keyField['subcategory']],
                                    'parent_id' => $category->id,
                                    'import_batch_id' => $this->importBatchId,
                                ]);
                            }
                            $data['subcategory_id'] = $subcategory->id;

                            if ($productData[$this->keyField['subsubcategory']]) {
                                $subsubcategory = Category::where(
                                    'name',
                                    $productData[$this->keyField['subsubcategory']]
                                )->where('parent_id', $subcategory->id)->first();
                                if (! $subsubcategory) {
                                    $subsubcategory = Category::create([
                                        'name' => $productData[$this->keyField['subsubcategory']],
                                        'parent_id' => $subcategory->id,
                                        'import_batch_id' => $this->importBatchId,
                                    ]);
                                }
                                $data['subsubcategory_id'] = $subsubcategory->id;
                            }
                        }
                    }

                    if(isset($data['quantity']) && $data['quantity'] != "")
                    {
                        $quantity = str_replace([',','$',' '], '', $data['quantity']);
                        $data['quantity'] = (int)$quantity;
                    }

                    if(isset($data['price_per_quantity']) && $data['price_per_quantity'] != "")
                    {
                        $price = str_replace([',','$',' '], '', $data['price_per_quantity']);
                        $data['price_per_quantity'] = (float)$price;
                    }

                    $product = Product::withTrashed()
                        ->where('part_number', $data['part_number'])
                        ->first();

                    if ($product) {
                        if ($product->deleted_at) {
                            $product->restore();
                        } else {
                            $data['updated_import_batch_id'] = $data['import_batch_id'];
                            unset($data['import_batch_id']);
                            $this->importBatch->update(
                                ['override_row' => DB::raw('override_row + 1')]
                            );
                        }
                    }

                    if ($product) {
                        $product->update($data);
                    } else {
                        $product = Product::create($data);
                    }

                    $productDetail = $product->productDetail;

                    //(\Log::info($product_detail_data));
                    if ($productDetail) {
                        $productDetail->update($product_detail_data);
                    } else {
                        $product->productDetail()->create($product_detail_data);
                    }

                    $this->importBatch->update(['processed_row' => DB::raw('processed_row + 1')]);
                } catch (Exception $e) {
                    $this->failedToInsert(
                        $productData,
                        (($this->index - 1) * 50) + $i + 2,
                        $e->getMessage() . ' ' . $e->getLine()
                    );
                }
            }

            fclose($handle);
            //Storage::disk('local')->deleteDirectory($this->importBatch->chunk_directory_path);
        }
    }

    public function failedToInsert($productData, $rowNumber, $reason = '')
    {
        $this->importBatch->update(['failed_row' => DB::raw('failed_row + 1')]);

        FailedImportData::create(
            [
                'imported_batch_id' => $this->importBatchId,
                'data' => $productData,
                'row_number' => $rowNumber,
                'message' => $reason,
            ]
        );
    }
}
