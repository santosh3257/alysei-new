<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Attachment;
use App\Notification;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Illuminate\Support\Facades\Auth; 
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Validator;
use Storage;
use League\Flysystem\Filesystem;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem\AwsS3v3\AwsS3Adapter;
use Modules\User\Entities\User;
use Kreait\Firebase\Factory;
//use App\Events\UserRegisterEvent;

trait NotificationTrait
{
    
    /***
    Send Notification
    ***/
    public function sendNotification($token, $title, $redirectTo, $redirectToId, $notificationType, $senderId, $senderName, $senderImg, $postId='', $connectionId='', $senderRoleId='', $commentId='', $reply='', $likeUnlike,$notiTitle = null,$productName=null,$productImage=null)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $jsonArray['title'] = $notiTitle ? "Alysei" :$notiTitle;
        $jsonArray['body'] = $title;
        $jsonArray['notification_type'] = $notificationType;
        $jsonArray['redirect_to'] = $redirectTo;
        $jsonArray['redirect_to_id'] = $redirectToId;
        
        $jsonArray['sender_id'] = $senderId;
        $jsonArray['sender_name'] = $senderName;
        $jsonArray['sender_image'] = $senderImg;
        $jsonArray['post_id'] = $postId;
        $jsonArray['connection_id'] = $connectionId;
        $jsonArray['sender_role'] = $senderRoleId;
        $jsonArray['comment_id'] = $commentId;
        $jsonArray['reply'] = $reply;
        $jsonArray['likeUnlike'] = $likeUnlike;
        $jsonArray['productName'] = $productName;
        $jsonArray['productImage'] = $productImage;
        
        $fcmNotification = [
        	//'to'        => $token, //single token
            'registration_ids' => $token, //multple token array
            'data' => $jsonArray,
            'mutable-content'=> true, 
        ];

        $serverKey = env('FIREBASE_AUTHORIZATION_KEY');
        $headers =[
            'Authorization: key= '.$serverKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /***
    Send Notification to ios
    ***/
    public function sendNotificationToIOS($token, $title, $redirectTo, $redirectToId, $notificationType, $senderId, $senderName, $senderImg, $postId='', $connectionId='', $senderRoleId='', $commentId='', $reply='', $likeUnlike,$notiTitle=null,$productName=null,$productImage=null, $notificationCount = 0)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $jsonArray['title'] = $title;
        $jsonArray['notification_type'] = $notificationType;
        $jsonArray['redirect_to'] = $redirectTo;
        $jsonArray['redirect_to_id'] = $redirectToId;
        
        $jsonArray['sender_id'] = $senderId;
        $jsonArray['sender_name'] = $senderName;
        $jsonArray['sender_image'] = $senderImg;
        $jsonArray['post_id'] = $postId;
        $jsonArray['connection_id'] = $connectionId;
        $jsonArray['sender_role'] = $senderRoleId;
        $jsonArray['comment_id'] = $commentId;
        $jsonArray['reply'] = $reply;
        $jsonArray['likeUnlike'] = $likeUnlike;
        $jsonArray['productName'] = $productName;
        $jsonArray['productImage'] = $productImage;
        
       
        $serverKey = env('FIREBASE_AUTHORIZATION_KEY');
        
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='. $serverKey;

        $notiTitle  = $notiTitle ? "Alysei": $notiTitle;
        $notification = array('title'=>$notiTitle,'body' =>$title , 'data' => $jsonArray, 'sound' => 'default', 'badge' =>$notificationCount,'mutable-content'=> true );
        $arrayToSend = array('registration_ids' => $token, 'notification'=>$notification,'priority'=>'high');
        $fcmNotification = [
                'registration_ids' => $token, //multple token array
                //'to'        => $token, //single token
                'data' => $jsonArray,
            ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function pushNotificationUserSelectedLanguage($userId){

        $user = User::where('user_id', $userId)->first(); 

        return $user->locale;
    }

    // Create connection from Firebase
    public function firebaseConnection(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();    
        return $database;
    }

    // Update user notification 
    public function updateFirebaseUsersNotification($id)
    {
        
            $reference = $this->firebaseConnection()->getReference('users');
            $snapshot = $reference->getChild($id);
            $getKey = $snapshot->getValue();
            if(isset($getKey['notification'])){
                $countNotification = $getKey['notification'];

                $data = $this->firebaseConnection()->getReference('users/'.$id)
                ->update([
                'notification' => $countNotification+1
                ]);
            }
            else{
                $data = $this->firebaseConnection()->getReference('users/'.$id)
                ->update([
                'notification' => 0
                ]);
            }
            return true;

    }

}