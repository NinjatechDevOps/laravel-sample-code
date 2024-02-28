<?php

use App\Http\Resources\Frontend\NewsResource;
use App\Http\Resources\Frontend\TechnologyResource;
use App\Models\Category;
use App\Models\CmsContent;
use App\Models\CurrencyExchangeRate;
use App\Models\Manufacturer;
use App\Models\Setting;
use App\Services\Currency\Contracts\CurrentCurrency;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

if (!function_exists('popularManufacturers')) {
    function popularManufacturers()
    {
        return cache()->rememberForever(
            'popularManufacturers',
            function () {
                $manufacturerIds = Setting::where('setting_key', 'featured_manufacturers')
                    ->pluck('setting_value')
                    ->first();
                if ($manufacturerIds) {
                    // return Manufacturer::whereIn('id', $manufacturerIds)->get();
                    return Manufacturer::whereIn('id', $manufacturerIds)->orderByRaw('FIELD(id, ' . implode(',', $manufacturerIds) . ')')->get();
                }

                return [];
            }
        );
    }
}

if (!function_exists('categories')) {
    function categories()
    {
        return cache()->rememberForever(
            'categories',
            function () {
                return generateSubCategory(Category::where('no_of_products', '>', 0)->orderBy('name')->get());
            }
        );
    }
}


if (!function_exists('headerCategoryMenu')) {
    function headerCategoryMenu()
    {
        return cache()->rememberForever(
            'headerCategoryMenu',
            function () {
                return view('components.header-category-menu')->render();
            }
        );
    }
}

if (!function_exists('homePageSliderSection')) {
    function homePageSliderSection()
    {
        return cache()->rememberForever(
            'homePageSliderSection',
            function () {
                $cmsContent = CmsContent::fromSlug('home');
                $searchDescription = null;
                $searchPlaceholder = null;
                $sliderImages = [];
                if (isset($cmsContent->data['sections']) && is_array($cmsContent->data['sections'])) {
                    foreach ($cmsContent->data['sections'] as $sections) {
                        if ($sections['type'] == "home_search_box") {
                            if (isset($sections['fields']['sliders']) && @count(@$sections['fields']['sliders'])) {
                                $sliderImages = array_column($sections['fields']['sliders'], 'image');
                            }
                            $searchDescription = $sections['fields']['search_description'] ?? "";
                            $searchPlaceholder = $sections['fields']['placeholder'] ?? "";
                        }
                    }
                }
                return view('components.home-page-slider', compact('searchPlaceholder', 'searchDescription', 'sliderImages'))->render();
            }
        );
    }
}

if (!function_exists('generateSubCategory')) {
    function generateSubCategory($elements, $parentId = 0)
    {
        $branch = [];
        foreach ($elements as $key => $element) {
            if ($element['parent_id'] == $parentId) {
                $children = generateSubCategory($elements, $element['id']);
                if ($children) {
                    $element->children = $children;
                    $element->childrenCount = count($children);
                }
                $branch[$element['id']] = [
                    'name' => $element->name,
                    'slug' => $element->slug,
                    'icon_class' => $element->icon_class,
                    'image_url' => $element->image_url,
                    'parent_id' => $element->parent_id,
                    'children' => $element->children ?? [],
                ];
            }
        }

        return $branch;
    }
}

if (!function_exists('customProductAttribureValueString')) {
    function customProductAttribureValueString($string)
    {
        if (str_contains($string, 'http')) {
            $explodedStrings = explode(',', $string);
            $finalString = [];
            foreach ($explodedStrings as $explodedString) {
                $pipeString = explode('|', $explodedString);
                if (is_array($pipeString) && count($pipeString) == 2) {
                    $finalString[] = '<a href="' . $pipeString[1] . '">' .
                        $pipeString[0] . '<span class="icon-right-arrow"></span></a>';
                }
            }

            return implode('<br>', $finalString);
        }

        return $string;
    }
}

