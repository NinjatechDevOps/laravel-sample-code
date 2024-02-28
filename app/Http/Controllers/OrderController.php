<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\CartResource;
use App\Models\CmsContent;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart;
use App\Services\Currency\Amount;
use App\Services\Currency\Contracts\CurrentCurrency;
use Illuminate\Http\Request;
use Session;
use App\Models\Country;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\ShippingBillingDetails;

/**
 * Class OrderController
 *
 * @package App\Http\Controllers
 */
class OrderController extends Controller
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

    public function index(Request $request)
    {
        $cmsContent = CmsContent::fromSlug('request-quote');
        $quotes = Cart::getItems();
        return view('frontend.request-quote', compact('quotes', 'cmsContent'));
    }

    public function store(AddToCartRequest $request)
    {
        $itemLists = Session::get('inquiryList') ?? [];
        foreach ($itemLists as $key => $itemList) {
            if ($itemList['part_number'] == $request->part_number) {
                unset($itemLists[$key]);
            }
        }
        $addData = $request->all();
        $addData['price'] = new Amount((float)$addData['price'], app(CurrentCurrency::class)->abbr);

        $itemLists[] = $addData;
        Session::put('inquiryList', $itemLists);
        return Cart::getItems();
    }

    public function deleteEnquiryItem(Request $request)
    {
        $itemLists = Session::get('inquiryList');
        foreach ($itemLists as $key => $itemList) {
            if ($itemList['part_number'] == $request->part_number) {
                unset($itemLists[$key]);
            }
        }
        Session::put('inquiryList', $itemLists);

        return response()->json(['message' => $request->part_number . ' deleted successfully']);
    }

    public function thankYou(Request $request)
    {
        return view('frontend.thank-you');
    }

    public function submitQuote(OrderRequest $request)
    {
        if($request->isPayOnline) {
            /* Code when click on pay online button or submit quote page */
            $itemLists = [];
            if ($request->part_number) {
                foreach ($request->part_number as $key => $part_number) {
                    $itemLists[] = [
                        'part_number' => $request->part_number[$key],
                        'price' =>  new Amount((float)$request->price[$key], app(CurrentCurrency::class)->abbr),
                        'quantity' => $request->quantity[$key]
                    ];
                }
            }
            Session::put('inquiryList', $itemLists);
            $payee = [
                'name' =>  $request->contact_name,
                'email' => $request->email,
                'company_name' => $request->company_name,
                'message' => $request->message,
            ];
            Session::put('payee', $payee);
            return response()->json(['message' => 'Session updated successfully']);
            /* END */
        } else {

            $user = User::where('email', $request->email)->first();
            $username = $request->contact_name;
            if(isset($username) && !empty($username)) {
                $username = explode(" ", $request->contact_name);
            }
            $first_name = "";
            $last_name = "";
            if(isset($username) && !empty($username)) {
                if(isset($username[0])) {
                    $first_name = $username[0];
                }
                if(isset($username[1])) {
                    $last_name = $username[1];
                }
            }
            if(empty($username)){
                $parts = explode('@', $request->email);
                $first_name = $parts[0];
            }
            $userData = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $request->email,
                'company_name' => $request->company_name,
            ];

            if (!$user) {
                $user = User::create($userData);
                $user->assignRole('Prospect');
            } else if ($user->hasRole('Prospect')) {
                User::where('id',$user->id)->update($userData);
            }else{
                $user->assignRole('Prospect');
            }

            /** @var Order $order */
            $order = $user->orders()->create(
                [
                    'status' => Order::STATUS_QUOTE,
                    'message' => $request->message,
                    'currency_exchange_rate_id' => currentCurrency()->id,
                    'rate' => currentCurrency()->rate,
                ]
            );

            if ($request->part_number) {
                foreach ($request->part_number as $key => $part_number) {
                    $product = Product::find($request->product_id[$key]);
                    $order->orderProducts()->create(
                        [
                        'product_id' => $product ? $product->id : null,
                        'part_number' => $request->part_number[$key],
                        'manufacturer_name' => $request->manufacturer_name[$key],
                        'price' => $product ? $product->price : null,
                        // 'part_number' => $part_number,
                        'target_price' => $request->price[$key],
                        'quantity' => $request->quantity[$key],
                        ]
                    );
                }
            }

            $bomFiles = Session::get('bom_files') ?? [];
            foreach ($bomFiles as $bomFile) {
                $order->orderFiles()->create(
                    [
                        'file_name' => $bomFile['name'],
                    ]
                );
            }
            $order->updateTotalAmount();
            $order = $order->refresh();
            Session::forget('bom_files');
            Session::forget('inquiryList');

            $order->sendMail();

            return response()->json(['message' => 'Order created successfully']);
        }
    }

    public function updateSession(Request $request) {
        $itemLists = Session::get('inquiryList') ?? [];
        foreach ($itemLists as $key => $itemList) {
            if ($itemList['part_number'] == $request->input('part_number')) {
                unset($itemLists[$key]);
            }
        }
        $searchQuery = [
            'part_number' => $request->input('part_number'),
            'price' => new \App\Services\Currency\Amount((float)0, currentCurrency()->abbr),
            'quantity' => 1
        ];
        $itemLists[] = $searchQuery;
        Session::put('inquiryList', $itemLists);

        return response()->json(['message' => 'Session updated successfully']);
    }

    public function cart(Request $request)
    {
        $quotes = Cart::getItems();
        if(!$quotes) {
            return redirect(route('homepage'));
        }
        $countries = Country::all();
        return view('frontend.product.cart', compact('quotes', 'countries'));
    }

    public function pay(Request $request)
    {
        $order = null;
        $stripeError = null;

        try {
            \Stripe\Stripe::setApiKey(config('stripe.STRIPE_SECRET'));
            $stripe = new \Stripe\StripeClient(config('stripe.STRIPE_SECRET'));

            $paymentMethod = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'token' => $request->stripeToken,
                ],
            ]);
            $paymentMethodDetails = $stripe->paymentMethods->retrieve($paymentMethod->id);

            $firstName = explode(' ', $request->card_holder);
            $first_name = $firstName[0];
            $last_name = "";
            if(isset($firstName[1])) {
                $last_name = $firstName[1];
            }

            $user = User::where('email', $request->email)->first();

            if ($user) {
                $user->update([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'company_name' => $request->company_name ? $request->company_name : null,
                ]);
            } else {
                $user = User::create([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $request->email,
                    'company_name' => $request->company_name ? $request->company_name : null,
                ]);
            }

            // Create or update Stripe customer
            if (!$user->stripe_customer_id) {
                $customer = \Stripe\Customer::create([
                    'email'       => $user->email,
                    'description' => 'Customer of ' . config('app.name'),
                ]);

                $user->stripe_customer_id = $customer->id;
                $user->save();
            }

            // Attach payment method to customer
            if (isset($user->stripe_customer_id) && !empty($user->stripe_customer_id)) {
                $stripe->paymentMethods->attach(
                    $paymentMethod->id,
                    ['customer' => $user->stripe_customer_id]
                );
            }

            // Create order
            $order = new Order();
            $order->user_id = $user->id;
            $order->total_amount = $request->totalPrice;
            $order->rate = currentCurrency()->rate;
            $order->currency_exchange_rate_id = currentCurrency()->id;
            $order->message = $request->message;
            $order->status = 'pending';
            $order->payment_method_id = $paymentMethod->id;
            $order->save();
            // Create order product
            // $orderProduct = new OrderProduct();
            // $orderProduct->order_id = $order->id;
            // $orderProduct->product_id = $request->product_id;
            // $orderProduct->part_number = $request->part_number;
            // $orderProduct->manufacturer_name = $request->manufacturer_name;
            // $orderProduct->quantity = $request->quantity;
            // $orderProduct->price = $request->rate;
            // $orderProduct->target_price = $request->targetPrice;
            // $orderProduct->save();

            //****************************************************START : Save multiple order product data ****************************************************
            $count = count($request->manufacturer_name);
            for ($i = 0; $i < $count; $i++) {
                $orderProduct = new OrderProduct();
                $orderProduct->order_id = $order->id;
                $orderProduct->product_id = isset($request->product_id[$i]) ? $request->product_id[$i] : null;
                $orderProduct->part_number = $request->part_number[$i] ?? null;
                $orderProduct->manufacturer_name = $request->manufacturer_name[$i] ?? null;
                $orderProduct->quantity = $request->quantity[$i] ?? null;
                $orderProduct->price = $request->price[$i] ?? null;
                $orderProduct->target_price = $request->target_price[$i] ?? null;
                $orderProduct->save();
            }
            //****************************************************END : Save multiple order product data ****************************************************

            //****************************************************START : shipping details save ****************************************************
            $shippingBillingDetails = [
                'order_id' => $order->id,
                'shipping_telephone' => $request->shipping_telephone,
                'shipping_country' => $request->shipping_country,
                'shipping_company_name' => $request->shipping_company_name,
                'shipping_first_name' => $request->shipping_first_name,
                'shipping_last_name' => $request->shipping_last_name,
                'shipping_id' => $request->shipping_id,
                'shipping_address_line_1' => $request->shipping_address_line_1,
                'shipping_address_line_2' => $request->shipping_address_line_2,
                'shipping_city' => $request->shipping_city,
                'shipping_state' => $request->shipping_state,
                'shipping_postal_code' => $request->shipping_postal_code,
                'order_type' => $request->order_type,
                'is_billing_same_as_shipping' => $request->has('isBillingSameAsShippingAddress') ? 1 : 0,
            ];

            if ($shippingBillingDetails['is_billing_same_as_shipping']) {
                // If billing address is same as shipping address
                $billingFields = [
                    'billing_telephone' => $request->shipping_telephone,
                    'billing_country' => $request->shipping_country,
                    'billing_first_name' => $request->shipping_first_name,
                    'billing_last_name' => $request->shipping_last_name,
                    'billing_company_name' => $request->shipping_company_name,
                    'billing_id' => $request->shipping_id,
                    'billing_address_line_1' => $request->shipping_address_line_1,
                    'billing_address_line_2' => $request->shipping_address_line_2,
                    'billing_city' => $request->shipping_city,
                    'billing_state' => $request->shipping_state,
                    'billing_postal_code' => $request->shipping_postal_code,
                ];
            } else {
                // If billing address is different from shipping address
                $billingFields = [
                    'billing_telephone' => $request->billing_telephone,
                    'billing_country' => $request->billing_country,
                    'billing_first_name' => $request->billing_first_name,
                    'billing_last_name' => $request->billing_last_name,
                    'billing_company_name' => $request->billing_company_name,
                    'billing_id' => $request->billing_id,
                    'billing_address_line_1' => $request->billing_address_line_1,
                    'billing_address_line_2' => $request->billing_address_line_2,
                    'billing_city' => $request->billing_city,
                    'billing_state' => $request->billing_state,
                    'billing_postal_code' => $request->billing_postal_code,
                ];
            }
            $shippingBillingDetails = array_merge($shippingBillingDetails, $billingFields);
            $shippingBillingDetailsRecord  = ShippingBillingDetails::create($shippingBillingDetails);
            //*************************************************** END Shipping data ***************************************************
            Session::forget('inquiryList');
            Session::forget('payee');

            $order->sendMail();

            return response()->json(['status' => 1, 'message' => "Your order has been placed.", 'messageTitle' => 'Your order has been placed!']);
        } catch (\Stripe\Exception\CardException $e) {
            return response()->json(['status' => 'card_error', 'message' => $e->getError()->message]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['status' => 'stripe_api_error', 'message' => $e->getMessage()]);
        } catch (\Stripe\Exception\CardException | \Stripe\Exception\InvalidRequestException $e) {
            $stripeError = $e->getError();
            if ($order) {
                $order->status = 'failed';
                $order->stripe_payment_response = $stripeError->message . ', ' . $stripeError->code;
                $order->save();
            }
            return response()->json(['status' => 0, 'message' => $stripeError ? $stripeError->message : 'An error occurred', 'messageTitle' => 'Payment Failed!']);
        }
    }

}
