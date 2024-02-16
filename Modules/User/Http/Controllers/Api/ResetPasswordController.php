<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use App\Events\ForgotPassword;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Validator;
use DB;
use Cache;
use Hash;

class ResetPasswordController extends CoreController
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;

    /** 
     * Forgot password api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function forgotPassword(Request $request) 
    {
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|email', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()], $this->validationStatus);            
            }

            $userDetail = User::where('email', $request->email)->first();
            if(!empty($userDetail))
            {
                
                $forgotKey = random_int(0, 999999);
                $forgotKey = str_pad($forgotKey, 6, 6, STR_PAD_LEFT);

                $userDetail->otp = $forgotKey;
                $userDetail->save();

                //Send Otp Over Mail
                
                event(new ForgotPassword($userDetail->user_id,$forgotKey));

                $message = $this->translate('messages.Reset password otp has been sent on your email','Reset password otp has been sent on your email');

                return response()->json(['success' => $this->successStatus,
                                         'message' => $message,
                                         'otp' => $forgotKey,
                                        ], $this->successStatus); 
            }
            else
            {
                $message = $this->translate("messages.Invalid email","Invalid email");
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>$message], $this->exceptionStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        } 
    }

    /** 
     * Verify OTP 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function verifyForgotPasswordOtp(Request $request) 
    { 
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|email', 
                'otp' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first()], $this->validationStatus);            
            }

            $userDetail = User::where('email', $request->email)->where('otp', $request->otp)->first();
            if(!empty($userDetail))
            {
                $userDetail->otp = null;
                $userDetail->verified_at = date('Y-m-d H:i:s');
                $userDetail->verified_via = 'email';
                $userDetail->save();

                $message = "Otp has been verified";//$this->translate("messages.OTP Verified","OTP Verified");

                return response()->json(['success' => $this->successStatus,
                                         'message' => $message,
                                        ], $this->successStatus); 
            }
            else
            {
                $message = $this->translate("messages.Invalid OTP","Invalid OTP");
                return response()->json(['success'=>$this->validationStatus,'errors' =>$message], $this->validationStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /** 
     * Reset Password after Otp verified api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function resetPassword(Request $request) 
    { 
        try
        {
            $validator = Validator::make($request->all(), [  
                'email' => 'required|email', 
                'password' => 'required|min:8', 
                'confirm_password' => 'required|same:password',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first()], $this->validationStatus);            
            }

            $userDetail = User::where('email', $request->email)->first();
            if(!empty($userDetail))
            {
                $userDetail->password = bcrypt($request->password); 
                $userDetail->save();

                $message = $this->translate("passwords.reset");

                return response()->json(['success' => $this->successStatus,
                                         'message' => $message
                                         //'data' => $userDetail,
                                        ], $this->successStatus); 
            }
            else
            {
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>'The token has been expired'], $this->exceptionStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }

    /** 
     * Change Password
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function changePassword(Request $request) 
    {  
        try 
        {
            $validator = Validator::make($request->all(), [  
                'old_password' => 'required', 
                'new_password' => 'required', 
                //'confirm_password' => 'required|same:new_password',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()], $this->validationStatus);            
            }

            $userId = Auth()->user()->user_id;
            if(!empty($userId))
            {

                $userUpdate = User::where('user_id', $userId)->first();

                if (Hash::check($request->old_password, $userUpdate->password)) {
                    $userUpdate->password = bcrypt($request->new_password); 
                    $userUpdate->save();


                    return response()->json(['success' => $this->successStatus,
                                             'message' => $this->translate("passwords.reset"),
                                            ], $this->successStatus);
                }else{
                    return response()->json(['success'=>$this->validationStatus,'errors' =>$this->translate("passwords.old_password")], $this->validationStatus); 
                }

                 
            }
            else
            {
                return response()->json(['success'=>$this->validationStatus,'errors' =>$this->translate("passwords.user")], $this->validationStatus); 
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
    }
}
