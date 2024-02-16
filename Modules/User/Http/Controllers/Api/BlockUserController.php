<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use Modules\User\Entities\BlockList;
use Modules\User\Entities\ReportUser;
use Modules\Activity\Entities\Connection;
use Modules\Activity\Entities\Follower;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

class BlockUserController extends CoreController
{
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

    /***
    Block user
    ***/
    public function blockUser(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
            'block_user_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = User::where('user_id', $request->block_user_id)->first();
            if(!empty($user))
            {
                $checkList = BlockList::where('user_id', $loggedInUser->user_id)->where('block_user_id', $request->block_user_id)->first();
                if(empty($checkList))
                {
                    $blockList = new BlockList;
                    $blockList->user_id = $loggedInUser->user_id;
                    $blockList->block_user_id = $request->block_user_id;
                    $blockList->save();

                    $checkConnection = Connection::where(function ($query) use ($request, $loggedInUser) {
                        $query->where('resource_id', '=', $request->block_user_id)
                            ->Where('user_id', '=', $loggedInUser->user_id);
                    })->orWhere(function ($query) use ($loggedInUser, $request) {
                        $query->where('resource_id', '=', $loggedInUser->user_id)
                            ->Where('user_id', '=', $request->block_user_id);
                    })->delete();

                    $checkFollower = Follower::where(function ($query) use ($request, $loggedInUser) {
                        $query->where('follow_user_id', '=', $request->block_user_id)
                            ->Where('user_id', '=', $loggedInUser->user_id);
                    })->orWhere(function ($query) use ($loggedInUser, $request) {
                        $query->where('follow_user_id', '=', $loggedInUser->user_id)
                            ->Where('user_id', '=', $request->block_user_id);
                    })->delete();


                    return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'."User blocked successfuly!","User blocked successfuly!"),
                                             'data' => $blockList,
                                            ], $this->successStatus);
                }
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."This user is already in your block list","This user is already in your block list")]], $this->exceptionStatus);       
                }
                
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."This user does not exist","This user does not exist")]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    UnBlock user
    ***/
    public function unBlockUser(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
            'block_user_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $user = User::where('user_id', $request->block_user_id)->first();
            
            if(!empty($user))
            {
                $blockList = BlockList::where('user_id', $loggedInUser->user_id)->where('block_user_id', $request->block_user_id)->first();
                if(!empty($blockList))
                {
                    $blockList = BlockList::where('user_id', $loggedInUser->user_id)->where('block_user_id', $request->block_user_id)->delete();

                    return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'."User unblocked successfuly!","User unblocked successfuly!")
                                        ], $this->successStatus);
                }
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."This user does not exist in your block list","This user does not exist in your block list")]], $this->exceptionStatus);
                }
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."This user does not exist","This user does not exist")]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /***
    get Cities
    ***/
    public function getBlockedUserList(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $blockList = BlockList::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->with('blockuser:user_id,first_name,last_name,name,email,company_name,restaurant_name,role_id,avatar_id','blockuser.avatar_id')->where('user_id', $loggedInUser->user_id)->orderBy('block_list_id','DESC')->paginate(10);
            if(count($blockList) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                        'block_count_user'  =>  count($blockList),
                                        'data' => $blockList,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,
                                        'block_count_user'  =>  count($blockList),
                                        'errors' =>['exception' => $this->translate('messages.'."This user found in your block list","This user found in your block list")]],
                                         $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* 
     * Report Post
     * 
     */
    public function reportUser(Request $request){
        try
        {
            
            $validator = Validator::make($request->all(), [ 
                'user_id' => 'required',
                'report_as' => 'required_without:message',
                'message' => 'required_without:report_as',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $reportUser = new ReportUser;
            $reportUser->message = $request->message;
            $reportUser->report_as = $request->report_as;
            $reportUser->user_id = $request->user_id;
            $reportUser->report_by = $this->user->user_id;
            $reportUser->created_at = Now();
            $reportUser->updated_at = Now();
            $reportUser->save();

            return response()->json(['success' => $this->successStatus,
                'message' => $this->translate('messages.'."Report has been initiated successfully","Report has been initiated successfully")], $this->successStatus);
                

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    
    
}
