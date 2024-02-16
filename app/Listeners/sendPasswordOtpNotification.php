<?php

namespace App\Listeners;

use App\Events\ForgotPassword;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\User;
use Mail;

class sendPasswordOtpNotification
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
     * @param  ForgotPassword  $event
     * @return void
     */
    public function handle(ForgotPassword $event)
    {
        $user = User::find($event->userId)->toArray();
        Mail::send('emails.forgot_password_otp', ["user"=>$user], function($message) use ($user) {
            $message->to($user['email']);
            $message->subject('Forgot Password OTP');
        });
    }
}
