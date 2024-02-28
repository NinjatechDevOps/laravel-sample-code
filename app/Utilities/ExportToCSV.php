<?php

namespace App\Utilities;

use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

/**
 * Class ExportToCSV
 *
 * @package App\Utilities
 */
class ExportToCSV
{
    public int $chunkSize = 10;
    public array $headers;
    protected Builder $query;
    protected array $data;
    protected string $zipFileName;
    protected string $fileName;
    protected Filesystem $storage;
    protected string $processId;
    protected int $oldFilesDueDays = 1;
    protected mixed $file;

    /**
     * ExportToCSV constructor.
     */
    public function __construct()
    {
        $this->processId = Str::uuid()->toString();
    }

    /**
     * Generate CSV via Builder Query
     *
     * @param  Builder|null $query
     * @param  array        $headers
     * @param  int          $chunk_size
     * @return StreamedResponse
     * @throws Throwable
     */
    public function exportFromQuery(
        ?Builder $query = null,
        $headers = [],
        $chunk_size = 50000
    ): StreamedResponse {
        $this->query = $query;
        $this->headers = $headers;
        $this->chunkSize = $chunk_size;
        throw_if(!$this->query, new Exception('Query has not been set.'));
        throw_if(empty($this->headers), new Exception('Headers cannot be empty.'));

        $this->setDefaults();

        $this->openFile();

        $this->query->chunk(
            $this->chunkSize,
            function ($records) {
                $this->newFile();
                $this->putToCSV($records);
            }
        );

        return $this->compressFilesAndGetDownloadStream();
    }

    /**
     * Set Default Value for Class
     */
    protected function setDefaults()
    {
        $this->zipFileName = time() . '.zip';
        $this->fileName = time() . rand(1111, 99999) . '.csv';
        $this->storage = $this->storage ?? app('filesystem')
                ->disk(config('filesystems.default'));
    }

    /**
     * Open file to be write
     *
     * @return false|mixed|resource
     */
    protected function openFile()
    {
        $this->storage->makeDirectory($this->processId);
        // Write to csv file in append mode(a+)
        $this->file = fopen($this->storage->path("$this->processId/$this->fileName"), 'a+');
        fputcsv($this->file, $this->headers);

        return $this->file;
    }

    /**
     * Generate New File
     */
    protected function newFile()
    {
        fclose($this->file);
        $this->fileName = time() . rand(1111, 99999) . '.csv';
        $this->file = fopen($this->storage->path("$this->processId/$this->fileName"), 'a+');
    }

    /**
     * @param $iterableData
     */
    protected function putToCSV($iterableData)
    {
        foreach ($iterableData as $row) {
            $toArray = json_decode(json_encode($row), true);
            $res = [];
            foreach ($this->headers as $key => $value) {
                $res[$key] = data_get($toArray, $key);
            }
            $res = array_filter($res, fn($item) => !is_array($item));

            fputcsv($this->file, $res);
        }
    }

    /**
     * @return StreamedResponse
     */
    protected function compressFilesAndGetDownloadStream()
    {
        fclose($this->file);
        $this->deleteOldZipFiles();

        $zip = new ZipArchive();
        $zip->open(
            $this->storage->path($this->zipFileName),
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );
        foreach ($this->storage->allFiles($this->processId) as $filePath) {
            if (strpos('.zip', $filePath)) {
                continue;
            }
            $zip->addFile(
                $this->storage->path($filePath),
                substr($filePath, strlen($this->processId) + 1)
            );
        }
        $zip->close();

        // Delete processId dir with all of its files
        $this->storage->deleteDirectory($this->processId);

        return $this->storage->download($this->zipFileName);
    }

    /**
     * Delete old files that longer than x day
     * For not deleting old files, -1 can be set to $oldFilesDueDays
     */
    protected function deleteOldZipFiles()
    {

        if ($this->oldFilesDueDays === -1) {
            return;
        }

        foreach ($this->storage->allFiles() as $old_file) {
            $fileCreatedAtDaysDiff = Carbon::now()
                ->diffInDays(Carbon::parse($this->storage->lastModified($old_file)));
            if ($fileCreatedAtDaysDiff > abs($this->oldFilesDueDays)) {
                $this->storage->delete($old_file);
            }
        }
    }

    /**
     * Generate CSV via given array
     *
     * @param  array|null $data
     * @return StreamedResponse
     * @throws Throwable
     */
    public function exportFromArrayData(?array $data = []): StreamedResponse
    {
        $this->data = $data;
        throw_if(empty($this->data), new Exception('Data cannot be empty.'));
        throw_if(empty($this->headers), new Exception('Headers cannot be empty.'));

        $this->setDefaults();

        $this->openFile();

        $this->putToCSV($this->data);

        return $this->compressFilesAndGetDownloadStream();
    }

    /**
     * Set Query
     *
     * @param  Builder $query
     * @return $this
     */
    public function setQuery(Builder $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param  array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param  array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param  int $chunkSize
     * @return $this
     */
    public function setChunkSize(int $chunkSize)
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @param  int $oldFilesDueDays
     * @return $this
     */
    public function setOldFilesDueDays(int $oldFilesDueDays)
    {
        $this->oldFilesDueDays = $oldFilesDueDays;

        return $this;
    }
}
