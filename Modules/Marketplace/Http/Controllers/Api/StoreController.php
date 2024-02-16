<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use Modules\Marketplace\Entities\MarketplaceRating;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use Modules\Marketplace\Entities\MarketplaceProductEnquery;
use Modules\User\Entities\UserFieldValue;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use App\Http\Traits\UploadImageTrait;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use Validator;
use DB;
use Cviebrock\EloquentSluggable\Services\SlugService;
use PDF;
use Storage;
use App\Http\Requests;
use App\Exports\MartketPlaceStats;
use Modules\Marketplace\Entities\MarketplaceMostViewedStores;
use Modules\Marketplace\Entities\Incoterms;
use App\Events\StoreRequest;

class StoreController extends CoreController
{
    use UploadImageTrait;
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

    /*
    * Check if store previously created
    *
    */
    public function checkIfStoreCreated()
    {
        try
        {
            $user = $this->user;
            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->count();
            
            if(!empty($myStore))
            {
                $checkIfStoreCreated = 1;
                $storeId = $myStore->marketplace_store_id;
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $storeLogo = $logoId->attachment_url;
                $base_url = $logoId->base_url;

                $storeName = $myStore->name;
            }
            else
            {   
                $checkIfStoreCreated = 0;
                $storeId = 0;
                $storeName = null;
                $storeLogo = null;
                $base_url = null;
            }
            
            return response()->json(['success' => $this->successStatus,
                                'is_store_created' => $checkIfStoreCreated,
                                'marketplace_store_id' => $storeId,
                                'product_count' => $productCount,
                                'name' => $storeName,
                                'logo_id' => $storeLogo,
                                'base_url' => $base_url
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    /*
     * Get Dashboard Screen
     * 
     */
    public function getDashboardScreen($filterType='')
    {
        try
        {
            $user = $this->user;
            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(!empty($myStore))
            {
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $bannerId = Attachment::where('id', $myStore->banner_id)->first();
                $myStore->logo_id = $logoId->attachment_url;
                $myStore->logo_base_url = $logoId->base_url;
                $myStore->banner_id = $bannerId->attachment_url;
                $myStore->banner_base_url = $logoId->base_url;

                $getAnalytics = $this->getAnalyticsByFilter($filterType, $myStore); 
                $productCount = MarketplaceProduct::where('user_id', $user->user_id)->count();              
                
                return response()->json(['success' => $this->successStatus,
                                        'banner' => $myStore->banner_id,
                                        'logo' => $myStore->logo_id,
                                        'logo_base_url' => $myStore->logo_base_url,
                                        'banner_base_url' => $myStore->banner_base_url,
                                        'product_counts' => $productCount,
                                        'total_product' => $getAnalytics[0],
                                        'total_category' => count($getAnalytics[1]),
                                        'total_reviews' => $getAnalytics[2],
                                        'total_enquiries' => $getAnalytics[3],
                                        //'data' => $myStore
                                    ],$this->successStatus); 
            }
            else
            {
                $message = "You have not setup your store yet!";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /**
    * get analytics by filter
    * 
    * */
    public function getAnalyticsByFilter($filterType, $myStore)
    {
        $user = $this->user;
        $returnedArray = [];
        $arrayValues = [];
        $enqueryCount = 0;

        if($filterType == 1)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->whereYear('created_at', date('Y'))->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->whereYear('created_at', date('Y'))
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->whereYear('created_at', date('Y'))->count();

            $enqueryCount = DB::table('marketplace_product_enqueries')
            ->join('marketplace_products', 'marketplace_product_enqueries.product_id', '=', 'marketplace_products.marketplace_product_id')
            ->where('marketplace_products.user_id',$user->user_id)
            ->whereYear('marketplace_product_enqueries.created_at', date('Y'))
            ->select('marketplace_product_enqueries.marketplace_product_enquery_id')
            ->count();

        }
        elseif($filterType == 2)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->whereMonth('created_at', date('m'))->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->whereMonth('created_at', date('m'))
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->whereMonth('created_at', date('m'))->count();

            $enqueryCount = DB::table('marketplace_product_enqueries')
            ->join('marketplace_products', 'marketplace_product_enqueries.product_id', '=', 'marketplace_products.marketplace_product_id')
            ->where('marketplace_products.user_id',$user->user_id)
            ->whereMonth('marketplace_product_enqueries.created_at', date('m'))
            ->select('marketplace_product_enqueries.marketplace_product_enquery_id')
            ->count();
        }
        elseif($filterType == 3)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();

            $enqueryCount = DB::table('marketplace_product_enqueries')
            ->join('marketplace_products', 'marketplace_product_enqueries.product_id', '=', 'marketplace_products.marketplace_product_id')
            ->where('marketplace_products.user_id',$user->user_id)
            ->whereBetween('marketplace_product_enqueries.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->select('marketplace_product_enqueries.marketplace_product_enquery_id')
            ->count();
        }
        elseif($filterType == 4)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->where('created_at','=', Carbon::yesterday())->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->where('created_at','=', Carbon::yesterday())
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->where('created_at','=', Carbon::yesterday())->count();

            $enqueryCount = DB::table('marketplace_product_enqueries')
            ->join('marketplace_products', 'marketplace_product_enqueries.product_id', '=', 'marketplace_products.marketplace_product_id')
            ->where('marketplace_products.user_id',$user->user_id)
            ->where('marketplace_product_enqueries.created_at','=', Carbon::yesterday())
            ->select('marketplace_product_enqueries.marketplace_product_enquery_id')
            ->count();
        }
        elseif($filterType == 5)
        {
            
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->whereDate('created_at', Carbon::today())->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->whereDate('created_at', Carbon::today())
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->whereDate('created_at', Carbon::today())->count();

            $enqueryCount = DB::table('marketplace_product_enqueries')
            ->join('marketplace_products', 'marketplace_product_enqueries.product_id', '=', 'marketplace_products.marketplace_product_id')
            ->where('marketplace_products.user_id',$user->user_id)
            ->whereDate('marketplace_product_enqueries.created_at', Carbon::today())
            ->select('marketplace_product_enqueries.marketplace_product_enquery_id')
            ->count();
        }
        else
        {
            $productCount = MarketplaceProduct::where('user_id', $user->user_id)->count();
            $fieldValues = DB::table('user_field_values')
                        ->where('user_id', $user->user_id)
                        ->where('user_field_id', 2)
                        ->get();
            $totalReviewCount = MarketplaceRating::where('type', '1')->where('id', $myStore->store_id)->count();
            $enqueryCount = DB::table('marketplace_product_enqueries')
            ->join('marketplace_products', 'marketplace_product_enqueries.product_id', '=', 'marketplace_products.marketplace_product_id')
            ->where('marketplace_products.user_id',$user->user_id)
            ->select('marketplace_product_enqueries.marketplace_product_enquery_id')
            ->count();
        }

        if(count($fieldValues) > 0)
        {
            foreach($fieldValues as $fieldValue)
            {
                $options = DB::table('user_field_options')
                        ->where('head', 0)->where('parent', 0)
                        ->where('user_field_option_id', $fieldValue->value)
                        ->first();
                
                if(!empty($options->option))
                $arrayValues[] = $options->option;
            }
        }

        $returnedArray = [$productCount, $arrayValues, $totalReviewCount, $enqueryCount];
        return $returnedArray;
    }


    /*
    * Get Store Prefilled values
    *
    */
    public function getPreFilledValues()
    {
        try
        {
            $user = $this->user;
            $userDetail = User::select('company_name','about','phone','email','website','address','lattitude','longitude','state','country_code')->with('state_data')->where('user_id', $user->user_id)->first();

            $fdaOptionId = UserFieldValue::select('value')
                                    ->where('user_id', $user->user_id)
                                    ->where('user_field_id', 39)
                                    ->first();
            if($fdaOptionId){ 
                $fdaOptios = DB::table('user_field_options')
                            ->where('user_field_option_id', $fdaOptionId->value)
                            ->first();
                if($fdaOptios){
                    $userDetail['fda_certified'] = $fdaOptios->option;
                }
            }
              
            if($userDetail && !empty($userDetail['state_data'])){

                $userDetail['state'] = $userDetail->state_data;
                $userDetail['state']->name  = $this->translate('messages.'.$userDetail->state_data->name,$userDetail->state_data->name);
            }

            $incoterms = Incoterms::orderBy('id','asc')->get();
            $userDetail['incoterms'] = $incoterms;
            return response()->json(['success' => $this->successStatus,
                                'data' => $userDetail,
                            ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }
    
    /*
     * Save Store Details
     * @Params $request
     */
    public function saveStoreDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                /*'name' => 'required|max:255', 
                'description' => 'required',
                'website' => 'required|max:255',*/
                //'store_region' => 'required',
                //'phone' =>  'required',
                //'location' => 'required|max:255',
                //'lattitude' => 'required|max:255',
                //'longitude' => 'required|max:255',
                'logo_id' => 'required',
                'banner_id' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            $userData = User::where('user_id', $user->user_id)->first();
            $myStore = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(empty($myStore))
            {
                $store = new MarketplaceStore;
                $store->user_id = $user->user_id;
                $store->package_id = $request->package_id;
                $store->incoterm_id = $request->incoterm_id;
                $store->incoterm_text = $request->incoterm_text;
                $store->logo_id = $this->uploadImage($request->file('logo_id'));
                $store->banner_id = $this->uploadImage($request->file('banner_id'));
                $store->save();

                $userDetail = MarketplaceStore::where('user_id', $user->user_id)->update(['description' => $userData->about, 'name' => $userData->company_name, 'slug' => SlugService::createSlug(MarketplaceStore::class, 'slug', $userData->company_name), 'website' => $userData->website, 'phone' => $userData->phone, 'location' => $userData->address, 'store_region' => $userData->state, 'lattitude' => $userData->lattitude, 'longitude' => $userData->longitude]);

                if(!empty($request->gallery_images) && count($request->gallery_images) > 0)
                {
                    foreach($request->gallery_images as $key=>$images)
                    {
                        $attachmentLinkId = $this->postGallery($images, $store->marketplace_store_id, 1, $key);
                    }
                }

                $createdStore = MarketplaceStore::with('logo_id')->where('user_id', $user->user_id)->first();

                return response()->json(['success'=>$this->successStatus,'data' => $createdStore],$this->successStatus); 
            }
            else
            {
                $message = "Your store has already been setup";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Get store details
     * @Params $request
     */
    public function getStoreDetails()
    {
        try
        {
            $user = $this->user;

            $myStore = MarketplaceStore::with('region:id,name')->where('user_id', $user->user_id)->first();
            if(!empty($myStore))
            {
                $userDetail = User::select('company_name','about','phone','email','website','address','lattitude','longitude','state','country_code')->with('state:id,name')->where('user_id', $user->user_id)->first();
                
                $getFDAoption =  DB::table('user_field_values')->select('value')
                                    ->where('user_id', $user->user_id)
                                    ->where('user_field_id', 39)
                                    ->first();
                if($getFDAoption){
                    $getFDAOptionValue = DB::table('user_field_options')->select('option')
                                            ->where('user_field_option_id', $getFDAoption->value)
                                            ->first();
                    if($getFDAOptionValue){
                        $userDetail->fda_certified = $getFDAOptionValue->option;
                    }
                }
                $myStore->prefilled = $userDetail;
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $bannerId = Attachment::where('id', $myStore->banner_id)->first();
                $myStore->logo_id = $logoId->attachment_url;
                $myStore->logo_base_url = $logoId->base_url;
                $myStore->banner_id = $bannerId->attachment_url;
                $myStore->banner_base_url = $bannerId->base_url;


                $avgRating = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->count();

                $oneStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 1)->count();
                $twoStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 2)->count();
                $threeStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 3)->count();
                $fourStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 4)->count();
                $fiveStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 5)->count();

                $isfavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $myStore->marketplace_store_id)->first();

                $myStore->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $myStore->total_reviews = $totalReviews;

                $myStore->total_one_star = $oneStar;
                $myStore->total_two_star = $twoStar;
                $myStore->total_three_star = $threeStar;
                $myStore->total_four_star = $fourStar;
                $myStore->total_five_star = $fiveStar;
                $myStore->is_favourite = (!empty($isfavourite)) ? 1 : 0;

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
                        $arrayValues[] = $options->option;
                    }
                }
                $myStore->total_category = count($arrayValues);

                $getLatestReview = MarketplaceRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('type', '1')->where('id', $myStore->marketplace_store_id)->orderBy('marketplace_review_rating_id', 'DESC')->first();

                
                $getLatestReviewCounts = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->count();

                $myStore->latest_review = $getLatestReview;
                if(!empty($getLatestReview))
                $myStore->latest_review->review_count = $getLatestReviewCounts;


                $galleries = MarketplaceStoreGallery::where('marketplace_store_id', $myStore->marketplace_store_id)->get();
                (count($galleries) > 0) ? $myStore->store_gallery = $galleries : $myStore->store_gallery = [];

                $storeProducts = MarketplaceProduct::with('product_gallery')->where('marketplace_store_id', $myStore->marketplace_store_id)->get();
                $incoterms = Incoterms::orderBy('id','asc')->get();
                return response()->json(['success'=>$this->successStatus,'data' =>$myStore, 'store_products' => $storeProducts,'incoterms'=>$incoterms],$this->successStatus); 
            }
            else
            {
                $message = "You have not setup your store yet!";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }


    /*
     * Get store details
     * @Params $request
     */
    public function getSellerProfile($storeId='')
    {
        try
        {
            $user = $this->user;

            $myStore = MarketplaceStore::where('marketplace_store_id', $storeId)->first();
            //return $myStore;
            if(!empty($myStore))
            {
                $userDetail = User::select('first_name', 'last_name','company_name','about','phone','email','website','address','role_id','lattitude','longitude','state','avatar_id')->with('avatar_id')->with('state:id,name')->where('user_id', $myStore->user_id)->first();
                //return $userDetail;
                
                $myStore->prefilled = $userDetail;
                $myStore->role_id = $userDetail->role_id;
                $logoId = Attachment::where('id', $myStore->logo_id)->first();
                $bannerId = Attachment::where('id', $myStore->banner_id)->first();
                $myStore->logo_id = $logoId->attachment_url;
                $myStore->logo_base_url = $logoId->base_url;
                $myStore->banner_id = $bannerId->attachment_url;
                $myStore->banner_base_url = $bannerId->base_url;


                $avgRating = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->avg('rating');
                $totalReviews = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->count();

                $oneStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 1)->count();
                $twoStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 2)->count();
                $threeStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 3)->count();
                $fourStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 4)->count();
                $fiveStar = MarketplaceRating::where('type', '1')->where('id', $myStore->marketplace_store_id)->where('rating', 5)->count();

                $isfavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $myStore->marketplace_store_id)->first();
                
