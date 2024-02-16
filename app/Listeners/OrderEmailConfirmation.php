<?php

namespace App\Listeners;

use App\Events\BuyerOrderConfirmationEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\MarketplaceOrderItem;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\Marketplace\Entities\MarketplaceStore;
use Mail;

class OrderEmailConfirmation
{


    /**
     * Handle the event.
     *
     * @param  BuyerOrderConfirmation  $event
     * @return void
     */
    public function handle(BuyerOrderConfirmationEvent $event)
    {
        $user = User::where('user_id',$event->order->buyer_id)->first()->toArray();
        $seller = User::where('user_id',$event->order->seller_id)->first()->toArray();
        $store = MarketplaceStore::with('logo')->where('marketplace_store_id',$event->order->store_id)->first();
        $offer = MarketplaceOrderItem::where('order_id',$event->order->order_id)->whereNotNull('offer_map_id')->first();
        $offerInfo = '';
        if($offer){
            $offerInfo = ProductOffer::where('offer_id',$offer->offer_map_id)->first();
        }
        // print_r($store);
        // die();
        //Log::info('Execution order ID :'.$event->order->order_id, ['Buyer Id' => $event->order->buyer_id]);
        //Send Email to buyer
        // Mail::send('emails.orderEmailTemplate', ["user"=>$user,"order"=>$event->order,'store'=>$store,'offerInfo'=>$offerInfo], function($message) use ($user) {
        //     $message->to($user['email']);
        //     $message->subject('Alysei Order');
        // });
        // Send Email to Seller
        Mail::send('emails.orderEmailTemplateToSeller', ["user"=>$user,"order"=>$event->order,'store'=>$store,'offerInfo'=>$offerInfo], function($message) use ($seller) {
            $message->to($seller['email']);
            $message->subject('Alysei Order');
        });
    }
}
