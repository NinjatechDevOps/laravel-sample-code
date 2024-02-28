<?php

namespace App\Jobs;

use App\Models\Export;
use App\Models\Product;
use Bus;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Storage;
use Throwable;
use ZipArchive;

class ExportProductChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 10000;
    public $failOnTimeout = false;

    protected $export = null;

    /**
     * Create a new job instance.
     *
     * @param $export
     */
    public function __construct(Export $export)
    {
        $this->export = $export;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $processes = [];

            $this->export->update(['process_start_at' => Carbon::now()]);

            // Removed left join as it is not impacting
            // $productCount = Product::join('product_details', 'products.id', '=', 'product_details.product_id')
            //     ->join('manufacturers', 'manufacturers.id', '=', 'products.manufacturer_id')
            //     ->join('categories', 'categories.id', '=', 'products.category_id')
            //     ->join('categories as sub_categories', 'sub_categories.id', '=', 'products.subcategory_id');

            $conditions = $this->export->conditions;
            $productCount = Product::has('productDetail')->has('category')->has('subCategory')->has('manufacturer');
            if($conditions && is_array($conditions)) {
                if (isset($conditions['category_id']) && $conditions['category_id'] != "") {
                    $productCount = $productCount->where('category_id', $conditions['category_id']);
                }
                if (isset($conditions['subcategory_id']) && $conditions['subcategory_id'] != "") {
                    $productCount = $productCount->where('subcategory_id', $conditions['subcategory_id']);
                }
                if (isset($conditions['subsubcategory_id']) && $conditions['subsubcategory_id'] != "") {
                    $productCount = $productCount->where('subsubcategory_id', $conditions['subsubcategory_id']);
                }
                if (isset($conditions['manufacturer_id']) && $conditions['manufacturer_id'] != "") {
                    $productCount = $productCount->where('manufacturer_id', $conditions['manufacturer_id']);
                }
            }
            $productCount = $productCount->count();

            $chunk = ceil($productCount / $this->export->chunk_size);
            for ($i = 0; $i < $chunk; $i++) {
                $processes[] = new ExportProduct(
                    $this->export,
                    $i
                );
            }

            $this->export->update([
                'total_row' => $productCount,
                'no_of_file' => $chunk,
            ]);

            if (!count($processes)) {
                return;
            }

            Storage::disk('local')->makeDirectory('public/export-tmp/' . $this->export->id);

            $bus = Bus::batch($processes)
                ->then(function (Batch $batch) {

                    $export = Export::where('job_batch_id', $batch->id)->first();
                    if ($export) {

                        $zipFileName = $batch->id . '.zip';

                        $zip = new ZipArchive();

                        $zip->open(
                            Storage::disk('local')->path('public/export/' . $zipFileName),
                            ZipArchive::CREATE | ZipArchive::OVERWRITE
                        );

                        foreach (Storage::disk('local')->allFiles('public/export-tmp/' . $export->id) as $filePath) {
                            if (strpos('.zip', $filePath)) {
                                continue;
                            }
                            $zip->addFile(
                                Storage::disk('local')->path($filePath),
                                substr($filePath, strlen($export->id) + 1)
                            );
                        }

                        $zip->close();

                        // Delete processId dir with all of its files
                        Storage::disk('local')->deleteDirectory('public/export-tmp/' . $export->id);

                        $export->update([
                            'file_name' => $zipFileName
                        ]);
                    }
                })
                ->catch(function (Batch $batch, Throwable $exception) {

                })->finally(function (Batch $batch) {

                })
                ->name('Export Product #' . $this->export->id)
                ->allowFailures(false)
                ->onConnection('redis')
                ->onQueue('export-product')
                ->dispatch();

            $this->export->update([
                'job_batch_id' => $bus->id
            ]);
        } catch (\Exception $e) {
            Log::info('ExportProductChunk job error : ' . $e->getMessage());
        }
    }

//    public function failed($exception)
//    {
//        $this->importBatch->update([
//            'status' => ImportBatch::STATUS_FAILED,
//            'reason' => $exception->getMessage(),
//        ]);
//    }
}
