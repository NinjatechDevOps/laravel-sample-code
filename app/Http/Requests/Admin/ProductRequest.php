<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

/**
 * Class ProductRequest
 */
class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'part_number' => 'required|unique:'.(new Product)->getTable().',part_number,'.(request()->product ? request()->product->id : null),
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'subsubcategory_id' => 'nullable',
            'tags' => 'nullable',
            'price_per_quantity' => 'nullable',
            'manufacturer_id' => 'required',
            'description' => 'nullable',
            'short_description' => 'nullable',
            'rohs_status' => 'nullable',
            'quantity' => 'nullable',
            'datasheet_file' => 'nullable|mimes:pdf|max:5048',
            'datasheet_url' => 'nullable|url',
            'image' => 'nullable',
            'image_url' => 'nullable',
            'meta_title' => 'nullable',
            'meta_description' => 'nullable',
            'data' => 'nullable',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_payable' => 'nullable',
        ];

        return $rules;
    }
}
