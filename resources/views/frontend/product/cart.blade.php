@extends('layouts.frontend')
@section('content')
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<link href="{{ asset('assets/admin/node_modules/sweetalert2/dist/sweetalert2.min.css') }}" rel="stylesheet">
<link href="{!! asset(config('constants.CSS_PATH').'select2.min.css') !!}" rel="stylesheet">

@php $rand = rand(); @endphp
<form id="cart-form" role="form" method="post" class="require-validation" data-cc-on-file="false" data-stripe-publishable-key="{{ config('stripe.STRIPE_KEY') }}">
    @csrf
    <div class="container">
        <div class="breadcrumb">
            <ul>
                <li class="breadcrumb-item"><a href="{{ url('/') }}" title="Home"><img src="{!! asset('/images/home-icon.png') !!}" alt="Home" title="Home"></a></li>
                <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
            </ul>
        </div>
    </div>

    <div class="products-page-main">
        <div class="container">
            <div class="shipping-cart-top">
                <h2 class="cart-titles">Shopping Cart</h2>
                <a class="btn btn-primary" id="viewMoreBtn" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                View Cart
                <!-- <span class="currency-arrow icon-currency-icon"></span> -->
                </a>
            </div>
            <div class="collapse" id="collapseExample">
                <div class="card card-body">
                    <div class="products-main">
                        <div class="row">
                            <div class="col-lg-12 request-productlist-main">
                                <div class="table-responsive">
                                    <table id="cart-table">
                                        <thead class="thead">
                                            <tr class="tr">
                                                <th class="th subtitle">Product</th>
                                                <th class="th subtitle">Manufacturer</th>
                                                <th class="th subtitle">Price reference</th>
                                                <th class="th subtitle">Quantity</th>
                                                <th class="th subtitle">Summary</th>
                                                <th class="th subtitle">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <input type="hidden" name="redirectUrl" id="redirectUrl" value="{{ route('homepage') }}">
                                        <input type="hidden" name="totalPrice" id="totalPrice" value="">
                                        <input type="hidden" id="stripeErrorMessage" value="Sorry, We can’t process your payment right now!">
                                        <input type="hidden" id="currentCurrencyAbbr" value="{!! currentCurrency()->abbr !!}">
                                        <input type="hidden" id="stripeSuccessMessage" value="{{ config('stripe.SUCCESS_MSG') }}">
                                        <input type="hidden" id="stripePostUrl" value="{{ route('cart.pay') }}">
                                        <input type="hidden" id="currentCurrency" value="{{ currentCurrency()->icon_class }}">
                                        <input type="hidden" name="rate" class="rate" value="{{ currentCurrency()->rate }}">
                                        <input type="hidden" name="quantity" class="pop-up-quantity" id="quantity" value="">
                                        <input type="hidden" name="stripeError" class="stripeError" id="stripeError" value="">
                                        <input type="hidden" id="stripePublishableKey" value="{{ config('stripe.STRIPE_KEY') }}">
                                        @foreach($quotes as $quote)
                                        <tbody class="tbody additional-projects" style="display: none;">
                                            <tr>
                                                <td>
                                                    @if($quote['product'])
                                                    <div class="product-name-img d-flex align-items-center ga">
                                                        <div class="prdct-imgs">
                                                            <img src="{!! $quote['product']->image_full_url!!}" alt="{!! $quote['product']->manufacturer->name !!} {!! $quote['product']->part_number !!}" style="width: 70px; height: 70px;" title="{!! $quote['product']->manufacturer->name !!} {!! $quote['product']->part_number !!}">
                                                        </div>
                                                        <input type="hidden" class="form-control" name="part_number[]" value="{!! $quote['product']->part_number!!}">
                                                        <div class="title-text-form">
                                                            <div class="">{!! $quote['product']->manufacturer->name !!} <span> {!! $quote['product']->part_number !!}</span></div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="prdct-manu-img">
                                                        @if($quote['product'])
                                                        <img src="{!! $quote['product']->manufacturer->image_url !!}" alt="{!! $quote['product']->manufacturer->name!!}" title="{!! $quote['product']->manufacturer->name!!}" style="width: 70px; height: 70px;">
                                                        <div class="subtitle">{!! $quote['product']->manufacturer->name!!}</div>
                                                        <input type="hidden" class="form-control" name="manufacturer_name[]" value="{!! $quote['product']->manufacturer->name!!}">
                                                        @else
                                                        <div class="">
                                                            <input type="text" class="form-control" name="manufacturer_name[]" value="" placeholder="Manufacturer Name">
                                                        </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="mobile-block-table td d-flex justify-content-between" data-header="Your Target Price">
                                                    <div class="price-quantyinput">
                                                        <input type="text" class="form-control price-input form-calc form-cost" data-rand-id="{!! $rand !!}" name="price[]" @if($quote['price']) max="{{$quote['price']}}" id="price" @endif value="{!! ($quote['price'] > 0) ? $quote['price'] : (isset($quoteAmounts[$loop->index]) ? $quoteAmounts[$loop->index] : '') !!}" {!! $quote['price'] ? 'readonly' : '' !!}>
                                                        <span class="dollar-static">{!!currentCurrency()->symbol!!}</span>
                                                    </div>
                                                </td>
                                                <td class="mobile-block-table td d-flex justify-content-between" data-header="Quantity">
                                                    <div class="price-quantity-input">
                                                        <input type="text" class="form-control quantity-input form-calc form-qty" data-rand-id="{!! $rand !!}" name="quantity[]" value="{!! (isset($qtyData[$loop->index])) ? $qtyData[$loop->index] : $quote['quantity'] !!}">
                                                        <input type="hidden" name="product_id[]" value="{!! $quote['product'] ? $quote['product']->id : ''!!}">
                                                    </div>
                                                </td>
                                                <td data-header="Summary" class="td d-flex justify-content-between input-text price-blank with-price show-desk-content">
                                                    <span class="subtitle">{!!currentCurrency()->symbol!!}<span class="form-line">{!! (isset($summaryData[$loop->index])) ? $summaryData[$loop->index] : ($quote['price'] * $quote['quantity'])!!}</span></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                        @endforeach
                                    </table>
                                    <!-- <div class="view-more-btn-table">
                                        <button class="btn btn-primary" id="viewMoreBtn">Show Products</button>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="products-page-main">
        <div class="container">
            <h2>Card Details</h2>
            <div class="products-main d-flex flex-direction-row justify-content-between">
                <div class="row">
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Card Holder <span>*</span></strong>
                            <input type="text" class="form-control" name="card_holder" id="card_holder" placeholder="Enter your name as per card" value="{!! session('payee.name') ?? "" !!}">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Card Number <span>*</span></strong>
                            <input type="text" class="form-control card-number" name="card_number" id="card_number" placeholder="Enter your card number" maxlength="20" autocomplete="false">
                        </div>
                    </div>
                    <div class="col-lg-2 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Expiry Month <span>*</span></strong>
                            <select class="form-control card-expiry-month" name="expiry_month" id="expiry_month">
                                <option value="">Month</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Expiry Year <span>*</span></strong>
                            <select class="form-control card-expiry-year" name="expiry_year" id="expiry_year">
                                <option value="">Year</option>
                                @for ($i = Carbon\Carbon::now()->year; $i < Carbon\Carbon::now()->year + 10; $i++)
                                    <option value="{{ substr($i, -2) }}">
                                        {{ $i }}
                                    </option>
                                    @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">CVV <span>*</span></strong>
                            <input type="text" class="form-control card-cvc" name="cvv" id="cvv" placeholder="Enter card CVV" maxlength="4">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Company Name</strong>
                            <input type="text" class="form-control" name="company_name" id="company_name" placeholder="Enter your company name" value="{!! session('payee.company_name') ?? "" !!}">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Email <span>*</span></strong>
                            <input type="email" name="email" id="email" class="form-control userEmail" placeholder="Enter your email" value="{!! session('payee.email') ?? "" !!}">
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Message</strong>
                            <textarea class="form-control message" name="message" placeholder="Any special request...">{!! session('payee.message') ?? "" !!}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-------------------------------------------------------- START : Shippping card ------------------------------------------------------->
    <div class="products-page-main">
        <div class="container">
            <h2>Shippping Address</h2>
            <div class="products-main d-flex flex-direction-row justify-content-between">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <strong class="subtitle">Telephone</strong>
                            <input type="text" class="form-control" name="shipping_telephone" id="shipping_telephone" placeholder="Enter your telephone">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="subtitle">Country/Region</label>
                            <select class="form-control" name="shipping_country" id="shipping_country">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                <option value="{{$country->id}}">{{$country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <strong class="subtitle">Order Type<span>*</span></strong>
                        <div class="dk-radio-group btmMargin">
                            <input class="dk-radio" data-val="true" data-val-number="This field must be a number." data-val-regex="Invalid" data-val-regex-pattern="([0-9]+)" data-val-required="Required" id="orderTypeCompany" name="order_type" type="radio" value="company" checked>
                            <label class="dk-radio-label" for="orderTypeCompany">Company</label>
                            <input class="dk-radio" id="orderTypePersonal" name="order_type" type="radio" value="personal">
                            <label class="dk-radio-label" for="orderTypePersonal">Personal</label>
                            <span class="field-validation-valid" data-valmsg-for="order_type" data-valmsg-replace="true"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2 company_name">
                        <div class="form-group">
                            <strong class="subtitle">Company Name <span>*</span></strong>
                            <input type="text" class="form-control" name="shipping_company_name" id="shipping_company_name" placeholder="Enter your company name">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">First Name <span>*</span></strong>
                            <input type="text" class="form-control" name="shipping_first_name" id="shipping_first_name" placeholder="Enter your first name">
                        </div>
                    </div>

                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Last Name <span>*</span></strong>
                            <input type="text" class="form-control" name="shipping_last_name" id="shipping_last_name" placeholder="Enter your last name">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Shipping Id</strong>
                            <input type="text" class="form-control" name="shipping_id" id="shipping_id" placeholder="Enter your shipping id">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Address Line 1<span>*</span></strong>
                            <input type="text" class="form-control" name="shipping_address_line_1" id="shipping_address_line_1" placeholder="Enter your address">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Address Line 2</strong>
                            <input type="text" class="form-control" name="shipping_address_line_2" id="shipping_address_line_2" placeholder="Enter your address">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">City<span>*</span></strong>
                            <input type="text" class="form-control" name="shipping_city" id="shipping_city" placeholder="Enter your city">
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <label class="subtitlelabel required">State</label>
                            <select class="form-control" name="shipping_state" id="shipping_state">
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <strong class="subtitle">Postal Code<span>*</span></strong>
                            <input type="text" class="form-control" name="shipping_postal_code" id="shipping_postal_code" placeholder="Enter your postal code">
                        </div>
                    </div>
                    <div class="col-lg-12 mt-2">
                        <div class="divShippingBillingSameAddress topMargin">
                            <div class="dk-checkbox-group">
                                <input checked="checked" class="dk-checkbox" data-val="true" data-val-required="The My Billing Address is the same as my shipping field is required." id="isBillingSameAsShippingAddress" name="isBillingSameAsShippingAddress" type="checkbox" value="true">
                                <label class="dk-checkbox-label" for="isBillingSameAsShippingAddress">My Billing Address is the same as my shipping</label>
                                <input type="hidden" id="isBillingSameAsShippingValue" name="isBillingSameAsShippingValue" value="1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--------------------------START :  Billing form  -------------------------->
            <div class="products-main d-flex flex-direction-row justify-content-between">
                <div id="billing_form" style="display:none;">
                    <h2>Billing Contact Information</h2>
                    <!-- Your form fields go here -->
                    <div class="row mt-2">
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">Telephone</strong>
                                <input type="text" class="form-control" name="billing_telephone" id="billing_telephone" placeholder="Enter your telephone">
                            </div>
                        </div>
                        <div class="col-lg-3 mt-2">
                        <div class="form-group">
                            <label class="subtitle">Country/Region</label>
                            <select class="form-control" name="billing_country" id="billing_country">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                <option value="{{$country->id}}">{{$country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">First Name <span>*</span></strong>
                                <input type="text" class="form-control" name="billing_first_name" id="billing_first_name" placeholder="Enter your first name">
                            </div>
                        </div>

                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">Last Name <span>*</span></strong>
                                <input type="text" class="form-control" name="billing_last_name" id="billing_last_name" placeholder="Enter your last name">
                            </div>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">Company Name <span>*</span></strong>
                                <input type="text" class="form-control" name="billing_company_name" id="billing_company_name" placeholder="Enter your company name">
                            </div>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">Billing Id</strong>
                                <input type="text" class="form-control" name="billing_id" id="billing_id" placeholder="Enter your billing id">
                            </div>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">Address Line 1<span>*</span></strong>
                                <input type="text" class="form-control" name="billing_address_line_1" id="billing_address_line_1" placeholder="Enter your address line 1">
                            </div>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">Address Line 2</strong>
                                <input type="text" class="form-control" name="billing_address_line_2" id="billing_address_line_2" placeholder="Enter your address">
                            </div>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">City<span>*</span></strong>
                                <input type="text" class="form-control" name="billing_city" id="billing_city" placeholder="Enter your city">
                            </div>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <label class="subtitlelabel required">State</label>
                                <select class="form-control" name="billing_state" id="billing_state">
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="form-group">
                                <strong class="subtitle">Postal Code<span>*</span></strong>
                                <input type="text" class="form-control" name="billing_postal_code" id="billing_postal_code" placeholder="Enter your postal code">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--------------------------END :  Billing form  -------------------------->
            <div class="products-main d-flex flex-direction-row justify-content-between">
                <div class="total-price">
                    <h3>Total Price: <span>{{ currentCurrency()->symbol }}<span id="totalPayablePrice"></span></span></h3>
                    <span class="show-desk">The total price provided is an estimate</span>
                    <span class="show-mobile">Estimated Price</span>
                </div>
                <div class="paynow-btn">
                    <button type="submit" class="btn btn-primary" id="submit-payment123">Pay Online<i class="icon-pay-now"></i></button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-------------------------------------------------------- END : Shippping card ------------------------------------------------------->

@endsection
@section('script')
<script>
    var getStateUrl = "{{ route('getstate') }}";
</script>
<script src="{!! asset(config('constants.JS_PAGES_PATH').'cart.js') !!}?v=1"></script>
<script src="{!! asset(config('constants.JS_PATH').'sweetalert2.all.min.js') !!}" type="text/javascript"></script>
<script src="{!! asset(config('constants.JS_PATH').'select2.min.js') !!}" type="text/javascript"></script>
@endsection
