<?php

namespace App\Listeners;

use App\Events\OrderStatusChangeEmailEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\MarketplaceOrderItem;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\Marketplace\Entities\MarketplaceStore;
use Mail;

class OrderStatusChangeListener
{

    /**
     * Handle the event.
     *
     * @param  OrderStatusChangeEmailEvent  $event
     * @return void
     */
    public function handle(OrderStatusChangeEmailEvent $event)
    {
        $buyer = User::where('user_id',$event->order->buyer_id)->first()->toArray();
        $store = MarketplaceStore::with('logo')->where('marketplace_store_id',$event->order->store_id)->first();
        $offer = MarketplaceOrderItem::where('order_id',$event->order->order_id)->whereNotNull('offer_map_id')->first();
        $offerInfo = '';
        if($offer){
            $offerInfo = ProductOffer::where('offer_id',$offer->offer_map_id)->first();
        }

        if($event->order->status == 'processing'){
            $status = 'accepted';
        }
        else{
            $status = $event->order->status;
        }
        
        Mail::send('emails.sendEmailChangeOrderStatus', ["order"=>$event->order,'store'=>$store,'offerInfo'=>$offerInfo], function($message) use ($buyer, $status) {
            $message->to($buyer['email']);
            $message->subject('Your Order '.$status);
        });
    }
}
