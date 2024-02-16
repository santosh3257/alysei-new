<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\BlockList; 
use Modules\User\Entities\UserSelectedHub; 
use Modules\User\Entities\Hub;
use Modules\User\Entities\State;
use Modules\User\Entities\UserField;
use Modules\User\Entities\UserFieldValue;
use Modules\User\Entities\UserFieldOption;
use Modules\User\Entities\Event;
use Modules\User\Entities\Trip;
use Modules\User\Entities\Blog;
use Modules\User\Entities\Award;
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\FeaturedListing;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\UserPrivacy;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\Follower;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Modules\User\Entities\Role;
use Carbon\Carbon;
use DB;
use App\Attachment;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Modules\User\Entities\ConnectionRequestHubs;
//use App\Events\UserRegisterEvent;

class SearchController extends CoreController
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
     * Get Featured Type Using Role Id
     * @params $roleId
     */
    public function getFeaturedListingTypes($roleId){
        $featuredTypes = DB::table("featured_listing_types as flt")
            ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

            ->where("fltrm.role_id","=",$roleId)
            ->get();

        return $featuredTypes;
    }


    /*
     * Search user and hubs
     *
     */
    public function search(Request $request)
    {
        // try
        // {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'search_type' => 'required' 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->search_type == 1)
            {
                $validateSearchType = Validator::make($request->all(), [ 
                    'keyword' => 'required' 
                ]);

                if ($validateSearchType->fails()) { 
                    return response()->json(['errors'=>$validateSearchType->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                return $this->globalSearch($request->keyword);   
            }
            elseif($request->search_type == 2)
            {
                $validateSearchType = Validator::make($request->all(), [ 
                    'role_id' => 'required' 
                ]);

                if ($validateSearchType->fails()) { 
                    return response()->json(['errors'=>$validateSearchType->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }
                return $this->searchUserByRoles($request->role_id, $request, $user->user_id);
            }
            elseif($request->search_type == 3)
            {
                return $this->searchUserByHubs($request, $user->user_id);
            }
            else
            {
                $message = "Invalid search type";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        // }
        // catch(\Exception $e)
        // {
        //     return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        // }
    }

    /*
    * Search users by hubs
    */
    public function searchUserByHubs($request, $myId)
    {
        $hubsArray = array();
        $condition = '';
        $isSearch = 0;
        
        if(!empty($request->keyword))
        {
            $isSearch = 1;
            $hubs = Hub::where('title', 'LIKE', '%' . $request->keyword . '%')->get();
            if(count($hubs) > 0)
            {
                foreach($hubs as $hub)
                {
                    array_push($hubsArray, $hub->id);
                }
            }
            if(count($hubsArray) > 0)
            {
                if($condition != '')
                $condition .=" and hubs.title LIKE "."'%".$request->keyword."%'"."";
                else
                $condition .="hubs.title LIKE "."'%".$request->keyword."%'"."";
            }
        }
        if(!empty($request->state))
        {
            $isSearch = 1;
            $hubs = explode(",", $request->state);
            $hubsByStates = Hub::whereIn('state_id', $hubs)->get();
            if(count($hubsByStates) > 0)
            {
                if($condition != '')
                $condition .=" and hubs.state_id in(".$request->state.")";
                else
                $condition .="hubs.state_id in(".$request->state.")";
            }
            /*$hubsByState = Hub::where('state_id', $request->state)->first();
            if(!empty($hubsByState))
            {
                if($condition != '')
                $condition .=" and hubs.state_id = ".$hubsByState->state_id."";
                else
                $condition .="hubs.state_id = ".$hubsByState->state_id."";
                array_push($hubsArray, $hubsByState->id);
            }*/
            
        }

        if($isSearch == 0)
        {
            $myHubsArray = [];
            $myHubs = UserSelectedHub::where('user_id', $myId)->get();
            if(count($myHubs) > 0)
            {
                $myHubsArray = $myHubs->pluck('hub_id');
                $hubs = Hub::with('image:id,attachment_url,base_url','country:id,name','state:id,name')->whereIn('id', $myHubsArray)->where('status', '1')->paginate(100);
                if($hubs){
                    foreach($hubs as $key=>$hub){
                        $hubs[$key]->is_selected = true;
                    }
                }
            }
            else
            {
                $hubs = Hub::with('image:id,attachment_url,base_url','country:id,name','state:id,name')->where('status', '1')->paginate(100);
            }
            
        }
        else
        {
            if($condition != '')
            {
                $myHubs = UserSelectedHub::where('user_id',$this->user->user_id)->get();
                $hubs = Hub::with('image:id,attachment_url,base_url','country:id,name','state:id,name')->whereRaw('('.$condition.')')->where('status', '1')->paginate(100);   
                if(!empty($myHubs)){
                   $myHubsArray = $myHubs->pluck('hub_id')->toArray();
                    if($hubs){
                        foreach($hubs as $key=>$hub){
                            if(in_array($hub->id, $myHubsArray)){
                                $hubs[$key]->is_selected = true;
                            }else{
                                $hubs[$key]->is_selected = false; 
                            }
                        }
                    }
                } 
            }
            else
            {
                $message = "No hubs found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }

        
        if(count($hubs) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                'data' => $hubs
                                ], $this->successStatus);
        }
        else
        {
            $message = "No hubs found";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
    /*Subscribe/Unsubscribe hub
    */
    public function subscribeOrUnsubscribeHub(Request $request)
    {
        try
        {
            $user = $this->user;   
            $validateData = Validator::make($request->all(), [ 
                'hub_id' => 'required', 
                'subscription_type' => 'required'  // 1 = subscribe, 0 = unsubscribe
            ]);

            if ($validateData->fails()) { 
                return response()->json(['errors'=>$validateData->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $hub = Hub::where('id', $request->hub_id)->first();
            if(!empty($hub))
            {
                $isSubscribedWithHub = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $request->hub_id)->first();
                if($request->subscription_type == 1)
                {
                    if(!empty($isSubscribedWithHub))
                    {
                        $message = "You have already subscribed to this hub";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {
                        $selectedHub = new UserSelectedHub;
                        $selectedHub->user_id = $user->user_id;
                        $selectedHub->hub_id = $request->hub_id;
                        $selectedHub->save();

                        $message = "You have subscribed to this hub";
                        return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                    }
                }
                elseif($request->subscription_type == 0)
                {
                    if(!empty($isSubscribedWithHub))
                    {
                        $unsubscribeWithHub = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $request->hub_id)->delete();
                        if($unsubscribeWithHub == 1)
                        {
                            $message = "You have unsubscribed from this hub";
                            return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first subscribe to this hub";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You have to first subscribe to this hub";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid subscription type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            else
            {
                $message = "Invalid hub id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*Get states list
    */
    public function getStates(Request $request)
    {
        try
        {
            $user = $this->user;   
            $userCountry = User::where('user_id', $user->user_id)->first();         
            
            if(!empty($request) && $request->param == 'usa')
            {
                $states = State::select('id','name','country_id')->where('country_id', 233)->where('status', '1')->orderBy('name','ASC')->get();
            }
            else
            {
                $states = State::select('id','name','country_id')->where('country_id', $userCountry->country_id)->where('status', '1')->orderBy('name','ASC')->get();
            }
            
            if(count($states) > 0)
            {
                foreach($states as $key => $state)
                {
                    $states[$key]->name = $this->translate('messages.'.$state->name,$state->name);
                }
                return response()->json(['success' => $this->successStatus,
                                    'data' => $states
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No states found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*Get roles by hub
    */
    public function getRolesByHub($hubId)
    {
        try
        {
            $user = $this->user;   

            $isHubSelected = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $hubId)->first();
            if(!empty($isHubSelected))
            {
                $subscription = 1;
            }
            else
            {
                $subscription = 0;
            }
            
            $hub = Hub::where('id', $hubId)->first();         
            if(!empty($hub))
            {
                $users = UserSelectedHub::where('hub_id', $hubId)->get();
                $hubSelectedUsers = array();
                if(count($users) > 0)
                {
                    $users = $users->pluck('user_id');
                    
                    foreach($users as $key=>$roleUser){
                        $profileMode = User::select('who_can_view_profile')->where('user_id',$roleUser)->first();
                        if($profileMode->who_can_view_profile == 'anyone'){
                            array_push($hubSelectedUsers, $roleUser);
                        }
                        elseif($profileMode->who_can_view_profile == 'followers'){
                            $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $user->user_id)->pluck('poster_id');
                            if($myFollowers){
                                array_push($hubSelectedUsers, $roleUser);
                            }
                        }
                        elseif($profileMode->who_can_view_profile == 'connections'){
                            // Get user connections
                            $requestedConnection = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');
                            $getRequestedConnection = Connection::select('*','resource_id as poster_id')->where('user_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');
    
                            $merged = $requestedConnection->merge($getRequestedConnection);
                            $myConnections = $merged->all();
    
    
                            if(!empty($myConnections)){
                                array_push($hubSelectedUsers, $roleUser);
                            }
                        }
                        elseif($profileMode->who_can_view_profile == 'justme'){
                             if (in_array($user->user_id, $users)){
                                    array_push($hubSelectedUsers, $user->user_id);
                                }
                        }
    
                    }
                    $hubSelectedUsers = array_unique($hubSelectedUsers);
                }
    
                    
                    if($user->role_id != 10)
                    {
                        $roles = Role::select('role_id','name','slug','image_id')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->with("attachment")->orderBy('order')->get();
                    }
                    else
                    {
                        $roles = Role::select('role_id','name','slug','image_id')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->with("attachment")->orderBy('order')->get();
                    }
                     

                    foreach($roles as $key => $role)
                    {
                        if($roles[$key]->slug == "Importer_and_Distributer")
                        {
                            $roles[$key]->name = $this->translate('messages.'.'Importers & Distributors','Importers & Distributors');
                        }

                        if($roles[$key]->name == "Italian Restaurants in US")
                        {
                            $roles[$key]->name = $this->translate('messages.'.'Italian Restaurants','Italian Restaurants');
                        }

                        $roles[$key]->name = $this->translate('messages.'.$role->name,$role->name);
                        $roles[$key]->image = "public/images/roles/".$role->slug.".jpg";

                        /*$userWithRole = User::whereHas(
                            'roles', function($q) use ($role){
                                $q->where('slug', $role->slug);
                            }
                        )->whereIn('user_id', $users)->count();*/
                        if($role->role_id == 6)
                        {
                            $userWithRole = User::whereHas(
                            'roles', function($q) use ($role){
                                $q->where('role_id', 4)
                                ->orwhere('role_id', 5)
                                ->orwhere('role_id', 6);
                                }
                            )->whereIn('user_id', $hubSelectedUsers)
                            ->where('profile_percentage', 100)
                            ->count();
                        }
                        else
                        {
                            $userWithRole = User::whereHas(
                            'roles', function($q) use ($role){
                                $q->where('slug', $role->slug);
                                }
                            )->whereIn('user_id', $hubSelectedUsers)
                            ->where('profile_percentage', 100)->count();
                        }

                        $roles[$key]->user_count = $userWithRole;
                    }

                    return response()->json(['success' => $this->successStatus,
                                    'is_subscribed_with_hub' => $subscription,
                                    'data' => $roles
                                    ], $this->successStatus);
                
            }
            else
            {
                $message = "Invalid hub";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*Get User list by roles
    */
    public function getUserInCurrentRole(Request $request)
    {
        try
        {
            $user = $this->user;   
            $newArr = [];
            $validateSearchType = Validator::make($request->all(), [ 
                'hub_id' => 'required', 
                'role_id' => 'required' 
            ]);

            if ($validateSearchType->fails()) { 
                return response()->json(['errors'=>$validateSearchType->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $users = UserSelectedHub::where('hub_id', $request->hub_id)->get();
            if(count($users) > 0)
            {
                $users = $users->pluck('user_id')->toArray();
                $hubSelectedUsers = array();
              
                foreach($users as $key=>$roleUser){
                    $profileMode = User::select('who_can_view_profile')->where('user_id',$roleUser)->first();
                    if($profileMode->who_can_view_profile == 'anyone'){
                        array_push($hubSelectedUsers, $roleUser);
                    }
                    elseif($profileMode->who_can_view_profile == 'followers'){
                        $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $user->user_id)->pluck('poster_id');
                        if($myFollowers){
                            array_push($hubSelectedUsers, $roleUser);
                        }
                    }
                    elseif($profileMode->who_can_view_profile == 'connections'){
                        // Get user connections
                        $requestedConnection = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');
                        $getRequestedConnection = Connection::select('*','resource_id as poster_id')->where('user_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');

                        $merged = $requestedConnection->merge($getRequestedConnection);
                        $myConnections = $merged->all();


                        if(!empty($myConnections)){
                            array_push($hubSelectedUsers, $roleUser);
                        }
                    }
                    elseif($profileMode->who_can_view_profile == 'justme'){
                         if (in_array($user->user_id, $users)){
                                array_push($hubSelectedUsers, $user->user_id);
                            }
                    }
                    else{
                        if (in_array($user->user_id, $users)){
                                array_push($hubSelectedUsers, $user->user_id);
                            }
                    }

                }

                $hubSelectedUsers = array_unique($hubSelectedUsers);

                

                // return response()->json(['success' => $this->successStatus,
                //                 'data' => $hubSelectedUsers
                //                 ], $this->successStatus);
                

                /*$blockList = BlockList::where('user_id', $user->user_id)->whereIn('block_user_id', $users)->get();
                if(count($blockList) > 0)
                {
                    $blockUsers = $blockList->pluck('block_user_id');
                }*/

                if($request->role_id == 6 || $request->role_id == 4 || $request->role_id == 5)
                {
                    //$userWithRole = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->with('avatar_id')->where('user_id', '!=', $user->user_id)->whereIn('user_id', $users)->whereIn('role_id', [4,5,6])->where('profile_percentage', 100)->orderBy('user_id', 'DESC')->get();

                    $userWithRole = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->with('avatar_id')->whereIn('user_id', $hubSelectedUsers)->whereIn('role_id', [4,5,6])->where('profile_percentage', 100)->orderBy('company_name')->orderBy('name')->orderBy('first_name')->paginate(12);
                }
                else
                {
                    //$userWithRole = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->with('avatar_id')->where('user_id', '!=', $user->user_id)->where('role_id', $request->role_id)->whereIn('user_id', $users)->where('profile_percentage', 100)->orderBy('user_id', 'DESC')->get();

                    $userWithRole = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->with('avatar_id')->where('role_id', $request->role_id)->whereIn('user_id', $hubSelectedUsers)->where('profile_percentage', 100)->orderBy('company_name')->orderBy('name')->orderBy('first_name')->paginate(12);
                    
                }
                
                foreach($userWithRole as $key => $getUser)
                {
                    $followerCount = Follower::where('follow_user_id', $getUser->user_id)->count();  
                    $userWithRole[$key]->follower_count = $followerCount;                
                }
                
                    
                return response()->json(['success' => $this->successStatus,
                                'count' => count($userWithRole),
                                'data' => $userWithRole
                                ], $this->successStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*
    * Search user by roles
    */
    public function searchUserByRoles($roleId, $request, $myId)
    {
        $usersArray = array();
        $searchKeyCount = array();
        // Check Hub is exist in a request
        if(!empty($request->hub_id))
        {
            $hubs = explode(",", $request->hub_id);
            $selectedHubs = UserSelectedHub::whereIn('hub_id', $hubs)->groupBy('user_id')->get();
            if(count($selectedHubs))
            {
                $selectedHubs = $selectedHubs->pluck('user_id');
                //$hubArray = array();
                foreach($selectedHubs as $selectedHub)
                {
                    array_push($usersArray, $selectedHub);
                }
                
            }
            array_push($searchKeyCount, 1);
        }
        // Check country is exist in a request
        if(!empty($request->country) && $request->country > -1)
        {
            $countries = UserFieldValue::where('value', $request->country)->where('user_field_id', 13)->groupBy('user_id')->get();
            if(count($countries))
            {
                $countries = $countries->pluck('user_id');
                //$countryArray = array();
                foreach($countries as $selectedCountry)
                {
                    array_push($usersArray, $selectedCountry);
                }
               
            }
            array_push($searchKeyCount, 2);
        }

        // Check region is exist in a request
        if(!empty($request->region))
        {
            $explodeRegion = explode(',',$request->region);
            $regions = UserFieldValue::whereIn('value', $explodeRegion)->where('user_field_id', 28)->groupBy('user_id')->get();
            if(count($regions))
            {
                $regions = $regions->pluck('user_id');
                //$regionArray = array();
                foreach($regions as $selectedRegion)
                {
                    array_push($usersArray, $selectedRegion);
                }
               
            }
            array_push($searchKeyCount, 3);
        }

        // Check product type is exist in a request
        if(!empty($request->product_type))
        {
            $productTypeArray = explode(",", $request->product_type);
            $optionsTypes = UserFieldOption::select('user_field_option_id','parent')->whereIn('user_field_option_id', $productTypeArray)->where('user_field_id', 2)->get();
            $parentProductType = [];
            $methodProductType = [];
            $propertyProductType = [];
            foreach($optionsTypes as $optionKey => $optionValue){

                if($optionValue->parent == 0){
                    $parentProductType[] = $optionValue->user_field_option_id;
                }else{
                    $methodProp = UserFieldOption::select('user_field_option_id','parent','option','optionType')->where('user_field_option_id', $optionValue->parent)->first();
                    
                    if($methodProp && $methodProp->optionType == "conservation"){
                        $methodProductType[] = $optionValue->user_field_option_id;       
                    }else{
                        $propertyProductType[] = $optionValue->user_field_option_id;       
                    }
                    
                }
            }
            if(!empty($parentProductType)){
                $productTypes = UserFieldValue::whereIn('value', $parentProductType)->where('user_field_id', 2)->groupBy('user_id')->get();
                if($productTypes)
                {
                    $productTypes = $productTypes->pluck('user_id');
                    //$productTypeArray = array();
                    foreach($productTypes as $productType)
                    {
                        array_push($usersArray, $productType);
                    }
                }
                array_push($searchKeyCount, 4);
            }

            if(!empty($methodProductType)){
                
                $productTypes = UserFieldValue::whereIn('value', $methodProductType)->where('user_field_id', 2)->groupBy('user_id')->get();
                if($productTypes)
                {
                    $productTypes = $productTypes->pluck('user_id');
                    $methodProductTypeArray = array();
                    foreach($productTypes as $productType)
                    {
                        array_push($usersArray, $productType);
                    }
                    
                }
                array_push($searchKeyCount, 5);
            }
            if(!empty($propertyProductType)){
                
                $productTypes = UserFieldValue::whereIn('value', $propertyProductType)->where('user_field_id', 2)->groupBy('user_id')->get();
                if($productTypes)
                {
                    $productTypes = $productTypes->pluck('user_id');
                    $propertyProductTypeArray = array();
                    foreach($productTypes as $productType)
                    {
                        array_push($usersArray, $productType);
                    }
                }
                array_push($searchKeyCount, 6);
            }
        }

        // Check horeca is exist in a request
        if(!empty($request->horeca))
        {
            $horeca_val='';
            if($request->horeca == 'yes'){
                $horeca_val= 621;

            }
            if($request->horeca == 'no'){
                $horeca_val= 622;
            }
            $horeca = UserFieldValue::where('value', $horeca_val)->where('user_field_id', 4)->groupBy('user_id')->get();
            if(count($horeca))
            {
                $horeca = $horeca->pluck('user_id');
                $horecaArray = array();
                foreach($horeca as $horecaUsers)
                {
                    array_push($usersArray, $horecaUsers);
                }
                
            }
            array_push($searchKeyCount, 7);
        }

        // Check private label is exist in a request
        if(!empty($request->private_label))
        {
            
            $privateLabels = UserFieldValue::where('value', $request->private_label)->where('user_field_id', 5)->groupBy('user_id')->get();
            if(count($privateLabels))
            {
                $privateLabels = $privateLabels->pluck('user_id');
                $privateLabelArray = array();
                foreach($privateLabels as $privateLabel)
                {
                    array_push($usersArray, $privateLabel);
                }
                
            }
            array_push($searchKeyCount, 8);
        }

        // Check alysei brand label is exist in a request
        if(!empty($request->alysei_brand_label))
        {
            $brandLabels_val = $request->alysei_brand_label;
            $alysei_brand_label_val='';
            if($request->alysei_brand_label == 'yes'){
                $alysei_brand_label_val= 625;

            }
            if($request->alysei_brand_label == 'no'){
                $alysei_brand_label_val= 626;
            }
            $brandLabels = UserFieldValue::where('value', $alysei_brand_label_val)->where('user_field_id', 6)->groupBy('user_id')->get();
            
            if(count($brandLabels))
            {
                $brandLabels = $brandLabels->pluck('user_id')->toArray();
                $brandLabelsArray = array();
                foreach($brandLabels as $brandLabel)
                {
                    array_push($usersArray, $brandLabel);
                }
                
            }
            array_push($searchKeyCount, 9);
            
        }

        // Check pickup is exist in a request
        if(!empty($request->pickup))
        {
            $pickUps = UserFieldValue::where('value', $request->pickup)->where('user_field_id', 9)->groupBy('user_id')->get();
            if(count($pickUps))
            {
                $pickUps = $pickUps->pluck('user_id');
                $pickUpsArray = array();
                foreach($pickUps as $pickUp)
                {
                    array_push($usersArray, $pickUp);
                }
               
            }
            array_push($searchKeyCount, 10);
            
        }

        // Check pickup discount is exist in a request
        if(!empty($request->pickupdiscount))
        {
            $pickUpDiscounts = UserFieldValue::where('value', $request->pickupdiscount)->where('user_field_id', 21)->groupBy('user_id')->get();
            if(count($pickUpDiscounts))
            {
                $pickUpDiscounts = $pickUpDiscounts->pluck('user_id');
                $pickUpDiscountsArray = array();
                foreach($pickUpDiscounts as $pickUpDiscount)
                {
                    array_push($usersArray, $pickUpDiscount);
                }
               
            }
            array_push($searchKeyCount, 11);
        }

        // Check dilivery is exist in a request
        if(!empty($request->delivery))
        {
            $deleveries = UserFieldValue::where('value', $request->delivery)->where('user_field_id', 9)->groupBy('user_id')->get();
            if(count($deleveries))
            {
                $deleveries = $deleveries->pluck('user_id');
                $deleveriesArray = array();
                foreach($deleveries as $delevery)
                {
                    array_push($usersArray, $delevery);
                }
                
            }
            array_push($searchKeyCount, 12);
        }

        // Check dilivery discount is exist in a request
        if(!empty($request->delivery_discount))
        {
            $deleveryDiscounts = UserFieldValue::where('value', $request->delivery_discount)->where('user_field_id', 22)->groupBy('user_id')->get();
            if(count($deleveryDiscounts))
            {
                $deleveryDiscounts = $deleveryDiscounts->pluck('user_id');
                $deleveryDiscountsArray = array();
                foreach($deleveryDiscounts as $deleveryDiscount)
                {
                    array_push($usersArray, $deleveryDiscount);
                }
                
            }
            array_push($searchKeyCount, 13);
            
        }

        // Check expertise is exist in a request
        if(!empty($request->expertise))
        {
            $expertis = explode(",", $request->expertise);
            $userExpertise = UserFieldValue::whereIn('value', $expertis)->where('user_field_id', 11)->groupBy('user_id')->get();
            if(count($userExpertise))
            {
                $userExpertise = $userExpertise->pluck('user_id');
                $userExpertiseArray = array();
                foreach($userExpertise as $expertise)
                {
                    array_push($usersArray, $expertise);
                }
                
            }
            array_push($searchKeyCount, 14);
            
        }

        // Check titles is exist in a request
        if(!empty($request->title))
        {
            $titl = explode(",", $request->title);
            $titles = UserFieldValue::whereIn('value', $titl)->where('user_field_id', 12)->groupBy('user_id')->get();
            if(count($titles))
            {
                $titles = $titles->pluck('user_id');
                $titlesArray = array();
                foreach($titles as $title)
                {
                    array_push($usersArray, $title);
                }
                
            }   
            array_push($searchKeyCount, 15);            
        }

        // Check speciality is exist in a request
        if(!empty($request->speciality))
        {
            $speciality = explode(",", $request->speciality);
            $specialities = UserFieldValue::whereIn('value', $speciality)->where('user_field_id', 14)->groupBy('user_id')->get();
            if(count($specialities))
            {
                $specialities = $specialities->pluck('user_id');
                $specialitiesArray = array();
                foreach($specialities as $specialit)
                {
                    array_push($usersArray, $specialit);
                }
               
            }
            array_push($searchKeyCount, 16);
            
        }
        //return $searchKeyCount;
        if(count($searchKeyCount) > 1){
            $usersArray = $this->get_duplicates($usersArray, count($searchKeyCount));
        }
        $blockUsers = [];
        $myBlockList = BlockList::where('user_id', $myId)->get()->pluck('block_user_id')->toArray();
        $blockList = BlockList::where('block_user_id', $myId)->get()->pluck('user_id')->toArray();
        if(count($blockList) > 0){
            foreach($blockList as $key=>$value){
                array_push($blockUsers, $value);
            }
        }
        if(count($myBlockList) > 0){
            foreach($myBlockList as $key=>$value){
                array_push($blockUsers, $value);
            }
        }
        // $index = array_search($myId, $blockUsers);
        // if($index >= 0){
        //     unset($blockUsers[$index]);
        //     $blockUsers = array_values($blockUsers);
        // }
        //return $blockUsers;
        $users = array();
        if(count($searchKeyCount) > 1){
            $defaultHubsUsers = $this->getPrivacyUsersListBasedonProfileStatus($usersArray, $myId);
            $query = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')
                            ->with('avatar_id')
                            ->where('role_id', $roleId)
                            ->where('profile_percentage', 100)
                            ->whereIn('user_id', $defaultHubsUsers)
                            ->groupBy('user_id')
                            ->orderBy('company_name')
                            ->orderBy('name')
                            ->orderBy('first_name');

            if($roleId == 4 || $roleId == 5 || $roleId == 6){
                $query->whereIn('role_id', [4,5,6]);
            }
            else{
                $query->where('role_id', $roleId);
            }
            if(!empty($myBlockList)){
                $query->whereNotIn('user_id',$myBlockList);
            }
            $users = $query->paginate(12);
            
        }
        else{
            $myHubs = UserSelectedHub::where('user_id', $myId)->get();

            if(isset($myHubs) && count($myHubs) > 0 && $roleId != 10)
            {
                $myHubs = $myHubs->pluck('hub_id');
                $defaultHubs = UserSelectedHub::whereIn('hub_id', $myHubs)->get();
                $defaultHubsUser = $defaultHubs->pluck('user_id');
                $defaultHubsUsers = $this->getPrivacyUsersListBasedonProfileStatus($defaultHubsUser, $myId);
                $query = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')
                        ->with('avatar_id')
                        ->whereIn('user_id', $defaultHubsUsers)
                        ->where('profile_percentage', 100)
                        ->groupBy('user_id')
                        ->orderBy('company_name')
                        ->orderBy('name')
                        ->orderBy('first_name');
                if($roleId == 4 || $roleId == 5 || $roleId == 6){
                    $query->whereIn('role_id', [4,5,6]);
                }
                else{
                    $query->where('role_id', $roleId);
                }
                if(!empty($blockUsers)){
                    $query->whereNotIn('user_id',$blockUsers);
                }
                $users = $query->paginate(12);
            }
            else{
                $allUsers = User::where('role_id',$roleId)->where('profile_percentage','100')->pluck('user_id');
                $defaultHubsUsers = $this->getPrivacyUsersListBasedonProfileStatus($allUsers, $myId);
                $query = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')
                            ->with('avatar_id')
                            ->whereIn('user_id', $defaultHubsUsers)
                            ->where('profile_percentage', 100)
                            ->groupBy('user_id')
                            ->orderBy('company_name')
                            ->orderBy('name')
                            ->orderBy('first_name');
                if(!empty($blockUsers)){
                    $query->whereNotIn('user_id',$blockUsers);
                }
                $users = $query->paginate(12);
            }
        }
        $searchUser = User::where('user_id',$myId)->first();
        if(count($users) > 0)
        {
            foreach($users as $keyVal => $user)
            {

                $checkPrivacy = User::whereRaw("find_in_set(".$searchUser->role_id.",who_can_connect)")->where('user_id', $user->user_id)->first();
                $permissions = $this->getPermissions($searchUser->role_id);
                $available_to_connect = '';
                $available_to_follow = '';
                if(count($permissions) > 0) 
                {
                    foreach($permissions as $permission)
                    {
                        if($permission->permission_type == 1)
                        {
                            foreach($permission->map_permissions as $per)
                            {
                                if($user->role_id == $per->role_id)
                                {
                                    $available_to_connect = 1;
                                    break;
                                }                        
                            } 
                        }
                        if($permission->permission_type == 2)
                        {
                            foreach($permission->map_permissions as $per)
                            {
                                if($user->role_id == $per->role_id)
                                {
                                    $available_to_follow = 1;
                                    break;
                                }
                            }
                        }
                    }
                }
                if(!empty($available_to_connect) && !empty($checkPrivacy))
                {
                    $users[$keyVal]->available_to_connect = 1;

                    $checkIfConnected = Connection::where(function ($query) use ($myId, $user) {
                    $query->where('resource_id', $myId)->where('user_id', $user->user_id);
                      })->oRwhere(function ($query) use ($myId, $user) {
                          $query->where('resource_id', $user->user_id)->where('user_id', $myId);
                      })->first();

                    if(!empty($checkIfConnected))
                    {
                        if($checkIfConnected->is_approved == '1')
                        {
                            $users[$keyVal]->connection_flag = 1;
                        }
                        elseif($checkIfConnected->resource_id == $myId)
                        {
                            $users[$keyVal]->connection_flag = 2;
                        }
                        elseif($checkIfConnected->resource_id == $user->user_id)
                        {
                            $users[$keyVal]->connection_flag = 3;
                        }
                    } 
                    else
                    {
                        $users[$keyVal]->connection_flag = 0;    
                    }
                }
                else
                {   
                    $users[$keyVal]->available_to_connect = 0;
                    $users[$keyVal]->connection_flag = 0;
                }

                
                if(!empty($available_to_follow))
                {
                    $users[$keyVal]->available_to_follow = 1;
                    $checkIfFollowing = Follower::where('user_id', $myId)->where('follow_user_id', $user->user_id)->first();
                    (!empty($checkIfFollowing)) ? $users[$keyVal]->follow_flag = 1 : $users[$keyVal]->follow_flag = 0;
                }
                else
                {   
                    $users[$keyVal]->available_to_follow = 0;
                    $users[$keyVal]->follow_flag = 0;
                }
                $followerCount = Follower::where('follow_user_id', $user->user_id)->count();  
                $users[$keyVal]->follower_count = $followerCount;

                $users[$keyVal]->hubConnectionRequest = true;
                $visitorHubsBlocks = ConnectionRequestHubs::where('user_id',$user->user_id)->get()->pluck('hub_id');
                if($visitorHubsBlocks){
                    $mySelectedHubs = UserSelectedHub::where('user_id',$this->user->user_id)->WhereIn('hub_id',$visitorHubsBlocks)->count();
                    if($mySelectedHubs > 0){
                        $users[$keyVal]->hubConnectionRequest = false;
                    }
                }
            }
            return response()->json(['success' => $this->successStatus,
                                'data' => $users
                                ], $this->successStatus);

        }
        else{
            $message = "No users found";
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    // Get array duplicate records only
    public function get_duplicates ($array, $searchLength) {
        $temp_array = array();
        foreach($array as $val)
        {
            $length = array_count_values($array);
            if(!in_array($val, $temp_array) && $length[$val] >= $searchLength){
                array_push($temp_array, $val);
            }
            
        }
        return $temp_array;
        //return array_unique( array_diff_assoc( $array, array_unique( $array ) ) );
    }

    /*
    * Searching User
    */
    public function globalSearch($keyWord)
    {
        $user = $this->user;
        $user_id = $this->user->user_id;
        $blockList = BlockList::where('user_id', $user->user_id)->get();
        $blockUsers = [];
        if(count($blockList) > 0)
        {
            $blockUsers = $blockList->pluck('block_user_id');
        }
        $query = User::select('user_id','role_id','name','email','first_name','last_name','company_name','restaurant_name','avatar_id','who_can_view_profile')->with('avatar_id')
        ->whereNotIn('role_id', [1,2])->where('who_can_view_profile','!=','just me')->where('profile_percentage', 100)->orderBy('user_id', 'DESC');
        $query->Where(function ($q) use ($keyWord) {
            $q->where('email', 'LIKE', '%' . $keyWord . '%')
                ->orWhere('first_name', 'LIKE', '%' . $keyWord . '%')
                ->orWhere('last_name', 'LIKE', '%' . $keyWord . '%')
                ->orWhere('company_name', 'LIKE', '%' . $keyWord . '%')
                ->orWhere('restaurant_name', 'LIKE', '%' . $keyWord . '%')
                ->orWhere('name', 'LIKE', '%' . $keyWord . '%');
        });
        if(!empty($blockUsers)){
            $query->whereNotIn('user_id',$blockUsers);
        }
        $users = $query->paginate(10)->toArray();

        $events = Event::with(['is_event_liked' => function ($like) use ($user_id) {
                                    return $like->whereHas('user', function ($user) use ($user_id) {
                                        $user->select('user_id');
                                        $user->where('user_id', $user_id);
                                    })->get();  
                                }])->with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('status', '1')->Where('event_name', 'LIKE', '%' . $keyWord . '%')->paginate(10);

        $blogs = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('title', 'LIKE', '%' . $keyWord . '%')->where('status', '1')->paginate(10);

        $trips = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','country:id,name',)->where('trip_name', 'LIKE', '%' . $keyWord . '%')->where('status', '1')->paginate(10);
        if($trips){
            foreach($trips as $key => $trip)
            {
                if(!empty($trip->region)){
                    $tripIdArray = explode(',',$trip->region);
                    $tripState = State::select('id','name')->whereIn('id',$tripIdArray)->get();
                    $trips[$key]->region = $tripState;
                    
                }
            }
        }

        $awards = Award::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','medal')->where('award_name', 'LIKE', '%' . $keyWord . '%')->where('status', '1')->paginate(10);


        // $myConnections = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->get();
        // $myConnections = $myConnections->pluck('poster_id');

         // Get user connections
        $requestedConnection = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');
        $getRequestedConnection = Connection::select('*','resource_id as poster_id')->where('user_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');

        $merged = $requestedConnection->merge($getRequestedConnection);
        $myConnections = $merged->all();

        // Get user followers
        $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $user->user_id)->pluck('poster_id');

        // Merged connections & Followers
        $userIds = array_merge((array)$myConnections, (array)$myFollowers);

        if(count($userIds) > 0)
        {
            // array_push($userIds, $user->user_id);
            // $userIds = array_unique($userIds);
            $query = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
            ->with('attachments.attachment_link')
            ->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
            ->where('body', 'LIKE', '%' . $keyWord . '%');
            
            $query->Where(function ($q) use ($user) {
                    $q->where('privacy', 'Public')
                      ->orWhere('subject_id', $user->user_id);
            });

            if(!empty($myConnections)){
                $query->orWhere(function ($q) use ($myConnections) {
                    $q->where('privacy', 'Connections')
                      ->WhereIn('subject_id', $myConnections);
                });
            }


            if(!empty($myFollowers)){
                $query->orWhere(function ($q) use ($myFollowers) {
                    $q->where('privacy', 'Followers')
                      ->WhereIn('subject_id', $myFollowers);
                });
            }

            $activityPosts = $query->orderBy('created_at', 'DESC')->paginate(10);
        }
        else
        {
            $activityPosts = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width')
            ->with('attachments.attachment_link')
            ->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
            ->where('body', 'LIKE', '%' . $keyWord . '%')
            ->Where(function ($query) use ($user) {
            $query->where('privacy', 'public')
              ->orWhere('subject_id', $user->user_id);
             })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        }

        foreach($activityPosts as $activityKey => $activityPost)
        {
            $isLikedActivityPost = ActivityLike::where('resource_id', $activityPost->activity_action_id)->where('poster_id', $user->user_id)->first();
            if(!empty($isLikedActivityPost))
            {
                $activityPosts[$activityKey]->like_flag = 1;
            }
            else
            {
                $activityPosts[$activityKey]->like_flag = 0;
            }
            $activityPosts[$activityKey]->posted_at = $activityPost->created_at->diffForHumans();

            $followerCount = Follower::where('follow_user_id', $activityPost->subject_id)->count();  
            $activityPosts[$activityKey]->follower_count = $followerCount; 
        }

        $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id);
            
        $products = [];

        foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
            
            $featuredListing = FeaturedListing::with('image')
                                ->where('user_id', $this->user->user_id)
                                ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                ->where('title', 'LIKE', '%' . $keyWord . '%')
                                ->orderBy('featured_listing_id','DESC')->paginate(10); 

            $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
            
        }
        if(!empty($users)){
            foreach($users['data'] as $key => $getUser)
            {
                if($getUser['who_can_view_profile'] == 'connections'){

                    $getConnections = Connection::where('is_approved','1')
                    ->where(function($query)  use ($getUser) {
                        $query->where(function($q) use ($getUser) {
                            $q->where('resource_id', $getUser['user_id'])
                            ->where('user_id', $this->user->user_id);
                        })->orWhere(function($q) use ($getUser) {
                            $q->where('user_id', $getUser['user_id'])
                            ->where('resource_id', $this->user->user_id);
                        });
                    })->first();

                    if(empty($getConnections)){
                        $new_value = $users['data'][$key];
                        unset($users['data'][$key]);
                        array_unshift($users['data'], $new_value); 
                    }
                }

                if($getUser['who_can_view_profile'] == 'followers'){

                    $getFollower = Follower::where(function($query)  use ($getUser) {
                        $query->where(function($q) use ($getUser) {
                            $q->where('follow_user_id', $getUser['user_id'])
                            ->where('user_id', $this->user->user_id);
                        })->orWhere(function($q) use ($getUser) {
                            $q->where('user_id', $getUser['user_id'])
                            ->where('follow_user_id', $this->user->user_id);
                        });
                    })->first();

                    if(empty($getFollower)){
                        $new_value = $users['data'][$key];
                        unset($users['data'][$key]);
                        array_unshift($users['data'], $new_value); 
                    }
                }


                $followerCount = Follower::where('follow_user_id', $getUser['user_id'])->count();  
                if(isset($users['data'][$key])){
                    $users['data'][$key]['follower_count'] = $followerCount;
                }

                $users['data'][$key]['hubConnectionRequest'] = true;
                $visitorHubsBlocks = ConnectionRequestHubs::where('user_id',$getUser['user_id'])->get()->pluck('hub_id');
                if($visitorHubsBlocks){
                    $mySelectedHubs = UserSelectedHub::where('user_id',$this->user->user_id)->WhereIn('hub_id',$visitorHubsBlocks)->count();
                    if($mySelectedHubs > 0){
                        $users['data'][$key]['hubConnectionRequest'] = false;
                    }
                }
            }
        }
        $data = ['peoples' => $users, 'posts' => $activityPosts, 'events' => $events, 'blogs' => $blogs, 'trips' => $trips, 'awards' => $awards/*, 'featured_listing' => $products*/];
        return response()->json(['success' => $this->successStatus,
                                 'data' => $data
                                ], $this->successStatus);
   
    }


    /*
     * Get list of hubs seleted by user
     *
     */
    public function getAllHubs()
    {
        try
        {
            $user = $this->user;
            $checkUser = User::where('user_id', $user->user_id)->first();
            $myHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            if(!empty($checkUser))
            {
                $hubs = Hub::select('id','title')->where('status', '1')->get();
                if(count($hubs) > 0)
                {
                    return response()->json(['success' => $this->successStatus,
                                 'hubs' => $hubs
                                ], $this->successStatus);
                }
                else
                {
                    $message = "No hubs available";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                /*if(count($myHubs) > 0)
                {
                    $myHubs = $myHubs->pluck('hub_id')->toArray();
                    $hubs = Hub::select('id','title')->whereIn('id', $myHubs)->where('status', '1')->get();
                    if(count($hubs) > 0)
                    {
                        return response()->json(['success' => $this->successStatus,
                                     'hubs' => $hubs
                                    ], $this->successStatus);
                    }
                    else
                    {
                        $message = "No hubs available";
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "You have not selected any hubs";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }*/
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get pickup or delivery
     *
     */
    public function getPickupOrDelivery()
    {
        try
        {
            $user = $this->user;
            $role_id = $user->role_id;
            $steps = [];
            $checkUser = User::where('user_id', $user->user_id)->first();
            
            $roleFields = DB::table('user_fields')
                                      ->whereIn("user_field_id", [9,21,22])
                                      ->get();
            if(!empty($roleFields))
            {
                foreach ($roleFields as $key => $value)
                {
                    $roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);

                    //Check Fields has option
                    if($value->type !='text' && $value->type !='email' && $value->type !='password')
                    {
                        
                        $value->options = $this->getUserFieldOptionParent($value->user_field_id);

                        if(!empty($value->options))
                        {
                            foreach ($value->options as $k => $oneDepth) 
                            {

                                $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                                //Check Option has any Field Id
                                $checkRow = DB::table('user_field_maps')->where('user_field_id','=',$value->user_field_id)->where('role_id', 9)->first();

                                if($checkRow){
                                    $value->parentId = $checkRow->option_id;
                                }

                                $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$oneDepth->user_field_option_id);

                                $value->options[$k]->options = $data;

                                
                                foreach ($value->options[$k]->options as $optionKey => $optionValue) 
                                {
                                    $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                    $value->options[$k]->options[$optionKey]->options = $options;
                                }  

                            }
                        }
                    }// End Check Fields has option

                    $steps[] = $value;
                }
                return response()->json(['success'=>$this->successStatus,'data' =>$steps], $this->successStatus); 
            }
            else
            {
                $message = "The field does not exist";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get list of field values
     *
     */
    public function getFieldValues($fieldId)
    {
        try
        { 
            $user = $this->user;
            $checkUser = User::where('user_id', $user->user_id)->first();
            
            $roleFields = DB::table('user_fields')
                                      ->where("user_field_id","=", $fieldId)
                                      ->first();
            $roleFields->title = $this->translate('messages.'.$roleFields->title,$roleFields->title);
            $roleFields->placeholder = $this->translate('messages.'.$roleFields->placeholder,$roleFields->placeholder);
            if(!empty($checkUser))
            {
                if($roleFields->type !='text' && $roleFields->type !='email' && $roleFields->type !='password')
                {
                                
                    $roleFields->options = $this->getUserFieldOptionParent($roleFields->user_field_id);

                    if(!empty($roleFields->options)){

                        foreach ($roleFields->options as $k => $oneDepth) 
                        {

                            //$roleFields->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                            $data = $this->getUserFieldOptionsNoneParent($roleFields->user_field_id,$oneDepth->user_field_option_id);

                            $roleFields->options[$k]->options = $data;

                            
                            foreach ($roleFields->options[$k]->options as $optionKey => $optionValue) 
                            {
                                $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                $roleFields->options[$k]->options[$optionKey]->options = $options;
                            }  
                                
                        }
                    }

                    return response()->json(['success' => $this->successStatus,
                                     'data' => $roleFields
                                    ], $this->successStatus);
                }
                else
                {
                    $message = "Undefined field type";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "The field does not exist";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }               
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* Get All Fields Option who are child
     * @params $user_field_id 
    */
    public function getUserFieldOptionParent($fieldId){

        $fieldOptionData = [];
        
        if($fieldId > 0){
            $fieldOptionData = DB::table('user_field_options')
                    ->where('user_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->where('deleted_at', null)
                    ->orderBy('option','ASC')
                    ->get()->toArray();

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }

            if($fieldId == 2){
                array_multisort(array_column( $fieldOptionData, 'option' ), SORT_ASC, $fieldOptionData);
            }
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Get All Fields Option who are child
     * @params $user_field_id and $user_field_option_id
     */
    public function getUserFieldOptionsNoneParent($fieldId, $parentId){

        $fieldOptionData = [];
        
        if($fieldId > 0 && $parentId > 0){
            $fieldOptionData = DB::table('user_field_options')
                ->where('user_field_id','=',$fieldId)
                ->where('parent','=',$parentId)
                ->where('deleted_at', null)
                ->get()->toArray();                                

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }

            if($fieldId == 2){
                array_multisort(array_column( $fieldOptionData, 'option' ), SORT_ASC, $fieldOptionData);
            }
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Save privacy data
     * @Params $request
     */
    public function savePrivacy(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'allow_message_from' => 'required', 
                //'who_can_view_age' => 'required',
                'who_can_view_profile' => 'required',
                'who_can_connect' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $checkUser = User::where('user_id', $user->user_id)->first();
            $checkPrivacyDataExist = UserPrivacy::where('user_id', $user->user_id)->first();

            if(!empty($checkUser))
            {
                if(empty($checkPrivacyDataExist))
                {
                    $privacy = new UserPrivacy;
                    $privacy->user_id = $user->user_id;
                    $privacy->allow_message_from = $request->allow_message_from;
                    $privacy->who_can_view_age = $request->who_can_view_age;
                    $privacy->who_can_view_profile = $request->who_can_view_profile;
                    $privacy->who_can_connect = $request->who_can_connect;
                    $privacy->save();
                }
                else
                {
                    UserPrivacy::where('user_id', $user->user_id)->update(['allow_message_from' => $request->allow_message_from, 'who_can_view_age' => $request->who_can_view_age, 'who_can_view_profile' => $request->who_can_view_profile, 'who_can_connect' => $request->who_can_connect]);

                }             
                    $message = "Privacy settings has been saved";
                    return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    
    /*
     * Check Role Permission
     * @Params $userRole, $FollowingUserRole
     */

    public function checkRolePermission($userRole, $FollowingUserRole)
    {
        $status = [];
        $permission = ConnectFollowPermission::where("role_id", $userRole)->where('permission_type', '2')->first();

        if(!empty($permission))
        {
            $permissionRole = MapPermissionRole::where("connect_follow_permission_id", $permission->connect_follow_permission_id)->get();
            $checkRoleWisePermission = $permissionRole->pluck('role_id')->toArray();

            if(in_array($FollowingUserRole, $checkRoleWisePermission))
            {
                $status = [$this->translate('messages.'."Success","Success"), 0];
            }
            else
            {
                $status = [$this->translate('messages.'."Failed","Failed"), 1];
            }
            
        }
        else
        {
            $status = [$this->translate('messages.'."You are not authorized to follow this user","You are not authorized to follow this user"), 2];
        }
        
        return $status;
    }


    /*
     * Get Permission For Sending Requests
     * 
     */
    public function getPermissions($roleId)
    {
        try
        {
            $permissions = ConnectFollowPermission::select('connect_follow_permission_id','role_id','permission_type')->where('role_id', $roleId)->get();
            if(count($permissions) > 0)
            {
                foreach($permissions as $key => $permission)
                {
                    $mapPermission = MapPermissionRole::select('map_permission_role_id','connect_follow_permission_id','role_id')->where('connect_follow_permission_id', $permission->connect_follow_permission_id)->get();
                    $permissions[$key]->map_permissions = $mapPermission;
                }

                return $permissions;
            }
            else
            {
                $message = "No privillege granted for sending request or following someone";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Get users based on who can view profile status
     * 
     */
    private function getPrivacyUsersListBasedonProfileStatus($users, $myId){

        $hubSelectedUsers = array();    
        foreach($users as $key=>$roleUser){
            $profileMode = User::select('who_can_view_profile')->where('user_id',$roleUser)->first();
            if($profileMode){
                if($profileMode->who_can_view_profile == 'anyone'){
                    array_push($hubSelectedUsers, $roleUser);
                }
                elseif($profileMode->who_can_view_profile == 'followers'){
                    $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $myId)->first();
                    if($myFollowers){
                        array_push($hubSelectedUsers, $roleUser);
                    }


                }
                elseif($profileMode->who_can_view_profile == 'connections'){
                    // Get user connections
                    $myConnections = Connection::where('is_approved', '1')->where(function ($query) use ($myId) {
                                            $query->where('resource_id',$myId)
                                                  ->orWhere('user_id', $myId);
                                        })->first();


                    if($myConnections){
                        array_push($hubSelectedUsers, $roleUser);
                    }

                }
                elseif($profileMode->who_can_view_profile == 'justme'){
                    if ($roleUser == $myId){
                        array_push($hubSelectedUsers, $myId);
                    }
                }
            }
        }

        if(!empty($hubSelectedUsers)){
            $hubSelectedUsers = array_unique($hubSelectedUsers);
        }

        return $hubSelectedUsers;
    }

   
}