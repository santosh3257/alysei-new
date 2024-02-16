@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>View</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace/orders')}}">Orders</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <table class="order_Table" border="1" width="100%">
                    <thead>
                        <tr>
                            <th style="width:30%;">Billing Address</th>
                            <th style="width:30%;">Shipping Address</th>
                            <th>Payment Method</th>
                            <th class="Order__Summary">Order Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                @if(!empty($order->billingAddress))
                                    {{ $order->billingAddress->first_name }} {{ $order->billingAddress->last_name }} {{ $order->billingAddress->street_address }} {{ $order->billingAddress->street_address_2 }}<br>
                                    {{ $order->billingAddress->city }} {{ $order->billingAddress->state }}<br>
                                    {{ $order->billingAddress->country }} {{ $order->billingAddress->zipcode }}
                                @endif
                            </td>
                            <td>
                                @if(!empty($order->shippingAddress))
                                    {{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }} {{ $order->shippingAddress->street_address }} {{ $order->shippingAddress->street_address_2 }}<br>
                                    {{ $order->shippingAddress->city }} {{ $order->shippingAddress->state }}<br>
                                    {{ $order->shippingAddress->country }} {{ $order->shippingAddress->zipcode }}
                                @endif
                            </td>
                            <td>Online</td>
                            <td>
                                <div class="order_Span">
                                    <p>Item({{$order->num_items_sold}}) Subtotal :<span>${{$order->net_total}}</span></p>
                                    <p>Shipping : <span>${{$order->shipping_total}}</span></p>
                                    <p>Total : <span>${{$order->net_total}}</span></p>
                                    <p>Vat : <span>${{$order->tax_total}}</span></p>
                                    <p>Grand Total : <span>${{$order->total_seles}}</span></p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <table class="order_Table" border="1" width="100%">
                    <thead>
                        <tr>
                            <th>Company Name/Store</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th class="orderDetailAddress">Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($order->sellerInfo))
                        <tr>
                            <td>{{$order->sellerInfo->company_name}}</td>
                            <td>+{{$order->sellerInfo->country_code}} {{$order->sellerInfo->phone}}</td>
                            <td>{{$order->sellerInfo->email}}</td>
                            <td>{{$order->sellerInfo->address}} <br> {{$order->sellerInfo->address1}}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <br>
                <table class="order_Table" border="1" width="100%">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($order->buyerInfo))
                        <tr>
                            <td>{{$order->buyerInfo->company_name}}</td>
                            <td>+{{$order->buyerInfo->country_code}} {{$order->buyerInfo->phone}}</td>
                            <td>{{$order->buyerInfo->email}}</td>
                            <td>{{$order->buyerInfo->address}} <br> {{$order->buyerInfo->address1}}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <br>
                <table class="order_Table" border="1" width="100%">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Product Category</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @if(!empty($order->productItemInfo))
                        @foreach($order->productItemInfo as $key=>$product)
                        <!-- <?php 
                        echo '<pre>';
                        print_r($product->productInfo); 
                        echo '</pre>';
                        ?> -->
                        <tr>
                            <td>{{$key+1}}</td>
                            <td class="cart-product-list">
                                @if(!empty($product->productInfo))
                                    @if(!empty($product->productInfo->product_gallery))
                                <img src="{{$product->productInfo->product_gallery[0]->base_url}}{{$product->productInfo->product_gallery[0]->attachment_medium_url}}" alt="Product-Image">
                                    @endif
                                @endif
                                    </td>
                            <td>
                                @if(!empty($product->productInfo))
                                {{ $product->productInfo->title }}
                                @endif
                            </td>
                            <td>@if(!empty($product->productInfo))
                                    @if(!empty($product->productInfo->productCategory))
                                        {{ $product->productInfo->productCategory->option }}
                                    @endif
                                @endif</td>
                            <td>${{$product->product_price}}</td>
                            <td>{{$product->quantity}}</td>
                            <td>${{$product->product_price*$product->quantity}}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection