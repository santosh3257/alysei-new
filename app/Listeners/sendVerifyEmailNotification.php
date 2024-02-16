<?php

namespace App\Listeners;

use App\Events\VerifyEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\User;
use Mail;

class sendVerifyEmailNotification
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
     * @param  VerifyEmail  $event
     * @return void
     */
    public function handle(VerifyEmail $event)
    {
        $user = User::find($event->userId)->toArray();
        
        
        Mail::send('emails.verify_email', ["user"=>$user], function($message) use ($user) {
            $message->to($user['email']);
            $message->subject('Verify Your Email');
        });

        
    }
}
