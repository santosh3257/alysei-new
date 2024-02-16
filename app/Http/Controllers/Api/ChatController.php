<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\UserSelectedHub; 
use Modules\User\Entities\Hub;
use Modules\User\Entities\State;
use Modules\User\Entities\UserField;
use Modules\User\Entities\UserFieldValue;
use App\Http\Traits\UploadImageTrait;
use Modules\Activity\Entities\UserPrivacy;
use Modules\Activity\Entities\ConnectFollowPermission;
use Modules\Activity\Entities\MapPermissionRole;
use Modules\User\Entities\Role;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;

class ChatController extends CoreController
{
    use UploadImageTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    /*
     * Upload media for chats using S3
     *
     */
    public function uploadMedia(Request $request)
    {
        try
        {
            if($request->hasFile('media'))
            {
                $validator = Validator::make($request->all(), [ 
                    'media' => 'required' 
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }

                $data = $this->uploadMediaUsingS3($request->file('media'));
                return response()->json(['success' => $this->successStatus,
                                    'media_url' => "https://alysei.s3.us-west-1.amazonaws.com/".$data[0],
                                    'media_type' => $data[1],
                                    ], $this->successStatus);
            }
            else
            {
                $message = "This is not a valid media type";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }



}