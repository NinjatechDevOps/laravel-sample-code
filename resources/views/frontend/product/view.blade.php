@extends('layouts.frontend')

@section('metaTitle', $productDetail && $productDetail->meta_title_parsed ? $productDetail->meta_title_parsed :
    $product->meta_title_parsed)
@section('metaDescription', $productDetail && $productDetail->meta_description_parsed ?
    $productDetail->meta_description_parsed : $product->meta_description_parsed)
@section('metaImage', $product->image_full_url)

@section('header')

    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.5",
    "reviewCount": "1019"
  },
  "name": "{{$manufacturer->name}} {{ $product->part_number }}",
  "description": "{{($productDetail && $productDetail->meta_description_parsed) ? $productDetail->meta_description_parsed : $product->meta_description_parsed}}",
  "image": "{{$product->image_full_url}}",
  "offers": {
    "@type": "Offer",
    "priceValidUntil": "{{now()->addYears(2)->endOfYear()->format('Y-m-d')}}",
    "itemCondition": "http://schema.org/UsedCondition",
    "availability": "https://schema.org/InStock",
    "price": "{{$product->price ? $product->price : config('constants.PRODUCT_DEFAULT_PRICE')}}",
    "priceCurrency": "USD"
  },
  "review": []
}

    </script>


@endsection
@section('content')
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <link href="{{ asset('assets/admin/node_modules/sweetalert2/dist/sweetalert2.min.css') }}" rel="stylesheet">
    <!--breadcrumb start-->
    <div class="submit-inq-popup bg-white-radius">
        <div class="submit-inq-inner d-flex justify-content-between align-items-center">
            <span class="d-flex justify-content-between"> <span class="icon-request-quote"></span> <i
                    class="body2-text">{{ $manufacturer->name }} {{ $product->part_number }} was added to your
                    inquiries</i></span>
            <a href="{{ route('request-quote') }}" title="Submit Inquiry" rel="nofollow"
                class="small-link small-link-states">Go
                to My Inquiries <span class="icon-p-arrow"></span></a>
        </div>
    </div>
    <div class="container">
        <div class="breadcrumb">
            <ul>
                <li class="breadcrumb-item"><a href="{{ url('/') }}" title="Home"><img src="{!! asset('/images/home-icon.png') !!}"
                            alt="Home" title="Home"></a></li>

                <li class="breadcrumb-item"><a href="{{ route('categories.index') }}" title="Products">Products</a>

                    @if ($category)
                <li class="breadcrumb-item">
                    <a href="{!! $category->detail_url !!}" title="{{ $category->name }}">{{ $category->name }}</a>
                </li>
                @endif
                @if ($category && $subCategory)
                    <li class="breadcrumb-item">
                        <a href="{!! $subCategory->detail_url !!}" title="{{ $subCategory->name }}">{{ $subCategory->name }}</a>
                    </li>
                @endif
                @if ($category && $subCategory && $subSubCategory)
                    <li class="breadcrumb-item">
                        <a href="{!! $subSubCategory->detail_url !!}"
                            title="{{ $subSubCategory->name }}">{{ $subSubCategory->name }}</a>
                    </li>
                @endif

                <li class="breadcrumb-item active" aria-current="page">{{ $product->part_number }}</li>
            </ul>
        </div>
    </div>
    <!--breadcrumb end-->
    <!--products start-->
    <div class="products-page-main">
        <div class="container">
            <div class="products-main d-flex flex-direction-row justify-content-between">

                <div class="product-left d-flex justify-content-between">
                    <div class="product-img-main">
                        <div class="products-image-brand">
                            <div class="product-imgs">
                                <img src="{{ $product->image_full_url }}"
                                    alt="{{ $manufacturer->name }} {{ $product->name }} {{ $product->part_number }}"
                                    title="{{ $manufacturer->name }} {{ $product->name }} {{ $product->part_number }}">
                            </div>
                            <div class="prdct-brands"><img src="{{ $manufacturer->image_url }}"
                                    alt="{{ $manufacturer->name }}" title="{{ $manufacturer->name }}">
                            </div>
                        </div>
                    </div>
                    <div class="product-img-details">
                        <span class="h2">Part #: </span>
                        <h1 class="h2">{!! $product->part_number !!}</h1>
                        @if ($product->formatted_frontend_price)
                            <div class="common-price">
                                <h4 class="price h3">{{ $product->formatted_frontend_price }}</h4> <strong
                                    class="price-info body1-text">Price reference</strong>
                            </div>
                        @else
                            <div class="common-price">
                                <h4 class="price h3">Available on Request</h4>
                            </div>
                        @endif
                        <h2 style="margin-top: 15px; font-size: 16px; opacity: 0.4;">{{ $product->part_number }} Details
                        </h2>
                        <ul>
                            <li>
                                <span class="subtitle">Availability</span>
                                <strong>
                                    @if ($product->quantity)
                                        <span class="status status-instock">In Stock</span>
                                    @else
                                        <span class="status status-outof-stock">On Request</span>
                                    @endif
                                </strong>
                            </li>
                            @if (isset($product->short_description) && $product->short_description != null)
                                <li>
                                    <span class="subtitle">Description</span> <strong
                                        class="p-desc button-text">{{ $product->short_description }}</strong>
                                </li>
                            @endif
                            <li>
                                <span class="subtitle">Manufacturer</span> <strong class="manuf-link button-text"><a
                                        href="{{ route('manufacturer.show', $manufacturer->slug) }}"
                                        title="{{ $manufacturer->name }}">{{ $manufacturer->name }} <span
                                            class="icon-p-arrow"></span></a></strong>
                            </li>
                            @if (isset($product->description) && $product->description != null)
                                <li>
                                    <span class="subtitle">Detailed Description</span> <strong
                                        class="d-desc button-text">{{ $product->description }}</strong>
                                </li>
                            @endif
                            @if ($product->datasheet_url)
                                <li>
                                    <span class="subtitle">Data Sheet</span> <strong class="p-download button-text"><a
                                            href="{!! $product->datasheet_full_url ?? route('products.datasheet', $product->id) !!}" title="Download" target="_blank">Download <span
                                                class="icon-download"></span></a></strong>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="product-right">
                    <form id="frmSubmitInquiry" class="submit-inquiry">
                        <input type="hidden" value="{{ $product->part_number }}" name="part_number"
                            class="form-control quantity-input">
                        <div class="quick-inquiry-main quick-inq-fixed-price">
                            @if (Session::has('success'))
                                <div class="alert alert-success text-center">
                                    <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
                                    <p>{{ Session::get('success') }}</p>
                                </div>
                            @endif
                            <div class="quick-inquiry-inner mobileScroll">
                                <h4 class="h3">Quick Inquiry</h4>
                                <p class="body1-text">
                                    {{ $manufacturer->name }}
                                    <strong> {{ $product->part_number }}</strong>
                                </p>

                                <div class="price-quantity d-flex justify-content-between">
                                    <div class="price-quantyinput">
                                        <strong class="subtitle">Price reference</strong>
                                        @php
                                            $price_referenece = '';
                                            if (@$myquote['price'] > 0) {
                                                $price_referenece = $myquote['price'];
                                            } elseif ($product->frontend_price) {
                                                $price_referenece = $product->frontend_price;
                                            }
                                        @endphp
                                        @if ($price_referenece)
                                            <input name="price" type="hidden" value="{!! str_replace(',', '', $price_referenece) !!}"
                                                class="form-control price-input" readonly>
                                            <span class="{{ currentCurrency()->icon_class }}"></span>
                                            {!! $price_referenece !!}
                                        @else
                                            <input name="price" type="hidden"
                                                value="{{ config('constants.PRODUCT_DEFAULT_PRICE') }}"
                                                class="form-control price-input" readonly>
                                            From <span class="{{ currentCurrency()->icon_class }}"></span>
                                            {{ config('constants.PRODUCT_DEFAULT_PRICE') }}<BR>(On request)
                                        @endif
                                    </div>
                                    <div class="price-quantity-input">
                                        <strong class="subtitle">Quantity</strong>
                                        <input name="quantity" type="text" value="{!! $myquote['quantity'] ?? '' !!}"
                                            step="1" class="form-control quantity-input quantityInput"
                                            maxlength="8" autocomplete="off">
                                    </div>
                                    <div class="quick-inq-error-msg general-invalid-feedback"><span></span></div>
                                </div>

                                <div class="total-price">
                                    <h3 class="input-text">Total Price: <span
                                            class="h3">{{ currentCurrency()->symbol }}<span
                                                class="calc-price"></span></span></h3>
                                    <span class="body2-text">The total price provided is an estimate</span>
                                </div>
                            </div>
                            <div class="submit-inquiry-main">
                                <div class="d-flex">
                                    <button type="submit" title="Submit Inquiry" class="btn btn-primary submit-inquiry"
                                        rel="nofollow">Submit Inquiry<i class="icon-request-quote"></i></button>
                                </div>
                                @if ($product->is_payable === 1)
                                    {{-- <span class="body2-text">For orders over <span
                                            class="{{ currentCurrency()->icon_class }}"></span>{{ config('stripe.MIN_AMOUNT') }},
                                        you can pay online</span> --}}
                                    <div class="d-flex">
                                            </div>
                                            @endif
                                            <button type="button" class="btn btn-secondary" id="payonline_new">Pay Online <i class="icon-pay-now"></i></button>
                            </div>
                        </div>
                    </form>
                    @if ($product->is_payable === 1)
                        <form role="form" method="post" class="require-validation" data-cc-on-file="false"
                            data-stripe-publishable-key="{{ config('stripe.STRIPE_KEY') }}" id="payment-form">
                            @csrf
                            <input type="hidden" name="product_id" class="productId" value="{{ $product->id }}">
                            <input type="hidden" name="part_number" class="partNumber"
                                value="{{ $product->part_number }}">
                            <input type="hidden" value="{{ $product->price_per_quantity }}" name="pricePerQuantity"
                                id="pricePerQuantity">
                            <input type="hidden" name="totalPrice" id="totalPrice" value="">
                            <input type="hidden" name="targetPrice" value="{!! str_replace(',', '', $price_referenece) !!}">
                            <input type="hidden" name="manufacturer_name" value="{!! $manufacturer->name !!}">
                            <input type="hidden" name="quantity" class="pop-up-quantity" id="quantity"
                                value="">
                            <input type="hidden" name="stripeError" class="stripeError" id="stripeError"
                                value="">
                            <input type="hidden" id="currentCurrency" value="{{ currentCurrency()->icon_class }}">
                            <input type="hidden" name="rate" class="rate" value="{{ currentCurrency()->rate }}">
                            <input type="hidden" id="stripeMinAmount" value="{{ config('stripe.MIN_AMOUNT') }}">
                            <input type="hidden" id="stripePostUrl" value="{{ route('cart.pay') }}">
                            <input type="hidden" id="stripeSuccessMessage" value="{{ config('stripe.SUCCESS_MSG') }}">
                            <input type="hidden" id="stripePublishableKey" value="{{ config('stripe.STRIPE_KEY') }}">
                            <input type="hidden" id="stripeErrorMessage" value="Sorry, We can’t process your payment right now!">
                            <input type="hidden" id="stripeMinAmount" value="{{ config('stripe.MIN_AMOUNT') }}">
                            <input type="hidden" id="currentCurrencyAbbr" value="{!! currentCurrency()->abbr !!}">
                            <div class="modal fade paynow-popup" id="pay-now-popup" tabindex="-1"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable">
                                    <div class="modal-content bg-white-radius">
                                        <div class="popup-heading d-flex align-items-center justify-content-between">
                                            <h2>Buy by fixed price</h2>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true" class="icon-close"></span>
                                            </button>
                                        </div>
                                        <div class="buyby-product-info-main">
                                            <div class="buyby-product-info">
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6">
                                                        <div class="d-flex align-items-center">
                                                            <div class="fix-price-img"><img
                                                                    src="{{ $product->image_full_url }}"
                                                                    alt="{{ $manufacturer->name }}"
                                                                    title="{{ $manufacturer->name }}">
                                                            </div>
                                                            <div class="fixprdct-info body1-text">
                                                                <span>{{ $manufacturer->name }}
                                                                    <strong>{{ $product->part_number }}</strong></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 quick-inq-fixed-price">
                                                        <div class="price-quantity d-flex justify-content-between">
                                                            <div class="price-quantyinput">
                                                                <strong class="subtitle">Fixed price</strong>
                                                                <input type="number"
                                                                    class="form-control price-input fixed_price"
                                                                    name="fixed_price" readonly
                                                                    value="{!! $price_referenece ? str_replace(',', '', $price_referenece) : config('constants.PRODUCT_DEFAULT_PRICE') !!}">
                                                                @if ($price_referenece)
                                                                    <span class="dollar-static"><span
                                                                            class="{{ currentCurrency()->icon_class }}"></span></span>
                                                                @endif
                                                                <div class="field-lock"><span class="icon-lock"></span>
                                                                </div>
                                                            </div>
                                                            <div class="price-quantity-input form-group">
                                                                <strong class="subtitle">Quantity <span style="color: red">*</span></strong>
                                                                <input type="text" name="quantity" id="quantity"
                                                                    value=""
                                                                    class="form-control quantity-input quantity-value"
                                                                    maxlength="8" autocomplete="false">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="fixed-price-form">
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong class="subtitle">Card Holder <span>*</span></strong>
                                                            <input type="text" class="form-control" name="card_holder"
                                                                id="card_holder"
                                                                placeholder="Enter your name as per card">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong class="subtitle">Card Number <span>*</span></strong>
                                                            <input type="text" class="form-control card-number"
                                                                name="card_number" id="card_number"
                                                                placeholder="Enter your card number" maxlength="20"
                                                                autocomplete="false">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="form-group">
                                                            <strong class="subtitle">Expiry Month <span>*</span></strong>
                                                            <select class="form-control card-expiry-month"
                                                                name="expiry_month" id="expiry_month" required>
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
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="form-group">
                                                            <strong class="subtitle">Expiry Year <span>*</span></strong>
                                                            <select class="form-control card-expiry-year"
                                                                name="expiry_year" id="expiry_year" required>
                                                                <option value="">Year</option>
                                                                @for ($i = Carbon\Carbon::now()->year; $i < Carbon\Carbon::now()->year + 10; $i++)
                                                                    <option value="{{ substr($i, -2) }}">
                                                                        {{ $i }}</option>
                                                                @endfor
                                                            </select>
                                                        </div>

                                                    </div>
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="form-group">
                                                            <strong class="subtitle">CVV <span>*</span></strong>
                                                            <input type="text" class="form-control card-cvc"
                                                                name="cvv" id="cvv"
                                                                placeholder="Enter card CVV" maxlength="4">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong class="subtitle">Company Name</strong>
                                                            <input type="text" class="form-control"
                                                                name="company_name" id="company_name"
                                                                placeholder="Enter your company name">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong class="subtitle">Email <span>*</span></strong>
                                                            <input type="email" name="email" id="email"
                                                                class="form-control userEmail"
                                                                placeholder="Enter your email">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12 col-md-12">
                                                        <div class="form-group">
                                                            <strong class="subtitle">Message</strong>
                                                            <textarea class="form-control message" name="message" placeholder="Any special request..."></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="total-price-info bg-white-radius d-flex align-items-center justify-content-between">
                                                <div class="total-price">
                                                    <h3>Total Price: <span>{{ currentCurrency()->symbol }}<span
                                                                id="totalPayablePrice"></span></span></h3>
                                                    <span class="show-desk">The total price provided is an estimate</span>
                                                    <span class="show-mobile">Estimated Price</span>
                                                </div>
                                                <div class="paynow-btn">
                                                    <button type="submit" class="btn btn-primary"
                                                        id="submit-payment">Pay Online<i
                                                            class="icon-pay-now"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!--products end-->
    <!-- middle -->
    <section class="middle-section">
        <div class="container">
            <div class="row">
                <!-- leftbar start -->
                <div class="col-xl-3 col-lg-4 col-md-3 sidebar product-sidebar">
                    <div class="inner-sidebar">
                        <div class="catalog-card catalog-card-prodct-page">
                            <div class="cat-logo"><img src="{{ $manufacturer->image_url }}"
                                    alt="{{ $manufacturer->name }}" title="{{ $manufacturer->name }}"></div>
                            <a href="javascript:void(0);" title="{{ $product->part_number }}">
                                <div class="catalog-category-img text-center">
                                    <img src="{{ $product->image_full_url }}" alt="{{ $product->part_number }}"
                                        title="{{ $product->part_number }}">
                                </div>
                                <div class="catalog-cat-name">
                                    {{ $manufacturer->name }} <span>{{ $product->part_number }}</span>
                                </div>
                            </a>
                            <div class="category-cat-desc">
                                <div class="catalog-price-main d-flex justify-content-between align-items-center">
                                    @if ($product->formatted_frontend_price)
                                        <div class="catalog-price common-price">
                                            <span class="price">{{ $product->formatted_frontend_price }} <strong
                                                    class="price-info">Price reference</strong></span>
                                        </div>
                                    @else
                                        <div class="catalog-price common-price">
                                            <span class="price">Available on Request</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="submit-inquiry-main d-flex">
                                <a href="javascript:void(0);" title="Submit Inquiry"
                                    class="btn btn-primary submit-inquiry btnSubmitInquiryWithoutPrice"
                                    rel="nofollow">Submit Inquiry <i class="icon-request-quote"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- leftbar end -->
                <!-- rightbar start -->
                <div class="col-xl-9 col-lg-8 rightbar product-rightbar">
                    <!-- product rightbar start -->
                    <!--  Products details start -->
                    <div class="products-details">
                        <h2 class="nav nav-tabs" role="tablist">
                            <span class="active" data-toggle="tab" href="#p-details" role="tab">
                                {!! $product->part_number !!}<br>
                                Specifications
                            </span>
                            @if ($product->datasheet_url)
                                <span data-toggle="tab" href="#data-sheets" role="tab">Datasheet</span>
                            @endif
                        </h2>
                        <!-- <h2 data-toggle="tab" href="#data-sheets" role="tab"><span>Data Sheet</span></h2> -->
                        <div class="">
                            <div class="product-tables tab-content">
                                <div class="tab-pane active" id="p-details" role="tabpanel">
                                    <div class="products-tables-main">
                                        <table>
                                            <tr>
                                                <td>
                                                    <span class="subtitle">Part Number</span>
                                                </td>
                                                <td>
                                                    <strong class="button-text">
                                                        {!! $product->part_number !!}
                                                    </strong>
                                                </td>
                                            </tr>
                                            @if ($category)
                                                <tr>
                                                    <td><span class="subtitle">Category</span></td>
                                                    <td>
                                                        <strong class="button-text">
                                                            <a class="button-text small-link-states"
                                                                href="{{ route('categories.show', $category->slug) }}"
                                                                title="{{ $category->name }}">{{ $category->name }}</a><span
                                                                class="icon-right-arrow"></span>
                                                        </strong>

                                                        @if ($subCategory)
                                                            <br />
                                                            <strong class="button-text">
                                                                <a class="button-text small-link-states"
                                                                    href="{{ route('subcategories.show', [$category->slug, $subCategory->slug]) }}"
                                                                    title="{{ $subCategory->name }}">{{ $subCategory->name }}</a><span
                                                                    class="icon-right-arrow"></span>
                                                            </strong>
                                                        @endif

                                                    </td>

                                                </tr>
                                            @endif
                                            @php
                                                $rohs = null;
                                                for ($i = 1; $i <= 56; $i++) {
                                                    if (@$productDetail['data']['attribute_' . $i] === 'RoHS Status') {
                                                        $rohs = $i;
                                                    }
                                                }
                                            @endphp
                                            @for ($i = 1; $i <= ($rohs ? $rohs - 1 : 56); $i++)
                                                @if (@$productDetail['data']['value_' . $i])
                                                    @if (
                                                        !in_array($productDetail['data']['attribute_' . $i], [
                                                            'Mfr',
                                                            'Datasheets',
                                                            'Video File',
                                                            'Environmental Information',
                                                            'Featured Product',
                                                            'PCN Manufacturer Information',
                                                            'HTML Datasheet',
                                                            'Forum Discussions',
                                                        ]))
                                                        <tr>
                                                            <td>
                                                                <span
                                                                    class="subtitle">{{ $productDetail['data']['attribute_' . $i] }}</span>
                                                            </td>
                                                            <td>
                                                                <strong class="button-text">
                                                                    {!! customProductAttribureValueString($productDetail['data']['value_' . $i]) !!}
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if ($productDetail['data']['attribute_' . $i] == 'Mfr')
                                                        <tr>
                                                            <td><span
                                                                    class="subtitle">{{ $productDetail['data']['attribute_' . $i] }}</span>
                                                            </td>
                                                            <td>
                                                                <strong class="button-text">
                                                                    <a class="button-text small-link-states"
                                                                        href="{{ route('manufacturer.show', $manufacturer->slug) }}"
                                                                        title="{{ $manufacturer->name }}">{{ $manufacturer->name }}</a><span
                                                                        class="icon-right-arrow"></span>
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            @endfor

                                            @if ($product->datasheet_url)
                                                <tr>
                                                    <td><span class="subtitle">Datasheet</span></td>
                                                    <td>
                                                        <strong class="button-text">
                                                            <a href="{!! $product->datasheet_full_url ?? route('products.datasheet', $product->id) !!}" title="Download"
                                                                target="_blank">Download <span
                                                                    class="icon-download"></span></a>
                                                        </strong>
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>

                                    @if ($rohs)
                                        <div class="products-tables-main">
                                            <h3>Environmental & Export Classifications</h3>
                                            <table>

                                                @for ($i = $rohs; $i <= 56; $i++)
                                                    @if (@$productDetail['data']['value_' . $i])
                                                        <tr>
                                                            <td><span
                                                                    class="subtitle">{{ $productDetail['data']['attribute_' . $i] }}</span>
                                                            </td>
                                                            <td>
                                                                <strong class="button-text">
                                                                    {!! customProductAttribureValueString($productDetail['data']['value_' . $i]) !!}
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endfor

                                            </table>
                                        </div>
                                    @endif
                                </div>
                                @if ($product->datasheet_url)
                                    <div class="tab-pane" id="data-sheets" role="tabpanel">
                                        <div id="datasheet_preview"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Products details end -->
                    <!-- Related Products start -->
                    <div class="related-products">
                        <h2>#{!! $product->part_number !!} Analogs and Related</h2>
                        <div class="owl-carousel products-cards-slider">
                            @foreach ($relatedProducts as $relatedProduct)
                                @php
                                    $relatedManufacturer = $relatedProduct->cache_manufacturer;
                                @endphp
                                @if ($relatedManufacturer)
                                    <li class="item catalog-card catalog-card-prodct-page">
                                        <div class="cat-logo">
                                            <img src="{!! $relatedManufacturer->image_url !!}" alt="{!! $relatedManufacturer->name !!}"
                                                title="{!! $relatedManufacturer->name !!}">
                                        </div>
                                        <a href="{!! $relatedProduct->detail_url !!}" title="{!! $relatedProduct->full_name !!}">
                                            <div class="catalog-category-img text-center">
                                                <img src="{!! $relatedProduct->image_full_url !!}" alt="{!! $relatedProduct->part_number !!}"
                                                    title="{!! $relatedProduct->part_number !!}">
                                            </div>
                                            <div class="catalog-cat-name">
                                                <a href="{!! $relatedProduct->detail_url !!}"
                                                    title="{!! $relatedProduct->full_name !!}"><span>{!! $relatedProduct->part_number !!}</span>
                                            </div>
                                        </a>
                                        <div class="category-cat-desc">
                                            <div
                                                class="catalog-price-main d-flex justify-content-between align-items-center">
                                                <div class="catalog-price common-price">
                                                    <span class="price">
                                                        @if ($relatedProduct->formatted_frontend_price)
                                                            {!! $relatedProduct->formatted_frontend_price !!} <strong class="price-info">Price
                                                                reference</strong>
                                                        @else
                                                            <strong>Available on Request</strong>
                                                        @endif
                                                    </span>
                                                </div>
                                                <a href="{!! $relatedProduct->detail_url !!}" class="btn btn-secondary btn-rounded">
                                                    <span class="icon-catalog-card-arrow"></span>
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <!-- Related Products end -->
                    <!-- product rightbar end -->
                </div>
            </div>
        </div>
        <!-- rightbar end -->
        <input type="hidden" id="datasheet_iframe_url" value="{!! (($product->datasheet_url) ? $product->datasheet_iframe_url : '') !!}">
    </section>
@endsection
@section('script')
    <script src="{{ asset('assets/admin/node_modules/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
    @if ($product->datasheet_url)
    <script src="{{ url(config('constants.JS_PATH').'pdfobject.min.js') }}"></script>
    @endif
    <script src="{!! asset(config('constants.JS_PAGES_PATH').'product_view.js') !!}"></script>
@endsection
