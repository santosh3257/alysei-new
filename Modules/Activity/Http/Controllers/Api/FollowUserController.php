<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\UploadImageTrait;
use App\Http\Traits\NotificationTrait;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\Follower;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use App\Notification;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Kreait\Firebase\Factory;
//use App\Events\UserRegisterEvent;

class FollowUserController extends CoreController
{
    use UploadImageTrait;
    use NotificationTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))        //->withDatabaseUri('https://alysei-a2f37-default-rtdb.firebaseio.com/');
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();    
        return $database;
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
     * Follow/Unfollow User
     * @Params $request
     */
    public function followUnfollowUser(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'follow_user_id' => 'required', 
                'follow_or_unfollow' => 'required', // 1 for follow 0 for unfollow
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $followingUserRoleId = User::with('avatar_id')->where('user_id', $request->follow_user_id)->first();
            if(!empty($followingUserRoleId))
            {
                if($request->follow_or_unfollow == 1)
                {
                    $rolePermission = $this->checkRolePermission($user->role_id, $followingUserRoleId->role_id);
                    if($rolePermission[1] > 0)
                    {
                        return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $rolePermission[0]]], $this->exceptionStatus);
                    }
                    else
                    {
                        $isFollowedUser = Follower::where('user_id', $user->user_id)->where('follow_user_id', $request->follow_user_id)->first();
                        if(empty($isFollowedUser))
                        {
                            $follower = new Follower;
                            $follower->user_id = $user->user_id;
                            $follower->follow_user_id = $request->follow_user_id;
                            $follower->save();

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

                            if($followingUserRoleId->role_id == 7 || $followingUserRoleId->role_id == 10)
                            {
                                $accepterName = ucwords(strtolower($followingUserRoleId->first_name)) . ' ' . ucwords(strtolower($followingUserRoleId->last_name));
                            }
                            elseif($followingUserRoleId->role_id == 9)
                            {
                                $accepterName = $followingUserRoleId->restaurant_name;
                            }
                            else
                            {
                                $accepterName = $followingUserRoleId->company_name;
                            }

                            $title_en = "started following you";
                            $title_it = "ha iniziato a seguirti";
                            $selectedLocale = $this->pushNotificationUserSelectedLanguage($user->user_id);
                            if($selectedLocale == 'en'){
                                $title1 = $name." started following you";
                            }
                            else{
                                $title1 = $name." ha iniziato a seguirti";
                            }

                            $saveNotification = new Notification;
                            $saveNotification->from = $user->user_id;
                            $saveNotification->to = $request->follow_user_id;
                            $saveNotification->notification_type = 5; //follow request
                            $saveNotification->title_it = $title_it;
                            $saveNotification->title_en = $title_en;
                            $saveNotification->redirect_to = 'user_screen';
                            $saveNotification->redirect_to_id = $user->user_id;

                            $saveNotification->sender_id = $user->user_id;
                            $saveNotification->sender_name = $name;
                            $saveNotification->sender_image = null;
                            $saveNotification->post_id = null;
                            $saveNotification->connection_id = null; 
                            $saveNotification->sender_role = $followingUserRoleId->role_id;
                            $saveNotification->comment_id = null;
                            $saveNotification->reply = null;
                            $saveNotification->likeUnlike = null;

                            $saveNotification->save();

                            $tokens = DeviceToken::where('user_id', $request->follow_user_id)->get();
                            $notificationCount = $this->updateUserNotificationCountFirebase($request->follow_user_id);
                            if(count($tokens) > 0)
                            {
                                $collectedTokenArray = $tokens->pluck('device_token');
                                $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $followingUserRoleId->user_id, $accepterName, null, null, null, $followingUserRoleId->role_id, null, null, null);

                                $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $followingUserRoleId->user_id, $accepterName, null, null, null, $followingUserRoleId->role_id, null, null, null,null,null,null, $notificationCount);
                            }

                            
                            $message = "You are now following this user";
                            return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                            
                        }
                        else
                        {
                            $message = "You are already following this user";
                            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                        }
                    }
                }
                elseif($request->follow_or_unfollow == 0)
                {
                    $isExistFollowUser = Follower::where('user_id', $user->user_id)->where('follow_user_id', $request->follow_user_id)->first();

                    if(!empty($isExistFollowUser))
                    {
                        $isFollowUserDeleted = Follower::where('user_id', $user->user_id)->where('follow_user_id', $request->follow_user_id)->delete();
                        if($isFollowUserDeleted == 1)
                        {
                            $delete = Notification::where('from',$user->user_id)
                                               ->where('to',$request->follow_user_id)
                                               ->where('notification_type',5)
                                               ->delete();

                            $message = "You unfollowed this user";
                            return response()->json(['success' => $this->successStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first follow this user";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You are not following this user";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid follow/unfollow type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "Invalid following id";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Followers list
     *
     */
    public function getFollowersList()
    {
        try
        {
            $user = $this->user;
            $myFollowers = Follower::with('user:user_id,name,email')->with('follow_user:user_id,name,email')->where('follow_user_id', $user->user_id)->orderBy('id', 'DESC')->get();
            if(count($myFollowers) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'follower_count' => count($myFollowers),  
                                         'data' => $myFollowers
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No followers found";
                return response()->json(['success'=>$this->successStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->successStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Following list
     *
     */
    public function getFollowingsList()
    {
        try
        {
            $user = $this->user;
            $followings = Follower::with('user:user_id,name,email')->with('follow_user:user_id,name,email')->where('user_id', $user->user_id)->orderBy('id', 'DESC')->get();
            if(count($followings) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'follower_count' => count($followings),  
                                         'data' => $followings
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No followers found";
                return response()->json(['success'=>$this->successStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->successStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Check Role Permission
     * @Params $userRole, $FollowingUserRole
     */

    public function checkRolePermission($userRole, $FollowingUserRole)
    {
        $status = [];
        $permission = ConnectFollowPermission::where("role_id", $userRole)->where('permission_type', '2')->first();

        if(!empty($permission))
        {
            $permissionRole = MapPermissionRole::where("connect_follow_permission_id", $permission->connect_follow_permission_id)->get();
            $checkRoleWisePermission = $permissionRole->pluck('role_id')->toArray();

            if(in_array($FollowingUserRole, $checkRoleWisePermission))
            {
                $status = [$this->translate('messages.'."Success","Success"), 0];
            }
            else
            {
                $status = [$this->translate('messages.'."Failed","Failed"), 1];
            }
            
        }
        else
        {
            $status = [$this->translate('messages.'."You are not authorized to follow this user","You are not authorized to follow this user"), 2];
        }
        
        return $status;
    }

   
}
