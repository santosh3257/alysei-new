<?php

namespace Modules\Activity\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\UserPrivacy;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Modules\User\Entities\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\ConnectionRequestHubs;
use Modules\User\Entities\Hub; 
use Validator;
//use App\Events\UserRegisterEvent;

class PrivacyController extends CoreController
{
    use UploadImageTrait;
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


    /*
     * Get Roles list to connect with
     *
     */
    /*public function getRolesForConnection()
    {
        try
        {
            $user = $this->user;
            $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin','Importer_and_Distributer','voyagers'])->orderBy('order')->get();

            
            foreach ($roles as $key => $role) {
                $roles[$key]->name = $this->translate('messages.'.$roles[$key]->name,$roles[$key]->name);
            }

            if(count($roles) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $roles
                                        ], $this->successStatus);
            }
            else
            {
                $message = "No roles found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }*/

    /*
     * Get Privacy data
     *
     */
    public function getPrivacyData()
    {
        try
        {
            $user = $this->user;
            $userPrivacy = User::select('user_id','allow_message_from','who_can_view_age','who_can_view_profile','who_can_connect','email_visibility','phone_visibility','address_visibility','role_id')->where('user_id', $user->user_id)->first();

            $userEmailPreference = User::select('user_id','private_messages','when_someone_request_to_follow','weekly_updates')->where('user_id', $user->user_id)->first();
            
            if(!empty($userPrivacy))
            {
               
                $privacyData = ['user_id' => $userPrivacy->user_id, 'allow_message_from' => $userPrivacy->allow_message_from, 'who_can_view_age' => $userPrivacy->who_can_view_age, 'who_can_view_profile' => $userPrivacy->who_can_view_profile, 'who_can_connect' => $userPrivacy->who_can_connect,'email_visibility' => $userPrivacy->email_visibility,"phone_visibility" => $userPrivacy->phone_visibility,"address_visibility" => $userPrivacy->address_visibility,'role_id' =>$userPrivacy->role_id];

                $messagePreference = ['user_id' => $user->user_id, 'private_messages' => $userEmailPreference->private_messages, 'when_someone_request_to_follow' => $userEmailPreference->when_someone_request_to_follow, 'weekly_updates' => $userEmailPreference->weekly_updates];
                if($user->role_id != 10)
                {
                    $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin','Importer_and_Distributer','voyagers'])->orderBy('order')->get();
                }
                else
                {
                    $roles = Role::select('role_id','name','slug')->where('slug','voyagers')->get();
                }
                

            
                foreach ($roles as $key => $role) {
                    if(in_array($roles[$key]->role_id,[4,5])){
                        $roles[$key]->name = $this->translate('messages.'.$roles[$key]->name."s",$roles[$key]->name);
                    }else{
                        $roles[$key]->name = $this->translate('messages.'.$roles[$key]->name,$roles[$key]->name);
                    }
                }

                //$myCountryHubs = Hub::select("id","title")->where('country_id', $user->country_id)->where('status','1')->orderBy('title','asc')->get();
                $myCountryHubs = Hub::select("id","title")->where('status','1')->orderBy('title','asc')->get();
                if($myCountryHubs){
                    foreach($myCountryHubs as $key=>$hub)
                    {
                        $request = ConnectionRequestHubs::where('user_id',$user->user_id)->where('hub_id',$hub->id)->first();
                        if($request){
                            $myCountryHubs[$key]->checked = true;
                        }
                        else{
                            $myCountryHubs[$key]->checked = false;
                        }
                    }
                }
                $show = false;
                if($user->role_id == 3){
                    $show = true;
                }

                return response()->json(['success' => $this->successStatus,
                                     'roles' => $roles,
                                     'privacy_data' => $privacyData,
                                     'email_preference' => $messagePreference,
                                     'hubs' => $myCountryHubs,
                                     'setHubconnectionShow' => $show,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }   
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Save privacy data
     * @Params $request
     */
    public function savePrivacy(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                //'allow_message_from' => 'required', 
                //'who_can_view_age' => 'required',
                'who_can_view_profile' => 'required',
                'who_can_connect' => 'required',
                'private_messages' => 'required', 
                'when_someone_request_to_follow' => 'required',
                'weekly_updates' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $checkUser = User::where('user_id', $user->user_id)->first();

            //Check User role 
            
            /*
             *
                If User selected Importer and Distrubuter (both)
                then Importer&Distributor role should be insert into db 
            */

            $explodeUserRoles  = explode(',',$request->who_can_connect);
            $who_can_connect = $request->who_can_connect;
            if(in_array(4,$explodeUserRoles) && in_array(5,$explodeUserRoles)){
                array_push($explodeUserRoles,6);
                $who_can_connect = implode(',', array_unique($explodeUserRoles));
            }

            if(isset($request->hubs) && !empty($request->hubs)){
                ConnectionRequestHubs::where('user_id',$user->user_id)->delete();
                $hubs = explode(",",$request->hubs);
                foreach($hubs as $key=>$hub){
                    $connectionHubs = new ConnectionRequestHubs();
                    $connectionHubs->user_id = $user->user_id;
                    $connectionHubs->hub_id = $hub;
                    $connectionHubs->save();
                }
            }


            if(!empty($checkUser))
            {
                
               User::where('user_id', $user->user_id)->update(['allow_message_from' => $request->allow_message_from, 'who_can_view_age' => $request->who_can_view_age, 'who_can_view_profile' => $request->who_can_view_profile, 'who_can_connect' => $who_can_connect,'email_visibility' => $request->email_visibility, 'phone_visibility' => $request->phone_visibility,'address_visibility' => $request->address_visibility]);
               User::where('user_id', $user->user_id)->update(['private_messages' => $request->private_messages, 'when_someone_request_to_follow' => $request->when_someone_request_to_follow, 'weekly_updates' => $request->weekly_updates]);
                            
                $message = "Privacy settings has been saved";
                return response()->json(['success' => $this->successStatus,
                                     'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Save Email preference data
     * @Params $request
     */
    /*public function saveEmailPreference(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'private_messages' => 'required', 
                'when_someone_request_to_follow' => 'required',
                'weekly_updates' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $checkUser = User::where('user_id', $user->user_id)->first();

            if(!empty($checkUser))
            {
                
                User::where('user_id', $user->user_id)->update(['private_messages' => $request->private_messages, 'when_someone_request_to_follow' => $request->when_someone_request_to_follow, 'weekly_updates' => $request->weekly_updates]);
                           
                $message = "Email preferences has been saved";
                return response()->json(['success' => $this->successStatus,
                                         'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus);
            }
            else
            {
                $message = "Invalid user";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }*/

    

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
