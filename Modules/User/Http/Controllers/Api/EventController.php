<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use Modules\User\Entities\Event;
use Modules\User\Entities\EventLike;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;
use App\Http\Traits\UploadImageTrait;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Kreait\Firebase\Factory;
use App\Http\Traits\NotificationTrait;
use App\Notification;
use Modules\User\Entities\DeviceToken;
use Modules\Activity\Entities\DiscoverAlysei;
use Modules\Activity\Entities\DiscoveryNewsView;

class EventController extends CoreController
{
    use UploadImageTrait;
    use NotificationTrait;
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

    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();    
        return $database;
    }

    public function likeUnlikeInFirebase($id, $userId, $eventId)
    {
        $data = $this->conn_firbase()->getReference('like_unlike_event/'.$id)
        ->update([
        'id' => $id,
        'user_id' => $userId,
        'event_id' => $eventId
        ]);

        return $data;

    }

    public function removeLikes($id)
    {
        $data = $this->conn_firbase()->getReference('like_unlike_event/'.$id)->remove();
    }

    // Update user notification 
    public function updateUserNotificationCountFirebase($id)
    {
        try{
            $reference = $this->conn_firbase()->getReference('users');
            $snapshot = $reference->getChild($id);
            $getKey = $snapshot->getValue();
            if(isset($getKey['notification'])){
                $countNotification = $getKey['notification'];

                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => $countNotification+1
                ]);
            }
            else{
                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => 0
                ]);
            }
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }

    /*
     * Like Post
     * @Params $request
     */
    public function likeUnlikeEvent(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'event_id' => 'required',
                'like_or_unlike' => 'required', // 1 for like 0 for unlike
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $checkEvent = Event::where('event_id', $request->event_id)->first();
            if(!empty($checkEvent))
            {
                if($request->like_or_unlike == 1)
                {
                    $poster = User::with('avatar_id')->where('user_id', $user->user_id)->first();
                    $isLikedEvent = EventLike::where('event_id', $request->event_id)->where('user_id', $user->user_id)->first();


                    if(!empty($isLikedEvent))
                    {
                        $message = "You have already liked this event";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {
                        $eventLike = new EventLike;
                        $eventLike->event_id = $request->event_id;
                        $eventLike->user_id = $user->user_id;
                        $eventLike->save();

                        $checkEvent->like_counts = $checkEvent->like_counts + 1;
                        $checkEvent->save();

                        $this->likeUnlikeInFirebase($eventLike->id, $user->user_id, $request->event_id);

                        if($poster->role_id == 7 || $poster->role_id == 10)
                        {
                            $name = ucwords(strtolower($poster->first_name)) . ' ' . ucwords(strtolower($poster->last_name));
                        }
                        elseif($poster->role_id == 9)
                        { 
                            $name = $poster->restaurant_name;
                        }
                        else
                        {
                            $name = $poster->company_name;
                        }

                        $selectedLocale = $this->pushNotificationUserSelectedLanguage($checkEvent->user_id);
                        if($selectedLocale == 'en'){
                            $title1 = $name." liked your event";
                        }
                        else{
                            $title1 = 'A '.$name." piace il tuo evento";
                        }
                        $title = "liked your event";

                        $saveNotification = new Notification;
                        $saveNotification->from = $poster->user_id;
                        $saveNotification->to = $checkEvent->user_id;
                        $saveNotification->notification_type = 8; //liked a event
                        $saveNotification->title_it = $this->translate('messages.'.$title,$title);
                        $saveNotification->title_en = $title;
                        $saveNotification->redirect_to = 'event_screen';
                        $saveNotification->redirect_to_id = $request->event_id;

                        $saveNotification->sender_id = $request->user_id; 
                        $saveNotification->sender_name = $name;
                        $saveNotification->sender_image = null;
                        $saveNotification->post_id = $request->event_id;
                        $saveNotification->connection_id = null;
                        
                        $saveNotification->comment_id = null;
                        $saveNotification->reply = null;
                        $saveNotification->likeUnlike = $request->like_or_unlike;

                        $saveNotification->save();

                        $tokens = DeviceToken::where('user_id', $checkEvent->user_id)->get();
                        $notificationCount = $this->updateUserNotificationCountFirebase($checkEvent->user_id);
                        if(count($tokens) > 0)
                        {
                            $collectedTokenArray = $tokens->pluck('device_token');
                            $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, /*$poster->avatar_id->attachment_url*/ 
                                null, $request->event_id, null, null, null,null, $request->like_or_unlike);

                            $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $request->user_id, $name, 
                                null, $request->event_id, null, null, null,null, $request->like_or_unlike,null,null, $notificationCount);
                        }
                        $eventLikeData = EventLike::where('id', $eventLike->id)->first();

                        $message = "You liked this event";
                        
                        return response()->json(['success' => $this->successStatus,
                                                 'total_likes' => $checkEvent->like_counts,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                 'data' => $eventLikeData
                                                ], $this->successStatus);
                    }
                }
                elseif($request->like_or_unlike == 0)
                {
                    $isLikedEvent = EventLike::where('event_id', $request->event_id)->where('user_id', $user->user_id)->first();
                    if(!empty($isLikedEvent))
                    {
                        $this->removeLikes($isLikedEvent->id);
                        $isUnlikedEvent = EventLike::where('event_id', $request->event_id)->where('user_id', $user->user_id)->delete();
                        if($isUnlikedEvent == 1)
                        {
                            $checkEvent->like_counts = $checkEvent->like_counts - 1;
                            $checkEvent->save();

                            

                            $message = "You unliked this event";
                            return response()->json(['success' => $this->successStatus,
                                                 'total_likes' => $checkEvent->like_counts,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first like this event";
                            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You have not liked this event";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid like/unlike type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            else
            {
                $message = "Invalid event id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Get blog listing
    ***/
    public function getEventListing(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $user_id = $this->user->user_id;

            if(!empty($request->visitor_profile_id))
            {
                $eventLists = Event::with(['is_event_liked' => function ($like) use ($user_id) {
                                    return $like->whereHas('user', function ($user) use ($user_id) {
                                        $user->select('user_id');
                                        $user->where('user_id', $user_id);
                                    })->get();  
                                }])->with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('user_id', $request->visitor_profile_id)->where('status', '1')->get();
            }
            else
            {
                $eventLists = Event::with(['is_event_liked' => function ($like) use ($user_id) {
                                    return $like->whereHas('user', function ($user) use ($user_id) {
                                        $user->select('user_id');
                                        $user->where('user_id', $user_id);
                                    })->get();  
                                }])->with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('user_id', $loggedInUser->user_id)->where('status', '1')->get();
            }
            
            
            if(count($eventLists) > 0)
            {
                foreach($eventLists as $key => $eventList)
                {
                    $eventLists[$key]->event_name = $this->translate('messages.'.$eventList->event_name, $eventList->event_name);
                    $eventLists[$key]->host_name = $this->translate('messages.'.$eventList->host_name, $eventList->host_name);
                    $isLikedEvent = EventLike::where('event_id', $eventList->event_id)->where('user_id', $loggedInUser->user_id)->first();
                    $eventLists[$key]->like_flag = (!empty($isLikedEvent) ? 1 : 0);
                }
                return response()->json(['success' => $this->successStatus,
                                         'data' => $eventLists,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."No events found","No events found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Create event
    ***/
    public function createEvent(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'event_name' => 'required', 
                'host_name' => 'required',
                'location' => 'required',
                'date' => 'required',
                'time' => 'required',  
                'description' => 'required', 
                'website' => 'required', 
                'event_type' => 'required', 
                'registration_type' => 'required', 
                'image_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $createBLog = new Event;
            $createBLog->user_id = $loggedInUser->user_id;
            $createBLog->event_name = $request->event_name;
            $createBLog->slug = SlugService::createSlug(Event::class, 'slug', $request->event_name);
            $createBLog->host_name = $request->host_name;
            $createBLog->location = $request->location;
            $createBLog->date = date("Y-m-d",strtotime($request->date));
            $createBLog->time = date("H:i:s",strtotime($request->time));
            $createBLog->event_date_time = $request->date." ".$request->time;
            $createBLog->description = $request->description;
            $createBLog->website = $request->website;
            $createBLog->event_type = $request->event_type;
            $createBLog->registration_type = $request->registration_type;
            $createBLog->url = $request->url;
            $createBLog->image_id = $this->uploadFrontImage($request->file('image_id'));
            $createBLog->save();

            $discoveryAlysei = DiscoverAlysei::where('discover_alysei_id',1)->first();
            if($discoveryAlysei){
                $discoveryAlysei->new_update = 1;
                $discoveryAlysei->save();
                DiscoveryNewsView::where('viewType',$discoveryAlysei->name)->delete();
            }

            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Event created successfuly!","Event created successfuly!")
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Edit event
    ***/
    public function editEvent($eventId = '')
    {
        try
        {
            $loggedInUser = $this->user;
            
            $event = Event::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id','attachment')->where('event_id', $eventId)->where('status', '1')->first();
            if(!empty($event))
            {
                return response()->json(['success' => $this->successStatus,
                                         'data' => $event,
                                        ], $this->successStatus);
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Event not found","Event not found")]], $this->exceptionStatus);       
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /***
    Update event
    ***/
    public function updateEvent(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'event_id'  =>  'required',
                'event_name' => 'required', 
                'host_name' => 'required',
                'location' => 'required',
                'date' => 'required',
                'time' => 'required',  
                'description' => 'required', 
                'website' => 'required', 
                'event_type' => 'required', 
                'registration_type' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $event = Event::where('event_id', $request->event_id)->where('user_id', $loggedInUser->user_id)->first();
            if(!empty($event))
            {
                $event->event_name = $request->event_name;
                $event->host_name = $request->host_name;
                $event->location = $request->location;
                $event->date = date("Y-m-d",strtotime($request->date));
                $event->time = date("H:i:s",strtotime($request->time));
                $event->event_date_time = $request->date." ".$request->time;
                $event->description = $request->description;
                $event->website = $request->website;
                $event->event_type = $request->event_type;
                $event->registration_type = $request->registration_type;
                $event->url = $request->url;
                //$event->status = $request->status;

                if(!empty($request->image_id))
                {
                    $this->deleteAttachment($event->image_id);
                    $event->image_id = $this->uploadFrontImage($request->file('image_id'));
                }
                $event->save();

                return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'."Event updated successfuly!","Event updated successfuly!")
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
    Delete event
    ***/
    public function deleteEvent(Request $request)
    {
        try
        {
            $loggedInUser = $this->user;
            $validator = Validator::make($request->all(), [ 
                'event_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $event = Event::where('event_id', $request->event_id)->where('user_id', $loggedInUser->user_id)->first();
            if(!empty($event))
            {
                $this->deleteAttachment($event->image_id);
                $isEventDeleted = Event::where('event_id', $request->event_id)->delete();
                if($isEventDeleted == 1)
                {
                    return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'."Event deleted successfuly!","Event deleted successfuly!")
                                    ], $this->successStatus);
                }
                else
                {
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Something went wrong","Something went wrong")]], $this->exceptionStatus);    
                }
            }
            else
            {
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'."Invalid event","Invalid event")]], $this->exceptionStatus);    
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    
}
