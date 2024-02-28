<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProductImageDownload as ProductImageDownloadJob;

class ProductImageDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:image_download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dispatch(new ProductImageDownloadJob())->onQueue('asset-import');
        return Command::SUCCESS;
    }
}
