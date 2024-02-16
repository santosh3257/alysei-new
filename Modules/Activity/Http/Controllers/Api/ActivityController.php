<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\CoreComment;
use Modules\User\Entities\DeviceToken; 
use Modules\User\Entities\User;
use Modules\User\Entities\Blog;
use Modules\User\Entities\Trip;
use Modules\User\Entities\Event; 
use Modules\User\Entities\UserSelectedHub;
use Modules\User\Entities\Hub;
use Modules\Recipe\Entities\PreferenceMapUser;
use Modules\User\Entities\News;
use App\Attachment;
use App\Notification;
use App\Http\Traits\NotificationTrait;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\Follower;
use Modules\Activity\Entities\DiscoverAlysei;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Modules\Activity\Entities\CoreCommentLikes;
use Modules\Miscellaneous\Entities\AppVersion;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Kreait\Firebase\Factory;
use Modules\User\Entities\Country;
use Modules\Activity\Entities\ActivitySpam;
use Modules\User\Entities\BlockList;
use Modules\User\Entities\State;
use App\Jobs\SendPostNotification;
use Modules\User\Entities\DiscoveryPost; 
use Modules\User\Entities\DiscoveryPostCategory;
use Modules\Activity\Entities\DiscoveryNewsView;

class ActivityController extends CoreController
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


    public function directLinkCon(){
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $dynamicLinksDomain = 'https://devalysei.page.link';
        $dynamicLinks = $factory->createDynamicLinksService($dynamicLinksDomain);
        return $dynamicLinks;
    }
    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();    
        return $database;
    }

    public function removeLikes($id)
    {
        $data = $this->conn_firbase()->getReference('like_unlike_post/'.$id)->remove();
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
     * Get all hubs
     * @Params $request
     */
    public function getAllHubs()
    {
        $user = $this->user;
        $hubs = DB::table('hubs')->select('id','title')
                        ->where('status', '1')
                        ->get();
        if(count($hubs) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                     'hubs' => $hubs
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Nothing found';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get Specialization
     * @Params $request
     */
    public function getSpecialization()
    {
        $user = $this->user;
        $specializations = DB::table('user_field_options')->select('user_field_option_id','option')
                        ->where('user_field_id', 11)
                        ->where('deleted_at', null)
                        ->get()->toArray();
        if(count($specializations) > 0)
        {
            foreach($specializations as $key => $value){
                $specializations[$key]->option = $this->translate('messages.'.$value->option,$value->option);
            }

            array_multisort(array_column( $specializations, 'option' ), SORT_ASC, $specializations);
            return response()->json(['success' => $this->successStatus,
                                     'data' => $specializations
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Nothing found';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get Restaurant types
     * @Params $request
     */
    public function getRestaurantTypes()
    {
        $user = $this->user;
        $restaurants = DB::table('user_field_options')->select('user_field_option_id','option')
                        ->where('user_field_id', 10)
                        ->where('deleted_at', null)
                        ->get()->toArray();
        if(count($restaurants) > 0)
        {
            foreach($restaurants as $key => $value){
                $restaurants[$key]->option = $this->translate('messages.'.$value->option,$value->option);
            }
            array_multisort(array_column( $restaurants, 'option' ), SORT_ASC, $restaurants);
            return response()->json(['success' => $this->successStatus,
                                     'data' => $restaurants
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Nothing found';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }


    /*
     * Get Discover Alysei
     * @Params $request
     */
    public function getDiscoverAlysei($user='')
    {
        $user = $this->user;
        
        $discoverAlysei = DiscoverAlysei::select('discover_alysei_id','title','name','description','image_id','status')->with('attachment')->where('status', '1')->get();            
        return $discoverAlysei; 
    }

    /*
     * Get Discover Alysei listing
     * @Params $request
     */
    public function getDetailListingOfStories($user='')
    {
        $usersArray =[];
        $getConnections = Connection::where('is_approved', '1')
        ->Where(function ($query) use ($user) {
            $query->where('resource_id', $user->user_id)
              ->orWhere('user_id', $user->user_id);
        })
        ->get();

        if(count($getConnections) > 0)
        {
            foreach($getConnections as $connections)
            {
                if($connections->resource_id == $user->user_id)
                {
                    array_push($usersArray, $connections->user_id);
                }
                else
                {
                    array_push($usersArray, $connections->resource_id);   
                }
            }
        }

        $getFollowings = Follower::with('user:user_id,name,email,company_name,first_name,last_name,avatar_id','user.avatar_id')->where('user_id', $user->user_id)->orderBy('id', 'DESC')->get();
        if(count($getFollowings))
        {
            foreach($getFollowings as $getFollowing)
            {
                array_push($usersArray, $getFollowing->follow_user_id);
            }    
        }
        
        return $usersArray;
    }

    /*
     * Get Bubbles Shuffling
     * @Params $request
     */
    public function getBubblesShuffling($user='', $request)
    {
        $user = $this->user;
        $language = $request->header('locale');
        $userIds =  $this->getDetailListingOfStories($user);
        
        $events = Event::whereIn('user_id', $userIds)->where('status', '1')->orderBy('created_at','DESC')->first();
        $trips = Trip::whereIn('user_id', $userIds)->where('status', '1')->orderBy('created_at','DESC')->first();
        $blogs = Blog::whereIn('user_id', $userIds)->where('status', '1')->orderBy('created_at','DESC')->first();
        $users = User::where('role_id','!=', 1)->where('user_id','!=', $user->user_id)->whereIn('user_id', $userIds)->orderBy('created_at','DESC')->first();
        $top = '';
        if(!empty($events) && !empty($trips) && !empty($blogs)){
            if(($events['created_at'] > $trips['created_at']) && ($events['created_at'] > $blogs['created_at']) && ($events['created_at'] > $users['created_at']))
            {
                $top = 'events';
            }
            elseif(($trips['created_at'] > $events['created_at']) && ($trips['created_at'] > $blogs['created_at']) && ($trips['created_at'] > $users['created_at']))
            {   
                $top = 'trips';
            }
            elseif(($blogs['created_at'] > $events['created_at']) && ($blogs['created_at'] > $trips['created_at']) && ($blogs['created_at'] > $users['created_at']))
            {
                $top = 'blogs';
            }
            elseif(($users['created_at'] > $events['created_at']) && ($users['created_at'] > $trips['created_at']) && ($users['created_at'] > $blogs['created_at']))
            {
                $top = 'users';
            }
            else
            {
                $top = '';
            }
        }
        $title = 'title';
        if(!empty($language)){
            if($language == 'it'){
                $title = 'title_it';
            }
        }
        $discoverAlysei = DiscoverAlysei::select('discover_alysei_id', $title.' as title','name','description','image_id','status','new_update','category_id','created_at','updated_at')->with('attachment','category')->where('status', '1')->get()->toArray();
        
        foreach($discoverAlysei as $key => $stories)
        {
            $latestNewsSeen = DiscoveryNewsView::where('user_id', $user->user_id)->where('viewType',$stories['name'])->count();
            if($latestNewsSeen == 0 && $stories['new_update'] == 1){
                $discoverAlysei[$key]['new_update'] = true;
            }
            else{
                $discoverAlysei[$key]['new_update'] = false;
            }
            if($stories['name'] == $top)
            {
                $new_value = $discoverAlysei[$key];
                unset($discoverAlysei[$key]);
                array_unshift($discoverAlysei, $new_value);    
            }

            if($stories['discover_alysei_id'] == 5)
            {
                $new_value = $discoverAlysei[$key];
                unset($discoverAlysei[$key]);
                array_unshift($discoverAlysei, $new_value);    
            }


        } 
        
        return $discoverAlysei;
    }

    /*
     * Get Discover Alysei(Circle) detail
     * @Params $request
     */
    public function getCircleDetail(Request $request)
    {
        try
        {
            $user = $this->user;
            $user_id = $this->user->user_id;
            $validator = Validator::make($request->all(), [ 
                'type' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $usersArray = [];
            $users =  $this->getDetailListingOfStories($user);
            $message = 'Nothing found!';
            switch($request->type)
            {
                case ('events'):
                    /*if(count($users) > 0)
                    {*/ 
                        $today = Carbon::now();
                        $today = $today->toDateTimeString();
                        
                        $data = Event::with(['is_event_liked' => function ($like) use ($user_id) {
                                    return $like->whereHas('user', function ($user) use ($user_id) {
                                        $user->select('user_id');
                                        $user->where('user_id', $user_id);
                                    })->get();  
                                }])->with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('status', '1')->whereDate('event_date_time', '>=', Carbon::now())->orderBy('event_date_time','DESC')->paginate(10);
                    /*}
                    else
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }*/
                    
                break;

                case ('trips'):
                    /*if(count($users) > 0)
                    {*/
                        $data = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','country:id,name')->where('status', '1')->orderBy('created_at','DESC')->paginate(10);
                        foreach($data as $key => $datas)
                        {
                            if(!empty($datas->region)){
                                $selectedRegion = $datas->region;
                                $tripIdArray = explode(',',$datas->region);
                                $tripState = State::select('id','name')->whereIn('id',$tripIdArray)->get();
                                $data[$key]->region = $tripState;
                            }
                            $specialityTrip = DB::table('user_field_options')
                                    ->where('user_field_option_id', $datas->adventure_type)
                                    ->where('user_field_id', 14)
                                    ->where('deleted_at', null)
                                    ->first();
                                    
                            if(!empty($specialityTrip))  
                            {
                                $data[$key]->adventure = ['adventure_type_id' => $specialityTrip->user_field_option_id, 'adventure_type' => $specialityTrip->option];    
                            }
                            else
                            {
                                $data->adventure = null;   
                            }
                        }

                    /*}
                    else
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }*/
                    
                break;

                case ('blogs'):
                    /*if(count($users) > 0)
                    {*/
                        $data = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('status', '1')->orderBy('created_at','DESC')->paginate(10);
                        
                        if($data){
                            foreach($data as $key=>$blog){
                                $data[$key]->date = date('Y/m/d', strtotime($blog->date) );
                            }
                        }
                    /*}
                    else
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }*/
                    
                break;

                case ('restaurants'):
                    $query = User::select('user_id','name','email','restaurant_name','role_id','address','lattitude','longitude','avatar_id','cover_id','country_id')
                            ->with('avatar_id')->with('cover_id')->where('role_id', 9)->orderBy('created_at','DESC')->where('profile_percentage', 100)->whereNotIn('user_id',[$user_id]);
                    // if(!empty($request->country)){
                    //     $country = Country::where('name',$request->country)->first();
                    //     if($country){
                    //         $query->where('country_id',$country->id);
                    //     }
                    // }
                    $data = $query->paginate(10);

                    foreach ($data as $key => $value) {

                        $fieldOption = DB::table("user_field_values as ufv")
                            ->select('ufo.option')
                            ->join("user_field_options as ufo", 'ufo.user_field_option_id', '=', 'ufv.value')

                            ->where("ufv.user_field_id","=",10)
                            ->where("ufv.user_id","=",$value->user_id)
                            ->first();

                        if(!empty($fieldOption)){
                            $data[$key]->restaurant_type = $fieldOption->option;    
                        }else{
                            $data[$key]->restaurant_type = "";    
                        }
                    }
                break;

                case ('news'):
                    
                    $data = News::select('news_id','title','image_id')->where('status', 'publish')->with('attachment')->orderBy('created_at','desc')->get();
                    
                break;

                default:
                $message = 'Something went wrong!';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

            return response()->json(['success' => $this->successStatus,
                                     'data' => $data
                                    ], $this->successStatus);           
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }        
    }


    // Get Discovery Post
    public function getDiscoveryPosts(Request $request, $slug){
        try
        {
            $language = $request->header('locale');
            $title = 'title';
            if(!empty($language)){
                if($language == 'it'){
                    $title = 'title_it';
                }
            }
        
            $cat = DiscoveryPostCategory::where('slug',$slug)->first();
            if($cat){
                $posts = DiscoveryPost::select('id',$title.' as title','slug','email','country_code','phone_number','category_id','description','url','status','image_id','created_at')->with('attachment')->where('category_id',$cat->id)->where('status','1')->orderBy('created_at','DESC')->paginate(20);
                return response()->json(['success' => $this->successStatus,
                                        'data' => $posts,
                                        ], $this->successStatus); 
            }
            else{
                $message = 'No data found';
            return response()->json(['success' => false,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * filter stories
     * @Params $request
     */
    public function filterDiscoverStories(Request $request)
    {
        $user = $this->user;
        $condition = '';
        
        $validator = Validator::make($request->all(), [ 
            'type' => 'required'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
        }
        $users =  $this->getDetailListingOfStories($user);
        $message = $this->translate('message.Nothing found!','Nothing found!');
        $usersArray = [];
        switch($request->type)
        {
            case ('events'):
               
                    if(!empty($request->restaurant_type))
                    {
                        $values = DB::table('user_field_values')
                                            ->where('value', $request->restaurant_type)
                                            ->where('user_field_id', 10)
                                            ->get();
                        $userIds = $values->pluck('user_id')->toArray();
                        foreach($userIds as $userId)
                        {
                            array_push($usersArray, $userId);
                        }
                    }
                    $query = Event::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id,cover_id','user.avatar_id','user.cover_id','attachment')->where('status', '1')->whereDate('event_date_time', '>=', Carbon::now());
                    if(!empty($request->date))
                    {
                        $query->where('date',$request->date);
                    }
                    if(!empty($request->event_type))
                    {
                        $query->where('event_type',$request->event_type);
                    }
                    if(!empty($request->registration_type))
                    {
                        $query->where('registration_type',$request->registration_type);
                    }
                    if(!empty($usersArray)){
                        $query->whereIn('user_id',$usersArray);
                    }

                    $data = $query->orderBy('event_date_time','desc')->paginate(10);
                
                
            break;

            case ('trips'):
                    $regionIds = array();
                    if(!empty($request->region)){
                        $regionIds = Trip::select('trip_id')
                        ->selectRaw('`region` REGEXP REPLACE("'.$request->region.'", ",", "(\\,|$)|") as haslists')
                        ->whereRaw('`region` REGEXP REPLACE("'.$request->region.'", ",", "(\\,|$)|") = 1')
                        ->groupBy('trip_id')->pluck('trip_id')->toArray();
                        //return $regionIds;
                    }
                    $query = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id,cover_id','user.avatar_id','attachment','intensity','country:id,name','user.cover_id')->where('status', '1');
                    if(count($regionIds) > 0)
                    {
                        //$region_keywords = explode(',',$request->region);
                        $query->whereIn('trip_id', $regionIds);
                        
                    }

                    if(!empty($request->adventure_type))
                    {
                        $query->where('adventure_type',$request->adventure_type);
                    }

                    if(!empty($request->duration))
                    {
                        $query->where('duration',$request->duration);
                    }
                    if(!empty($request->intensity))
                    {
                        $query->where('intensity',$request->intensity);
                    }
                    if(!empty($request->price))
                    {
                        $query->where('price',$request->price);
                    }

                    $data = $query->paginate(10);
                    foreach($data as $key => $datas)
                    {

                        if(!empty($datas->region)){
                            $tripIdArray = explode(',',$datas->region);
                            $tripState = State::select('id','name')->whereIn('id',$tripIdArray)->get();
                            $data[$key]->region = $tripState;
                        }
                        $specialityTrip = DB::table('user_field_options')
                                ->where('user_field_option_id', $datas->adventure_type)
                                ->where('user_field_id', 14)
                                ->where('deleted_at', null)
                                ->first();
                                
                        if(!empty($specialityTrip))  
                        {
                            $data[$key]->adventure = ['adventure_type_id' => $specialityTrip->user_field_option_id, 'adventure_type' => $specialityTrip->option];    
                        }
                        else
                        {
                            $data->adventure = null;   
                        }
                    }
               
                
            break;

            case ('blogs'):
               
                if(!empty($request->specialization)){
                    $values = DB::table('user_field_values')
                                        ->where('value', $request->specialization)
                                        ->where('user_field_id', 11)
                                        ->get();
                    $userIds = $values->pluck('user_id')->toArray();
                    foreach($userIds as $userId)
                    {
                        array_push($usersArray, $userId);
                    }
                }
                if(!empty($request->title)){
                    $values = DB::table('user_field_values')
                                        ->where('value', $request->title)
                                        ->where('user_field_id', 12)
                                        ->get();
                    $userIds = $values->pluck('user_id')->toArray();
                    foreach($userIds as $userId)
                    {
                        array_push($usersArray, $userId);
                    }
                }
                if((!empty($request->specialization)) && (!empty($request->title)))
                {
                    $usersArray = $this->get_duplicates($usersArray);
                }

             
                $query = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id,cover_id','user.avatar_id','attachment','user.cover_id')->where('status', '1');
                
                if(!empty($usersArray)){
                    $query->whereIn('user_id',$usersArray );
                }
                $data = $query->paginate(10);
                
            break;

            case ('restaurants'):
               
                    if(!empty($request->restaurant_type))
                    {
                        $values = DB::table('user_field_values')
                                            ->where('value', $request->restaurant_type)
                                            ->where('user_field_id', 10)
                                            ->get();
                        $userIds = $values->pluck('user_id')->toArray();
                        foreach($userIds as $userId)
                        {
                            array_push($usersArray, $userId);
                        }
                    }

                    if(!empty($request->hubs)){
                        $values = UserSelectedHub::where('hub_id',$request->hubs)->get();
                        $userIds = $values->pluck('user_id')->toArray();
                        foreach($userIds as $userId)
                        {
                            array_push($usersArray, $userId);
                        }
                    }

                    if((!empty($request->restaurant_type)) && (!empty($request->hubs)))
                    {
                        $usersArray = $this->get_duplicates($usersArray);
                    }

                    if(count($usersArray) > 0)
                    {
                        $join = join(",", $usersArray);
                        if($condition != '')
                        $condition .=" and users.user_id in(".$join.")";
                        else
                        $condition .="users.user_id in(".$join.")";
                    }
                    if($condition != '')
                    {
                        $data = User::select('user_id','name','email','restaurant_name','role_id','address','lattitude','longitude','avatar_id','cover_id')->with('avatar_id')->with('cover_id')->whereRaw('('.$condition.')')->where('role_id', 9)->where('profile_percentage', 100)->paginate(10);

                        foreach ($data as $key => $value) {

                            $fieldOption = DB::table("user_field_values as ufv")
                                ->select('ufo.option')
                                ->join("user_field_options as ufo", 'ufo.user_field_option_id', '=', 'ufv.value')

                                ->where("ufv.user_field_id","=",10)
                                ->where("ufv.user_id","=",$value->user_id)
                                ->first();

                            if(!empty($fieldOption)){
                                $data[$key]->restaurant_type = $fieldOption->option;    
                            }else{
                                $data[$key]->restaurant_type = "";    
                            }
                        }
                    }
                    else
                    {
                        $data = User::select('user_id','name','email','restaurant_name','role_id','address','lattitude','longitude','avatar_id','cover_id')->with('avatar_id','cover_id')->where('role_id', 9)->where('profile_percentage', 100)->paginate(10);

                        foreach ($data as $key => $value) {

                            $fieldOption = DB::table("user_field_values as ufv")
                                ->select('ufo.option')
                                ->join("user_field_options as ufo", 'ufo.user_field_option_id', '=', 'ufv.value')

                                ->where("ufv.user_field_id","=",10)
                                ->where("ufv.user_id","=",$value->user_id)
                                ->first();

                            if(!empty($fieldOption)){
                                $data[$key]->restaurant_type = $fieldOption->option;    
                            }else{
                                $data[$key]->restaurant_type = "";    
                            }
                        }
                    }
                                 
            break;

            default:
            $message = 'Something went wrong!';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }

        if(count($data) > 0)
        {
            return response()->json(['success' => $this->successStatus,
                                     'data' => $data
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Nothing found!';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Get Stories detail
     * @Params $request
     */
    public function getStoriesDetails(Request $request)
    {
        $user = $this->user;
        $condition = '';
        
        $validator = Validator::make($request->all(), [ 
            'type' => 'required',
            'id'   => 'required'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
        }
        
        if($request->type == 'events')
        {
            $details = Event::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('event_id', $request->id)->where('status', '1')->first();
        }
        elseif($request->type == 'trips')
        {
            $details = Trip::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','intensity','country:id,name','region:id,name')->where('trip_id', $request->id)->where('status', '1')->first();
        }
        elseif($request->type == 'blogs')
        {
            $details = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('blog_id', $request->id)->where('status', '1')->first();
        }
        else
        {
            $message = 'Something went wrong';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }

        if(!empty($details))
        {
            return response()->json(['success' => $this->successStatus,
                                     'data' => $details
                                    ], $this->successStatus);
        }
        else
        {
            $message = 'Invalid id provided!';
            return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
        }
    }

    /*
     * Add Post
     * @Params $request
     */
    public function addPost(Request $request)
    {
        try
        {
            $user = $this->user;
            
            $validator = Validator::make($request->all(), [ 
                'action_type' => 'required|max:190',
                'privacy'     => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }


            $actionType = $this->checkActionType($request->action_type, 1);
            if($actionType[1] > 0)
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityActionType = ActivityActionType::where("type", $request->action_type)->first();
                $activityAction = new ActivityAction;
                $activityAction->type = $activityActionType->activity_action_type_id;
                $activityAction->subject_type = "user";
                $activityAction->subject_id = $user->user_id;
                $activityAction->object_type = "user";
                $activityAction->object_id = $user->user_id;
                $activityAction->body = $request->body;
                $activityAction->privacy = $this->getPrivacy($request->privacy);
                $activityAction->height = $request->height;
                $activityAction->width = $request->width;
                if(!empty($request->attachments))
                {
                    $activityAction->attachment_count = count($request->attachments);
                }
                else
                {
                    $activityAction->attachment_count = 0;   
                }
                
                $activityAction->save();
                $parameters = [
                    'dynamicLinkInfo' => [
                        'domainUriPrefix' => 'https://devalysei.page.link',
                        'link' => 'https://dev.alysei.com/home/public/post/activity/'. $activityAction->activity_action_id,
                        'androidInfo' => [
                            'androidPackageName' => 'com.alysei',
                        ],
                        "iosInfo" => [
                        'iosBundleId'=> 'com.app.Alysei',
                        'iosAppStoreId' => '1634783265',
                        ],
                    ],
                    'suffix' => ['option' => 'SHORT'],
                ];

                $link = $this->directLinkCon()->createDynamicLink($parameters);
                $slug = rand().''.$activityAction->activity_action_id;
                ActivityAction::where('activity_action_id', $activityAction->activity_action_id)->update(['slug' => $slug, 'directLink' => $link]);
            }

            if(!empty($request->attachments) && count($request->attachments) > 0)
            {
                $this->uploadAttchments($request->attachments, $activityAction->activity_action_id);
            }
            if($activityAction)
            {
                // Send Post Notification To Job
                SendPostNotification::dispatch($activityAction->activity_action_id);
                //dispatch(new SendPostNotification($activityAction->activity_action_id));
               

                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'."Post added successfully!","Post added successfully!"),
                                         'post_id' => $activityAction->activity_action_id,
                                        ], $this->successStatus);
            }
            else
            {
                $message = 'Something went wrong!';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    
    // Get array duplicate records only
    public function get_duplicates ($array) {
        return array_unique( array_diff_assoc( $array, array_unique( $array ) ) );
    }
    /*
     * Share Post
     * @Params $request
     */
    public function sharePost(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'action_type' => 'required|max:190',
                'privacy'     => 'required',
                'shared_post_id'    => 'required' 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $actionType = $this->checkActionType($request->action_type, 5);
            if($actionType[1] > 0)
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityPost = ActivityAction::where('activity_action_id', $request->shared_post_id)->first();
                $activityActionType = ActivityActionType::where("type", $request->action_type)->first();
                $activityAction = new ActivityAction;
                $activityAction->type = $activityActionType->activity_action_type_id;
                $activityAction->subject_type = "user";
                $activityAction->subject_id = $user->user_id;
                $activityAction->object_type = "user";
                $activityAction->object_id = $user->user_id;
                $activityAction->body = $request->body;
                $activityAction->privacy = $request->privacy;
                $activityAction->shared_post_id = $request->shared_post_id;
                $activityAction->attachment_count = $activityPost->attachment_count;
                $activityAction->save();

                $parameters = [
                    'dynamicLinkInfo' => [
                        'domainUriPrefix' => 'https://devalysei.page.link',
                        'link' => 'https://dev.alysei.com/home/public/post/activity/'. $activityAction->activity_action_id,
                        'androidInfo' => [
                            'androidPackageName' => 'com.alysei',
                        ],
                        "iosInfo" => [
                        'iosBundleId'=> 'com.app.Alysei',
                        'iosAppStoreId' => '1634783265',
                        ],
                    ],
                    'suffix' => ['option' => 'SHORT'],
                ];

                $link = $this->directLinkCon()->createDynamicLink($parameters);
                $slug = rand().''.$activityAction->activity_action_id;
                ActivityAction::where('activity_action_id', $activityAction->activity_action_id)->update(['slug' => $slug, 'directLink'=>$link]);

                $activityAttchments = ActivityAttachment::where('action_id', $request->shared_post_id)->get();
                if(count($activityAttchments) > 0)
                {
                    foreach($activityAttchments as $activityAttchment)
                    {
                        $cloneAttachment = new ActivityAttachment;
                        $cloneAttachment->action_id = $activityAction->activity_action_id;
                        $cloneAttachment->type = $activityAttchment->type;
                        $cloneAttachment->id = $activityAttchment->id;
                        $cloneAttachment->save();
                    }
                    
                }
            }

            
            if($activityAction)
            {
                $getUserDetail = User::with('avatar_id')->where('user_id', $user->user_id)->first();

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

                $title_en = "shared your post";
                $title_it = "ha condiviso il tuo post";
                $selectedLocale = $this->pushNotificationUserSelectedLanguage($activityPost->subject_id);
                if($selectedLocale == 'en'){
                    $title1 = $name." shared your post";
                }
                else{
                    $title1 = $name." ha condiviso il tuo post";
                }
                if($activityPost->subject_id != $user->user_id){
                    $saveNotification = new Notification;
                    $saveNotification->from = $user->user_id;
                    $saveNotification->to = $activityPost->subject_id;
                    $saveNotification->notification_type = 2; //post share
                    $saveNotification->title_it = $title_it;
                    $saveNotification->title_en = $title_en;
                    $saveNotification->redirect_to = 'post_screen';
                    $saveNotification->redirect_to_id = $request->shared_post_id;

                    $saveNotification->sender_id = $user->user_id;
                    $saveNotification->sender_name = $name;
                    $saveNotification->sender_image = null;
                    $saveNotification->post_id = $activityAction->activity_action_id;
                    $saveNotification->connection_id = null;
                    $saveNotification->sender_role = $user->role_id;
                    $saveNotification->comment_id = null;
                    $saveNotification->reply = null;
                    $saveNotification->likeUnlike = null;

                    $saveNotification->save();

                    $tokens = DeviceToken::where('user_id', $activityPost->subject_id)->get();
                    $notificationCount = $this->updateUserNotificationCountFirebase($activityPost->subject_id);
                    if(count($tokens) > 0)
                    {
                        $collectedTokenArray = $tokens->pluck('device_token');
                        $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $name, null, /*(!empty($getUserDetail->avatar_id->attachment_url) ?? $getUserDetail->avatar_id->attachment_url : null),*/ $request->shared_post_id, null, $user->role_id, null,null,null);

                        $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $name, null, null, $request->shared_post_id, null, $user->role_id, null,null,null,null,null, $notificationCount);
                    }
                }

                return response()->json(['success' => $this->successStatus,
                                         'share_post_id' => $activityAction->activity_action_id,
                                         'message' => $this->translate('messages.'."Post shared successfully!","Post shared successfully!"),
                                        ], $this->successStatus);
            }
            else
            {
                $message = 'Something went wrong!';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
           
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Edit Post
     * @Params $request
     */
    public function editPost(Request $request)
    {
        try
        {
            $user = $this->user;
            /*$requestFields = $request->params;
            
            $requestedFields = $requestFields;
            
            $rules = $this->validateData($requestedFields, 2);*/

            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required',
                'action_type' => 'required|max:190',
                'privacy'     => 'required'
            ]);


            //$validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $actionType = $this->checkActionType($request->action_type, 2);
            if($actionType[1] > 0)
            {
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
            }
            else
            {
                $activityActionType = ActivityActionType::where("type", $request->action_type)->first();
                $activityAction = ActivityAction::where('activity_action_id', $request->post_id)->where('subject_id', $user->user_id)->first();
                if(!empty($activityAction))
                {
                    $activityAction->type = $activityActionType->activity_action_type_id;
                    $activityAction->body = $request->body;
                    $activityAction->privacy = $request->privacy;
                    if(!empty($request->attachments))
                    {
                        $activityAction->attachment_count = count($request->attachments) + $activityAction->attachment_count;
                    }
                    $activityAction->save();

                    if(!empty($request->attachments) && count($request->attachments) > 0)
                    {
                        $this->uploadAttchments($request->attachments, $request->post_id);
                    }

                    //return $request->deleteAttachments;
                    if(!empty($request->deleteAttachments)){
                        foreach($request->deleteAttachments as $key=>$attachmentId){
                            $deleteSuccess = ActivityAttachment::where('activity_attachment_id', $attachmentId)->delete();
                            if($deleteSuccess){
                            $activityAction = ActivityAction::where('activity_action_id', $request->post_id)->first();
                            $activityAction->attachment_count = $activityAction->attachment_count - 1;
                            $activityAction->save();
                            }
                        }
                    }

                    $activityPost = ActivityAction::select('activity_action_id', 'type', 'attachment_count')
                    ->with('attachments.attachment_link')->where('activity_action_id', $request->post_id)->first();
                   

                    return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'."Post updated successfully!","Post updated successfully!"),
                                        'post' => $activityPost,
                                        ], $this->successStatus);
                }
                else
                {
                    $message = $this->translate('messages.'."Invalid post id","Invalid post id");
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
                }
                
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Delete Post
     * @Params $request
     */
    public function deletePost(Request $request)
    {
        try
        {
            $user = $this->user;
            
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::where('activity_action_id', $request->post_id)->where('subject_id', $user->user_id)->first();

            if(!empty($activityPost))
            {
                $this->deleteSelectedPost($request->post_id, $user->user_id);
                $likes = ActivityLike::where('resource_id', $request->post_id)->get();
                if($likes){
                    foreach($likes as $like){
                        $this->removeLikes($likes->activity_like_id);
                    }
                }

                $message = "Post deleted successfully";
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
            }
            else
            {
                $message = "This post does not exist";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Activity Feeds
     * @Params $request
     */
    public function getActivityFeeds(Request $request)
    {
        try
        {
            $user = $this->user;
            $loggedInUserHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            $loggedInUserHubs = $loggedInUserHubs->pluck('hub_id')->toArray();
            $latestVersion = AppVersion::where('status','1')->first();
            // Get user connections
            $requestedConnection = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');
            $getRequestedConnection = Connection::select('*','resource_id as poster_id')->where('user_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');

            $merged = $requestedConnection->merge($getRequestedConnection);
            $myConnections = $merged->all();
            
            // get block user
            
            $blockUsers = [];
            $myBlockList = BlockList::where('user_id', $user->user_id)->get()->pluck('block_user_id')->toArray();
            $blockList = BlockList::where('block_user_id', $user->user_id)->get()->pluck('user_id')->toArray();
            if(count($blockList) > 0){
                foreach($blockList as $key=>$value){
                    if($user->user_id != $value){
                        array_push($blockUsers, $value);
                    }
                }
            }
            if(count($myBlockList) > 0){
                foreach($myBlockList as $key=>$value){
                    if($user->user_id != $value){
                        array_push($blockUsers, $value);
                    }
                }
            }
           
            //return $blockUsers;
            // get report post 
            $blockPosts = ActivitySpam::where('report_by',$user->user_id)->groupBy('activity_action_id')->pluck('activity_action_id')->toArray();
            //return $blockPosts;
            // Get user followers
            $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $user->user_id)->pluck('poster_id');
            // Merged connections & Followers
            $userIds = array_merge((array)$myConnections, (array)$myFollowers);
            //return $userIds;
            if(count($userIds) > 0)
            {
                $query = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                ->with('attachments.attachment_link')
                ->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->whereNotIn('activity_action_id',$blockPosts)->whereNotIn('subject_id',$blockUsers);

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
                $activityPosts = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                ->with('attachments.attachment_link')
                ->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->whereNotIn('activity_action_id',$blockPosts)->whereNotIn('subject_id',$blockUsers)
                ->Where(function ($query) use ($user) {
                $query->where('privacy', 'Public')
                  ->orWhere('subject_id', $user->user_id);
                 })
                 
                /*->where('privacy', 'public')
                ->orWhere('subject_id', $user->user_id)*/
                //->inRandomOrder()
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            }

            if(count($activityPosts) > 0)
            {
                foreach($activityPosts as $key => $activityPost)
                {
                    //is activity liked
                    $isLikedActivityPost = ActivityLike::where('resource_id', $activityPost->activity_action_id)->where('poster_id', $user->user_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $activityPosts[$key]->like_flag = 1;
                    }
                    else
                    {
                        $activityPosts[$key]->like_flag = 0;
                    }

                    //shared post
                    
                    $activityShared = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')->with('attachments.attachment_link')->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->where('activity_action_id', $activityPost->shared_post_id)->first();
                    if(!empty($activityShared))
                    {
                        $activityPosts[$key]->shared_post = $activityShared;
                        $followerCount = Follower::where('follow_user_id', $activityShared->subject_id)->count();  
                        $activityPosts[$key]->shared_post->follower_count = $followerCount; 

                    }
                    else
                    {
                        $checkPost = DB::table('activity_actions')->where('activity_action_id',$activityPost->shared_post_id)->whereNotNull('deleted_at')->first();
                        if($checkPost){
                            $activityPosts[$key]->shared_post_deleted = true;  
                        }
                        else{
                            $activityPosts[$key]->shared_post_deleted = false; 
                        } 

                        $activityPosts[$key]->shared_post = null; 
                    }

                    $activityPosts[$key]->posted_at = $activityPost->created_at->diffForHumans(); 
                    $followerCount = Follower::where('follow_user_id', $activityPost->subject_id)->count();  
                    $activityPosts[$key]->follower_count = $followerCount; 
                }

                $getPreferences = PreferenceMapUser::where('user_id', $user->user_id)->count();
                if($getPreferences > 0)
                {
                    $preferenceStatus = 1;
                }
                else
                {
                    $preferenceStatus = 0;
                }
             
                $getDiscoverAlysei = $this->getBubblesShuffling($user, $request);
                return response()->json(['success' => $this->successStatus,
                                         'having_preferences' => $preferenceStatus,
                                         'discover_alysei' => $getDiscoverAlysei,
                                         'data' => $activityPosts,
                                         'version' => $latestVersion,
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No post to display";
                return response()->json(['success'=>$this->successStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->successStatus); 
            }
            
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Get Post Details
     * @Params $request
     */
    public function getPostDetails(Request $request)
    {
        try
        {
            //$user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                ->with('attachments.attachment_link')
                ->with('subject_id:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                $isLikedActivityPost = ActivityLike::where('resource_id', $activityPost->activity_action_id)->where('poster_id', $activityPost->subject_id)->first();
                $activityPost->posted_at = $activityPost->created_at->diffForHumans();
                if(!empty($isLikedActivityPost))
                {
                    $activityPost->like_flag = 1;
                }
                else
                {
                    $activityPost->like_flag = 0;
                }

                $followerCount = Follower::where('follow_user_id', $activityPost->subject_id)->count();
                $activityPost->follower_count = $followerCount;

                $activityActionType = ActivityActionType::where('activity_action_type_id', $activityPost->type)->first();
                $actionType = $this->checkActionType($activityActionType->type, 3);

                 //shared post
                    
                 $activityShared = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')->with('attachments.attachment_link')->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->where('activity_action_id', $activityPost->shared_post_id)->first();

                 if(!empty($activityShared))
                 {
                     $activityPost['shared_post'] = $activityShared;
                     $followerCount = Follower::where('follow_user_id', $activityShared->subject_id)->count();  
                     $activityPost['shared_post']->follower_count = $followerCount; 

                 }
                 else
                 {
                     $checkPost = DB::table('activity_actions')->where('activity_action_id',$activityPost->shared_post_id)->whereNotNull('deleted_at')->first();
                     if($checkPost){
                         $activityPost['shared_post_deleted'] = true;  
                     }
                     else{
                         $activityPost['shared_post_deleted'] = false; 
                     } 

                     $activityPost['shared_post'] = null; 
                 }
                if($actionType[1] > 0)
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
                }
                else
                {
                    return response()->json(['success' => $this->successStatus,
                                         'data' => $activityPost,
                                        ], $this->successStatus);
                }
            }
            else
            {
                $message = "Invalid post Id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Like Post
     * @Params $request
     */
    public function likeUnlikePost(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required',
                'like_or_unlike' => 'required', // 1 for like 0 for unlike
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                if($request->like_or_unlike == 1)
                {
                    $isLikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $activityPost->subject_id)->first();


                    if(!empty($isLikedActivityPost))
                    {
                        $message = "You have already liked this post";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {
                        $activityLike = new ActivityLike;
                        $activityLike->resource_id = $request->post_id;
                        $activityLike->poster_type = "user";
                        $activityLike->poster_id = $activityPost->subject_id;
                        $activityLike->save();

                        $activityPost->like_count = $activityPost->like_count + 1;
                        $activityPost->save();

                        $message = "You liked this post";
                        return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                    }
                }
                elseif($request->like_or_unlike == 0)
                {
                    $isLikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $user->user_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $isUnlikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $user->user_id)->delete();
                        if($isUnlikedActivityPost == 1)
                        {
                            $activityPost->like_count = $activityPost->like_count - 1;
                            $activityPost->save();

                            $message = "You unliked this post";
                            return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first like this post";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You have not liked this post";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid like/unlike type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            else
            {
                $message = "Invalid post id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Comment Post
     * @Params $request
     */
    public function commentPost(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required',
                'comment' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                $activityActionType = ActivityActionType::where('activity_action_type_id', $activityPost->type)->first();
                $actionType = $this->checkActionType($activityActionType->type, 4);
                if($actionType[1] > 0)
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $actionType[0]]], $this->exceptionStatus);
                }
                else
                {
                    $activityComment = new CoreComment;
                    $activityComment->resource_type = "user";
                    $activityComment->resource_id = $request->post_id;
                    $activityComment->poster_type = "user";
                    $activityComment->poster_id = $user->user_id;
                    $activityComment->body = $request->comment;
                    $activityComment->save();

                    $activityPost->comment_count = $activityPost->comment_count + 1;
                    $activityPost->save();

                    $message = "Your comment has been posted successfully";
                    return response()->json(['success' => $this->successStatus,
                                             'message' => $this->translate('messages.'.$message,$message),
                                            ], $this->successStatus);
                }
            }
            else
            {
                $message = "Invalid post id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Comment Post
     * @Params $request
     */
    public function replyPost(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required',
                'comment_id' => 'required',
                'reply' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                $activityComment = new CoreComment;
                $activityComment->resource_type = "user";
                $activityComment->resource_id = $request->post_id;
                $activityComment->poster_type = "user";
                $activityComment->poster_id = $user->user_id;
                $activityComment->body = $request->reply;
                $activityComment->parent_id = $request->comment_id;
                $activityComment->save();

                $message = "Your reply has been posted successfully";
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
            }
            else
            {
                $message = "Invalid post id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Comment Post
     * @Params $request
     */
    public function deletePostComment(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'comment_id' => 'required',
                'post_id' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPostComment = CoreComment::where('core_comment_id', $request->comment_id)->where('poster_id', $user->user_id)->first();
            if(!empty($activityPostComment))
            {
                $activityPostCommentDelete = CoreComment::where('core_comment_id', $request->comment_id)->where('poster_id', $user->user_id)->delete();
                if($activityPostCommentDelete)
                {
                    if($activityPostComment->parent_id == 0){
                        $activityPost = ActivityAction::where('activity_action_id', $request->post_id)->first();
                        $activityPost->comment_count = $activityPost->comment_count - 1;
                        $activityPost->save();
    
                    }
                    
                    $message = "Your comment has been deleted successfully";
                    return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
                }
                else
                {
                    $message = "Invalid comment";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
                
            }
            else
            {
                $message = "Invalid comment";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Member Post tab
     *
     */
    public function getAllUserPosts(Request $request, $havingAttachment)
    {
        try
        {
            $loggedInUser = $this->user;

            if(!empty($request->visitor_profile_id))
            {
                if($havingAttachment == 1)
                {
                    if(!empty($request->per_page))
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $request->visitor_profile_id)
                        ->where('privacy', 'Public')
                        ->where('attachment_count','>', 0)
                        ->orderBy('activity_action_id','DESC')->paginate($request->per_page);
                        
                    }
                    else
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,first_name,last_name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $request->visitor_profile_id)
                        ->where('attachment_count','>', 0)
                        ->where('privacy', 'Public')
                        ->orderBy('activity_action_id','DESC')->paginate(15);
                    }
                    
                }
                elseif($havingAttachment == 0)
                {
                    if(!empty($request->per_page))
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,first_name,last_name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $request->visitor_profile_id)
                        ->where('privacy', 'Public')
                        ->orderBy('activity_action_id','DESC')
                        ->paginate($request->per_page);
                    }
                    else
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,first_name,last_name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $request->visitor_profile_id)
                        ->where('privacy', 'Public')
                        ->orderBy('activity_action_id','DESC')
                        ->paginate(15);
                    }
                    
                }
                else
                {
                    $message = "Please select either 1 or 0";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                if($havingAttachment == 1)
                {
                    if(!empty($request->per_page))
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,first_name,last_name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $loggedInUser->user_id)
                        ->where('attachment_count','>', 0)
                        ->orderBy('activity_action_id','DESC')->paginate($request->per_page);
                    }
                    else
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,first_name,last_name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $loggedInUser->user_id)
                        ->where('attachment_count','>', 0)
                        ->orderBy('activity_action_id','DESC')->paginate(15);
                    }
                    
                }
                elseif($havingAttachment == 0)
                {
                    if(!empty($request->per_page))
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,first_name,last_name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $loggedInUser->user_id)
                        ->orderBy('activity_action_id','DESC')
                        ->paginate($request->per_page);
                    }
                    else
                    {
                        $activityPost = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')
                        ->with('attachments.attachment_link')
                        ->with('subject_id:user_id,name,first_name,last_name,email,company_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')
                        ->where('subject_id', $loggedInUser->user_id)
                        ->orderBy('activity_action_id','DESC')
                        ->paginate(15);
                    }
                    
                }
                else
                {
                    $message = "Please select either 1 or 0";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }

            
            
            if(!empty($activityPost))
            {
                foreach($activityPost as $key => $post)
                {
                    //is activity liked
                    $isLikedActivityPost = ActivityLike::where('resource_id', $post->activity_action_id)->where('poster_id', $loggedInUser->user_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $activityPost[$key]->like_flag = 1;
                    }
                    else
                    {
                        $activityPost[$key]->like_flag = 0;
                    }
                    $activityPost[$key]->posted_at = $post->created_at->diffForHumans();

                    $followerCount = Follower::where('follow_user_id', $post->subject_id)->count();
                    $activityPost[$key]->follower_count = $followerCount;

                    $activityShared = ActivityAction::select('activity_action_id','type','subject_id','body','shared_post_id','attachment_count','comment_count','like_count','privacy','created_at','height','width','directLink')->with('attachments.attachment_link')->with('subject_id:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','subject_id.avatar_id')->where('activity_action_id', $post->shared_post_id)->first();
                    if(!empty($activityShared))
                    {
                        $activityPost[$key]->shared_post = $activityShared;
                        $followerCount = Follower::where('follow_user_id', $activityShared->subject_id)->count();  
                        $activityPost[$key]->shared_post->follower_count = $followerCount; 

                    }
                    else
                    {
                        $checkPost = DB::table('activity_actions')->where('activity_action_id',$post->shared_post_id)->whereNotNull('deleted_at')->first();
                        if($checkPost){
                            $activityPost[$key]->shared_post_deleted = true;  
                        }
                        else{
                            $activityPost[$key]->shared_post_deleted = false; 
                        } 
                        $activityPost[$key]->shared_post = null; 
                    }
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $activityPost,
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No post found";
                return response()->json(['success'=>$this->successStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->successStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
     * Delete Post
     * @Params $postId, $userId
     */

    public function deleteSelectedPost($postId, $userId)
    {
        $checkSharedPost = ActivityAction::where('shared_post_id', $postId)->first();
        $isDeletedPost = ActivityAction::where('activity_action_id', $postId)->where('subject_id', $userId)->delete();
        Notification::where('post_id',$postId)->where('from',$userId)->delete();
        if(!$checkSharedPost){
            if($isDeletedPost == 1)
            {
                $activityAttchments = ActivityAttachment::where('action_id', $postId)->get();
                if(count($activityAttchments) > 0)
                {
                    foreach($activityAttchments as $activityAttchment)
                    {
                        $this->deletePostAttachment($activityAttchment->id);
                    }
                    
                    $isDeletedActivityAttachment = ActivityAttachment::where('action_id', $postId)->delete();

                }
            }
        }
    }

    /*
     * Validate Data
     * @Params $requestedfields
     */

    public function validateData($requestedFields, $addOrUpdate)
    {
        $rules = [];
        if($addOrUpdate == 1) // validation for adding activity post
        {
            foreach ($requestedFields as $key => $field) {
                if($key == 'action_type'){
                    $rules[$key] = 'required|max:190';
                }
                elseif($key == 'privacy'){
                    $rules[$key] = 'required|max:190';
                }
            }
        }
        elseif($addOrUpdate == 2) // validation for updating activity post
        {
            foreach ($requestedFields as $key => $field) {
                if($key == 'post_id'){
                    $rules[$key] = 'required';
                }
                elseif($key == 'action_type'){
                    $rules[$key] = 'required|max:190';
                }
                elseif($key == 'privacy'){
                    $rules[$key] = 'required|max:190';
                }
            }
        }
        
        return $rules;
    }

    public function validateSharePostData($requestedFields)
    {
        $rules = [];
          foreach ($requestedFields as $key => $field) {
                if($key == 'action_type'){
                    $rules[$key] = 'required|max:190';
                }
                elseif($key == 'privacy'){
                    $rules[$key] = 'required|max:190';
                }
                elseif($key == 'shared_post_id'){
                    $rules[$key] = 'required';
                }
            }
        return $rules;    
    }

    /*
     * Check Action Type
     * @Params $type
     */

    public function checkActionType($type, $addOrUpdate)
    {
        $status = [];
        $activityActionType = ActivityActionType::where("type", $type)->first();
        if(!empty($activityActionType))
        {
            if($addOrUpdate == 1) // adding a new activity post
            {
                if($activityActionType->enabled == '0')
                {
                    $status = [$this->translate('messages.'."Currently you are not authorised to post anything","Currently you are not authorised to post anything"), 1];
                }
                elseif($activityActionType->attachable == '0')
                {
                    $status = [$this->translate('messages.'."You are not authorised to attach a media","You are not authorised to attach a media"), 2];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            elseif($addOrUpdate == 2) // updating existing activity post
            {
                if($activityActionType->editable == '0')
                {
                    $status = [$this->translate('messages.'."Currently you are not authorised to edit this post","Currently you are not authorised to edit this post"), 1];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            elseif($addOrUpdate == 3) // check is_displayble activity post
            {
                if($activityActionType->displayable == '0')
                {
                    $status = [$this->translate('messages.'."Currently you are not authorised to view this post","Currently you are not authorised to view this post"), 1];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            elseif($addOrUpdate == 4) // check is_commentable activity post
            {
                if($activityActionType->commentable == '0')
                {
                    $status = [$this->translate('messages.'."You are not authorised to comment on this post","You are not authorised to comment on this post"), 1];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            elseif($addOrUpdate == 5) // check is_sharable activity post
            {
                if($activityActionType->shareable == '0')
                {
                    $status = [$this->translate('messages.'."You are not authorised to share this post","You are not authorised to share this post"), 1];
                }
                else
                {
                    $status = [$this->translate('messages.'."Success","Success"), 0];
                }
            }
            
        }
        else
        {
            $status = [$this->translate('messages.'."Invalid action type","Invalid action type"), 3];
        }
        
        return $status;
    }

    /*
    * Upload Post Attachments
    * @Params $attchments,$actionId
    */

    public function uploadAttchments($attchments, $actionId)
    {
        foreach($attchments as $key => $attachment)
        {
            /*if($attchments[$key]->hasFile($attchments[$key]))
            {*/
                $attachmentLinkId = $this->postAttchment($attachment);
                //$attachmentLinkId = $this->createPostImage($attachment);

                $activityAttchments = new ActivityAttachment;
                $activityAttchments->action_id = $actionId;
                $activityAttchments->type = "storage_file";
                $activityAttchments->id = $attachmentLinkId;
                $activityAttchments->save();

            //}
            
        }
        
    }

    /*
     * Save comment likes
     * @Params $request
     */
    public function saveCommentLiks(Request $request){
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'user_id' => 'required',
                'post_id' => 'required|integer', 
                'like_or_unlike' => 'required|integer',
                'comment_id' => 'required|integer',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $post = ActivityAction::where('activity_action_id',$request->post_id)->first();
            if($post){
                $coreComment = CoreComment::where('core_comment_id',$request->comment_id)->first();
                if($coreComment){
                    if($request->like_or_unlike == 1){
                        $poster = User::with('avatar_id')->where('user_id', $request->user_id)->first();
                        $coreCommentLike = CoreCommentLikes::where('resource_id', $request->post_id)->where('poster_id', $request->user_id)->where('comment_id',$request->comment_id)->first();
                        if(!empty($coreCommentLike)){
                            $message = "You have already liked this comment";
                            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                        }
                        else{
                            $likeArray = array(
                                'resource_id' => $request->post_id,
                                'poster_type' => 'user',
                                'poster_id' => $request->user_id,
                                'comment_id' => $request->comment_id,
                                'created_at' => now(),
                                'updated_at' => now()
                            );

                            $commentLikes = CoreCommentLikes::create($likeArray);

                            // Count total like of this comment
                            $coreCommentLike = CoreCommentLikes::where('resource_id', $request->post_id)->where('comment_id',$request->comment_id)->count();
                            if($coreComment->poster_id != $request->user_id){
                                if($poster->role_id == 7 || $poster->role_id == 10)
                                {
                                    $name = ucwords(strtolower($poster->first_name)) . ' ' . ucwords(strtolower($poster->last_name));
                                }
                                elseif($poster->role_id == 9)
                                {
                                    $name = $poster->restaurant_name;
                                }
                                else
                                {
                                    $name = $poster->company_name;
                                }

                                $title = "liked your comment";
                                $title_it = "Mi è piaciuto il tuo commento";
                                $selectedLocale = $this->pushNotificationUserSelectedLanguage($coreComment->poster_id);
                                if($selectedLocale == 'en'){
                                    $title1 = $name." liked your comment";
                                }
                                else{
                                    $title1 = $name." Mi è piaciuto il tuo commento";
                                }

                                $saveNotification = new Notification;
                                $saveNotification->from = $poster->user_id;
                                $saveNotification->to = $coreComment->poster_id;
                                $saveNotification->notification_type = 6; //liked a post
                                $saveNotification->title_it = $title_it;
                                $saveNotification->title_en = $title;
                                $saveNotification->redirect_to = 'comment_screen';
                                $saveNotification->redirect_to_id = $request->comment_id;

                                $saveNotification->sender_id = $request->user_id; 
                                $saveNotification->sender_name = $name;
                                $saveNotification->sender_image = null;
                                $saveNotification->post_id = $request->post_id;
                                $saveNotification->connection_id = null;
                                //$saveNotification->sender_role = $user->role_id;
                                $saveNotification->comment_id = $request->comment_id;
                                $saveNotification->reply = null;
                                $saveNotification->likeUnlike = $request->like_or_unlike;

                                $saveNotification->save();

                                $tokens = DeviceToken::where('user_id', $coreComment->poster_id)->get();
                                $notificationCount = $this->updateUserNotificationCountFirebase($coreComment->poster_id);
                                if(count($tokens) > 0)
                                {
                                    $collectedTokenArray = $tokens->pluck('device_token');
                                    $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, /*$poster->avatar_id->attachment_url*/ null, $request->post_id, null, null, $request->comment_id,null, $request->like_or_unlike);

                                    $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, null, $request->post_id, null, null,null,null,null, $request->comment_id,null, $request->like_or_unlike, $notificationCount);
                                }

                                
                            }

                            $message = "You like this comment";
                                return response()->json(['success' => $this->successStatus,
                                                    'total_likes' => $coreCommentLike,
                                                    'like_id' => $commentLikes->id,
                                                    'message' => $this->translate('messages.'.$message,$message),
                                                    ], $this->successStatus);
                        }
                    
                    }
                    else{
                        $coreCommentLike = CoreCommentLikes::where('resource_id', $request->post_id)->where('poster_id', $request->user_id)->where('comment_id',$request->comment_id)->first();
                        if(!empty($coreCommentLike))
                        {
                            $deletedId = $coreCommentLike->id;
                            $likeDeleted = CoreCommentLikes::where('id', $coreCommentLike->id)->delete();
                            
                            $delete = Notification::where('from',$request->user_id)
                                               ->where('to',$coreComment->poster_id)
                                               ->where('notification_type',8)
                                               ->where('post_id',$request->post_id)
                                               ->delete();
                                               
                            // Count total like of this comment
                            $coreCommentLike = CoreCommentLikes::where('resource_id', $request->post_id)->where('comment_id',$request->comment_id)->count();

                            

                            $message = "You unliked this comment";
                            return response()->json(['success' => $this->successStatus,
                                                 'total_likes' => $coreCommentLike,
                                                 'like_id' => $deletedId,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first like this comment";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                }
                else{
                    $message = "Comment id invalid";
                    return response()->json(['success'=>$this->validationStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->validationStatus);
                }
            }
            else{
                $message = "post id invalid";
                return response()->json(['success'=>$this->validationStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->validationStatus);
            }


        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
    * Update Post comment
    * @Params $comment_id,$comment
    */
    public function updatePostComment(Request $request, $id){
        try
        {
            $user = $this->user;
            $comment = CoreComment::where('poster_id',$user->user_id)->where('core_comment_id',$id)->first();
            if($comment){
                $update = array(
                    'body' => $request->comment,
                    'updated_at' => now()
                );
                $success = CoreComment::where("core_comment_id",$id)->update($update);
                if($success){
                    return response()->json(['success' => $this->successStatus,
                    'message' => $this->translate('messages.'."Comment has been updated successfully!","Comment has been updated successfully!")], $this->successStatus);
                }
                else{
                    return response()->json(['success' => $this->validationStatus,
                    'message' => $this->translate('messages.'."Opps! Something went wrong","Opps! Something went wrong")], $this->validationStatus);
                    
                }
            }
            else{
                $message = "You can't be updated it.";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
     * Delete Post Attachment
     * @Params $request
     */
    public function deletePostAttachment($attachmentId)
    {
        try
        {
            $user = $this->user;
            
            $attachments = ActivityAttachment::where('activity_attachment_id',$attachmentId)->delete();

            if($attachments){
                return response()->json(['success' => $this->successStatus,
                'message' => $this->translate('messages.'."Attachments has been deleted successfully!","Attachments has been deleted successfully!")], $this->successStatus);
            }
            else{
                return response()->json(['success' => $this->validationStatus,
                'message' => $this->translate('messages.'."Opps! Something went wrong","Opps! Something went wrong")], $this->validationStatus);
                
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* 
     * Report Post
     * 
     */
    public function reportPost(Request $request){
        try
        {
            
            $validator = Validator::make($request->all(), [ 
                'activity_action_id' => 'required',
                'report_as' => 'required_without:message',
                'message' => 'required_without:report_as',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activitySpam = new ActivitySpam;
            $activitySpam->message = $request->message;
            $activitySpam->report_as = $request->report_as;
            $activitySpam->activity_action_id = $request->activity_action_id;
            $activitySpam->report_by = $this->user->user_id;
            $activitySpam->created_at = Now();
            $activitySpam->updated_at = Now();
            $activitySpam->save();

            if(isset($request->block_user_id) && !empty($request->block_user_id)){
                $user = User::where('user_id', $request->block_user_id)->first();
                if(!empty($user))
                {
                    $loggedInUser = $this->user;
                    $checkList = BlockList::where('user_id', $loggedInUser->user_id)->where('block_user_id', $request->block_user_id)->first();
                    if(empty($checkList))
                    {
                        $blockList = new BlockList;
                        $blockList->user_id = $loggedInUser->user_id;
                        $blockList->block_user_id = $request->block_user_id;
                        $blockList->save();

                        $checkConnection = Connection::where(function ($query) use ($request, $loggedInUser) {
                            $query->where('resource_id', '=', $request->block_user_id)
                                ->Where('user_id', '=', $loggedInUser->user_id);
                        })->orWhere(function ($query) use ($loggedInUser, $request) {
                            $query->where('resource_id', '=', $loggedInUser->user_id)
                                ->Where('user_id', '=', $request->block_user_id);
                        })->delete();

                        $checkFollower = Follower::where(function ($query) use ($request, $loggedInUser) {
                            $query->where('follow_user_id', '=', $request->block_user_id)
                                ->Where('user_id', '=', $loggedInUser->user_id);
                        })->orWhere(function ($query) use ($loggedInUser, $request) {
                            $query->where('follow_user_id', '=', $loggedInUser->user_id)
                                ->Where('user_id', '=', $request->block_user_id);
                        })->delete();
                    }
                }
            }

            return response()->json(['success' => $this->successStatus,
                'message' => $this->translate('messages.'."Activity has been reported successfully","Activity has been reported successfully")], $this->successStatus);
                

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    
    public function getPrivacy($privacy){
        if(strtolower($privacy) == 'public' || strtolower($privacy) == 'pubblico'){
            return 'Public';
        }elseif(strtolower($privacy) == 'only me' || $privacy == 'Only Me' || strtolower($privacy) == 'solo io'){
            return 'Only Me';
        }elseif(strtolower($privacy) == 'followers'){
            return 'Followers';
        }elseif(strtolower($privacy) == 'connessioni' || strtolower($privacy) == 'connections'){
            return 'Connections';
        }else{
            return 'Public';
        }
    }

    // Update Latest News has seen
    public function viewLatestNews(Request $request){
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'view_name' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $viewNews = new DiscoveryNewsView();
            $viewNews->user_id = $user->user_id;
            $viewNews->viewType = $request->view_name;
            $viewNews->save();
            return response()->json(['success' => $this->successStatus,
                    'message' => $this->translate('messages.'."Saved successfully","Saved successfully")], $this->successStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }
}
