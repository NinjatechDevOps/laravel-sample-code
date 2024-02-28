<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use Bus;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SplFileObject;
use Storage;
use Symfony\Component\Process\Process;
use Throwable;

class ImportProductChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 10000;

    protected ImportBatch $importBatch;
    protected array $keyField = [];

    /**
     * Create a new job instance.
     *
     * @param ImportBatch $importBatch
     */
    public function __construct($importBatch)
    {
        $this->importBatch = $importBatch;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        if (str_ends_with($this->importBatch->file_path, '.xls') || str_ends_with($this->importBatch->file_path, '.xlsx')) {
            $this->generateCsv();
        }

        $this->setDefaultKeyFields();


        $this->updateTotalRow();

        Storage::disk('local')->createDirectory($this->importBatch->chunk_directory_path);


        $this->splitCsv();

        $files = Storage::disk('local')->files($this->importBatch->chunk_directory_path);

        $processes = [];

        foreach ($files as $jobIndex => $fileName) {

            if ($jobIndex == 0) {
                $this->setKeyFields($fileName);
            }

            $processes[] = new ImportProduct(
                $fileName,
                $this->importBatch->id,
                $jobIndex + 1,
                $this->keyField
            );

        }

        if (!count($processes)) {
            $this->importBatch->update([
                'status' => ImportBatch::STATUS_FAILED,
                'reason' => 'Something went wrong.',
            ]);
            return;
        }

        $bus = $this->dispatchProcesses($processes);

        $this->importBatch->update([
            'job_batch_id' => $bus->id
        ]);

        Storage::put('import-files/' . $this->importBatch->file_name . '.pdf', file_get_contents(Storage::disk('local')->path('import/files/' . $this->importBatch->file_name)));

        Storage::disk('local')->delete('import/files/' . $this->importBatch->file_name);
        Storage::disk('local')->delete($this->importBatch->converted_file_base_path);
    }

    /**
     * Generate CSV form Excel File
     */
    private function generateCsv()
    {

        try{
        // Using PHP
        //            $reader = new Xlsx();
        //            $spreadsheet = $reader->load($this->importBatch->file_path);
        //            $loadedSheetNames = $spreadsheet->getSheetNames();
        //            $writer = new Csv($spreadsheet);
        //            foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
        //                if($sheetIndex == 0) {
        //                    $writer->setSheetIndex($sheetIndex);
        //                    $writer->save($this->importBatch->converted_file_path);
        //                }
        //            }

        // Using Command Line
        $process = Process::fromShellCommandline(
            'libreoffice --headless --convert-to csv ' .
            $this->importBatch->file_path . ' --outdir ' .
            Storage::disk('local')->path('import/files/')
        );
        $process->setTimeout(5000);
        $process->run();

        }catch (\Exception $exception){
            $this->importBatch->update([
                'status' => ImportBatch::STATUS_FAILED,
                'reason' => $exception->getMessage(),
            ]);
        }

    }

    /**
     * Set Default Key Field
     */
    private function setDefaultKeyFields()
    {
        $this->keyField = [
            'manufacturer' => null,
            'category' => null,
            'subcategory' => null,
            'subsubcategory' => null,
            'product' => [
                'part_number' => null,
                'short_description' => null,
                'tags' => null,
                'price_per_quantity' => null,
                'description' => null,
                'quantity' => null,
                'datasheet' => null,
                'image' => null,
            ],
            'product_detail' => [
                'product_id' => null,
                'meta_title' => null,
                'meta_description' => null,
                'data' => [],
            ],
        ];
    }

    /**
     * Update Total row to Import Batch Table
     */
    private function updateTotalRow()
    {
        $process = Process::fromShellCommandline("echo $(wc -l ".$this->importBatch->converted_file_path."|awk '{print $1}')");
        $process->run();
        $output = $process->getOutput();
        $output = $output ?? 0;
        $this->importBatch->update(
            [
                'total_row' => (int)$output - 1,
            ]
        );
    }

    /**
     * Split CSV into Chunk
     */
    private function splitCsv()
    {
        $process = Process::fromShellCommandline(
            'split -a 3 --additional-suffix=.csv -l 500 -d "' .
            $this->importBatch->converted_file_path . '" ' .
            $this->importBatch->chunk_directory . '/'
        );
        $process->setTimeout(3600);
        $process->run();
    }

    /**
     * Set Key Fields for Import
     *
     * @param $fileName
     */
    private function setKeyFields($fileName)
    {
        if (($handle = fopen(Storage::disk('local')->path($fileName), 'r')) !== false) {
            if (($data = fgetcsv($handle, 0, ',')) !== false) {
                $data = array_map('utf8_encode', $data); //added
                foreach ($data as $key => $val) {
                    switch (strtolower($val)) {
                        case 'partnumber':
                        case 'part number':
                            $this->keyField['product']['part_number'] = $key;
                            break;
                        case 'manufacturer':
                            $this->keyField['manufacturer'] = $key;
                            break;
                        case 'category':
                            $this->keyField['category'] = $key;
                            break;
                        case 'subcategory':
                        case 'sub category':
                            $this->keyField['subcategory'] = $key;
                            break;
                        case 'subsubcategory':
                        case 'sub sub category':
                        case 'sub subcategory':
                            $this->keyField['subsubcategory'] = $key;
                            break;
                        case 'description':
                            $this->keyField['product']['short_description'] = $key;
                            break;
                        case 'tags':
                        case 'tag':
                            $this->keyField['product']['tags'] = $key;
                            break;
                        case 'price_per_quantity':
                        case 'price per quantity':
                        case 'price':
                            $this->keyField['product']['price_per_quantity'] = $key;
                            break;
                        case 'detaildescription':
                        case 'detail description':
                            $this->keyField['product']['description'] = $key;
                            break;
                        case 'quantityavailable':
                        case 'quantity available':
                        case 'quantity':
                            $this->keyField['product']['quantity'] = $key;
                            break;
                        case 'datasheet':
                            $this->keyField['product']['datasheet'] = $key;
                            break;
                        case 'image':
                            $this->keyField['product']['image'] = $key;
                            break;
                        case 'metatitle':
                        case 'meta title':
                            $this->keyField['product_detail']['meta_title'] = $key;
                            break;
                        case 'metadescription':
                        case 'meta description':
                            $this->keyField['product_detail']['meta_description'] = $key;
                            break;
                    }
                    for ($i = 0; $i < 60; $i++) {
                        switch (strtolower($val)) {
                            case 'attribute' . $i:
                            case  'attribute ' . $i:
                                $this->keyField['product_detail']['data']['attribute_' . $i] = $key;
                                break;
                            case  'value' . $i:
                            case  'value ' . $i :
                                $this->keyField['product_detail']['data']['value_' . $i] = $key;
                                break;

                        }
                    }
                }
            }
            fclose($handle);
        }
    }

    /**
     * Dispatch Processes
     *
     * @param $processes
     * @return Batch
     * @throws Throwable
     */
    private function dispatchProcesses($processes): Batch
    {

        return Bus::batch($processes)
            ->then(function (Batch $batch) {
                $importBatch = ImportBatch::where('job_batch_id', $batch->id)->first();
                //  All jobs completed successfully...
                if ($importBatch) {
                    /*
                    dispatch(new ProductDatasheetDownload(
                        // $importBatch->id
                    ))->onQueue('asset-import');
                    dispatch(new ProductImageDownload(
                        // $importBatch->id
                    ))->onQueue('asset-import');
                    */
                    $importBatch->updateStatus();
                    Storage::disk('local')->deleteDirectory($importBatch->chunk_directory_path);
                }
            })
            ->catch(function (Batch $batch, Throwable $exception) {
                $importBatch = ImportBatch::where('job_batch_id', $batch->id)->first();
                if($importBatch) {
                    $importBatch->updateStatus();
                }
            })->finally(function (Batch $batch) {

            })
            ->name('Import Product #' . $this->importBatch->id)
            ->allowFailures(false)
            ->onConnection('redis')
            ->onQueue('import-product')
            ->dispatch();
    }

    public function failed($exception)
    {
        $this->importBatch->update([
            'status' => ImportBatch::STATUS_FAILED,
            'reason' => $exception->getMessage(),
        ]);
    }
}
