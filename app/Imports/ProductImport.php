<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductImport implements ToModel, WithChunkReading, ShouldQueue
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Product(
            [
                'part_number' => $row[1],
            ]
        );
    }

    public function chunkSize(): int
    {
        return 100000;
    }
}