if (!function_exists('popularCategories')) {
    function popularCategories()
    {
        try {
            return cache()->rememberForever(
                'popularCategories',
                function () {

                    $categoryIds = Setting::where(
                        'setting_key',
                        'featured_categories'
                    )->pluck('setting_value')->first();

                    $categories = [];

                    foreach ($categoryIds as $categoryId) {
                        $category = Category::find($categoryId);

                        if ($category) {
                            $subcategories = $category->subcategories()
                                ->orderByDesc('no_of_products')
                                ->limit(3)
                                ->get();

                            $categories[] = [
                                'category' => $category,
                                'subcategories' => $subcategories,
                            ];
                        }
                    }

                    return $categories;
                }
            );
        } catch (Exception $e) {
            report($e);

            return [];
        }
    }
}

if (!function_exists('latestNews')) {
    function latestNews(): array
    {
        return cache()->rememberForever(
            'latestNews',
            function () {
                $setting = Setting::where('setting_key', 'featured_news')->first();
                $featuredNews = null;
                $latestNews = null;
                if ($setting && ($setting_value = $setting->setting_value) && is_array($setting_value) && implode(',', $setting_value)) {
                    $featuredNews = CmsContent::whereIn('id', $setting_value)
                        ->where('template', CmsContent::TYPE_NEWS)
                        ->orderByRaw('FIELD(id, ' . implode(',', $setting_value) . ')')
                        ->latest()
                        ->get();
                }
                $required = 4 - ($featuredNews ? $featuredNews->count() : 0);
                if ($required && $required > 0) {
                    $latestNews = CmsContent::where('template', CmsContent::TYPE_NEWS)
                        ->latest();
                    if ($featuredNews) {
                        $latestNews = $latestNews->whereNotIn('id', $featuredNews->pluck('id')->toArray());
                    }
                    $latestNews = $latestNews->take($required)->get();
                }
                $news = ($featuredNews && $latestNews) ? $featuredNews->merge($latestNews) : ($featuredNews ? $featuredNews : $latestNews);
                return NewsResource::collection($news)->jsonSerialize();
            }
        );
    }
}

if (!function_exists('currentCurrency')) {
    function currentCurrency()
    {
        return app(CurrentCurrency::class);
        //        return request()->getSession()->get('currency');
    }
}


if (!function_exists('exchangeRates')) {
    function exchangeRates()
    {
        return cache()->rememberForever(
            'exchangeRates',
            function () {
                return CurrencyExchangeRate::where('active', 1)->get();
            }
        );
    }
}

if (!function_exists('currencyObj')) {
    function currencyObj($abbr)
    {
        return cache()->rememberForever(
            'exchangeRates.' . $abbr,
            function () use ($abbr) {
                return CurrencyExchangeRate::where('abbr', $abbr)->first();
            }
        );
    }
}

if (!function_exists('getSetting')) {
    function getSetting($key, $field = 'value', $defaultValue = null)
    {
        $data = Setting::getValueByKey($key);
        if ($data) {
            if (!empty($field)) {
                return $data[$field];
            }
            return $data;
        }
        return $defaultValue;
    }
}

if (!function_exists('getContactEmail')) {
    function getContactEmail()
    {
        return getSetting('contact_email');
    }
}
if (!function_exists('getContactEmailAdmin')) {
    function getContactEmailAdmin($default = 'info@test.com')
    {
        return getSetting('inquiry_send_to', 'value', $default);
    }
}
if (!function_exists('siteName')) {
    function siteName()
    {
        return getSetting('site_name');
    }
}
if (!function_exists('siteLogo')) {
    function siteLogo()
    {
        return getSetting('site_logo');
    }
}

if (!function_exists('recentTechnologies')) {
    function recentTechnologies(): array
    {
        return cache()->rememberForever(
            'recentTechnologies',
            function () {
                $news = CmsContent::whereHas('manufacturers')
                    ->where('template', CmsContent::TYPE_TECHNOLOGY)
                    ->latest()
                    ->take(10)
                    ->get();
                return TechnologyResource::collection($news)->resolve();
            }
        );
    }
}

