<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\User\Entities\User;
use Modules\Activity\Entities\ActivityAction;
use App\Http\Traits\NotificationTrait;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\Follower;
use App\Notification;
use Modules\User\Entities\DeviceToken; 
use Illuminate\Support\Facades\Log;

class SendPostNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotificationTrait;

    private $postId = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Execution Job id : {this->postId}', ['post_id' => $this->postId]);
        $activityAction = ActivityAction::where('activity_action_id',$this->postId)->first();
        if($activityAction){
            $user = User::where('user_id',$activityAction->subject_id)->first();
            // Get user connections
            $requestedConnection = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id')->toArray();
            $getRequestedConnection = Connection::select('*','resource_id as poster_id')->where('user_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id')->toArray();

            // Get user followers 
            $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $user->user_id)->pluck('poster_id')->toArray();
            
            $userIds = array_merge($requestedConnection,$getRequestedConnection,$myFollowers);

            if(count($userIds) > 0){

                if($user->role_id == 7 || $user->role_id == 10)
                {
                    $name = ucwords(strtolower($user->first_name)) . ' ' . ucwords(strtolower($user->last_name));
                }
                elseif($user->role_id == 9)
                {
                    $name = $user->restaurant_name;
                }
                else
                {
                    $name = $user->company_name;
                }

            
                foreach($userIds as $friendId){
                    $title_en = "added a post";
                    $title_it = "ha aggiunto un post";
                    $selectedLocale = $this->pushNotificationUserSelectedLanguage($friendId);
                    if($selectedLocale == 'en'){
                        $title1 = $name." added a post";
                    }
                    else{
                        $title1 = $name." ha aggiunto un post";
                    }
                    
                    $saveNotification = new Notification;
                    $saveNotification->from = $user->user_id;
                    $saveNotification->to = $friendId;
                    $saveNotification->notification_type = 6; //Added a post
                    $saveNotification->title_it = $title_it;
                    $saveNotification->title_en = $title_en;
                    $saveNotification->redirect_to = 'post_screen';
                    $saveNotification->redirect_to_id = $activityAction->activity_action_id;

                    $saveNotification->sender_id = $user->user_id; 
                    $saveNotification->sender_name = $name;
                    $saveNotification->sender_image = null;
                    $saveNotification->post_id = $activityAction->activity_action_id;
                    $saveNotification->connection_id = null;
                    //$saveNotification->sender_role = $user->role_id;
                    $saveNotification->comment_id = null;
                    $saveNotification->reply = null;
                    $saveNotification->likeUnlike = null;

                    $saveNotification->save();

                    $tokens = DeviceToken::where('user_id', $friendId)->get();
                    
                    if(count($tokens) > 0)
                    {
                        $collectedTokenArray = $tokens->pluck('device_token');
                        $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $name, /*$poster->avatar_id->attachment_url*/ null, $activityAction->activity_action_id, null, null, null,null, null);

                        $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $user->user_id, $name, /*$poster->avatar_id->attachment_url*/ null, $activityAction->activity_action_id, null, null, null,null, null);
                    }

                    $this->updateFirebaseUsersNotification($friendId);
                }
            }
        }
        else{
            Log::info('post notification has been faild and post : {post_id}', ['post_id' => $this->postId]);
        }
    }
}
