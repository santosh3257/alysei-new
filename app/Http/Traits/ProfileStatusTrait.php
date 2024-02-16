<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\User\Entities\User;
use Modules\User\Entities\FeaturedListing;
use Modules\User\Entities\UserSelectedHub;
use Modules\User\Entities\UserTempHub;
use App\Attachment;
use App\Notification;
use Modules\User\Entities\DeviceToken;
use Illuminate\Support\Facades\Auth; 
use Validator;
use DB;
use App\Http\Traits\NotificationTrait;
//use App\Events\UserRegisterEvent;

trait ProfileStatusTrait
{
    use NotificationTrait;
    /***
    Get Profile Status
    ***/
    public function profileStatus($userId)
    {
        $user = User::where('user_id', $userId)->first();  // 10%
        $FeaturedListing = FeaturedListing::where('user_id', $userId)->get();  // 25%
        $userSelectedHub = UserSelectedHub::where('user_id', $userId)->get();  // 25%
        $userTempHub = UserTempHub::where('user_id', $userId)->get();
       
            $ourProduct = DB::table('user_field_values')
                        ->where('user_id', $userId)
                        ->where('user_field_id', 35)
                        ->first();    
        
            $ourTour = DB::table('user_field_values')
                        ->where('user_id', $userId)
                        ->where('user_field_id', 38)
                        ->first();
        
            $ourMenu = DB::table('user_field_values')
                        ->where('user_id', $userId)
                        ->where('user_field_id', 37)
                        ->first();
        
        

        $totalProfilePercentage = 0;
        if(count($userSelectedHub) > 0 && $user->role_id != 10){
            $totalProfilePercentage += 10;
        }
        switch($user->role_id)
        {
            case 3:
                $role = "Producer";
                $totalProfilePercentage += 30;
                break;

            case 4:
                $role = "Importer";
                $totalProfilePercentage += 30;
                break;

            case 5:
                $role = "Distributor";
                $totalProfilePercentage += 30;
                break;

            case 6:
                $role = "Importer & Distributor";
                $totalProfilePercentage += 30;
                break;

            case 7:
                $role = "Voice Of Expert";
                $totalProfilePercentage += 50;
                break;

            case 8:
                $role = "Travel Agency";
                $totalProfilePercentage += 30;
                break;

            case 9:
                $role = "Italian Restaurant";
                $totalProfilePercentage += 30;
                break;

            case 10:
                $role = "Voyager";
                $totalProfilePercentage += 60;
                break;

            default:
                $role = "Importer";
                $totalProfilePercentage += 30;
                break;

        }

        if(!empty($user->cover_id)){
            $totalProfilePercentage += 10;
        }
        if(!empty($user->avatar_id)){
            $totalProfilePercentage += 10;
        }
        if(!empty($user->about)){
            $totalProfilePercentage += 10;
        } 
        if((!empty($user->phone)) || $user->role_id == 10){
            $totalProfilePercentage += 10;
        }
        if($user->role_id != 10){
            if($ourProduct){
                $totalProfilePercentage += 10;
            }
        }
        if($ourTour){
            $totalProfilePercentage += 10;
        }
        if($ourMenu){
            $totalProfilePercentage += 10;
        }
        if(count($FeaturedListing) > 0){
            $totalProfilePercentage += 10;
        }

        if($totalProfilePercentage > 100){
            $totalProfilePercentage = 100;
        }

        if($user->alysei_certification == '0' && $totalProfilePercentage == 100)
        {
            $userUpdate = User::where('user_id', $userId)->update(['profile_percentage' => $totalProfilePercentage]);
            $userUpdateCertification = User::where('user_id', $userId)->update(['alysei_certification' => '1']);
            $myRole = $this->translate('messages.'.$role,$role);
            $admin = User::where('role_id', '1')->first();
            $title_en = "Congratulation! You are now a certified Alysei ".$role;
            $title_it = "Complimenti! Ora sei un ".$myRole." Certificato Alysei.";
            $selectedLocale = $this->pushNotificationUserSelectedLanguage($userId);
            if($selectedLocale == 'en'){
                $title = "Congratulation! You are now a certified Alysei ".$role;
            }
            else{ 
                $title = "Complimenti! Ora sei un ".$myRole." Certificato Alysei.";
            }

            $notificationCount = Notification::where('to',$userId)->where('title_en',$title_en)->count();
            if($notificationCount == 0){
                $saveNotification = new Notification;
                $saveNotification->from = $admin->user_id;
                $saveNotification->to = $userId;
                $saveNotification->notification_type = 'progress';
                $saveNotification->title_en = $title_en;
                $saveNotification->title_it = $title_it;
                $saveNotification->redirect_to = 'membership_progress';
                $saveNotification->redirect_to_id = $userId;
                $saveNotification->save();
            }

            $tokens = DeviceToken::where('user_id', $userId)->get();
            if(count($tokens) > 0)
            {

                $collectedTokenArray = $tokens->pluck('device_token');

                
                
                $this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,null,null,null,null,null,null,null,null,null,null);

                $this->sendNotificationToIOS($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,null,null,null,null,null,null,null,null,null,null);

                
            }
        }
        return $totalProfilePercentage;

        
        
       
    }
    

}
