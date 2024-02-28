<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Class CategoryRequest
 */
class CategoryRequest extends FormRequest
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
        return [
            'name' => 'required',
            'slug' => [
                'required'
            ],
            'parent_id' => 'nullable',
            'product_counts' => 'nullable',
            'description' => 'nullable',
            'long_description' => 'nullable',
            'meta_title' => 'nullable',
            'image' => 'nullable',
            'is_feature' => 'boolean',
            'meta_description' => 'nullable',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'icon_name' => 'nullable',
            'parent_parent_id' => 'nullable',
        ];
    }

    public function withValidator(Validator $validator)
    {
        if ($validator->fails()) {
            return;
        }
        $validator->after(function (Validator $validator) {
            $slugExist = Category::where('slug', $this->slug);
            if($this->parent_id){
                $slugExist = $slugExist->where('parent_id', $this->parent_id);
            }else{
                $slugExist = $slugExist->whereNull('parent_id');
            }
            if($this->category && $this->category->id){
                $slugExist = $slugExist->where('id', '!=', $this->category->id);
            }
            $slugExist = $slugExist->exists();
            if($slugExist){
                $validator->errors()->add('slug','The slug has already been taken.');
            }
        });
    }
}
