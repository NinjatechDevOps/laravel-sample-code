<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\ProductQtyPriceRequest;
use App\Models\Category;
use App\Models\CurrencyExchangeRate;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductDetail;
use Auth;
use DataTables;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Storage, DB;
use Image;
use Stripe\PaymentIntent;
use Stripe\Stripe as StripeStripe;
use Stripe\Exception\CardException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;

/**
 * Class ProductController
 *
 * @package App\Http\Controllers\Admin
 */
class ProductController extends Controller
{
    /**
     * ProductController constructor.
     */
    public $currencyIcon;
    public function __construct()
    {
        /* User Permissions */
        $this->middleware(
            'permission:product-list|product-create|product-edit|product-delete',
            ['only' => ['index', 'store']]
        );
        $this->middleware('permission:product-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
        $this->middleware('permission:product-qty-price-update', ['only' => ['qtyPriceUpdatePage', 'qtyPriceUpdate']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|JsonResponse
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $query = Product::with(['category', 'subCategory', 'manufacturer']);
            if ($request->category_id) {
                $query = $query->where('category_id', $request->category_id);
            }
            if ($request->subcategory_id) {
                $query = $query->where('subcategory_id', $request->subcategory_id);
            }
            if ($request->subsubcategory_id) {
                $query = $query->where('subsubcategory_id', $request->subsubcategory_id);
            }
            if ($request->manufacturer_id) {
                $query = $query->where('manufacturer_id', $request->manufacturer_id);
            }
            if ($request->part_number) {
                $query = $query->where('part_number', $request->part_number);
            }
            if ($request->has('is_payable') && ($request->is_payable != null)  && ($request->is_payable >= 0)) {
                $query = $query->where('is_payable', $request->is_payable);
            }
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('price_per_quantity', function ($row) {
                    return $row->formatted_price;
                })
                ->addColumn('is_payable', function ($row) {
                    return '<input type="checkbox" class="is-payable-checkbox" name="updateProductIds[]" data-product-id="' . $row->id . '"' . ($row->is_payable ? 'checked' : '') . '>';
                })
                ->addColumn(
                    'part_number',
                    function ($row) {
                        return '<div class ="d-flex" >
                                    <div class="mr-2">
                                        <a target="_blank" href="' . route('products.show', $row->encoded_part_number) . '"><img class="image-table-thumb" src="' . $row->image_full_url . '" ' . (!$row->image_url ? 'style="opacity:0.1;"' : '') . '></a>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                    <div class="mr-2">' . $row->part_number . '</div>
                                    <div class="mr-2 small">' . $row->manufacturer->name . '</div>
                                    </div>
                                    </div>';
                    }
                )
                //                ->addColumn(
                //                    'image',
                //                    function ($row) {
                //                        return $row->image_full_url ? '<a
                //                        href="' . $row->image_full_url . '"
                //                        target="_blank"><img class="image-table-thumb" src="' . $row->image_full_url . '"/></a>' : '';
                //                    }
                //                )
                ->addColumn(
                    'action',
                    function ($row) {
                        $btn = '';
                        if (Auth::user()->can('product-edit')) {
                            $btn = '<a href="' . route('admin.products.edit', $row->id) . '"
                            data-toggle="tooltip"
                            data-id="' . $row->id . '"
                            data-original-title="Edit"
                            class="edit btn btn-primary btn-sm editProduct">Edit</a>';
                        }
                        if (Auth::user()->can('product-delete')) {
                            $btn = $btn . ' <a href="javascript:void(0)"
                            data-toggle="tooltip"
                            data-id="' . $row->id . '"
                            data-action="' . route(
                                'admin.products.destroy',
                                ['product' => $row->id]
                            ) . '"
                            data-original-title="Delete"
                            class="btn btn-danger btn-sm deleteProduct">Delete</a>';
                        }

                        return $btn;
                    }
                )
                ->editColumn(
                    'categories',
                    function ($row) {
                        return $row->category->name ?? '';
                    }
                )
                ->editColumn(
                    'subCategories',
                    function ($row) {
                        return $row->subCategory->name ?? '';
                    }
                )
                //                ->editColumn(
                //                    'manufacturer',
                //                    function ($row) {
                //                        return $row->manufacturer->name ?? '';
                //                    }
                //                )

                ->rawColumns(['part_number', 'action', 'is_payable'])
                ->make(true);
        } else {
            $categories = Category::whereNull('parent_id')->get();
            $manufacturers = Manufacturer::all();
            $category = null;
            if ($request->category_id) {
                $category = Category::whereId($request->category_id)->first();
            }
            $subCategory = null;
            if ($request->subcategory_id) {
                $subCategory = Category::whereId($request->subcategory_id)->first();
            }
            $subSubCategory = null;
            if ($request->subsubcategory_id) {
                $subSubCategory = Category::whereId($request->subsubcategory_id)->first();
            }
            return view('admin.product.index', compact('categories', 'manufacturers', 'category', 'subCategory', 'subSubCategory'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        $manufacturers = Manufacturer::all();
        $product = new Product();
        $productDetail = new ProductDetail();

        return view('admin.product.form', compact(
            'product',
            'categories',
            'manufacturers',
            'productDetail'
        ));
    }

    /**
     * @return Application|RedirectResponse|Redirector
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::user()->id;

        $image = $request->image_file;
        if ($image) {
            $imageName = time() . '.webp';
            $imgFile = Image::make($image->getRealPath());
            $imgFile->trim();
            /*
            if ($imgFile->width() > 250) {
                $imgFile->resize(250, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            if ($imgFile->height() > 250) {
                $imgFile->resize(null, 250, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $canvas = Image::canvas(250, 250, '#ffffff');
            */
            $canvas = Image::canvas($imgFile->width(), $imgFile->height(), '#ffffff');
            $canvas->insert($imgFile, 'center')->encode('webp');
            Storage::put(config('constants.PRODUCT_IMAGE_PATH') . $imageName, $canvas->stream());
            $data['image_url'] = $imageName;
        }
        if ($request->datasheet_file) {
            $imageName = time() . '.' . $request->datasheet_file->extension();
            Storage::putFileAs('assets/datasheets/', $request->datasheet_file, $imageName);
            $data['datasheet_url'] = $imageName;
        }
        $product = Product::create($data);
        $product->productDetail()->create($data);

        if ($request->ajax()) {
            session()->flash('success', 'Product created successfully');
            return response()->json(['redirectUrl' => route('admin.products.index')]);
        }
        return redirect(route('admin.products.index'))->with('success', 'Product created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Application|Factory|View
     */
    public function edit(Product $product)
    {
        $categories = Category::whereNull('parent_id')->get();
        $subCategories = new Category();
        if ($product->subcategory_id) {
            $subCategories = Category::where('parent_id', $product->category_id)->get();
        }
        $subSubCategories = new Category();
        if ($product->subsubcategory_id) {
            $subSubCategories = Category::where('parent_id', $product->subcategory_id)->get();
        }

        $manufacturers = Manufacturer::all();
        $productDetail = $product->productDetail;

        return view('admin.product.form', compact(
            'product',
            'categories',
            'subCategories',
            'manufacturers',
            'productDetail',
            'subSubCategories',
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $image = $request->image_file;
        if ($image) {
            $imageName = time() . '.webp';
            $imgFile = Image::make($image->getRealPath());
            $imgFile->trim();
            /*
            if ($imgFile->width() > 250) {
                $imgFile->resize(250, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            if ($imgFile->height() > 250) {
                $imgFile->resize(null, 250, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $canvas = Image::canvas(250, 250, '#ffffff');
            */
            $canvas = Image::canvas($imgFile->width(), $imgFile->height(), '#ffffff');
            $canvas->insert($imgFile, 'center')->encode('webp');
            Storage::put(config('constants.PRODUCT_IMAGE_PATH') . $imageName, $canvas->stream());
            $data['image_url'] = $imageName;
        }
        if ($request->datasheet_file) {
            $imageName = time() . '.' . $request->datasheet_file->extension();
            Storage::putFileAs(
                'assets/datasheets/',
                $request->datasheet_file,
                $imageName
            );
            $data['datasheet_url'] = $imageName;
        }
        $product->update($data);
        $product->productDetail->update($data);
        if ($request->ajax()) {
            session()->flash('success', 'Product updated successfully');
            return response()->json(['redirectUrl' => route('admin.products.index')]);
        }
        return redirect(route('admin.products.index'))->with('success', 'Product updated successfully');
    }

    /**
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['success' => 'Product deleted successfully']);
    }

    /**
     * Get Product Sub Category.
     *
     * @return JsonResponse
     */
    public function getSubCategories(Request $request, $category_id)
    {
        if ($request->ajax() && $category_id) {
            $subCategories = Category::select(['id', 'name'])
                ->where('parent_id', $category_id)
                ->get();

            return response()->json(['subCategories' => $subCategories]);
        }
    }

    public function qtyPriceUpdatePage(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with(['category', 'subCategory', 'manufacturer']);
            if ($request->category_id) {
                $query = $query->where('category_id', $request->category_id);
            }
            if ($request->subcategory_id) {
                $query = $query->where('subcategory_id', $request->subcategory_id);
            }
            if ($request->subsubcategory_id) {
                $query = $query->where('subsubcategory_id', $request->subsubcategory_id);
            }
            if ($request->manufacturer_id) {
                $query = $query->where('manufacturer_id', $request->manufacturer_id);
            }
            if ($request->part_number) {
                $query = $query->where('part_number', $request->part_number);
            }
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('price_per_quantity', function ($row) {
                    return $row->formatted_price;
                })
                ->editColumn(
                    'categories',
                    function ($row) {
                        return $row->category->name ?? '';
                    }
                )
                ->editColumn(
                    'subCategories',
                    function ($row) {
                        return $row->subCategory->name ?? '';
                    }
                )
                ->editColumn(
                    'manufacturer',
                    function ($row) {
                        return $row->manufacturer->name ?? '';
                    }
                )
                ->editColumn(
                    'checkbox',
                    function ($row) {
                        return '<div class="form-check"><input type="checkbox" name="dpIds[]" class="form-check-input allPermissionCheckBox" value="' . $row->id . '"></div>';
                    }
                )
                ->rawColumns(['checkbox'])
                ->make(true);
        } else {
            // dd($request->all());
            $categories = Category::whereNull('parent_id')->get();
            $manufacturers = Manufacturer::all();
            $totalFiltered = 0;
            $category = null;
            if ($request->category_id) {
                $category = Category::whereId($request->category_id)->first();
                $totalFiltered++;
            }
            $subCategory = null;
            if ($request->subcategory_id) {
                $subCategory = Category::whereId($request->subcategory_id)->first();
                $totalFiltered++;
            }
            $subSubCategory = null;
            if ($request->subsubcategory_id) {
                $subSubCategory = Category::whereId($request->subsubcategory_id)->first();
                $totalFiltered++;
            }
            $manufacturer = null;
            if ($request->manufacturer_id) {
                $manufacturer = Manufacturer::whereId($request->manufacturer_id)->first();
                $totalFiltered++;
            }
            $partNumber = null;
            if ($request->part_number) {
                $partNumber = Product::where('part_number', $request->part_number)->first();
                $totalFiltered++;
            }
            $isFilter = 0;
            if ($category || $subCategory || $subSubCategory || $manufacturer || $partNumber) {
                $isFilter = 1;
            }
            return view('admin.product.qty-price-update', compact('categories', 'manufacturers', 'category', 'subCategory', 'subSubCategory', 'manufacturer', 'isFilter', 'totalFiltered'));
        }
    }

    public function qtyPriceUpdate(ProductQtyPriceRequest $request)
    {
        $update_qty = $request->update_qty;
        $update_price = $request->update_price;
        if ($update_qty <= 0 && $update_price <= 0) {
            return redirect(route('admin.products.qtyPriceUpdate'))->with('error', 'Update value should be more than zero!');
        }
        if ($request->isFilter) {
            $query = new Product;
            if ($request->category_id) {
                $query = $query->where('category_id', $request->category_id);
            }
            if ($request->subcategory_id) {
                $query = $query->where('subcategory_id', $request->subcategory_id);
            }
            if ($request->subsubcategory_id) {
                $query = $query->where('subsubcategory_id', $request->subsubcategory_id);
            }
            if ($request->manufacturer_id) {
                $query = $query->where('manufacturer_id', $request->manufacturer_id);
            }
            if ($request->part_number) {
                $query = $query->where('part_number', $request->part_number);
            }
            $productIds = $query->pluck('id')->toArray();
            if (count($productIds) > 0) {
                if ($update_qty > 0) {
                    $products = Product::whereIn('id', $productIds)->where('quantity', '>', 0)->update([
                        'quantity' => DB::raw("CEIL((quantity*({$update_qty})))"),
                    ]);

                    $products = Product::whereIn('id', $productIds)->where(function ($q) {
                        $q->where('quantity', '<=', 0)->orWhereNull('quantity');
                    })->update([
                        'quantity' => $update_qty,
                    ]);
                }
                if ($update_price > 0) {
                    $products = Product::whereIn('id', $productIds)->update([
                        'price_per_quantity' => DB::raw("(REPLACE(price_per_quantity, '$', '')*({$update_price}))"),
                    ]);
                }
            }
        } else {
            if ($update_qty > 0) {
                $products = Product::where('quantity', '>', 0)->update([
                    'quantity' => DB::raw("CEIL((quantity*({$update_qty})))"),
                ]);

                $products = Product::where(function ($q) {
                    $q->where('quantity', '<=', 0)->orWhereNull('quantity');
                })->update([
                    'quantity' => $update_qty,
                ]);
            }

            if ($update_price > 0) {
                $products = Product::query();
                $products = $products->update([
                    'price_per_quantity' => DB::raw("(REPLACE(price_per_quantity, '$', '')*({$update_price}))"),
                ]);
            }
        }
        return redirect(route('admin.products.qtyPriceUpdate'))->with('success', 'Updated successfully');
    }

    public function deleteAll(Request $request)
    {
        $query = new Product;
        if (is_array($request->ids) && count($request->ids)) {
            $query = $query->whereIn('id', $request->ids);
            $query->delete();
        } else if ($request->isFilter && ($request->category_id || $request->subcategory_id || $request->subsubcategory_id || $request->manufacturer_id || $request->part_number)) {
            if ($request->category_id) {
                $query = $query->where('category_id', $request->category_id);
            }
            if ($request->subcategory_id) {
                $query = $query->where('subcategory_id', $request->subcategory_id);
            }
            if ($request->subsubcategory_id) {
                $query = $query->where('subsubcategory_id', $request->subsubcategory_id);
            }
            if ($request->manufacturer_id) {
                $query = $query->where('manufacturer_id', $request->manufacturer_id);
            }
            if ($request->part_number) {
                $query = $query->where('part_number', $request->part_number);
            }
            $query->delete();
        }
        session()->flash('success', 'Products deleted successfully');
        return response()->json(['success' => 'Product deleted successfully']);
    }

    public function updateIsPayable(Request $request)
    {
        $productId = $request->input('productId');
        $isPayableValue = $request->input('isPayableValue');
        if ($isPayableValue == 0) {
            $isPayableValue = 1;
        } else {
            $isPayableValue = 0;
        }
        try {
            $product = Product::find($productId);

            $product->update([
                'is_payable' => $isPayableValue,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }
    public function updateAllPayable(Request $request)
    {
        // dd($request->all());
        $isPayableValue = $request->input('isPayableValue');
        $category_id = $request->input('category_id');
        $subcategory_id = $request->input('subcategory_id');
        $subsubcategory_id = $request->input('subsubcategory_id');
        $manufacturer_id = $request->input('manufacturer_id');
        $part_number = $request->input('part_number');
        $is_payable = $request->input('is_payable');

        $query = Product::query();
        if ($category_id) {
            $query->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $query->where('subcategory_id', $subcategory_id);
        }
        if ($subsubcategory_id) {
            $query = $query->where('subsubcategory_id', $subsubcategory_id);
        }
        if ($manufacturer_id) {
            $query->where('manufacturer_id', $manufacturer_id);
        }
        if ($part_number) {
            $query->where('part_number', $part_number);
        }
        $query->update(['is_payable' => $isPayableValue]);
        return response()->json(['success' => 'Update successful']);
    }
}
