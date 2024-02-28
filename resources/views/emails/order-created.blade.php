@extends('layouts.email')

@section('content')


    <tr>
        <td style="padding-top: 28px; padding-right: 32px; padding-bottom: 7px; padding-left: 32px;">
            <h2 style="margin: 0;font-family:'Inter', sans-serif; font-size: 24px; padding-bottom: 16px; color: #112A5A; font-weight: 600; line-height: 32px;">New Request №{{$order->id}}</h2>
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" style="padding: 0; padding-left: 32px; padding-right: 32px;" class="line">
            <hr color="#F1F1F3" align="center" width="100%" size="1" noshade="" style="margin: 0; padding: 0;">
        </td>
    </tr>

    <tr>
        <td align="center" style="padding-left: 32px; padding-right: 32px;">
            <table width="536" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td height="22"></td>
                    </tr>
                    <tr>
                        <td style="width: 200px; padding-bottom: 8px;">
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height: 20px; font-weight: 400;">Order Date</div>
                        </td>
                        <td style="width: 332px;text-align: right;vertical-align: top;">
                            <div style="text-align: right; font-family:'Inter', sans-serif; font-size: 12px; font-weight: 600; line-height: 16px; color:#474A55;">
                                {{$order->created_at->format('d F Y')}}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 200px; padding-bottom: 8px;">
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height: 20px; font-weight: 400;">Customer Name</div>
                        </td>
                        <td style="width: 332px;text-align: right;vertical-align: top;">
                            <div style="text-align: right; font-family:'Inter', sans-serif; font-size: 12px; font-weight: 600; line-height: 16px; color:#474A55;">
                                {{$order->user->name}}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 200px; padding-bottom: 8px;">
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height: 20px; font-weight: 400;">Company Name</div>
                        </td>
                        <td style="width: 332px;text-align: right;vertical-align: top;">
                            <div style="text-align: right; font-family:'Inter', sans-serif; font-size: 12px; font-weight: 600; line-height: 16px; color:#474A55;">
                                {{$order->user->company_name}}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 200px; padding-bottom: 8px;">
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height: 20px; font-weight: 400;">Company Email</div>
                        </td>
                        <td style="width: 332px;text-align: right;vertical-align: top;">
                            <div style="text-align: right; font-family:'Inter', sans-serif; font-size: 12px; font-weight: 600; line-height: 16px; color:#474A55;">
                                <a href="mailto:{{$order->user->email}}" style="font-family:'Inter', sans-serif; font-size: 12px; font-weight: 600; line-height: 16px; color:#474A55; text-decoration: none;">{{$order->user->email}}</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="11"></td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>

    <tr>
        <td align="center" valign="top" style="padding: 0; padding-left: 32px; padding-right: 32px;" class="line">
            <hr color="#F1F1F3" align="center" width="100%" size="1" noshade="" style="margin: 0; padding: 0;">
        </td>
    </tr>

    <tr>
        <td align="center" style="padding-left: 32px; padding-right: 32px;">
            <table width="536" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td height="22"></td>
                    </tr>
                    <tr>
                        <td>
                            <h3 style="margin: 0;font-family:'Inter', sans-serif;
                                                            font-size: 14px; padding-bottom: 5px;
                                                            font-weight: 600;
                                                            line-height: 20px;
                                                           color: #2A2C33;">Hello {!! siteName() !!},</h3>
                            <p style="color: #474A55;
                                                            font-family:'Inter', sans-serif;
                                                            font-size: 12px;
                                                            font-weight: 400;
                                                            line-height: 20px;">
                                {{$order->message}}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td height="26"></td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>


    @foreach($order->orderProducts as $orderProduct)
    <tr>
        <td align="center" valign="top" style="padding: 0; padding-left: 32px; padding-right: 32px;" class="line">
            <hr color="#F1F1F3" align="center" width="100%" size="1" noshade="" style="margin: 0; padding: 0;">
        </td>
    </tr>
    <tr>
        <td align="center" style="padding-left: 32px; padding-right: 32px;">
            <table width="536" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td height="22"></td>
                    </tr>
                    <tr>
                        <td style="width: 72px;">
                            @if($orderProduct->product)
                            <img alt=" {{$orderProduct->product ? $orderProduct->product->full_name : ''}}" class="product-thumb" height="72" width="72"
                                src="{{$orderProduct->product->image_full_url}}"
                                style="width: 72px;height: 72px;">
                            @endif
                        </td>
                        <td style="width: 22px;"></td>
                        <td style="width: 308px;">
                            <div style="font-family:'Inter', sans-serif; font-size: 14px; font-style: normal; font-weight: 500; line-height: 24px; color: #474A55;">
                                Part Number:
                                @if($orderProduct->product)
                                <a href="{!! $orderProduct->product->detail_url !!}">{!! $orderProduct->part_number !!}</a>
                                @else
                                {!! $orderProduct->part_number !!}
                                @endif
                            </div>

                            @if(@$orderProduct->product->full_name)
                            <!-- <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Product Name: <span style="font-weight: 500;">{{$orderProduct->product->full_name}}</span>
                            </div> -->
                            @endif

                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Manufacturer: <span style="font-weight: 500;">{{$orderProduct->manufacturer_name}}</span>
                            </div>

                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Quantity: <span style="font-weight: 500;">{{$orderProduct->quantity}} </span>
                            </div>

                            <div style="color: #676B79; line-height: 24px; font-family:'Inter', sans-serif; font-size: 12px; font-style: normal; font-weight: 400;">
                                Price reference:<span style="font-weight: 500;"> {{$order->currencyExchangeRate->symbol}}{{$orderProduct->formated_target_price}}</span>
                            </div>

                        </td>
                        <td style="width: 22px;"></td>
                        <td style="width: 108px;text-align: right;vertical-align: top;">
                            <div style="text-align: right;  font-family:'Inter', sans-serif; font-size: 16px; font-style: normal; font-weight: 500; line-height: 24px; color: #1B3C7B;">
                                {{$order->currencyExchangeRate->symbol.$orderProduct->formated_total_price}}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="25"></td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
    @endforeach
    @foreach($order->OrderShippingDetails as $data)
    <tr>
        <td align="center" valign="top" style="padding: 0; padding-left: 32px; padding-right: 32px;" class="line">
            <hr color="#F1F1F3" align="center" width="100%" size="1" noshade="" style="margin: 0; padding: 0;">
        </td>
    </tr>
    <tr>
        <!-- START Shipping details -->
        <td align="center" style="padding-left: 32px; padding-right: 32px;">
            <h5>Shipping Details</h5>
            <table width="536" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td height="22"></td>
                    </tr>
                    <tr>
                        <td style="width: 308px;">
                            @if($data->shipping_telephone)
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping telephone: <span style="font-weight: 500;">{{$data->shipping_telephone}}</span>
                            </div>
                            @endif
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping country: <span style="font-weight: 500;">{{$data->shipping_country}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping company name: <span style="font-weight: 500;">{{$data->shipping_company_name}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping first name: <span style="font-weight: 500;">{{$data->shipping_first_name}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping last name: <span style="font-weight: 500;">{{$data->shipping_last_name}}</span>
                            </div>
                            @if($data->shipping_id)
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping id: <span style="font-weight: 500;">{{$data->shipping_id}}</span>
                            </div>
                            @endif
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping address 1: <span style="font-weight: 500;">{{$data->shipping_address_line_1}}</span>
                            </div>
                            @if($data->shipping_address_line_2)
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping address 2: <span style="font-weight: 500;">{{$data->shipping_address_line_2}}</span>
                            </div>
                            @endif
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping city: <span style="font-weight: 500;">{{$data->shipping_city}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping state: <span style="font-weight: 500;">{{$data->shipping_state}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                Shipping postal code: <span style="font-weight: 500;">{{$data->shipping_postal_code}}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    <!-- end Shipping details -->

        @if($data->is_billing_same_as_shipping == 0)
        <td align="center" style="padding-left: 32px; padding-right: 32px;">
            <h5>Billing Details</h5>
            <table width="536" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                        <td height="22"></td>
                    </tr>
                    <tr>
                        <td style="width: 308px;">
                            @if($data->billing_telephone)
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing telephone: <span style="font-weight: 500;">{{$data->billing_telephone}}</span>
                            </div>
                            @endif
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing country: <span style="font-weight: 500;">{{$data->billing_country}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing company name: <span style="font-weight: 500;">{{$data->billing_company_name}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing first name: <span style="font-weight: 500;">{{$data->billing_first_name}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing last name: <span style="font-weight: 500;">{{$data->billing_last_name}}</span>
                            </div>
                            @if($data->billing_id)
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing id: <span style="font-weight: 500;">{{$data->billing_id}}</span>
                            </div>
                            @endif
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing address 1: <span style="font-weight: 500;">{{$data->billing_address_line_1}}</span>
                            </div>
                            @if($data->billing_address_line_2)
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing address 2: <span style="font-weight: 500;">{{$data->billing_address_line_2}}</span>
                            </div>
                            @endif
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing city: <span style="font-weight: 500;">{{$data->billing_city}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing state: <span style="font-weight: 500;">{{$data->billing_state}}</span>
                            </div>
                            <div style="color: #676B79; font-family:'Inter', sans-serif; font-size: 12px; line-height:24px; font-style: normal; font-weight: 400;">
                                billing postal code: <span style="font-weight: 500;">{{$data->billing_postal_code}}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
        @endif
    </tr>
    @endforeach
    <tr>
        <td align="center" valign="top" style="padding: 0; padding-left: 32px; padding-right: 32px;" class="line">
            <hr color="#F1F1F3" align="center" width="100%" size="1" noshade="" style="margin: 0; padding: 0;">
        </td>
    </tr>

    <tr>
        <td align="center" style="padding-left: 32px; padding-right: 32px;">
            <table width="536" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td height="26"></td>
                </tr>
                <tr>
                    <td style="width: 266px;">
                        <div style="color: #676B79;
                                          font-family:'Inter', sans-serif;
                                          font-size: 12px;
                                          font-style: normal; line-height: 24px;
                                          font-weight: 400;">
                            The total price provided is an estimate
                        </div>
                    </td>
                    <td style="width: 266px;text-align: right;vertical-align: top;">
                        <div style="text-align: right;
                                          font-family:'Inter', sans-serif;
                                          color: #2A2C33;
text-align: right;
font-size: 14px;
font-style: normal;
font-weight: 500;
line-height: 24px;">
                            Total Price: <span style="font-size: 18px; color: #1B3C7B;">{{$order->currencyExchangeRate->symbol}}{{$order->total_with_order_currency}}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td height="32" colspan="2">
                        <div style="color: #676B79; line-height: 24px; font-family:'Inter', sans-serif; font-size: 12px; font-style: normal; font-weight: 400;">
                            Payment Status:<span style="font-weight: 500;">@if($order->status == "paid") Received @else {!! $order->status !!} @endif</span>
                        </div>
                    </td>
                </tr>

                </tbody>
            </table>
        </td>
    </tr>
@endsection
