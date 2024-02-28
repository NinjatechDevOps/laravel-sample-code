<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\OrderProduct
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $product_id
 * @property string|null $description
 * @property string|null $quantity
 * @property string|null $price
 * @property string|null $target_price
 * @property string|null $part_number
 * @property string|null $manufacturer
 * @property int|null $manufacturer_id
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|OrderProduct newModelQuery()
 * @method static Builder|OrderProduct newQuery()
 * @method static Builder|OrderProduct query()
 * @method static Builder|OrderProduct whereCreatedAt($value)
 * @method static Builder|OrderProduct whereDeletedAt($value)
 * @method static Builder|OrderProduct whereDescription($value)
 * @method static Builder|OrderProduct whereId($value)
 * @method static Builder|OrderProduct whereManufacturer($value)
 * @method static Builder|OrderProduct whereManufacturerId($value)
 * @method static Builder|OrderProduct whereOrderId($value)
 * @method static Builder|OrderProduct wherePartNumber($value)
 * @method static Builder|OrderProduct wherePrice($value)
 * @method static Builder|OrderProduct whereProductId($value)
 * @method static Builder|OrderProduct whereQuantity($value)
 * @method static Builder|OrderProduct whereTargetPrice($value)
 * @method static Builder|OrderProduct whereUpdatedAt($value)
 * @property-read Order $order
 * @property-read Product|null $product
 * @method static Builder|OrderProduct onlyTrashed()
 * @method static Builder|OrderProduct withTrashed()
 * @method static Builder|OrderProduct withoutTrashed()
 * @property-read int|string $formated_target_price
 * @property-read mixed $formated_total_price
 * @mixin Eloquent
 */
class OrderProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'part_number',
        'manufacturer_name',
        'quantity',
        'price',
        'target_price',
    ];

    /**
     * Relation to Order
     *
     * @return BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relation to Product
     *
     * @return mixed
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get actual price of product for calculation
     *
     * @return int|string
     */
    public function getFormatedTargetPriceAttribute()
    {
        return number_format($this->target_price, 2);
    }

    public function getFormatedTotalPriceAttribute()
    {
        return number_format($this->target_price * $this->quantity, 2);
    }
}
