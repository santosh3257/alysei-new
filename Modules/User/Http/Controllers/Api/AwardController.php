<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use Modules\User\Entities\Trip;
use Modules\User\Entities\AdventureType;
use Modules\User\Entities\Award;
use Modules\User\Entities\Medal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use DB;
use Validator;
//use App\Events\UserRegisterEvent;
use App\Http\Traits\UploadImageTrait;
use Cviebrock\EloquentSluggable\Services\SlugService;


class AwardController extends CoreController
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

    /***
    Get trip listing
    ***/
    public function getAwardListing(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            
            if(!empty($request->visitor_profile_id))
            {
                $awardLists = Award::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','medal')->where('user_id', $request->visitor_profile_id)->where('status', '1')->get();
                
            }
            else
            {
                $awardLists = Award::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment','medal')->where('user_id', $loggedInUser->user_id)->where('status', '1')->get();
            }
            
            if(count($awardLists) > 0)
            {
                foreach($awardLists as $key => $award)
                {
                    $awardLists[$key]->award_name = $this->translate('messages.'.$award->award_name, $award->award_name);
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $awardLists,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->successStatus,'errors' =>['exception' => $this->translate('messages.'."No awards found","No awards found")]], $this->successStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Get adventure types
    ***/
    public function getMedalTypes()
    {
        try
        {
            $loggedInUser = $this->user;
            
            $medalTypes = Medal::get();
                                     
            if(count($medalTypes) > 0)
            {
                foreach($medalTypes as $key => $medals)
                {
                    $medalTypes[$key]->name = $this->translate('messages.'.$medals->name, $medals->name);
                }

                return response()->json(['success' => $this->successStatus,
                                         'data' => $medalTypes,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."No medals found","No medals found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    
    /***
    Create award
    ***/
    public function createAward(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'award_name' => 'required', 
                'winning_product' => 'required',
                'medal_id' => 'required',
                //'competition_url' => 'required',
                'image_id' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $award = new Award;
            $award->user_id = $loggedInUser->user_id;
            $award->award_name = $request->award_name;
            $award->slug = SlugService::createSlug(Award::class, 'slug', $request->award_name);
            $award->winning_product = $request->winning_product;
            $award->medal_id = $request->medal_id;
            $award->competition_url = $request->competition_url;
            $award->image_id = $this->uploadFrontImage($request->file('image_id'));
            $award->save();

            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Award created successfuly!","Award created successfuly!")
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /***
    Edit Award
    ***/
    public function editAward($awardId = '')
    {
        try
        {
            $loggedInUser = $this->user;
            
            $award = Award::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('award_id', $awardId)->where('status', '1')->first();
            if(!empty($award))
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $award,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Award not found","Award not found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    

    /***
    Update award
    ***/
    public function updateAward(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'award_id'   =>  'required',
                'award_name' => 'required', 
                'winning_product' => 'required',
                'medal_id' => 'required',
                //'competition_url' => 'required'
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $award = Award::where('award_id', $request->award_id)->where('user_id', $loggedInUser->user_id)->first();
            if(!empty($award))
            {
                $award->award_name = $request->award_name;
                $award->winning_product = $request->winning_product;
                $award->medal_id = $request->medal_id;
                $award->competition_url = $request->competition_url;                

                if(!empty($request->image_id))
                {
                    $this->deleteAttachment($award->image_id);
                    $award->image_id = $this->uploadFrontImage($request->file('image_id'));
                }
            
                $award->save();

                return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'."Award updated successfuly!","Award updated successfuly!")
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Something went wrong","Something went wrong")]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Delete award
    ***/
    public function deleteAward(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'award_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $award = Award::where('award_id', $request->award_id)->where('user_id', $loggedInUser->user_id)->first();
            if(!empty($award))
            {
                $this->deleteAttachment($award->image_id);
                $isAwardDeleted = Award::where('award_id', $request->award_id)->delete();
                if($isAwardDeleted == 1)
                {
                    return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Award deleted successfuly!","Award deleted successfuly!")
                                    ], $this->successStatus);
                }
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Something went wrong","Something went wrong")]], $this->exceptionStatus);    
                }
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Invalid award","Invalid award")]], $this->exceptionStatus);    
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    
}
