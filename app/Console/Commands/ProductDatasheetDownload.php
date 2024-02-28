<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProductDatasheetDownload as ProductDatasheetDownloadJob;

class ProductDatasheetDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:datasheet_download';

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
        dispatch(new ProductDatasheetDownloadJob())->onQueue('asset-import');
        return Command::SUCCESS;
    }
}
