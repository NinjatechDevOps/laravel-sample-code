<?php

namespace App\Services;

use App\Models\Product;

/**
 * Class ProductSearch
 *
 * @package App\Services
 */
class ProductSearch
{
    /**
     * Product Search
     *
     * @param  $param
     * @param  int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function search($param, $pageSize = 5)
    {
        $products = Product::query();

        if (array_key_exists('product_ids', $param) && $param['product_ids']) {
            $products = $products->whereIn('products.id', $param['product_ids']);
        }

        if (array_key_exists('subcategory_id', $param) && $param['subcategory_id']) {
            $products = $products->where('subcategory_id', $param['subcategory_id']);
        }

        if (array_key_exists('category_id', $param) && $param['category_id']) {
            $products = $products->where('category_id', $param['category_id']);
        }

        if (array_key_exists('subsubcategory_id', $param) && $param['subsubcategory_id']) {
            $products = $products->where('subsubcategory_id', $param['subsubcategory_id']);
        }

        if (array_key_exists('manufacturer_id', $param) && $param['manufacturer_id']) {
            $products = $products->where('manufacturer_id', $param['manufacturer_id']);
        }

        if (array_key_exists('manufacturer_ids', $param) && $param['manufacturer_ids']) {
            $products = $products->whereIn('manufacturer_id', $param['manufacturer_ids']);
        }


        if (array_key_exists('all_category_ids', $param) && $param['all_category_ids']) {
            $products = $products->where(function($q) use($param){
                $q->whereIn('category_id', (array)$param['all_category_ids'])
                    ->orWhereIn('subcategory_id', (array)$param['all_category_ids'])
                    ->orWhereIn('subsubcategory_id', (array)$param['all_category_ids']);
            });
        }

        if (array_key_exists('part_number_like', $param) && $param['part_number_like']) {
            $search = '%' . $param['part_number_like'] . '%';
            $products = $products->where(
                function ($q) use ($search) {
                    $q->where('part_number', 'like', $search);
                }
            );
        }

        if (array_key_exists('s', $param) && $param['s']) {
            $search = '%' . $param['s'] . '%';
            $products = $products->where(
                function ($q) use ($search) {
                    $q->where('part_number', 'like', $search);
                        //->orWhere('tags', 'like', $search);
                }
            );
        }
        if (array_key_exists('sort_on', $param) && $param['sort_on'] && array_key_exists('sort_by', $param) && $param['sort_by']) {
            $products = $products->orderBy($param['sort_on'],$param['sort_by']);
        }

        return $products->paginate($pageSize);
    }
}
