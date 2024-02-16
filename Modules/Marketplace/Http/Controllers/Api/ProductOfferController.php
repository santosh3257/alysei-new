<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\Marketplace\Entities\MapProductOffer;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceOrder;
use Modules\Marketplace\Entities\MarketplaceOrderTransaction;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Traits\NotificationTrait;
use App\Notification;
use Kreait\Firebase\Factory;
use Modules\User\Entities\DeviceToken;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem\AwsS3v3\AwsS3Adapter;
use Modules\Marketplace\Entities\Incoterms;
use Modules\Marketplace\Entities\MarketplaceStore;

class ProductOfferController extends Controller
{

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
     * Display a listing of the My Offers.
     * @return Response
     */
    public function getMyOffer()
    {
        try{
        $user = $this->user;
        if($user->role_id == 3){
            $myOffers = ProductOffer::with('getSellerInfo','getBuyerInfo','getIncoterm','getMapOffer','getMapOffer.productInfo','getMapOffer.productInfo.getProductTax.getTaxClasses.getTaxDetail')->where('seller_id',$user->user_id)->orderBy('created_at','desc')->paginate(10);
        }
        else{
            $myOffers = ProductOffer::with('getSellerInfo','getBuyerInfo','getIncoterm','getMapOffer','getMapOffer.productInfo','getMapOffer.productInfo.getProductTax.getTaxClasses.getTaxDetail')->where('buyer_id',$user->user_id)->orderBy('created_at','desc')->paginate(10);
        }

        if($myOffers){
            foreach($myOffers as $inx=>$offer){
                // $store = MarketplaceStore::where('user_id',$offer->seller_id)->first();
                // $myOffers[$inx]->incoterm = '';
                // if($store){
                //     $incoTerm = Incoterms::where('id',$store->incoterm_id)->first();
                //     if($incoTerm){
                //         $myOffers[$inx]->incoterm = $incoTerm->incoterms;
                //     }
                // }
                if(!empty($offer->getMapOffer)){
                    foreach($offer->getMapOffer as $key=>$offerProduct){
                        if(!empty($offerProduct->productInfo)){
                            $galleries = MarketplaceProductGallery::where('marketplace_product_id', $offerProduct->productInfo->marketplace_product_id)->get();
                            $myOffers[$inx]->getMapOffer[$key]->productInfo->galleries = $galleries;
                        }
                    }
                }
            }
        }
        return response()->json(['success' => $this->successStatus,
                                    'data' => $myOffers,
                                    ], $this->successStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /**
     * Store a newly created Offer in storage.
     * @param Request $request
     * @return Response
     */
    public function addProductOffer(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'user_id' => 'required|integer',
                'end_date' => 'required|date|date_format:Y-m-d',
                'product_id' => 'required|array',
                'product_id.*' => 'integer',
                'unit_price' => 'required|array',
                'unit_price.*' => 'numeric',
                'quantity' => 'required|array',
                'quantity.*' => 'integer',
            ]);
        

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            if(!empty($request->shipping_price) && $request->shipping_price !=null){
                $shippingCharge = $request->shipping_price;
            }
            else{
                $shippingCharge = 0.00;
            }
            $productOffer = new ProductOffer();
            $productOffer->offer_title = $request->offer_title;
            $productOffer->seller_id = $user->user_id;
            $productOffer->buyer_id = $request->user_id;
            $productOffer->end_date = $request->end_date;
            $productOffer->payment_term = 'instant';
            $productOffer->imp_notes = $request->imp_notes;
            $productOffer->other_term = $request->other_term;
            $productOffer->shipping_price = $shippingCharge;
            $productOffer->icoterm_id = $request->icoterm_id;
            $productOffer->incoterm_text = $request->incoterms_text;
            $productOffer->include_shipping_charge = $request->include_shipping_charge;
            $productOffer->status = 'pending';
            $productOffer->save();
            $offerProductId = $request->product_id;
            $offerPrice = $request->unit_price;
            $offerQuantity = $request->quantity;
            if($offerProductId){
                foreach($offerProductId as $key=>$productId){
                    $mapProductOffer = new MapProductOffer();
                    $mapProductOffer->offer_id = $productOffer->offer_id;
                    $mapProductOffer->product_id = $productId;
                    $mapProductOffer->unit_price = $offerPrice[$key];
                    $mapProductOffer->quantity =  $offerQuantity[$key];
                    $mapProductOffer->save();
                }

                DB::commit();
                $title_en = " has been created New offer for you";
                $title_it = " è stata creata una nuova offerta per te";
                $selectedLocale = $this->pushNotificationUserSelectedLanguage($request->user_id);
                if($selectedLocale == 'en'){
                    $title1 = $user->company_name." has created new offer for you.";
                }
                else{
                    $title1 = $user->company_name." ha creato una nuova offerta per te.";
                }

                $saveNotification = new Notification;
                $saveNotification->from = $user->user_id;
                $saveNotification->to = $request->user_id;
                $saveNotification->notification_type = '13'; //view offer
                $saveNotification->title_it = $title_it;
                $saveNotification->title_en = $title_en;
                $saveNotification->redirect_to = 'offer_screen';
                $saveNotification->redirect_to_id = $productOffer->offer_id;

                $saveNotification->sender_id = $user->user_id;
                $saveNotification->sender_name = $user->company_name;
                $saveNotification->sender_image = null;
                $saveNotification->post_id = $productOffer->offer_id;
                $saveNotification->connection_id = null;
                $saveNotification->sender_role = $user->role_id;
                $saveNotification->comment_id = null;
                $saveNotification->reply = null;
                $saveNotification->likeUnlike = null;
                $saveNotification->save();

                $tokens = DeviceToken::where('user_id', $request->user_id)->get();
                $notificationCount = $this->updateUserNotificationCountFirebase($request->user_id);
                if(count($tokens) > 0)
                {
                    $collectedTokenArray = $tokens->pluck('device_token');
                    $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $user->company_name, null, $productOffer->offer_id, null, $user->role_id, null,null,null);

                    $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $user->company_name, null, null, $productOffer->offer_id, null, $user->role_id, null,null,null,null,null, $notificationCount);
                }
                return response()->json(['success' => $this->successStatus,
                                    'message' => 'Offer has been created successfully',
                                    ], $this->successStatus);
            }
            $message = 'Something went wrong try later';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /**
     * Show the form for editing the specified product offer.
     * @param int $id
     * @return Response
     */
    public function editMyOffer($offer_id)
    {
        $user = $this->user;
        $productOffer = ProductOffer::with('getBuyerInfo','getMapOffer')->where('offer_id',$offer_id)->where('seller_id',$user->user_id)->first();
        if($productOffer){
            return response()->json(['success' => $this->successStatus,
                                    'data' => $productOffer,
                                    ], $this->successStatus);
        }
        else{
            $message = 'No record fount';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }
    }

