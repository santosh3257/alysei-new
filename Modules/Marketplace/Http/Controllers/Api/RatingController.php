<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\MarketplaceRating;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 
use Validator;

class RatingController extends CoreController
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
     * Make a Review store/product
     * @Params $request
     */
    public function doReview(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'id' => 'required',
                'type' => 'required', // 1 for store 2 for product
                'rating' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->type == 1)
            {
                $isRated = MarketplaceRating::where('user_id', $user->user_id)->where('type', '1')->where('id', $request->id)->first();
                if(!empty($isRated))
                {
                    $message = "You have already done a review on this store";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                else
                {
                    $review = new MarketplaceRating;
                    $review->user_id = $user->user_id;
                    $review->id = $request->id;
                    $review->type = '1';
                    $review->rating = $request->rating;
                    $review->review = $request->review;
                    $review->save();

                    $message = "Your rating has been done";
                    return response()->json(['success' => $this->successStatus,
                                                'message' => $this->translate('messages.'.$message,$message),
                                                'data' => $review,
                                             ], $this->successStatus);
                }

            }
            elseif($request->type == 2)
            {
                $isRated = MarketplaceRating::where('user_id', $user->user_id)->where('type', '2')->where('id', $request->id)->first();
                if(!empty($isRated))
                {
                    $message = "You have already done a review on this product";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                else
                {
                    $review = new MarketplaceRating;
                    $review->user_id = $user->user_id;
                    $review->id = $request->id;
                    $review->type = '2';
                    $review->rating = $request->rating;
                    $review->review = $request->review;
                    $review->save();

                    $message = "Your rating has been done";
                    return response()->json(['success' => $this->successStatus,
                                                'message' => $this->translate('messages.'.$message,$message),
                                                'data' => $review,
                                             ], $this->successStatus);
                }
            }
            else
            {
                $message = "Invalid favourite type";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Update a Review on store/product
     * @Params $request
     */
    public function updateReview(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'marketplace_review_rating_id' => 'required',
                'type' => 'required', // 1 for store 2 for product
                'rating' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->type == 1)
            {
                $isRated = MarketplaceRating::where('user_id', $user->user_id)->where('type', '1')->where('marketplace_review_rating_id', $request->marketplace_review_rating_id)->first();
                if(!empty($isRated))
                {
                    $isRated->rating = $request->rating;
                    $isRated->review = $request->review;
                    $isRated->save();

                    $message = "Your rating has been updated";
                    return response()->json(['success' => $this->successStatus,
                                                'message' => $this->translate('messages.'.$message,$message),
                                                'data' => $isRated,
                                             ], $this->successStatus);
                }
                else
                {
                    $message = "Something went wrong";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }

            }
            elseif($request->type == 2)
            {
                $isRated = MarketplaceRating::where('user_id', $user->user_id)->where('type', '2')->where('marketplace_review_rating_id', $request->marketplace_review_rating_id)->first();
                if(!empty($isRated))
                {
                    $isRated->rating = $request->rating;
                    $isRated->review = $request->review;
                    $isRated->save();

                    $message = "Your rating has been updated";
                    return response()->json(['success' => $this->successStatus,
                                                'message' => $this->translate('messages.'.$message,$message),
                                                'data' => $isRated,
                                             ], $this->successStatus);
                }
                else
                {
                    $message = "Something went wrong";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "Invalid favourite type";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * get all ratings
     * @Params $request
     */
    public function getAllReviews(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'id'   => 'required',
                'type' => 'required', // 1 for store 2 for product
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $checkIfRated = MarketplaceRating::where('user_id', $user->user_id)->where('id', $request->id)->first();

            if($request->type == 1)
            {
                $getAllRatings = MarketplaceRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id,first_name,last_name','user.avatar_id')->where('type', '1')->where('id', $request->id)->orderBy('marketplace_review_rating_id', 'DESC')->get()->toArray();
                if(count($getAllRatings) > 0)
                {
                    foreach($getAllRatings as $key => $stories)
                    {
                        if($getAllRatings[$key]['user']['role_id'] == 7 || $getAllRatings[$key]['user']['role_id'] == 10)
                        {
                            $names = ucwords(strtolower($getAllRatings[$key]['user']['first_name'])) . ' ' . ucwords(strtolower($getAllRatings[$key]['user']['last_name']));
                        }
                        elseif($getAllRatings[$key]['user']['role_id'] == 9)
                        {
                            $names = $getAllRatings[$key]['user']['restaurant_name'];
                        }
                        else
                        {
                            $names = $getAllRatings[$key]['user']['company_name'];
                        }

                        $getAllRatings[$key]['user']['review_name'] = $names;

                        if($stories['user_id'] == $user->user_id)
                        {
                            $new_value = $getAllRatings[$key];
                            unset($getAllRatings[$key]);
                            array_unshift($getAllRatings, $new_value);    
                        }

                    }
                    return response()->json(['success' => $this->successStatus,
                                                'is_rated' => (!empty($checkIfRated) ? 1 : 0),
                                                'data' => $getAllRatings,
                                             ], $this->successStatus);
                }
                else
                {
                    $message = "No review found on this store";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            elseif($request->type == 2)
            {
                $getAllRatings = MarketplaceRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id,first_name,last_name','user.avatar_id')->where('type', '2')->where('id', $request->id)->orderBy('marketplace_review_rating_id', 'DESC')->get()->toArray();
                if(count($getAllRatings) > 0)
                {
                    foreach($getAllRatings as $key => $stories)
                    {
                        if($getAllRatings[$key]['user']['role_id'] == 7 || $getAllRatings[$key]['user']['role_id'] == 10)
                        {
                            $names = ucwords(strtolower($getAllRatings[$key]['user']['first_name'])) . ' ' . ucwords(strtolower($getAllRatings[$key]['user']['last_name']));
                        }
                        elseif($getAllRatings[$key]['user']['role_id'] == 9)
                        {
                            $names = $getAllRatings[$key]['user']['restaurant_name'];
                        }
                        else
                        {
                            $names = $getAllRatings[$key]['user']['company_name'];
                        }

                        $getAllRatings[$key]['user']['review_name'] = $names;
                        
                        if($stories['user_id'] == $user->user_id)
                        {
                            $new_value = $getAllRatings[$key];
                            unset($getAllRatings[$key]);
                            array_unshift($getAllRatings, $new_value);    
                        }

                    }
                    return response()->json(['success' => $this->successStatus,
                                                'is_rated' => (!empty($checkIfRated) ? 1 : 0),
                                                'data' => $getAllRatings,
                                             ], $this->successStatus);
                }
                else
                {
                    $message = "No review found on this product";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            else
            {
                $message = "Invalid type";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
}
