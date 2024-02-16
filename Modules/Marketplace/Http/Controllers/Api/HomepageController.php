<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\WalkthroughScreen;
use Modules\User\Entities\State;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceRating;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceBanner;
use Modules\Marketplace\Entities\MarketplaceMostViewedStores;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\User\Entities\User;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 
use App\Http\Traits\SortArray;
use Validator;
use DB;
use Modules\Marketplace\Entities\PaymentSetting;
use Modules\Marketplace\Entities\Incoterms;

class HomepageController extends CoreController
{
	public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;
    use SortArray;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }


    /*
     * Get Box details
     * 
     */
    public function getBoxDetails($boxId='')
    {
        try
        {
            $user = $this->user;

            if($boxId == 1)
            {
            	return $this->getAllStores();
            }
            elseif($boxId == 2)
            {
            	return $this->getConservationMethod();
            }
            elseif($boxId == 3)
            {
            	return $this->getAllRegions();
            }
            elseif($boxId == 4)
            {
            	return $this->getProductCategories();
            }
            elseif($boxId == 5)
            {
            	return $this->getProductProperties();
            }
            elseif($boxId == 6)
            {
            	return $this->getFDACertifiedProducts();
            }
            elseif($boxId == 7)
            {
            	return $this->getMyFavouriteProducts();
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

     /*
     * get filter data
     * 
     */
    public function filter(Request $request)
    {
        // try
        // {
            $condition = '';
            $storCondition = '';
            $productsArray = [];
            $usersArray = [];
            $storesArray = [];
            $storesUserArray = [];
            $methodUsersArray = [];
            $propertiesUsersArray = [];
            $fdaUsersArray = [];
            $regionUsersArray = [];
            $methodProductUsersArray = [];
            $propertiesProductUsersArray = [];
            $regionProductUsersArray = [];


            $validator = Validator::make($request->all(), [ 
                'type' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            if(!empty($request->category))
            {
                if($request->type == 1)
                {
                    $categoryIds = explode(",", $request->category);
                    $productList = MarketplaceProduct::whereIn('product_category_id', $categoryIds)->where('status','1')->get();
                    if(count($productList))
                    {
                        $productIds = $productList->pluck('marketplace_store_id')->toArray();
                        foreach($productIds as $productId)
                        {
                            array_push($storesArray, $productId);
                        }
                    }
                }
                elseif($request->type == 2)
                {
                    $categoryIds = explode(",", $request->category);
                    $productList = MarketplaceProduct::whereIn('product_category_id', $categoryIds)->where('status','1')->get();
                    if(count($productList))
                    {
                        $productIds = $productList->pluck('marketplace_product_id')->toArray();
                        foreach($productIds as $productId)
                        {
                            array_push($productsArray, $productId);
                        }
                    }    
                }
                
            }

            if(!empty($request->property))
            {
                // $categoryIds = ($request->category && $request->category !="") ? explode(",", $request->category) : [];
                // $properties = explode(",", $request->property);
                // if(!empty($categoryIds)){
                //     $propertiesParentId = DB::table('user_field_options')->select('user_field_option_id')->where('option','Properties')->whereIn('parent',$categoryIds)->get();
                //     if(!empty($propertiesParentId)){
                //         $propertiesChildId = $propertiesParentId->pluck('user_field_option_id');
                //         $options = DB::table('user_field_options')->select('user_field_option_id')
                //                     ->whereIn('option', $properties)
                //                     ->where('user_field_id', 2)
                //                     ->whereIn('parent',$propertiesChildId)
                //                     ->get();
                        
                //     }
                // }else{
                //     $options = DB::table('user_field_options')->select('user_field_option_id')
                //                     ->whereIn('option', $properties)
                //                     ->where('user_field_id', 2)
                //                     ->get();
                    
                // }

                // if(count($options) > 0)
                // {
                //     $getOptionIds = $options->pluck('user_field_option_id');
                //     $values = DB::table('user_field_values')
                //                         ->select('user_id')
                //                         ->whereIn('value', $getOptionIds)
                //                         ->where('user_field_id', 2)
                //                         ->get();
                //     $userIds = $values->pluck('user_id')->toArray();
                //     foreach($userIds as $userId)
                //     {
                //         array_push($propertiesProductUsersArray, $userId);
                //         array_push($propertiesUsersArray, $userId);
                //     }
                // }

                $getOptionIds = ($request->property && $request->property !="") ? explode(",", $request->property) : [];

                $values = DB::table('user_field_values')
                                        ->select('user_id')
                                        ->whereIn('value', $getOptionIds)
                                        ->where('user_field_id', 2)
                                        ->get();
                $userIds = $values->pluck('user_id')->toArray();
                foreach($userIds as $userId)
                {
                    array_push($propertiesProductUsersArray, $userId);
                    array_push($propertiesUsersArray, $userId);
                }

                if(empty($propertiesProductUsersArray)){
                    array_push($propertiesProductUsersArray, 0);
                    array_push($propertiesUsersArray, 0);   
                }
            }
            if(!empty($request->method))
            {
                $getOptionIds = ($request->method && $request->method !="") ? explode(",", $request->method) : [];

                $values = DB::table('user_field_values')
                                        ->select('user_id')
                                        ->whereIn('value', $getOptionIds)
                                        ->where('user_field_id', 2)
                                        ->get();
                $userIds = $values->pluck('user_id')->toArray();
                foreach($userIds as $userId)
                {
                    array_push($methodProductUsersArray, $userId);
                    array_push($methodUsersArray, $userId);
                }

                if(empty($methodProductUsersArray)){
                    array_push($methodProductUsersArray, 0);
                    array_push($methodUsersArray, 0);   
                }
            }
            if(!empty($request->region))
            {
                $regionIds = explode(",", $request->region);
                $userList = User::select('user_id')->whereIn('state', $regionIds)->get();
                if(count($userList))
                {
                    $userIds = $userList->pluck('user_id')->toArray();
                    foreach($userIds as $userId)
                    {
                        array_push($regionProductUsersArray, $userId);
                        array_push($regionUsersArray, $userId);
                    }
                }
            }
            if($request->fda_certified!='' && $request->fda_certified == 1)
            {
                $userField = DB::table("user_fields")->select('user_field_id')->where("name","fda_certified")->first();
                if(!empty($userField)){
                    $fdaCertified = DB::table("user_field_values")
                        ->select("user_field_values.user_id")
                        ->join("user_field_options","user_field_options.user_field_option_id","=","user_field_values.value")
                        ->where("user_field_values.user_field_id",$userField->user_field_id)
                        ->where("user_field_options.option","yes")
                        ->get();  

                    if($fdaCertified){
                        $fdaCertUsers = $fdaCertified->pluck('user_id')->toArray();
                        foreach($fdaCertUsers as $fdaCertUser)
                        {
                            //array_push($usersArray, $fdaCertUser);
                            array_push($fdaUsersArray, $fdaCertUser);
                        }
                    }
                }
                

                // $fdaUsers = User::select('user_id')->whereNotNull('fda_no')->get();
                // if(count($fdaUsers) > 0)
                // {
                //     $fdaCertUsers = $fdaUsers->pluck('user_id')->toArray();
                //     foreach($fdaCertUsers as $fdaCertUser)
                //     {
                //         array_push($usersArray, $fdaCertUser);
                //         array_push($fdaUsersArray, $fdaCertUser);
                //     }
                // }
            }elseif($request->fda_certified !='' && $request->fda_certified == 0){
                $userField = DB::table("user_fields")->select('user_field_id')->where("name","fda_certified")->first();
                
                if(!empty($userField)){
                    $fdaCertified = DB::table("user_field_values")
                        ->select("user_field_values.user_id")
                        ->join("user_field_options","user_field_options.user_field_option_id","=","user_field_values.value")
                        ->where("user_field_values.user_field_id",$userField->user_field_id)
                        ->where("user_field_options.option","no")
                        ->get();  

                    if($fdaCertified){
                        $fdaCertUsers = $fdaCertified->pluck('user_id')->toArray();
                        foreach($fdaCertUsers as $fdaCertUser)
                        {
                            //array_push($usersArray, $fdaCertUser);
                            array_push($fdaUsersArray, $fdaCertUser);
                        }
                    }
                }
            }

            if(!empty($request->sort_by_producer))
            {
                if($request->sort_by_producer == 1) //accending
                {
                    $producers = User::orderBy('company_name')->get();
                    if(count($producers) > 0)
                    {
                        $producersIds = $producers->pluck('user_id')->toArray();
                        foreach($producersIds as $producersId)
                        {
                            array_push($usersArray, $producersId);

                        }
                    }
                }
                else //decending
                {
                    $producers = User::orderBy('company_name', 'DESC')->get();
                    if(count($producers) > 0)
                    {
                        $producersIds = $producers->pluck('user_id')->toArray();
                        foreach($producersIds as $producersId)
                        {
                            array_push($usersArray, $producersId);
                        }
                    }
                }
                
            }
            /*if(!empty($request->sort_by_product))
            {
                if($request->sort_by_product == 1) //accending
                {                
                    if($condition != '')
                    $condition .=" and marketplace_products.user_id in(".$joinProducersId.")";
                    else
                    $condition .="marketplace_products.user_id in(".$joinProducersId.")";
                }
                else //decending
                {
                    $producers = User::orderBy('company_name', 'DESC')->get();
                    if(count($producers) > 0)
                    {
                        $producersId = $producers->pluck('user_id')->toArray();
                        $joinProducersId = join(",", $producersId);
                        if($condition != '')
                        $condition .=" and marketplace_products.user_id in(".$joinProducersId.")";
                        else
                        $condition .="marketplace_products.user_id in(".$joinProducersId.")";
                    }
                }
                
            }*/
            if(!empty($request->rating))
            {
                if($request->rating == 1) //most rated
                {
                    if($request->type == 1)
                    {
                        $avgRating = MarketplaceRating::where('type', '1')->groupBy('id')->orderBy(DB::raw("count(*)"), "DESC")->get();
                        if(count($avgRating) > 0)
                        {
                            $productId = $avgRating->pluck('id')->toArray();
                            foreach($productId as $productIdss)
                            {
                                array_push($storesArray, $productIdss);
                            }
                        }
                    }
                    elseif($request->type == 2)
                    {
                        $avgRating = MarketplaceRating::where('type', '2')->groupBy('id')->orderBy(DB::raw("count(*)"), "DESC")->get();
                        if(count($avgRating) > 0)
                        {
                            $productId = $avgRating->pluck('id')->toArray();
                            foreach($productId as $productIdss)
                            {
                                array_push($productsArray, $productIdss);
                            }
                        }
                    }
                    
                }
                if($request->rating == 2) //5 star
                {
                    if($request->type == 1)
                    {
                        $fiveRating = MarketplaceRating::where('type', '1')->where('rating', 5)->get();
                        if(count($fiveRating) > 0)
                        {
                            $productIds = $fiveRating->pluck('id')->toArray();
                            foreach($productIds as $prodId)
                            {
                                array_push($productsArray, $prodId);
                            }
                        }
                    }
                    elseif($request->type == 2)
                    {
                        $fiveRating = MarketplaceRating::where('type', '2')->where('rating', 5)->get();
                        if(count($fiveRating) > 0)
                        {
                            $productIds = $fiveRating->pluck('id')->toArray();
                            foreach($productIds as $prodId)
                            {
                                array_push($productsArray, $prodId);
                            }
                        }
                    }
                    
                }

                if($request->rating == 3){

                    $topViewedStores = MarketplaceMostViewedStores::groupBy('store_id')->orderByRaw('SUM(viewed_count) DESC')->get();
                    if(count($topViewedStores) > 0){
                        $productId = $topViewedStores->pluck('store_id')->toArray();
                        foreach($productId as $productIdss)
                        {
                            array_push($storesArray, $productIdss);
                        }

                        //return $storesArray;
                    }
                }

                
            }


            
            if($request->type == 1){
                $newArr = [$fdaUsersArray,$regionUsersArray,$methodUsersArray,$propertiesUsersArray];
                $checkIndexEmpty = [];
                foreach($newArr as $newKey => $newValue){
                    if(!empty($newValue)){
                        $checkIndexEmpty[] = $newKey;
                    }
                }

                if(count($checkIndexEmpty) == 1){
                    $storesUserArray = $newArr[$checkIndexEmpty[0]];
                }

                if(count($checkIndexEmpty) == 2){
                    $storesUserArray = array_intersect($newArr[$checkIndexEmpty[0]],$newArr[$checkIndexEmpty[1]]);
                }

                if(count($checkIndexEmpty) == 3){
                    $storesUserArray = array_intersect($newArr[$checkIndexEmpty[0]],$newArr[$checkIndexEmpty[1]],$newArr[$checkIndexEmpty[2]]);
                }

                if(count($checkIndexEmpty) == 4){
                    $storesUserArray = array_intersect($newArr[$checkIndexEmpty[0]],$newArr[$checkIndexEmpty[1]],$newArr[$checkIndexEmpty[2]],$newArr[$checkIndexEmpty[3]]);
                }
            }else{
                $newArr = [$regionProductUsersArray,$methodProductUsersArray,$propertiesProductUsersArray];
                
                $checkIndexEmpty = [];
                foreach($newArr as $newKey => $newValue){
                    if(!empty($newValue)){
                        $checkIndexEmpty[] = $newKey;
                    }
                }

                if(count($checkIndexEmpty) == 1){
                    $usersArray = $newArr[$checkIndexEmpty[0]];
                }

                if(count($checkIndexEmpty) == 2){
                    $usersArray = array_intersect($newArr[$checkIndexEmpty[0]],$newArr[$checkIndexEmpty[1]]);
                }

                if(count($checkIndexEmpty) == 3){
                    $usersArray = array_intersect($newArr[$checkIndexEmpty[0]],$newArr[$checkIndexEmpty[1]],$newArr[$checkIndexEmpty[2]]);
                }
            }

            if(count($productsArray) > 0)
            {
                $join = join(",", $productsArray);
                if($condition != '')
                $condition .=" and marketplace_products.marketplace_product_id in(".$join.")";
                else
                $condition .="marketplace_products.marketplace_product_id in(".$join.")";
            }else{
                if($request->category && $request->category !=''){
                    if($condition != '')
                    $condition .=" and marketplace_products.marketplace_product_id in(0)";
                    else
                    $condition .="marketplace_products.marketplace_product_id in(0)";
                }
            }

            if(count($usersArray) > 0)
            {
                $joinUsers = join(",", $usersArray);
                if($condition != '')
                $condition .=" and marketplace_products.user_id in(".$joinUsers.")";
                else
                $condition .="marketplace_products.user_id in(".$joinUsers.")";
            }else{
                if(count($checkIndexEmpty) > 1){
                  $condition .=" and marketplace_products.user_id=0";  
                }
            }
            if(count($storesArray) > 0)
            {
                $joinStoresId = join(",", $storesArray);
                if($storCondition != '')
                $storCondition .=" and marketplace_stores.marketplace_store_id in(".$joinStoresId.")";
                else
                $storCondition .="marketplace_stores.marketplace_store_id in(".$joinStoresId.")";
            }
            if(count($storesUserArray) > 0)
            {
                $joinStoresUsers = join(",", $storesUserArray);
                if($storCondition != '')
                $storCondition .=" and marketplace_stores.user_id in(".$joinStoresUsers.")";
                else
                $storCondition .="marketplace_stores.user_id in(".$joinStoresUsers.")";
            }else{
                if(count($checkIndexEmpty) > 1){
                  $storCondition .=" and marketplace_stores.user_id=0";  
                }
            }
            if(!empty($request->keyword))
            {
                if($request->type == 1)
                {
                    if($storCondition != '')
                    $storCondition .=" and marketplace_stores.name LIKE "."'%".$request->keyword."%'"."";
                    else
                    $storCondition .="marketplace_stores.name LIKE "."'%".$request->keyword."%'"."";
                }
                elseif($request->type == 2)
                {
                    if($condition != '')
                    $condition .=" and marketplace_products.title LIKE "."'%".$request->keyword."%'"."";
                    else
                    $condition .="marketplace_products.title LIKE "."'%".$request->keyword."%'"."";
                }
                
            }
            if(!empty($request->sort_by_product))
            {
                if($request->sort_by_product == 1) //accending
                {
                    if($condition != '')
                    $condition .=" and marketplace_products.title LIKE '%".$request->keyword."%'";
                    else
                    $condition .="marketplace_products.title LIKE '%".$request->keyword."%'";
                }
            }
            $getFilterProducts = [];
            if($request->type == 1)
            {
                if($storCondition != '')
                {
                    $getFilterProducts = MarketplaceStore::with('region:id,name')->with('store_gallery')->where('status','1')->whereRaw('('.$storCondition.')')->orderBy('marketplace_store_id', 'DESC')->paginate(10);
                }
                else
                {
                    //$getFilterProducts = MarketplaceStore::with('region:id,name')->with('store_gallery')->where('status','1')->orderBy('marketplace_store_id', 'DESC')->paginate(10);
                    $getFilterProducts = [];
                }

                if(count($getFilterProducts) > 0)
                {
                    foreach($getFilterProducts as $key => $product)
                    {
                        $categoryIds = ($request->category && $request->category !="") ? explode(",", $request->category) : [];

                        $avgRating = MarketplaceRating::where('type', '1')->where('id', $product->marketplace_store_id)->avg('rating');
                        $totalReviews = MarketplaceRating::where('type', '1')->where('id', $product->marketplace_store_id)->count();
                        $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                        $storeProducts = MarketplaceProduct::where('marketplace_store_id',$product->marketplace_store_id)->where('status','1')->count();

                        if(!empty($categoryIds)){
                            $getProduct = MarketplaceProduct::distinct()->where('marketplace_store_id', $store->marketplace_store_id)->whereIn('product_category_id',$categoryIds)->where('status','1')->get(['product_category_id']);    
                        }else{
                            $getProduct = MarketplaceProduct::where('marketplace_store_id', $store->marketplace_store_id)->where('status','1')->first();
                        }

                        $arrayValues = array();
                        $fieldValues = DB::table('user_field_values')
                                    ->where('user_id', $store->user_id)
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
                                if(!empty($options->option))
                                $arrayValues[] = $this->translate('messages.'.$options->option,$options->option);
                                
                            }
                        }

                        if(!empty($arrayValues)){

                            $getFilterProducts[$key]->product_category_name = $arrayValues[0];
                            $getFilterProducts[$key]->count_category = count($arrayValues);

                        }else{
                            $getFilterProducts[$key]->product_category_name = "";    
                            $getFilterProducts[$key]->count_category = count($arrayValues);
                        }
                        
                        // if(empty($categoryIds) &&  !empty($getProduct->product_category_id))
                        // {
                        //     $options = DB::table('user_field_options')
                        //                     ->where('user_field_option_id', $getProduct->product_category_id)
                        //                     ->first();
                        //     (!empty($options->option) ? $getFilterProducts[$key]->product_category_name = $options->option : $getFilterProducts[$key]->product_category_name = '');
                        // }elseif($getProduct){
                        //     $categoryNameArray = [];
                        //     foreach($getProduct as $productKey => $productValue){
                        //         $options = DB::table('user_field_options')
                        //                     ->where('user_field_option_id', $productValue->product_category_id)
                        //                     ->first();
                        //         $categoryNameArray[] = $options->option;
                        //     }

                        //     $getFilterProducts[$key]->product_category_name = implode(',',$categoryNameArray);
                        // }
                        // else
                        // {
                        //     $getFilterProducts[$key]->product_category_name = '';
                        // }
                        
                        $totalCategories = array();
                        $fieldValues = DB::table('user_field_values')
                            ->where('user_id', $store->user_id)
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
                                
                                //$totalCategories[] = $options->option;
                                if(!empty($options->option))
                                $totalCategories[] = $options->option;
                            }
                        }

                        $logoId = Attachment::where('id', $store->logo_id)->first();
                        $bannerId = Attachment::where('id', $store->banner_id)->first();
                        $getFilterProducts[$key]->logo_id = $logoId->attachment_url;
                        $getFilterProducts[$key]->logo_base_url = $logoId->base_url;
                        $getFilterProducts[$key]->banner_id = $bannerId->attachment_url;
                        $getFilterProducts[$key]->banner_base_url = $bannerId->base_url;
                        $getFilterProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                        $getFilterProducts[$key]->total_reviews = $totalReviews;
                        $getFilterProducts[$key]->store_name = $store->name;
                        $getFilterProducts[$key]->count_product = count($totalCategories);//$storeProducts;
                    }
                }          
                
            }
            elseif($request->type == 2)
            {
                
                if($condition != '')
                {
                    
                    // if($request->box_id == 6){
                    //     if(count($this->getCertifiedProducts()) > 0)
                    //     {
                    //         $userIds = $this->getCertifiedProducts();
                    //         $query = MarketplaceProduct::with('product_gallery')->whereRaw('('.$condition.')')->whereIn('user_id', $userIds);
                    //     } 
                    // }
                    // elseif($request->box_id == 2 || $request->box_id == 5)
                    // {
                    //     if(count($this->getAllUsersByProductTypes($request->title)) > 0)
                    //     {
                    //         $conservationUsers = $this->getAllUsersByProductTypes($request->title);
                    //         $query = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $conservationUsers)->whereRaw('('.$condition.')');
                    //     }
                    // }
                    // elseif($request->box_id == 3)
                    // {
                    //     if(count($this->getAllProductsByRegions($request->title)) > 0)
                    //     {
                    //         $usersByRegion = $this->getAllProductsByRegions($request->title);
                    //         $query = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $usersByRegion)->whereRaw('('.$condition.')');
                    //     }
                    // }
                    // elseif($request->box_id == 4)
                    // {
                    //     $query = MarketplaceProduct::with('product_gallery')->where('product_category_id', $request->title)->whereRaw('('.$condition.')');
                    // }
                    // elseif($request->box_id == 7)
                    // {
                    //     if(count($this->getAllFavouriteProducts($request->title)) > 0)
                    //     {
                    //         $favProducts = $this->getAllFavouriteProducts($request->title);
                    //         $query = MarketplaceProduct::with('product_gallery')->whereIn('marketplace_product_id', $favProducts)->whereRaw('('.$condition.')');
                    //     }
                    // }
                    
                    // if($request->sort_by_product == 1) {//accending
                    //     $query->orderBy('title','asc');
                    // }
                    // else{
                    //     $query->orderBy('title','desc');
                    // }
                    $query = MarketplaceProduct::with('product_gallery')->whereRaw('('.$condition.')')->where('status','1');
                    $getFilterProducts = $query->paginate(10);
                    //echo $getFilterProducts = $query->toSql();exit;
                }
                else
                {
                    
                    if($request->box_id == 6){
                        if(count($this->getCertifiedProducts()) > 0)
                        {
                            $userIds = $this->getCertifiedProducts();
                            $query = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $userIds)->where('status','1');
                            
                        } 
                    }
                    elseif($request->box_id == 2 || $request->box_id == 5)
                    {
                        if(count($this->getAllUsersByProductTypes($request->title)) > 0)
                        {
                            $conservationUsers = $this->getAllUsersByProductTypes($request->title);
                            $query = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $conservationUsers)->where('status','1');
                        }
                    }
                    elseif($request->box_id == 3)
                    {
                        if(count($this->getAllProductsByRegions($request->title)) > 0)
                        {
                            $usersByRegion = $this->getAllProductsByRegions($request->title);
                            $query = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $usersByRegion)->where('status','1');
                        }
                    }
                    elseif($request->box_id == 4)
                    {
                        $query = MarketplaceProduct::with('product_gallery')->where('product_category_id', $request->title)->where('status','1');
                    }
                    elseif($request->box_id == 7)
                    {
                        if(count($this->getAllFavouriteProducts($request->title)) > 0)
                        {
                            $favProducts = $this->getAllFavouriteProducts($request->title);
                            $query = MarketplaceProduct::with('product_gallery')->whereIn('marketplace_product_id', $favProducts)->where('status','1');
                        }
                    }

                    
                    if($request->sort_by_product == 1) {//accending
                        $query->orderBy('title','asc');
                    }
                    else{
                        $query->orderBy('title','desc');
                    }
                    
                    $getFilterProducts = $query->paginate(10);
                }


                /***/
                if(count($getFilterProducts) > 0)
                {
                    foreach($getFilterProducts as $key => $product)
                    {
                        $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->avg('rating');
                        $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                        $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();
                        $storeProducts = MarketplaceProduct::where('marketplace_store_id',$product->marketplace_store_id)->where('status','1')->count();

                        $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                        if(!empty($options->option))
                        {
                            $getFilterProducts[$key]->product_category_name = $options->option;
                        }
                        else
                        {
                            $getFilterProducts[$key]->product_category_name = '';
                        }

                        $totalCategories = array();
                        $fieldValues = DB::table('user_field_values')
                            ->where('user_id', $store->user_id)
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
                                
                                //$totalCategories[] = $options->option;
                                if(!empty($options->option))
                                $totalCategories[] = $options->option;
                            }
                        }


                        $logoId = Attachment::where('id', $store->logo_id)->first();
                        $bannerId = Attachment::where('id', $store->banner_id)->first();
                        $getFilterProducts[$key]->logo_id = $logoId->attachment_url;
                        $getFilterProducts[$key]->logo_base_url = $logoId->base_url;
                        $getFilterProducts[$key]->banner_id = $logoId->attachment_url;
                        $getFilterProducts[$key]->banner_base_url = $logoId->base_url;
                        $getFilterProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                        $getFilterProducts[$key]->total_reviews = $totalReviews;
                        $getFilterProducts[$key]->store_name = $store->name;
                        $getFilterProducts[$key]->count_product = count($totalCategories);//$storeProducts;
                    }
                }
                else
                {
                    return response()->json(['success' => $this->successStatus,
                                    'count' => count($getFilterProducts),
                                     'data' => $getFilterProducts   
                                    ],$this->successStatus);
                }
                
            }
            
            return response()->json(['success' => $this->successStatus,
                                    'count' => count($getFilterProducts),
                                     'data' => $getFilterProducts   
                                    ],$this->successStatus);
        // }
        // catch(\Exception $e)
        // {
        //     return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        // }         
    }

    /*
     * Get all entities for homescreen
     * 
     */
    public function getAllEntities($entityId = '')
    {
        try
        {
            $user = $this->user;

            if($entityId == 1)
            {
                return $this->getRecentlyAddedProducts();
            }
            elseif($entityId == 2)
            {
                return $this->getNewlyAddedStores();
            }
            elseif($entityId == 3)
            {
                return $this->gettopRatedProducts();
            }
            else
            {
                $message = "wrong entity selected";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Get recently added products
     * 
     */
    public function getRecentlyAddedProducts()
    {
        $topRatedProducts = MarketplaceProduct::with('getProductTax.getTaxClasses.getTaxDetail')->where('status','1')->with('product_gallery')->orderBy('marketplace_product_id', 'DESC')->paginate(12);
        if(count($topRatedProducts) > 0)
        {
            foreach($topRatedProducts as $topKey => $topRatedProduct)
            {
                $avgRatingOfTopRated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->avg('rating');
                $totalReviewsOfToprated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->count();
                $storeOfTopRated = MarketplaceStore::where('marketplace_store_id', $topRatedProduct->marketplace_store_id)->first();
                $productOfTopRatedImg = MarketplaceProductGallery::where('marketplace_product_id', $topRatedProduct->marketplace_product_id)->first();
                
                $options = DB::table('user_field_options')
                        ->where('head', 0)->where('parent', 0)
                        ->where('user_field_option_id', $topRatedProduct->product_category_id)
                        ->first();
                $topRatedProducts[$topKey]->product_category_name = (!empty($options->option) ? $options->option : '');
                if($productOfTopRatedImg){
                    if(!empty($productOfTopRatedImg->attachment_medium_url))
                    {
                        $topRatedProducts[$topKey]->logo_id = $productOfTopRatedImg->attachment_medium_url;    
                    }
                    else
                    {
                        $topRatedProducts[$topKey]->logo_id = $productOfTopRatedImg->attachment_url;
                    }
                }
                $topRatedProducts[$topKey]->avg_rating = number_format((float)$avgRatingOfTopRated, 1, '.', '');
                $topRatedProducts[$topKey]->total_reviews = $totalReviewsOfToprated;
                $topRatedProducts[$topKey]->store_name = (!empty($storeOfTopRated->name) ? $storeOfTopRated->name : '');
            }
            
            return response()->json(['success' => $this->successStatus,
                                'data' => $topRatedProducts,
                                ],$this->successStatus);
        }
        else
        {
            $message = "We did not found any products";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get newly added stores
     * 
     */
    public function getNewlyAddedStores()
    {
        $newAddedStores = MarketplaceStore::with('region:id,name')->where('status', '1')->orderBy('marketplace_store_id', 'DESC')->paginate(12);
        if(count($newAddedStores) > 0)
        {
            foreach($newAddedStores as $key => $store)
            {
                $avgRating = MarketplaceRating::where('type', '1')->where('id', $store->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '1')->where('id', $store->marketplace_store_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $store->marketplace_store_id)->first();

                //$getProduct = MarketplaceProduct::where('marketplace_store_id', $store->marketplace_store_id)->first();


                $arrayValues = array();
                $fieldValues = DB::table('user_field_values')
                            ->where('user_id', $store->user_id)
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
                        if(!empty($options->option))
                        $arrayValues[] = $this->translate('messages.'.$options->option,$options->option);
                        
                    }
                }

                if(!empty($arrayValues)){

                    $newAddedStores[$key]->product_category_name = $arrayValues[0];
                    $newAddedStores[$key]->count_category = count($arrayValues);

                }else{
                    $newAddedStores[$key]->product_category_name = "";    
                    $newAddedStores[$key]->count_category = count($arrayValues);
                }
                


                // if(!empty($getProduct->product_category_id))
                // {
                //     $options = DB::table('user_field_options')
                //                     ->where('user_field_option_id', $getProduct->product_category_id)
                //                     ->first();
                //     (!empty($options->option) ? $newAddedStores[$key]->product_category_name = $options->option : $newAddedStores[$key]->product_category_name = '');
                // }
                // else
                // {
                //     $newAddedStores[$key]->product_category_name = '';
                // }
                $storeProducts = MarketplaceProduct::where('marketplace_store_id',$store->marketplace_store_id)->count();
                
                // $totalCategories = array();
                // $fieldValues = DB::table('user_field_values')
                //     ->where('user_id', $store->user_id)
                //     ->where('user_field_id', 2)
                //     ->get();
                // if(count($fieldValues) > 0)
                // {
                //     foreach($fieldValues as $fieldValue)
                //     {
                //         $options = DB::table('user_field_options')
                //                 ->where('head', 0)->where('parent', 0)
                //                 ->where('user_field_option_id', $fieldValue->value)
                //                 ->first();
                        
                //         //$totalCategories[] = $options->option;
                //         if(!empty($options->option))
                //         $totalCategories[] = $options->option;
                //     }
                // }


                $logoId = Attachment::where('id', $store->logo_id)->first();
                $bannerId = Attachment::where('id', $store->banner_id)->first();
                if($logoId->attachment_medium_url){
                    $newAddedStores[$key]->logo_id = $logoId->attachment_medium_url;
                }
                else{
                    $newAddedStores[$key]->logo_id = $logoId->attachment_url;
                }
                $newAddedStores[$key]->logo_base_url = $logoId->base_url;
                $newAddedStores[$key]->banner_id = $logoId->attachment_url;
                $newAddedStores[$key]->banner_base_url = $logoId->base_url;
                        
                $newAddedStores[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $newAddedStores[$key]->total_reviews = $totalReviews;
                $newAddedStores[$key]->store_name = $store->name;
                $newAddedStores[$key]->count_product = $storeProducts;//count($totalCategories);
            }
            return response()->json(['success' => $this->successStatus,
                                    'data' => $newAddedStores,
                                    ],$this->successStatus);
        }
        else
        {
            $message = "We did not found any stores";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get top rated products
     * 
     */
    public function gettopRatedProducts()
    {
        $topRatedProductsArray = [];
        $topRatedProductsData = DB::select(DB::raw("select id from marketplace_review_ratings where type = '2' GROUP BY id ORDER BY count(*) DESC"));
        if(count($topRatedProductsData) > 0)
        {
            foreach($topRatedProductsData as $topRatedProductData)
            {
                array_push($topRatedProductsArray, $topRatedProductData->id);
            }

            $topRatedProducts = MarketplaceProduct::with('getProductTax.getTaxClasses.getTaxDetail','product_gallery')->whereIn('marketplace_product_id', $topRatedProductsArray)->where('status','1')->orderBy('marketplace_product_id', 'DESC')->paginate(12);

            if(count($topRatedProducts) > 0)
            {
                foreach($topRatedProducts as $topKey => $topRatedProduct)
                {
                    $avgRatingOfTopRated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsOfToprated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->count();
                    $storeOfTopRated = MarketplaceStore::where('marketplace_store_id', $topRatedProduct->marketplace_store_id)->first();
                    $productOfTopRatedImg = MarketplaceProductGallery::where('marketplace_product_id', $topRatedProduct->marketplace_product_id)->first();
                    
                    $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $topRatedProduct->product_category_id)
                            ->first();
                    $topRatedProducts[$topKey]->product_category_name = (!empty($options->option) ? $options->option : '');

                    if(!empty($productOfTopRatedImg->attachment_medium_url))
                    {
                        $topRatedProducts[$topKey]->logo_id = $productOfTopRatedImg->attachment_medium_url;    
                    }
                    else
                    {
                        $topRatedProducts[$topKey]->logo_id = $productOfTopRatedImg->attachment_url;
                    }
                    $topRatedProducts[$topKey]->avg_rating = number_format((float)$avgRatingOfTopRated, 1, '.', '');
                    $topRatedProducts[$topKey]->total_reviews = $totalReviewsOfToprated;
                    $topRatedProducts[$topKey]->store_name = $storeOfTopRated->name;
                }
                
                return response()->json(['success' => $this->successStatus,
                                    'data' => $topRatedProducts,
                                    ],$this->successStatus);
            }
        }
        else
        {
            $message = "We did not found any products";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    } 

     /*
     * Get homepage data
     * 
     */
    public function getHomeScreen()
    {
        /*try
        {*/
            $user = $this->user;
            $topRatedProductsArray = [];
            $topFavouriteProductsArray = [];
            $allProducts = MarketplaceProduct::with('getProductTax.getTaxClasses.getTaxDetail')->where('status','1')->orderBy('marketplace_product_id', 'DESC')->limit(8)->get();
            $allStores = MarketplaceStore::with('region:id,name')->with('logo_id')->where('status', '1')->orderBy('marketplace_store_id', 'DESC')->limit(8)->get();
            $allRegions = State::select('id','name','flag_id')->with('flag_id')->where('status', '1')->where('country_id', 107)->orderBy('name', 'ASC')->get();
            $topBanners = MarketplaceBanner::with('attachment')->where('type', '1')->orderBy('marketplace_banner_id', 'DESC')->get();
            $lowerBanners = MarketplaceBanner::with('attachment')->where('type', '2')->orderBy('marketplace_banner_id', 'DESC')->get();

            if(count($allStores) > 0){
                foreach($allStores as $key => $store)
                {
                    $avgRating = MarketplaceRating::where('type', '1')->where('id', $store->marketplace_store_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '1')->where('id', $store->marketplace_store_id)->count();
                    $allStores[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $allStores[$key]->total_reviews = $totalReviews;
                }
            }

            if($allRegions){
                foreach($allRegions as $key=>$region){
                    $allRegions[$key]->name = $this->translate('messages.'.$region->name,$region->name);
                }

                $allRegions = $this->MysortArray($allRegions,'name','ASC');
                $eigthRegions = [];
                foreach($allRegions as $key => $val){
                    if($key <= 7){
                        $eigthRegions[] = $val;
                    }
                }
            }

            if(count($allProducts) > 0)
            {
                foreach($allProducts as $key => $product)
                {
                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                    $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();
                    $productImg = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->first();

                    if(!empty($productImg->attachment_url))
                    {
                        //$allProducts[$key]->logo_id = $productImg->attachment_url;
                        $allProducts[$key]->logo_id = $productImg->attachment_medium_url;
                        $allProducts[$key]->base_url = $productImg->base_url;    
                    }
                    else
                    {
                        $allProducts[$key]->logo_id = "";
                        $allProducts[$key]->base_url="";
                    }

                    // $allProducts[$key]->get_product_offer = ProductOffer::select('offer_id','seller_id','buyer_id','product_id')->with('getMapOffer')->where(['buyer_id' => $user->user_id,
                    //                  'product_id' => $product->marketplace_product_id
                    //                 ])->first();
                    
                    $allProducts[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $allProducts[$key]->total_reviews = $totalReviews;
                    $allProducts[$key]->store_name = (!empty($store->name) ? $store->name : '');
                }
                
            }

            //top rated products
            $topRatedProductsData = DB::select(DB::raw("select id from marketplace_review_ratings where type = '2' GROUP BY id ORDER BY count(*) DESC LIMIT 8"));
            foreach($topRatedProductsData as $topRatedProductData)
            {
                array_push($topRatedProductsArray, $topRatedProductData->id);
            }

            $topRatedProducts = MarketplaceProduct::with('product_gallery','getProductTax.getTaxClasses.getTaxDetail')->whereIn('marketplace_product_id', $topRatedProductsArray)->orderBy('marketplace_product_id', 'DESC')->limit(8)->get();
            if(count($topRatedProducts) > 0)
            {
                foreach($topRatedProducts as $topKey => $topRatedProduct)
                {
                    $avgRatingOfTopRated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsOfToprated = MarketplaceRating::where('type', '2')->where('id', $topRatedProduct->marketplace_product_id)->count();
                    $storeOfTopRated = MarketplaceStore::where('marketplace_store_id', $topRatedProduct->marketplace_store_id)->first();
                    $productOfTopRatedImg = MarketplaceProductGallery::where('marketplace_product_id', $topRatedProduct->marketplace_product_id)->first();
                    
                    // $topRatedProducts[$topKey]->get_product_offer = ProductOffer::select('offer_id','seller_id','buyer_id','product_id')->with('getMapOffer')->where(['buyer_id' => $user->user_id,
                    //                  'product_id' => $topRatedProduct->marketplace_product_id
                    //                 ])->first();
                    if(!empty($productOfTopRatedImg->attachment_url))
                    {
                        $topRatedProducts[$topKey]->logo_id = $productOfTopRatedImg->attachment_url;    
                        $topRatedProducts[$topKey]->base_url = $productOfTopRatedImg->base_url;    
                    }
                    else
                    {
                        $topRatedProducts[$topKey]->logo_id = "";
                        $topRatedProducts[$topKey]->base_url = "";
                    }
                    $topRatedProducts[$topKey]->avg_rating = number_format((float)$avgRatingOfTopRated, 1, '.', '');
                    $topRatedProducts[$topKey]->total_reviews = $totalReviewsOfToprated;
                    $topRatedProducts[$topKey]->store_name = (!empty($storeOfTopRated->name) ? $storeOfTopRated->name : '');
                }
                
            }
            //

            //top favourite products
            $topFavouriteProductsData = DB::select(DB::raw("select id from marketplace_favourites where favourite_type = '2' GROUP BY id ORDER BY count(*) DESC LIMIT 8"));
            foreach($topFavouriteProductsData as $topFavouriteProductData)
            {
                array_push($topFavouriteProductsArray, $topFavouriteProductData->id);
            }

            $topFavouriteProducts = MarketplaceProduct::with('product_gallery','getProductTax.getTaxClasses.getTaxDetail')->whereIn('marketplace_product_id', $topFavouriteProductsArray)->orderBy('marketplace_product_id', 'DESC')->limit(8)->get();
            if(count($topFavouriteProducts) > 0)
            {
                foreach($topFavouriteProducts as $topFavKey => $topFavouriteProduct)
                {
                    $avgRatingOfTopFavourite = MarketplaceRating::where('type', '2')->where('id', $topFavouriteProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsOfTopFavourite = MarketplaceRating::where('type', '2')->where('id', $topFavouriteProduct->marketplace_product_id)->count();
                    $storeOfTopFavourite = MarketplaceStore::where('marketplace_store_id', $topFavouriteProduct->marketplace_store_id)->first();
                    $productOfTopFavouriteImg = MarketplaceProductGallery::where('marketplace_product_id', $topFavouriteProduct->marketplace_product_id)->first();

                    // $topFavouriteProducts[$topFavKey]->get_product_offer = ProductOffer::select('offer_id','seller_id','buyer_id','product_id')->with('getMapOffer')->where(['buyer_id' => $user->user_id,
                    //                  'product_id' => $topFavouriteProduct->marketplace_product_id
                    //                 ])->first();

                    if(!empty($productOfTopFavouriteImg->attachment_url))
                    {
                        $topFavouriteProducts[$topFavKey]->logo_id = $productOfTopFavouriteImg->attachment_url; 
                        $topFavouriteProducts[$topFavKey]->base_url = $productOfTopFavouriteImg->base_url;    
                    }
                    else
                    {
                        $topFavouriteProducts[$topFavKey]->logo_id = "";
                        $topFavouriteProducts[$topFavKey]->base_url = "";
                    }

                    $topFavouriteProducts[$topFavKey]->avg_rating = number_format((float)$avgRatingOfTopFavourite, 1, '.', '');
                    $topFavouriteProducts[$topFavKey]->total_reviews = $totalReviewsOfTopFavourite;
                    $topFavouriteProducts[$topFavKey]->store_name = $storeOfTopFavourite->name;
                }
                
            } 
            User::where('user_id', $user->user_id)->update(['is_visited_marketplace' => '1']);

            $data = ['top_banners' => $topBanners, 'recently_added_product' => $allProducts, 'newly_added_store' => $allStores, 'regions' => $eigthRegions, 'top_rated_products' => $topRatedProducts,'top_favourite_products' => $topFavouriteProducts, 'bottom_banners' => $lowerBanners];
            return response()->json(['success' => $this->successStatus,
                                     'is_visited_marketplace' => ($user->is_visited_marketplace == '1' ? 1 : 0),
                                     'data' => $data   
                                    ],$this->successStatus);
        /*}
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }*/
    }


    /*
     * Get all stores
     * 
     */
    public function getAllStores()
    {
        $allStores = MarketplaceStore::with('region:id,name')->where('status', '1')->orderBy('marketplace_store_id', 'DESC')->paginate(10);
        if(count($allStores) > 0)
        {
            foreach($allStores as $key => $store)
            {
                $avgRating = MarketplaceRating::where('type', '1')->where('id', $store->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '1')->where('id', $store->marketplace_store_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $store->marketplace_store_id)->first();

                $getProduct = MarketplaceProduct::where('marketplace_store_id', $store->marketplace_store_id)->first();

                if(!empty($getProduct->product_category_id))
                {
                    $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $getProduct->product_category_id)
                                    ->first();
                    (!empty($options->option) ? $allStores[$key]->product_category_name = $options->option : $allStores[$key]->product_category_name = '');
                }
                else
                {
                    $allStores[$key]->product_category_name = '';
                }
                
                $storeProducts = MarketplaceProduct::where('marketplace_store_id',$store->marketplace_store_id)->count();

                $totalCategories = array();
                $fieldValues = DB::table('user_field_values')
                    ->where('user_id', $store->user_id)
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
                        
                        //$totalCategories[] = $options->option;
                        if(!empty($options->option))
                        $totalCategories[] = $options->option;
                    }
                }

                $logoId = Attachment::where('id', $store->logo_id)->first();
                $bannerId = Attachment::where('id', $store->banner_id)->first();
                $allStores[$key]->logo_id = $logoId->attachment_url;
                $allStores[$key]->logo_base_url = $logoId->base_url;
                $allStores[$key]->banner_id = $logoId->attachment_url;
                $allStores[$key]->banner_base_url = $logoId->base_url;
                $allStores[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $allStores[$key]->total_reviews = $totalReviews;
                $allStores[$key]->store_name = $store->name;
                $allStores[$key]->count_product = $storeProducts;
                $allStores[$key]->count_category = count($totalCategories);

        	}
            return response()->json(['success' => $this->successStatus,
                                    'data' => $allStores,
                                	],$this->successStatus); 
        }
        else
        {
            $message = "We did not found any stores";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get conservation methods
     * 
     */
    public function getConservationMethod(Request $request)
    {
        $input = $request->all();
        if(!empty($input) && array_key_exists('category_id',$input)){
            $options = DB::table('user_field_options')
                                ->where('head','!=', 0)->where('parent','=', $input['category_id'])
                                ->where('user_field_id', 2)
                                ->first();

        }else{

            $options = DB::table('user_field_options')
                                ->where('head','!=', 0)->where('parent','!=', 0)
                                ->where('user_field_id', 2)
                                ->first();
        }

        if($options)
        {
            $childOptions = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', $options->user_field_option_id)
                                ->where('user_field_id', 2)
                                ->get()->toArray();
            foreach($childOptions as $key=>$opt){
                $childOptions[$key]->actual_name = $opt->option;
                $childOptions[$key]->option = $this->translate('messages.'.$opt->option,$opt->option);
            }

            array_multisort(array_column( $childOptions, 'option' ), SORT_ASC, $childOptions);

            return response()->json(['success' => $this->successStatus,
                                    'data' => $childOptions,
                                    ],$this->successStatus); 
        }
        else
        {
            $message = "We did not found any conservation methods";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get Products box-2/5
     * 
     */
    public function getProducts(Request $request)
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

            return $this->getProductsBySelection($request->keyword);
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
        
    }

    /*
     * Get Products by regions
     * 
     */
    public function getProductsByRegions(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'region_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $getUsersByRegion = User::where('state', $request->region_id)->get();
            if(count($getUsersByRegion) > 0)
            {
                $getUserIds = $getUsersByRegion->pluck('user_id');
                $products = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $getUserIds)->paginate(10);
                if(count($products) > 0)
                {
                    foreach($products as $key => $product)
                    {
                        $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                        if(!empty($options->option))
                        {
                            $products[$key]->product_category_name = $options->option;
                        }
                        else
                        {
                            $products[$key]->product_category_name = '';
                        }
                        $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                        $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                        $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                        $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                        $products[$key]->total_reviews = $totalReviews;
                        $products[$key]->store_name = $store->name;
                    }
                    return response()->json(['success' => $this->successStatus,
                                    'count' => count($products),
                                    'data' => $products,
                                    ], $this->successStatus);
                }
                else
                {
                    $message = "No product found";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
                }
            }
            else
            {
                $message = "No product found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
        
    }

    /*
     * Get Products by category
     * 
     */
    public function getProductsByCategory(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'category_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            
            $products = MarketplaceProduct::with('product_gallery')->where('product_category_id', $request->category_id)->paginate(10);
            if(count($products) > 0)
            {
                foreach($products as $key => $product)
                {
                    $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                    if(!empty($options->option))
                    {
                        $products[$key]->product_category_name = $options->option;
                    }
                    else
                    {
                        $products[$key]->product_category_name = '';
                    }
                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                    $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                    $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $products[$key]->total_reviews = $totalReviews;
                    $products[$key]->store_name = $store->name;
                }
                return response()->json(['success' => $this->successStatus,
                                'count' => count($products),
                                'data' => $products,
                                ], $this->successStatus);
            }
            else
            {
                $message = "No product found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
            }
            
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
        
    }

    /*
    *
    * Get products by conservation methods or product properties
    *
    */
    public function getProductsBySelection($keyword='')
    {
        $options = DB::table('user_field_options')
                                ->where('option', 'LIKE', '%'.$keyword.'%')
                                ->where('user_field_id', 2)
                                ->get();
                                

        if(count($options) > 0)
        {
            $getOptionIds = $options->pluck('user_field_option_id');
            $values = DB::table('user_field_values')
                                ->whereIn('value', $getOptionIds)
                                ->where('user_field_id', 2)
                                ->get();
            if(count($values) > 0)
            {
                $userIds = $values->pluck('user_id');
                $products = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $userIds)->paginate(10);
                foreach($products as $key => $product)
                {
                    $options = DB::table('user_field_options')
                                ->where('user_field_option_id', $product->product_category_id)
                                ->first();

                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                    $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                    $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $products[$key]->total_reviews = $totalReviews;
                    $products[$key]->store_name = $store->name??'';
                    if(!empty($options->option))
                    {
                        $products[$key]->product_category_name = $options->option;
                    }
                    else
                    {
                        $products[$key]->product_category_name = '';
                    }
                    
                }
                
            }
            else
            {
                $message = "No product found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
            return response()->json(['success' => $this->successStatus,
                                    'data' => $products,
                                    ],$this->successStatus); 
        }
        else
        {
            $message = "No product found";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get product properties
     * 
     */
    public function getProductProperties(Request $request)
    {
        $input = $request->all();
        if(!empty($input) && array_key_exists('category_id',$input)){
            $options = DB::table('user_field_options')
                                ->where('head','!=', 0)->where('parent','=', $input['category_id'])
                                ->where('user_field_id', 2)->skip(1)
                                ->first();
        }else{
            $options = DB::table('user_field_options')
                                ->where('head','!=', 0)->where('parent','!=', 0)
                                ->where('user_field_id', 2)->skip(1)
                                ->orderBy('option', 'DESC')
                                ->first();
        }
        
        if($options)
        {
            $childOptions = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', $options->user_field_option_id)
                                ->where('user_field_id', 2)
                                ->where('option','!=', 'Others')
                                ->orderBy('option', 'ASC')
                                ->get()->toArray();
            foreach($childOptions as $key=>$opt){
                $childOptions[$key]->actual_name = $opt->option;
                $childOptions[$key]->option = $this->translate('messages.'.$opt->option,$opt->option);
            }

            array_multisort(array_column( $childOptions, 'option' ), SORT_ASC, $childOptions);

            $otherOption = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', $options->user_field_option_id)
                                ->where('user_field_id', 2)
                                ->where('option', 'Others')
                                ->orderBy('option', 'ASC')
                                ->first();

            $otherOption->actual_name = $otherOption->option;
            $otherOption->option = $this->translate('messages.'.$otherOption->option,$otherOption->option);

            //$key = array_search('Others', array_column($childOptions, 'option'));

            // $otherValue = $childOptions[$key];
            // unset($childOptions[$key]);
             array_push($childOptions,$otherOption);

            return response()->json(['success' => $this->successStatus,
                                    'data' => $childOptions,
                                    ],$this->successStatus); 
        }
        else
        {
            $message = "We did not found any product properties";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get all regions
     * 
     */
    public function getAllRegions() 
    {
    	$allRegions = State::select('id','name','flag_id')->with('flag_id')->where('status', '1')->where('country_id', 107)->orderBy('name', 'ASC')->get();
        if(count($allRegions) > 0)
        {
            foreach($allRegions as $key=>$region){
                $allRegions[$key]->name = $this->translate('messages.'.$region->name,$region->name);
            }
            $allRegions = $this->MysortArray($allRegions,'name','ASC');
            return response()->json(['success' => $this->successStatus,
                                    'data' => $allRegions,
                                	],$this->successStatus); 
        }
        else
        {
            $message = "We did not found any regions";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get product categories
     * 
     */
    public function getProductCategories()
    {
    	$options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_id', 2)
                                ->orderBy('option', 'ASC')
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

    /*
     * Get my favourite products
     * 
     */
    public function getMyFavouriteProducts()
    {
    	$user = $this->user;
    	$favouriteList = MarketplaceFavourite::where('favourite_type', '2')->where('user_id', $user->user_id)->get();
    	if(count($favouriteList) > 0)
    	{
    		$productIds = $favouriteList->pluck('id');
    		$products = MarketplaceProduct::with('product_gallery')->whereIn('marketplace_product_id', $productIds)->paginate(10);
    		foreach($products as $key => $product)
    		{
                $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                if(!empty($options->option))
                {
                    $products[$key]->product_category_name = $options->option;
                }
                else
                {
                    $products[$key]->product_category_name = '';
                }
    			$avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $products[$key]->total_reviews = $totalReviews;
                $products[$key]->store_name = $store->name;
    		}
    		return response()->json(['success' => $this->successStatus,
                            'count' => count($products),
                            'data' => $products,
                            ], $this->successStatus);
    	}
        else
        {
            $message = "No product found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
        }
    }

    /*
     * Get FDA certified products
     * 
     */
    public function getFDACertifiedProducts()
    {
    	$user = $this->user;
    	$userList = User::where('fda_no','!=',null)->get();
    	if(count($userList) > 0)
    	{
    		$userIds = $userList->pluck('user_id');
    		$products = MarketplaceProduct::with('product_gallery')->whereIn('user_id', $userIds)->paginate(10);
    		foreach($products as $key => $product)
    		{
                $options = DB::table('user_field_options')
                                    ->where('user_field_option_id', $product->product_category_id)
                                    ->first();
                if(!empty($options->option))
                {
                    $products[$key]->product_category_name = $options->option;
                }
                else
                {
                    $products[$key]->product_category_name = '';
                }
    			$avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();
                $store = MarketplaceStore::where('marketplace_store_id', $product->marketplace_store_id)->first();

                $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $products[$key]->total_reviews = $totalReviews;
                $products[$key]->store_name = $store->name;
    		}
    		return response()->json(['success' => $this->successStatus,
                            'count' => count($products),
                            'data' => $products,
                            ], $this->successStatus);
    	}
        else
        {
            $message = "No product found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
        }
    }

    /*
     * Get all product types
     * 
     */
    public function getAllUsersByProductTypes($title='')
    {
        $userIds = [];

        $options = DB::table('user_field_options')
                                ->where('option', 'LIKE', '%'.$title.'%')
                                ->where('user_field_id', 2)
                                ->get();
        if(count($options) > 0)
        {
            $getOptionIds = $options->pluck('user_field_option_id');
            $values = DB::table('user_field_values')
                                ->whereIn('value', $getOptionIds)
                                ->where('user_field_id', 2)
                                ->get();
            if(count($values) > 0)
            {
                $userIds = $values->pluck('user_id');
            }
        }    
        return $userIds;
    }

    /*
     * Get all products by regions
     * 
     */
    public function getAllProductsByRegions($regionId='')
    {
        $getUserRegions = [];
        $getUsersByRegion = User::where('state', $regionId)->get();
        if(count($getUsersByRegion) > 0)
        {
            $getUserRegions = $getUsersByRegion->pluck('user_id');
        }
        return $getUserRegions;
    }

     /*
     * Get certified products
     * 
     */
    public function getCertifiedProducts()
    {
        $user = $this->user;
        $userList = User::where('fda_no','!=',null)->get();
        if(count($userList) > 0)
        {
            $userIds = $userList->pluck('user_id');
        }
        else
        {
            $userIds = [];  
        }
        return $userIds;
    }

    /*
    * 
    *
    */
    public function getAllFavouriteProducts()
    {
        $user = $this->user;
        $productIds = [];
        $favouriteList = MarketplaceFavourite::where('favourite_type', '2')->where('user_id', $user->user_id)->get();
        if(count($favouriteList) > 0)
        {
            $productIds = $favouriteList->pluck('id');
        }
        return $productIds;
    }

    /*
     * Get Conservation and Properties
     * @fieldOptionId
     */
    public function getConservationAndProperties($fieldOptionId){
        $options = [];
        $data = $this->getUserFieldOptionsNoneParent($fieldOptionId);
        $options = $data;
        foreach($data as $key => $value){
            $noneParentData = $this->getUserFieldOptionsNoneParent($value->user_field_option_id);
            $options[$key]->options = $noneParentData; 
        }

        if(!empty($options)){
            return response()->json(['success' => $this->successStatus,
                            'data' => $options,
                            ], $this->successStatus);
        }
        else{
            $message = "No method found";
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get All Fields Option who are child
     * @params $user_field_id and $user_field_option_id
     */
    public function getUserFieldOptionsNoneParent($parentId){

        $fieldOptionData = [];
        
        if($parentId > 0){
            $fieldOptionData = DB::table('user_field_options')
                ->where('user_field_id','=',2)
                ->where('deleted_at',null)
                ->where('parent','=',$parentId)
                ->get()->toArray();                                

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->hint = $option->hint;
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }
            
            array_multisort(array_column( $fieldOptionData, 'option' ), SORT_ASC, $fieldOptionData);
            
        }
        
        return $fieldOptionData;    
        
    }

    // Payment Setting Function
    public function paymentSetting(Request $request){
        try{
            $validator = Validator::make($request->all(), [ 
                'payment_option' => 'required|in:paypal,bank',
            ]);
        

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $userExist = paymentSetting::where('user_id',$this->user->user_id)->first();
            if($userExist){
                $userExist->account_holder_name = $request->account_holder_name;
                $userExist->bank_name = $request->bank_name;
                $userExist->account_number = $request->account_number;
                $userExist->swift_code = $request->swift_code;
                $userExist->bank_address = $request->bank_address;
                $userExist->paypal_id = $request->paypal_id;
                $userExist->payment_limit = $request->payment_limit;
                $userExist->payment_option = $request->payment_option;
                $userExist->country = $request->country;
                $userExist->city = $request->city;
                $userExist->default_payment = $request->default_payment;
                $userExist->save();
            }
            else{
                $paymentSetting = new PaymentSetting();
                $paymentSetting->user_id = $this->user->user_id;
                $paymentSetting->account_holder_name = $request->account_holder_name;
                $paymentSetting->bank_name = $request->bank_name;
                $paymentSetting->account_number = $request->account_number;
                $paymentSetting->swift_code = $request->swift_code;
                $paymentSetting->bank_address = $request->bank_address;
                $paymentSetting->paypal_id = $request->paypal_id;
                $paymentSetting->payment_limit = $request->payment_limit;
                $paymentSetting->payment_option = $request->payment_option;
                $paymentSetting->country = $request->country;
                $paymentSetting->city = $request->city;
                $userExist->default_payment = $request->default_payment;
                $paymentSetting->save();
            }

            return response()->json(['success' => $this->successStatus,
                                    'message' => 'Setting update successfully',
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    public function getPaymentSetting(){
        $paymentSetting = paymentSetting::where('user_id',$this->user->user_id)->first();
        return response()->json(['success' => $this->successStatus,
                                    'payment' => $paymentSetting,
                                    ], $this->successStatus);
    }


    // Get Importer Users List
    public function getImporterUsersList(){
        try{
            $incoterms = Incoterms::orderBy('id','asc')->get();
            $importers = User::select('user_id','company_name')->where('profile_percentage',100)->whereIn('role_id',[4,5,6])->get();
            return response()->json(['success' => $this->successStatus,
                                    'data' => $importers,
                                    'incoterms' => $incoterms,
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

}