    /**
     * Update the specified product offer in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function updateMyOffer(Request $request, $offer_id)
    {
        try{
                $user = $this->user;
                $productOffer = ProductOffer::where('offer_id',$offer_id)->where('seller_id',$user->user_id)->first();
                if($productOffer){
                    $validator = Validator::make($request->all(), [ 
                        'user_id' => 'required|integer',
                        'end_date' => 'required|date|date_format:Y-m-d',
                        'product_id' => 'required|array',
                        'product_id.*' => 'integer',
                        'unit_price' => 'required|array',
                        'unit_price.*' => 'numeric',
                        'quantity' => 'required|array',
                        'quantity.*' => 'integer',
                    ]);
                

                    if ($validator->fails()) { 
                        return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                    }
                    $productOffer->offer_title = $request->offer_title;
                    $productOffer->seller_id = $user->user_id;
                    $productOffer->buyer_id = $request->user_id;
                    $productOffer->end_date = $request->end_date;
                    $productOffer->payment_term = $request->payment_term;
                    $productOffer->other_term = $request->other_term;
                    $productOffer->shipping_price = $request->shipping_price;
                    $productOffer->icoterm_id = $request->icoterm_id;
                    $productOffer->incoterm_text = $request->incoterms_text;
                    $productOffer->include_shipping_charge = $request->include_shipping_charge;
                    $productOffer->imp_notes = $request->imp_notes;
                    $productOffer->save();

                    $delete = MapProductOffer::where('offer_id',$offer_id)->delete();
                    if($delete){
                        $offerProductId = $request->product_id;
                        $offerPrice = $request->unit_price;
                        $offerQuantity = $request->quantity;
                        if($offerProductId){
                            foreach($offerProductId as $key=>$productId){
                                $mapProductOffer = new MapProductOffer();
                                $mapProductOffer->offer_id = $productOffer->offer_id;
                                $mapProductOffer->product_id = $productId;
                                $mapProductOffer->unit_price = $offerPrice[$key];
                                $mapProductOffer->quantity =  $offerQuantity[$key];
                                $mapProductOffer->save();
                            }
                        }
                    }
                    return response()->json(['success' => $this->successStatus,
                                    'message' => 'Offer has been upated successfully',
                                    ], $this->successStatus);
                }
                else{
                    $message = 'No record found';
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
                }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    public function myProductsList(){
        try{
            $user = $this->user;
            $products = MarketplaceProduct::select('marketplace_product_id','title','min_order_quantity')->where('user_id', $user->user_id)->where('status','1')->orderBy('title','asc')->get();
            return response()->json(['success' => $this->successStatus,
                                    'data' => $products,
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    public function importerChangeOfferStatus(Request $request){
        try{
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'offer_id' => 'required|integer',
                'status' => 'required|in:accepted,rejected',
            ]);
        

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $productOffer = ProductOffer::where('offer_id',$request->offer_id)->where('buyer_id',$user->user_id)->first();
            if($productOffer){
                $productOffer->status = $request->status;
                $productOffer->save();
                
                return response()->json(['success' => $this->successStatus,
                                    'message' => 'Status upated successfully',
                                    ], $this->successStatus);
            }
            else{
                $message = 'No record found';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    // View Single Offer
    public function viewSingleOffer($id){
        try{
            $user = $this->user;
            if($user->role_id == 3){
                $offer = ProductOffer::with('getSellerInfo','getBuyerInfo','getIncoterm','getMapOffer','getMapOffer.productInfo','getMapOffer.productInfo.getProductTax.getTaxClasses.getTaxDetail')->where('seller_id',$user->user_id)->where('offer_id',$id)->first();
            }
            else{
                $offer = ProductOffer::with('getSellerInfo','getBuyerInfo','getIncoterm','getMapOffer','getMapOffer.productInfo','getMapOffer.productInfo.getProductTax.getTaxClasses.getTaxDetail')->where('buyer_id',$user->user_id)->where('offer_id',$id)->first();
            }

            if($offer){
                if(!empty($offer->getIncoterm)){
                    $offer->getIncoterm->incoterms = $offer->getIncoterm->incoterms.' '.$offer->incoterm_text; 
                }
                // $offer->incoterm = '';
                // $store = MarketplaceStore::where('user_id',$offer->seller_id)->first();
                // if($store){
                //     $incoTerm = Incoterms::where('id',$store->incoterm_id)->first();
                //     if($incoTerm){
                //         $offer->incoterm = $incoTerm->incoterms;
                //     }
                // }
                if(!empty($offer->getMapOffer)){
                    foreach($offer->getMapOffer as $key=>$offerProduct){
                        if(!empty($offerProduct->productInfo)){
                            $galleries = MarketplaceProductGallery::where('marketplace_product_id', $offerProduct->productInfo->marketplace_product_id)->get();
                            $offer->getMapOffer[$key]->productInfo->galleries = $galleries;
                        }
                    }
                }
            }
                    
                    
                
            return response()->json(['success' => $this->successStatus,
                                        'data' => $offer,
                                        ], $this->successStatus);
    
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
    public function deleteOffer($offer_id)
    {
        $user = $this->user;
        $offer = ProductOffer::where('offer_id',$offer_id)->where('seller_id',$user->user_id)->first();
        if($offer){

            $orderBuyed = MarketplaceOrder::leftJoin('marketplace_order_items','marketplace_order_items.order_id','=','marketplace_orders.order_id')
                                            ->where('marketplace_order_items.offer_map_id',$offer_id)
                                            ->count();
            if($orderBuyed == 0){
                $offer->delete();
                return response()->json(['success' => $this->successStatus,
                                    'message' => 'Offer has been deleted successfully',
                                    ], $this->successStatus);
            }
            $message = "We can't delete it because someone buy this offer.";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }else{
            $message = 'You are not authorised';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }

    }
}
