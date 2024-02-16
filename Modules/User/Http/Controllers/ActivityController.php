<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Activity\Entities\ActivityAction;
use Modules\Activity\Entities\ActivityLike;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use App\Http\Traits\UploadImageTrait;
use Carbon\Carbon;
use Validator;
use App\Attachment;
use Kreait\Firebase\Factory;
use DB;
use Modules\Activity\Entities\ActivitySpam;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    use UploadImageTrait;

    public function CreateDynamicLink()
    {
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $dynamicLinksDomain = 'https://devalysei.page.link';
        $dynamicLinks = $factory->createDynamicLinksService($dynamicLinksDomain);

        $posts = ActivityAction::select('activity_action_id', 'object_id')->get();
        if($posts){
            foreach($posts as $key=>$post){
                $parameters = [
                    'dynamicLinkInfo' => [
                        'domainUriPrefix' => 'https://devalysei.page.link',
                        'link' => 'https://dev.alysei.com/home/post/activity/'. $post->activity_action_id,
                        'androidInfo' => [
                            'androidPackageName' => 'com.alysei',
                        ],
                        "iosInfo" => [
                            'iosBundleId'=> 'com.app.Alysei',
                            'iosAppStoreId' => '1634783265',
                        ],
                    ],
                    'suffix' => ['option' => 'SHORT'],
                ];

                $link = $dynamicLinks->createDynamicLink($parameters);
                if($link){
                    ActivityAction::where('activity_action_id', $post->activity_action_id)->update(['directLink' => $link]);
                }

                echo $link.'<br>';
            }
        }
        
    }
    public function index()
    {
        $keyword = isset($_GET['keyword'])? $_GET['keyword'] : '';
        $feeds_search = ActivityAction::with('subjectId')->orderBy('created_at','desc');
        if((isset($_GET['keyword'])) && (!empty($_GET['keyword']))){
            $feeds_search->Where(function ($q) use ($keyword) {
            $q->where('activity_action_id', 'LIKE', '%' . $keyword . '%')
                ->orWhere('slug', 'LIKE', '%' . $keyword . '%')
                ->orWhere('privacy', 'LIKE', '%' . $keyword . '%')
                ->orWhere('body', 'LIKE', '%' . $keyword . '%')
                ->orWhereHas('subjectId', function($query) use ($keyword){
                    $query->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('restaurant_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('first_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('company_name', 'LIKE', '%' . $keyword . '%')
                    ;});
            });
        }
        $feeds = $feeds_search->paginate(10);
        return view('user::feed.list', compact('feeds'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $activityPost = ActivityAction::with('attachments.attachment_link','subjectId')->select('activity_action_id','slug','privacy','body','subject_id')->where('activity_action_id', $id)->first();
        return view('user::feed.show',compact('activityPost'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('user::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $activityPost = ActivityAction::where('activity_action_id', $id)->first();
        if(!empty($activityPost))
            {
                $this->deleteSelectedPost($id);
                $likes = ActivityLike::where('resource_id', $id)->get();
                if($likes){
                    foreach($likes as $like){
                        $this->removeLikes($like->activity_like_id);
                    }
                }  

                $message = "Feed deleted successfuly";
                return redirect()->back()->with('success', $message);
            }
            else{
        
                $message = "We can't be deleted";
                return redirect()->back()->with('error', $message);

            }
        
      /*  $feed = ActivityAction::where('activity_action_id', $id)->first();
        if($feed){
            $feed->delete();
            $message = "Feed deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }  */
    }
    public function deleteSelectedPost($postId){
        $checkSharedPost = ActivityAction::where('shared_post_id', $postId)->first();
        $isDeletedPost = ActivityAction::where('activity_action_id', $postId)->delete();
        if(!$checkSharedPost){
            if($isDeletedPost == 1)
            {
                $activityAttchments = ActivityAttachment::where('action_id', $postId)->get();
                if(count($activityAttchments) > 0)
                {
                    foreach($activityAttchments as $activityAttchment)
                    {
                        $this->deletePostAttachment($activityAttchment->id);
                    }
                    
                    $isDeletedActivityAttachment = ActivityAttachment::where('action_id', $postId)->delete();

                }
            }
        }

    }
    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();    
        return $database;
    }
    public function removeLikes($id)
    {
        $data = $this->conn_firbase()->getReference('like_unlike_post/'.$id)->remove();
    }

    /*
     * Get All activity spams
     */
    public function getSpamsActivity(){

        $keyword = isset($_GET['keyword'])? $_GET['keyword'] : '';
      /*  $feeds = ActivityAction::whereIn('activity_action_id',function($query)
                        {
                            $query->select('activity_action_id')
                            ->from('activity_spams');
                        })
                    ->Where(function ($q) use ($keyword) {
            $q->where('activity_action_id', 'LIKE', '%' . $keyword . '%')
                ->orWhere('slug', 'LIKE', '%' . $keyword . '%')
                ->orWhere('privacy', 'LIKE', '%' . $keyword . '%')
                ->orWhere('body', 'LIKE', '%' . $keyword . '%');
            })->paginate(10);  */
            
           $feeds = ActivitySpam::select('users.name','users.role_id','users.restaurant_name','users.first_name','users.last_name','users.company_name','activity_actions.body', 'activity_actions.privacy', 'activity_spams.activity_action_id', 'activity_spams.report_by', DB::raw('count(*) as total'))
         ->leftJoin('activity_actions', 'activity_actions.activity_action_id', '=', 'activity_spams.activity_action_id')
         ->leftJoin('users', 'users.user_id', '=', 'activity_actions.subject_id')
         ->where('activity_actions.deleted_at',null)->groupBy('activity_spams.activity_action_id')->orderBy('activity_spams.created_at','desc')->Where(function ($q) use ($keyword) {
            $q->where('activity_spams.activity_action_id', 'LIKE', '%' . $keyword . '%')
                ->orWhere('users.name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('users.restaurant_name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('users.first_name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('users.last_name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('users.company_name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('activity_actions.privacy', 'LIKE', '%' . $keyword . '%')
                ->orWhere('activity_actions.body', 'LIKE', '%' . $keyword . '%');
            })
         ->paginate(10);
        $totalSpams = ActivitySpam::where('status',0)->count();
        
        return view('user::feed.spamlists', compact('feeds'));
    }

    /*
     * show activity spams
     */
    public function showSpamActivity($id){

        if(isset($_GET['delete']) && $_GET['activity_action_id'] && $_GET['activity_action_id'] !=''){
            $activityPost = ActivityAction::where('activity_action_id', $id)->first();
            if(!empty($activityPost))
                {
                    $this->deleteSelectedPost($id);
                    $likes = ActivityLike::where('resource_id', $id)->get();
                    if($likes){
                        foreach($likes as $like){
                            $this->removeLikes($like->activity_like_id);
                        }
                    }  

                    ActivitySpam::where('activity_action_id',$id)->update(['status'=>'1']);
                    $message = "Feed deleted successfuly";
                    return redirect()->route('spams')->with('success', $message);
                }
        }

        $activityPost = ActivityAction::with('attachments.attachment_link','subjectId')->select('activity_action_id','privacy','body','subject_id')->where('activity_action_id', $id)->first();
        //dd($activityPost);
        $spams = ActivitySpam::with('user')->where('activity_action_id',$id)->get();
        
        return view('user::feed.singlespam',compact('activityPost','spams'));
    }

    /*
     * Delete All Posts 
     */
    public function deleteAllFeeds(Request $request){
        try{
            $ids = $request->ids;
            if(!empty($ids)){
                foreach($ids as $id){
                    $activityPost = ActivityAction::where('activity_action_id', $id)->first();
                    if(!empty($activityPost))
                    {
                        $this->deleteSelectedPost($id);
                        $likes = ActivityLike::where('resource_id', $id)->get();
                        if($likes){
                            foreach($likes as $like){
                                $this->removeLikes($like->activity_like_id);
                            }
                        }  

                    }
                }
                $message = "Feed deleted successfuly";
                return response()->json(array('success' => true, 'message' => $message));
            }
            else{
                $message = "We can't be deleted";
                return response()->json(array('success' => false, 'message' => $message));

            }
        }
        catch(\Exception $e){
            return response()->json(array('success' => false, 'message' => $e));
        }
    }
    
}
