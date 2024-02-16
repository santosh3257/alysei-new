<?php

namespace App\Listeners;

use App\Events\RequestConnectionEmailEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Log;
use Modules\Activity\Entities\Connection;
use Mail;

class RequestConnectionEmailListener
{
   
    /**
     * Handle the event.
     *
     * @param  RequestConnectionEmailEvent  $event
     * @return void
     */
    public function handle(RequestConnectionEmailEvent $event)
    {
        $connection = Connection::where('connection_id',$event->connection_id)->first();
        $user = User::where('user_id',$connection->user_id)->first()->toArray();
        $sender = User::where('user_id',$connection->resource_id)->first()->toArray();
        //Log::info('Execution connection ID :20', ['Buyer Id' => $event->connection_id]);
        Mail::send('emails.emailForConnectionRequest', ["user"=>$user,"sender"=>$sender,"connection"=>$connection], function($message) use ($user) {
            $message->to($user['email']);
            $message->subject('New Connection Request');
        });
    }
}
