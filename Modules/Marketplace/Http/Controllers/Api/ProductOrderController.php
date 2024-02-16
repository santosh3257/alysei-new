<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Illuminate\Support\Facades\DB;
use Modules\Marketplace\Entities\MarketplaceOrder;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceOrderItem;
use Modules\Marketplace\Entities\MarketplaceOrderItemTax;
use Modules\Marketplace\Entities\MarketplaceOrderTransaction;
use Modules\Marketplace\Entities\MarketplaceOrderUserAddress;
use Modules\Marketplace\Entities\MarketplaceOrderShippingAddress;
use Modules\Marketplace\Entities\MarketplaceTaxClasses;
use Modules\Marketplace\Entities\MarketplaceTax;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\Marketplace\Entities\MapProductOffer;
use Modules\Marketplace\Entities\MapClassTax;
use Modules\Marketplace\Entities\MarketplaceStore;
use App\Events\BuyerOrderConfirmationEvent;
use App\Events\PaymentRequestEvent;
use App\Events\OrderStatusChangeEmailEvent;
use Modules\User\Entities\User;
use PDF;
use Storage;
use App\Http\Traits\UploadImageTrait;
use Carbon\Carbon;
use App\Http\Traits\NotificationTrait;
use App\Notification;
use Kreait\Firebase\Factory;
use Modules\User\Entities\DeviceToken;
use Illuminate\Support\Facades\File;
use League\Flysystem\Filesystem;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem\AwsS3v3\AwsS3Adapter;
use Modules\Marketplace\Entities\Incoterms;

