<?php

namespace App\Listeners;

use App\Events\SendMailIncompleteProfile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\User;
use Mail;

class SendMailIncompleteProfileFired
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
     * @param  SendMailIncompleteProfile  $event
     * @return void
     */
    public function handle(SendMailIncompleteProfile $event)
    {
        $user = User::find($event->userId)->toArray();
        Mail::send('emails.profileIncompleteSentEmail', ["user"=>$user], function($message) use ($user) {
            $message->to($user['email']);
            $message->subject('Regarding incomplete your profile');
        });
    
    }
}
