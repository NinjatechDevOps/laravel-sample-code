<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ProductRequest;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ShippingBillingDetails;
use App\Models\User;
use App\Services\Currency\Amount;
use App\Services\ProductSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Session;
use App\Services\Cart;
use Exception;
use App\Services\Currency\Contracts\BaseCurrency;

/**
 * Class ProductController
 *
 * @package App\Http\Controllers
 */
class ProductController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $products = ProductSearch::search(['s' => $request->s, 5]);

        return view('frontend.search', compact('products'));
    }

    /**
     * @param $manufacturerSlug
     * @param $slug
     * @return Application|Factory|View
     */
    public function show($slug)
    {
        $slug = rawurldecode($slug);
        $product = Product::where(['part_number' => $slug])->firstOrFail();
        $productDetail = $product->productDetail;
        $params = [];
        if ($product->subsubcategory_id) {
            $params['subsubcategory_id'] = $product->subsubcategory_id;
        } else {
            $params['subcategory_id'] = $product->subcategory_id;
        }
        $relatedProducts = ProductSearch::search($params, 15);

        $manufacturer = $product->cache_manufacturer;
        $category = $product->cache_category;
        $subCategory = $product->cache_sub_category;
        $subSubCategory = $product->cache_sub_sub_category;

        $myquote = [];
        $quotes = Cart::getItems();
        if (is_array($quotes) && count($quotes) > 0) {
            foreach ($quotes as $quote) {
                if ($product->part_number == $quote['part_number']) {
                    $myquote = $quote;
                    break;
                }
            }
        }

        saveRecentSearch('products', $product);
        return view('frontend.product.view', compact(
            'product',
            'productDetail',
            'relatedProducts',
            'manufacturer',
            'category',
            'subCategory',
            'subSubCategory',
            'myquote'
        ));
    }

    public function list(Request $request)
    {
        $manufacturerIds = $request->input('manufacturer_ids', []);
        $otherParameters = $request->except('manufacturer_ids');

        $products = ProductSearch::search($request->all(), 15);

        return response()->json([
            'data' => view()->make('frontend.product-list', compact('products'))->render(),
            'pagination' => (string) $products->appends($request->all())->links(),
        ]);
    }

    public function productDetails(Request $request)
    {
        $partNumber = $request['part_number'];
        $existingProduct = collect(Session::get('inquiryList'))->first(
            function ($item) use ($partNumber) {
                return $item['part_number'] === $partNumber;
            }
        );
        if ($existingProduct) {
            return response()->json(['message' => 'Part number already exists'], 400);
        }
        $product = Product::where('part_number', $partNumber)->first();
        $cartItem = [
            'part_number' => $product ? $product->part_number : $partNumber,
            'price' => new Amount($product ? (float)$product->price : 0, app(BaseCurrency::class)->abbr),
            'quantity' => 1,
        ];
        $key = 'inquiryList';
        $sessionData = Session::get($key, []);
        $sessionData[] = $cartItem;
        Session::put($key, $sessionData);
        $cartItem['price'] = $cartItem['price']->toCurrentCurrency();
        $cartItem['product'] = $product;
        $html = view()->make('components.cart-item', compact('cartItem'))->render();
        return response()->json(['html' => $html, 'total' => count($sessionData)]);
    }

    public function datasheet($id)
    {
        $product = Product::whereNot('datasheet')->findOrFail($id);
        return view()->make('frontend.product.datasheet', compact('product'));
    }
}
