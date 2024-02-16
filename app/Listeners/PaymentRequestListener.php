<?php

namespace App\Listeners;

use App\Events\PaymentRequestEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\MarketplaceOrderItem;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\Marketplace\Entities\MarketplaceStore;
use Mail;

class PaymentRequestListener
{
   

    /**
     * Handle the event.
     *
     * @param  PaymentRequestEvent  $event
     * @return void
     */
    public function handle(PaymentRequestEvent $event)
    {
        $buyer = User::where('user_id',$event->order->buyer_id)->first()->toArray();
        $store = MarketplaceStore::with('logo')->where('marketplace_store_id',$event->order->store_id)->first();
        $offer = MarketplaceOrderItem::where('order_id',$event->order->order_id)->whereNotNull('offer_map_id')->first();
        $offerInfo = '';
        if($offer){
            $offerInfo = ProductOffer::where('offer_id',$offer->offer_map_id)->first();
        }
        // print_r($store);
        // die();
        //Log::info('Execution order ID :'.$event->order->order_id, ['Buyer Id' => $event->order->buyer_id]);
        //Send Email to Admin
        Mail::send('emails.paymentRequest', ["order"=>$event->order,'store'=>$store,'offerInfo'=>$offerInfo], function($message) use ($offerInfo) {
            $message->to(env('ADMIN_EMAIL'));
            $message->subject('Payment Request');
        });

        // Sent Email to Importer
        Mail::send('emails.producerPaymentSentEmailToImporter', ["order"=>$event->order,'store'=>$store,'offerInfo'=>$offerInfo], function($message) use ($buyer) {
            $message->to($buyer['email']);
            $message->subject('Payment Request');
        });
    }
}
