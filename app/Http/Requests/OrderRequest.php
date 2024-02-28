<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Session;

/**
 * Class OrderRequest
 */
class OrderRequest extends FormRequest
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

        $bomFiles = Session::get('bom_files') ?? [];

        $rule = [
            'company_name' => 'required',
            'message' => 'nullable',
            'email' => 'required|email:rfc|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|max:100',
            'contact_name' => 'nullable',

        ];
        if (!count($bomFiles)) {
            $rule = [
                ...$rule,
                'price' => 'required|array',
                'price.*' => 'nullable|numeric|min:0.01',
                'quantity.*' => 'required_with:product_id.*|nullable|numeric|min:1',
                'part_number' => 'required|array',
                'manufacturer_name.*' => 'nullable',
                'part_number.*' => 'required',
                //  'part_number.*' =>['required', Rule::exists('products', 'part_number')],
            ];
        }

        return $rule;
    }

    public function withValidator(Validator $validator)
    {
//        if ($validator->fails()) {
//            return;
//        }

        if (is_array($this->price)) {
            $validator->after(function (Validator $validator) {
                $minPriceToAdjust = getSetting('quotation_decrease', 'value', 20);

                if (!$minPriceToAdjust) {
                    $minPriceToAdjust = 20;
                }
                foreach ($this->part_number as $key => $price) {

                    $price = $this->price[$key];

                    $product = Product::where('part_number', $this->part_number[$key])->first();

                    if ($product && $product->price && !$price) {
                        $validator->errors()->add('price.' . $key, 'The price field is required.');
                    }

                    if ($product && $price) {
                        $frontend_price = (float)$product->price;
                        $minPrice = ($frontend_price * (100 - $minPriceToAdjust)) / 100;
                        if ($minPrice > $price) {
                            $errorMsg = 'The reference price cant be <strong>less than ' . currentCurrency()->symbol . $minPrice . '</strong>';
                            $validator->errors()->add('price.' . $key, $errorMsg);
                        }
                    }
                }
            });
        }
    }

}
