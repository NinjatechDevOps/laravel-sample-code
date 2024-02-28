<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProductSearchResource
 *
 * @mixin Product
 */
class ProductSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $category = $this->category;
        $subCategory = $this->subCategory;
        $subSubCategory = $this->subSubCategory;
        $title = $subSubCategory
            ? $subSubCategory->name
            : ($subCategory ? $subCategory->name : $category->name);

        $manufacturer = $this->manufacturer;

        return [
            'id' => $this->id,
            'link' => $manufacturer ? $this->detail_url : '',
            'image' => $this->image_full_url,
            'sub_image' => $manufacturer ? $manufacturer->image_url : '',
            'title' => $title,
            'sub_title' => $this->part_number,
        ];
    }
}
