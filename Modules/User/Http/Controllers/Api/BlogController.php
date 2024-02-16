<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use Modules\User\Entities\Blog;
use Modules\User\Entities\BlogFeedback;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;
use App\Http\Traits\UploadImageTrait;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Modules\Activity\Entities\DiscoveryNewsView;
use Modules\Activity\Entities\DiscoverAlysei;

class BlogController extends CoreController
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
    Get blog listing
    ***/
    public function getBlogListing(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            
            if(!empty($request->visitor_profile_id))
            {
                $blogLists = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('user_id', $request->visitor_profile_id)->where('status', '1')->get();
            }
            else
            {
                $blogLists = Blog::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('user_id', $loggedInUser->user_id)->get();    
            }
            
            if(count($blogLists) > 0)
            {
                foreach($blogLists as $key => $blogList)
                {
                    if(($blogLists[$key]->status == '0' || $blogLists[$key]->status == '1') &&  $blogLists[$key]->user_id == $loggedInUser->user_id)
                    {
                        $blogLists[$key]->title = $this->translate('messages.'.$blogList->title, $blogList->title);
                        $blogLists[$key]->description = $this->translate('messages.'.$blogList->description, $blogList->description);
                    }
                    elseif($blogLists[$key]->status == '1')
                    {
                        $blogLists[$key]->title = $this->translate('messages.'.$blogList->title, $blogList->title);
                        $blogLists[$key]->description = $this->translate('messages.'.$blogList->description, $blogList->description);
                    }

                    //$blogLists[$key]->date = date('Y-m-d', strtotime($blogList->date) );
                    
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $blogLists,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->successStatus,'errors' =>['exception' => $this->translate('messages.'."No blogs found","No blogs found")]], $this->successStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Create blog
    ***/
    public function createBlog(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'title' => 'required', 
                'date' => 'required',
                'time' => 'required',  
                'description' => 'required', 
                'status' => 'required', 
                'image_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $createBLog = new Blog;
            $createBLog->user_id = $loggedInUser->user_id;
            $createBLog->title = $request->title;
            $createBLog->slug = SlugService::createSlug(Blog::class, 'slug', $request->title);
            $createBLog->date = $request->date;
            $createBLog->time = $request->time;
            $createBLog->description = $request->description;
            $createBLog->status = $request->status;
            $createBLog->image_id = $this->uploadFrontImage($request->file('image_id'));
            $createBLog->save();

            $discoveryAlysei = DiscoverAlysei::where('discover_alysei_id',3)->first();
            if($discoveryAlysei){
                $discoveryAlysei->new_update = 1;
                $discoveryAlysei->save();
                DiscoveryNewsView::where('viewType',$discoveryAlysei->name)->delete();
            }

            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Blog created successfuly!","Blog created successfuly!")
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /***
    Update blog
    ***/
    public function updateBlog(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'blog_id' => 'required', 
                'title' => 'required', 
                'date' => 'required',
                'time' => 'required',  
                'description' => 'required', 
                'status' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $createBLog = Blog::where('blog_id', $request->blog_id)->first();
            $createBLog->title = $request->title;
            $createBLog->date = $request->date;
            $createBLog->time = $request->time;
            $createBLog->description = $request->description;
            $createBLog->status = $request->status;
            if(!empty($request->image_id))
            {
                $this->deleteAttachment($createBLog->image_id);
                $createBLog->image_id = $this->uploadFrontImage($request->file('image_id'));
            }
                
            $createBLog->save();

            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Blog updated successfuly!","Blog updated successfuly!")
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Delete blog
    ***/
    public function deleteBlog(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'blog_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $blog = Blog::where('blog_id', $request->blog_id)->where('user_id', $loggedInUser->user_id)->first();
            if(!empty($blog))
            {
                $this->deleteAttachment($blog->image_id);
                $isBlogDeleted = Blog::where('blog_id', $request->blog_id)->delete();
                if($isBlogDeleted == 1)
                {
                    return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Blog deleted successfuly!","Blog deleted successfuly!")
                                    ], $this->successStatus);
                }
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Something went wrong","Something went wrong")]], $this->exceptionStatus);    
                }
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Invalid blog","Invalid blog")]], $this->exceptionStatus);    
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    public function blogFeedback(Request $request){
        try{
            $user = $this->user;

            $requestedFields = $request->params;
            $rules = $this->validateFeedbackData($requestedFields);
            
            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $alreadyFeedbackGiven = BlogFeedback::where('user_id',$user->user_id)->where('blog_id',$requestedFields['blog_id'])->first();
            if($alreadyFeedbackGiven){
                $message = "Opps! You have already given feedback";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            else{
                $feedback = new BlogFeedback;
                $feedback->user_id = $user->user_id;
                $feedback->blog_id = $requestedFields['blog_id'];
                $feedback->name = $requestedFields['name'];
                $feedback->email = $requestedFields['email'];
                $feedback->message = $requestedFields['comment'];
                $feedback->created_at = Now();
                $feedback->updated_at = Now();
                $feedback->save();
                $message = "Your feedback has been saved successfully";
                return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'.$message,$message),
                                        ], $this->successStatus); 
            }
        }
        catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Validate Data
     * @Params $requestedfields
    */

    public function validateFeedbackData($requestedFields){
        $rules = [];
        foreach ($requestedFields as $key => $field) 
        {
            if($key == 'name')
            {
                $rules[$key] = 'required|max:190';
            }
            elseif($key == 'blog_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'email')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'comment')
            {
                $rules[$key] = 'required';
            }
        }
        return $rules;
    }


    public function getBlogFeedbacks($blogId){
        try{
            $user = $this->user;
            $blogFeedbacks = BlogFeedback::where('blog_id',$blogId)->get();
            if($blogFeedbacks){
                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($blogFeedbacks),
                                        'data' => $blogFeedbacks,
                                    ], $this->successStatus);
            }
            else{
                $message = "No blog feedback found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    
}
