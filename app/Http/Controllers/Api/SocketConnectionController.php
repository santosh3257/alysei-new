<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use App\SocketConnection;
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\Role;
use Modules\User\Entities\DeviceToken;
use Modules\Activity\Entities\CoreComment;
use Carbon\Carbon;
use App\Http\Traits\NotificationTrait;
use Kreait\Firebase\Factory;
use App\Notification;
use DB;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class SocketConnectionController extends CoreController
{
    use UploadImageTrait;
    use NotificationTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    /*public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }*/

    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();
        return $database;
    }

    public function likeUnlikeInFirebase($id, $userId, $postId)
    {
        $data = $this->conn_firbase()->getReference('like_unlike_post/'.$id)
        ->update([
        'id' => $id,
        'user_id' => $userId,
        'post_id' => $postId
        ]);

        return $data;

    }

    public function removeLikes($id)
    {
        $data = $this->conn_firbase()->getReference('like_unlike_post/'.$id)->remove();
    }

    // Update user notification 
    public function updateUserNotificationCountFirebase($id)
    {
        try{
            $reference = $this->conn_firbase()->getReference('users');
            $snapshot = $reference->getChild($id);
            $getKey = $snapshot->getValue();
            if(isset($getKey['notification'])){
                $countNotification = $getKey['notification'];

                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => $countNotification+1
                ]);

                return $countNotification+1;
            }
            else{
                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => 0
                ]);

                return 0;
            }
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }


    /*
     * Get Post Comments
     * @Params $request
     */
    public function getPostComments(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'post_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityAction = ActivityAction::where("activity_action_id", $request->post_id)->first();
            if(!empty($activityAction))
            {
                $postComments = CoreComment::where('resource_id', $request->post_id)->where('parent_id', 0)->orderBy('core_comment_id', 'DESC')->get();
                if(count($postComments) > 0)
                {
                    foreach($postComments as $key => $postComment)
                    {
                        $poster = User::select('user_id','name','email','role_id','avatar_id','first_name','last_name','restaurant_name','company_name')->with('avatar_id')->where('user_id', $postComment->poster_id)->first();
                        if(!empty($poster))
                        {
                            if($poster->role_id == 7 || $poster->role_id == 10)
                            {
                                $name = ucwords(strtolower($poster->first_name)) . ' ' . ucwords(strtolower($poster->last_name));
                            }
                            elseif($poster->role_id == 9)
                            {
                                $name = $poster->restaurant_name;
                            }
                            else
                            {
                                $name = $poster->company_name;
                            }
                            $poster->name = $name;
                            $postComments[$key]->poster = $poster;
                        }
                        else
                        {
                            $postComments[$key]->poster = null;   
                        }
                        $postComments[$key]->posted_at = $postComment->created_at->diffForHumans();

                        $replyCounts = CoreComment::where('parent_id', $postComment->core_comment_id)->count();
                        $postComments[$key]->reply_counts = $replyCounts;   
                    }
                    return response()->json(['success' => $this->successStatus,
                                             'data' => $postComments,
                                            ], $this->successStatus);
                }
                else
                {
                    $message = "No comments found for this post";
                    return response()->json(['success'=>$this->exceptionStatus,'data' => $this->translate('messages.'.$message,$message)], $this->exceptionStatus); 
                }
            }
            else
            {
                $message = "Invalid post Id";
                    return response()->json(['success'=>$this->exceptionStatus,'data' => $this->translate('messages.'.$message,$message)], $this->exceptionStatus); 
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Get Comments replies
     * @Params $request
     */
    public function getCommentReplies(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'comment_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            
            //$postComments = CoreComment::with('poster:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','poster.avatar_id')->where('parent_id', $request->comment_id)->orderBy('core_comment_id', 'DESC')->get();
            $postComments = CoreComment::where('parent_id', $request->comment_id)->orderBy('core_comment_id', 'DESC')->get();
            foreach($postComments as $key => $postComment)
            {
                $poster = User::select('user_id','name','email','role_id','avatar_id','first_name','last_name','restaurant_name','company_name')->with('avatar_id')->where('user_id', $postComment->poster_id)->first();
                if(!empty($poster))
                {
                    if($poster->role_id == 7 || $poster->role_id == 10)
                    {
                        $name = ucwords(strtolower($poster->first_name)) . ' ' . ucwords(strtolower($poster->last_name));
                    }
                    elseif($poster->role_id == 9)
                    {
                        $name = $poster->restaurant_name;
                    }
                    else
                    {
                        $name = $poster->company_name;
                    }
                    $poster->name = $name;
                    $postComments[$key]->poster = $poster;
                }
                else
                {
                    $postComments[$key]->poster = null;   
                }
                $postComments[$key]->posted_at = $postComment->created_at->diffForHumans();

                
            }
            if(count($postComments) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $postComments,
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No replies found under this comment";
                return response()->json(['success'=>$this->exceptionStatus,'data' => $this->translate('messages.'.$message,$message)], $this->exceptionStatus); 
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Comment Post
     * @Params $request
     */
    public function commentPost(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'user_id'  => 'required',
                'post_id' => 'required',
                'comment' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                $poster = User::where('user_id', $request->user_id)->first();
                $activityActionType = ActivityActionType::where('activity_action_type_id', $activityPost->type)->first();
                $actionType = $this->checkActionType($activityActionType->type);
                if($actionType[1] > 0)
                {
                    return response()->json(['success'=>$this->exceptionStatus,'message' => $actionType[0]], $this->exceptionStatus);
                }
                else
                {
                    $activityComment = new CoreComment;
                    $activityComment->resource_type = "user";
                    $activityComment->resource_id = $request->post_id;
                    $activityComment->poster_type = "user";
                    $activityComment->poster_id = $request->user_id;
                    $activityComment->body = $request->comment;
                    $activityComment->save();

                    $activityPost->comment_count = $activityPost->comment_count + 1;
                    $activityPost->save();

                    if($poster->role_id == 7 || $poster->role_id == 10)
                    {
                        $name = ucwords(strtolower($poster->first_name)) . ' ' . ucwords(strtolower($poster->last_name));
                    }
                    elseif($poster->role_id == 9)
                    {
                        $name = $poster->restaurant_name;
                    }
                    else
                    {
                        $name = $poster->company_name;
                    }

                    $selectedLocale = $this->pushNotificationUserSelectedLanguage($activityPost->subject_id);
                    if($selectedLocale == 'en'){
                        $title1 = $name." commented on your post";
                    }
                    else{
                        $title1 = $name." ha commentato il tuo post";
                    }

                    if($activityPost->subject_id != $request->user_id){
                        $saveNotification = new Notification;
                        $saveNotification->from = $poster->user_id;
                        $saveNotification->to = $activityPost->subject_id;
                        $saveNotification->notification_type = 6; //commented on post
                        $saveNotification->title_it = 'ha commentato il tuo post';
                        $saveNotification->title_en = 'commented on your post';
                        $saveNotification->redirect_to = 'post_screen';
                        $saveNotification->redirect_to_id = $request->post_id;

                        $saveNotification->sender_id = $request->user_id;
                        $saveNotification->sender_name = $name;
                        $saveNotification->sender_image = null;
                        $saveNotification->post_id = $request->post_id;
                        $saveNotification->connection_id = null;
                        $saveNotification->sender_role = $poster->role_id;
                        $saveNotification->comment_id = null;
                        $saveNotification->reply = null;
                        $saveNotification->likeUnlike = null;
                        $saveNotification->save();

                        $tokens = DeviceToken::where('user_id', $activityPost->subject_id)->get();
                        $notificationCount = $this->updateUserNotificationCountFirebase($activityPost->subject_id);
                        if(count($tokens) > 0)
                        {
                            $collectedTokenArray = $tokens->pluck('device_token');
                            $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, /*$poster->avatar_id->attachment_url*/null, $request->post_id, null, $poster->role_id, null, null, null);

                            $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, null, $request->post_id, null, $poster->role_id, null, null, null,null,null,null, $notificationCount);
                        }
                    }

                    $postCommentData = CoreComment::with('poster:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','poster.avatar_id')->where('core_comment_id', $activityComment->core_comment_id)->first();

                    $message = "Your comment has been posted successfully";
                    return response()->json(['success' => $this->successStatus,
                                             'message' => $this->translate('messages.'.$message,$message),
                                             'data' => $postCommentData
                                            ], $this->successStatus);
                }
            }
            else
            {
                $message = "Invalid post id";
                return response()->json(['success'=>$this->exceptionStatus,'message' => $this->translate('messages.'.$message,$message)], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Reply Post
     * @Params $request
     */
    public function replyPost(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'user_id' => 'required',
                'post_id' => 'required',
                'comment_id' => 'required',
                'reply' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                $existingComment = CoreComment::where('core_comment_id',$request->comment_id)->first();
                if($existingComment){
                    $poster = User::where('user_id', $request->user_id)->first();
                    $activityComment = new CoreComment;
                    $activityComment->resource_type = "user";
                    $activityComment->resource_id = $request->post_id;
                    $activityComment->poster_type = "user";
                    $activityComment->poster_id = $request->user_id;
                    $activityComment->body = $request->reply;
                    $activityComment->parent_id = $request->comment_id;
                    $activityComment->save();

                    if($poster->role_id == 7 || $poster->role_id == 10)
                    {
                        $name = ucwords(strtolower($poster->first_name)) . ' ' . ucwords(strtolower($poster->last_name));
                    }
                    elseif($poster->role_id == 9)
                    {
                        $name = $poster->restaurant_name;
                    }
                    else
                    {
                        $name = $poster->company_name;
                    }

                    $selectedLocale = $this->pushNotificationUserSelectedLanguage($existingComment->poster_id);
                    if($selectedLocale == 'en'){
                        $title1 = $name." replied on your post";
                    }
                    else{
                        $title1 = $name." ha risposto al tuo post";
                    }

                    if($existingComment->poster_id != $request->user_id){
                        $saveNotification = new Notification;
                        $saveNotification->from = $poster->user_id;
                        $saveNotification->to = $existingComment->poster_id;
                        $saveNotification->notification_type = 7; //replied on a post
                        $saveNotification->title_it = 'ha risposto al tuo post';
                        $saveNotification->title_en = 'replied on your post';
                        $saveNotification->redirect_to = 'post_screen';
                        $saveNotification->redirect_to_id = $request->post_id;

                        $saveNotification->sender_id = $request->user_id;
                        $saveNotification->sender_name = $name;
                        $saveNotification->sender_image = null;
                        $saveNotification->post_id =$request->post_id;
                        $saveNotification->connection_id = null;
                        $saveNotification->sender_role = $poster->role_id;
                        $saveNotification->comment_id = $request->comment_id;
                        $saveNotification->reply = $request->reply;
                        $saveNotification->likeUnlike = null;

                        $saveNotification->save();

                        $tokens = DeviceToken::where('user_id', $existingComment->poster_id)->get();
                        $notificationCount = $this->updateUserNotificationCountFirebase($existingComment->poster_id);
                        if(count($tokens) > 0)
                        {
                            $collectedTokenArray = $tokens->pluck('device_token');
                            $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, /*$poster->avatar_id->attachment_url*/null, $request->post_id, null, $poster->role_id, $request->comment_id, $request->reply,null);

                            $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, null, $request->post_id, null, $poster->role_id, $request->comment_id, $request->reply,null,null,null,null,$notificationCount);
                        }

                        
                    }
                    $postReplyData = CoreComment::with('poster:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','poster.avatar_id')->where('core_comment_id', $activityComment->core_comment_id)->first();

                    $message = "Your reply has been posted successfully";
                    return response()->json(['success' => $this->successStatus,
                                             'message' => $this->translate('messages.'.$message,$message),
                                             'data' => $postReplyData
                                            ], $this->successStatus);
                }
                else{
                    $message = "Invalid comment id";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "Invalid post id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Like Post
     * @Params $request
     */
    public function likeUnlikePost(Request $request)
    {
        try
        {
            //$user = $this->user;
            $validator = Validator::make($request->all(), [ 
                //'ww' => 'required',
                'user_id' => 'required',
                'post_id' => 'required',
                'like_or_unlike' => 'required', // 1 for like 0 for unlike
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $activityPost = ActivityAction::with('attachments.attachment_link','subject_id')->where('activity_action_id', $request->post_id)->first();
            if(!empty($activityPost))
            {
                if($request->like_or_unlike == 1)
                {
                    $poster = User::with('avatar_id')->where('user_id', $request->user_id)->first();
                    $isLikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $request->user_id)->first();


                    if(!empty($isLikedActivityPost))
                    {
                        $message = "You have already liked this post";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {
                        $activityLike = new ActivityLike;
                        $activityLike->resource_id = $request->post_id;
                        $activityLike->poster_type = "user";
                        $activityLike->poster_id = $request->user_id;
                        $activityLike->save();

                        $activityPost->like_count = $activityPost->like_count + 1;
                        $activityPost->save();

                        $this->likeUnlikeInFirebase($activityLike->activity_like_id, $request->user_id, $request->post_id);

                        if($poster->role_id == 7 || $poster->role_id == 10)
                        {
                            $name = ucwords(strtolower($poster->first_name)) . ' ' . ucwords(strtolower($poster->last_name));
                        }
                        elseif($poster->role_id == 9)
                        {
                            $name = $poster->restaurant_name;
                        }
                        else
                        {
                            $name = $poster->company_name;
                        }

                        $selectedLocale = $this->pushNotificationUserSelectedLanguage($activityPost->subject_id);
                        if($selectedLocale == 'en'){
                            $title1 = $name." liked your post";
                        }
                        else{
                            $title1 = 'A '. $name." piace il tuo post";
                        }

                        if($activityPost->subject_id != $request->user_id){
                            $saveNotification = new Notification;
                            $saveNotification->from = $poster->user_id;
                            $saveNotification->to = $activityPost->subject_id;
                            $saveNotification->notification_type = 6; //liked a post
                            $saveNotification->title_it = 'A piace il tuo post';
                            $saveNotification->title_en = 'liked your post';
                            $saveNotification->redirect_to = 'post_screen';
                            $saveNotification->redirect_to_id = $request->post_id;

                            $saveNotification->sender_id = $request->user_id; 
                            $saveNotification->sender_name = $name;
                            $saveNotification->sender_image = null;
                            $saveNotification->post_id = $request->post_id;
                            $saveNotification->connection_id = null;
                            //$saveNotification->sender_role = $user->role_id;
                            $saveNotification->comment_id = null;
                            $saveNotification->reply = null;
                            $saveNotification->likeUnlike = $request->like_or_unlike;

                            $saveNotification->save();

                            $tokens = DeviceToken::where('user_id', $activityPost->subject_id)->get();
                            $notificationCount = $this->updateUserNotificationCountFirebase($activityPost->subject_id);
                            if(count($tokens) > 0)
                            {
                                $collectedTokenArray = $tokens->pluck('device_token');
                                $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, /*$poster->avatar_id->attachment_url*/ null, $request->post_id, null, null, null,null, $request->like_or_unlike);

                                $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, null, $request->post_id, null, null, null,null, null,null,null, $request->like_or_unlike, $notificationCount);
                            }

                            
                        }

                        $activityPostData = ActivityLike::with('user:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('resource_id', $request->post_id)->first();
                        $postLikeCount = ActivityLike::where('resource_id', $request->post_id)->count();
                        $message = "You liked this post";
                        return response()->json(['success' => $this->successStatus,
                                                 'total_likes' => $postLikeCount,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                 'data' => $activityPostData
                                                ], $this->successStatus);
                    }
                }
                elseif($request->like_or_unlike == 0)
                {
                    $isLikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $request->user_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        
                        $delete = Notification::where('from',$request->user_id)
                                               ->where('post_id',$request->post_id)
                                               ->where('notification_type',8)
                                               ->delete();

                        $this->removeLikes($isLikedActivityPost->activity_like_id);
                        
                        $isUnlikedActivityPost = ActivityLike::where('resource_id', $request->post_id)->where('poster_id', $request->user_id)->delete();
                        if($isUnlikedActivityPost == 1)
                        {
                            $activityPost->like_count = $activityPost->like_count - 1;
                            $activityPost->save();

                            $postLikeCount = ActivityLike::where('resource_id', $request->post_id)->count();
                            $message = "You unliked this post";
                            return response()->json(['success' => $this->successStatus,
                                                 'total_likes' => $postLikeCount,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first like this post";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You have not liked this post";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid like/unlike type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            else
            {
                $message = "Invalid post id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Save connnection
    */
    public function saveConnection(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [ 
                'user_id' => 'required', 
                'socket_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $isConnectedUser = SocketConnection::where('user_id', $request->user_id)->first();
            if(empty($isConnectedUser))
            {
                $newConnection = new SocketConnection;
                $newConnection->user_id = $request->user_id;
                $newConnection->socket_id = $request->socket_id;
                $newConnection->status = '1';
                $newConnection->save();

                return response()->json(['success' => $this->successStatus,
                                     'data' => $newConnection,
                                    ], $this->successStatus);
                
            }
            else
            {
                $connection = SocketConnection::where('user_id', $request->user_id)->update(["socket_id" => $request->socket_id]);
                return response()->json(['success' => $this->successStatus,
                                     //'data' => $newConnection,
                                    ], $this->successStatus);
            }      
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get all connection by userid
    */
    public function getAllConnections($userId,$postOwner='')
    {
        try
        {
            $isConnectedUser = SocketConnection::where('user_id', $userId)->orWhere('user_id', $postOwner)->get();
            if(count($isConnectedUser) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                     'data' => $isConnectedUser,
                                    ], $this->successStatus);
                
            }
            else
            {
                $message = "No socket connection for this userId";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }    
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Remove connection by socketid
    */
    public function removeSocketConnection($socketId)
    {
        try
        {
            $isConnectedUser = SocketConnection::where('socket_id', $socketId)->first();
            if(!empty($isConnectedUser))
            {
                SocketConnection::where('socket_id', $socketId)->delete();
                return response()->json(['success' => $this->successStatus,
                                     'message' => 'Removed successfully',
                                    ], $this->successStatus);
                
            }
            else
            {
                $message = "Invalid socket Id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }    
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Check Action Type
     * @Params $type
     */

    public function checkActionType($type)
    {
        $status = [];
        $activityActionType = ActivityActionType::where("type", $type)->first();
        if(!empty($activityActionType))
        {
            if($activityActionType->commentable == '0')
            {
                $status = [$this->translate('messages.'."You are not authorised to comment on this post","You are not authorised to comment on this post"), 1];
            }
            else
            {
                $status = [$this->translate('messages.'."Success","Success"), 0];
            }            
        }
        else
        {
            $status = [$this->translate('messages.'."Invalid action type","Invalid action type"), 3];
        }
        
        return $status;
    }
}