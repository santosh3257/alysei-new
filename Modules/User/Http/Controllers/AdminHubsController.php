<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\Hub; 
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\Country;
use Modules\User\Entities\State;
use Modules\User\Entities\City;
use Modules\User\Entities\UserTempHub;
use Modules\User\Entities\UserSelectedHub;
use Modules\User\Entities\MapHubCountryRole;
use Modules\User\Entities\Role;
use Validator;
use DB;

class AdminHubsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        // $hubs = Hub::with('country', 'state','attachment')->whereHas('state', function ($query) {
        //     $query->select('name');
        // })->paginate('10');
        $keyword = isset($_GET['keyword'])? $_GET['keyword'] : '';
        $query = Hub::with('country', 'attachment')->leftJoin('states', 'states.id','=','hubs.state_id')->select('hubs.id','hubs.country_id','hubs.state_id','hubs.image_id', 'hubs.title','states.name');
        if((isset($_GET['keyword'])) && (!empty($_GET['keyword']))){
            $query->Where(function ($q) use ($keyword) {
            $q->where('hubs.title', 'LIKE', '%' . $keyword . '%')
                ->where('hubs.title', 'LIKE', '%' . $keyword . '%')
                ->orWhere('states.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        $hubs = $query->orderBy('states.name', 'ASC')->paginate(12);
        // $hubs  = Hub::with('country', 'state', 'attachment')->leftJoin('states', 'states.id','=','hubs.state_id')->select('hubs.id','hubs.country_id','hubs.state_id','hubs.image_id', 'hubs.title','states.name')->orderBy('states.name','ASC')->paginate(10);
        // echo '<pre>';
        // print_r($hubs);
        // echo '</pre>';
        // die();
        return view('user::hubs.index',compact('hubs'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $countries = Country::all();
        
        $states = [];

        if(!empty($countries)){
            $states = State::where('country_id',$countries[0]->id)->orderBy('name','ASC')->get();
        }


        return view('user::hubs.create',compact('countries','states'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'title' => 'required',
                    'image' => 'required',
                    'autocomplete' => "required",
                    'country' => "required",
                    "state" => "required",
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }
            $state_id = '';
            $city_id = '';
            $country = Country::where('id', $request->country)->first();
            if($country->name != $request->searchcountry){
                return redirect()->back()->with('error', "Opps! your selected country and search country doesn't matched. please search in same country.");  
            }
            $country_id = $request->country;
            $state = State::where('name', $request->state)->where('country_id',$country_id)->first();
            if($state){
                $state_id = $state->id;
            }
            else{
                return redirect()->back()->with('error', $request->state." does not exist in our db");  
            }

            $city = City::where('name', $request->city)->where('country_id',$country_id)->first();
            if($city){
                $city_id = $city->id;
            }
            else{
                return redirect()->back()->with('error', $request->city." does not exist in our db");  
            }

            
            $countryExist = MapHubCountryRole::where('country_id',$country_id)->first();
            if(!$countryExist){
                $roles = Role::select('role_id')->whereNotIn('slug',['super_admin','admin','voyagers'])->orderBy('order')->get();

                if($roles){
                    foreach($roles as $key=>$role){
                        $mapHub = new MapHubCountryRole;
                        $mapHub->country_id = $country_id;
                        $mapHub->role_id = $role->role_id;
                        $mapHub->is_active = 1;
                        $mapHub->save();
                    }
                }

            }
            $newHub = new Hub;
            $newHub->image_id = $this->uploadImage($request->file('image'));
            $newHub->title = $request->title;
            $newHub->country_id = $country_id;
            $newHub->state_id = $state_id;
            $newHub->city_id = $city_id;
            $newHub->radius = $request->radius;
            $newHub->autocomplete = $request->autocomplete;
            $newHub->latitude = $request->latitude;
            $newHub->longitude = $request->longitude;
            $newHub->save();

            $radiusMiles = $request->radius;
            $lat = $request->latitude;
            $lng = $request->longitude;
            $hubCities = DB::table("cities")
                        ->select("cities.id","cities.name", \DB::raw("3956 * acos(cos(radians(" . $lat . "))
                        * cos(radians(cities.latitude)) 
                        * cos(radians(cities.longitude) - radians(" . $lng . ")) 
                        + sin(radians(" .$lat. ")) 
                        * sin(radians(cities.latitude))) AS distance"))
                        ->having('distance', '<=', $radiusMiles)
                        ->get();
            if($hubCities){
                foreach($hubCities as $key=>$city){
                    $getHubs = UserTempHub::select('id','user_id')->where('city_id',$city->id)->get();
                    if($getHubs){
                        foreach($getHubs as $key=>$tempHub){
                            $user_id = $tempHub->user_id;
                            $userExistHub = UserSelectedHub::where('user_id',$user_id)->where('hub_id',$newHub->id)->first();
                            if(!$userExistHub){
                                $userSelectedHub = new UserSelectedHub;
                                $userSelectedHub->user_id = $user_id;
                                $userSelectedHub->hub_id = $newHub->id;
                                $userSelectedHub->save();
                                $tempHub = UserTempHub::find($tempHub->id);
                                if($tempHub){
                                    $tempHub->delete();
                                }
                            }
                        }
                    }
                }
            }

            
            $message = "Hub added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
            //return redirect()->back()->with('error', "Something went wrong");   
            //return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('user::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $hub = Hub::where('id',$id)->with("attachment")->with('country')->with('state')->with('city')->first();
        $countries = Country::all();
        
        // $states = [];

        // if(!empty($countries)){
        //     $states = State::where('country_id',$countries[0]->id)->orderBy('name','ASC')->get();
        // }

        return view('user::hubs.edit',compact('hub','id','countries'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'title' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];
            $updatedTempData = [];

            if($request->file('image')){
                $updatedData['image_id'] = $this->uploadImage($request->file('image'));    
            }
            
            $country_id = '';
            $state_id = '';
            $city_id = '';
            $country = Country::where('name', $request->country)->first();
            if($country){
                $country_id = $country->id;
            }
            else{
                return redirect()->back()->with('error', $request->country." does not exist in our db");  
            }

            $state = State::where('name', $request->state)->where('country_id',$country_id)->first();
            if($state){
                $state_id = $state->id;
            }
            else{
                return redirect()->back()->with('error', $request->state." does not exist in our db");  
            }

            $city = City::where('name', $request->city)->where('country_id',$country_id)->first();
            if($city){
                $city_id = $city->id;
            }
            else{
                return redirect()->back()->with('error', $request->city." does not exist in our db");  
            }

            if($request->file('image')){
                if(!empty($request->file('image'))){
                    $updatedData['image_id'] = $this->uploadImage($request->file('image'));  
                }  
            }
           
            $updatedData['title'] = $request->title;
            $updatedData['country_id'] = $country_id;
            $updatedData['state_id'] = $state_id;
            $updatedData['city_id'] = $city_id;
            $updatedData['radius'] = $request->radius;
            //$updatedData['autocomplete'] = $request->autocomplete;
            $updatedData['latitude'] = $request->latitude;
            $updatedData['longitude'] = $request->longitude;
            
            Hub::where('id',$id)->update($updatedData);

            $radiusMiles = $request->radius;
            $lat = $request->latitude;
            $lng = $request->longitude;
            $hubCities = DB::table("cities")
                        ->select("cities.id","cities.name", \DB::raw("3956 * acos(cos(radians(" . $lat . "))
                        * cos(radians(cities.latitude)) 
                        * cos(radians(cities.longitude) - radians(" . $lng . ")) 
                        + sin(radians(" .$lat. ")) 
                        * sin(radians(cities.latitude))) AS distance"))
                        ->having('distance', '<=', $radiusMiles)
                        ->get();
            if($hubCities){
                foreach($hubCities as $key=>$city){
                    $getHubs = UserTempHub::select('id','user_id')->where('city_id',$city->id)->get();
                    if($getHubs){
                        foreach($getHubs as $key=>$tempHub){
                            $user_id = $tempHub->user_id;
                            $userExistHub = UserSelectedHub::where('user_id',$user_id)->where('hub_id',$id)->first();
                            if(!$userExistHub){
                                $userSelectedHub = new UserSelectedHub;
                                $userSelectedHub->user_id = $user_id;
                                $userSelectedHub->hub_id = $id;
                                $userSelectedHub->save();

                                $tempHub = UserTempHub::find($tempHub->id);
                                if($tempHub){
                                    $tempHub->delete();
                                }
                            }
                            
                        }
                    }
                }
            }
            // echo '<pre>';
            // print_r($hubCities);
            // die();
            $message = "Hub updated successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
            //return redirect()->back()->with('error', "Something went wrong");   
            //return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $hub = Hub::find($id);
        if($hub){
            $hub->delete();
            UserSelectedHub::where('hub_id',$id)->delete();
            $message = "Hub deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "Hub can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }
}