if (!function_exists('manufacturersCount')) {
    function manufacturersCount()
    {
        return cache()->rememberForever(
            'manufacturersCount',
            function () {
                return Manufacturer::count();
            }
        );
    }
}

if (!function_exists('getCategory')) {
    /**
     * @param $id
     * @return Category|null
     */
    function getCategory($id): ?Category
    {
        if (!$id) {
            return null;
        }
        return cache()->rememberForever(
            'category.' . $id,
            function () use ($id) {
                return Category::find($id);
            }
        );
    }
}

if (!function_exists('getManufacturer')) {
    /**
     * @param $id
     * @return Manufacturer|null
     */
    function getManufacturer($id): ?Manufacturer
    {
        if (!$id) {
            return null;
        }
        return cache()->rememberForever(
            'manufacturer.' . $id,
            function () use ($id) {
                return Manufacturer::find($id);
            }
        );
    }
}

if (!function_exists('metaReplace')) {
    /**
     * @param $content
     * @return string
     */
    function metaReplace($content): string
    {
        $content = str_replace('{WebsiteName}', siteName(), $content);
        $content = str_replace('{Website-name}', siteName(), $content);
        $content = str_replace('{year_now}', now()->format('Y'), $content);
        return $content;
    }
}


if (!function_exists('saveRecentSearch')) {
    function saveRecentSearch($section, $obj, $html = "")
    {
        $recent_searches = session('recent_searches');
        $recent_searchesArr = json_decode($recent_searches, true);
        if ($section && isset($obj->id)) {
            if (isset($recent_searchesArr[$section])) {
                $recent_search = $recent_searchesArr[$section];
                if (count($recent_search) > 4) {
                    array_multisort(array_column($recent_search, 'searched_at'), SORT_DESC, $recent_search);
                    array_splice($recent_search, 4);
                }
            }
            if (!$html) {
                if ($section == "products") {
                    $title = $obj->subSubCategory ? $obj->subSubCategory->name : ($obj->subCategory ? $obj->subCategory->name : $obj->category->name);

                    $html = '<a href="' . $obj->detail_url . '" class="search-products d-flex justify-content-between"><div class="product-name-img d-flex align-items-center"><div class="prdct-imgs"><img src="' . $obj->image_full_url . '" alt=""></div><div class="prdct-name-info"><div class="body1-text title-p">' . $title . ' <span>' . $obj->part_number . '</span></div></div> </div><div class="products-brand-name"><img src="' . ($obj->manufacturer->image_url ?? '') . '" alt=""></div></a>';
                }
            }
            $recent_searchesArr[$section][$obj->id] = [
                'id' => $obj->id,
                'html' => $html,
                'searched_at' => date('Y-m-d H:i:s')
            ];
        }
        session()->put('recent_searches', json_encode($recent_searchesArr));
        return true;
    }
}

if (!function_exists('getRecentSearchHtml')) {
    function getRecentSearchHtml()
    {
        $html = "";
        $recent_searches = session('recent_searches');
        $recent_searchesArr = json_decode($recent_searches, true);
        if (is_array($recent_searchesArr) && count($recent_searchesArr) > 0) {
            foreach ($recent_searchesArr as $section => $recent_search) {
                if (is_array($recent_search) && count($recent_search)) {
                    array_multisort(array_column($recent_search, 'searched_at'), SORT_DESC, $recent_search);
                    foreach ($recent_search as $rsearch) {
                        $html .= $rsearch['html'] ?? "";
                    }
                }
            }
        }
        return $html;
    }
}

