<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\ShippingBillingDetails;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DataTables;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\OrderPaymentProduct;
use App\Models\OrderPaymentProductAdditionalCharge;
use App\Models\OrderProduct;
use Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\Stripe as StripeStripe;

/**
 * Class OrderController
 *
 * @package App\Http\Controllers\Admin
 */
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(
            'permission:order-list|category-show|order-delete|charge-customer',
            ['only' => ['monthlyRevenue']]
        );
        $this->middleware('permission:order-list', ['only' => ['index']]);
        $this->middleware('permission:order-show', ['only' => ['show']]);
        $this->middleware('permission:order-delete', ['only' => ['destroy']]);
        $this->middleware('permission:charge-customer', ['only' => ['pay']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::join('users', 'orders.user_id', '=', 'users.id')
                ->with('orderProducts')
                ->select('orders.*', 'users.first_name')
                // ->orderByRaw('view = 0 desc')
                ->whereNotIn('status', ['quote'])
                ->orderBy('orders.created_at', 'desc')
                ->get();


            return DataTables::of($data)
                ->editColumn(
                    'first_name',
                    function ($row) {
                        return $row->first_name . '' . $row->last_name;
                    }
                )
                ->editColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->editColumn(
                    'email',
                    function ($row) {
                        return optional($row->user)->email;
                    }
                )
                ->editColumn(
                    'count_of_products',
                    function ($row) {
                        return $row->orderProducts->count();
                    }
                )
                ->editColumn(
                    'number_of_products',
                    function ($row) {
                        return $row->orderProducts->sum('quantity');
                    }
                )
                ->addColumn(
                    'action',
                    function ($row) {
                        $btn = '';
                        if (Auth::user()->can('order-show')) {
                            $btn = '<a href="' . route('admin.orders.show', $row->id) . '"
                            data-toggle="tooltip"
                            data-id="' . $row->id . '"
                            data-original-title="Show"
                            class="edit btn btn-primary btn-sm show-quote">Show</a>';
                        }
                        if (Auth::user()->can('order-delete')) {
                            $btn = $btn . ' <a
                            href="javascript:void(0)"
                            data-toggle="tooltip" data-id="' . $row->id . '"
                            data-action="' . route(
                                'admin.orders.destroy',
                                ['order' => $row->id]
                            ) . '"
                            data-original-title="Delete"
                            class="btn btn-danger btn-sm deleteOrder">Delete</a>';
                        }

                        return $btn;
                    }
                )
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.order.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Application|Factory|View
     */
    public function show(Order $order, User $user)
    {
        $order->increment('view');

        $orderPayment = OrderPayment::with('orderPaymentProducts', 'orderPaymentProductAdditionalCharges')
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $orderedQty = OrderProduct::where('order_id', $order->id)->sum('quantity');
        $deliveredQty = OrderPaymentProduct::where('order_id', $order->id)->sum('quantity');

        $order_payment_products_sum = OrderPaymentProduct::where('order_id', $order->id)->sum('productTotal');
        $order_payment_product_additional_charges_sum = OrderPaymentProductAdditionalCharge::where('order_id', $order->id)->sum('total');

        $totalSumTillNow = $order_payment_products_sum + $order_payment_product_additional_charges_sum;

        return view('admin.order.view', compact('order', 'orderPayment', 'orderedQty', 'deliveredQty', 'totalSumTillNow'));
    }

    public function paymentHistory(Order $order)
    {
        $orderPayment = OrderPayment::with('orderPaymentProducts', 'orderPaymentProductAdditionalCharges')
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.order.paymentHistory', compact('order', 'orderPayment'));
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        Order::find($id)->delete();

        return response()->json(['success' => 'Order deleted successfully']);
    }


    /**
     * Return List of Orders
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function list(Request $request)
    {
        $total = 0;
        $orders = Order::query();
        $text = '';
        switch ($request->type) {
            case 'today':
                $total = Order::whereDate('created_at', now()->format('Y-m-d'))->sum('total_amount');
                $orders = $orders->whereDate('created_at', now()->format('Y-m-d'));
                break;
            case 'monthly':
                $data = explode('-', $request->month);
                $month = (int)$data[1];
                $year = (int)$data[0];
                $total = Order::whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->sum('total_amount');
                $orders = $orders->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);
                break;
            default:
                abort(404);
                break;
        }
        $orders = $orders->paginate(8);
        return response()->json(
            [
                'total' => "$".$total,
                'data' => view()->make('admin.order.list', compact('orders'))->render(),
                'pagination' => (string) $orders->appends($request->all())->links(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function monthlyRevenue(Request $request)
    {
        $totalRevenue = [];

        $data = CarbonImmutable::now();
        for ($i = 0; $i < 12; $i++) {
            $StartDate = $data->subMonths($i)->startOfMonth();
            $endDate = $data->subMonths($i)->endOfMonth();

            $total = Order::whereDate('created_at', '>=', $StartDate)
                ->whereDate('created_at', '<=', $endDate)
                ->sum('total_amount');

            $totalRevenue[] = [
                'period' => $StartDate->format('Y-m'),
                'RFQ' => (float)$total,
            ];
        }
        return response()->json(
            [
                'data' => array_reverse($totalRevenue),
            ]
        );
    }

    public function pay($id)
    {
        $order = Order::with('orderPayments')->whereId($id)->where(function ($q) {
            $q->where('status','pending')->orwhere('status','partial')->orwhere('status','paid');
        })->first();
        if(!$order)
        {
            return redirect()->route('admin.orders.index')->with('error', 'Order does not exist!');
        }

        $paymentHistory = $order->orderPayments;
        $remainingQtyByProductId = OrderPaymentProduct::join('order_products', 'order_payment_products.order_product_id', '=', 'order_products.id')
        ->where('order_products.order_id', $id)
        ->groupBy('order_products.id')
        ->select('order_products.id', \DB::raw('SUM(order_payment_products.quantity) as remaining_quantity'))
        ->pluck('remaining_quantity', 'id');

        return view('admin.order.pay', compact('order', 'paymentHistory', 'remainingQtyByProductId'));
    }

    public function deductAmount(Request $request)
    {
        // dd($request->all());
        $order = Order::whereId($request->order_id)->where(function ($q) {
            $q->where('status','pending')->orwhere('status','partial')->orwhere('status','paid');
        })->first();

        if($order)
        {
            StripeStripe::setApiKey(config('stripe.STRIPE_SECRET'));
            try {

                $returnUrl = route('admin.orders.processOrder', ['order_id' => $order->id, 'payment_method_id' => $order->payment_method_id, 'part_number' => $request->part_number, 'quantity' => $request->quantity, 'paid_amount' => $request->amount, 'email' => $order->user->email, 'customerId' => $order->user->stripe_customer_id, 'manufacturer_name' => $request->manufacturer_name, 'additional_charge' => $request->additional_charge, 'charge_details' => $request->charge_details, 'charge_type' => $request->charge_type, 'price' => $request->main_price]);

                $currencySymbol = $order->currencyExchangeRate->symbol;
                if ($currencySymbol == '$') {
                    $currency = 'usd';
                } elseif ($currencySymbol == '€') {
                    $currency = 'eur';
                }

                $paymentObject = PaymentIntent::create([
                    'amount' => $request->finalAmountToBeDeduct * 100,
                    // 'currency' => strtolower($order->currencyExchangeRate->symbol),
                    'currency' => $currency,
                    'payment_method' => $order->payment_method_id,
                    'confirmation_method' => 'automatic',
                    'customer' => $order->user->stripe_customer_id,
                    'confirm' => true,
                    'return_url' => $returnUrl,
                ]);

                $paymentIntent = PaymentIntent::retrieve($paymentObject->id);

                if(isset($paymentIntent->next_action->redirect_to_url->url) && !empty($paymentIntent->next_action->redirect_to_url->url)) {
                    $redirectURL = $paymentIntent->next_action->redirect_to_url->url;
                    return response()->json(['status' => 'is_3d_secure_card', 'redirect_url' => $redirectURL, 'clientSecret' => $paymentIntent->client_secret]);
                } else {

                    $this->storePaymentDetails($request,$paymentIntent);
                    // $orderPayment->sendMailToCustomer($request->email, $order->currencyExchangeRate->symbol);
                    return response()->json(['status' => 'success', 'message' => 'Payment has been successfully collected']);
                }

            } catch (CardException $e) {
                return response()->json(['status' => 'card_error', 'message' => $e->getError()->message]);
            }  catch (ApiErrorException $e) {
                return response()->json(['status' => 'stripe_api_error', 'message' => $e->getMessage()]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            return response()->json(['status' => 'can_not_charge_order', 'message' => 'Order not found.']);
        }
    }

    public function processOrder(Request $request)
    {
        $id = $request->order_id;
        try {
            StripeStripe::setApiKey(config('stripe.STRIPE_SECRET'));

            $stripe = new \Stripe\StripeClient(config('stripe.STRIPE_SECRET'));
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent);
            $returnUrl = route('admin.orders.show', ['order' => $id]);

            $paymentIntentStatus = $stripe->paymentIntents->confirm(
                $paymentIntent->id,
                [
                    // 'payment_method' => $payment_method_id,
                    'return_url' => $returnUrl,
                ]
            );

            if ($paymentIntentStatus->status == 'succeeded') {
                $this->storePaymentDetails($request,$paymentIntentStatus);
                // $orderPayment->sendMailToCustomer($request->email, $order->currencyExchangeRate->symbol);
            }

            return redirect()->route('admin.orders.show', [$id]);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }

    public function storePaymentDetails($request,$paymentIntent)
    {
        $order = Order::find($request->order_id);
        // dd($request->all(), $order, $order->currencyExchangeRate->symbol, $order->user->stripe_payment_response);
        $orderPayment = OrderPayment::create([
            'order_id' => $order->id,
            'transaction_id' => $paymentIntent->id,
            'total_price' => $request->finalAmountToBeDeduct,
            'currency' => $order->currencyExchangeRate->symbol,
            'customer_id' => $order->user->stripe_payment_response,
            'quantity' => "",
            'part_number' => "",
            'manufacturer_name' => "",
            'charge_details' => "",
            'charge_type' => "",
            'price' => "",
        ]);

        foreach($request->orderProducts as $key => $orderProducts) {
            $orderPaymentProduct = OrderPaymentProduct::create([
                'order_id' => $order->id,
                'order_payment_id' => $orderPayment->id,
                'order_product_id' => $key,
                'product_id' => $orderProducts['product_id'],
                'part_number' => $orderProducts['part_number'],
                'manufacturer_name' => $orderProducts['manufacturer'],
                'price' => $orderProducts['price'] ?? 0,
                "quantity" => $orderProducts['qty'] ?? 0,
                "productTotal" => $orderProducts['productTotal'],
                "additionalTotal" => $orderProducts['additionalTotal'],
                "total" => $orderProducts['total'],
            ]);

            if(isset($orderProducts['additionalCharge']) && is_array($orderProducts['additionalCharge']) && count($orderProducts['additionalCharge']) > 0)
            {
                foreach($orderProducts['additionalCharge'] as $k => $additionalCharge) {
                    OrderPaymentProductAdditionalCharge::create([
                        'order_id' => $order->id,
                        'order_payment_id' => $orderPayment->id,
                        'order_payment_product_id' => $orderPaymentProduct->id,
                        'order_product_id' => $key,
                        'details' => $additionalCharge['details'],
                        'type' => $additionalCharge['type'],
                        'amount' => $additionalCharge['amount'] ?? null,
                        'percentage' => $additionalCharge['percentage'] ?? null,
                        "total" => $additionalCharge['total'] ?? null,
                    ]);
                }
                // return redirect()->route('admin.orders.show', ['order' => $order->id]);
            }
            // Calculate delivered quantity
            $deliveredQuantity = OrderPaymentProduct::where('order_id', $request->order_id)->sum('quantity');
            $orderedQuantity = OrderProduct::where('order_id', $request->order_id)->sum('quantity');
            // dd($orderedQuantity);

            // Check if the delivered quantity meets or exceeds the total ordered quantity
            if ($deliveredQuantity >= $orderedQuantity) {
                $order->update(['status' => 'paid']);
            } else {
                $order->update(['status' => 'partial']);
            }

            // return redirect()->route('admin.orders.show', ['order' => $order->id]);
        }
    }
}
