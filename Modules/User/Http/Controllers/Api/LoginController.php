<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\User\Entities\UserSelectedHub;
use Modules\User\Entities\DeviceToken;
use Modules\User\Entities\UserTempHub;
use Validator;
use DB;
use Cache;
use Hash;

class LoginController extends CoreController
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;
    
    public $userFieldsArray = ['user_id', 'name', 'email','website','locale','display_name','first_name','last_name','middle_name','account_enabled','phone','postal_code','last_login_date','roles'];
    /** 
     * Login
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(Request $request){
        
        try{
            $input = [];

            if($request->getUser() || $request->getPassword()){
                $input['name']  = $request->getUser();
                $input['password']  = $request->getPassword();
            }
            
            $validator = Validator::make($input, [ 
                'name' => 'required', 
                'password' => 'required',
                //'device_type' => 'required',
                //'device_token' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            //Check Auth 
            if (Auth::attempt(array('email' => $request->getUser(), 'password' => $request->getPassword()), true)){ 
                if(Auth::user()->role_id == 1)
                {
                    $mess = 'You are not allowed to login';
                    $message = $this->translate('messages.'.$mess,$mess);
                        
                    return response()->json(['error'=> $message], 401);  
                }
                else
                {
                    $user = Auth::user(); 
                
                    /*if($user->account_enabled == 'active')
                    {*/
                    $UserSelectedHub = UserSelectedHub::where('user_id', $user->user_id)->count();
                    $UserTempHub = UserTempHub::where('user_id', $user->user_id)->count();
                    if($UserSelectedHub > 0 || $UserTempHub > 0)
                    {
                        $isHubSelected = true;
                    }
                    else
                    {
                        $isHubSelected = false;
                    }
                    $checkMyStoreExist = MarketplaceStore::where('user_id', $user->user_id)->first();
                    if(empty($checkMyStoreExist))
                    {
                        $isStoreCreated = 0;
                    }
                    else
                    {
                        $isStoreCreated = 1;
                    }
                    $userData = User::select('*','name as username')->with('roles','avatar_id','cover_id')->where('user_id', $user->user_id)->first();
                    $userData->is_hub_selected = $isHubSelected;
                    $userData->is_store_created = $isStoreCreated;
                    
                    //DeviceToken::where('user_id', $userData->user_id)->delete();
                    if(!empty($request->device_token))
                    {
                        $userToken = new DeviceToken;
                        $userToken->user_id = $userData->user_id;
                        $userToken->device_type = $request->device_type;
                        $userToken->device_token = $request->device_token;
                        $userToken->save();
                    }
                    /**/
                        Auth::user()->roles;
                        
                        $token =  $user->createToken('alysei')->accessToken;
                        return response()->json(['success' => $this->successStatus,
                                             //'data' => $user->only($this->userFieldsArray),
                                             'data' => $userData,
                                             'token'=> $token,
                                             'otp' => $user->account_enabled
                                            ], $this->successStatus); 
                    /*}else{

                        $message = $this->translate('messages.'.$user->account_enabled,$user->account_enabled);
                        
                        return response()->json(['error'=> $message], 401);  
                    }*/
                }
            } 
            else{ 

                $message = $this->translate('messages.'."login_failed","Login Failed");

                return response()->json(['error'=> $message], 401); 
            }
            
        }catch(\Exception $e){
            return response()->json(['success'=>$this->validationStatus,'errors' =>$e->getMessage()], $this->validationStatus);
        }
    }


    /***
    logout 
    ***/
    public function logout(Request $request)
    {
        $mes = 'Logout successfully';
        $message = $this->translate('messages.'.$mes,$mes);

        $user = Auth::user();

        if($request->device_token){
            $deviceToken = DeviceToken::where('device_token',$request->device_token)->first();
            if($deviceToken){
                $deviceToken->delete();
            }
        }

        //DeviceToken::where('user_id', $user->user_id)->delete();
        $token = $request->user()->token();
        $token->revoke();
        return response()->json(['success' => $this->successStatus,
                                 'message' => $message,
                                ], $this->successStatus); 
    }

    /***
    Alysei Progress 
    ***/
    public function alyseiProgress(Request $request)
    {
        try
        {
            $user = Auth::user();
            
            $userData = User::select('user_id','email','role_id','alysei_review','alysei_certification','alysei_recognition','alysei_qualitymark')->where('user_id', $user->user_id)->first();
            
            if(!empty($userData))
            {
                $alyseiReview = ($userData->alysei_review > 0) ? true : false;
                $alyseiCertification = ($userData->alysei_certification > 0) ? true : false;
                $alyseiRecognition = ($userData->alysei_recognition > 0) ? true : false;
                $alyseiQuality = ($userData->alysei_qualitymark > 0) ? true : false;
                
               
                $profileReview = ['title' => $this->translate('messages.'.'Review','Review'),'status' => $alyseiReview, 'description' => $this->translate('messages.'.'Your account has been reviewed by our staff.','Your account has been reviewed by our staff.')];

                $profileCertified = ['title' => $this->translate('messages.'.'Alysei Certification','Alysei Certification'),'status' => $alyseiCertification, 'description' => $this->translate('messages.'.'You are now a Certified Alysei Member.','You are now a Certified Alysei Member.')];

                $profileRecognised = ['title' => $this->translate('messages.'.'Recognition','Recognition'),'status' => $alyseiRecognition, 'description' => $this->translate('messages.'.'You are within the top 10 most searched Alysei Members.','You are within the top 10 most searched Alysei Members.')];

                $profileQualityMarked = ['title' => $this->translate('messages.'.'Quality Mark','Quality Mark'),'status' => $alyseiQuality, 'description' => $this->translate('messages.'.'You are within the top 5 highest rated Alysei Members.','You are within the top 5 highest rated Alysei Members.')];

                $dataProgress = [$profileReview, $profileCertified, $profileRecognised, $profileQualityMarked];
               
                return response()->json(['success' => $this->successStatus,
                                         'data' => $userData,
                                         'alysei_progress' => $dataProgress
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success'=>false,'errors' =>['exception' => 'Unauthorised']], $this->unauthorisedStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->unauthorisedStatus); 
        } 
        
    }


}
