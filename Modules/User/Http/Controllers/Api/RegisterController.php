<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken;
use App\Attachment;
use App\Notification;
use Modules\User\Entities\City;
use Modules\User\Entities\Hub;
use Validator;
use Str;
use DB;
use Kreait\Firebase\Factory;
use Cache;
use App\Events\Welcome;
use App\Events\VerifyEmail;
use App\Http\Traits\NotificationTrait;
use Modules\User\Entities\Walkthrough;
use App\Http\Traits\SortArray;
use Modules\User\Entities\WalkThroughPoint; 
use Carbon\Carbon;

class RegisterController extends CoreController
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    use NotificationTrait;
    use SortArray;

    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();    
        return $database;
    }

    public function addUserOrUpdateInFirebase($user_id, $name)
    {
        try{
            $data = $this->conn_firbase()->getReference('users/'.$user_id)
            ->update([
            'user_id' => $user_id,
            'name' => $name,
            'alysei_approval' => false,
            'notification' => 0,
            'url' => ''
            ]);

            return $data;
        }catch(\Exception $e){
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
                }
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
            }
            else{
                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => 0
                ]);
            }
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /* 
        Get Registration Roles
        Get Registration Roles Except Super Admin, Admin, Impoters & Distributors
    */
    public function getRoles(Request $request){

        try{
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $response_time = (microtime(true) - LARAVEL_START)*1000;
            $roles = Role::select('role_id','name','slug','display_name','description_'.$lang.' as description','image_id')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->with("attachment")->orderBy('order')->get();

            $importerRoles = Role::select('role_id','name','slug','display_name','image_id')->whereNotIn('slug',['super_admin','admin','Italian_F_and_B_Producers','voice_of_expert','travel_agencies','restaurents','voyagers'])->with("attachment")->get();
            

            foreach ($roles as $key => $role) {
                $roles[$key]->name = $this->translate('messages.'.$roles[$key]->name,$roles[$key]->name);
                $roles[$key]->image = "public/images/roles/".$role->slug.".jpg";

                if($roles[$key]->name == "US Importers & Distributers")
                {
                    $roles[$key]->name = $this->translate('messages.'.'US Importers & Distributors','US Importers & Distributors');
                }

                $roles[$key]->description = $this->translate('messages.'.$role->description,$role->description);
            }

            foreach ($importerRoles as $key => $role) {
                if($importerRoles[$key]->slug == "Importer_and_Distributer")
                {
                    $importerRoles[$key]->name = $this->translate('messages.'.'Importer & Distributor','Importer & Distributor');
                }
                else
                {
                    $importerRoles[$key]->name = $this->translate('messages.'.$importerRoles[$key]->name,$importerRoles[$key]->name);
                }

                $importerRoles[$key]->image = "public/images/roles/".$role->slug.".png";
            }


            $data = ['roles'=> $roles,'importer_roles' => $importerRoles, 'title' => $this->translate('messages.'.'Select your role','Select your role'),
                'subtitle' => $this->translate('messages.'.'Join Alysei Today','Join Alysei Today'),
                'description' => $this->translate('messages.'.'Become an Alysei Member by signing up for the Free Trial Beta Version, Your access request will be subject to approval.','Become an Alysei Member by signing up for the Free Trial Beta Version, Your access request will be subject to approval.')];

            return response()->json(['success'=>$this->successStatus,'data' =>$data],$this->successStatus); 

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Get Walk Through Screens
     * @Params $request and $roleId
     */
    public function getWalkThroughScreens(Request $request,$roleId = 0){

        try{

            $response_time = (microtime(true) - LARAVEL_START)*1000;

            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }

            
            $screens = Walkthrough::select('title_'.$lang.' as title','description_'.$lang.' as description','order','role_id','image_id','walk_through_screen_id')
                        ->where('role_id','=',$roleId)->where('type','alysei')->with('attachment')
                        ->orderBy('order','asc')->get();

            foreach ($screens as $key => $screen) {
                 $screens[$key]->title = $this->translate('messages.'.$screen->title,$screen->title);
                 $screens[$key]->description = $this->translate('messages.'.$screen->description,$screen->description);
                //$attachment = Attachment::where('id', $screen->image_id)->first();
            }

            return response()->json(['success'=>$this->successStatus,'data' =>$screens,'response_time'=>$response_time],$this->successStatus); 

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    public function generate_unique_username($name)
    {
        $new_username   = $name;

        $query = \DB::table("users")->select('count(id) as user_count')
        ->where("name",'LIKE',"'%'".$new_username."'%'")
        ->get();
        $count = $query->user_count;

        if(!empty($count)) {
            $new_username = $new_username . $count;
        }

        return $new_username;
    }

    /*
     * Register 
     * @params $request 
     */
    public function register(Request $request){

        try{
            $stateId='';
            $input = $request->all();
            $rules = [];
            $getIds = null;
            $rules['role_id'] = 'required';
            /*$rules['device_type'] = 'required';
            $rules['device_token'] = 'required';*/
            $language = $request->header('locale');
            if(empty($language)){
                $language = 'en';
            }
            $validator = Validator::make($input, $rules);

            if ($validator->fails()) { 
                return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
            }

            $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin','Importer_and_Distributer'])->orderBy('order')->get();
            if(count($roles) > 0)
            {
                $getRolesId = $roles->pluck('role_id')->toArray();
                array_push($getRolesId,6);
                $getIds = implode(",",$getRolesId);
            }
            $roleFields = $this->checkFieldsByRoleId($input['role_id']);

            if(count($roleFields) == 0){
                return response()->json(['success'=>$this->validationStatus,'errors' =>'Sorry,There are no fields for current role_id'], $this->validationStatus);
            }else{

                $rules = $this->makeValidationRules($roleFields);
                $inputData = $this->segregateInputData($input,$roleFields);
            }

            if(!empty($rules) && !empty($inputData)){
                $rules['email'] = 'required|email|unique:users';
                $roles['company_name'] = 'required|unique:users';
                $validator = Validator::make($inputData, $rules);

                if ($validator->fails()) { 

                    return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
                }

                if(array_key_exists('email',$inputData) && array_key_exists('password',$inputData)
                  ){

                    $userData = [];
                    $userData['email'] = $inputData['email'];
                    //$userData['name'] = $inputData['email'];
                    $userData['password'] = bcrypt($inputData['password']);
                    $userData['role_id'] = $input['role_id'];
                    $userData['otp'] = $this->generateOTP();
                    $userData['otp_expired'] = now()->addDays(2);

                    $userData['account_enabled'] = "incomplete";
                     
                    if(array_key_exists('first_name',$inputData) && array_key_exists('last_name',$inputData)
                      ){
                        $userData['first_name'] = $inputData['first_name'];
                        $userData['last_name'] = $inputData['last_name'];

                        //$userData['name'] = ucwords(strtolower($inputData['first_name'])).' '.ucwords(strtolower($inputData['last_name']));
                    }
                    $userData['locale'] = $language;
                    if(array_key_exists('timezone',$input) && array_key_exists('locale',$input)
                      ){
                        $userData['timezone'] = $input['timezone'];
                    }
                    if(array_key_exists('vat_no',$inputData)){
                        $userData['vat_no'] = $inputData['vat_no'];
                        
                    }
                    if(array_key_exists('company_name',$inputData)){
                        $userData['company_name'] = $inputData['company_name'];
                        
                    }
                    if(array_key_exists('restaurant_name',$inputData)){
                        $userData['restaurant_name'] = $inputData['restaurant_name'];
                        
                    }
                    if(array_key_exists('country',$inputData)){
                        $userData['country_id'] = $inputData['country'];
                        
                    }
                    if(array_key_exists('state',$inputData)){
                        $userData['state'] = $inputData['state'];
                        
                    }
                    if(array_key_exists('lattitude',$inputData)){
                        $userData['lattitude'] = $inputData['lattitude'];
                        
                    }
                    if(array_key_exists('longitude',$inputData)){
                        $userData['longitude'] = $inputData['longitude'];
                        
                    }
                    if(array_key_exists('address',$inputData)){
                        $userData['address'] = $inputData['address'];
                        
                    }

                    if(!empty($inputData['first_name']) && !empty($inputData['last_name']))
                    {
                        $userName = (strtolower($inputData['first_name']).' '.strtolower($inputData['last_name']));
                    }
                    elseif(!empty($inputData['company_name']))
                    {
                        $userName = $inputData['company_name'];
                    }
                    elseif(!empty($inputData['restaurant_name']))
                    {
                        $userName = $inputData['restaurant_name'];   
                    }

                    $new_username = strtolower(str_replace(' ', '_', $userName));
                    
                    $query = DB::table('users')->select(DB::raw('count(user_id) as user_count'))->where('name','LIKE','%'.$new_username.'%')->first();
                    
                    
                    $count = $query->user_count;
                    //return $count;

                    if(!empty($count)) {
                        $new_username = $new_username . rand();
                    }
                    else
                    {
                        $new_username;
                    }

                    //return $new_username;
                    $userData['name'] = $new_username;
                    $user = User::create($userData); 
                    User::where('user_id', $user->user_id)->update(["who_can_connect" => $getIds]);
                    
                    if($user){

                        foreach ($input as $key => $value) {
                            if($key == 'role_id' || $key == 'timezone' || $key == 'locale'){
                                continue;
                            }

                            $checkMultipleOptions = explode(',', $value);

                            if(count($checkMultipleOptions) == 1)
                            {
                                $data = [];
                                if(!empty($key))
                                {
                                    /*if($key == 28)
                                    {
                                        $stateId = $value;
                                    }
                                    if($key == 32)
                                    {
                                        $createdCityId = $this->createNewCityForApproval($key, $value, $stateId);
                                        $data['user_field_id'] = $key;
                                        $data['user_id'] = $user->user_id;
                                        $data['value'] = $createdCityId;
                                    }
                                    else
                                    {*/
                                        if($key == 13)
                                        {
                                            $data['table_name'] = 'countries';    
                                        }
                                        if($key == 28)
                                        {
                                            $data['table_name'] = 'states';    
                                        }
                                        if($key == 29)
                                        {
                                            $data['table_name'] = 'cities';    
                                        }
                                        $data['user_field_id'] = $key;
                                        $data['user_id'] = $user->user_id;
                                        $data['value'] = trim($value);
                                    //}
                                    DB::table('user_field_values')->insert($data);
                                }
                                
                            }else{

                                foreach($checkMultipleOptions as $option){
                                    $data = [];
                                    if(!empty($key))
                                    {
                                        $data['user_field_id'] = $key;
                                        $data['user_id'] = $user->user_id;
                                        $data['value'] = $option;
                                        DB::table('user_field_values')->insert($data);
                                    }
                                    
                                }
                            }
                            

                            
                        }
                        if(!empty($user->first_name) && !empty($user->last_name))
                        {
                            $userName = ucwords(strtolower($user->first_name).' '.strtolower($user->last_name));
                        }
                        elseif(!empty($user->company_name))
                        {
                            $userName = ucwords($user->company_name);
                        }
                        elseif(!empty($user->restaurant_name))
                        {
                            $userName = ucwords($user->restaurant_name);   
                        }
                        //DeviceToken::where('user_id', $user->user_id)->delete();
                        if(isset($input['device_token']) && !empty($input['device_token']))
                        {
                            $deviceInfo = [];
                            $deviceInfo['user_id'] = $user->user_id;
                            $deviceInfo['device_type'] = $input['device_type'];
                            $deviceInfo['device_token'] = $input['device_token'];

                            DeviceToken::create($deviceInfo);
                        }
                          
                        if($input['role_id'] == 10){
                            $hubs = Hub::where('status','1')->get();
                            if($hubs){
                                foreach($hubs as $hub){
                                    DB::table('user_selected_hubs')->insert(['user_id'=>$user->user_id,'hub_id'=>$hub->id]);
                                }
                            }
                            
                        }

                        // if($input['role_id'] == 10)
                        // {
                        //     //Send verify eMail OTP
                    
                        //     event(new VerifyEmail($user->user_id));

                        //     $this->addUserOrUpdateInFirebase($user->user_id, $userName);
                        //     return response()->json(['success' => $this->successStatus,
                        //                 'message' => 'OTP has been sent on your email ID',
                        //                 'data' => $user->only($this->userFieldsArray)                  
                        //             ], $this->successStatus);
                        // }
                        // else
                        // {
                            //Send verify eMail OTP
                    
                            event(new VerifyEmail($user->user_id));
                            $this->addUserOrUpdateInFirebase($user->user_id, $userName);
                            $token =  $user->createToken('alysei')->accessToken; 
                            //Send Welcome Mail
                    
                             //event(new Welcome($user->user_id, $language));
                            // $title = "Thank you ".$userName." for submitting your request. Alysei Team will review it and will provide a response as soon as possible.";

                            // $admin = User::where('role_id', '1')->first();

                            // $saveNotification = new Notification;
                            // $saveNotification->from = $admin->user_id;
                            // $saveNotification->to = $user->user_id;
                            // $saveNotification->notification_type = 'progress';
                            // $saveNotification->title = $this->translate('messages.'.$title,$title);
                            // $saveNotification->redirect_to = 'membership_progress';
                            // $saveNotification->redirect_to_id = 0;
                            // $saveNotification->sender_name = "Admin";
                            // $saveNotification->save();

                            // $tokens = DeviceToken::where('user_id', $user->user_id)->get();
                            // if(count($tokens) > 0)
                            // {
                            //     $collectedTokenArray = $tokens->pluck('device_token');

                            //     //$this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,null,null,null,null,null,null,null,null,null,null);

                            //     //$this->sendNotificationToIOS($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,null,null,null,null,null,null,null,null,null,null);
                                
                            // }
                            //$this->updateUserNotificationCountFirebase($user->user_id);
                            // return response()->json(['success' => $this->successStatus,
                            //              'data' => $user->only($this->userFieldsArray),
                            //              'account_enabled' => $user->account_enabled
                            //             ], $this->successStatus);

                            return response()->json(['success' => $this->successStatus,
                                        'data' => $user->only($this->userFieldsArray),
                                        'token' => $token,
                                        'account_enabled' => $user->account_enabled
                                       ], $this->successStatus);
                        // }
                         

                    }
                    else{
                        return response()->json(['success' => $this->exceptionStatus,
                                     'errors' => ['Something went wrong'],
                                    ], $this->exceptionStatus); 
                    }

                }
                
            }

            

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        
    }


    /** 
     * Verify Otp api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function verifyOtp(Request $request) 
    { 
        try
        {
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }

            $validator = Validator::make($request->all(), [  
                'email' => 'required|max:190|email', 
                'otp' => 'required',
            ]);


            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first()], $this->validationStatus);            
            }

            $userDetail = User::with('roles')->where('email', $request->email)->where('otp', $request->otp)->first();
            if(!empty($userDetail))
            {
                
                $nowDateTime = date('Y-m-d H:i:s');
                if($userDetail->otp_expired >= $nowDateTime){
                    $userDetail->otp = null;
                    $userDetail->otp_expired = null;
                    $userDetail->account_enabled = 'active';
                    $userDetail->save();


                    $message = $this->translate("messages.OTP Verified","OTP Verified");
                    Auth::loginUsingId($userDetail->id);
                    //Auth::user()->roles;
                    $token =  $userDetail->createToken('yss')->accessToken; 
                    $userName = '';
                    if(!empty($userDetail->first_name) && !empty($userDetail->last_name))
                    {
                        $userName = ucwords(strtolower($userDetail->first_name).' '.strtolower($userDetail->last_name));
                    }
                    elseif(!empty($userDetail->company_name))
                    {
                        $userName = ucwords($userDetail->company_name);
                    }
                    elseif(!empty($userDetail->restaurant_name))
                    {
                        $userName = ucwords($userDetail->restaurant_name);   
                    }

                    event(new Welcome($userDetail->user_id,$lang));

                    if($userDetail->roles->role_id !== 10){
                        $title = "Thank you ".$userName." for submitting your request. Alysei Team will review it and will provide a response as soon as possible.";    
                        $title_it = "Grazie ".$userName." per aver inviato la tua richiesta. Il Team Alysei la esaminerà e ti risponderà il prima possible.";    
                    }else{
                        $title = "Welcome on board! Please complete your profile in order to access Alysei.";
                        $title_it = "Benvenuto a bordo! Completa il tuo profilo per accedere alla piattaforma Alysei.";
                    }
                    

                    $admin = User::where('role_id', '1')->first();

                    $notificationCount = Notification::where('to',$userDetail->user_id)->where('title_en',$title)->count();
                    if($notificationCount == 0){
                        $saveNotification = new Notification;
                        $saveNotification->from = $admin->user_id;
                        $saveNotification->to = $userDetail->user_id;
                        $saveNotification->notification_type = 'progress';
                        $saveNotification->title_it = $title_it;
                        $saveNotification->title_en = $title;
                        $saveNotification->redirect_to = 'membership_progress';
                        $saveNotification->redirect_to_id = 0;
                        //$saveNotification->sender_name = "Admin";
                        $saveNotification->sender_name = "";
                        $saveNotification->save();
                    }
                    

                    $tokens = DeviceToken::where('user_id', $userDetail->user_id)->get();
                    if(count($tokens) > 0)
                    {
                        $collectedTokenArray = $tokens->pluck('device_token');

                    }
                    $this->updateUserNotificationCountFirebase($userDetail->user_id);
                    
                    

                    // return response()->json(['success' => $this->successStatus,
                    //                      'data' => $userDetail->only($this->userFieldsArray),
                    //                      'token'=> $token,
                    //                      'otp' => 'active',
                    //                     ], $this->successStatus); 
                    return response()->json(['success' => $this->successStatus,
                                        'data' => $userDetail->only($this->userFieldsArray),
                                        'token'=> $token,
                                        'otp' => 'active',
                                        ], $this->successStatus); 
                }
                else{
                    $message = $this->translate("OTP has been expired!","OTP has been expired!");
                    return response()->json(['success'=>$this->validationStatus,
                                        'message' => $message
                                    ], $this->validationStatus); 
                }
                
            }
            else
            {
                $message = $this->translate("messages.Invalid otp","Invalid otp");
                return response()->json(['success'=>$this->validationStatus,
                                        'message' => $message
                                    ], $this->validationStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    public function verifyOtpResetPassword(Request $request){
        try
        {
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }

            $validator = Validator::make($request->all(), [  
                'email' => 'required|max:190|email', 
                'otp' => 'required',
            ]);


            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first()], $this->validationStatus);            
            }

            $userDetail = User::with('roles')->where('email', $request->email)->where('otp', $request->otp)->first();
            if(!empty($userDetail))
            {
                
                $nowDateTime = date('Y-m-d H:i:s');
                if($userDetail->otp_expired >= $nowDateTime){
                    $userDetail->otp = null;
                    $userDetail->otp_expired = null;
                    $userDetail->account_enabled = 'active';
                    $userDetail->save();


                    $message = $this->translate("messages.OTP Verified","OTP Verified");
                   
                    return response()->json(['success' => $this->successStatus,
                                        'message'=> $message
                                        ], $this->successStatus); 
                }
                else{
                    $message = $this->translate("OTP has been expired!","OTP has been expired!");
                    return response()->json(['success'=>$this->validationStatus,
                                        'message' => $message
                                    ], $this->validationStatus); 
                }
                
            }
            else
            {
                $message = $this->translate("messages.Invalid otp","Invalid otp");
                return response()->json(['success'=>$this->validationStatus,
                                        'message' => $message
                                    ], $this->validationStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /** 
     * Resend Otp api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function resendOtp(Request $request) 
    { 
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|max:190|email', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first()], $this->validationStatus);            
            }

            $userDetail = User::where('email', $request->email)->first();
            if(!empty($userDetail))
            {
                $userDetail->otp = $this->generateOTP();
                //$userDetail->otp = "123456";
                $userDetail->otp_expired = now()->addDays(2);
                $userDetail->save();

                event(new VerifyEmail($userDetail->user_id));
                return response()->json(['success' => $this->successStatus,
                                         'message' => 'OTP has been sent!'
                                        ], $this->successStatus); 
            }
            else
            {
                return response()->json(['success'=>$this->validationStatus,
                                        'errors' => 'Invalid email ID'
                                    ], $this->validationStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Rendering Registration Form According to Roles
     * @Params $role_id
     */
    public function getRegistrationFormFields(Request $request,$role_id)
    {
        try{    
                $response_time = (microtime(true) - LARAVEL_START)*1000;
                $steps = Cache::get('registration_form');

                if($role_id && (env("cache") == false) || $steps==null){
                    $steps = [];
                    $roleFields = DB::table('user_field_map_roles')
                                      ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                                      ->where("role_id","=",$role_id)
                                      ->where("display_on_registration","=",'true')
                                      ->where("conditional","=",'no')
                                      ->orderBy("order","asc")
                                      ->get();

                    $importerRoles = Role::select('role_id','name','slug','display_name')->whereNotIn('slug',['super_admin','admin','Italian_F_and_B_Producers','voice_of_expert','travel_agencies','restaurents','voyagers'])->get();                  

                    if($roleFields){
                        foreach ($roleFields as $key => $value) {
                            $data = [];
                            
                            $roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);
                            $roleFields[$key]->placeholder = $this->translate('messages.'.$value->placeholder,$value->placeholder);

                            $roleFields[$key]->hint = $this->translate('messages.'.$value->hint,$value->hint);
                            //Set Locale
                            if($role_id == 3 && $value->user_field_id == 28)
                            {
                                $roleFields[$key]->title = $this->translate('messages.'.'Region','Region');
                            }
                            if(($role_id == 6 || $role_id == 4 || $role_id == 5 || $role_id == 9 ) && $value->user_field_id == 28)
                            {
                                $roleFields[$key]->title = $this->translate('messages.'.'State','State');
                            }
                            
                            if($role_id == 3 && $value->user_field_id == 2){
                                $roleFields[$key]->hint = $this->translate('messages.'.'Select the product or products your company produces and that you want to export.','Select the product or products your company produces and that you want to export.');
                            }

                            if(($role_id == 6 || $role_id == 4 || $role_id == 5) && $value->user_field_id == 2){

                                $roleFields[$key]->hint = $this->translate('messages.'.'Select the product or products your company handles.','Select the product or products your company handles.');
                            }
                            //$roleFields[$key]->title = $this->translate('messages.'.$value->title,$value->title);

                            //Check Fields has option
                            if($value->type !='text' && $value->type !='email' && $value->type !='password'){
                                
                                $value->options = $this->getUserFieldOptionParent($value->user_field_id);

                                if(!empty($value->options)){

                                    foreach ($value->options as $k => $oneDepth) {

                                            $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);

                                            //Check Option has any Field Id
                                            $checkRow = DB::table('user_field_maps')->where('user_field_id','=',$value->user_field_id)->where('role_id','=',$role_id)->first();

                                            if($checkRow){
                                                $value->parentId = $checkRow->option_id;
                                            }

                                                $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$oneDepth->user_field_option_id);

                                                $value->options[$k]->options = $data;

                                                
                                                foreach ($value->options[$k]->options as $optionKey => $optionValue) {

                                                    $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id,$optionValue->user_field_option_id);

                                                    $value->options[$k]->options[$optionKey]->options = $options;
                                                }  
                                            

                                    }

                                    //$value->options = $this->MysortArray($value->options, 'option','asc');
                                }

                                //return $value;
                            }
                            // End Check Fields has option
                            $steps[$value->step][] = $value;
                        }
                    }



                    if($role_id == 6){

                        foreach ($importerRoles as $key => $role) {
                            
                                $importerRoles[$key]->name = $this->translate('messages.'.$importerRoles[$key]->display_name,$importerRoles[$key]->display_name);
                            
                            $importerRoles[$key]->image = env("APP_URL")."/images/roles/".$role->slug.".png";
                        }

                        $newArray =  ['type' => 'select','name' => 'role_id','title' => $this->translate('messages.'."Select Role","Select Role"),'required' => 'yes','Placeholder'=>$this->translate('messages.'."Select Role","Select Role"),'options' => $importerRoles];

                        //array_splice( $steps['step_2'], -1, 0, $newArray );
                        array_push($steps['step_2'], $newArray);
                    }

                    Cache::forever('registration_form', $steps);
                 

                }

                return response()->json(['success'=>$this->successStatus,'data' =>$steps,'response_time'=>$response_time], $this->successStatus); 
                
                
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /*
     * Get User Field
     * @params $fieldId = user_field_id 
    */
    public function getUserField($fieldId){

        $fieldData = DB::table('user_fields')
                    ->where('user_field_id','=',$fieldId)
                    ->first();
        return $fieldData;    
        
    }

    /*
     * Get All Fields Option who are child
     * @params $user_field_id 
    */
    public function getUserFieldOptionParent($fieldId){

        $fieldOptionData = [];
        
        if($fieldId > 0){
            $fieldOptionData = DB::table('user_field_options')
                    ->where('user_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->where('deleted_at', null)
                    ->orderBy('weight','ASC')
                    ->orderBy('option','ASC')
                    ->get()->toArray();

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->hint = $this->translate('messages.'.$option->hint,$option->hint);
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }

            //if($fieldId == 2){
                array_multisort(array_column( $fieldOptionData, 'option' ), SORT_ASC, $fieldOptionData);
            //}
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
                ->orderBy('weight','ASC')
                ->orderBy('option','ASC')
                ->get()->toArray();                                

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->hint = $this->translate('messages.'.$option->hint,$option->hint);
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }

            //if($fieldId == 2){
                array_multisort(array_column( $fieldOptionData, 'option' ), SORT_ASC, $fieldOptionData);
            //}
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Check Fields based on role id
     * @Params $roleId
     */

    public function checkFieldsByRoleId($roleId){

        $roleFields = DB::table('user_field_map_roles')
                                  ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                                  ->where("role_id","=",$roleId)
                                  ->where("user_fields.display_on_registration","=",true)
                                  ->orderBy("order","asc")
                                  ->get();

        return $roleFields;
    }

    /*
     * Make Validation Rules
     * @Params $userFields
     */

    public function makeValidationRules($userFields){
        $rules = [];
        foreach ($userFields as $key => $field) {
            
            if($field->name == 'email' && $field->required == 'yes'){

                $rules[$field->name] = 'required|email|unique:users|max:50';

            }else if($field->name == 'password' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:8';

            }else if($field->name == 'first_name' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:3';

            }else if($field->name == 'last_name' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:3';

            }else {

                if($field->required == 'yes'){
                    $rules[$field->name] = 'required';
                }
            }
        }

        return $rules;

    }

    /*
     * Segregate user input data
     * @Params $input and @userFields
     */
    public function segregateInputData($input,$userFields){

        $inputData = [];

        foreach($userFields as $key => $field){
            if(array_key_exists($field->user_field_id, $input)){
                $inputData[$field->name] = $input[$field->user_field_id];
            }
        }

        return $inputData;

    }

    /*
     * Generate OTP
     */ 
    public function generateOTP(){
        $otp = random_int(0, 999999);
        $otp = str_pad($otp, 6, 6, STR_PAD_LEFT);
        return $otp;
        //return 654321;
    }

    /*
     * Create new city for admin approval
     */ 
    public function createNewCityForApproval($key, $value, $stateId)
    {
        $newCity = new City;
        $newCity->name = $value;
        $newCity->state_id = $stateId;
        $newCity->save();

        return $newCity->id;
    }
}