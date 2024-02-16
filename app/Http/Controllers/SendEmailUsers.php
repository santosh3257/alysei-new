<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\User\Entities\User;
use App\Events\SendMailIncompleteProfile;

class SendEmailUsers extends Controller
{
    public function sendEmailIncompleteProfileProducer(Request $request){
        $users = User::where('role_id','!=',1)->where('profile_percentage','<',100)->get();
        foreach($users as $key=>$user){
            $userId = $user->user_id;
            event(new SendMailIncompleteProfile($userId));
        }
        //dd($users);

    }
}
