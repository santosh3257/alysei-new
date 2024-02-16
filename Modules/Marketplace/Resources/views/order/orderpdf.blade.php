<html>

<head>
  <META http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
  <table border="0" align="center" cellpadding="0" cellspacing="0" width="100%"
    style="max-width: 600px;">
    <tr>
      <td>
        <table border="0" align="center" cellpadding="0" cellspacing="0" width="100%"
          style="max-width:600px;background:#ffffff;padding:0px 25px">
          <tbody>
            <tr>
              <td style="margin:0;padding:0">
                <table border="0" cellpadding="20" cellspacing="0" width="100%"
                  style="background:#ffffff;color:#1a1a1a;line-height:150%;text-align:center;border-bottom:1px solid #e9e9e9;font-family:300 14px &#39;Helvetica Neue&#39;">
                  <tbody>
                    <tr>
                      <td valign="top" align="center" width="100" style="background-color:#ffffff">
                        <img alt="Alysei" style="width:134px" src="https://alysei.com/dist/images/logo.png">
                      </td>
                    </tr>
                  </tbody>
                </table>
                <br>
                <table width="100%"
                  style="background:#ffffff;color:#000000;line-height:150%;text-align:center; border-collapse: collapse;">
                  <tbody>
                    <tr>
                      <td valign="top" width="100" style="text-align:right;font-size: 1rem;">
                        <img alt="Alysei" style="width:65px"
                          src="{{$store->logo->base_url}}{{$store->logo->attachment_url}}">
                        <h4 style="margin:4px;font-size: 1rem;"><span style="font-weight: 600;">Store Name:</span> {{$store->name}}</h4>
                        <p style="margin:4px; font-size: 1rem; font-weight: 400;"><span style="font-weight: 600;">Location:</span> <span>{{$store->location}}</span></p>
                        <p style="margin:4px; font-size: 1rem; font-weight: 400;"><span style="font-weight: 600;">Phone Number:</span> <span>{{$store->phone}}</span></p>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <table style="width: 100%; border-collapse: collapse;">
                  <tbody>
                    <tr>
                      <td colspan="3" style="font-size: 1.25rem; font-weight: 600; padding: 0.5rem; padding-left: 0;">Order No: {{$order->order_id}}</td>
                      <td style="font-size: 1.25rem; font-weight: 600; padding: 0.5rem; padding-right: 0; text-align: right;">April 25, 2016</td>
                    </tr>
                  </tbody>
                </table>
                <table style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr>
                      <th style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: left; padding: 0.5rem;">Product</th>
                      <th style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: left; padding: 0.5rem;">Quantity</th>
                      <th style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: right; padding: 0.5rem;">Price</th>
                      <th style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: right; padding: 0.5rem;">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if(count($order->productItemInfo) > 0)
                    @foreach($order->productItemInfo as $key=>$product)
                    @php $productCost = (int)$product->quantity * (int)$product->product_price; @endphp
                    <tr>
                      <td style="border: 1px solid #ddd; font-size: 1rem; font-weight: 400; text-align: left; padding: 0.5rem;">{{ $product->productInfo->title }}</td>
                      <td style="border: 1px solid #ddd; font-size: 1rem; font-weight: 400; text-align: left; padding: 0.5rem;">{{ $product->quantity }}</td>
                      <td style="border: 1px solid #ddd; font-size: 1rem; font-weight: 400; text-align: right; padding: 0.5rem;">{{ $order->currency.$product->product_price }}</td>
                      <td style="border: 1px solid #ddd; font-size: 1rem; font-weight: 400; text-align: right; padding: 0.5rem;">{{ $order->currency.$productCost }}</td>
                    </tr>
                    @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <td scope="row" colspan="3"
                        style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: left; padding: 0.5rem; text-align: right;">Cart Subtotal </td>
                      <td style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: right; padding: 0.5rem;">{{$order->currency.$order->net_total}}</td>
                    </tr>
                    <tr>
                      <td scope="row" colspan="3"
                        style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: left; padding: 0.5rem;text-align: right;">
                        Tax</td>
                      <td
                        style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: right; padding: 0.5rem;">{{$order->currency.$order->tax_total}}</td>
                    </tr>
                    <tr>
                      <td scope="row" colspan="3" style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: left; padding: 0.5rem;text-align: right;">
                        Shipping Charges</td>
                      <td style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: right; padding: 0.5rem;text-align: right;">{{$order->currency.$order->shipping_total}}</td>
                    </tr>

                    <tr>
                      <td scope="row" colspan="3"
                      style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: left; padding: 0.5rem;text-align: right;">
                        Order Total</td>
                      <td style="border: 1px solid #ddd; font-size: 1rem; font-weight: 600; text-align: left; padding: 0.5rem;text-align: right;">{{$order->currency.$order->total_seles}}</td>
                    </tr>
                  </tfoot>
                </table>
                <table width="100%"
                  style="border-collapse: collapse;">
                  <tbody>
                    <tr>
                      <td valign="top">
                        <h4 style="font-size: 1.25rem; font-weight: 600; margin: 0.5rem 0;">Customer Details</h4>
                        <p style="font-size: 1rem; font-weight: 600; margin: 0.5rem 0;"><strong>Name:</strong> <span style="font-weight: 400;">{{ $order->billingAddress->first_name }} {{ $order->billingAddress->last_name }}</span></p>
                        <p style="font-size: 1rem; font-weight: 600; margin: 0.5rem 0;"><strong>Email:</strong><a
                          href="mailto:{{ $order->billingAddress->email }}" target="_blank"> <span style="font-weight: 400;">{{ $order->billingAddress->email }}</span></a></p>
                        <p style="font-size: 1rem; font-weight: 600; margin: 0.5rem 0;"><strong>Address:</strong> <span style="font-weight: 400;">{{ $order->billingAddress->street_address }} {{ $order->billingAddress->street_address_2}}, {{ $order->billingAddress->city }} {{ $order->billingAddress->state }} {{ $order->billingAddress->country }} - {{ $order->billingAddress->zipcode }}</span></p>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <table width="100%"
                  style="border-collapse: collapse;">
                  <tbody>
                    <tr>
                      <td valign="top">
                        <h4 style="font-size: 1.25rem; font-weight: 600; margin: 0.5rem 0;">Delivery address</h4>
                        <p style="font-size: 1rem; font-weight: 600; margin: 0.5rem 0;"><strong>Name:</strong> <span style="font-weight: 400;">{{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }}</span></p>
                        <p style="font-size: 1rem; font-weight: 600; margin: 0.5rem 0;"><strong>Email:</strong><a
                          href="mailto:{{ $order->billingAddress->email }}" target="_blank"> <span style="font-weight: 400;">{{ $order->shippingAddress->email }}</span></a></p>
                        <p style="font-size: 1rem; font-weight: 600; margin: 0.5rem 0;"><strong>Address:</strong> <span style="font-weight: 400;">{{ $order->shippingAddress->street_address }} {{ $order->shippingAddress->street_address_2}}, {{ $order->shippingAddress->city }} {{ $order->shippingAddress->state }} {{ $order->shippingAddress->country }} - {{ $order->shippingAddress->zipcode }}</span></p>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <table cellspacing="0" cellpadding="6" style="border-collapse: collapse;">
                  <tbody>
                    <tr>
                      <td style="font-size: 1rem; font-weight: 400; padding-left: 0; padding-right: 0;">Please call <b> 212456</b> in case of any doubts or questions. Please reply back to email in case of any issues with prices, packing charges, taxes and other menus issues.</td>
                    </tr>
                  </tbody>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0"
                  style="border-collapse: collapse;">
                  <tbody>
                    <tr>
                      <td>
                        <table style="border-collapse: collapse; padding-top: 0.5rem; padding-bottom: 0.5rem;">
                          <tbody>
                            <tr>
                              <td>Download the App: </td>
                              <td>
                                <a href="#"
                                  target="_blank" style="display: inline-block; padding: 0.25rem;">
                                  <img style="max-height:20px;width:auto"
                                    src="https://res.cloudinary.com/swiggy/image/upload/v1447855172/Android_qt1acy.png" />
                                </a>
                              </td>
                              <td>
                                <a href="#"
                                  target="_blank" style="display: inline-block; padding: 0.25rem;">
                                  <img style="max-height:20px;width:auto"
                                    src="https://res.cloudinary.com/swiggy/image/upload/v1447855170/Apple_e7lnfc.png">
                                </a>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                      <td>
                        <table cellspacing="0" cellpadding="0">
                          <tbody>
                            <tr>
                              <td>
                                <a href="https://www.facebook.com/AlyseiB2B/" target="_blank" style="display: inline-block; padding: 0.25rem; ">
                                  <img style="max-height:20px;width:auto"
                                    src="https://res.cloudinary.com/swiggy/image/upload/v1447855170/Facebook_ezoqwy.png"
                                    alt="Swiggy Facebook" style="display:block">
                                </a>
                              </td>
                              <td>
                                <a href="https://www.youtube.com/channel/UCLS2XGoIFJcqhBCxm9K7OEg" target="_blank" style="display: inline-block; padding: 0.25rem;">
                                  <img style="max-height:20px;width:auto"
                                    src="https://res.cloudinary.com/swiggy/image/upload/v1447855171/Twitter_stmvbr.png"
                                    alt="Swiggy Twitter" style="display:block">
                                </a>
                              </td>
                              <td>
                                <a href="https://www.linkedin.com/company/alysei/" target="_blank" style="display: inline-block; padding: 0.25rem;">
                                  <img style="max-height:20px;width:auto"
                                    src="https://res.cloudinary.com/swiggy/image/upload/v1447855171/Pinterest_dd2nv9.png"
                                    alt="Swiggy pinterest" style="display:block" border="0"></a>
                              </td>
                              <td>
                                <a href="https://www.instagram.com/alyseilaunch2020/" target="_blank" style="display: inline-block; padding: 0.25rem;">
                                  <img style="max-height:20px;width:auto"
                                    src="https://res.cloudinary.com/swiggy/image/upload/v1447855170/Instagram_okx3pg.png"
                                    alt="Swiggy instagram" style="display:block" border="0"></a>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2" style="text-align: center;">
                        © 2023 Alysei. All rights reserved.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </table>
</body>

</html>