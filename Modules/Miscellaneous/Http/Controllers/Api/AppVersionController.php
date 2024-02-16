<?php

namespace Modules\Miscellaneous\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Miscellaneous\Entities\AppVersion;

class AppVersionController extends Controller
{

    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function getAppLatestVersion()
    {
        try
        {
            $latestVersion = AppVersion::where('status','1')->first();

            if($latestVersion){
                return response()->json(['success' => $this->successStatus,
                                         'data' => $latestVersion,
                                        ], $this->successStatus);
            }
            else{
                return response()->json(['success' => $this->successStatus,'errors' =>['exception' => $this->translate('messages.'."")]], $this->successStatus); 
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

   
}
