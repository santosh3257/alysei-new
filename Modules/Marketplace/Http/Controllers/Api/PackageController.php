<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplacePackage;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 

class PackageController extends CoreController
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
    
    /*
     * Get Packages Screens
     * @Params $request
     */
    public function getPackages()
    {
        try
        {
            $response_time = (microtime(true) - LARAVEL_START)*1000;
            $packages = MarketplacePackage::where('status','1')->get();

            foreach ($packages as $key => $package) {
                $packages[$key]->name = $this->translate('messages.'.$package->name,$package->name);
            }

            return response()->json(['success'=>$this->successStatus,'data' =>$packages,'response_time'=>$response_time],$this->successStatus); 

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    
}
