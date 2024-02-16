<?php

namespace App\Listeners;

use App\Events\StoreReviewed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Marketplace\Entities\MarketplaceStore;
use Mail;

class sendStoreReviewedNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Welcome  $event
     * @return void
     */
    public function handle(StoreReviewed $event)
    {
        $store = MarketplaceStore::with('user')->find($event->storeId)->toArray();
        //Welcome Message to store
        Mail::send('emails.store_reviewed', ["store"=>$store], function($message) use ($store) {
            $message->to($store['user']['email']);
            $message->subject('Negozio recensito');
        });
    }
}