if (!function_exists('downloadProductDatasheet')) {
    function downloadProductDatasheet($product)
    {
        if (isset($product->id)) {
            $fileName = time() . rand(111111, 999999);
            $oldImage = $fileName . '.pdf';
            $fileBasePath = Storage::disk('public')->path('assets/datasheets/');
            $filePath = Storage::disk('public')->path('assets/datasheets/' . $oldImage);

            try {
                Log::info('PDF Downloading....');

                $url = (str_starts_with($product->datasheet, '//') ? 'https:' : '') . $product->datasheet;
                $url = str_replace([' ', ',', ';', '_'], ['%20', '%2C', '%3B', '%5F'], $url);

                if (str_starts_with($url, 'https://media')) {
                    $command = 'cd ' . $fileBasePath . ' && curl "' . $url . '" -H \'sec-ch-ua: "Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"\' -H \'Referer: https://www.digikey.com/\' -H \'sec-ch-ua-mobile: ?0\' -H \'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36\' -H \'sec-ch-ua-platform: "Linux"\' --compressed -o ' . $oldImage;
                } else {
                    $command = 'cd ' . $fileBasePath . ' && curl "' . $url . '" -H \'sec-ch-ua: "Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"\' -H \'sec-ch-ua-mobile: ?0\' -H \'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36\' -H \'sec-ch-ua-platform: "Linux"\' --compressed -o ' . $oldImage;
                }
                $process = Process::fromShellCommandline($command);
                $process->setTimeout(5000);
                $process->run();

                if (Storage::disk('public')->fileExists('assets/datasheets/' . $oldImage)) {
                    $fileContent = file_get_contents($filePath);
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $fileContentType = $finfo->buffer($fileContent);
                    if (str_contains(strtolower($fileContentType), 'pdf')) {
                        Storage::put('assets/datasheets/' . $fileName . '.pdf', $fileContent);
                        Storage::disk('public')->delete('assets/datasheets/' . $oldImage);
                        $product->update(['datasheet_url' => $fileName . '.pdf']);
                        //      Log::info("PDF command ".$command);
                        Log::info("PDF completed for " . $product->id);
                    } else {
                        $product->update(['datasheet_url' => 'FAILED']);
                        Log::info("PDF failed due to content type issue for " . $product->id . '(' . $product->datasheet . ') ' . $fileContentType . " = Path = " . $filePath);
                    }
                } else {
                    $product->update(['datasheet_url' => 'FAILED']);
                }
            } catch (Exception $e) {
                $product->update(['datasheet_url' => 'FAILED']);
                Log::info("PDF failed for " . $product->id . '(' . $product->datasheet . ')' . $e->getMessage());
            }
        }
        return;
    }
}

if (!function_exists('noIndexPages')) {
    function noIndexPages()
    {
        $noIndexPages = ['request-quote', 'thank-you', 'about-us', 'terms-conditions', 'privacy-policy', 'shipping-policy', 'contact-us'];
        return $noIndexPages;
    }
}

if (!function_exists('getLineCardPdf')) {
    function getLineCardPdf()
    {
        return cache()->rememberForever(
            'getLineCardPdf',
            function () {
                $setting = Setting::where('setting_key', 'linecard_pdf')->first();
                if ($setting && isset($setting->setting_value['value']) && $setting->setting_value['value'] != "") {
                    return $setting->setting_value['value'];
                }
                return null;
            }
        );
    }
}

if (!function_exists('getFooterLogoAndUrl')) {
    function getFooterLogoAndUrl()
    {
        return cache()->rememberForever('footerLogoAndUrl', function () {
            $setting = Setting::where('setting_key', 'iso')->first();

            if ($setting && isset($setting->setting_value['value'])) {
                return $setting->setting_value['value'];
            }

            return null;
        });
    }
}

if (!function_exists('getStripeSettings')) {
    function getStripeSettings()
    {
        return cache()->rememberForever('getStripeSettings', function () {
            $stripeSetting = \App\Models\Setting::where('setting_key', 'stripe-setting')->first();
            if ($stripeSetting && isset($stripeSetting->setting_value['value']) && $stripeSetting->setting_value['value'] != "") {
                return json_decode($stripeSetting->setting_value, true)['value'] ?? [];
            }

            return [];
        });
    }
}