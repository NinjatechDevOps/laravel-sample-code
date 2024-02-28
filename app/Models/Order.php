<?php

namespace App\Models;

use App\Mail\OrderCreated;
use App\Mail\QuoteCreated;
use App\Notifications\ThankYouToCustomerQuote;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property int $user_address_id
 * @property string|null $ip
 * @property string|null $device
 * @property string|null $status
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, OrderProduct> $orderProducts
 * @property-read int|null $order_products_count
 * @property-read User $user
 * @property-read UserAddress $userAddress
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereDevice($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereIp($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserAddressId($value)
 * @method static Builder|Order whereUserId($value)
 * @property-read Collection<int, OrderProduct> $orderProducts
 * @method static Builder|Order onlyTrashed()
 * @method static Builder|Order withTrashed()
 * @method static Builder|Order withoutTrashed()
 * @property string|null $message
 * @property-read Collection<int, OrderFile> $orderFiles
 * @property-read int|null $order_files_count
 * @property-read Collection<int, OrderProduct> $orderProducts
 * @method static Builder|Order whereMessage($value)
 * @property int $currency_exchange_rate_id
 * @property string $total_amount
 * @property string $rate
 * @property int $view
 * @property-read \App\Models\CurrencyExchangeRate $currencyExchangeRate
 * @property-read string|null $total
 * @property-read float|int $total_with_order_currency
 * @property-read Collection<int, \App\Models\OrderFile> $orderFiles
 * @property-read Collection<int, \App\Models\OrderProduct> $orderProducts
 * @method static Builder|Order whereCurrencyExchangeRateId($value)
 * @method static Builder|Order whereRate($value)
 * @method static Builder|Order whereTotalAmount($value)
 * @method static Builder|Order whereView($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_QUOTE = 'quote';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'currency_exchange_rate_id',
        'payment_method_id',
        'rate',
        'total_amount',
        'ip',
        'device',
        'status',
        'message',
        'stripe_payment_response',
    ];

    /**
     * Update Value via boot method
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(
            function (Order $order) {
                if (!$order->ip) {
                    $order->ip = request()->ip();
                }
            }
        );
    }

    /**
     * Relation to User
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to User Address
     *
     * @return BelongsTo
     */
    public function userAddress()
    {
        return $this->belongsTo(UserAddress::class);
    }

 /**
     * Relation to Rate
     *
     * @return BelongsTo
     */
    public function currencyExchangeRate()
    {
        return $this->belongsTo(CurrencyExchangeRate::class);
    }

    /**
     * Relation to Products
     *
     * @return HasMany
     */
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * Relation to Payments
     *
     * @return HasMany
     */
    public function orderPayments()
    {
        return $this->hasMany(OrderPayment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relation to File
     *
     * @return HasMany
     */
    public function orderFiles()
    {
        return $this->hasMany(OrderFile::class);
    }

    /**
     * Get Formatted Price
     *
     * @return string|null
     */
    public function getTotalAttribute()
    {
        return $this->orderProducts()->sum(\DB::raw('target_price*quantity')) * $this->rate;
    }

    /**
     * @return float|int
     */
    public function getTotalWithOrderCurrencyAttribute()
    {
        return $this->orderProducts()->sum(\DB::raw('target_price*quantity'));
    }

    /**
     * Send email to Admin
     */
    public function sendMail()
    {
        if($this->status == Order::STATUS_QUOTE)
        {
            try {
                $emails = explode(",",getContactEmailAdmin());
                // dd($emails);
                Mail::to($emails)->send(new QuoteCreated($this));
                // 2 Feb 2024 - Kien Can you disable the automatic emails to the customers when place order/request on BD and MLC website? We are pausing active sales activity on those websites at the moment due to some reorganisation.
                // $this->user->notify(new ThankYouToCustomerQuote($this));
            } catch (\Exception $e) {

            }
        }
        else
        {
            try {
                $emails = explode(",",getContactEmailAdmin());
                Mail::to($emails)->send(new OrderCreated($this));
                // 2 Feb 2024 - Kien Can you disable the automatic emails to the customers when place order/request on BD and MLC website? We are pausing active sales activity on those websites at the moment due to some reorganisation.
                // $this->user->notify(new ThankYouToCustomerOrder($this));
            } catch (\Exception $e) {

            }
        }
    }

    public function updateTotalAmount()
    {
        return $this->update(['total_amount' => $this->total]);
    }

    /**
     * Relation to File
     *
     * @return HasMany
     */
    public function OrderShippingDetails()
    {
        return $this->hasMany(ShippingBillingDetails::class);
    }

    public function shippingDetail()
    {
        return $this->hasOne(ShippingBillingDetails::class)->orderByDesc('created_at');
    }
}
