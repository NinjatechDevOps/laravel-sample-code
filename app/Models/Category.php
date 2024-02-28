<?php

namespace App\Models;

use Cache;
use Eloquent;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property int|null $parent_id
 * @property int|null $product_counts
 * @property string|null $description
 * @property string|null $long_description
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $image
 * @property int|null $is_feature
 * @property int|null $import_batch_id
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $image_url
 * @property-read Category|null $parent
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, Product> $subProducts
 * @property-read int|null $sub_products_count
 * @property-read Collection<int, Category> $subcategories
 * @property-read int|null $subcategories_count
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category query()
 * @method static Builder|Category whereCreatedAt($value)
 * @method static Builder|Category whereCreatedBy($value)
 * @method static Builder|Category whereDescription($value)
 * @method static Builder|Category whereId($value)
 * @method static Builder|Category whereImage($value)
 * @method static Builder|Category whereImportBatchId($value)
 * @method static Builder|Category whereIsFeature($value)
 * @method static Builder|Category whereLongDescription($value)
 * @method static Builder|Category whereMetaDescription($value)
 * @method static Builder|Category whereMetaTitle($value)
 * @method static Builder|Category whereName($value)
 * @method static Builder|Category whereParentId($value)
 * @method static Builder|Category whereProductCounts($value)
 * @method static Builder|Category whereSlug($value)
 * @method static Builder|Category whereUpdatedAt($value)
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @property string|null $icon_name
 * @property-read Collection<int, CmsContent> $cmsContents
 * @property-read int|null $cms_contents_count
 * @property-read mixed $icon_class
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Category> $subcategories
 * @method static Builder|Category whereIconName($value)
 * @property-read Collection<int, CmsContent> $cmsContents
 * @property-read string|string[] $meta_description_parsed
 * @property-read string|string[] $meta_title_parsed
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Product> $subProducts
 * @property-read Collection<int, Product> $subSubProducts
 * @property-read int|null $sub_sub_products_count
 * @property-read Collection<int, Category> $subcategories
 * @mixin Eloquent
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'product_counts',
        'description',
        'long_description',
        'meta_title',
        'image',
        'meta_description',
        'import_batch_id',
        'created_by',
        'icon_name',
    ];

    /**
     * Updating value via boot
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(
            function (Category $category) {
                if (!$category->slug) {
                    $category->slug = Str::slug($category->name);
                }
            }
        );

        static::saved(
            function (Category $category) {
                $category->removeCache();
            }
        );
        static::deleted(
            function (Category $category) {
                $category->removeCache();
            }
        );
        static::deleting(function ($category) {
            $category->subcategories()->each(function ($subcategory) {
                $subcategory->delete();
            });
        });
    }

    public function removeCache()
    {
        cache()->forget('categories');
        cache()->forget('popularCategories');
        cache()->forget('headerCategoryMenu');
        cache()->forget('category.' . $this->id);
    }


    /**
     * @return string|string[]
     */
    public function getMetaTitleParsedAttribute()
    {
        return metaReplace($this->meta_title
            ?? $this->name . ' - Distributor - {Website-name}');
    }

    /**
     * @return string|string[]
     */
    public function getMetaDescriptionParsedAttribute()
    {
        if(strip_tags($this->meta_description)) {
            return metaReplace(strip_tags($this->meta_description));
        }
        return metaReplace($this->name . ' ➜ Find and buy '.$this->name.' in our online store - Large selection and affordable prices - Supplier {Website-name}');
    }

    /**
     * To get image full URL
     *
     * @return Application|UrlGenerator|string
     */
    public function getImageUrlAttribute()
    {
        if ($this->image != '' && Storage::exists(config('constants.CATEGORY_IMAGE_PATH').$this->image)) {
           return Storage::url(config('constants.CATEGORY_IMAGE_PATH')) . $this->image;
        } else {
            return url(config('constants.DEFAULT_CATEGORY_IMAGE'));
        }
    }

    /**
     * To get icon class
     *
     * @return string|null
     */
    public function getIconClassAttribute()
    {
        return $this->icon_name && $this->icon_name != '' ? $this->icon_name : 'icon-uncategorized';
    }

    /**
     * Relation with Sub Category (Self Relation)
     *
     * @return HasMany
     */
    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Relation with Patent Category (Self Relation)
     *
     * @return HasOne
     */
    public function parent()
    {
        return $this->hasOne(Category::class, 'id', 'parent_id');
    }

    /**
     * Relation with Product (Where this category stored as main category)
     *
     * @return HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relation with Sub Products
     *
     * @return HasMany
     */
    public function subProducts()
    {
        return $this->hasMany(Product::class, 'subcategory_id');
    }

    /**
     * Relation with Sub Products
     *
     * @return HasMany
     */
    public function subSubProducts()
    {
        return $this->hasMany(Product::class, 'subsubcategory_id');
    }

    /**
     * Relation with CMS Contents
     *
     * @return MorphToMany
     */
    public function cmsContents()
    {
        return $this->morphToMany(CmsContent::class, 'relatable');
    }

    public function getDetailUrlAttribute(): string
    {
        $parent = $this->parent;
        if (! $parent) {
            $link = route('categories.show', [$this->slug]);
        } elseif ($parent && $mostParent = $parent->parent) {
            $link = route('subSubCategories.show', [$mostParent->slug, $parent->slug, $this->slug]);
        } else {
            $link = route('subcategories.show', [$parent->slug, $this->slug]);
        }
        return $link;
    }

}