                $myStore->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $myStore->total_reviews = $totalReviews;

                $myStore->total_one_star = $oneStar;
                $myStore->total_two_star = $twoStar;
                $myStore->total_three_star = $threeStar;
                $myStore->total_four_star = $fourStar;
                $myStore->total_five_star = $fiveStar;
                $myStore->is_favourite = (!empty($isfavourite)) ? 1 : 0;
                $arrayValues = array();
                $fieldValues = DB::table('user_field_values')
                            ->where('user_id', $myStore->user_id)
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
                        $arrayValues[] = $options->option;
                    }
                }
                $myStore->total_category = count($arrayValues);

                $getLatestReview = MarketplaceRating::with('user:user_id,first_name,last_name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('type', '1')->where('id', $myStore->marketplace_store_id)->orderBy('marketplace_review_rating_id', 'DESC')->first();
               
                if($getLatestReview){
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

                    $getLatestReview->user->name = $names;
                }
                $myStore->latest_review = $getLatestReview; 



                $galleries = MarketplaceStoreGallery::where('marketplace_store_id', $myStore->marketplace_store_id)->get();
                (count($galleries) > 0) ? $myStore->store_gallery = $galleries : $myStore->store_gallery = [];
                
                $storeProducts = MarketplaceProduct::with('product_gallery','getProductTax')->where('status','1')->where('marketplace_store_id', $myStore->marketplace_store_id)->get();
                foreach($storeProducts as $key => $storeProduct)
                {
                    $avgRatingStoreProducts = MarketplaceRating::where('type', '2')->where('id', $storeProduct->marketplace_product_id)->avg('rating');
                    $totalReviewsStoreProducts = MarketplaceRating::where('type', '2')->where('id', $storeProduct->marketplace_product_id)->count();

                    $storeProducts[$key]->avg_rating = number_format((float)$avgRatingStoreProducts, 1, '.', '');
                    $storeProducts[$key]->total_reviews = $totalReviewsStoreProducts;
                    
                }
                $mostViewedStore = MarketplaceMostViewedStores::where('store_id', $storeId)->where('user_id', $user->user_id)->first();
                if($mostViewedStore){
                    $totalCountViewed = $mostViewedStore->viewed_count + 1;
                    $userDetail = MarketplaceMostViewedStores::where('id', $mostViewedStore->id)->update(['viewed_count' => $totalCountViewed]);
                }
                else{
                    $saveMostViewed = new MarketplaceMostViewedStores;
                    $saveMostViewed->store_id = $storeId;
                    $saveMostViewed->user_id = $user->user_id;
                    $saveMostViewed->viewed_count = 1;
                    $saveMostViewed->created_at = now();
                    $saveMostViewed->updated_at = now();
                    $saveMostViewed->save();
                }
                return response()->json(['success'=>$this->successStatus,'data' =>$myStore, 'store_products' => $storeProducts],$this->successStatus); 
            }
            else
            {
                $message = "Store not availabel!";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Update Store Details
     * @Params $request
     */
    public function updateStoreDetails(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                /*'name' => 'required|max:255', 
                'description' => 'required',
                'website' => 'required|max:255',*/
                //'store_region' => 'required',
                //'phone' =>  'required',
                //'location' => 'required|max:255',
                //'lattitude' => 'required|max:255',
                //'longitude' => 'required|max:255'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            $store = MarketplaceStore::where('user_id', $user->user_id)->first();
            if(!empty($store))
            {
                $store->name = $request->name;
                $store->description = $request->description;
                $store->website = $request->website;
                $store->phone = $request->phone;
                $store->store_region = $request->store_region;
                $store->location = $request->location;
                $store->incoterm_id = $request->incoterm_id;
                $store->incoterm_text = $request->incoterm_text;
                $store->lattitude = $request->lattitude;
                $store->longitude = $request->longitude;

                if(!empty($request->file('logo_id')))
                {
                    $this->deleteAttachment($store->logo_id);
                    $store->logo_id = $this->uploadFrontImage($request->file('logo_id'));    
                }
                if(!empty($request->file('banner_id')))
                {
                    $this->deleteAttachment($store->banner_id);
                    $store->banner_id = $this->uploadFrontImage($request->file('banner_id'));    
                }
                $store->save();

                $userData = User::where('user_id', $user->user_id)->first();
                $userDetail = MarketplaceStore::where('user_id', $user->user_id)->update(['description' => $userData->about, 'name' => $userData->company_name, 'website' => $userData->website, 'phone' => $userData->phone, 'location' => $userData->address, 'store_region' => $userData->state, 'lattitude' => $userData->lattitude, 'longitude' => $userData->longitude]);

                $existingGalleries = MarketplaceStoreGallery::where('marketplace_store_id', $store->marketplace_store_id)->get();
                /*if(count($existingGalleries) > 0)
                {
                    foreach($existingGalleries as $existingGallery)
                    {
                        unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$existingGallery->attachment_url);
                        MarketplaceStoreGallery::where('marketplace_store_gallery_id',$existingGallery->marketplace_store_gallery_id)->delete();
                    }
                }*/
                

                //$userDetail = User::where('user_id', $user->user_id)->update(['about' => $request->description, 'company_name' => $request->name, 'website' => $request->website, 'phone' => $request->phone, 'address' => $request->location]);

                if(!empty($request->gallery_images) && count($request->gallery_images) > 0)
                {
                    foreach($request->gallery_images as $key=>$images)
                    {
                        $attachmentLinkId = $this->postGallery($images, $store->marketplace_store_id, 1, $key);
                    }
                }
                $galleries = MarketplaceStoreGallery::where('marketplace_store_id', $store->marketplace_store_id)->get();
                (count($galleries) > 0) ? $store->store_gallery = $galleries : $store->store_gallery = [];

                return response()->json(['success'=>$this->successStatus,'data' =>$store],$this->successStatus); 
            }
            else
            {
                $message = "This store is not valid";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
    * Delete Gallery
    *
    */
    public function deleteGalleryImage(Request $request)
    {
        try
        {
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'gallery_type' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            if($request->gallery_type == 1)
            {
                $validator = Validator::make($request->all(), [ 
                    'marketplace_store_gallery_id' => 'required'
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                $myStoreGallery = MarketplaceStoreGallery::where('marketplace_store_gallery_id', $request->marketplace_store_gallery_id)->first();
                if(!empty($myStoreGallery))
                {
                    //unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$myStoreGallery->attachment_url);
                    MarketplaceStoreGallery::where('marketplace_store_gallery_id',$request->marketplace_store_gallery_id)->delete();

                    return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'.'Deleted successfully','Deleted successfully')
                                            ], $this->successStatus);
                }
                else
                {
                    $message = "This gallery image is not valid";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }

            }
            elseif($request->gallery_type == 2)
            {   
                $validator = Validator::make($request->all(), [ 
                    'marketplace_product_gallery_id' => 'required'
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                $myProductGallery = MarketplaceProductGallery::where('marketplace_product_gallery_id', $request->marketplace_product_gallery_id)->first();
                if(!empty($myProductGallery))
                {
                    //unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$myProductGallery->attachment_url);
                    MarketplaceProductGallery::where('marketplace_product_gallery_id',$request->marketplace_product_gallery_id)->delete();
                    
                    return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'.'Deleted successfully','Deleted successfully')
                                            ], $this->successStatus);
                }
                else
                {
                    $message = "This gallery image is not valid";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }

            }
            else
            {
                $message = "This gallery type is not valid";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    /*
    * Check Producer Store status
    */
    public function checkStoreStatus(){
        try
        {
            $user = $this->user;
            $userDetail = User::select('role_id')->where('user_id', $user->user_id)->first();

            if(!empty($userDetail)){

                if($userDetail->role_id == 3){

                    $myStore = MarketplaceStore::select('status')->where('user_id', $user->user_id)->first();
                    
                    if(!empty($myStore)){

                        if($myStore->status == 0){
                            
                            return response()->json(['success' => $this->successStatus,
                                'data' => 0,
                                'message' => 'Store status is pending'
                            ], $this->successStatus);

                        }else if($myStore->status == 1){

                            return response()->json(['success' => $this->successStatus,
                                'data' => 1,
                                'message' => 'Store status is approved'
                            ], $this->successStatus);
                        }else{
                            return response()->json(['success' => $this->successStatus,
                                'data' => 2,
                                'message' => 'Store status is disabled'
                            ], $this->successStatus);
                        }


                    }else{

                        $message = "you have not created any store";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }

                }else{

                    $message = "Only Producer can check store status";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }

            }else{

                $message = "Invalid User";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }


    public function downloadMarketPlaceAnalyst($filterType){
        try
        {
            $user = $this->user;
            
            // $data = [
            //     'title' => $filterType,
            //     'date' => date('m/d/Y')
            // ];
            // $date = date("Y/m");
            // $pdf = PDF::loadView('user::pdf', $data);
            // $pdfName = rand()."".round(microtime(true)).".pdf";
            // Storage::put('public/pdf/'.$date.'/'.$pdfName, $pdf->output());
            // $path = asset('storage/pdf/'.$date.'/'.$pdfName);
            //dd($path);
            // $file_path = public_path('files/'.$file_name);
            // return response()->download($file_path);
            $productCount = MarketplaceProduct::leftJoin('marketplace_review_ratings', 'marketplace_products.marketplace_product_id', '=', 'marketplace_review_ratings.id')->select('marketplace_products.title','marketplace_products.product_category_id','marketplace_products.marketplace_product_id','marketplace_products.user_id',DB::raw('avg(marketplace_review_ratings.rating) as avg_rating'),DB::raw('count(marketplace_review_ratings.marketplace_review_rating_id) as rating_count'))->where('marketplace_products.user_id', 2148)->groupBy('marketplace_products.marketplace_product_id')->orderBy('avg_rating','desc')->get(); 

            if($productCount){
                foreach($productCount as $key=>$product){
                    $productCount[$key]->avg_rating = number_format((float)$product->avg_rating, 1, '.', '');
                    $productLikes = MarketplaceFavourite::where('id',$product->marketplace_product_id)->where('favourite_type','2')->count();
                    $productCount[$key]->total_reviews = $productLikes;

                    //Get Product Category
                    $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $product->product_category_id)
                            ->first();

                    $productCount[$key]->category_name = ($options->option) ?  $options->option : "";

                    //Get Prodct Enquiry
                    $importerUserNames = [];
                    $importerUserEmails = [];
                    $enqueries = MarketplaceProductEnquery::select(['user_id'])->where('product_id',$product->marketplace_product_id)->get();

                    if(!empty($enqueries)){
                        foreach($enqueries as $enquery){
                            $user = User::select(['name','email'])->where('user_id',$enquery->user_id)->first();
                            $importerUsers[] = $user->name;
                            $importerUserEmails[] = $user->email;
                        }
                    }

                    $productCount[$key]->importer_names = implode(',', $importerUserNames);
                    $productCount[$key]->importer_emails = implode(',', $importerUserEmails);
                    $productCount[$key]->total_enquiries = count($enqueries);
                    
                }
            }
            
            $fileName = 'Stats.csv';
           
                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$fileName",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );

                $columns = array('Title', 'Category Name', 'Total Reviews', 'Avg  Rating', 'Total enqueries','Importer Name','Importer Emails');

                $callback = function() use($productCount, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);

                    foreach ($productCount as $task) {
                        fputcsv($file, array($task->title, $task->category_name, $task->total_reviews, $task->avg_rating, $task->total_enquiries,$task->importer_names,$task->importer_emails));
                    }

                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            //return response()->json(['success' => $this->successStatus,
                            //     'link' => $productCount,
                            // ], $this->successStatus);
        catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    /*
    * Get Store category data
    */
    public function getStoreCategoryData($storeid, $categoryId=''){
        try
        {
            $storeUser = MarketplaceStore::select('user_id')->where('marketplace_store_id',$storeid)->first();
            $arrayValues = array();
            if($categoryId == ''){
                $fieldValues = DB::table('user_field_values')
                            ->where('user_id', $storeUser->user_id)
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
                        if(!empty($options->option)){
                            $products = MarketplaceProduct::where('marketplace_store_id',$storeid)->where('status','1')->where('product_category_id',$options->user_field_option_id)->get();
                            if(count($products) > 0){
                                foreach($products as $key=>$product){
                                    $galleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->get();
                                    $products[$key]->product_gallery = $galleries;
                                    $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->avg('rating');
                                    $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();

                                    $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                                    $products[$key]->total_reviews = $totalReviews;
                                }
                            }
                            $arrayValues[] = ['category_id'=>$options->user_field_option_id, 'name' => $this->translate('messages.'.$options->option,$options->option), 'products'=>$products];
                        }

                    }
                    if(!empty($arrayValues)){
                        array_multisort(array_column( $arrayValues, 'name' ), SORT_ASC, $arrayValues);
                    }
                    
                }

            }
            else{
                $options = DB::table('user_field_options')
                                ->where('head', 0)->where('parent', 0)
                                ->where('user_field_option_id', $categoryId)
                                ->first();
                        
                //$arrayValues[] = $options->option;
                if(!empty($options->option)){
                    $products = MarketplaceProduct::where('marketplace_store_id',$storeid)->where('status','1')->where('product_category_id',$options->user_field_option_id)->get();
                    if(count($products) > 0){
                        foreach($products as $key=>$product){
                            $galleries = MarketplaceProductGallery::where('marketplace_product_id', $product->marketplace_product_id)->get();
                            $products[$key]->product_gallery = $galleries;
                            $avgRating = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->avg('rating');
                            $totalReviews = MarketplaceRating::where('type', '2')->where('id', $product->marketplace_product_id)->count();

                            $products[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                            $products[$key]->total_reviews = $totalReviews;
                        }
                    }
                    $arrayValues[] = ['category_id'=>$options->user_field_option_id, 'name' => $this->translate('messages.'.$options->option,$options->option), 'products'=>$products];
                }
                if(!empty($arrayValues)){
                    array_multisort(array_column( $arrayValues, 'name' ), SORT_ASC, $arrayValues);
                }
            }
            return response()->json(['success'=>$this->successStatus,'data' =>$arrayValues],$this->successStatus); 
        }
        catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }
}
