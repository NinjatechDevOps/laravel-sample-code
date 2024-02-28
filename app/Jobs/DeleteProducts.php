<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param array $conditions
     * @param bool $forceDelete
     */
    
    public function __construct(
        public array $conditions,
        public bool $forceDelete = false,
    ) {
        $this->conditions = $conditions;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Product::where($this->conditions)->delete();
    }
}
