<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\Entities\Hub; 
use App\Http\Controllers\CoreController;
use Modules\User\Entities\City;
use Modules\User\Entities\State;
use Modules\User\Entities\Country;
use Modules\User\Entities\UserTempHub;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\MapHubCity;
use Modules\User\Entities\MapHubCountryRole;
use Modules\User\Entities\UserSelectedHub;
use Illuminate\Routing\Controller;
use Modules\User\Entities\HubInfoIcon;
use Modules\User\Entities\ConnectionRequestHubs;
use App\Http\Traits\SortArray;
use Validator;
use DB;

class HubController extends CoreController
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    use SortArray;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }


    /***
    get Countries
    ***/
    public function getHubCountries(Request $request)
    {
        try
        {
            $user = $this->user;
            $getAssignedCountries = MapHubCountryRole::where('role_id', $user->role_id)->get();
            $getCountries = $getAssignedCountries->pluck('country_id')->toArray();

            if(count($getCountries) > 0)
            {
                $countryData = Country::where('status', '1')->whereIn('id', $getCountries)->orderBy('name','ASC')->get();
            }
            else
            {
                $countryData = Country::where('status', '1')->orderBy('name','ASC')->get();
            }
            
            if(count($countryData) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $countryData,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'No countries found']], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Active and Upcoming Countries
    ***/
    public function getActiveAndUpcomingCountries(Request $request)
    {
        try
        {
            $user = $this->user;
            $getAssignedCountries = MapHubCountryRole::where('role_id', $user->role_id)->where('is_active', '1')->get();
            $getUpcomingCountries = MapHubCountryRole::where('is_active', '0')->get();

            $getCountries = $getAssignedCountries->pluck('country_id')->toArray();
            $getComingCountries = $getUpcomingCountries->pluck('country_id')->toArray();

            if(count($getCountries) > 0)
            {
                $countryData = Country::select('id','name','flag_id','status')->with('flag_id')->where('status', '1')->whereIn('id', $getCountries)->orderBy('name','ASC')->get();
                $countryUpcomingCountrieData = Country::select('id','name','flag_id','status')->with('flag_id')->where('status', '1')->whereIn('id', $getComingCountries)->orderBy('name','ASC')->get();
            }
            else
            {
                $countryData = Country::select('id','name','flag_id','status')->with('flag_id')->where('status', '1')->orderBy('name','ASC')->get();
            }
            
            if(count($countryData) > 0)
            {
                $data = ['active_countries' => $countryData, 'upcoming_countries' => $countryUpcomingCountrieData,'role_id'=>$user->role_id];
                return response()->json(['success' => $this->successStatus,
                                         'data' => $data,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'No countries found']], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Cities for Hubs
    ***/
    public function getHubsCity(Request $request)
    {
        try
        {
            $user = $this->user;
            $jsonArray = [];
            foreach($request->params as $state)
            {
                $stateData = State::where('id', $state)->first();
                
                $cities = City::where('state_id', $state)->where('status', '1')->get();

                $UserTempHubs = UserTempHub::where('user_id', $user->user_id)->whereIn('state_id', $request->params)->get();
                $allCity = $UserTempHubs->pluck('city_id')->toArray();

                if(!empty($UserTempHubs))
                {
                    foreach($cities as $key => $city)
                    {
                        if(in_array($city->id, $allCity))
                        {
                            $cities[$key]->is_selected = true;
                        }
                        else
                        {
                            $cities[$key]->is_selected = false;
                        }
                    }
                }
                
                
                $harray[] = ['state_id'=>$stateData->id,'state_name'=>$stateData->name,'city_array'=>$cities];
               
                    
            }
            $hubs = ['cities' => $harray];
            return response()->json(['success' => $this->successStatus,
                                        'data' => $hubs,
                                    ], $this->successStatus);
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Sate wise Hubs
    ***/
    public function getStateWiseHubs(Request $request) //this ons is changes as latest
    {
        try
        {
            $user = $this->user;
            
            $validator = Validator::make($request->all(), [ 
                'country_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $selectedStates = array();
            $getHubsStates = Hub::where('country_id', $request->country_id)->get();
            if(count($getHubsStates) > 0)
            {
                $getStateIds = $getHubsStates->pluck('state_id')->toArray();
                if(count($getStateIds) > 0)
                {
                    $stateData = State::whereIn('id', $getStateIds)->where('status', '1')->get();
                    foreach($stateData as $keyState => $states)
                    {
                        $UserSelectedHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
                        if(count($UserSelectedHubs) > 0 )
                        {
                            foreach($UserSelectedHubs as $UserSelectedHub)
                            {
                                $selectedHub = Hub::where('id', $UserSelectedHub->hub_id)->first();
                                $selectedStates[] = $selectedHub->state_id;
                            }
                        }
                        $hubs = Hub::with('image','state')->where('country_id', $request->country_id)->where('state_id', $states->id)->orderBy('title', 'asc')->get();
                        foreach($hubs as $key => $hub)
                        {
                            $hubs[$key]->is_checked = false;
                            $UserSelectedHub = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $hub->id)->first();
                            if(!empty($UserSelectedHub) && $hub->id == $UserSelectedHub->hub_id)
                            {
                                if((count($hubs) == 1) && (count($getHubsStates) == 1)){
                                    $selectedSingleRoleIds = array(3,7,8);
                                    if(in_array($user->role_id, $selectedSingleRoleIds)){
                                        //$hubs[$key]->is_checked = true;
                                    }
                                }
                                $hubs[$key]->is_selected = true;
                            }
                            else
                            {
                                if((count($hubs) == 1) && (count($getHubsStates) == 1)){
                                    $selectedSingleRoleIds = array(3,7,8);
                                    if(in_array($user->role_id, $selectedSingleRoleIds)){
                                        // $insertedHub = array(
                                        //     'user_id' => $user->user_id,
                                        //     'hub_id' => $hub->id,
                                        //     'created_at' => now(),
                                        //     'updated_at' => now()
                                        // );
                                        // UserSelectedHub::create($insertedHub);
                                        $hubs[$key]->is_checked = false;
                                    }
                                    
                                    $hubs[$key]->is_selected = true;
                                }
                                else{
                                    $hubs[$key]->is_selected = false;
                                    $selectedSingleRoleIds = array(3,4,5,6,7,8);
                                    if(in_array($user->role_id, $selectedSingleRoleIds)){
                                        if($hubs[$key]->title == 'Chicago Hub' && count($UserSelectedHubs) == 0){
                                            $hubs[$key]->is_checked = true;
                                        }
                                    }
                                    

                                }
                            }
                        }
                        $harray[] = ['state_id'=>$states->id,'state_name'=>$states->name, 'lattitude' => $states->latitude, 'longitude' => $states->longitude ,'radius' => 50, 'is_selected'=> (in_array($states->id, $selectedStates)) ? true : false   ,'hubs_array'=>$hubs];
                    }

                    $harray = $this->MysortArray($harray, 'state_name', 'ASC');
                    $hubs = ['hubs' => $harray];
                    return response()->json(['success' => $this->successStatus,
                                        'data' => $hubs,
                                    ], $this->successStatus);
                }
            }  
            else
            {
                $message = "We do not have any hub in this country";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);    
            }                         
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    public function getHubs(Request $request)
    {
        try
        {
            $user = $this->user;
            $jsonArray = [];
            $hubsArray = [];
            foreach($request->params as $country => $states)
            {
                $countryData = Country::where('id', $country)->first();
                foreach($states as $state)
                {
                    $stateData = State::where('id', $state)->first();
                    
                    $hubs = Hub::with('image')->where('country_id', $country)->where('state_id', $state)->get();
                    
                    
                        foreach($hubs as $key => $hub)
                        {
                            $UserSelectedHub = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $hub->id)->first();
                            if(!empty($UserSelectedHub) && $hub->id == $UserSelectedHub->hub_id)
                            {
                                
                                if((count($hubs) == 1) && ($key == 0)){
                                    $selectedSingleRoleIds = array(3,7,8);
                                    if(in_array($user->role_id, $selectedSingleRoleIds)){
                                        $hubs[$key]->is_checked = true;
                                    }
                                    else{
                                        $hubs[$key]->is_checked = false;
                                    }
                                }
                                $hubs[$key]->is_selected = true;
                            }
                            else
                            {
                                if((count($hubs) == 1) && ($key == 0)){

                                    $selectedSingleRoleIds = array(3,7,8);
                                    if(in_array($user->role_id, $selectedSingleRoleIds)){
                                        $insertedHub = array(
                                            'user_id' => $user->user_id,
                                            'hub_id' => $hub->id,
                                            'created_at' => now(),
                                            'updated_at' => now()
                                        );
                                        UserSelectedHub::create($insertedHub);
                                        $hubs[$key]->is_checked = true;
                                    }
                                    else{
                                        $hubs[$key]->is_checked = false;
                                    }
                                    $hubs[$key]->is_selected = true;
                                }
                                else{
                                    $hubs[$key]->is_selected = false;
                                }
                            }
                        }
                        $harray[] = ['state_id'=>$stateData->id,'state_name'=>$stateData->name,'hubs_array'=>$hubs];

                }
            }
            $hubs = ['hubs' => $harray];
            return response()->json(['success' => $this->successStatus,
                                        'data' => $hubs,
                                    ], $this->successStatus);
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /******
     * Get Single hub data by hub Id 
     ******/

    public function getSingleHub($hubId){
        try
        {
            $myHubs = UserSelectedHub::where('user_id',$this->user->user_id)->get();
            $hub = Hub::with('image:id,attachment_url,base_url','country:id,name','state:id,name')->where('id',$hubId)->where('status', '1')->first();   
            if(!empty($myHubs)){
            $myHubsArray = $myHubs->pluck('hub_id')->toArray();
                if($hub){
                    if(in_array($hub->id, $myHubsArray)){
                        $hub->is_selected = true;
                    }else{
                        $hub->is_selected = false; 
                    }
                    
                }
            } 
            return response()->json(['success' => $this->successStatus,
                                'data' => $hub
                                ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Review Hubs Selection
    ***/
    public function hubsReviewSelection(Request $request)
    {
        try
        {
            $user = $this->user;
            $harray = [];
            $language = $request->header('locale');
            $UserSelectedHubs = UserSelectedHub::where('user_id', $user->user_id)->whereNotNull('hub_id')->get();
            $hubsSelectedByUser = $UserSelectedHubs->pluck('hub_id')->toArray();
            $UserTempHubs = UserTempHub::where('user_id', $user->user_id)->get();
            $selectedCountries = array();
            if(count($UserSelectedHubs) > 0)
            {
                foreach($UserSelectedHubs as $UserSelectedHub)
                {
                    $selectedHub = Hub::where('id', $UserSelectedHub->hub_id)->first();
                    $selectedCountries[] = $selectedHub->country_id;
                }
            }
            if(count($UserTempHubs) > 0)
            {
                foreach($UserTempHubs as $UserTempHub)
                {
                    $selectedCountries[] = $UserTempHub->country_id;
                }
            }
            $selectedCountries = array_unique($selectedCountries);
            if(count($selectedCountries) > 0)
            {
                foreach($selectedCountries as $selectedCountry)
                {
                    $countryData = Country::where('id', $selectedCountry)->first();
                    $hubsData = Hub::with('image','state')->whereIn('id', $hubsSelectedByUser)->where('country_id', $selectedCountry)->orderBy('title','asc')->get();
                    $UserTemporaryHubs = UserTempHub::with('city:id,name','state')->where('user_id', $user->user_id)->where('country_id', $selectedCountry)->get();
                    $harray[] = ['country_id' => $countryData->id,'country_name' => $countryData->name,'hubs' => $hubsData, 'cities' => $UserTemporaryHubs];
                }
            }

            $lan = 'en';
            if(!empty($language)){
                if($language == 'it'){
                    $lan = 'it';
                }
            }
            $hubInfo = HubInfoIcon::select('message_'.$lan.' as message')->where('role_id',$user->role_id)->first();
            return response()->json(['success' => $this->successStatus,
                                        'data' => $harray,
                                        'hubInfo' => $hubInfo,
                                    ], $this->successStatus);
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Post User Hubs
    ***/
    public function postUserHubs(Request $request)
    {
        try
        {
            $user = $this->user;

            if(!empty($request->params['add_or_update']) && $request->params['add_or_update'] != 1) // 1=save, 2=update
            {
                UserSelectedHub::where('user_id', $user->user_id)->delete();
                UserTempHub::where('user_id', $user->user_id)->delete();
            }
                if(!empty($request->params['selectedhubs']))
                {
                    foreach($request->params['selectedhubs'] as $hub)
                    {
                        $userHub = new UserSelectedHub;
                        $userHub->user_id = $user->user_id;
                        $userHub->hub_id = $hub;
                        $userHub->save();
                    }
                }
                if(!empty($request->params['selectedcity']))
                {
                    foreach($request->params['selectedcity'] as $city)
                    {

                        $userHub = new UserTempHub;
                        $userHub->user_id = $user->user_id;
                        $userHub->country_id = $city['country_id'];
                        $userHub->state_id = $city['state_id'];
                        $userHub->city_id = $city['city_id'];

                        $cityLatLng = City::select('latitude','longitude')->where('id',$city['city_id'])->first();
                        $getHubs = Hub::select('id','radius')->where('country_id',$city['country_id'])->where('state_id',$city['state_id'])->get();
                        if($cityLatLng){
                            if($getHubs){
                                foreach($getHubs as $key=>$hub){
                                    $radiusMiles = $hub->radius;
                                    $lat = $cityLatLng->latitude;
                                    $lng = $cityLatLng->longitude;
                                    $existHub = DB::table("hubs")
                                                ->select("hubs.id", \DB::raw("3956 * acos(cos(radians(" . $lat . "))
                                                * cos(radians(hubs.latitude)) 
                                                * cos(radians(hubs.longitude) - radians(" . $lng . ")) 
                                                + sin(radians(" .$lat. ")) 
                                                * sin(radians(hubs.latitude))) AS distance"))
                                                ->having('distance', '<=', $radiusMiles)
                                                ->first();
                                    if($existHub){
                                        $userExistHub = UserSelectedHub::where('user_id',$user->user_id)->where('hub_id',$existHub->id)->first();
                                        if(!$userExistHub){
                                            $userSelectedHub = new UserSelectedHub;
                                            $userSelectedHub->user_id = $user->user_id;
                                            $userSelectedHub->hub_id = $existHub->id;
                                            $userSelectedHub->save();
                                        }

                                        $userHub->hub = $existHub->id;
                                    }
                                }
                            }
                        }
                        

                        
                        $userHub->save();
                    }
                }

            if(!empty($request->params['selectedcity']) || !empty($request->params['selectedhubs']))
            {
                return response()->json(['success' => $this->successStatus,
                                    'message' => 'Successfully added',
                                    ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => ['Please select atleast a hub or a city']]], $this->exceptionStatus); 
            }
            
            
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Selected Hub Countries
    ***/
    public function getSelectedHubCountries(Request $request)
    {
        try
        {
            $user = $this->user;
            $jsonArray = [];
            $hubsArray = [];
            
            $UserSelectedHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            $UserTempHubs = UserTempHub::where('user_id', $user->user_id)->get();
            $selectedCountries = array();
            if(count($UserSelectedHubs) > 0 )
            {
                foreach($UserSelectedHubs as $UserSelectedHub)
                {
                    $selectedHub = Hub::where('id', $UserSelectedHub->hub_id)->first();
                    $selectedCountries[] = $selectedHub->country_id;
                }
            }
            if(count($UserTempHubs) > 0)
            {
                foreach($UserTempHubs as $UserTempHub)
                {
                    $selectedCountries[] = $UserTempHub->country_id;
                }
            }

            $getUpcomingCountries = MapHubCountryRole::where('is_active', '0')->get();
            $getComingCountries = $getUpcomingCountries->pluck('country_id')->toArray();

            $countryUpcomingCountrieData = Country::with('flag_id')->select('id','name','flag_id','status')->where('status', '1')->whereIn('id', $getComingCountries)->orderBy('name','ASC')->get();

            $getAssignedCountries = MapHubCountryRole::where('role_id', $user->role_id)->where('is_active', '1')->get();
            $getCountries = $getAssignedCountries->pluck('country_id')->toArray();

            if(count($getCountries) > 0)
            {
                $countryData = Country::with('flag_id')->select('id','name','flag_id','status')->where('status', '1')->whereIn('id', $getCountries)->orderBy('name','ASC')->get();
            }
            else
            {
                $countryData = Country::with('flag_id')->select('id','name','flag_id','status')->where('status', '1')->orderBy('name','ASC')->get();
            }

            foreach($countryData as $key => $country)
            {
                if(in_array($country->id, $selectedCountries))
                {
                    $countryData[$key]->is_selected = true;
                }
                else
                {
                    $countryData[$key]->is_selected = false;
                }
            }

            $data = ['active_countries' => $countryData, 'upcoming_countries' => $countryUpcomingCountrieData];

            return response()->json(['success' => $this->successStatus,
                                        'data' => $data,
                                    ], $this->successStatus);
                
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    get Selected Hub States
    ***/
    public function getSelectedHubStates(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'country_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = $this->user;

            $UserSelectedHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
            $UserTempHubs = UserTempHub::where('user_id', $user->user_id)->where('country_id', $request->country_id)->get();

            $selectedStates = array();
            if(count($UserSelectedHubs) > 0 )
            {
                foreach($UserSelectedHubs as $UserSelectedHub)
                {
                    $selectedHub = Hub::where('id', $UserSelectedHub->hub_id)->first();
                    $selectedStates[] = $selectedHub->state_id;
                }
            }
            if(count($UserTempHubs) > 0)
            {
                foreach($UserTempHubs as $UserTempHub)
                {
                    $selectedStates[] = $UserTempHub->state_id;
                }
            }

            $states = State::where('status', '1')->where('country_id', $request->country_id)->orderBy('name','ASC')->get();
            
            if(count($states) > 0)
            {
                foreach($states as $key => $state)
                {
                    if(in_array($state->id, $selectedStates))
                    {
                        $states[$key]->is_selected = true;
                    }
                    else
                    {   
                        $states[$key]->is_selected = false;
                    }   
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $states,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'No states found']], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }          
            
    }

    /*
     * Make Validation Rules
     * @Params $params
     */

    public function makeValidationRules($params){
        $rules = [];
        
        foreach ($params as $key => $field) {
            //return $key;
            if($key == 'hubs'){

                $rules[$key] = 'required';

            }else if($key == 'cities'){

                //$rules[$key] = 'required|max:190';

            }
        }

        return $rules;

    }

    /** Delete All Hubs **/
    public function userDeletedAllHubs(){
        try{
            $user = $this->user;
            if($user){
                UserSelectedHub::where('user_id', $user->user_id)->delete();
                UserTempHub::where('user_id', $user->user_id)->delete();

                return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Hub has been deleted successfully","Hub has been deleted successfully")
                                    ], $this->successStatus);
            }

            return response()->json(['success'=>false,'errors' =>['exception' => "We can't delete all hubs"]], $this->exceptionStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        } 
    }


    public function searchHubsMyRegions(Request $request){
        try{
        $user = $this->user;
            
        $validator = Validator::make($request->all(), [ 
            'country_id' => 'required', 
            'latitude' => 'required', 
            'longitude' => 'required', 
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
        }
        $lat = $request->latitude;
        $lng = $request->longitude;
        $countryId = $request->country_id;
        $getHubsStates = Hub::where('country_id', $request->country_id)->get();
        $stateData = DB::table("states")
                    ->select("states.id","states.name","states.latitude","states.longitude", \DB::raw("3956 * acos(cos(radians(" . $lat . "))
                    * cos(radians(states.latitude)) 
                    * cos(radians(states.longitude) - radians(" . $lng . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(states.latitude))) AS distance"))
                    ->where('country_id',$countryId)
                    ->having('distance', '>=', 0)
                    ->orderBy('distance','asc')
                    ->get();
            if(count($stateData) > 0)
            {
                //$stateData = State::whereIn('id', $getStateIds)->where('status', '1')->get();
                $selectedStates = array();
                foreach($stateData as $keyState => $states)
                {
                    $UserSelectedHubs = UserSelectedHub::where('user_id', $user->user_id)->get();
                    if(count($UserSelectedHubs) > 0 )
                    {
                        foreach($UserSelectedHubs as $UserSelectedHub)
                        {
                            $selectedHub = Hub::where('id', $UserSelectedHub->hub_id)->first();
                            $selectedStates[] = $selectedHub->state_id;
                        }
                    }
                    $hubs = Hub::with('image','state')->where('state_id', $states->id)->orderBy('title', 'asc')->get();
                    if(count($hubs) > 0){
                        foreach($hubs as $key => $hub)
                        {
                            $hubs[$key]->is_checked = false;
                            $UserSelectedHub = UserSelectedHub::where('user_id', $user->user_id)->where('hub_id', $hub->id)->first();
                            if(!empty($UserSelectedHub) && $hub->id == $UserSelectedHub->hub_id)
                            {
                                
                                $hubs[$key]->is_selected = true;
                            }
                            else
                            {
                                if((count($hubs) == 1) && (count($getHubsStates) == 1)){
                                    $selectedSingleRoleIds = array(3,7,8);
                                    if(in_array($user->role_id, $selectedSingleRoleIds)){
                                    
                                        $hubs[$key]->is_checked = false;
                                    }
                                    
                                    $hubs[$key]->is_selected = true;
                                }
                                else{
                                    $hubs[$key]->is_selected = false;
                                    $selectedSingleRoleIds = array(3,4,5,6,7,8);
                                    if(in_array($user->role_id, $selectedSingleRoleIds)){
                                        if($hubs[$key]->title == 'Chicago Hub' && count($UserSelectedHubs) == 0){
                                            $hubs[$key]->is_checked = true;
                                        }
                                    }
                                    

                                }
                            }
                        }
                    $harray[] = ['state_id'=>$states->id,'state_name'=>$states->name, 'lattitude' => $states->latitude, 'longitude' => $states->longitude ,'radius' => 50, 'is_selected'=> (in_array($states->id, $selectedStates)) ? true : false   ,'hubs_array'=>$hubs];
                    }
                }

                //$harray = $this->MysortArray($harray, 'state_name', 'ASC');
                $hubs = ['hubs' => $harray];
                return response()->json(['success' => $this->successStatus,
                                    'data' => $hubs,
                                ], $this->successStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /** 
     * 
     * Get My All Country HUbs  
     * 
     **/
    public function getMyCountryHubs(Request $request){
        $user = $this->user;

        $query = Hub::select("id","title")->where('country_id', $user->country_id)->where('status','1')->orderBy('title','asc');
        if((isset($request->keyword)) && (!empty($request->keyword))){
            $query->where('title', 'LIKE', '%' . $request->keyword . '%');
        }
        $myCountryHubs = $query->get();
        if($myCountryHubs){
            foreach($myCountryHubs as $key=>$hub)
            {
                $request = ConnectionRequestHubs::where('user_id',$user->user_id)->where('hub_id',$hub->id)->first();
                if($request){
                    $myCountryHubs[$key]->checked = true;
                }
                else{
                    $myCountryHubs[$key]->checked = false;
                }
            }
        }
        
        return response()->json(['success' => $this->successStatus,
                                        'data' => $myCountryHubs,
                                    ], $this->successStatus);

    }

    public function notAllowedConnectionRequest(Request $request){
        try{
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'hubs' => 'required|array',
                'hubs.*' => 'numeric',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            ConnectionRequestHubs::where('user_id',$user->user_id)->delete();
            $hubs = $request->hubs;
            foreach($hubs as $key=>$hub){
                $connectionHubs = new ConnectionRequestHubs();
                $connectionHubs->user_id = $user->user_id;
                $connectionHubs->hub_id = $hub;
                $connectionHubs->save();
            }

            return response()->json(['success' => $this->successStatus,
                                        'message' => "Your Data has been saved successfully",
                                    ], $this->successStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
   
}
