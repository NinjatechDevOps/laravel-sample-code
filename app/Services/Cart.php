<?php


namespace App\Services;


use App\Models\Product;
use Session;

class Cart
{

    public function __construct()
    {

    }

    /**
     * @return float
     */
    public static function getItems(): array
    {
        $quotes = Session::get('inquiryList') ?? [];
        foreach ($quotes as $key => $quote) {
            $product = Product::where('part_number', $quote['part_number'])->first();
            $quotes[$key]['product'] = $product;
            $quotes[$key]['price'] = $quote['price']->toCurrentCurrency();
        }
        return $quotes;
    }

}
