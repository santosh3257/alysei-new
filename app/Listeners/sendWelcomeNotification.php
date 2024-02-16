<?php

namespace App\Listeners;

use App\Events\Welcome;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\User;
use Mail;

class sendWelcomeNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Welcome  $event
     * @return void
     */
    public function handle(Welcome $event)
    {
        $user = User::find($event->userId)->toArray();
        
        if(!empty($user["first_name"]) && !empty($user["last_name"]))
        {
            $userName = ucwords(strtolower($user["first_name"]).' '.strtolower($user["last_name"]));
        }
        elseif(!empty($user["company_name"]))
        {
            $userName = ucwords($user["company_name"]);
        }
        elseif(!empty($user["restaurant_name"]))
        {
            $userName = ucwords($user["restaurant_name"]);   
        }

        $user['display_name'] = $userName;

        //Welcome Message to User
        if($event->lang == 'it'){
            Mail::send('emails.welcomeitalian', ["user"=>$user], function($message) use ($user) {
                $message->to($user['email']);
                $message->subject('Welcome to Alysei');
            });
        }else{
            Mail::send('emails.welcome', ["user"=>$user], function($message) use ($user) {
                $message->to($user['email']);
                $message->subject('Welcome to Alysei');
            });
        }

        //New Candidate Message to Admin
        // $user['to'] = env("mail_email");

        Mail::send('emails.new_user', ["user"=>$user], function($message) use ($user) {
            $message->to(env('ADMIN_EMAIL'));
            $message->cc([env('SUB_ADMIN_EMAIL1'),env('SUB_ADMIN_EMAIL2'),env('SUB_ADMIN_EMAIL3')]);
            $message->subject('New User Registration Mail');
        });
    }
}

