<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Attachment;
use Modules\Marketplace\Entities\WalkthroughScreen;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Auth; 
use Validator;

class FavouriteController extends CoreController
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
     * Mark a Favourite store/product
     * @Params $request
     */
    public function makeFavourite(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'id' => 'required',
                'favourite_type' => 'required', // 1 for store 2 for product
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->favourite_type == 1)
            {
                $isFavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $request->id)->first();
                if(!empty($isFavourite))
                {
                    $message = "This store is already in your favourite list";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                else
                {
                    $favStore = new MarketplaceFavourite;
                    $favStore->user_id = $user->user_id;
                    $favStore->id = $request->id;
                    $favStore->favourite_type = '1';
                    $favStore->save();

                    $message = "The store has been added to your favourite list";
                    return response()->json(['success' => $this->successStatus,
                                                'message' => $this->translate('messages.'.$message,$message),
                                                'data' => $favStore,
                                             ], $this->successStatus);
                }

            }
            elseif($request->favourite_type == 2)
            {
                $isFavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '2')->where('id', $request->id)->first();
                if(!empty($isFavourite))
                {
                    $message = "This product is already in your favourite list";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                else
                {
                    $favStore = new MarketplaceFavourite;
                    $favStore->user_id = $user->user_id;
                    $favStore->id = $request->id;
                    $favStore->favourite_type = '2';
                    $favStore->save();

                    $message = "The product has been added to your favourite list";
                    return response()->json(['success' => $this->successStatus,
                                                'message' => $this->translate('messages.'.$message,$message),
                                                'data' => $favStore,
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
     * Mark unFavourite store/product
     * @Params $request
     */
    public function makeUnfavourite(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'id' => 'required',
                'favourite_type' => 'required', // 1 for store 2 for product
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->favourite_type == 1)
            {
                $isFavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $request->id)->first();
                if(!empty($isFavourite))
                {
                    $deleteFavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '1')->where('id', $request->id)->delete();
                    if($deleteFavourite == 1)
                    {
                        $message = "The store has been removed from your favourite list";
                        return response()->json(['success' => $this->successStatus,
                                                'message' => $this->translate('messages.'.$message,$message),
                                                 ], $this->successStatus);
                    }
                }
                else
                {
                    $message = "This store is not in your favourite list";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
            }
            elseif($request->favourite_type == 2)
            {
                $isFavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '2')->where('id', $request->id)->first();
                if(!empty($isFavourite))
                {
                    $deleteFavourite = MarketplaceFavourite::where('user_id', $user->user_id)->where('favourite_type', '2')->where('id', $request->id)->delete();
                    if($deleteFavourite == 1)
                    {
                        $message = "The product has been removed from your favourite list";
                        return response()->json(['success' => $this->successStatus,
                                                'message' => $this->translate('messages.'.$message,$message),
                                                 ], $this->successStatus);
                    }
                }
                else
                {
                    $message = "This product is not in your favourite list";
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

    
}
