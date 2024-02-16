<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceRating;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceProductCategory;
use Modules\Marketplace\Entities\MarketplaceProductSubcategory;
use Modules\Marketplace\Entities\MarketplaceRecentSearch;
use Modules\Marketplace\Entities\MarketplaceBrandLabel;
use Modules\Marketplace\Entities\MarketplaceProductEnquery;
use Modules\Marketplace\Entities\MarketplaceEnqueryMessage;
use App\Http\Controllers\CoreController;
use App\Http\Traits\UploadImageTrait;
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Carbon\Carbon;
use App\Notification;
use Modules\User\Entities\DeviceToken; 
use Kreait\Firebase\Factory;
use App\Http\Traits\NotificationTrait;
use Modules\User\Entities\User;
use App\Events\StoreRequest;
use Modules\User\Entities\UserFieldValue;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\Marketplace\Entities\MarketplaceTaxClasses;
use Modules\Marketplace\Entities\Incoterms;

class ProductController extends CoreController
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

    /*
     * Get Product Categories
     * 
     */
    public function getProductCategories($allCategories='')
    {
        try
        {
            $user = $this->user;

            if($allCategories == '')
            {
                $arrayValues = array();
                $fieldValues = DB::table('user_field_values')
                            ->where('user_id', $user->user_id)
                            ->where('user_field_id', 2)
                            ->get();
                if(count($fieldValues) > 0)
                {
                    foreach($fieldValues as $fieldValue)
                    {
                        $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_option_id', $fieldValue->value)
                                ->first();
                        
                        //$arrayValues[] = $options->option;
                        if(!empty($options->option))
                        $arrayValues[] = ['marketplace_product_category_id'=>$options->user_field_option_id, 'name' => $this->translate('messages.'.$options->option,$options->option)];
                    }

                    array_multisort(array_column( $arrayValues, 'name' ), SORT_ASC, $arrayValues);

                    return response()->json(['success' => $this->successStatus,
                                    'count' => count($arrayValues),
                                    'data' => $arrayValues,
                                    ], $this->successStatus);
                }
                else
                {
                    $message = "No product categories found";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_id', 2)
                                ->orderBy('option','ASC')
                                ->get();
                if(count($options) > 0)
                {
                    foreach($options as $key => $option)
                    {
                        $arrayValues[] = ['marketplace_product_category_id'=>$option->user_field_option_id, 'name' => $this->translate('messages.'.$option->option,$option->option)];    
                        
                    }

                    array_multisort(array_column( $arrayValues, 'name' ), SORT_ASC, $arrayValues);
                    return response()->json(['success' => $this->successStatus,
                                    'count' => count($arrayValues),
                                    'data' => $arrayValues,
                                    ], $this->successStatus);
                    
                }   
                else
                {
                    $message = "No product categories found";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
                }             
                
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

     /*
     * Get Product SubCategories
     * 
     */
    public function getProductSubcategories(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'product_category_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $subCategories = MarketplaceProductSubcategory::where('marketplace_product_category_id', $request->product_category_id)->where('status', '1')->get();
            if(count($subCategories) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                    'count' => count($subCategories),
                                    'data' => $subCategories,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No product subcategories found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    
    /*
     * Get Brand Labels
     * 
     */
    
    public function getBrandLabels()
    {
        try
        {
            $user = $this->user;
            $notShowArray = [];
            // Get user selected Alysei Brand Label ID
            $alysei = $this->getUserSelectedLabelAlyseiLabel(6, $user->user_id);
            // Get user selected Label ID
            $label = $this->getUserSelectedLabelAlyseiLabel(5, $user->user_id);
            
            
            if($alysei == '626'){
                array_push($notShowArray, 3);
            }
            
            if($label == '624'){
                array_push($notShowArray, 2);
                array_push($notShowArray, 4);
            }
            
            if($label == '623'){
                array_push($notShowArray, 1);
                array_push($notShowArray, 4);
            }
            
            $labels = MarketplaceBrandLabel::where('status', '1')->whereNotIn('marketplace_brand_label_id',$notShowArray)->get();
            
            
            if(count($labels) > 0)
            {
                foreach($labels as $key=> $label){
                    $labels[$key]->name = $this->translate('messages.' . $label->name, $label->name);
                    
                }

                
                return response()->json(['success' => $this->successStatus,
                                    'count' => count($labels),
                                    'data' => $labels,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No brand labels found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

     /*
     * Get user Selected Label and Alysei Brand Label
     * @Params $request
     */
    public function getUserSelectedLabelAlyseiLabel($fieldOptionId, $userId){
        $alyseiLabel = UserFieldValue::leftJoin('user_field_options','user_field_options.user_field_option_id','=','user_field_values.value')->where('user_field_values.user_id',$userId)->where('user_field_values.user_field_id',$fieldOptionId)->select('user_field_options.user_field_option_id')->first();
        return $alyseiLabel->user_field_option_id;
    }

    /*
     * Save Product Details
     * @Params $request
     */
    public function saveProductDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'marketplace_store_id' => 'required', 
                'title' => 'required|max:255',
                'description' => 'required',
                //'keywords' => 'required|max:255',
                'product_category_id' => 'required',
                //'product_subcategory_id' => 'required',
                //'quantity_available' => 'required|max:255',
                //'brand_label_id' => 'required|max:255',
                //'min_order_quantity' => 'required',
                'handling_instruction' => 'required',
                'dispatch_instruction' => 'required',
                'available_for_sample' => 'required',
                //'product_price' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            $totalProduct = MarketplaceProduct::where('user_id',$user->user_id)->count();

            if($totalProduct == 0){
                MarketplaceStore::where('user_id',$user->user_id)->update(['status' => '0']);
            }

            $store = MarketplaceStore::where('user_id',$user->user_id)->first();

            $product = new MarketplaceProduct;
            $product->user_id = $user->user_id;
            $product->marketplace_store_id = $request->marketplace_store_id;
            $product->title = $request->title;
            $product->slug = SlugService::createSlug(MarketplaceProduct::class, 'slug', $request->title);
            $product->description = $request->description;
            $product->keywords = $request->keywords;
            $product->product_category_id = $request->product_category_id;
            $product->product_subcategory_id = $request->product_subcategory_id;
            $product->quantity_available = $request->quantity_available;
            $product->brand_label_id = $request->brand_label_id;
            $product->min_order_quantity = $request->min_order_quantity;
            $product->handling_instruction = $request->handling_instruction;
            $product->dispatch_instruction = $request->dispatch_instruction;
            $product->available_for_sample = $request->available_for_sample;
            $product->product_price = $request->product_price;
            $product->unit = $request->unit;
            $product->rrp_price = $request->rrp_price;
            $product->status = ($store->status == '1') ? '1' : '0';
            $product->class_tax_id  = $request->class_tax_id;
            $product->save();

            if($totalProduct == 0){
                //Send Email 
                event(new StoreRequest($request->marketplace_store_id));
                $store->first_product_id = $product->marketplace_product_id;
                $store->save();
            }

            if(!empty($request->gallery_images) && count($request->gallery_images) > 0)
            {
                foreach($request->gallery_images as $key=>$images)
                {
                    $attachmentLinkId = $this->postGallery($images, $product->marketplace_product_id, 2, $key);
                }
            }

            return response()->json(['success'=>$this->successStatus,'data' =>$product],$this->successStatus); 

           /* $message = "Your store has already been setup";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);*/
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }


    /*
     * Update Product Details
     * @Params $request
     */
    public function updateProductDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'marketplace_product_id' => 'required', 
                'title' => 'required|max:255',
                'description' => 'required',
                //'keywords' => 'required|max:255',
                'product_category_id' => 'required',
                //'product_subcategory_id' => 'required',
                //'quantity_available' => 'required|max:255',
                //'brand_label_id' => 'required|max:255',
                //'min_order_quantity' => 'required',
                'handling_instruction' => 'required',
                'dispatch_instruction' => 'required',
                'available_for_sample' => 'required',
                //'product_price' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            
            $product = MarketplaceProduct::where('marketplace_product_id', $request->marketplace_product_id)->first();
            if(!empty($product))
            {
                $product->title = $request->title;
                $product->description = $request->description;
                $product->keywords = $request->keywords;
                $product->product_category_id = $request->product_category_id;
                $product->product_subcategory_id = $request->product_subcategory_id;
                $product->quantity_available = $request->quantity_available;
                $product->brand_label_id = $request->brand_label_id;
                $product->class_tax_id = $request->class_tax_id;
                $product->min_order_quantity = $request->min_order_quantity;
                $product->handling_instruction = $request->handling_instruction;
                $product->dispatch_instruction = $request->dispatch_instruction;
                $product->available_for_sample = $request->available_for_sample;
                $product->product_price = $request->product_price;
                $product->rrp_price = $request->rrp_price;
                $product->unit = $request->unit;
                $product->save();

                $existingGalleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->get();
                /*if(count($existingGalleries) > 0)
                {
                    foreach($existingGalleries as $existingGallery)
                    {
                        unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$existingGallery->attachment_url);
                        MarketplaceProductGallery::where('marketplace_product_gallery_id',$existingGallery->marketplace_product_gallery_id)->delete();
                    }
                }*/
                

                if(!empty($request->gallery_images) && count($request->gallery_images) > 0)
                {
                    foreach($request->gallery_images as $key=>$images)
                    {
                        $attachmentLinkId = $this->postGallery($images, $product->marketplace_product_id, 2, $key);
                    }
                }
                $galleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->get();
                (count($galleries) > 0) ? $product->product_gallery = $galleries : $product->product_gallery = [];

                return response()->json(['success'=>$this->successStatus,'data' =>$product],$this->successStatus); 
            }
            else
            {
                $message = "This product does not exist";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    /*
     * Get my product tax classes list
     * 
     */
    public function getAllProductTaxClasses(){
        try
        {
            $user = $this->user;
            $myTaxClasses = MarketplaceTaxClasses::select('tax_class_id','name')->where('user_id',$user->user_id)->get();
            return response()->json(['success' => $this->successStatus,
                                    'data' => $myTaxClasses,
                                    ], $this->successStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }


    /*
     * Get my product list
     * 
     */
    public function getMyProductList(Request $request)
    {
        
        try
        {
            $user = $this->user;
            $productCount = MarketplaceProduct::with('labels')->where('user_id', $user->user_id)->count();
            
            $query = MarketplaceProduct::with('labels','getProductTax')->where('user_id', $user->user_id)->orderBy('created_at','desc');
            if(!empty($request->search_product)){
                $query->where('title', 'LIKE', '%'.$request->search_product.'%')->orWhere('marketplace_product_id',$request->search_product);
            }
            if(!empty($request->category_id)){
                $query->where('product_category_id',$request->category_id);
            }
            if(!empty($request->stock)){
                if($request->stock === 'instock'){
                    $query->where('quantity_available', '!=', '0');
                }
                else{
                    $query->where('quantity_available', '0');
                }
            }
            $myProductLists = $query->paginate(10);
            if(count($myProductLists))
            {
                foreach($myProductLists as $key => $myProductList)
                {
                    $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_option_id', $myProductList->product_category_id)
                                ->first();
                    $myProductLists[$key]->product_category_name = $options->option;
                                              
                    $galleries = MarketplaceProductGallery::where('marketplace_product_id', $myProductList->marketplace_product_id)->get();
                    (count($galleries) > 0) ? $myProductLists[$key]->product_gallery = $galleries : $myProductLists[$key]->product_gallery = [];

                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->count();

                    $myProductLists[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $myProductLists[$key]->total_reviews = $totalReviews;
                }
                return response()->json(['success'=>$this->successStatus, 'count' => $productCount, 'data' =>$myProductLists],$this->successStatus); 
            }
            else
            {
                $message = "No product list found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Get all product list
     * 
     */
    public function getSearchProductListing(Request $request)
    {
        try
        {
            $user = $this->user;
            
            $validator = Validator::make($request->all(), [ 
                'keyword' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            return $this->applyFiltersToProductSearch($request);

            $productLists = MarketplaceProduct::with('labels')->where('title', 'LIKE', '%' . $request->keyword . '%')->where('status', '1')->paginate(10);  

            if(count($productLists) > 0)
            {

                foreach($productLists as $key => $myProductList)
                {
                    $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_option_id', $myProductList->product_category_id)
                                ->first();
                    if(!empty($options))
                    {
                        $productLists[$key]->product_category_name = $options->option;
                    }
                    else
                    {
                        $productLists[$key]->product_category_name = '';
                    }
                
                    $storeName = MarketplaceStore::where('marketplace_store_id', $myProductList->marketplace_store_id)->first();
                                              
                    $galleries = MarketplaceProductGallery::where('marketplace_product_id', $myProductList->marketplace_product_id)->get();
                    (count($galleries) > 0) ? $productLists[$key]->product_gallery = $galleries : $productLists[$key]->product_gallery = [];

                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->count();

                    $productLists[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $productLists[$key]->total_reviews = $totalReviews;

                    $productLists[$key]->store_name = $storeName->name;
                }
                return response()->json(['success' => $this->successStatus,
                                            'count' => count($productLists),
                                            'data' => $productLists,
                                            ], $this->successStatus);
            }
            else
            {
                $message = "No products found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }


    /*
    Apply filters
    */
    public function applyFiltersToProductSearch($request)
    {
        $condition = '';

        if(isset($request->available_for_sample))
        {
            if($request->available_for_sample == 1)
            {
                if($condition != '')
                $condition .=" and marketplace_products.available_for_sample = 'Yes'";
                else
                $condition .="marketplace_products.available_for_sample = 'Yes'";
            }
            elseif($request->available_for_sample == 0)
            {
                if($condition != '')
                $condition .=" and marketplace_products.available_for_sample = 'No'";
                else
                $condition .="marketplace_products.available_for_sample = 'No'";
            }
            
        }
        if(!empty($request->category))
        {
            if($condition != '')
                $condition .=" and marketplace_products.product_category_id in(".$request->category.")";
            else
                $condition .="marketplace_products.product_category_id in(".$request->category.")";
        }
        if(!empty($request->price_from))
        {
            if(!empty($request->price_to))
            {
                if($condition != '')
                $condition .=" and marketplace_products.product_price BETWEEN ".$request->price_from." AND ".$request->price_to;
                else
                $condition .="marketplace_products.product_price BETWEEN ".$request->price_from." AND ".$request->price_to;    
            }
            else
            {
                if($condition != '')
                $condition .=" and marketplace_products.product_price >= ".$request->price_from;
                else
                $condition .="marketplace_products.product_price >= ".$request->price_from;
            }
            
        }
        
        if(!empty($request->sort_by))
        {
            //1=popularity, 2=ratings, 3=price lowtohigh, 4=price hightolow, 5=new first
            if($request->sort_by == 1)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1'";
                else
                $condition .="marketplace_products.status = '1'";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->get();    
            }
            elseif($request->sort_by == 2)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1'";
                else
                $condition .="marketplace_products.status = '1'";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->get();
            }
            elseif($request->sort_by == 3)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1' order by product_price ASC";
                else
                $condition .="marketplace_products.status = '1' order by product_price ASC";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->orderBy('product_price', 'ASC')->get();
            }
            elseif($request->sort_by == 4)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1' order by product_price DESC";
                else
                $condition .="marketplace_products.status = '1' order by product_price DESC";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->orderBy('product_price', 'DESC')->get();
            }
            elseif($request->sort_by == 5)
            {
                if($condition != '')
                $condition .=" and marketplace_products.status = '1' order by marketplace_product_id DESC";
                else
                $condition .="marketplace_products.status = '1' order by marketplace_product_id DESC";
                //$productLists = MarketplaceProduct::with('labels')->where('status', '1')->orderBy('marketplace_product_id', 'DESC')->get();
            }
            else
            {
                $message = "No products found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }


        if($condition == '')
        {
            $productLists = MarketplaceProduct::with('labels')->where('title', 'LIKE', '%' . $request->keyword . '%')->where('status', '1')->paginate(10);    
        }
        else
        {
            //$productLists = MarketplaceProduct::with('labels')->where('title', 'LIKE', '%' . $request->keyword . '%')->whereRaw(''.$condition.'')->get();    
            $productLists = DB::table('marketplace_products')
                     //->with('labels')
                     //->select(DB::raw('count(*) as user_count, status'))
                     ->where('title', 'LIKE', '%' . $request->keyword . '%')
                     ->whereRaw(''.$condition.'')->paginate(10);  
        }

        if(count($productLists) > 0)
        {
            foreach($productLists as $key => $myProductList)
            {
                $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $myProductList->product_category_id)
                            ->first();

                if(!empty($options))
                {
                    $productLists[$key]->product_category_name = $options->option;
                }
                else
                {
                    $productLists[$key]->product_category_name = '';
                }            
                $storeName = MarketplaceStore::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id,first_name,last_name', 'user.avatar_id')->where('marketplace_store_id', $myProductList->marketplace_store_id)->first();
                                          
                $galleries = MarketplaceProductGallery::where('marketplace_product_id', $myProductList->marketplace_product_id)->get();
                (count($galleries) > 0) ? $productLists[$key]->product_gallery = $galleries : $productLists[$key]->product_gallery = [];

                $avgRating = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $myProductList->marketplace_product_id)->count();

                $productLists[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $productLists[$key]->total_reviews = $totalReviews;

                $productLists[$key]->store_name = $storeName->name;
                $productLists[$key]->store = $storeName;
            }
            return response()->json(['success' => $this->successStatus,
                                        'count' => count($productLists),
                                        'data' => $productLists,
                                        ], $this->successStatus);
        }
        else
        {
            $message = "No products found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }


    /*
     * Delete product
     * @Params $request
     */
    public function deleteProduct(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'marketplace_product_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $product = MarketplaceProduct::where('marketplace_product_id', $request->marketplace_product_id)->where('user_id', $user->user_id)->first();
            
            if(!empty($product))
            {
                $product->delete();

                MarketplaceProductEnquery::where('product_id',$request->marketplace_product_id)->delete();
                $message = "Product deleted successfully";
                return response()->json(['success'=>$this->successStatus, 'message' => $message],$this->successStatus); 
            }
            else
            {
                $message = "No product found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Search Product
     * 
    */
    public function searchProduct(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'keyword' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            return $this->getSearchProductList($request, $user);    
                 
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Recent Search Product
     * 
    */
    public function recentSearchProduct()
    {
        try
        {
            $user = $this->user;

            $recentSearch = MarketplaceRecentSearch::where('user_id', $user->user_id)->orderBy('marketplace_recent_search_id', 'DESC')->get();
            
            if(count($recentSearch) > 0)
            {
                
                return response()->json(['success'=>$this->successStatus, 'data' => $recentSearch],$this->successStatus); 
            }
            else
            {
                $message = "No recent search found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

     /*
     * Get product detail By Id
     * 
     */
    public function getProductDetail(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'marketplace_product_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            $productDetail = MarketplaceProduct::with('product_gallery','getProductTax.getTaxClasses.getTaxDetail')->with('labels')->with('user:user_id,company_name,email,role_id,avatar_id','user.avatar_id')->where('marketplace_product_id', $request->marketplace_product_id)->first();
            if(!empty($productDetail))
            {
                $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $productDetail->product_category_id)
                            ->first();
                $productDetail->product_category_name = $options->option;
                $storeName = MarketplaceStore::with('getIncoterm')->where('marketplace_store_id', $productDetail->marketplace_store_id)->first();
                $logoId = Attachment::where('id', $storeName->logo_id)->first();
                $storeName->store_logo = $logoId->attachment_url;
                $storeName->logo_base_url = $logoId->base_url;
                if($productDetail->keywords == null || $productDetail->keywords == ''){
                    $productDetail->keywords = '';
                }

                $avgRatingStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->avg('rating');
                $totalReviewsStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->count();

                $oneStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 1)->count();
                $twoStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 2)->count();
                $threeStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 3)->count();
                $fourStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 4)->count();
                $fiveStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 5)->count();

                $isfavouriteStore = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $storeName->marketplace_store_id)->first();

                $storeName->avg_rating = number_format((float)$avgRatingStore, 1, '.', '');
                $storeName->total_reviews = $totalReviewsStore;

                $storeName->total_one_star = $oneStarStore;
                $storeName->total_two_star = $twoStarStore;
                $storeName->total_three_star = $threeStarStore;
                $storeName->total_four_star = $fourStarStore;
                $storeName->total_five_star = $fiveStarStore;
                $storeName->is_favourite = (!empty($isfavouriteStore)) ? 1 : 0;

                $productDetail->store_logo = $logoId->attachment_url;

                $avgRating = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->count();

                $oneStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 1)->count();
                $twoStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 2)->count();
                $threeStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 3)->count();
                $fourStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 4)->count();
                $fiveStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 5)->count();

                $isfavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '2')->where('id', $productDetail->marketplace_product_id)->first();

                $productDetail->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $productDetail->total_reviews = $totalReviews;

                $productDetail->total_one_star = $oneStar;
                $productDetail->total_two_star = $twoStar;
                $productDetail->total_three_star = $threeStar;
                $productDetail->total_four_star = $fourStar;
                $productDetail->total_five_star = $fiveStar;

                $productDetail->is_favourite = (!empty($isfavourite)) ? 1 : 0;
                $productDetail->store_detail = $storeName;

                $getLatestReview = MarketplaceRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id,first_name,last_name','user.avatar_id')->where('type', '2')->where('id', $request->marketplace_product_id)->orderBy('marketplace_review_rating_id', 'DESC')->first();

                if(!empty($getLatestReview)){
                    if($getLatestReview->user->role_id == 7 || $getLatestReview->user->role_id == 10)
                        {
                            $names = ucwords(strtolower($getLatestReview->user->first_name)) . ' ' . ucwords(strtolower($getLatestReview->user->last_name));
                        }
                        elseif($getLatestReview->user->role_id == 9)
                        {
                            $names = $getLatestReview->user->restaurant_name;
                        }
                        else
                        {
                            $names = $getLatestReview->user->company_name;
                        }

                        $getLatestReview->user->review_name = $names;
                }

                $getLatestReviewCounts = MarketplaceRating::where('type', '2')->where('id', $request->marketplace_product_id)->count();

                $productDetail->latest_review = $getLatestReview;
                if(!empty($getLatestReview))
                $productDetail->latest_review->review_count = $getLatestReviewCounts;
                                          
                /*$galleries = MarketplaceProductGallery::where('marketplace_product_id', $productDetail->marketplace_product_id)->get();
                (count($galleries) > 0) ? $productDetail->product_gallery = $galleries : $productDetail->product_gallery = [];*/

                $marketplaceProductEnquery = MarketplaceProductEnquery::where('user_id', $user->user_id)->where('product_id', $productDetail->marketplace_product_id)->first();
                (!empty($marketplaceProductEnquery) ? $productDetail->enquery_status = 1 : $productDetail->enquery_status = 0);

                $relatedProducts = MarketplaceProduct::with('product_gallery','getProductTax.getTaxClasses.getTaxDetail')->with('labels')->where('product_category_id', $productDetail->product_category_id)->get();

                foreach($relatedProducts as $key => $relatedProduct)
                {
                    $avgRatingRelatedProduct = MarketplaceRating::where('type', '2')->where('id', $relatedProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsRelatedProducts = MarketplaceRating::where('type', '2')->where('id', $relatedProduct->marketplace_product_id)->count();

                    $relatedProducts[$key]->avg_rating = number_format((float)$avgRatingRelatedProduct, 1, '.', '');
                    $relatedProducts[$key]->total_reviews = $totalReviewsRelatedProducts;
                }

                $data = ['product_detail' => $productDetail, 'related_products' => $relatedProducts];

                return response()->json(['success' => $this->successStatus,
                                        'data' => $data,
                                        ], $this->successStatus);
                
            }
            else
            {
                $message = "Invalid product Id";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
                 
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get product detail By Slug
     * 
     */
    public function getProductDetailBySlug(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'slug' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            $productDetail = MarketplaceProduct::with('product_gallery','getProductTax.getTaxClasses.getTaxDetail')->with('labels')->with('user:user_id,company_name,email,role_id,avatar_id','user.avatar_id')->where('slug', $request->slug)->first();
            
            if(!empty($productDetail))
            {
                // $productDetail->get_product_offer = ProductOffer::select('offer_id','seller_id','buyer_id','product_id')->with('getMapOffer')->where(['buyer_id' => $user->user_id,
                //                      'product_id' => $productDetail->marketplace_product_id
                //                     ])->first();
                
                $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $productDetail->product_category_id)
                            ->first();
                
                
                $productDetail->product_category_name = $options->option;
                $storeName = MarketplaceStore::where('marketplace_store_id', $productDetail->marketplace_store_id)->first();
                $logoId = Attachment::where('id', $storeName->logo_id)->first();
                $storeName->store_logo = $logoId->attachment_url;
                $storeName->logo_base_url = $logoId->base_url;

                $avgRatingStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->avg('rating');
                $totalReviewsStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->count();

                $oneStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 1)->count();
                $twoStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 2)->count();
                $threeStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 3)->count();
                $fourStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 4)->count();
                $fiveStarStore = MarketplaceRating::where('type', '1')->where('id', $storeName->marketplace_store_id)->where('rating', 5)->count();

                $isfavouriteStore = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $storeName->marketplace_store_id)->first();

                $storeName->avg_rating = number_format((float)$avgRatingStore, 1, '.', '');
                $storeName->total_reviews = $totalReviewsStore;

                $storeName->total_one_star = $oneStarStore;
                $storeName->total_two_star = $twoStarStore;
                $storeName->total_three_star = $threeStarStore;
                $storeName->total_four_star = $fourStarStore;
                $storeName->total_five_star = $fiveStarStore;
                $storeName->is_favourite = (!empty($isfavouriteStore)) ? 1 : 0;

                $productDetail->store_logo = $logoId->attachment_url; 

                $avgRating = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->count();

                $oneStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 1)->count();
                $twoStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 2)->count();
                $threeStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 3)->count();
                $fourStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 4)->count();
                $fiveStar = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->where('rating', 5)->count();

                $isfavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '2')->where('id', $productDetail->marketplace_product_id)->first();

                $productDetail->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $productDetail->total_reviews = $totalReviews;

                $productDetail->total_one_star = $oneStar;
                $productDetail->total_two_star = $twoStar;
                $productDetail->total_three_star = $threeStar;
                $productDetail->total_four_star = $fourStar;
                $productDetail->total_five_star = $fiveStar;

                $productDetail->is_favourite = (!empty($isfavourite)) ? 1 : 0;
                $productDetail->store_detail = $storeName;

                $getLatestReview = MarketplaceRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id,first_name,last_name','user.avatar_id')->where('type', '2')->where('id', $productDetail->marketplace_product_id)->orderBy('marketplace_review_rating_id', 'DESC')->first();

                if(!empty($getLatestReview)){
                    if($getLatestReview->user->role_id == 7 || $getLatestReview->user->role_id == 10)
                        {
                            $names = ucwords(strtolower($getLatestReview->user->first_name)) . ' ' . ucwords(strtolower($getLatestReview->user->last_name));
                        }
                        elseif($getLatestReview->user->role_id == 9)
                        {
                            $names = $getLatestReview->user->restaurant_name;
                        }
                        else
                        {
                            $names = $getLatestReview->user->company_name;
                        }

                        $getLatestReview->user->review_name = $names;
                }

                $getLatestReviewCounts = MarketplaceRating::where('type', '2')->where('id', $productDetail->marketplace_product_id)->count();
                $productDetail->latest_review = $getLatestReview;
                if(!empty($getLatestReview))
                $productDetail->latest_review->review_count = $getLatestReviewCounts;
                                          
                /*$galleries = MarketplaceProductGallery::where('marketplace_product_id', $productDetail->marketplace_product_id)->get();
                (count($galleries) > 0) ? $productDetail->product_gallery = $galleries : $productDetail->product_gallery = [];*/

                $marketplaceProductEnquery = MarketplaceProductEnquery::where('user_id', $user->user_id)->where('product_id', $productDetail->marketplace_product_id)->first();
                (!empty($marketplaceProductEnquery) ? $productDetail->enquery_status = 1 : $productDetail->enquery_status = 0);

                $relatedProducts = MarketplaceProduct::with('product_gallery','getProductTax.getTaxClasses.getTaxDetail')->with('labels')->whereNotIn('marketplace_product_id',[$productDetail->marketplace_product_id])->where('marketplace_store_id',$productDetail->marketplace_store_id)->where('product_category_id', $productDetail->product_category_id)->where('status','1')->get();

                foreach($relatedProducts as $key => $relatedProduct)
                {
                    $avgRatingRelatedProduct = MarketplaceRating::where('type', '2')->where('id', $relatedProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsRelatedProducts = MarketplaceRating::where('type', '2')->where('id', $relatedProduct->marketplace_product_id)->count();
                    // $relatedProducts[$key]->get_product_offer = ProductOffer::select('offer_id','seller_id','buyer_id','product_id')->with('getMapOffer')->where(['buyer_id' => $user->user_id,
                    //                  'product_id' => $relatedProduct->marketplace_product_id
                    //                 ])->first();
                    $relatedProducts[$key]->avg_rating = number_format((float)$avgRatingRelatedProduct, 1, '.', '');
                    $relatedProducts[$key]->total_reviews = $totalReviewsRelatedProducts;
                }

                
                if($storeName){
                    $incoTerm = Incoterms::where('id',$storeName->incoterm_id)->first();
                    if($incoTerm){
                        $productDetail->incoterm = $incoTerm->incoterms.' '.$storeName->incoterm_text;
                    }
                }

                $data = ['product_detail' => $productDetail, 'related_products' => $relatedProducts];

                return response()->json(['success' => $this->successStatus,
                                        'data' => $data,
                                        ], $this->successStatus);
                
            }
            else
            {
                $message = "Invalid product Id";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
                 
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
    Search product list
    */
    public function getSearchProductList($request, $user)
    {
        
        $productLists = MarketplaceProduct::select('marketplace_product_id','title','product_category_id')->where('title', 'LIKE', '%' . $request->keyword . '%')->where('status', '1')->paginate(10);    

        $recentSearch = new MarketplaceRecentSearch; 
        $recentSearch->user_id = $user->user_id;
        $recentSearch->search_keyword = $request->keyword;
        $recentSearch->save();
        
        if(count($productLists) > 0)
        {
            foreach($productLists as $key => $productList)
            {
                $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $productList->product_category_id)
                            ->first();
                $productLists[$key]->product_category_name = (!empty($options->option)) ? $options->option : "";
            }
            return response()->json(['success' => $this->successStatus,
                                        'count' => count($productLists),
                                        'data' => $productLists,
                                        ], $this->successStatus);
        }
        else
        {
            $message = "No products found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
        
    }

    /*
     * Save Product Enquery
     * @Params $request
     */
    public function saveProductEnquery(Request $request)
    {
        // try
        // {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'product_id' => 'required',
                'name' => 'required',
                'email' => 'required|email',
                'phone' => 'required',
                'message' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $getExistingEnquery = MarketplaceProductEnquery::where('user_id', $user->user_id)->where('product_id', $request->product_id)->first();
            
            if(empty($getExistingEnquery))
            {
                $enquiryProuct = MarketplaceProduct::select('user_id')->where('marketplace_product_id',$request->product_id)->first();
                $product = new MarketplaceProductEnquery;
                $product->user_id = $user->user_id;
                $product->product_id = $request->product_id;
                $product->name = $request->name;
                $product->email = $request->email;
                $product->phone = $request->phone;
                $product->message = $request->message;
                $product->producer_id = $enquiryProuct->user_id;
                $product->save();
                $marketplaceProductEnqueryId = $product->marketplace_product_enquery_id;
                $receiverData = User::select(['user_id', 'name','email','first_name','middle_name','last_name','company_name','restaurant_name','avatar_id'])->where('user_id',$enquiryProuct->user_id)->first();

                $message = new MarketplaceEnqueryMessage;
                $message->product_id = $request->product_id;
                $message->sender_id = $user->user_id;
                $message->receiver_id = $enquiryProuct->user_id;
                $message->message = $request->message;
                $message->save();

                if($user->role_id == 7 || $user->role_id == 10)
                {
                    $name = ucwords(strtolower($user->first_name)) . ' ' . ucwords(strtolower($user->last_name));
                }
                elseif($user->role_id == 9)
                {
                    $name = $user->restaurant_name;
                }
                else
                {
                    $name = $user->company_name;
                }

                $selectedLocale = $this->pushNotificationUserSelectedLanguage($enquiryProuct->user_id);
                if($selectedLocale == 'en'){
                    $title1 = $name." sent inquiry request";
                }
                else{
                    $title1 = $name." ti ha inviato una richiesta";
                }
                $title_en = "sent inquiry request";
                $title_it = "ti ha inviato una richiesta";

                $product = MarketplaceProduct::where('marketplace_product_id', $request->product_id)->first();
                $galleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->first();
                $productImage = "";
                if($galleries){
                    $productImage = $galleries->base_url.$galleries->attachment_url;
                }
                
                $productName = $product->title;

                $saveNotification = new Notification;
                $saveNotification->from = $user->user_id;
                $saveNotification->to = $enquiryProuct->user_id;
                $saveNotification->notification_type = 10; //recieve connection request
                $saveNotification->title_it = $title_it;
                $saveNotification->title_en = $title_en;
                $saveNotification->redirect_to = 'enquiry_screen';
                $saveNotification->redirect_to_id = $request->product_id;

                $saveNotification->sender_id = $user->user_id;
                $saveNotification->sender_name = $name;
                $saveNotification->sender_image = null;
                $saveNotification->post_id = null;
                $saveNotification->connection_id = null;
                $saveNotification->sender_role = $user->role_id;
                $saveNotification->comment_id = null;
                $saveNotification->reply = null;
                $saveNotification->likeUnlike = null;
                $saveNotification->enquiry_product_name = $productName;
                $saveNotification->enquiry_product_image = $productImage;


                $saveNotification->save();

                $tokens = DeviceToken::where('user_id', $enquiryProuct->user_id)->get();
                $notificationCount = $this->updateUserNotificationCountFirebase($enquiryProuct->user_id);
                if(count($tokens) > 0)
                {
                    $collectedTokenArray = $tokens->pluck('device_token');
                    $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $name, null,null, null, '', $user->role_id,null,null,null,$productName,$productImage);

                    $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $name, null,null, null, '', $user->role_id,null,null,null,$productName,$productImage, $notificationCount);
                }

                
                $message = "Your enquery has been saved successfully";
                return response()->json(['success'=>$this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                        'data' =>$product,
                                        'product_id' => $request->product_id,
                                        'product_image' => $productImage,
                                        'product_name' => $productName,
                                        'marketplace_product_enquery_id' => $marketplaceProductEnqueryId,
                                        'receiver_data' => $receiverData
                                        ],$this->successStatus);
            }
            else
            {
                $message = "You already submitted a query on this product";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        // }
        // catch(\Exception $e)
        // {
        //     return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        // }
    }

    /*
     * Update Product Enquery
     * @Params $request
     */
    public function editProductEnquery(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'marketplace_product_enquery_id' => 'required',
                'name' => 'required',
                'email' => 'required|email',
                'phone' => 'required',
                'message' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $getExistingEnquery = MarketplaceProductEnquery::where('user_id', $user->user_id)->where('marketplace_product_enquery_id', $request->marketplace_product_enquery_id)->first();
            if(empty($getExistingEnquery))
            {
                $getExistingEnquery->name = $request->name;
                $getExistingEnquery->email = $request->email;
                $getExistingEnquery->phone = $request->phone;
                $getExistingEnquery->message = $request->message;
                $getExistingEnquery->save();

                $message = "Your enquery has been updated successfully";
                return response()->json(['success'=>$this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                        'data' =>$getExistingEnquery,
                                        ],$this->successStatus);
            }
            else
            {
                $message = "Enquery does not exist";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    /*
    Get product enqueries
    */
    public function getProductEnquery($tab)
    {
        if($tab == 1)
        {
            $enquery = MarketplaceProductEnquery::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('status', '0')->get();        
        }
        elseif($tab == 2)
        {
            $enquery = MarketplaceProductEnquery::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('status', '1')->get();    
        }
        elseif($tab == 3)
        {
            $enquery = MarketplaceProductEnquery::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('status', '2')->get();    
        }
        else
        {
            $message = "Invalid tab";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
        
       
        return response()->json(['success' => $this->successStatus,
                                'count' => count($enquery),
                                'data' => $enquery,
                                ], $this->successStatus);
        
    }

   

    public function getUserProductEnquiry($tab='open')
    {

        try{
                $unReadCount = 0;
                $totalOpenCount  = 0;
                $totalCloseCount = 0;
                $totalNewCount = 0;

                if($this->user->role_id == 3){

                    $enqueries = MarketplaceProductEnquery::with('sender','product')
                                                    ->where('producer_id',$this->user->user_id)
                                                    ->where('receiver_status',$tab)
                                                    ->orderBy('created_at','DESC')
                                                    ->paginate(10);
                    foreach($enqueries as $key => $enquery){
                         if($enquery->product){
                            $galleries = MarketplaceProductGallery::where('marketplace_product_id', $enquery->product->marketplace_product_id)->first();
                            $enqueries[$key]->product->galleries = $galleries;

                            $unReadCount = MarketplaceEnqueryMessage::where('product_id',$enquery->product->marketplace_product_id)->where('receiver_id', $this->user->user_id)->where('sender_id',$enquery->user_id)->where('read_by_receiver',0)->count();
                            $enqueries[$key]->unread_count = $unReadCount;
                        }else{
                            unset($enqueries->$key);
                        }
                    }

                    $openEnqueries = MarketplaceProductEnquery::with('sender','product')
                                                    ->where('producer_id',$this->user->user_id)
                                                    ->where('receiver_status','open')
                                                    ->orderBy('created_at','DESC')
                                                    ->get();

                    $closeEnqueries = MarketplaceProductEnquery::with('sender','product')
                                                    ->where('producer_id',$this->user->user_id)
                                                    ->where('receiver_status','close')
                                                    ->orderBy('created_at','DESC')
                                                    ->get();

                    $newEnqueries = MarketplaceProductEnquery::with('sender','product')
                                                    ->where('producer_id',$this->user->user_id)
                                                    ->where('receiver_status','new')
                                                    ->orderBy('created_at','DESC')
                                                    ->get();
                    foreach($openEnqueries as $key => $enquery){
                         if($enquery->product){
                            $openUnreadCount = MarketplaceEnqueryMessage::where('product_id',$enquery->product->marketplace_product_id)->where('receiver_id', $this->user->user_id)->where('sender_id',$enquery->user_id)->where('read_by_receiver',0)->count();
                            $totalOpenCount += $openUnreadCount;
                        }
                    }

                    foreach($closeEnqueries as $key => $enquery){
                         if($enquery->product){
                            $closeUnreadCount = MarketplaceEnqueryMessage::where('product_id',$enquery->product->marketplace_product_id)->where('receiver_id', $this->user->user_id)->where('sender_id',$enquery->user_id)->where('read_by_receiver',0)->count();
                            $totalCloseCount += $closeUnreadCount;
                        }
                    }

                    foreach($newEnqueries as $key => $enquery){
                         if($enquery->product){
                            $newUnreadCount = MarketplaceEnqueryMessage::where('product_id',$enquery->product->marketplace_product_id)->where('receiver_id', $this->user->user_id)->where('sender_id',$enquery->user_id)->where('read_by_receiver',0)->count();
                            $totalNewCount += $newUnreadCount;
                        }
                    }


                }else{

                    $enqueries = MarketplaceProductEnquery::with('receiver','product')
                                                    ->where('user_id',$this->user->user_id)
                                                    ->where('sender_status',$tab)
                                                    ->orderBy('created_at','DESC')
                                                    ->paginate(10);
                    foreach($enqueries as $key => $enquery){
                        if($enquery->product){
                            $galleries = MarketplaceProductGallery::where('marketplace_product_id', $enquery->product->marketplace_product_id)->first();
                            $enqueries[$key]->product->galleries = $galleries;

                            $unReadCount = MarketplaceEnqueryMessage::where('product_id',$enquery->product->marketplace_product_id)->where('receiver_id', $this->user->user_id)->where('sender_id',$enquery->producer_id)->where('read_by_receiver',0)->count();
                            $enqueries[$key]->unread_count = $unReadCount;
                        }else{
                            unset($enqueries->$key);
                        }
                    }

                    $openEnqueries = MarketplaceProductEnquery::with('sender','product')
                                                    ->where('producer_id',$this->user->user_id)
                                                    ->where('receiver_status','open')
                                                    ->orderBy('created_at','DESC')
                                                    ->get();

                    $closeEnqueries = MarketplaceProductEnquery::with('sender','product')
                                                    ->where('producer_id',$this->user->user_id)
                                                    ->where('receiver_status','close')
                                                    ->orderBy('created_at','DESC')
                                                    ->get();
                    foreach($openEnqueries as $key => $enquery){
                         if($enquery->product){
                            $openUnreadCount = MarketplaceEnqueryMessage::where('product_id',$enquery->product->marketplace_product_id)->where('receiver_id', $this->user->user_id)->where('sender_id',$enquery->user_id)->where('read_by_receiver',0)->count();
                            $totalOpenCount += $openUnreadCount;
                        }
                    }

                    foreach($closeEnqueries as $key => $enquery){
                         if($enquery->product){
                            $closeUnreadCount = MarketplaceEnqueryMessage::where('product_id',$enquery->product->marketplace_product_id)->where('receiver_id', $this->user->user_id)->where('sender_id',$enquery->user_id)->where('read_by_receiver',0)->count();
                            $totalCloseCount += $closeUnreadCount;
                        }
                    }
                    
                }

                return response()->json(['success' => $this->successStatus,
                                    'count' => count($enqueries),
                                    'total_open_count' => $totalOpenCount,
                                    'total_close_count' => $totalCloseCount,
                                    'total_new_count' => $totalNewCount,
                                    'data' => $enqueries,
                                    'current_user_id' => $this->user->user_id
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
            
    }

    /*
     * Get Enquery Message
    */
    public function getEnquiryMessages(Request $request){
        try{

            //$request = $request->all;
            $validator = Validator::make($request->all(), [ 
                'product_id' => 'required',
                'sender_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $enquery = MarketplaceEnqueryMessage::with('image_id')->with('receiver')->with('sender')->where('product_id',$request->product_id)->where(function($query)  use ($request) {
                        $query->where(function($q) use ($request) {
                            $q->where('sender_id', $request->sender_id)
                            ->where('receiver_id', $this->user->user_id);
                        })->orWhere(function($q) use ($request) {
                            $q->where('receiver_id', $request->sender_id)
                            ->where('sender_id', $this->user->user_id);
                        });
                    })->orderBy('created_at','asc')->get();

            $product = MarketplaceProduct::where('marketplace_product_id',$request->product_id)->first();
            $galleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->first();
            $product->galleries = $galleries;

            // if(count($enquery) > 0){
            //     foreach($enquery as $key=>$inquiry){
            //         $galleries = MarketplaceProductGallery::where('marketplace_product_id', request->product_id)->get();
            //         (count($galleries) > 0) ? $enquery[$key]->product_gallery = $galleries : $enquery[$key]->product_gallery = [];
            //     }
            // }

            return response()->json(['success' => $this->successStatus,
                                //'count' => count($enquery),
                                'data' => $enquery,
                                'current_user_id' => $this->user->user_id,
                                'product_details' => $product
                                ], $this->successStatus);
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }   
    }
    
    /*
     * Send Enquery Message
     */

    public function sendEnquiryMessage(Request $request){
        try{

            $validator = Validator::make($request->all(), [ 
                'product_id' => 'required',
                'receiver_id'=>'required',
                'message' => 'required_without:image',
                'image' => 'required_without:message',
            ]);


            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }


            $message = new MarketplaceEnqueryMessage;
            
            if($request->file('image')){
                $message->image_id = $this->uploadImage($request->file('image'));    
            }

            $message->product_id = $request->product_id;
            $message->sender_id = $this->user->user_id;
            $message->receiver_id = $request->receiver_id;
            $message->message = $request->message;
            $message->save();


            $enquery = MarketplaceEnqueryMessage::with('image_id')->with('receiver')->with('sender')->where('product_id',$request->product_id)->where(function($query)  use ($request) {
                        $query->where(function($q) use ($request) {
                            $q->where('sender_id', $request->receiver_id)
                            ->where('receiver_id', $this->user->user_id);
                        })->orWhere(function($q) use ($request) {
                            $q->where('receiver_id', $request->receiver_id)
                            ->where('sender_id', $this->user->user_id);
                        });
                    })->orderBy('created_at','asc')->get();

            MarketplaceEnqueryMessage::where('product_id',$request->product_id)->where('receiver_id', $this->user->user_id)->where('sender_id',$request->receiver_id)->update(['read_by_receiver' => 1]);

            $enquiryProuct = MarketplaceProduct::select('user_id')->where('marketplace_product_id',$request->product_id)->first();

            if($enquiryProuct->user_id == $this->user->user_id){

                $producerId = $this->user->user_id;
                $importerId = $request->receiver_id;
            }else{

                $importerId = $this->user->user_id;
                $producerId = $request->receiver_id;
            }

            if(count($enquery) == 2){

                MarketplaceProductEnquery::where(['product_id' => $request->product_id,
                                                  'producer_id' => $producerId,
                                                  'user_id' => $importerId
                                                 ])
                                                ->update(['receiver_status'=>'open']);
            }

            if($request->product_id && $producerId && $importerId){
                MarketplaceProductEnquery::where(['product_id' => $request->product_id,
                                                  'producer_id' => $producerId,
                                                  'user_id' => $importerId
                                                 ])
                                                ->update(['receiver_status'=>'open',
                                                          'sender_status'=>'open',
                                                          'status'=>1,
                                                          'message'=>($request->message) ? $request->message : ''
                                                        ]);

            }
        
            $this->sendMessageNotification($request->receiver_id,$request->product_id);

            return response()->json(['success' => $this->successStatus,
                                'count' => count($enquery),
                                'data' => $enquery,
                                ], $this->successStatus);

        }catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }   
    }

    /*
     * Update Enquiry Status
     */
    public function updateEnquiryStatus(Request $request){

        try {

            if($this->user->role_id == 3){

                $validator = Validator::make($request->all(), [ 
                    'status' => 'in:close',
                    'marketplace_product_enquery_id'=>'required'
                ]);


                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }


                $response = MarketplaceProductEnquery::where(['marketplace_product_enquery_id' => $request->marketplace_product_enquery_id])->update(['receiver_status'=>'close',
                                                              'sender_status' => 'close',
                                                              'status' => 'close'
                                                          ]);

                if($response){

                    $this->closeEnquiryNotification($request->marketplace_product_enquery_id);
                    $message = "Status has been changed successfully";
                    return response()->json(['success' => $this->successStatus,
                                                    'message' => $this->translate('messages.'.$message,$message),
                                                 ], $this->successStatus);
                }else{

                    $message = "Something went wrong";
                    return response()->json(['success' => $this->exceptionStatus,
                                                    'message' => $this->translate('messages.'.$message,$message),
                                                 ], $this->exceptionStatus);
                }
                
            }else{

                $message = "Sorry,you do not authorized person";

                return response()->json(['success' => $this->successStatus,
                                                    'message' => $this->translate('messages.'.$message,$message),
                                                 ], $this->successStatus);
            }

        }catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    public function sendMessageNotification($receiverId,$productId){

        $product = MarketplaceProduct::where('marketplace_product_id', $productId)->first();
        $galleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->first();
        $productImage = "";
        if($galleries){
            $productImage = $galleries->base_url.$galleries->attachment_url;
        }
        
        $productName = $product->title;
        

        if($this->user->role_id == 7 || $this->user->role_id == 10)
        {
            $name = ucwords(strtolower($this->user->first_name)) . ' ' . ucwords(strtolower($this->user->last_name));
        }
        elseif($this->user->role_id == 9)
        {
            $name = $this->user->restaurant_name;
        }
        else
        {
            $name = $this->user->company_name;
        }
       
        $selectedLocale = $this->pushNotificationUserSelectedLanguage($receiverId);
        if($selectedLocale == 'en'){
            $title1 = $name." sent new inquiry message";
        }
        else{
            $title1 = $name." ti ha inviato una richiesta";
        }
        $title_en = "sent new enquiry message";
        $title_it = "ti ha inviato una richiesta";

        $saveNotification = new Notification;
        $saveNotification->from = $this->user->user_id;
        $saveNotification->to = $receiverId;
        $saveNotification->notification_type = 10; //recieve connection request
        $saveNotification->title_en = $title_en;
        $saveNotification->title_it = $title_it;
        $saveNotification->redirect_to = 'enquiry_screen';
        $saveNotification->redirect_to_id = $productId;
        $saveNotification->enquiry_type = "open";
        $saveNotification->sender_id = $this->user->user_id;
        $saveNotification->sender_name = $name;
        $saveNotification->sender_image = null;
        $saveNotification->post_id = null;
        $saveNotification->connection_id = null;
        $saveNotification->sender_role = $this->user->role_id;
        $saveNotification->comment_id = null;
        $saveNotification->reply = null;
        $saveNotification->likeUnlike = null;
        $saveNotification->enquiry_product_name = $productName;
        $saveNotification->enquiry_product_image = $productImage;

        $saveNotification->save();

        $tokens = DeviceToken::where('user_id', $receiverId)->get();
        $notificationCount =  $this->updateUserNotificationCountFirebase($receiverId);
        if(count($tokens) > 0)
        {
            $collectedTokenArray = $tokens->pluck('device_token');
            $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $this->user->user_id, $name, null, null, '', $this->user->role_id,null,null,null,$productName,$productImage);

            $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $this->user->user_id, $name, null, null,null, '', $this->user->role_id,null,null,null,$productName,$productImage,$notificationCount);
        }

       
    }

    public function closeEnquiryNotification($enquiryId){
        if($this->user->role_id == 7 || $this->user->role_id == 10)
        {
            $name = ucwords(strtolower($this->user->first_name)) . ' ' . ucwords(strtolower($this->user->last_name));
        }
        elseif($this->user->role_id == 9)
        {
            $name = $this->user->restaurant_name;
        }
        else
        {
            $name = $this->user->company_name;
        }

        $enquiry = MarketplaceProductEnquery::with('product')->where('marketplace_product_enquery_id', $enquiryId)->first();
        $galleries = MarketplaceProductGallery::where('marketplace_product_id', $enquiry->product->marketplace_product_id)->first();
        $productImage = "";
        if($galleries){
            $productImage = $galleries->base_url.$galleries->attachment_url;
        }
        
        $productName = $enquiry->product->title;
        $receiverId = $enquiry->user_id;
        $productId = $enquiry->product_id;
        
        $selectedLocale = $this->pushNotificationUserSelectedLanguage($receiverId);
        if($selectedLocale == 'en'){
            $title1 = $name." closed inquiry on ".$enquiry->product->title;
        }
        else{
            $title1 = $name." hiudi richiesta su ".$enquiry->product->title;
        }

        $title_en = "closed inquiry on ".$enquiry->product->title;
        $title_it = "hiudi richiesta su ".$enquiry->product->title;

        $saveNotification = new Notification;
        $saveNotification->from = $this->user->user_id;
        $saveNotification->to = $receiverId;
        $saveNotification->notification_type = 10; //recieve connection request
        $saveNotification->title_en = $title_en;
        $saveNotification->title_it = $title_it;
        $saveNotification->redirect_to = 'enquiry_screen';
        $saveNotification->redirect_to_id = $productId;
        $saveNotification->enquiry_type = "close";
        $saveNotification->sender_id = $this->user->user_id;
        $saveNotification->sender_name = $name;
        $saveNotification->sender_image = null;
        $saveNotification->post_id = null;
        $saveNotification->connection_id = null;
        $saveNotification->sender_role = $this->user->role_id;
        $saveNotification->comment_id = null;
        $saveNotification->reply = null;
        $saveNotification->likeUnlike = null;
        $saveNotification->enquiry_product_name = $productName;
        $saveNotification->enquiry_product_image = $productImage;

        $saveNotification->save();

        $tokens = DeviceToken::where('user_id', $receiverId)->get();
        if(count($tokens) > 0)
        {
            $collectedTokenArray = $tokens->pluck('device_token');
            $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $this->user->user_id, $name, null,null, null, '', $this->user->role_id,null,null,null,$productName,$productImage);

            $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $this->user->user_id, $name, null,null, null, '', $this->user->role_id,null,null,null,null,$productName,$productImage);
        }
    }

    // Change Product Status
    public function changeProductStatus(Request $request, $product_id){
        $product = MarketplaceProduct::where('marketplace_product_id',$product_id)->where('user_id',$this->user->user_id)->first();
        if($product){
            $product->status = $request->status;
            $product->save();
            $productStatus = 'inactive';
            if($request->status == 1){
                $productStatus = 'active';
            }
            $message = "The status has been changed to ".$productStatus;
                    return response()->json(['success' => $this->successStatus,
                                                    'message' => $this->translate('messages.'.$message,$message),
                                                 ], $this->successStatus);
        }
        else{
            $message = "Sorry,you are not authorized person";

            return response()->json(['success' => false,
                                                'message' => $this->translate('messages.'.$message,$message),
                                             ], $this->successStatus);
        }
    }
}
