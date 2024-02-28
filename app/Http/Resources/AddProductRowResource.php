<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddProductRowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'part_number' => $this->part_number,
            'product_id' => $this->id,
            'price' => $this->price,
            'product_image' => $this->image_full_url,
            'quantity' => 1,
            'manufacturer_name' => $this->manufacturer->name,
            'manufacturer_image' => $this->manufacturer->image_url,
            'availability' => (bool) $this->quantity,
        ];
    }
}
