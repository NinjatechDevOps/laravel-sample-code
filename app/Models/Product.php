<?php

namespace App\Models;

use App\Services\Currency\Contracts\CurrentCurrency;
use Eloquent;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Storage;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $part_number
 * @property int|null $category_id
 * @property int|null $subcategory_id
 * @property int|null $manufacturer_id
 * @property string|null $tags
 * @property string|null $price_per_quantity
 * @property string|null $description
 * @property string|null $short_description
 * @property string|null $rohs_status
 * @property string|null $quantity
 * @property string|null $datasheet
 * @property string|null $datasheet_url
 * @property string|null $image
 * @property string|null $image_url
 * @property int|null $import_batch_id
 * @property int|null $row_number
 * @property int|null $created_by
 * @property int $is_feature
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read Manufacturer|null $manufacturer
 * @property-read ProductDetail|null $productDetail
 * @property-read Category|null $subCategory
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product query()
 * @method static Builder|Product whereCategoryId($value)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereCreatedBy($value)
 * @method static Builder|Product whereDatasheet($value)
 * @method static Builder|Product whereDatasheetUrl($value)
 * @method static Builder|Product whereDescription($value)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereImage($value)
 * @method static Builder|Product whereImageUrl($value)
 * @method static Builder|Product whereImportBatchId($value)
 * @method static Builder|Product whereIsFeature($value)
 * @method static Builder|Product whereManufacturerId($value)
 * @method static Builder|Product wherePartNumber($value)
 * @method static Builder|Product wherePricePerQuantity($value)
 * @method static Builder|Product whereQuantity($value)
 * @method static Builder|Product whereRohsStatus($value)
 * @method static Builder|Product whereRowNumber($value)
 * @method static Builder|Product whereShortDescription($value)
 * @method static Builder|Product whereSubcategoryId($value)
 * @method static Builder|Product whereTags($value)
 * @method static Builder|Product whereUpdatedAt($value)
 * @method static Builder|Product onlyTrashed()
 * @method static Builder|Product withTrashed()
 * @method static Builder|Product withoutTrashed()
 * @property int|null $subsubcategory_id
 * @property int|null $updated_import_batch_id
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Collection<int, CmsContent> $cmsContents
 * @property-read int|null $cms_contents_count
 * @property-read mixed $datasheet_full_url
 * @property-read mixed $formatted_price
 * @property-read mixed $full_name
 * @property-read mixed $image_full_url
 * @property-read mixed $name
 * @property-read mixed $price
 * @property-read Category|null $subSubCategory
 * @method static Builder|Product whereDeletedAt($value)
 * @method static Builder|Product whereSubsubcategoryId($value)
 * @method static Builder|Product whereUpdatedImportBatchId($value)
 * @property-read Collection<int, Activity> $activities
 * @property-read Collection<int, CmsContent> $cmsContents
 * @property-read Category|null $cache_category
 * @property-read Manufacturer|null $cache_manufacturer
 * @property-read Category|null $cache_sub_category
 * @property-read Category|null $cache_sub_sub_category
 * @property-read Manufacturer|null $encoded_part_number
 * @property-read string|null $formatted_frontend_price
 * @property-read string|null $frontend_price
 * @property-read string|string[] $meta_description_parsed
 * @property-read string|string[] $meta_title_parsed
 * @mixin Eloquent
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use Searchable;

    protected static $recordEvents = ['deleted', 'updated'];

    protected $fillable = [
        'part_number',
        'category_id',
        'subcategory_id',
        'subsubcategory_id',
        'tags',
        'price_per_quantity',
        'manufacturer_id',
        'description',
        'short_description',
        'quantity',
        'datasheet',
        'datasheet_url',
        'image',
        'image_url',
        'import_batch_id',
        'updated_import_batch_id',
        'row_number',
        'is_payable',
        'created_by',
    ];

    public function searchableAs(): string
    {
        return 'products_models_index';
    }

    public function searchableFields(): string
    {
        return 'part_number,tags';
    }

    /**
     * @return array{id: mixed}
     */
    public function toSearchableArray(): array
    {
        return $this->only('deleted_at', 'part_number', 'tags', 'manufacturer_id', 'category_id', 'subcategory_id', 'subsubcategory_id');
    }

    /**
     * Relation to Product Detail (To get Attribute - Value)
     *
     * @return HasOne
     */
    public function productDetail()
    {
        return $this->hasOne(ProductDetail::class);
    }

    /**
     * Relation to Category
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relation to Sub Category
     *
     * @return BelongsTo
     */
    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    /**
     * Relation to Sub Sub Category
     *
     * @return BelongsTo
     */
    public function subSubCategory()
    {
        return $this->belongsTo(Category::class, 'subsubcategory_id');
    }

    /**
     * Relation to Manufacturer
     *
     * @return BelongsTo
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * Relation to CMS Contents
     *
     * @return MorphToMany
     */
    public function cmsContents()
    {
        return $this->morphToMany(CmsContent::class, 'relatable');
    }

    /**
     * Get Parsed Meta Title
     *
     * @return string
     */
    public function getMetaTitleParsedAttribute(): string
    {
        return metaReplace($this->part_number . ' - {Website-name}');
    }

    /**
     * Get Parsed Meta Description
     *
     * @return string
     */
    public function getMetaDescriptionParsedAttribute(): string
    {
        return metaReplace('Buy【' . $this->part_number . '】➜ ' . $this->full_name . ': ' . $this->description . ' - {Website-name}.');
    }

    /**
     * Get actual price of product for calculation
     *
     * @return float|null
     */
    public function getPriceAttribute(): float|null
    {
        if ($this->price_per_quantity) {
            $price = str_replace([',', '$'], '', $this->price_per_quantity);
            return (float)$price;
        }
        return $this->price_per_quantity;
    }

    /**
     * Get Formatted Price
     *
     * @return string|null
     */
    public function getFormattedPriceAttribute()
    {
        return $this->price ? '$' . $this->price : null;
    }

    /**
     * Get Formatted Price
     *
     * @return string|null
     */
    public function getFormattedFrontendPriceAttribute()
    {
        $currency = app(CurrentCurrency::class);
        return $this->price ? $currency->symbol . $this->getFrontendPriceAttribute($currency) : null;
    }

    /**
     * Get Formatted Price
     *
     * @param null $currency
     * @return string|null
     */
    public function getFrontendPriceAttribute($currency = null)
    {
        if (!$currency) {
            $currency = app(CurrentCurrency::class);
        }
        return $this->price ? number_format(($this->price * $currency->rate), 2) : null;
    }

    /**
     * Get full  URL for Image
     *
     * @return Application|UrlGenerator|string
     */
    public function getImageFullUrlAttribute()
    {
        if ($this->image_url && $this->image_url !== 'FAILED') {
            return Storage::url(config('constants.PRODUCT_IMAGE_PATH') . $this->image_url);
        }
        else if (Storage::exists(config('constants.PRODUCT_IMAGE_PATH').$this->part_number.".webp")) {
            return Storage::url(config('constants.PRODUCT_IMAGE_PATH') . $this->part_number.".webp");
        }
        else if ($this->subcategory_id && $this->subCategory?->image) {
            return $this->subCategory->image_url;
        }
        else if ($this->category_id && $this->category?->image) {
            return $this->category->image_url;
        }
        return url(config('constants.DEFAULT_PRODUCT_IMAGE'));
    }

    /**
     * Get URL for Datasheet
     *
     * @return string|null
     */
    public function getDatasheetFullUrlAttribute()
    {
        return ($this->datasheet_url && $this->datasheet_url != 'FAILED')
            ? Storage::url('assets/datasheets/' . $this->datasheet_url)
            : null;
    }

    public function getDatasheetIframeUrlAttribute()
    {
        return ($this->datasheet_url && $this->datasheet_url != 'FAILED')
            ? Storage::url('assets/datasheets/' . $this->datasheet_url)
            : $this->datasheet;
    }

    /**
     * Get name of Product via Last Category
     *
     * @return string
     */
    public function getNameAttribute()
    {
        if ($category = $this->cache_sub_sub_category) {
            return $category->name;
        }
        if ($category = $this->cache_sub_category) {
            return $category->name;
        }
        if ($category = $this->cache_category) {
            return $category->name;
        }
        return '';
    }

    /**
     * To get Full Name of Product (Manufacturer + Last Category Name)
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return (($manufacturer = $this->cache_manufacturer) ? $manufacturer->name . ' ' : '') . $this->name;
    }

    /**
     * Setting for Activity Log
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(
                fn(string $eventName) => "Product has been {$eventName}"
            );
    }

    /**
     * @return string
     */
    public function getEncodedPartNumberAttribute()
    {
        return str_replace(['?','#'],['%3F','%23'],$this->part_number);
        // return urlencode(str_replace(' ', '%20', $this->part_number));
    }

    /**
     * @return Manufacturer|null
     */
    public function getCacheManufacturerAttribute()
    {
        return getManufacturer($this->manufacturer_id);
    }

    /**
     * @return Category|null
     */
    public function getCacheCategoryAttribute()
    {
        return getCategory($this->category_id);
    }

    /**
     * @return Category|null
     */
    public function getCacheSubCategoryAttribute()
    {
        return getCategory($this->subcategory_id);
    }

    /**
     * @return Category|null
     */
    public function getCacheSubSubCategoryAttribute()
    {
        return getCategory($this->subsubcategory_id);
    }

    public function getDetailUrlAttribute(): string
    {
        return route('products.show', $this->encoded_part_number);
    }

    public function getStrictImageFullUrlAttribute(): ?string
    {
        if ($this->image_url && $this->image_url !== 'FAILED') {
            return Storage::url(config('constants.PRODUCT_IMAGE_PATH') . $this->image_url);
        }
        return null;
    }
}
