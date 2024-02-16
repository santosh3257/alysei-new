<?php

namespace Modules\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Marketplace\Entities\MarketplaceOrderTransaction;
use Modules\Marketplace\Entities\MarketplaceOrder;
use App\Http\Traits\NotificationTrait;
use App\Notification;
use Modules\User\Entities\User; 
use Kreait\Firebase\Factory;
use Modules\User\Entities\DeviceToken;
use Illuminate\Support\Facades\File;
use League\Flysystem\Filesystem;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem\AwsS3v3\AwsS3Adapter;
use Modules\Marketplace\Entities\PaymentSetting;
use App\Events\OrderPaymentDoneByAdmin;

class TransactionController extends Controller
{
    use NotificationTrait;

    //Create Firebase connection
    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $database = $factory->createDatabase();    
        return $database;
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

                return $countNotification+1;
            }
            else{
                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => 0
                ]);

                return 0;
            }
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        // echo $_GET['time'];
        // die();
        $query = MarketplaceOrderTransaction::with('orderInfo','bankInfo')->orderBy('id','desc');
        if((isset($_GET['keyword'])) && (!empty($_GET['keyword']))){
            $query->where(function ($query) {
                $query->where('id', '=', $_GET['keyword'])
                      ->orWhere('order_id', '=', $_GET['keyword']);
            });
        }
        if((isset($_GET['date'])) && (!empty($_GET['date']))){
            $query->whereDate('created_at', '=', $_GET['date']);
        }
        if((isset($_GET['time'])) && (!empty($_GET['time']))){
            $query->whereTime('created_at', '=', $_GET['time']);
        }
        $transactions = $query->paginate(20);
        // echo '<pre>';
        // print_r($transactions);
        // echo '</pre>';
        // die();
        return view('marketplace::transactions.list', compact('transactions'));
    }

    public function updateAdminPaymentStatus(Request $request){
        $transactionId = $request->transaction_id;
        $transaction = MarketplaceOrderTransaction::where('id',$transactionId)->first();
        if($transaction){
            $paymentInfo = PaymentSetting::where('user_id',$transaction->seller_id)->first();
            
            $order = MarketplaceOrder::with('sellerInfo','buyerInfo','transactionInfo','shippingAddress','billingAddress','productItemInfo.productInfo','productItemInfo.productInfo.productCategory','productItemInfo.productInfo.product_gallery')->where('order_id',$transaction->order_id)->first();
            if($order){

                $transaction->admin_payment_made = 1;
                if($paymentInfo){
                    $transaction->account_holder_name = $paymentInfo->account_holder_name;
                    $transaction->bank_name = $paymentInfo->bank_name;;
                    $transaction->account_number = $paymentInfo->account_number;;
                    $transaction->swift_code = $paymentInfo->swift_code;;
                    $transaction->paypal_id = $paymentInfo->paypal_id;;
                    $transaction->default_payment = $paymentInfo->default_payment;;
                }
                $transaction->save();
                $orderAmount = ($transaction->paid_amount)*10/100;
                $adminPayAmount = $transaction->paid_amount - $orderAmount;

                $title_en = "Your payment ".$transaction->currency.$adminPayAmount." has been done by Admin";
                $title_it = "Il tuo pagamento di ".$transaction->currency.$adminPayAmount." è stato effettuato dall'amministratore";
                $selectedLocale = $this->pushNotificationUserSelectedLanguage($order->seller_id);
                if($selectedLocale == 'en'){
                    $title1 = "Your payment ".$transaction->currency.$adminPayAmount." has been done by Admin";
                }
                else{
                    $title1 = "Il tuo pagamento di ".$transaction->currency.$adminPayAmount." è stato effettuato dall'amministratore";
                }
                $admin = User::where('role_id', '1')->first();
                $saveNotification = new Notification;
                $saveNotification->from = $admin->user_id;
                $saveNotification->to = $order->seller_id;
                $saveNotification->notification_type = '12'; 
                $saveNotification->title_it = $title_it;
                $saveNotification->title_en = $title_en;
                $saveNotification->redirect_to = 'order_screen';
                $saveNotification->redirect_to_id = $transaction->order_id;

                $saveNotification->sender_id = $admin->user_id;
                $saveNotification->sender_name = "";
                $saveNotification->sender_image = null;
                $saveNotification->post_id = $transaction->order_id;
                $saveNotification->connection_id = null;
                $saveNotification->sender_role = '6';
                $saveNotification->comment_id = null;
                $saveNotification->reply = null;
                $saveNotification->likeUnlike = null;
                $saveNotification->save();

                $tokens = DeviceToken::where('user_id', $order->seller_id)->get();
                $notificationCount = $this->updateUserNotificationCountFirebase($order->seller_id);
                if(count($tokens) > 0)
                {
                    $collectedTokenArray = $tokens->pluck('device_token');
                    $this->sendNotification($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $admin->user_id , '', null, $transaction->order_id, null, '6', null,null,null);

                    $this->sendNotificationToIOS($collectedTokenArray, $title1, $saveNotification->redirect_to, $saveNotification->redirect_to_id, $saveNotification->notification_type, $admin->user_id, 'admin', null, null, $transaction->order_id, null, '6', null,null,null,null,null, $notificationCount);
                }
                event(new OrderPaymentDoneByAdmin($order));
                $message = "The status has been changed to paid";
                return response()->json(array('success' => true, 'message' => $message));
            }
            
        }
        else{
            $message = "Record don't exist our database";
            return response()->json(array('success' => true, 'message' => $message));
        }
    }
}
