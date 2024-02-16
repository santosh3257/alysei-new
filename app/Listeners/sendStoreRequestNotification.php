<?php

namespace App\Listeners;

use App\Events\StoreRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Marketplace\Entities\MarketplaceStore;
use Mail;

class sendStoreRequestNotification
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
    public function handle(StoreRequest $event)
    {
        $store = MarketplaceStore::with('user')->find($event->storeId)->toArray();
        //Welcome Message to store
        Mail::send('emails.store_request', ["store"=>$store], function($message) use ($store) {
            $message->to($store['user']['email']);
            $message->subject('Grazie per aver creato un negozio');
        });

        Mail::send('emails.new_store_request', ["store"=>$store], function($message) use ($store) {
            $message->to(env('ADMIN_EMAIL'));
            $message->cc([env('SUB_ADMIN_EMAIL1'),env('SUB_ADMIN_EMAIL2')]);
            //$message->cc(['simotaio@yahoo.it','simona.taioli@alysei.com'])
            $message->subject('Richiesta di creazione di un nuovo negozio');
        });
    }
}

