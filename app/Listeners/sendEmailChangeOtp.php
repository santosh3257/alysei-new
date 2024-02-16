<?php

namespace App\Listeners;

use App\Events\EmailChangeOtp;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\ChangeEmailRequest;
use Modules\User\Entities\User;
use Mail;

class sendEmailChangeOtp
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
     * @param  EmailChangeOtp  $event
     * @return void
     */
    public function handle(EmailChangeOtp $event)
    {
        $user = ChangeEmailRequest::where('user_id',$event->userId)->first()->toArray();
        $otp = User::select('otp')->where('user_id',$event->userId)->first()->toArray();
        $user['otp'] = $otp['otp'];

        Mail::send('emails.email_request_otp', ["user"=>$user], function($message) use ($user) {
            $message->to($user['email']);
            $message->subject('Email Change OTP');
        });
    }
}
