<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\User\Entities\Walkthrough;
use Modules\User\Entities\WalkThroughPoint;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 

class WalkthroughScreenController extends CoreController
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
     * Get Walk Through Screens
     * @Params $request
     */
    public function getMarketplaceWalkThroughScreens(Request $request){
        try{

            $response_time = (microtime(true) - LARAVEL_START)*1000;

            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }

            $user = $this->user;

            $screens = Walkthrough::select('title_'.$lang.' as title','description_'.$lang.' as description','order','role_id','image_id','walk_through_screen_id')
                        ->where('role_id','=',$user->role_id)->where('type','marketplace')->with('attachment')
                        ->orderBy('order','asc')->get();    
            
            foreach ($screens as $key => $screen) {
                 $screens[$key]->points =  WalkThroughPoint::select('title_'.$lang.' as title','description_'.$lang.' as description','icon_id','walk_through_screen_id')->with('attachment')->where('walk_through_screen_id',$screen->walk_through_screen_id)->get();

                if($user){
                    if(strpos($screen->title,'{name}') > -1){

                        $userName = '';
                        if(!empty($user->first_name) && !empty($user->last_name))
                        {
                            $userName = ucwords(strtolower($user->first_name).' '.strtolower($user->last_name));
                        }
                        elseif(!empty($user->company_name))
                        {
                            $userName = ucwords($user->company_name);
                        }
                        elseif(!empty($user->restaurant_name))
                        {
                            $userName = ucwords($user->restaurant_name);   
                        }

                        $title = str_replace('{name}',$userName,$screen->title);

                        $screens[$key]->title = $title;
                    }else{
                        $screens[$key]->title = $screen->title;
                    }
                }
            }

            return response()->json(['success'=>$this->successStatus,'data' =>$screens,'response_time'=>$response_time],$this->successStatus); 

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    
}
