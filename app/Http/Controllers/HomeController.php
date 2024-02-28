<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategorySearchResource;
use App\Http\Resources\Frontend\NewsResource;
use App\Http\Resources\Frontend\TechnologyResource;
use App\Http\Resources\ManufacturerSearchResource;
use App\Http\Resources\NewsSearchResource;
use App\Http\Resources\ProductSearchResource;
use App\Models\Category;
use App\Models\CmsContent;
use App\Models\Manufacturer;
use App\Models\NewsCategory;
use App\Models\Product;
use App\Services\NewsSearch;
use App\Services\ProductSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class HomeController
 *
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //    $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        $cmsContent = CmsContent::fromSlug('home');
        $searchPlaceholder = null;
        $sliderImages = [];
        if (isset($cmsContent->data['sections']) && is_array($cmsContent->data['sections'])) {
            foreach ($cmsContent->data['sections'] as $sections) {
                if ($sections['type'] == "home_search_box") {
                    $searchPlaceholder = $sections['fields']['placeholder'] ?? "";
                }
            }
        }

        $latestNews = latestNews();
        $recentTechnologies = recentTechnologies();
        return view('frontend.home', compact('latestNews', 'recentTechnologies', 'cmsContent', 'searchPlaceholder'));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function search(Request $request)
    {
        if (config('scout.enable')) {
            $products = Product::search([
                'bool' => [
                    'should' => [
                        [
                            'query_string' => [
                                'fields' => [
                                    'part_number.keyword',
                                    //   'tags.keyword',
                                ],
                                'query' => addslashes(strtoupper($request->q)),
                                'boost' => 2.0,
                            ],
                        ],
                        [
                            'query_string' => [
                                'fields' => [
                                    'part_number.keyword',
                                    //   'tags.keyword',
                                ],
                                'query' => '*' . addslashes(strtoupper($request->q)) . '*', // Input string gets quoted during the Zend_Json_Encoder::encode process
                                'boost' => 1.0,
                            ],
                        ],
                    ],
                    //    'minimum_should_match' => 1,
                ]
            ])->take(10)->get();

        } else {
            $products = ProductSearch::search(['s' => $request->q], 10);
        }

        $categories = Category::where('no_of_products', '>', 0)->where(
            'name',
            'like',
            '%' . $request->q . '%'
        )->take(3)->get();
        $manufacturers = Manufacturer::where('no_of_products', '>', 0)->where(
            'name',
            'like',
            '%' . $request->q . '%'
        )->take(3)->get();

        $news = CmsContent::where(
            'title',
            'like',
            '%' . $request->q . '%'
        )->where('template', CmsContent::TYPE_NEWS)->take(3)->get();

        $technologies = CmsContent::where(
            'title',
            'like',
            '%' . $request->q . '%'
        )->where('template', CmsContent::TYPE_TECHNOLOGY)->take(3)->get();

        return [
            'products' => ProductSearchResource::collection($products),
            'categories' => CategorySearchResource::collection($categories),
            'manufacturers' => ManufacturerSearchResource::collection($manufacturers),
            'news' => NewsSearchResource::collection($news),
            'technologies' => NewsSearchResource::collection($technologies),
        ];
    }

    /**
     * @param Request $request
     * @return Application|Factory|View|\Illuminate\Http\RedirectResponse
     */
    public function searchResults(Request $request)
    {
        if ($request->q) {
            if ($product = Product::where('part_number', $request->q)->first()) {
                return redirect()->to($product->detail_url);
            } else if ($manufacturer = Manufacturer::where('name', $request->q)->first()) {
                return redirect()->to($manufacturer->detail_url);
            }
        }

        return view('frontend.search-results');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function searchProduct(Request $request)
    {
        $products = ProductSearch::search(['s' => $request->q], 10);

        return response()->json([
            'data' => view()->make('frontend.search.product', compact('products'))->render(),
            'pagination' => (string) $products->appends($request->all())->links(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function searchCategory(Request $request)
    {
        $categories = Category::where('no_of_products', '>', 0)->where(
            'name',
            'like',
            '%' . $request->q . '%'
        )->orderBy('name')->paginate(10);

        return response()->json([
            'data' => view()->make('frontend.search.category', compact('categories'))->render(),
            'pagination' => (string) $categories->appends($request->all())->links(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function searchManufacturer(Request $request)
    {
        $manufacturers = Manufacturer::where('no_of_products', '>', 0)->where(
            'name',
            'like',
            '%' . $request->q . '%'
        )->orderBy('name')->paginate(12);

        return response()->json([
            'data' => view()->make('frontend.search.manufacturer', compact('manufacturers'))->render(),
            'pagination' => (string) $manufacturers->appends($request->all())->links(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function searchNews(Request $request)
    {
        $news = CmsContent::where(
            'title',
            'like',
            '%' . $request->q . '%'
        )->where('template', CmsContent::TYPE_NEWS)->orderBy('title')->paginate(12);

        // $news =  NewsResource::collection($newsData)->jsonSerialize();


        return response()->json([
            'data' => view()->make('frontend.search.news', compact('news'))->render(),
            'pagination' => (string) $news->appends($request->all())->links(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function searchTechnology(Request $request)
    {
        $technologies = CmsContent::where(
            'title',
            'like',
            '%' . $request->q . '%'
        )->where('template', CmsContent::TYPE_TECHNOLOGY)->paginate(10);

        $recentTechnologies =  TechnologyResource::collection($technologies)->resolve();
        return response()->json([
            'data' => view()->make('frontend.search.technology', compact('recentTechnologies'))->render(),
            'pagination' => (string) $technologies->appends($request->all())->links(),
        ]);
    }

    /**
     * Return HTML of header
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function productMenu(Request $request)
    {
        return response()->json(['data' => ['menu_html' => headerCategoryMenu()]]);
    }

    public function searchPartNumber(Request $request)
    {
        $partNumbers = ProductSearch::search(['part_number_like' => $request->q, 4]);

        return response()->json($partNumbers->pluck('part_number', 'id'));
    }

    /**
     * @param Request $request
     * @param $slug
     * @return Application|Factory|View
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function singleSlug(Request $request, $slug, $page = 1)
    {
        if ($slug == 'images' || $slug == 'js') {
            abort(404);
        }

        $allCmsSlug = CmsContent::allPageSlug();
        if (in_array($slug, $allCmsSlug)) {
            $cmsContent = CmsContent::fromSlug($slug);
            if ($cmsContent) {
                return view('frontend.cms.' . $cmsContent->template, compact('cmsContent'));
            }
        }

        $newsCategory = NewsCategory::where('slug', $slug)->first();
        if ($newsCategory) {
            if (!$newsCategory->parent_id) {
                $parentCategory = $newsCategory;
                $newsCategories = NewsCategory::whereNull('parent_id')->get();
                $subCategories = NewsCategory::with(['cmsContents'])->where('parent_id', $parentCategory->id)->get();
                $metaDes = implode(', ✔️', $newsCategories->pluck('name')->toArray());
                return view('frontend.news.index', compact('newsCategories', 'subCategories', 'parentCategory', 'metaDes'));
            }

            $news = NewsSearch::search([
                'news_category_id' => $newsCategory->id,
            ], 20);
            $news_html = view()->make('frontend.news.list', compact('news', 'newsCategory'))->render();
            $news_pagination = (string)$news->appends($request->all())->links();
            return view('frontend.news.category', compact('newsCategory', 'news_html', 'news_pagination'));
        }

        /** @var CmsContent $cmsContent */
        $cmsContent = CmsContent::where('slug', $slug)->first();
        if ($cmsContent) {
            if ($cmsContent->template == CmsContent::TYPE_TECHNOLOGY) {
                $manufacturer = $cmsContent->manufacturer;
                $category = $cmsContent->category;
                $recentTechnologies = [];
                if ($manufacturer) {
                    $technologies = $manufacturer->cmsContents()
                        ->where('template', CmsContent::TYPE_TECHNOLOGY)
                        ->latest()
                        ->take(10)
                        ->get();
                    $recentTechnologies = TechnologyResource::collection($technologies)->resolve();
                }

                $products = ProductSearch::search([
                    'part_number_like' => $request->s,
                    'manufacturer_id' => (($cmsContent->load_products_by == 1 || $cmsContent->load_products_by == 3) && $manufacturer) ? $manufacturer->id : "",
                    'category_id' => (($cmsContent->load_products_by == 1 || $cmsContent->load_products_by == 2) && $category) ? $category->id : "",
                    'sort_on' => ($request->sort_on && $request->sort_by) ? $request->sort_on : "",
                    'sort_by' => ($request->sort_on && $request->sort_by) ? $request->sort_by : "",
                ], 5);
                $products_html = view()->make('frontend.manufacturer.products', compact('products'))->render();
                $products_mobile_html = view()->make('frontend.manufacturer.mobile-products', compact('products'))->render();
                $products_pagination = (string)$products->appends($request->all())->links();

                $sort_icon_cls_quantity = "icon-sorting-arrow";
                $sort_by_quantity = "asc";
                if ($request->sort_on == "quantity") {
                    if ($request->sort_by == "asc") {
                        $sort_icon_cls_quantity = "icon-up-arrow";
                        $sort_by_quantity = "desc";
                    } else {
                        $sort_icon_cls_quantity = "icon-down-arrow";
                    }
                }

                $sort_icon_cls_price = "icon-sorting-arrow";
                $sort_by_price = "asc";
                if ($request->sort_on == "price_per_quantity") {
                    if ($request->sort_by == "asc") {
                        $sort_icon_cls_price = "icon-up-arrow";
                        $sort_by_price = "desc";
                    } else {
                        $sort_icon_cls_price = "icon-down-arrow";
                    }
                }
                return view('frontend.news.product', compact('cmsContent', 'manufacturer', 'category', 'recentTechnologies', 'products_html', 'products_mobile_html', 'products_pagination', 'sort_by_quantity', 'sort_by_price', 'sort_icon_cls_quantity', 'sort_icon_cls_price', 'page'));
            }
            if ($cmsContent->template == CmsContent::TYPE_NEWS) {
                $latestNews = latestNews();
                $newsCategory = $cmsContent->newsCategory;
                return view('frontend.news.view', compact('newsCategory', 'cmsContent', 'latestNews'));
            }
        }

        abort(404);
    }

    /**
     * @param $slug
     * @param $subSlug
     * @return Application|Factory|View
     */
    public function twoSlug($slug, $subSlug)
    {
        if ($slug == 'images' || $slug == 'js') {
            abort(404);
        }
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            $subCategory = Category::where('parent_id', $category->id)
                ->where('slug', $subSlug)
                ->first();
            if ($subCategory) {
                $products = Product::where('subcategory_id', $subCategory->id);
                $products = $products->paginate(5);

                return view('frontend.single-subcategory', compact('subCategory'));
            }
        }
        abort(404);
    }

    /**
     * @param Request $request
     * @param string $text
     * @return Application|Factory|View
     */
    public function generateLogo(Request $request, $text = 'Test')
    {

        $font = public_path('font/Typographica-Blp5.ttf');

        $font_size = 30;

        $setting = isset($request->s) ? $request->s : "E6EAF2_112A5A_240_132";
        $setting = explode("_", $setting);

        $img = array();

        switch ($n = count($setting)) {
            case $n > 4 :
            case 3:
                $setting[3] = $setting[2];
            case 4:
                $img['width'] = (int)$setting[2];
                $img['height'] = (int)$setting[3];
            case 2:
                $img['background'] = $setting[0];
                $img['color'] = $setting[1];
                break;
            default:
                list($img['background'], $img['color'], $img['width'], $img['height']) = array('F', '0', 100, 100);
                break;
        }
        $background = explode(",", $this->hex2rgb($img['background']));
        $textColorRgb = explode(",", $this->hex2rgb($img['color']));
        $width = empty($img['width']) ? 100 : $img['width'];
        $height = empty($img['height']) ? 100 : $img['height'];

        $text = (string)isset($text) ? urldecode($text) : $width . " x " . $height;
        $explodeText = preg_split("/(\s|\-|\.)/", $text);

        $maxStringTo = 11;
        $final = '';
        if (strlen($text) <= $maxStringTo) {
            $final = $text;
        } else if (count($explodeText) > 1 && (strlen($explodeText[0]) + strlen($explodeText[1]) + 1) <= $maxStringTo) {
            $final = $explodeText[0] . ($explodeText[1] != '&' ? ' ' . $explodeText[1] : '');
        } else if (strlen($explodeText[0]) <= $maxStringTo) {
            $final = $explodeText[0];
        } else {
            $words = preg_split("/(\s|\-|\.)/", $text);
            foreach ($words as $w) {
                $final .= substr($w, 0, 1);
            }
        }
        $text = substr($final, 0, $maxStringTo);

        $image = @imagecreate($width, $height) or die("Cannot Initialize new GD image stream");

        $background_color = imagecolorallocate($image, $background[0], $background[1], $background[2]);

        $bounding_box_size = imagettfbbox($font_size, 0, $font, $text);
        $text_width = $bounding_box_size[2] - $bounding_box_size[0];
        $text_height = $bounding_box_size[7] - $bounding_box_size[1];

        $x = ceil(($width - $text_width) / 2);
        $y = ceil(($height - $text_height) / 2);

        $text_color = imagecolorallocate($image, $textColorRgb[0], $textColorRgb[1], $textColorRgb[2]);
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $font, $text);

        //   imagerectangle($image, 0, 0, 239, 131, $text_color);


        header('Content-Type: image/png');
        imagepng($image);

        imagedestroy($image);

        exit;
    }

    function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        switch (strlen($hex)) {
            case 1:
                $hex = $hex . $hex;
            case 2:
                $r = hexdec($hex);
                $g = hexdec($hex);
                $b = hexdec($hex);
                break;
            case 3:
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
                break;
            default:
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                break;
        }

        $rgb = array($r, $g, $b);
        return implode(",", $rgb);
    }
}