class ProductOrderController extends Controller
{
    use UploadImageTrait;
    use NotificationTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    //Create Firebase connection
    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();    
        return $database;
    }

    // Update user notification 
    public function updateUserNotificationCountFirebase($id)
    {
        try{
            $reference = $this->conn_firbase()->getReference('users');
            $snapshot = $reference->getChild($id);
            $getKey = $snapshot->getValue();
            if(isset($getKey['notification'])){
                $countNotification = $getKey['notification'];

                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => $countNotification+1
                ]);

                return $countNotification+1;
            }
            else{
                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => 0
                ]);

                return 0;
            }
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }
    /**
     * Display a listing of the product orders.
     * @return Response
     */
    public function getMyOrders(Request $request)
    {
        try
        {
            $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';
            $user = $this->user;
            if($user->role_id == 3){
                $query = MarketplaceOrder::with('buyerInfo','transactionInfo','shippingAddress','billingAddress','productItemInfo.productInfo','productItemInfo.productInfo.productCategory','productItemInfo.productInfo.product_gallery')->where('seller_id',$user->user_id)->orderBy('created_at','desc');
                if(!empty($request->order_id)){
                    $query->where('order_id',$request->order_id);
                }
                if(!empty($request->status)){
                    if($request->status == 'ongoing'){
                        $query->whereIn('status',['processing','in transit','on hold']);
                    }
                    else{
                        $query->where('status',$request->status);
                    }
                }
                if(!empty($request->date)){
                    $query->whereDate('created_at', '=', $request->date);
                }
                if(!empty($request->customer)){
                    $customerName = $request->customer;
                    $query->where(function ($query) use ($customerName) {
                        $query->whereHas('buyerInfo', function ($q) use ($customerName) {
                            $q->where('name', 'like', '%' . $customerName . '%')->orWhere('company_name', 'like', '%' . $customerName . '%');
                        });
                    });;
                }
                
                $myOrders = $query->paginate(10);
            }
            else{
                $query = MarketplaceOrder::with('sellerInfo','transactionInfo','shippingAddress','billingAddress','productItemInfo.productInfo','productItemInfo.productInfo.productCategory','productItemInfo.productInfo.product_gallery')->where('buyer_id',$user->user_id);
                if(!empty($request->order_id)){
                    $query->where('order_id',$request->order_id);
                }
                if(!empty($request->status)){
                    if($request->status == 'ongoing'){
                        $query->whereIn('status',['processing','in transit','on hold']);
                    }
                    else{
                        $query->where('status',$request->status);
                    }
                }
                $myOrders = $query->orderBy('created_at','desc')->paginate(10);
            }

            if($myOrders){
                foreach($myOrders as $key=>$order){
                    $myOrders[$key]->baseUrl = $basePath;
                }
            }
            return response()->json(['success' => $this->successStatus,
                                    'data' => $myOrders,
                                    ], $this->successStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /**
     * Store a newly created order in storage.
     * @param Request $request
     * @return Response
     */
    public function makeNewOrder(Request $request)
    {
        // try
        // {
            DB::beginTransaction();
            $user = $this->user;
            if(isset($request->offer_map_id) && !empty($request->offer_map_id)){
                $validator = Validator::make($request->all(), [ 
                    'store_id' => 'required|integer',
                    'product_id' => 'required|array',
                    'product_id.*' => 'integer',
                    "product_quantity" => 'required|array',
                    'product_quantity.*' => 'integer',
                    "product_price" => 'required|array',
                    'product_price.*' => 'numeric',
                    "offer_map_id" => 'required|array',
                    'offer_map_id.*' => 'integer',
                    "total_amount" => 'required|numeric',
                    "shipping_total" => 'required|numeric',
                    'total_tax' => 'required|numeric',
                    'net_total' => 'required|numeric',
                    'intent_id' => 'required'
                ]);
            }
            else{
                $validator = Validator::make($request->all(), [ 
                    'store_id' => 'required|integer',
                    'product_id' => 'required|array',
                    'product_id.*' => 'integer',
                    "product_quantity" => 'required|array',
                    'product_quantity.*' => 'integer',
                    "product_price" => 'required|array',
                    'product_price.*' => 'numeric',
                    "total_amount" => 'required|numeric',
                    "shipping_total" => 'required|numeric',
                    'total_tax' => 'required|numeric',
                    'net_total' => 'required|numeric',
                    'intent_id' => 'required'
                ]);
            }
            
        

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $productIds = $request->product_id;
            $productQuantity = $request->product_quantity;
            $productPrice = $request->product_price;
            $frontTotalAmount = $request->total_amount;
            $netTotal = $request->net_total;
            $totalTax = $request->total_tax;
            // Check Product Quantity availablity
            foreach($productIds as $key=>$productId){
                // Get product Info
                $product = MarketplaceProduct::select('title','marketplace_product_id','quantity_available','min_order_quantity')->where('marketplace_product_id',$productId)->first();
                if($product){
                    if($product->min_order_quantity > $productQuantity[$key]){
                        return response()->json(['success' => $this->validationStatus,
                                        'message' => $product->title.' quantity should be grather than or equal to'.$product->min_order_quantity.'.',
                                        ], $this->validationStatus);
                    }
                }
                else{
                    return response()->json(['success' => $this->validationStatus,
                                        'message' => 'This product is not available at this moment',
                                        ], $this->validationStatus);
                }
            }
            //$backendTotalSales = $this->getTotalSaleAmount($request->offer_map_id, $productIds, $productQuantity, $productPrice, $request->shipping_total);
            $totalQuantity = 0;
            foreach($productQuantity as $qty){
                $totalQuantity += $qty;
            }
            // if($frontTotalAmount == $backendTotalSales){
                $store = MarketplaceStore::find($request->store_id);
                if($store){
                    $order = new MarketplaceOrder();
                    $order->seller_id = $store->user_id;
                    $order->buyer_id = $user->user_id;
                    $order->store_id = $request->store_id;
                    $order->num_items_sold = $totalQuantity;
                    $order->total_seles = $frontTotalAmount;
                    $order->tax_total = $totalTax;
                    $order->returning_customer = $request->returning_customer;
                    $order->shipping_total = $request->shipping_total;
                    $order->net_total = $netTotal;
                    $order->status = 'pending';
                    $order->currency = '$';
                    $order->save();
                    if($order->order_id){
                        $saveSaleProduct = $this->saveOrderItemTable($order->order_id, $request->offer_map_id, $productIds, $productQuantity, $productPrice);
                        if(!empty($request->billing_address_id) && isset($request->billing_address_id)){
                            $order->billing_id = $request->billing_address_id;
                        }
                        else{
                            $billingAddressId = $this->addNewOrderAddress($order->order_id, $request->billing_address, 'billing');
                            $order->billing_id = $billingAddressId;
                        }

                        if(!empty($request->shipping_address_id) && isset($request->shipping_address_id) && $request->same_billing_address == false){
                            $order->shipping_id = $request->shipping_address_id;
                        }
                        else{
                            if($request->same_billing_address == true){
                                if(!empty($request->billing_address_id)){
                                    $shippingAddressId = $this->addNewOrderAddress($order->order_id, $request->billing_address, 'shipping', $request->billing_address_id);
                                }
                                else{
                                    $shippingAddressId = $this->addNewOrderAddress($order->order_id, $request->billing_address, 'shipping');
                                }
                            }
                            else{
                                $shippingAddressId = $this->addNewOrderAddress($order->order_id, $request->shipping_address, 'shipping');
                            }
                            $order->shipping_id = $shippingAddressId;
                        }
                        $order->save();
                        

                        $payment = new MarketplaceOrderTransaction();
                        $payment->seller_id = $store->user_id;
                        $payment->buyer_id = $user->user_id;
                        $payment->order_id = $order->order_id; 
                        $payment->transaction_id = null;
                        $payment->intent_id = $request->intent_id;
                        $payment->paid_amount = $frontTotalAmount;
                        $payment->status = 'pending';
                        $payment->currency = '$';
                        $payment->save(); 

                        DB::commit();

                        $title_en = "new order has been successfully placed";
                        $title_it = "il nuovo ordine è stato effettuato con successo";
                        $selectedLocale = $this->pushNotificationUserSelectedLanguage($order->seller_id);
                        if($selectedLocale == 'en'){
                            $title1 = $user->company_name." new order has been successfully placed";
                        }
                        else{
                            $title1 = $user->company_name." fatto nuovo ordine";
                        }

                        $saveNotification = new Notification;
                        $saveNotification->from = $user->user_id;
                        $saveNotification->to = $order->seller_id;
                        $saveNotification->notification_type = '12'; //view Order
                        $saveNotification->title_it = $title_it;
                        $saveNotification->title_en = $title_en;
                        $saveNotification->redirect_to = 'order_screen';
                        $saveNotification->redirect_to_id = $order->order_id;

                        $saveNotification->sender_id = $user->user_id;
                        $saveNotification->sender_name = $user->company_name;
                        $saveNotification->sender_image = null;
                        $saveNotification->post_id = $order->order_id;
                        $saveNotification->connection_id = null;
                        $saveNotification->sender_role = $user->role_id;
                        $saveNotification->comment_id = null;
                        $saveNotification->reply = null;
                        $saveNotification->likeUnlike = null;
                        $saveNotification->save();

                        $tokens = DeviceToken::where('user_id', $order->seller_id)->get();
                        $notificationCount = $this->updateUserNotificationCountFirebase($order->seller_id);
                        if(count($tokens) > 0)
                        {
                            $collectedTokenArray = $tokens->pluck('device_token');
                            $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $user->company_name, null, $order->order_id, null, $user->role_id, null,null,null);

                            $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $user->company_name, null, null, $order->order_id, null, $user->role_id, null,null,null,null,null, $notificationCount);
                        }
                        //$this->sendMailAndGenerateInvoicePDF($order->order_id);
                        return response()->json(['success' => $this->successStatus,
                                    'message' => 'Order has been created successfully',
                                    'order_id' => $order->order_id,
                                    ], $this->successStatus);
                    }
                }
                $message = 'Something went wrong try later';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
            // }
            // else{
            //     $message = 'Opps! Total sale amount does not match.';
            //     return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
            // }

           
           
        // }
        // catch(\Exception $e)
        // {
        //     return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        // }
    }

    /**
     * Save Order Payment completed in storage.
     * @param Request $request
     * @return Response
     */
    public function orderPaymentCompleted(Request $request){
        try{
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'order_id' => 'required|numeric',
                'intent_id' => 'required',
            ]);
        
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $order = MarketplaceOrder::where('order_id',$request->order_id)->where('buyer_id',$user->user_id)->first();
            if($order){

                

                $transaction = MarketplaceOrderTransaction::where('order_id',$request->order_id)->where('intent_id',$request->intent_id)->first();
                if($transaction){
                    
                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                    $paymentInfo = $stripe->paymentIntents->retrieve(
                            $request->intent_id,
                            []
                        );
                    
                        $transaction->status = $paymentInfo->status;
                        $transaction->transaction_id = $paymentInfo->transaction_id;
                        $transaction->charge_id = $paymentInfo->latest_charge;
                        $transaction->save();

                        if($paymentInfo->status=='succeeded'){
                            $orderItem = MarketplaceOrderItem::where('order_id',$request->order_id)->first();
                            if($orderItem){
                                if(!empty($orderItem->offer_map_id)){
                                    $offer = ProductOffer::where('offer_id',$orderItem->offer_map_id)->first();
                                    if($offer){
                                        $offer->order_id = $request->order_id;
                                        $offer->save();
                                    }
                                }
                            }
                            $this->sendMailAndGenerateInvoicePDF($request->order_id);
                            return response()->json(['success' => $this->successStatus,
                                    'message' => 'Your Order has been created successfully',
                                    ], $this->successStatus);
                        }
                        else{
                            $order->status = 'cancelled';
                            $order->save();
                            $message = 'Your order has been cancelled because your payment is not marked success at our end. If the amount has been debited your account please contact to site admin. Thanks';
                            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);        
                        }
                }
                else{
                    $message = 'Your payment intent id invalid';
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
                }
                // $title_en = "Order Payment Successed";
                // $title_it = "Pagamento dell'ordine riuscito";
                // $selectedLocale = $this->pushNotificationUserSelectedLanguage($order->seller_id);
                // if($selectedLocale == 'en'){
                //     $title1 = $user->company_name." has been payment success";
                // }
                // else{
                //     $title1 = $user->company_name." è stato un successo nel pagamento";
                // }

                // $saveNotification = new Notification;
                // $saveNotification->from = $user->user_id;
                // $saveNotification->to = $order->seller_id;
                // $saveNotification->notification_type = 'order'; //post share
                // $saveNotification->title_it = $title_it;
                // $saveNotification->title_en = $title_en;
                // $saveNotification->redirect_to = 'order_screen';
                // $saveNotification->redirect_to_id = $order->order_id;

                // $saveNotification->sender_id = $user->user_id;
                // $saveNotification->sender_name = $user->company_name;
                // $saveNotification->sender_image = null;
                // $saveNotification->post_id = $order->order_id;
                // $saveNotification->connection_id = null;
                // $saveNotification->sender_role = $user->role_id;
                // $saveNotification->comment_id = null;
                // $saveNotification->reply = null;
                // $saveNotification->likeUnlike = null;
                // $saveNotification->save();

                // $tokens = DeviceToken::where('user_id', $order->seller_id)->get();
                // $notificationCount = $this->updateUserNotificationCountFirebase($order->seller_id);
                // if(count($tokens) > 0)
                // {
                //     $collectedTokenArray = $tokens->pluck('device_token');
                //     $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $user->company_name, null, $order->order_id, null, $user->role_id, null,null,null);

                //     $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $user->company_name, null, null, $order->order_id, null, $user->role_id, null,null,null,null,null, $notificationCount);
                // }
                
            }
            else{
                $message = 'Your are not authrised person';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    // Send Order Mail & Create Invoice PDF
    private function sendMailAndGenerateInvoicePDF($order_id){
        $order = MarketplaceOrder::with('buyerInfo','transactionInfo','shippingAddress','billingAddress','productItemInfo.productInfo','productItemInfo.productInfo.productCategory','productItemInfo.productInfo.product_gallery')->where('order_id',$order_id)->first();
        if($order){
            event(new BuyerOrderConfirmationEvent($order));
            // $user = User::where('user_id',$order->buyer_id)->first()->toArray();
            // $store = MarketplaceStore::with('logo')->where('marketplace_store_id',$order->store_id)->first();
            // $pdf = PDF::loadView('marketplace::order.orderpdf', compact('order','user','store'));
            // $orderPdfLink = time().'.pdf';
            // //Storage::put('public/pdf/'.$orderPdfLink, $pdf->output());
            // Storage::disk('s3')->put('invoice/'.$orderPdfLink, $pdf->output(), 'public');
            // //$pdf->save(public_path('order/pdf/'.$orderPdfLink));
            // //$url = $this->uploadOrderInvoicePDFS3($pdf);
            // $order->invoice_name = 'invoice/'.$orderPdfLink;
            // $order->save();
        }
    }

    // Order Transaction code
    private function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Save Order Items
     * @return Response
     */
    private function saveOrderItemTable($order_id, $offer_map_id, $productIds, $productQuantity, $productPrice){

        foreach($productIds as $key=>$productId){
            
            // Get product Info
            $product = MarketplaceProduct::select('marketplace_product_id','user_id','marketplace_store_id','class_tax_id','product_price','quantity_available')->with('getProductTax.getTaxClasses.getTaxDetail')->where('marketplace_product_id',$productId)->first();
            
            if($product){
                $product_price = $product->product_price;
                $orderQuantity = (int)$productQuantity[$key];
                $productAvailableQuantity = (int)$product->quantity_available;
                $mapId = null;
                // Check Product Offer
                if(!empty($offer_map_id) && count($offer_map_id) > 0){
                    $offerPrice = MapProductOffer::find($offer_map_id[0]);
                    $product_price = $productPrice[$key];
                    $mapId = $offer_map_id[0];
                }
                $mapOrderItem = new MarketplaceOrderItem();
                $mapOrderItem->order_id = $order_id;
                $mapOrderItem->product_id = $product->marketplace_product_id;
                $mapOrderItem->tax_class_id = $product->class_tax_id;
                $mapOrderItem->offer_map_id = $mapId;
                $mapOrderItem->product_price = $product_price;
                $mapOrderItem->quantity = $productQuantity[$key];
                $mapOrderItem->save(); 

                //Save tax in a table
                if(!empty($product->getProductTax)){
                    if(!empty($product->getProductTax->getTaxClasses)){
                        foreach($product->getProductTax->getTaxClasses as $taxIdx=>$tax){
                            if(!empty($tax->getTaxDetail)){
                                $orderItemTax = new MarketplaceOrderItemTax();
                                $orderItemTax->order_id = $order_id;
                                $orderItemTax->order_item_id = $mapOrderItem->id;
                                $orderItemTax->tax_name = $tax->getTaxDetail->tax_name;
                                $orderItemTax->tax_rate = $tax->getTaxDetail->tax_rate;
                                $orderItemTax->tax_type = $tax->getTaxDetail->tax_type;
                                $orderItemTax->save(); 
                            }
                        }
                    }
                }
                
                //$remainingStock = $productAvailableQuantity - $orderQuantity;

                //$product->quantity_available = $remainingStock;
                $product->save();
            }
        }
    }

    /**
     * Calculate total sale amount
     * @return Response
     */
    private function getTotalSaleAmount($offer_map_id, $productIds, $productQuantity, $productPrice, $shipping_total){
        $total_tax = 0;
        $totalProductPrice = 0;
        foreach($productIds as $key=>$productId){

            // Get product Info
            $product = MarketplaceProduct::select('marketplace_product_id','user_id','marketplace_store_id','class_tax_id','product_price')->with('getProductTax.getTaxClasses.getTaxDetail')->where('marketplace_product_id',$productId)->first();
            
            if($product){
                
                // Check Product Offer
                if(!empty($offer_map_id) && count($offer_map_id) > 0){
                    $offerId = $offer_map_id;
                    $offerPrice = MapProductOffer::find($offerId[$key]);
                    $total = $productQuantity[$key] * $offerPrice->price;
                    $totalProductPrice += $total;
                    if($offerPrice){
                        if($productPrice[$key] != $offerPrice->price){
                            $message = 'Opps! Product price and offered price does not match';
                            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
                        }

                        if((int)$productQuantity[$key] < $offerPrice->quantity){
                            $message = 'Opps! Please select at least min '.$offerPrice->quantity.' quantity';
                            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
                        }
                    }
                    else{
                        $message = 'Something went wrong try later';
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
                    }
                }
                else{
                    if($productPrice[$key] != $product->product_price){
                        $message = 'Opps! Product price and your price does not match';
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
                    }
                    $total = $productQuantity[$key] * $product->product_price;
                    $totalProductPrice += $total;
                }

                // Calculate product Tax
                if(!empty($product->getProductTax)){
                    if(!empty($product->getProductTax->getTaxClasses)){
                        foreach($product->getProductTax->getTaxClasses as $taxIdx=>$tax){
                            if($tax->getTaxDetail->tax_type == 'fixed'){
                                $total_tax += $tax->getTaxDetail->tax_rate;
                            }
                            else{                                    
                                $total_tax += ( $total * $tax->getTaxDetail->tax_rate)/100;
                            }
                        }
                    }
                }
            }
        }
        return $totalProductPrice + $total_tax + $shipping_total;
    }

    // save Order Billing/Shipping Address 
    public function addNewOrderAddress($order_id, $addressObject, $address_type, $billing_address_id = null){
        
          
            $user = $this->user;
            if($address_type == 'shipping'){
                if($billing_address_id==null){
                    $shippingAddressExist = MarketplaceOrderShippingAddress::where('user_id',$user->user_id)->where('first_name',$addressObject['first_name'])->where('last_name',$addressObject['last_name'])->where('email',$addressObject['email'])->where('street_address',$addressObject['street_address'])->where('street_address_2',$addressObject['street_address_2'])->where('city',$addressObject['city'])->where('state',$addressObject['state'])->where('country',$addressObject['country'])->where('zipcode',$addressObject['zipcode'])->first();

                    if($shippingAddressExist){
                        return $shippingAddressExist->id;
                    }
                    else{
                        $shippingAddress = new MarketplaceOrderShippingAddress();
                        $shippingAddress->order_id = $order_id;
                        $shippingAddress->user_id = $user->user_id;
                        $shippingAddress->first_name = $addressObject['first_name'];
                        $shippingAddress->last_name = $addressObject['last_name'];
                        $shippingAddress->email = $addressObject['email'];
                        if(isset($addressObject['company_name'])){
                            $shippingAddress->company_name = $addressObject['company_name'];
                        }
                        $shippingAddress->street_address = $addressObject['street_address'];
                        $shippingAddress->street_address_2 = $addressObject['street_address_2'];
                        $shippingAddress->city = $addressObject['city'];
                        $shippingAddress->state = $addressObject['state'];
                        $shippingAddress->country = $addressObject['country'];
                        $shippingAddress->zipcode = $addressObject['zipcode'];
                        $shippingAddress->save();
                        return $shippingAddress->id;
                    }
                }
                else{
                    $billingAddress = MarketplaceOrderUserAddress::find($billing_address_id);
                    if($billingAddress){
                        $shippingAddressExist = MarketplaceOrderShippingAddress::where('user_id',$user->user_id)->where('first_name',$billingAddress->first_name)->where('last_name',$billingAddress->last_name)->where('email',$billingAddress->email)->where('street_address',$billingAddress->street_address)->where('street_address_2',$billingAddress->street_address_2)->where('city',$billingAddress->city)->where('state',$billingAddress->state)->where('country',$billingAddress->country)->where('zipcode',$billingAddress->zipcode)->first();

                        if($shippingAddressExist){
                            return $shippingAddressExist->id;
                        }
                        else{
                            $shippingAddress = new MarketplaceOrderShippingAddress();
                            $shippingAddress->order_id = $order_id;
                            $shippingAddress->user_id = $user->user_id;
                            $shippingAddress->first_name = $billingAddress->first_name;
                            $shippingAddress->last_name = $billingAddress->last_name;
                            $shippingAddress->email = $billingAddress->email;
                            $shippingAddress->company_name = $billingAddress->company_name;
                            $shippingAddress->street_address = $billingAddress->street_address;
                            $shippingAddress->street_address_2 = $billingAddress->street_address_2;
                            $shippingAddress->city = $billingAddress->city;
                            $shippingAddress->state = $billingAddress->state;
                            $shippingAddress->country = $billingAddress->country;
                            $shippingAddress->zipcode = $billingAddress->zipcode;
                            $shippingAddress->save();
                            return $shippingAddress->id;
                        }
                    }
                }
            }else{
                $userAddress = new MarketplaceOrderUserAddress();
                $userAddress->order_id = $order_id;
                $userAddress->user_id = $user->user_id;
                $userAddress->first_name = $addressObject['first_name'];
                $userAddress->last_name = $addressObject['last_name'];
                $userAddress->email = $addressObject['email'];
                if(isset($addressObject['company_name'])){
                $userAddress->company_name = $addressObject['company_name'];
                }
                $userAddress->street_address = $addressObject['street_address'];
                $userAddress->street_address_2 = $addressObject['street_address_2'];
                $userAddress->city = $addressObject['city'];
                $userAddress->state = $addressObject['state'];
                $userAddress->country = $addressObject['country'];
                $userAddress->zipcode = $addressObject['zipcode'];
                $userAddress->save();
                return $userAddress->id;
            }
       
    }

    /**
     * Single order the specified resource.
     * @param int $id
     * @return Response
     */
    public function singleOrderInfo($order_id)
    {
        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';
        $user = $this->user;
        if($user->role_id == 3){
            $order = MarketplaceOrder::with('buyerInfo','transactionInfo','shippingAddress','billingAddress','productItemInfo.productInfo','productItemInfo.productInfo.productCategory','productItemInfo.productInfo.product_gallery')->where('order_id',$order_id)->first();
            
        }
        else{
            $order = MarketplaceOrder::with('sellerInfo','transactionInfo','shippingAddress','billingAddress','productItemInfo.productInfo','productItemInfo.productInfo.productCategory','productItemInfo.productInfo.product_gallery')->where('order_id',$order_id)->first();
        }
        if($order){
            $order->baseUrl = $basePath;
            return response()->json(['success' => $this->successStatus,
                                'data' => $order,
                                ], $this->successStatus);
        }
        else{
                $message = 'Order not found';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }


    }

    /**
     * GET My Customers.
     * @param int $id
     * @return Response
     */
    public function getMyCustomers(Request $request)
    {
        $query = MarketplaceOrder::with('buyerInfo.country')->where('seller_id',$this->user->user_id)->groupBy('buyer_id');
        
        if(!empty($request->customer)){
            $customerName = $request->customer;
            $query->where(function ($query) use ($customerName) {
                $query->whereHas('buyerInfo', function ($q) use ($customerName) {
                    $q->where('name', 'like', '%' . $customerName . '%')->orWhere('company_name', 'like', '%' . $customerName . '%');
                });
            });
        }
        // $sort_by = 'DESC';
        // // if(!empty($request->sort_by)){
        // //     $sort_by = $request->sort_by;
        // // }
        // $query->where(function ($query) use ($sort_by) {
        //     $query->whereHas('buyerInfo', function ($q) use ($sort_by) {
        //         $q->orderBy('company_name', $sort_by); 
        //     });
        // });
        $myCustomers = $query->paginate(20);
        if($myCustomers){
            foreach($myCustomers as $key=>$customer){
                $myCustomers[$key]->completed_order = MarketplaceOrder::where('seller_id',$this->user->user_id)->where('buyer_id',$customer->buyer_id)->where('status','completed')->count();
                $lastRecord = MarketplaceOrder::where('seller_id',$this->user->user_id)->where('buyer_id',$customer->buyer_id)->where('status','completed')->latest()->first();
                if($lastRecord){
                    $myCustomers[$key]->last_order_date = $lastRecord->created_at;
                }
                else{
                    $myCustomers[$key]->last_order_date = null;
                }
            }
        }
        return response()->json(['success' => $this->successStatus,
                                    'data' => $myCustomers,
                                    ], $this->successStatus);
    }

    // Get my customer info
    public function getMyCustomerInfo($customer_id)
    {
        $customer = User::select('user_id','company_name','role_id','email','phone','country_code','country_iso','address','address1','profile_percentage','created_at')->where('user_id', $customer_id)->first();

        if($customer){
            $orderCompleted = MarketplaceOrder::where('buyer_id',$customer_id)->where('status','completed')->count();
            $orderCancelled = MarketplaceOrder::where('buyer_id',$customer_id)->where('status','cancelled')->count();
            $customer->orderCompleted = $orderCompleted;
            $customer->orderCancelled = $orderCancelled;

        }

        return response()->json(['success' => $this->successStatus,
                            'customer' => $customer,
                            ], $this->successStatus);

    }

    // Add Order Billing/Shipping Address 
    public function addOrderAddress(Request $request){
        try{
            DB::beginTransaction();
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'address_type' => 'required|in:billing,shipping',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'street_address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'country' => 'required',
                'zipcode' => 'required',
            ]);
        
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->address_type == 'shipping'){
                $shippingAddress = new MarketplaceOrderShippingAddress();
                $shippingAddress->user_id = $user->user_id;
                $shippingAddress->first_name = $request->first_name;
                $shippingAddress->last_name = $request->last_name;
                $shippingAddress->email = $request->email;
                $shippingAddress->company_name = $request->company_name;
                $shippingAddress->street_address = $request->street_address;
                $shippingAddress->street_address_2 = $request->street_address_2;
                $shippingAddress->city = $request->city;
                $shippingAddress->state = $request->state;
                $shippingAddress->country = $request->country;
                $shippingAddress->zipcode = $request->zipcode;
                $shippingAddress->save();
            }else{
                $userAddress = new MarketplaceOrderUserAddress();
                $userAddress->user_id = $user->user_id;
                $userAddress->first_name = $request->first_name;
                $userAddress->last_name = $request->last_name;
                $userAddress->email = $request->email;
                $userAddress->company_name = $request->company_name;
                $userAddress->street_address = $request->street_address;
                $userAddress->street_address_2 = $request->street_address_2;
                $userAddress->city = $request->city;
                $userAddress->state = $request->state;
                $userAddress->country = $request->country;
                $userAddress->zipcode = $request->zipcode;
                $userAddress->save();
            }

            DB::commit();
            return response()->json(['success' => $this->successStatus,
                        'message' => 'Address has been successfully saved.',
                        ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    // Get My address
    public function getMyAddress(){
        $user = $this->user;
        $billingAddress = MarketplaceOrderUserAddress::where('user_id',$user->user_id)->get();
        if($billingAddress){
            foreach($billingAddress as $key=>$add){
                $lastId = MarketplaceOrder::select('billing_id')->where('buyer_id',$user->user_id)->latest()->first();
                if($lastId){
                    if($lastId->billing_id == $add->id){
                        $billingAddress[$key]->checked = true;
                    }
                    else{
                        $billingAddress[$key]->checked = false;
                    }
                }
                else{
                    if($key == 0){
                        $billingAddress[$key]->checked = true;
                    }
                    else{
                        $billingAddress[$key]->checked = false;
                    }
                }
            }
        }
        $shippingAddress = MarketplaceOrderShippingAddress::where('user_id',$user->user_id)->get();
        if($shippingAddress){
            foreach($shippingAddress as $key=>$add){
                $lastId = MarketplaceOrder::select('shipping_id')->where('buyer_id',$user->user_id)->latest()->first();
                if($lastId){
                    if($lastId->shipping_id == $add->id){
                        $shippingAddress[$key]->checked = true;
                    }
                    else{
                        $shippingAddress[$key]->checked = false;
                    }
                }
                else{
                    if($key == 0){
                        $shippingAddress[$key]->checked = true;
                    }
                    else{
                        $shippingAddress[$key]->checked = false;
                    }
                }
            }
        }

        return response()->json(['success' => true,
                                    'billing_address' => $billingAddress,
                                    'shipping_address' => $shippingAddress,
                                    ], $this->successStatus);
       
    }


    // Update my billing/shipping Address
    public function updateMyAddress(Request $request, $type, $id){
        try{
            $validator = Validator::make($request->all(), [ 
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'street_address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'country' => 'required',
                'zipcode' => 'required',
            ]);
        
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if (in_array($type,["billing","shipping"])){
                if($type == 'billing'){
                    $address = MarketplaceOrderUserAddress::where('id',$id)->first();
                    
                }
                else{
                    $address = MarketplaceOrderShippingAddress::where('id',$id)->first();
                }

                if($address){
                    $address->first_name = $request->first_name;
                    $address->last_name = $request->last_name;
                    $address->email = $request->email;
                    $address->company_name = $request->company_name;
                    $address->street_address = $request->street_address;
                    $address->street_address_2 = $request->street_address_2;
                    $address->city = $request->city;
                    $address->state = $request->state;
                    $address->country = $request->country;
                    $address->zipcode = $request->zipcode;
                    $address->save();

                    return response()->json(['success' => $this->successStatus,
                            'message' => 'Address has been update successfully.',
                            ], $this->successStatus);
                }
                else{
                    return response()->json(['errors'=>"No record found on this id.",'success' => $this->validationStatus], $this->validationStatus);
                }
            }
            else{
                return response()->json(['errors'=>"Address type doesn't match",'success' => $this->validationStatus], $this->validationStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /**
     * Update order status the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function changeOrderStatus(Request $request, $order_id)
    {
        try{
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'status' => 'required|in:pending,processing,in transit,on hold,completed,cancelled,faild',
            ]);
        
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $order = MarketplaceOrder::where('order_id',$order_id)->where('seller_id',$user->user_id)->first();
            if($order){
                $order->status = $request->status;
                $order->save();

                if($request->status == 'processing'){
                    $status = 'accepted';
                }
                else{
                    $status = $request->status;
                }
                $title_en = "Order status has been changed to ".$status;
                $title_it = "Lo stato dell'ordine è stato modificato in ".$status;
                $selectedLocale = $this->pushNotificationUserSelectedLanguage($order->buyer_id);
                if($selectedLocale == 'en'){
                    $title1 = $user->company_name." changed order status to ".$status;
                }
                else{
                    $title1 = $user->company_name." stato dell'ordine modificato to ".$status;
                }

                $saveNotification = new Notification;
                $saveNotification->from = $user->user_id;
                $saveNotification->to = $order->buyer_id;
                $saveNotification->notification_type = '12'; 
                $saveNotification->title_it = $title_it;
                $saveNotification->title_en = $title_en;
                $saveNotification->redirect_to = 'order_screen';
                $saveNotification->redirect_to_id = $order_id;

                $saveNotification->sender_id = $user->user_id;
                $saveNotification->sender_name = $user->company_name;
                $saveNotification->sender_image = null;
                $saveNotification->post_id = $order_id;
                $saveNotification->connection_id = null;
                $saveNotification->sender_role = $user->role_id;
                $saveNotification->comment_id = null;
                $saveNotification->reply = null;
                $saveNotification->likeUnlike = null;
                $saveNotification->save();

                $tokens = DeviceToken::where('user_id', $order->buyer_id)->get();
                $notificationCount = $this->updateUserNotificationCountFirebase($order->buyer_id);
                if(count($tokens) > 0)
                {
                    $collectedTokenArray = $tokens->pluck('device_token');
                    $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $user->company_name, null, $order_id, null, $user->role_id, null,null,null);

                    $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $user->company_name, null, null, $order_id, null, $user->role_id, null,null,null,null,null, $notificationCount);
                }

                if($order->status == 'cancelled' || $order->status == 'processing' || $order->status == 'completed'){
                    event(new OrderStatusChangeEmailEvent($order));
                }
                return response()->json(['success' => $this->successStatus,
                            'message' => 'Order'.' '.$status,
                            ], $this->successStatus);
            }
            return response()->json(['errors'=>"Order doesn't exist in our record",'success' => $this->validationStatus], $this->validationStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    // My Total Revenue
    public function weeklyRevenue(){
        $user = $this->user;
        $data = MarketplaceOrder::select(DB::raw("(SUM(total_seles)) as total_seles"),DB::raw("DAYNAME(created_at) as dayname"))
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->whereYear('created_at', date('Y'))
            ->where('seller_id',$user->user_id)
            ->groupBy('dayname')
            ->get();

        return response()->json(['success' => $this->successStatus,
        'revenue' => $data,
        ], $this->successStatus);
    }

    // Create Payment Intent
    public function createPaymentIntent(Request $request){

            $validator = Validator::make($request->all(), [ 
                'amount' => 'required|numeric',
            ]);
        
            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => false], $this->validationStatus);
            }
            $amount = (int)$request->amount;
            $commission = 0;
            $application_fee = $amount*$commission/100;
            $paidAmount = (int)$amount - (int)$application_fee;
            $totalAmount = $paidAmount*100;
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $intent = $stripe->paymentIntents->create([
            'amount' => $totalAmount,
            'currency' => 'USD',
            'automatic_payment_methods' => [
            'enabled' => true,]],
            );
            return response()->json(['success' => true,'secret_key'=>
            $intent,], $this->successStatus);
    }

    // Verify Checkout Order with available quantity 
    public function verifyCheckoutOrder(Request $request){
        $user = $this->user;
        $validator = Validator::make($request->all(), [ 
            'product_id' => 'required|array',
            'product_id.*' => 'integer',
            "product_quantity" => 'required|array',
            'product_quantity.*' => 'integer',
            'offer_id' => 'nullable|array',
        ]);
    
    
        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
        }

        $productIds = $request->product_id;
        $productQuantity = $request->product_quantity;

        $incoTerm = '';
        if(count($request->offer_id) > 0){
            foreach($request->offer_id as $offerId){
                $productOffer = ProductOffer::with('getMapOffer','getIncoterm','getMapOffer.productInfo','getMapOffer.productInfo.product_gallery','getMapOffer.productInfo.getProductTax.getTaxClasses.getTaxDetail')->where('offer_id',$offerId)->first();
                if($productOffer){
                    $productArray = [];
                    foreach($productOffer->getMapOffer as $key=>$mapOffer){
                        $product = $mapOffer->productInfo;
                        $product->product_price = $mapOffer->unit_price;
                        $product->count = $productQuantity[$key];
                        if($productOffer->include_shipping_charge == 'true'){
                            $product->shipping_price = $productOffer->shipping_price;
                        }
                        else{
                            $product->shipping_price = 0;
                        }
                        $tax = [];
                        if(!empty($product->getProductTax)){
                            
                            foreach($product->getProductTax->getTaxClasses as $indx=>$productTax){
                                if(!empty($productTax->getTaxDetail)){
                                    array_push($tax, $productTax->getTaxDetail);
                                }
                            }
                        }
                        $product->tax = $tax;
                        $product->map_offer_id = $request->offer_id;
                        array_push($productArray, $product);
                    }
                    //$productArray['incoterm'] = $productOffer->getIncoterm;
                    $productOfferIncoterm = '';
                    if(!empty($productOffer->getIncoterm)){
                        $productOfferIncoterm = $productOffer->getIncoterm->incoterms.' '.$productOffer->incoterm_text;
                    }
                    return response()->json(['success' => $this->successStatus,
                                    'data' => $productArray,
                                    'incoterm' => $productOfferIncoterm,
                                    ], $this->successStatus);
                }
                $message = 'The offer has been deleted please try another one.';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
            }
        }
        else{
            $products = MarketplaceProduct::with('product_gallery','getProductTax.getTaxClasses.getTaxDetail')->whereIn('marketplace_product_id',$productIds)->withTrashed()->get();
            $marketplace_store_id = '';
            if($products){
                foreach($products as $key=>$myProduct){
                    $marketplace_store_id = $myProduct->marketplace_store_id;
                    $tax = [];
                    if(!empty($myProduct->getProductTax)){
                        foreach($myProduct->getProductTax->getTaxClasses as $indx=>$productTax){
                            if(!empty($productTax->getTaxDetail)){
                                array_push($tax, $productTax->getTaxDetail);
                            }
                        }
                    }
                    $quantityKey = array_search($myProduct->marketplace_product_id, $productIds);
                    if($productQuantity[$quantityKey] == '' || $productQuantity[$quantityKey] == 0){
                        $product_count = 1;
                    }
                    else{
                        $product_count = $productQuantity[$quantityKey];
                    }
                    $products[$key]->count = $product_count;
                    $products[$key]->tax = $tax;
                    $products[$key]->map_offer_id = [];
                    $products[$key]->shipping_price = 0;
                    if($myProduct->min_order_quantity > $productQuantity[$quantityKey]){
                        $products[$key]->available_status = 0; // Product out of stock
                        if($myProduct->min_order_quantity <= $productQuantity[$quantityKey]){
                            $products[$key]->current_available_quantity = $myProduct->quantity_available;
                        }
                    }
                    else{
                        $products[$key]->available_status = 1; // Product available with desired quantity

                    }

                    if(!empty($myProduct->deleted_at) || $myProduct->status == '0'){
                        $products[$key]->available_status = 2; // Product deleted eighter disabled
                    }

                }
            }
            if(!empty($marketplace_store_id)){
                $storeName = MarketplaceStore::where('marketplace_store_id', $marketplace_store_id)->first();
                if($storeName){
                    $incoTerm = Incoterms::where('id',$storeName->incoterm_id)->first();
                    if($incoTerm){
                        $incoTerm->incoterms = $incoTerm->incoterms.' '. $storeName->incoterm_text;
                    }
                    
                }
            }
        }
        
        return response()->json(['success' => $this->successStatus,
                                    'data' => $products,
                                    'incoterm' => $incoTerm,
                                    ], $this->successStatus);
    }

    // Delete My address
    public function deleteMyAddress($type, $id){
        $user = $this->user;
        try{
            if($type == 'shipping'){
                $shipping = MarketplaceOrderShippingAddress::where('id',$id)->where('user_id',$user->user_id)->first();
                if($shipping){
                    $shipping->delete();
                    return response()->json(['success' => $this->successStatus,
                            'message' => 'Address has been deleted successfully.',
                            ], $this->successStatus);
                }
                else{
                    return response()->json(['success' => false,
                            'message' => 'This address id does not exist our record',
                            ], $this->successStatus);
                }
            }
            else{
                $billing = MarketplaceOrderUserAddress::where('id',$id)->where('user_id',$user->user_id)->first();
                if($billing){
                    $billing->delete();
                    return response()->json(['success' => $this->successStatus,
                            'message' => 'Address has been deleted successfully.',
                            ], $this->successStatus);
                }
                else{
                    return response()->json(['success' => false,
                            'message' => 'This address id does not exist our record',
                            ], $this->successStatus);
                }
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    // Upload Order Invoice 
    public function uploadOrderInvoice(Request $request, $order_id){
        try
        {
            $validator = Validator::make($request->all(), [ 
                'invoice' => 'required|file|mimes:pdf'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $order = MarketplaceOrder::where('order_id',$order_id)->first();
            if($order){
                $ext1 = $request->invoice->getClientOriginalName();
                $name = rand(1111,9999999).'.pdf';
                $filePath = 'invoice/'.$name;
                Storage::disk('s3')->put($filePath, file_get_contents($request->invoice),  'public');
                $order->invoice_name = $filePath;
                $order->save();

                return response()->json(['success' => $this->successStatus,
                            'message' => 'Invoice has been uploaded successfully.',
                            ], $this->successStatus);
            }
        }catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    public function paymentRequestForAdmin($order_id){
        try{
            $user = $this->user;
            $order = MarketplaceOrder::with('sellerInfo','buyerInfo','transactionInfo','shippingAddress','billingAddress','productItemInfo.productInfo','productItemInfo.productInfo.productCategory','productItemInfo.productInfo.product_gallery')->where('order_id',$order_id)->first();
            if($order){
                
                
                /* $orderAmount = ($order->net_total)*10/100;
                $adminPayAmount = $order->net_total - $orderAmount;

                $title_en = "Your total order amount is ".$order->currency.$order->net_total.". You will get ".$order->currency.$adminPayAmount." from ".$order->currency.$order->net_total;

                $title_it = "l'importo totale dell'ordine è ".$order->currency.$order->net_total." otterrai ".$order->currency.$adminPayAmount." da ".$order->currency.$order->net_total;

                $selectedLocale = $this->pushNotificationUserSelectedLanguage($order->seller_id);
                if($selectedLocale == 'en'){
                    $title1 = "Your total order amount is ".$order->currency.$order->net_total.". You will get ".$order->currency.$adminPayAmount." from ".$order->currency.$order->net_total;
                }
                else{
                    $title1 = "l'importo totale dell'ordine è ".$order->currency.$order->net_total." otterrai $ ".$order->currency.$adminPayAmount." da ".$order->currency.$order->net_total;
                }
                $saveNotification = new Notification;
                $saveNotification->from = $user->user_id;
                $saveNotification->to = $order->seller_id;
                $saveNotification->notification_type = '12'; 
                $saveNotification->title_it = $title_it;
                $saveNotification->title_en = $title_en;
                $saveNotification->redirect_to = 'order_screen';
                $saveNotification->redirect_to_id = $order->order_id;

                $saveNotification->sender_id = $user->user_id;
                $saveNotification->sender_name = "";
                $saveNotification->sender_image = null;
                $saveNotification->post_id = $order->order_id;
                $saveNotification->connection_id = null;
                $saveNotification->sender_role = $user->role_id;
                $saveNotification->comment_id = null;
                $saveNotification->reply = null;
                $saveNotification->likeUnlike = null;
                $saveNotification->save();

                $tokens = DeviceToken::where('user_id', $order->seller_id)->get();
                $notificationCount = $this->updateUserNotificationCountFirebase($order->seller_id);
                if(count($tokens) > 0)
                {
                    if($user->user_id != $order->seller_id){
                    $collectedTokenArray = $tokens->pluck('device_token');
                    $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id , '', null, $order->order_id, null, '6', null,null,null);

                    $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, 'admin', null, null, $order->order_id, null, '6', null,null,null,null,null, $notificationCount);
                    }
                } */
                event(new PaymentRequestEvent($order));
                $transaction = MarketplaceOrderTransaction::where('order_id',$order_id)->first();
                if($transaction){
                    $transaction->producer_payment_request = 1;
                    $transaction->save();
                }
                return response()->json(['success' => $this->successStatus,
                                'message' => 'Your payment request has been sent to admin successfully. Thanks',
                                ], $this->successStatus);
            }
            else{
                return response()->json(['errors'=>"Order doesn't exist in our record",'success' => $this->validationStatus], $this->validationStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
